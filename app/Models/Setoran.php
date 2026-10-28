<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Setoran extends Model
{
    protected $table = 'setoran';

    protected $fillable = [
        'user_id', 'pengepul_id',
        'perkiraan_kg', 'perkiraan_minyak_kg', 'perkiraan_endapan_kg',
        'minyak_kg', 'endapan_kg',
        'alamat_jemput', 'catatan', 'catatan_pengepul', 'bukti_foto',
        'harga_minyak_saat_itu', 'harga_endapan_saat_itu', 'total_saldo',
        'status', 'tanggal_ajukan', 'tanggal_selesai',
    ];

    protected function casts(): array
    {
        return [
            'perkiraan_kg' => 'decimal:2',
            'perkiraan_minyak_kg' => 'decimal:2',
            'perkiraan_endapan_kg' => 'decimal:2',
            'minyak_kg' => 'decimal:2',
            'endapan_kg' => 'decimal:2',
            'harga_minyak_saat_itu' => 'decimal:2',
            'harga_endapan_saat_itu' => 'decimal:2',
            'total_saldo' => 'decimal:2',
            'tanggal_ajukan' => 'datetime',
            'tanggal_selesai' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pengepul()
    {
        return $this->belongsTo(User::class, 'pengepul_id');
    }

    // ---------- Scope ----------

    public function scopeSelesai(Builder $query): Builder
    {
        return $query->where('status', 'selesai');
    }

    public function scopeMenunggu(Builder $query): Builder
    {
        return $query->where('status', 'menunggu');
    }

    public function scopeDiproses(Builder $query): Builder
    {
        return $query->where('status', 'diproses');
    }

    public function scopeRentang(Builder $query, ?string $dari, ?string $sampai): Builder
    {
        $kolom = 'tanggal_ajukan';

        return $query
            ->when($dari, fn (Builder $q) => $q->whereDate($kolom, '>=', $dari))
            ->when($sampai, fn (Builder $q) => $q->whereDate($kolom, '<=', $sampai));
    }

    // ---------- Helper ----------

    public function sudahSelesai(): bool
    {
        return $this->status === 'selesai';
    }

    /**
     * Perkiraan minyak dari warga.
     *
     * Data lama hanya menyimpan satu angka total, jadi seluruh berat
     * dianggap minyak bila rincian baru belum diisi.
     */
    public function perkiraanMinyakKg(): float
    {
        if ($this->perkiraan_minyak_kg !== null) {
            return (float) $this->perkiraan_minyak_kg;
        }

        return (float) $this->perkiraan_kg;
    }

    /** Perkiraan endapan dari warga (0 bila tidak diisi). */
    public function perkiraanEndapanKg(): float
    {
        return (float) ($this->perkiraan_endapan_kg ?? 0);
    }

    /** Total berat yang diperkirakan warga (minyak + endapan). */
    public function perkiraanTotalKg(): float
    {
        return round($this->perkiraanMinyakKg() + $this->perkiraanEndapanKg(), 2);
    }

    /** Total berat yang benar-benar diterima (minyak + endapan), dalam kg. */
    public function totalTerimaKg(): float
    {
        return round((float) ($this->minyak_kg ?? 0) + (float) ($this->endapan_kg ?? 0), 2);
    }

    /**
     * Selisih berat hasil timbang dengan perkiraan warga.
     * Negatif berarti lebih ringan dari perkiraan.
     */
    public function selisihKg(): ?float
    {
        if ($this->minyak_kg === null && $this->endapan_kg === null) {
            return null;
        }

        return round($this->totalTerimaKg() - $this->perkiraanTotalKg(), 2);
    }

    /**
     * Hitung nilai setoran dari minyak dan endapan.
     *
     * @return array{nilai: float, minyak: float, endapan: float}
     */
    public static function hitungNilai(float $minyakKg, float $endapanKg, float $hargaMinyak, float $hargaEndapan): array
    {
        $minyak = round($minyakKg * $hargaMinyak, 2);
        $endapan = round($endapanKg * $hargaEndapan, 2);

        return [
            'minyak' => $minyak,
            'endapan' => $endapan,
            'nilai' => round($minyak + $endapan, 2),
        ];
    }
}
