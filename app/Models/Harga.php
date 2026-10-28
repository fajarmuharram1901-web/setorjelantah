<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

class Harga extends Model
{
    protected $table = 'harga';

    protected $fillable = ['harga_minyak_kg', 'harga_endapan_kg', 'updated_by'];

    protected function casts(): array
    {
        return [
            'harga_minyak_kg' => 'decimal:2',
            'harga_endapan_kg' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /** Baris harga yang sedang berlaku (entri terbaru). */
    public static function aktif(): ?self
    {
        return static::orderByDesc('id')->first();
    }

    /** Harga minyak per kilogram yang berlaku. */
    public static function hargaMinyak(): float
    {
        return (float) (static::aktif()?->harga_minyak_kg ?? 0);
    }

    /** Harga endapan per kilogram yang berlaku. */
    public static function hargaEndapan(): float
    {
        return (float) (static::aktif()?->harga_endapan_kg ?? 5000);
    }

    /** Batas atas harga minyak per kilogram (validasi). */
    public const MAKS_HARGA_MINYAK = 100000;

    /** Batas atas berat satu setoran dalam kilogram. */
    public const MAKS_KG = 1000;

    /** Jeda minimal antar perubahan harga, dalam hari. */
    public const JEDA_HARGA_HARI = 7;

    /**
     * Apakah aturan jeda 7 hari sedang berlaku.
     *
     * Admin bisa mematikan aturan ini dari halaman Harga lewat sakelar
     * buka/kunci, misalnya saat sedang demonstrasi atau ujian.
     */
    public static function jedaAktif(): bool
    {
        return \App\Models\Pengaturan::hargaTerkunci();
    }

    /**
     * Kapan harga berikutnya boleh diubah.
     *
     * Null kalau belum pernah ada harga yang tercatat, atau kalau aturan
     * jeda sedang dimatikan admin.
     */
    public static function bolehUbahPada(): ?CarbonInterface
    {
        if (! static::jedaAktif()) {
            return null;
        }

        $terakhir = static::aktif();

        if (! $terakhir || ! $terakhir->created_at) {
            return null;
        }

        return $terakhir->created_at->copy()->addDays(self::JEDA_HARGA_HARI);
    }

    /** Sisa hari sebelum harga boleh diubah lagi. 0 berarti sudah boleh. */
    public static function sisaHari(): int
    {
        $boleh = static::bolehUbahPada();

        if ($boleh === null || $boleh->lessThanOrEqualTo(now())) {
            return 0;
        }

        return (int) ceil(now()->diffInSeconds($boleh) / 86400);
    }

    /** Apakah harga sedang dalam masa jeda. */
    public static function dalamJeda(): bool
    {
        return static::sisaHari() > 0;
    }
}
