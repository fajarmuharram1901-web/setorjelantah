<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('setoran', function (Blueprint $table) {
            // Bukti foto oleh pengepul saat sorta di lokasi (disimpan di disk publik).
            $table->string('bukti_foto')->nullable()->after('catatan');
            $table->text('catatan_pengepul')->nullable()->after('bukti_foto');

            // Penilaian warga terhadap pengepul setelah setoran selesai.
            $table->unsignedTinyInteger('bintang')->nullable()->after('total_saldo');
            $table->text('ulasan')->nullable()->after('bintang');
            $table->timestamp('dinilai_pada')->nullable()->after('ulasan');
        });
    }

    public function down(): void
    {
        Schema::table('setoran', function (Blueprint $table) {
            $table->dropColumn(['bukti_foto', 'catatan_pengepul', 'bintang', 'ulasan', 'dinilai_pada']);
        });
    }
};
