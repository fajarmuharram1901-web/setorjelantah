<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Bentuk data mutasi buku besar saldo. */
class TransaksiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'tipe' => $this->tipe,
            'tipe_label' => $this->labelTipe(),
            'jumlah' => (float) $this->jumlah,
            'masuk' => $this->masuk(),
            'saldo_sebelum' => (float) $this->saldo_sebelum,
            'saldo_sesudah' => (float) $this->saldo_sesudah,
            'referensi_tipe' => $this->referensi_tipe,
            'referensi_id' => $this->referensi_id,
            'keterangan' => $this->keterangan,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}