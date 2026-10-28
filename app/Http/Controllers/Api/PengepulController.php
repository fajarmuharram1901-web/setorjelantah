<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SetoranResource;
use App\Models\Harga;
use App\Models\Setoran;
use App\Support\AturanValidasi;
use App\Support\ApiData;
use App\Support\Background;
use App\Support\Saldo;
use App\Support\Statistik;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Endpoint untuk pengepul: mengambil tugas, menolak, dan verifikasi timbang. */
class PengepulController extends Controller
{
    // GET /api/pengepul/dashboard
    public function dashboard(Request $request): JsonResponse
    {
        $pengepul = $request->user();
        $id = $pengepul->id;
        $selesai = Setoran::query()->selesai()->where('pengepul_id', $id);

        return response()->json([
            'ringkasan' => [
                'jumlah_selesai' => (clone $selesai)->count(),
                'total_minyak_kg' => (float) (clone $selesai)->sum('minyak_kg'),
                'total_endapan_kg' => (float) (clone $selesai)->sum('endapan_kg'),
                'total_kg' => (float) (clone $selesai)->sum('minyak_kg') + (float) (clone $selesai)->sum('endapan_kg'),
                'total_nilai' => (float) (clone $selesai)->sum('total_saldo'),
                'jumlah_menunggu' => Setoran::menunggu()->count(),
                'jumlah_diproses' => Setoran::diproses()->where('pengepul_id', $id)->count(),
            ],
            'harga' => [
                'minyak' => Harga::hargaMinyak(),
                'endapan' => Harga::hargaEndapan(),
            ],
            'maks_kg' => Harga::MAKS_KG,
            'deret' => Statistik::deretHarian(10),
            'menunggu' => ApiData::polos(SetoranResource::collection(
                Setoran::with('user')->menunggu()->oldest('tanggal_ajukan')->get()
            )),
            'diproses' => ApiData::polos(SetoranResource::collection(
                Setoran::with('user')->diproses()->where('pengepul_id', $id)->oldest('tanggal_ajukan')->get()
            )),
            'selesai_terbaru' => ApiData::polos(SetoranResource::collection(
                (clone $selesai)->with('user')->latest('tanggal_selesai')->take(5)->get()
            )),
        ]);
    }

    // GET /api/pengepul/menunggu - hanya antrean, untuk polling ringan
    public function menunggu(): JsonResponse
    {
        $daftar = Setoran::with('user')->menunggu()->oldest('tanggal_ajukan')->get();

        return response()->json([
            'jumlah' => $daftar->count(),
            'data' => SetoranResource::collection($daftar),
        ]);
    }

    // POST /api/pengepul/ambil/{setoran}
    public function ambil(Request $request, Setoran $setoran): JsonResponse
    {
        if ($setoran->status !== 'menunggu') {
            return response()->json(['message' => 'Tugas ini sudah diambil pengepul lain.'], 422);
        }

        $setoran->update([
            'status' => 'diproses',
            'pengepul_id' => $request->user()->id,
        ]);

        $setoran->load('user');

        Background::artisan([
            'notif:send',
            (string) $setoran->user_id,
            (string) $setoran->id,
            'ambil',
        ]);

        return (new SetoranResource($setoran->fresh()->load(['user', 'pengepul'])))
            ->additional(['message' => 'Tugas berhasil diambil. Silakan konfirmasi setelah mengecek di lokasi.'])
            ->response();
    }

    // POST /api/pengepul/tolak/{setoran}
    public function tolak(Request $request, Setoran $setoran): JsonResponse
    {
        if ($setoran->status !== 'menunggu') {
            return response()->json(['message' => 'Pengajuan ini sudah diproses.'], 422);
        }

        $setoran->update(['status' => 'dibatalkan']);

        Background::artisan([
            'notif:send',
            (string) $setoran->user_id,
            (string) $setoran->id,
            'tolak',
        ]);

        return (new SetoranResource($setoran->fresh()))
            ->additional(['message' => 'Pengajuan ditolak. Warga telah diberi tahu melalui notifikasi.'])
            ->response();
    }

