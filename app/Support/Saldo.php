<?php

namespace App\Support;

use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Buku besar saldo.
 *
 * Setiap perubahan saldo selalu accompanied oleh baris di tabel `transaksi`
 * sehingga saldo pada tabel `users` bisa diaudit ulang. Semua operasi berjalan
 * di dalam satu transaksi database dan mengunci baris user supaya dua
 * permintaan bersamaan tidak menimpa saldo yang sama.
 */
class Saldo
{
    /**
     * Tambah saldo (setoran selesai, bonus, penyesuaian admin).
     */
    public static function kredit(User $user, float $jumlah, string $tipe, ?string $keterangan = null, ?int $referensiId = null, ?string $referensiTipe = null): Transaksi
    {
        return self::catat($user, $jumlah, $tipe, $keterangan, $referensiId, $referensiTipe);
    }

    /**
     * Kurangi saldo (pencairan disetujui).
     */
    public static function debit(User $user, float $jumlah, string $tipe, ?string $keterangan = null, ?int $referensiId = null, ?string $referensiTipe = null): Transaksi
    {
        return self::catat($user, -abs($jumlah), $tipe, $keterangan, $referensiId, $referensiTipe);
    }

    /**
     * Catat satu mutasi saldo. Nilai negatif mengurangi, positif menambah.
     */
    public static function catat(User $user, float $jumlah, string $tipe, ?string $keterangan = null, ?int $referensiId = null, ?string $referensiTipe = null): Transaksi
    {
        $transaksi = DB::transaction(function () use ($user, $jumlah, $tipe, $keterangan, $referensiId, $referensiTipe) {
            // Kunci baris user supaya increment tidak saling menimpa.
            $terkunci = User::whereKey($user->getKey())->lockForUpdate()->firstOrFail();

            $sebelum = (float) $terkunci->saldo;
            $sesudah = max(0.0, round($sebelum + $jumlah, 2));

            $terkunci->forceFill(['saldo' => $sesudah])->save();

            return Transaksi::create([
                'user_id' => $terkunci->id,
                'tipe' => $tipe,
                'jumlah' => $jumlah,
                'saldo_sebelum' => $sebelum,
                'saldo_sesudah' => $sesudah,
                'referensi_tipe' => $referensiTipe,
                'referensi_id' => $referensiId,
                'keterangan' => $keterangan,
            ]);
        });

        // Samakan instance yang dipanggil supaya pemanggil tidak membaca saldo lama.
        $user->forceFill(['saldo' => $transaksi->saldo_sesudah])->syncOriginalAttribute('saldo');

        return $transaksi;
    }
}
