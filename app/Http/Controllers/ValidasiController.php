<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Pemeriksaan cepat untuk aturan yang tidak bisa dipastikan di peramban,
 * terutama "email sudah dipakai atau belum".
 */
class ValidasiController extends Controller
{
    // GET /validasi/email?email=...&abaikan=ID
    public function email(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'abaikan' => ['nullable', 'integer'],
        ]);

        $sudahDipakai = User::where('email', $data['email'])
            ->when(
                $data['abaikan'] ?? null,
                fn ($q, $id) => $q->where('id', '!=', $id)
            )
            ->exists();

        return response()->json([
            'dipakai' => $sudahDipakai,
        ]);
    }
}
