<?php

namespace Database\Seeders;

use App\Models\Harga;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // User Admin 1 (local)
        User::firstOrCreate(
            ['email' => 'admin@jelantah.local'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('admin123'), // segera ganti setelah login pertama
                'role' => 'admin',
            ]
        );

        // User Admin 2 (jelantah.com) - Tambahan baru
        User::firstOrCreate(
            ['email' => 'admin@jelantah.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('admin123'), // password di-hash otomatis
                'role' => 'admin',
            ]
        );

        // Kolom `harga_per_liter` sudah di-rename menjadi `harga_minyak_kg`
        // saat satuan berubah dari liter ke kilogram, dan endapan jadi entitas
        // berbayar sendiri.
        if (Harga::count() === 0) {
            Harga::create([
                'harga_minyak_kg' => 5000,
                'harga_endapan_kg' => 5000,
            ]);
        }
    }
}