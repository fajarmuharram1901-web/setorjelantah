<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SetoranResource;
use App\Models\Harga;
use App\Models\Setoran;
use App\Support\ApiData;
use App\Support\AturanValidasi;
use App\Support\Statistik;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Endpoint untuk rumah tangga: dashboard, pengajuan setoran, dan riwayat. */
class SetoranController extends Controller
{
    // GET /api/user/dashboard
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $setoran = $user->setoran();
        $selesai = (clone $setoran)->selesai();

        $minyak = (float) (clone $selesai)->sum('minyak_kg');
        $endapan = (float) (clone $selesai)->sum('endapan_kg');

        return response()->json([
            'ringkasan' => [
                'menunggu' => (clone $setoran)->where('status', 'menunggu')->count(),
                'diproses' => (clone $setoran)->where('status', 'diproses')->count(),
                'selesai' => (clone $selesai)->count(),
                'total_minyak_kg' => round($minyak, 2),
                'total_endapan_kg' => round($endapan, 2),
                'total_kg' => round($minyak + $endapan, 2),
                'total_nilai' => (float) (clone $selesai)->sum('total_saldo'),
                'saldo_tersedia' => $user->saldoTersedia(),
                'bisa_pencair' => $user->bisaPencair(),
                'menunggu_pencairan' => $user->pencairans()->where('status', 'menunggu')->count(),
            ],
            'harga' => [
                'minyak' => Harga::hargaMinyak(),
                'endapan' => Harga::hargaEndapan(),
            ],
            'deret' => Statistik::deretHarian(10),
            'setoran_terbaru' => ApiData::polos(SetoranResource::collection(
                (clone $setoran)->with('pengepul')->latest('tanggal_ajukan')->take(5)->get()
            )),
        ]);
    }

    // GET /api/user/ippet - data yang dibutuhkan form pengajuan
    public function formAjukan(Request $request): JsonResponse
    {
        return response()->json([
            'harga_minyak' => Harga::hargaMinyak(),
            'harga_endapan' => Harga::hargaEndapan(),
            'maks_kg' => Harga::MAKS_KG,
            'alamat_default' => $request->user()->alamat,
        ]);
    }

    // POST /api/user/ajukan
    public function store(Request $request): JsonResponse
    {
        $validasi = AturanValidasi::siap('ajukanSetoran');
        $data = $request->validate($validasi['aturan'], $validasi['pesan']);

        $minyak = (float) $data['perkiraan_minyak_kg'];
        $endapan = (float) $data['perkiraan_endapan_kg'];
        $total = round($minyak + $endapan, 2);

        if ($total <= 0) {
            return response()->json([
                'message' => 'Isi perkiraan minyak atau endapan, jangan keduanya 0.',
                'errors' => ['perkiraan_minyak_kg' => ['Isi perkiraan minyak atau endapan, jangan keduanya 0.']],
            ], 422);
        }

        if ($total > Harga::MAKS_KG) {
            $pesan = 'Total perkiraan minyak dan endapan maksimal '
                .number_format(Harga::MAKS_KG, 0, ',', '.').' kg per pengajuan.';

            return response()->json([
                'message' => $pesan,
                'errors' => ['perkiraan_minyak_kg' => [$pesan]],
            ], 422);
        }

        $setoran = $request->user()->setoran()->create([
            'perkiraan_kg' => $total,
            'perkiraan_minyak_kg' => $minyak,
            'perkiraan_endapan_kg' => $endapan,
            'alamat_jemput' => $data['alamat_jemput'],
            'catatan' => $data['catatan'] ?? null,
            'tanggal_ajukan' => now(),
        ]);

        // `fresh()` dipakai karena status dan tanggal_selesai diisi oleh database
        // sebagai nilai bawaan, jadi belum ada di instance yang di-memory.
        return (new SetoranResource($setoran->fresh()->load('pengepul')))
            ->additional(['message' => 'Pengajuan setoran berhasil dikirim. Menunggu pengepul memproses.'])
            ->response()
            ->setStatusCode(201);
    }

    // GET /api/user/riwayat
    public function riwayat(Request $request)
    {
        $filter = $this->filterRiwayat($request);

        $daftar = $request->user()->setoran()
            ->with('pengepul')
            ->when($filter['status'], fn ($q) => $q->where('status', $filter['status']))
            ->when($filter['cari'], function ($q) use ($filter) {
                $q->where(function ($inner) use ($filter) {
                    $inner->where('alamat_jemput', 'like', '%'.$filter['cari'].'%')
                        ->orWhere('catatan', 'like', '%'.$filter['cari'].'%');
                });
            })
            ->when($filter['dari'], fn ($q) => $q->whereDate('tanggal_ajukan', '>=', $filter['dari']))
            ->when($filter['sampai'], fn ($q) => $q->whereDate('tanggal_ajukan', '<=', $filter['sampai']))
            ->latest('tanggal_ajukan')
            ->paginate($request->integer('per_page', 10));

        return SetoranResource::collection($daftar);
    }

    // GET /api/user/setoran/{setoran}
    public function detail(Request $request, Setoran $setoran): SetoranResource|JsonResponse
    {
        abort_unless($setoran->user_id === $request->user()->id, 403);

        return new SetoranResource($setoran->load('pengepul'));
    }

    /**
     * Normalisasi input filter riwayat.
     *
     * @return array<string, mixed>
     */
    private function filterRiwayat(Request $request): array
    {
        $status = $request->query('status');

        return [
            'status' => in_array($status, ['menunggu', 'diproses', 'selesai', 'dibatalkan'], true) ? $status : null,
            'cari' => trim((string) $request->query('cari', '')) ?: null,
            'dari' => $this->tanggalValid($request->query('dari')),
            'sampai' => $this->tanggalValid($request->query('sampai')),
        ];
    }

    private function tanggalValid(?string $nilai): ?string
    {
        if (! is_string($nilai) || $nilai === '') {
            return null;
        }

        return date('Y-m-d', strtotime($nilai)) ?: null;
    }
}