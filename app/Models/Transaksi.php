<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaksi';

    protected $fillable = [
        'user_id', 'tipe', 'jumlah', 'saldo_sebelum', 'saldo_sesudah',
        'referensi_tipe', 'referensi_id', 'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'decimal:2',
            'saldo_sebelum' => 'decimal:2',
            'saldo_sesudah' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function masuk(): bool
    {
        return (float) $this->jumlah >= 0;
    }

    public function labelTipe(): string
    {
        return [
            'setoran' => 'Setoran minyak',
            'pencairan' => 'Pencairan saldo',
            'bonus' => 'Bonus',
            'penyesuaian' => 'Penyesuaian admin',
        ][$this->tipe] ?? ucfirst($this->tipe);
    }
}
