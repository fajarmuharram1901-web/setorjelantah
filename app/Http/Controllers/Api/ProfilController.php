<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Support\AturanValidasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Endpoint profil dan penggantian kata sandi, untuk semua role. */
class ProfilController extends Controller
{
    // GET /api/profil
    public function show(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    // PATCH /api/profil
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validasi = AturanValidasi::siap('profil', ['id' => $user->id]);
        $data = $request->validate($validasi['aturan'], $validasi['pesan']);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'no_hp' => $data['no_hp'] ?? null,
            'alamat' => $data['alamat'] ?? null,
        ]);

        return (new UserResource($user->fresh()))
            ->additional(['message' => 'Profil berhasil diperbarui.'])
            ->response();
    }

    // PUT /api/profil/sandi
    public function gantiSandi(Request $request): JsonResponse
    {
        $validasi = AturanValidasi::siap('gantiSandi');
        $data = $request->validate($validasi['aturan'], $validasi['pesan']);

        $request->user()->update(['password' => $data['password']]);

        return response()->json(['message' => 'Kata sandi berhasil diubah.']);
    }
}