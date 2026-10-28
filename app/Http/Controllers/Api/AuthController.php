<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Support\AturanValidasi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Autentikasi untuk aplikasi seluler.
 *
 * Setiap klien memakai satu token dengan abilities kosong, supaya seluruh
 * endpoint lain tetap memutuskan sendiri role-nya lewat middleware 'role'.
 */
class AuthController extends Controller
{
    /** Nama perangkat yang akan ikut disimpan bersama token. */
    private const NAMA_PERANGKAT = 'setorjelantah-mobile';

    // POST /api/login
    public function login(Request $request): JsonResponse
    {
        $validasi = AturanValidasi::siap('masuk');
        $data = $request->validate($validasi['aturan'], $validasi['pesan']);

        $user = User::where('email', $data['email'])->first();

        // Pesan yang sama untuk email tidak terdaftar maupun sandi salah,
        // supaya aplikasi tidak bisa dipakai menebak email yang valid.
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json([
                'message' => 'Email atau kata sandi salah.',
                'errors' => ['email' => ['Email atau kata sandi salah.']],
            ], 401);
        }

        return $this->tokenUntuk($user, $request);
    }

    // POST /api/register
    public function register(Request $request): JsonResponse
    {
        $validasi = AturanValidasi::siap('daftar');
        $data = $request->validate($validasi['aturan'], $validasi['pesan']);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'], // di-cast 'hashed' oleh model
            'no_hp' => $data['no_hp'] ?? null,
            'alamat' => $data['alamat'] ?? null,
            'role' => 'user',
        ]);

        return $this->tokenUntuk($user, $request, 201);
    }

    // POST /api/logout
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Berhasil keluar.']);
    }

    // GET /api/me
    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    /**
     * Terbitkan token baru dan kembalikan profil beserta tokennya.
     *
     * Token lama dicabut dulu supaya satu akun hanya aktif di satu perangkat.
     */
    private function tokenUntuk(User $user, Request $request, int $status = 200): JsonResponse
    {
        $user->tokens()->where('name', self::NAMA_PERANGKAT)->delete();

        $token = $user->createToken(self::NAMA_PERANGKAT)->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => (new UserResource($user))->resolve($request),
        ], $status);
    }
}