    // GET /api/pengepul/verifikasi/{setoran}
    public function show(Request $request, Setoran $setoran): JsonResponse
    {
        // Tugas yang sudah pindah ke pengepul lain sengaja disembunyikan 404,
        // bukan 403, supaya pengepul tidak bisa menebak keberadaan tugas itu.
        abort_unless(
            $setoran->pengepul_id === $request->user()->id && $setoran->status === 'diproses',
            404,
            'Data setoran tidak ditemukan atau sudah diproses pengepul lain.'
        );

        return response()->json([
            'setoran' => (new SetoranResource($setoran->load('user')))->resolve($request),
            'harga' => [
                'minyak' => Harga::hargaMinyak(),
                'endapan' => Harga::hargaEndapan(),
            ],
            'maks_kg' => Harga::MAKS_KG,
        ]);
    }

    // POST /api/pengepul/verifikasi/{setoran}
    public function konfirmasi(Request $request, Setoran $setoran): JsonResponse
    {
        abort_unless(
            $setoran->pengepul_id === $request->user()->id && $setoran->status === 'diproses',
            404,
            'Data setoran tidak ditemukan atau sudah diproses pengepul lain.'
        );

        $validasi = AturanValidasi::siap('verifikasiSetoran');
        $data = $request->validate($validasi['aturan'], $validasi['pesan']);

        $minyak = (float) $data['minyak_kg'];
        $endapan = (float) $data['endapan_kg'];

        if ($minyak + $endapan <= 0) {
            return response()->json([
                'message' => 'Total minyak dan endapan tidak boleh nol.',
                'errors' => ['minyak_kg' => ['Total minyak dan endapan tidak boleh nol.']],
            ], 422);
        }

        if ($minyak + $endapan > Harga::MAKS_KG) {
            $pesan = 'Total berat minyak dan endapan maksimal '.Harga::MAKS_KG.' kg.';

            return response()->json([
                'message' => $pesan,
                'errors' => ['minyak_kg' => [$pesan]],
            ], 422);
        }

        $hargaMinyak = Harga::hargaMinyak();
        $hargaEndapan = Harga::hargaEndapan();
        $hitungan = Setoran::hitungNilai($minyak, $endapan, $hargaMinyak, $hargaEndapan);

        $pathBukti = $request->file('bukti_foto')?->store('bukti', 'public');

        DB::transaction(function () use ($setoran, $data, $hitungan, $hargaMinyak, $hargaEndapan, $minyak, $endapan, $pathBukti) {
            $setoran->update([
                'minyak_kg' => $minyak,
                'endapan_kg' => $endapan,
                'catatan_pengepul' => $data['catatan_pengepul'] ?? null,
                'bukti_foto' => $pathBukti,
                'harga_minyak_saat_itu' => $hargaMinyak,
                'harga_endapan_saat_itu' => $hargaEndapan,
                'total_saldo' => $hitungan['nilai'],
                'status' => 'selesai',
                'tanggal_selesai' => now(),
            ]);

            $rincian = sprintf(
                'Setoran #%d - minyak %s kg x Rp%s + endapan %s kg x Rp%s',
                $setoran->id,
                rtrim(rtrim(number_format($minyak, 2, ',', '.'), '0'), ','),
                number_format($hargaMinyak, 0, ',', '.'),
                rtrim(rtrim(number_format($endapan, 2, ',', '.'), '0'), ','),
                number_format($hargaEndapan, 0, ',', '.')
            );

            Saldo::kredit(
                user: $setoran->user,
                jumlah: $hitungan['nilai'],
                tipe: 'setoran',
                keterangan: $rincian,
                referensiId: (int) $setoran->id,
                referensiTipe: 'setoran',
            );
        });

        Background::artisan([
            'notif:send',
            (string) $setoran->user_id,
            (string) $setoran->id,
            'selesai',
        ]);

        return (new SetoranResource($setoran->fresh()->load('user')))
            ->additional([
                'message' => 'Setoran dikonfirmasi. Saldo warga bertambah Rp'
                    .number_format($hitungan['nilai'], 0, ',', '.').'.',
            ])
            ->response();
    }

    // GET /api/pengepul/riwayat
    public function riwayat(Request $request)
    {
        $status = $request->query('status');
        $status = in_array($status, ['menunggu', 'diproses', 'selesai', 'dibatalkan'], true) ? $status : null;

        $tugas = $request->user()->tugasPengepul()
            ->with('user')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest('tanggal_ajukan')
            ->paginate($request->integer('per_page', 10));

        return SetoranResource::collection($tugas);
    }
}