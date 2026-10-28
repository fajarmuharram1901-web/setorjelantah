<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PencairanResource;
use App\Http\Resources\TransaksiResource;
use App\Models\Harga;
use App\Models\Pencairan;
use App\Models\User;
use App\Support\ApiData;
use App\Support\AturanValidasi;
use App\Support\Statistik;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Endpoint saldo dan pencairan untuk rumah tangga. */
class SaldoController extends Controller
{
    // GET /api/user/saldo
    public function saldo(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'saldo' => (float) $user->saldo,
            'saldo_tersedia' => $user->saldoTersedia(),
            'min_pencairan' => User::MIN_PENCAIRAN,
            'bisa_pencair' => $user->bisaPencair(),
            'total_masuk' => (float) $user->transaksi()->where('tipe', 'setoran')->sum('jumlah'),
            // Nilai mutasi keluar tersimpan negatif, jadi dipakai nilai mutlak supaya
// field "total_keluar" bisa langsung ditampilkan tanpa tanda minus.
'total_keluar' => abs((float) $user->transaksi()->where('tipe', 'pencairan')->sum('jumlah')),
            'harga' => [
                'minyak' => Harga::hargaMinyak(),
                'endapan' => Harga::hargaEndapan(),
            ],
            'deret' => Statistik::deretHarian(10),
            'transaksi' => ApiData::of(
                TransaksiResource::collection($user->transaksi()->latest('id')->paginate($request->integer('per_page', 12)))
            ),
            'pencairan' => ApiData::of(
                PencairanResource::collection($user->pencairans()->latest('id')->paginate($request->integer('per_page', 8)))
            ),
        ]);
    }

    // POST /api/user/pencairan
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $saldoTersedia = $user->saldoTersedia();

        $validasi = AturanValidasi::siap('pencairan', ['maksSaldo' => (int) floor($saldoTersedia)]);
        $data = $request->validate($validasi['aturan'], $validasi['pesan']);

        $pencairan = Pencairan::create([
            'user_id' => $user->id,
            'jumlah' => $data['jumlah'],
            'metode' => $data['metode'],
            'tujuan' => $data['tujuan'],
            'nama_penerima' => $data['nama_penerima'],
            'status' => 'menunggu',
        ]);

        return (new PencairanResource($pencairan->load('user')))
            ->additional(['message' => 'Permintaan pencairan '.$pencairan->kode.' terkirim. Menunggu persetujuan admin.'])
            ->response()
            ->setStatusCode(201);
    }

    // POST /api/user/pencairan/{pencairan}/batal
    public function batal(Request $request, Pencairan $pencairan): JsonResponse
    {
        abort_unless($pencairan->user_id === $request->user()->id, 403);
        abort_unless($pencairan->status === 'menunggu', 422, 'Permintaan ini sudah diproses admin.');

        $pencairan->update([
            'status' => 'ditolak',
            'alasan_tolak' => 'Dibatalkan sendiri oleh pemohon.',
        ]);

        return response()->json([
            'message' => 'Permintaan pencairan dibatalkan.',
            'data' => (new PencairanResource($pencairan->fresh()))->resolve($request),
        ]);
    }
}