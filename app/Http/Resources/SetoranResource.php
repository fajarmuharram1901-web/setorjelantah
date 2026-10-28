<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Bentuk data setoran untuk aplikasi seluler.
 *
 * Berat hasil timbang dan nilai rupiah ikut dikirim apa adanya supaya
 * aplikasi bisa menampilkan rincian tanpa menghitung ulang.
 */
class SetoranResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kode' => 'STR-'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT),
            'user_id' => $this->user_id,
            'pengepul_id' => $this->pengepul_id,

            // Yang diisi warga saat mengajukan.
            'perkiraan_kg' => (float) $this->perkiraan_kg,
            'perkiraan_minyak_kg' => $this->perkiraanMinyakKg(),
            'perkiraan_endapan_kg' => $this->perkiraanEndapanKg(),
            'perkiraan_total_kg' => $this->perkiraanTotalKg(),

            // Yang diisi pengepul setelah ditimbang.
            'minyak_kg' => $this->minyak_kg === null ? null : (float) $this->minyak_kg,
            'endapan_kg' => $this->endapan_kg === null ? null : (float) $this->endapan_kg,
            'total_terima_kg' => $this->totalTerimaKg(),
            'selisih_kg' => $this->selisihKg(),

            'harga_minyak_saat_itu' => $this->harga_minyak_saat_itu === null ? null : (float) $this->harga_minyak_saat_itu,
            'harga_endapan_saat_itu' => $this->harga_endapan_saat_itu === null ? null : (float) $this->harga_endapan_saat_itu,
            'total_saldo' => $this->total_saldo === null ? null : (float) $this->total_saldo,

            'alamat_jemput' => $this->alamat_jemput,
            'catatan' => $this->catatan,
            'catatan_pengepul' => $this->catatan_pengepul,
            'bukti_foto_url' => $this->bukti_foto
                ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->bukti_foto)
                : null,

            'status' => $this->status,
            'status_label' => match ($this->status) {
                'menunggu' => 'Menunggu pengepul',
                'diproses' => 'Sedang diproses',
                'selesai' => 'Selesai',
                'dibatalkan' => 'Dibatalkan',
                default => ucfirst((string) $this->status),
            },

            'tanggal_ajukan' => $this->tanggal_ajukan?->toIso8601String(),
            'tanggal_selesai' => $this->tanggal_selesai?->toIso8601String(),

            // Ringkasan identitas untuk layar daftar, supaya aplikasi tidak
            // perlu permintaan terpisah hanya untuk menampilkan nama.
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'no_hp' => $this->user->no_hp,
                'alamat' => $this->user->alamat,
                'inisial' => $this->user->inisial(),
            ]),
            'pengepul' => $this->whenLoaded('pengepul', fn () => $this->pengepul ? [
                'id' => $this->pengepul->id,
                'name' => $this->pengepul->name,
                'no_hp' => $this->pengepul->no_hp,
            ] : null),
        ];
    }
}