<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Perubahan satuan dari liter ke kilogram, endapan jadi entitas berbayar
 * sendiri, serta penghapusan gradasi minyak dan rating pengepul.
 */
return new class extends Migration
{
    /** Nama indeks yang dulu dibuat untuk kolom `kualitas`. */
    private const INDEKS_KUALITAS = 'setoran_kualitas_index';

    public function up(): void
    {
        // Harga: satu baris memuat harga minyak dan harga endapan (per kg).
        Schema::table('harga', function (Blueprint $table) {
            $table->renameColumn('harga_per_liter', 'harga_minyak_kg');
            $table->decimal('harga_endapan_kg', 10, 2)->unsigned()->default(5000);
        });

        // Kolom `kualitas` pernah berindeks. Indeks harus dibuang lebih dulu,
        // karena SQLite tidak ikut menghapus indeksnya saat kolom dihapus
        // dan akan menolak dengan "no such column".
        $this->buangIndeksKualitas();

        Schema::table('setoran', function (Blueprint $table) {
            // Satuan field berubah dari liter ke kilogram.
            $table->renameColumn('perkiraan_liter', 'perkiraan_kg');
            $table->renameColumn('liter_aktual', 'minyak_kg');
            $table->renameColumn('harga_per_liter_saat_itu', 'harga_minyak_saat_itu');

            // Endapan hasil pembakaran, ditimbang terpisah dan dibayar sendiri.
            $table->decimal('endapan_kg', 10, 2)->unsigned()->nullable()->after('minyak_kg');

            // Harga endapan yang berlaku saat setoran dikonfirmasi.
            $table->decimal('harga_endapan_saat_itu', 10, 2)->unsigned()->nullable()->after('harga_minyak_saat_itu');

            // Gradasi minyak dan rating pengepul dihapus total.
            $table->dropColumn(['kualitas', 'bintang', 'ulasan', 'dinilai_pada']);
        });

        $this->lengkapiHargaEndapanLama();
    }

    /**
     * Isi `harga_endapan_saat_itu` untuk setoran lama yang belum punya harga
     * endapan tersimpan, memakai harga endapan yang berlaku saat itu.
     *
     * Tanpa ini laporan menampilkan "0" untuk harga endapan padahal(endapan
     * sudah punya harga resmi, jadi angka di riwayat menyesatkan.
     */
    private function lengkapiHargaEndapanLama(): void
    {
        $hargaEndapan = (float) (DB::table('harga')
            ->orderByDesc('id')
            ->value('harga_endapan_kg') ?? 5000);

        if ($hargaEndapan <= 0) {
            return;
        }

        DB::table('setoran')
            ->whereNull('harga_endapan_saat_itu')
            ->whereNotNull('harga_minyak_saat_itu')
            ->update(['harga_endapan_saat_itu' => $hargaEndapan]);
    }
    public function down(): void
    {
        // 1. Buang kolom endapan lebih dulu, terpisah dari operasi lain supaya
        //    MySQL tidak mencampur drop dan rename dalam satu ALTER.
        Schema::table('setoran', function (Blueprint $table) {
            $table->dropColumn(['endapan_kg', 'harga_endapan_saat_itu']);
        });

        // 2. Kembalikan nama kolom ke satuan lama.
        Schema::table('setoran', function (Blueprint $table) {
            $table->renameColumn('harga_minyak_saat_itu', 'harga_per_liter_saat_itu');
            $table->renameColumn('minyak_kg', 'liter_aktual');
            $table->renameColumn('perkiraan_kg', 'perkiraan_liter');
        });

        // 3. Kembalikan kolom gradasi dan rating beserta indeksnya.
        Schema::table('setoran', function (Blueprint $table) {
            $table->string('kualitas', 20)->default('sedang')->after('perkiraan_liter')->index();
            $table->unsignedTinyInteger('bintang')->nullable()->after('total_saldo');
            $table->text('ulasan')->nullable()->after('bintang');
            $table->timestamp('dinilai_pada')->nullable()->after('ulasan');
        });

        Schema::table('harga', function (Blueprint $table) {
            $table->dropColumn('harga_endapan_kg');
            $table->renameColumn('harga_minyak_kg', 'harga_per_liter');
        });
    }

    /**
     * Buang indeks `setoran_kualitas_index` bila masih ada.
     *
     * MySQL sudah menghapus indeksnya sendiri saat kolom dihapus, jadi
     * pemeriksaan 먼저 dipakai agar tidak error "index does not exist".
     */
    private function buangIndeksKualitas(): void
    {
        $ada = match (DB::getDriverName()) {
            'sqlite' => (bool) DB::selectOne(
                "SELECT name FROM sqlite_master WHERE type = 'index' AND name = ?",
                [self::INDEKS_KUALITAS]
            ),
            'mysql', 'mariadb' => (bool) DB::select(
                'SELECT 1 FROM information_schema.statistics
                 WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ? LIMIT 1',
                ['setoran', self::INDEKS_KUALITAS]
            ),
            default => false,
        };

        if ($ada) {
            Schema::table('setoran', function (Blueprint $table) {
                $table->dropIndex(self::INDEKS_KUALITAS);
            });
        }
    }
};
