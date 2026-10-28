<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Bentuk data permintaan pencairan saldo untuk aplikasi seluler. */
class PencairanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kode' => $this->kode,
            'user_id' => $this->user_id,
            'jumlah' => (float) $this->jumlah,
            'metode' => $this->metode,
            'metode_label' => $this->labelMetode(),
            'tujuan' => $this->tujuan,
            'nama_penerima' => $this->nama_penerima,
            'status' => $this->status,
            'status_label' => match ($this->status) {
                'menunggu' => 'Menunggu persetujuan',
                'disetujui' => 'Disetujui',
                'ditolak' => 'Ditolak',
                default => ucfirst((string) $this->status),
            },
            'alasan_tolak' => $this->alasan_tolak,
            'diproses_oleh' => $this->diproses_oleh,
            'diproses_pada' => $this->diproses_pada?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),

            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'no_hp' => $this->user->no_hp,
            ]),
            'admin' => $this->whenLoaded('admin', fn () => $this->admin ? [
                'id' => $this->admin->id,
                'name' => $this->admin->name,
            ] : null),
        ];
    }
}