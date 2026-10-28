<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Bentuk data pengguna untuk aplikasi seluler.
 *
 * Kolom `saldo_tersedia` sengaja ditambahkan di sini: aplikasi perlu
 * membedakan saldo yang sudah tercatat dengan saldo yang masih ditahan
 * oleh permintaan pencairan yang berstatus menunggu.
 */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'no_hp' => $this->no_hp,
            'no_hp_formatted' => $this->no_hp_formatted,
            'alamat' => $this->alamat,
            'role' => $this->role,
            'label_peran' => $this->labelPeran(),
            'inisial' => $this->inisial(),
            'saldo' => (float) $this->saldo,
            'saldo_tersedia' => $this->saldoTersedia(),
            'min_pencairan' => \App\Models\User::MIN_PENCAIRAN,
            'bisa_pencair' => $this->bisaPencair(),
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}