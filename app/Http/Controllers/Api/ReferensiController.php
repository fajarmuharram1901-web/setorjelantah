<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SetoranResource;
use App\Models\Harga;
use App\Models\Pengaturan;
use App\Models\Setoran;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endpoint bersama yang dipakai lebih dari satu role.
 *
 * Harga tidak memakai middleware 'auth' karena halaman pendaftaran perlu
 * menampilkan harga yang berlaku supaya warga bisa menghitung perkiraan
 * nilai setorannya sebelum akun dibuat.
 */
class ReferensiController extends Controller
{
    // GET /api/harga - publik
    public function harga(): JsonResponse
    {
        return response()->json([
            'harga' => [
                'minyak' => Harga::hargaMinyak(),
                'endapan' => Harga::hargaEndapan(),
            ],
            'maks_kg' => Harga::MAKS_KG,
            'min_pencairan' => User::MIN_PENCAIRAN,
        ]);
    }

    // GET /api/validasi/email?email= - publik, untuk cek cepat saat mengetik
    public function validasiEmail(Request $request): JsonResponse
    {
        $validasi = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid. Contoh: nama@email.com',
        ]);

        $dipakai = User::where('email', $validasi['email'])->exists();

        return response()->json([
            'email' => $validasi['email'],
            'tersedia' => ! $dipakai,
            'pesan' => $dipakai ? 'Email ini sudah terdaftar. Silakan masuk atau pakai email lain.' : null,
        ]);
    }

    // GET /api/pencarian?q= - pencarian global, semua role
    public function pencarian(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json(['q' => $q, 'setoran' => [], 'total' => 0]);
        }

        $user = $request->user();

        // Pengepul dan admin boleh melihat semua setoran, warga hanya miliknya.
        $setoran = Setoran::query()
            ->when($user->isUser(), fn ($b) => $b->where('user_id', $user->id))
            ->where(function ($inner) use ($q) {
                $inner->where('alamat_jemput', 'like', '%'.$q.'%')
                    ->orWhere('catatan', 'like', '%'.$q.'%');
            })
            ->latest('tanggal_ajukan')
            ->take(20)
            ->get();

        $daftar = SetoranResource::collection($setoran->load(['user', 'pengepul']));

        return response()->json([
            'q' => $q,
            'total' => $daftar->count(),
            'data' => $daftar->resolve($request),
        ]);
    }

    // GET /api/pengaturan - publik, untuk aturan main yang dipakai bersama
    public function pengaturan(): JsonResponse
    {
        return response()->json([
            'min_pencairan' => User::MIN_PENCAIRAN,
            'maks_kg' => Harga::MAKS_KG,
            'jeda_harga_hari' => Harga::JEDA_HARGA_HARI,
            'harga_terkunci' => Pengaturan::hargaTerkunci(),
        ]);
    }
}