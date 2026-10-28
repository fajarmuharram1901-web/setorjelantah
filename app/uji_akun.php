<?php

// Skrip sekali pakai untuk menyiapkan akun uji saat mengembangkan API dan
// aplikasi Flutter.
//
//   php app/uji_akun.php
//
// Hapus berkas ini sebelum dipakai di server sungguhan.
//
// Kenapa ada dua akun untuk tiap role: Sanctum mencabut token lama setiap kali
// ada login baru, jadi dua berkas uji yang memakai akun sama akan saling
// membatalkan token bila `flutter test` dijalankan sekaligus. Dua akun
// membuat berkas-berkas uji bisa berjalan berdampingan.

use App\Models\User;
use Illuminate\Support\Facades\Hash;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Skrip ini menambahkan akun uji bila belum ada, lalu menyetel ulang sandinya.
// Aman dijalankan berulang kali.
$sandi = 'rahasia123';

/** @var array<int, array{email: string, role: string}> */
$akun = [
    ['email' => 'pengepul1@gmail.com', 'role' => 'pengepul'],
    ['email' => 'ujipengepul@contoh.id', 'role' => 'pengepul'],
    ['email' => 'admin@jelantah.local', 'role' => 'admin'],
    ['email' => 'ujiadmin@contoh.id', 'role' => 'admin'],
];

foreach ($akun as $baris) {
    $user = User::where('email', $baris['email'])->first();

    if (! $user) {
        $user = User::create([
            'name' => 'Uji '.ucfirst($baris['role']),
            'email' => $baris['email'],
            'password' => $sandi,
            'role' => $baris['role'],
        ]);

        echo "DIBUAT: {$user->name} <{$user->email}>\n";

        continue;
    }

    $user->update(['password' => $sandi]);

    echo "SIAP: {$user->name} <{$user->email}> role={$user->role}\n";
}