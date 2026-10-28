<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Pencairan extends Model
{
    protected $table = 'pencairan';

    protected $fillable = [
        'kode', 'user_id', 'jumlah', 'metode', 'tujuan', 'nama_penerima',
        'status', 'alasan_tolak', 'diproses_oleh', 'diproses_pada',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'decimal:2',
            'diproses_pada' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $pencairan) {
            $pencairan->kode ??= self::kodeBaru();
        });
    }

    /** Nomor urut per hari, misalnya PNC-20260928-0007. */
    public static function kodeBaru(): string
    {
        $prefix = 'PNC-'.now()->format('Ymd').'-';

        $terakhir = static::where('kode', 'like', $prefix.'%')
            ->orderByDesc('kode')
            ->value('kode');

        $urutan = $terakhir ? ((int) substr($terakhir, -4)) + 1 : 1;

        return $prefix.str_pad((string) $urutan, 4, '0', STR_PAD_LEFT);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }

    public function scopeMenunggu(Builder $query): Builder
    {
        return $query->where('status', 'menunggu');
    }

    public function labelMetode(): string
    {
        return [
            'ewallet' => 'E-Wallet',
            'transfer' => 'Transfer Bank',
            'tunai' => 'Tunai / Kios',
        ][$this->metode] ?? ucfirst($this->metode);
    }

    public function badgeStatus(): string
    {
        return [
            'menunggu' => 'warning',
            'disetujui' => 'success',
            'ditolak' => 'danger',
        ][$this->status] ?? 'secondary';
    }
}
