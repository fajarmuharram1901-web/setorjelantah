<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Warga sekarang memperkirakan minyak dan endapan secara terpisah saat
 * mengajukan setoran, bukan hanya satu angka total.
 *
 * Kolom `perkiraan_kg` tetap disimpan sebagai total keduanya supaya laporan,
 * dashboard, dan penyaring yang sudah ada tidak perlu diubah.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('setoran', function (Blueprint $table) {
            $table->decimal('perkiraan_minyak_kg', 10, 2)->unsigned()->nullable()->after('perkiraan_kg');
            $table->decimal('perkiraan_endapan_kg', 10, 2)->unsigned()->nullable()->after('perkiraan_minyak_kg');
        });

        // Data lama tidak memisahkan minyak dan endapan, jadi seluruh berat
        // dianggap minyak. Ini juga yang dilakukan model sebelum endapan ada.
        DB::table('setoran')
            ->whereNull('perkiraan_minyak_kg')
            ->update([
                'perkiraan_minyak_kg' => DB::raw('perkiraan_kg'),
                'perkiraan_endapan_kg' => 0,
            ]);
    }

    public function down(): void
    {
        Schema::table('setoran', function (Blueprint $table) {
            $table->dropColumn(['perkiraan_minyak_kg', 'perkiraan_endapan_kg']);
        });
    }
};
