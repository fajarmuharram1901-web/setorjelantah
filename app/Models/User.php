<?php

namespace App\Models;

use App\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** Saldo minimum yang boleh dicairkan. */
    public const MIN_PENCAIRAN = 10000.0;
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'no_hp', 'alamat', 'saldo',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'saldo' => 'decimal:2',
        ];
    }

    // Setoran yang diajukan user ini (sebagai rumah tangga)
    public function setoran()
    {
        return $this->hasMany(Setoran::class, 'user_id');
    }

    // Setoran yang ditangani user ini (sebagai pengepul)
    public function tugasPengepul()
    {
        return $this->hasMany(Setoran::class, 'pengepul_id');
    }

    public function transaksi(): HasMany
    {
        return $this->hasMany(Transaksi::class);
    }

    public function pencairans(): HasMany
    {
        return $this->hasMany(Pencairan::class);
    }

    public function scopePeran(Builder $query, string $peran): Builder
    {
        return $query->where('role', $peran);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // Nomor HP tampil dengan kode negara +62 (menggantikan 0 di depan)
    public function getNoHpFormattedAttribute(): ?string
    {
        if ($this->no_hp === null) {
            return null;
        }

        return '+62'.$this->no_hp;
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    public function isPengepul(): bool
    {
        return $this->role === 'pengepul';
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    public function inisial(): string
    {
        $potongan = preg_split('/\s+/', trim($this->name)) ?: [];
        $awal = mb_substr((string) ($potongan[0] ?? ''), 0, 1);

        if (count($potongan) > 1) {
            $awal .= mb_substr((string) end($potongan), 0, 1);
        }

        return mb_strtoupper($awal ?: '?');
    }

    public function labelPeran(): string
    {
        return [
            'admin' => 'Administrator',
            'pengepul' => 'Pengepul',
            'user' => 'Rumah Tangga',
        ][$this->role] ?? ucfirst((string) $this->role);
    }

    /**
     * Saldo yang masih boleh dicairkan.
     *
     * Hanya permintaan berstatus "menunggu" yang dikurangi, karena saldo pada
     * kolom `saldo` sudah dipotong begitu pencairan disetujui.
     */
    public function saldoTersedia(): float
    {
        $tertahan = (float) $this->pencairans()->menunggu()->sum('jumlah');

        return max(0.0, round((float) $this->saldo - $tertahan, 2));
    }

    public function bisaPencair(): bool
    {
        return $this->isUser() && $this->saldoTersedia() >= self::MIN_PENCAIRAN;
    }
}
