<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Penyimpanan pengaturan sederhana berbasis kunci dan nilai.
 *
 * Dipakai untuk opsi yang harus bisa diubah admin lewat antarmuka tanpa
 * menyunting berkas .env, misalnya status kunci harga.
 */
class Pengaturan extends Model
{
    protected $table = 'pengaturan';

    protected $fillable = ['kunci', 'nilai'];

    /** Kunci pengaturan: aktifkan aturan jeda 7 hari pada perubahan harga. */
    public const HARGA_TERKUNCI = 'harga_terkunci';

    /**
     * Ambil nilai pengaturan, atau nilai bawaan bila belum pernah disimpan.
     */
    public static function ambil(string $kunci, ?string $bawaan = null): ?string
    {
        $baris = static::where('kunci', $kunci)->first();

        return $baris?->nilai ?? $bawaan;
    }

    /**
     * Ambil pengaturan sebagai boolean.
     *
     * Nilai yang dianggap benar: '1', 'true', 'ya', 'aktif', 'on'.
     */
    public static function ambilBool(string $kunci, bool $bawaan = false): bool
    {
        $nilai = static::ambil($kunci);

        if ($nilai === null) {
            return $bawaan;
        }

        return in_array(strtolower(trim($nilai)), ['1', 'true', 'ya', 'aktif', 'on'], true);
    }

    /** Simpan nilai pengaturan, membuat baris bila belum ada. */
    public static function simpan(string $kunci, string $nilai): void
    {
        static::updateOrCreate(['kunci' => $kunci], ['nilai' => $nilai]);
    }

    /**
     * Status kunci harga: true berarti aturan jeda 7 hari sedang berlaku.
     */
    public static function hargaTerkunci(): bool
    {
        return static::ambilBool(self::HARGA_TERKUNCI, true);
    }

    /** Nyalakan atau matikan aturan jeda harga. */
    public static function setHargaTerkunci(bool $nyala): void
    {
        static::simpan(self::HARGA_TERKUNCI, $nyala ? '1' : '0');
    }
}
