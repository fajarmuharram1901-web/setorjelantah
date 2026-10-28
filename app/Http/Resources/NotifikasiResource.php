<?php

namespace App\Http\Resources;

use App\Notifications\SetoranDiambil;
use App\Notifications\SetoranDitolak;
use App\Notifications\SetoranSelesai;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Bentuk data notifikasi untuk aplikasi seluler.
 *
 * Kolom `target` memberi tahu aplikasi layar mana yang harus dibuka saat
 * notifikasi diklik. Kolom ini diisi di dalam notifikasi itu sendiri, bukan
 * ditebak dari URL, karena URL versi web tidak bisa dipetakan ke navigasi
 * aplikasi.
 */
class NotifikasiResource extends JsonResource
{
    /** Judul cadangan untuk tiap jenis notifikasi. */
    private const JUDUL = [
        SetoranDiambil::class => 'Setoran Sedang Diproses',
        SetoranDitolak::class => 'Pengajuan Ditolak',
        SetoranSelesai::class => 'Setoran Selesai',
    ];

    public function toArray(Request $request): array
    {
        $data = $this->data ?? [];

        return [
            'id' => $this->id,
            'type' => class_basename($this->type),
            'judul' => self::JUDUL[$this->type] ?? 'Notifikasi',
            'pesan' => $data['message'] ?? $data['pesan'] ?? '',
            'setoran_id' => $data['setoran_id'] ?? null,
            'target' => $data['target'] ?? null,
            'dibaca' => $this->read_at !== null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}