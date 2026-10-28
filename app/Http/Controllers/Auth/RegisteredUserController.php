<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AturanValidasi;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Ganti file bawaan Breeze di app/Http/Controllers/Auth/RegisteredUserController.php
     * dengan file ini. Hanya role 'user' (rumah tangga) yang bisa daftar sendiri;
     * akun pengepul dibuat lewat AdminController::tambahPengepul().
     */
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        // Aturan dan pesan diambil dari AturanValidasi supaya pesan di layar
        // (validasi peramban) sama dengan pesan dari server.
        $validasi = AturanValidasi::siap('daftar');

        $data = $request->validate($validasi['aturan'], $validasi['pesan']);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'no_hp' => $data['no_hp'] ?? null,
            'alamat' => $data['alamat'] ?? null,
            'role' => 'user',
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
