<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\NotifikasiController;
use App\Http\Controllers\Api\PengepulController;
use App\Http\Controllers\Api\ProfilController;
use App\Http\Controllers\Api\ReferensiController;
use App\Http\Controllers\Api\SaldoController;
use App\Http\Controllers\Api\SetoranController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rute API untuk aplikasi seluler SetorJelantah
|--------------------------------------------------------------------------
|
| Seluruh rute memakai token Sanctum. Role dicek lewat middleware 'role'
| yang sama dengan versi web, jadi aturan aksesnya tidak terduplikasi.
| Prefix '/api' ditambahkan otomatis oleh Laravel.
|
*/

// ---- Publik ----
Route::post('login', [AuthController::class, 'login'])->name('api.login');
Route::post('register', [AuthController::class, 'register'])->name('api.register');

Route::get('harga', [ReferensiController::class, 'harga'])->name('api.harga');
Route::get('pengaturan', [ReferensiController::class, 'pengaturan'])->name('api.pengaturan');
Route::get('validasi/email', [ReferensiController::class, 'validasiEmail'])
    ->middleware('throttle:30,1')
    ->name('api.validasi.email');

// ---- Terlindungi token ----
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('me', [AuthController::class, 'me'])->name('api.me');

    // Profil (semua role)
    Route::get('profil', [ProfilController::class, 'show'])->name('api.profil.show');
    Route::patch('profil', [ProfilController::class, 'update'])->name('api.profil.update');
    Route::put('profil/sandi', [ProfilController::class, 'gantiSandi'])->name('api.profil.sandi');

    // Notifikasi (semua role)
    Route::prefix('notifikasi')->name('api.notifikasi.')->group(function () {
        Route::get('/', [NotifikasiController::class, 'index'])->name('index');
        Route::get('count', [NotifikasiController::class, 'count'])->name('count');
        Route::post('{id}/read', [NotifikasiController::class, 'read'])->name('read');
        Route::post('read-all', [NotifikasiController::class, 'readAll'])->name('readAll');
    });

    // Pencarian global (semua role)
    Route::get('pencarian', [ReferensiController::class, 'pencarian'])->name('api.pencarian');

    // ---- Rumah tangga ----
    Route::middleware('role:user')->prefix('user')->name('api.user.')->group(function () {
        Route::get('dashboard', [SetoranController::class, 'dashboard'])->name('dashboard');
        Route::get('formin', [SetoranController::class, 'formAjukan'])->name('formin');
        Route::post('ajukan', [SetoranController::class, 'store'])->name('ajukan');
        Route::get('riwayat', [SetoranController::class, 'riwayat'])->name('riwayat');
        Route::get('setoran/{setoran}', [SetoranController::class, 'detail'])->name('detail');

        Route::get('saldo', [SaldoController::class, 'saldo'])->name('saldo');
        Route::post('pencairan', [SaldoController::class, 'store'])->name('pencairan.store');
        Route::post('pencairan/{pencairan}/batal', [SaldoController::class, 'batal'])->name('pencairan.batal');
    });

    // ---- Pengepul ----
    Route::middleware('role:pengepul')->prefix('pengepul')->name('api.pengepul.')->group(function () {
        Route::get('dashboard', [PengepulController::class, 'dashboard'])->name('dashboard');
        Route::get('menunggu', [PengepulController::class, 'menunggu'])->name('menunggu');
        Route::post('ambil/{setoran}', [PengepulController::class, 'ambil'])->name('ambil');
        Route::post('tolak/{setoran}', [PengepulController::class, 'tolak'])->name('tolak');
        Route::get('verifikasi/{setoran}', [PengepulController::class, 'show'])->name('verifikasi');
        Route::post('verifikasi/{setoran}', [PengepulController::class, 'konfirmasi'])->name('verifikasi.store');
        Route::get('riwayat', [PengepulController::class, 'riwayat'])->name('riwayat');
    });

    // ---- Admin ----
    Route::middleware('role:admin')->prefix('admin')->name('api.admin.')->group(function () {
        Route::get('dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        Route::get('pengguna', [AdminController::class, 'pengguna'])->name('pengguna');
        Route::post('pengguna/pengepul', [AdminController::class, 'tambahPengepul'])->name('pengguna.pengepul');
        Route::put('pengguna/{user}', [AdminController::class, 'updatePengguna'])->name('pengguna.update');
        Route::delete('pengguna/{user}', [AdminController::class, 'hapusPengguna'])->name('pengguna.hapus');

        Route::get('harga', [AdminController::class, 'harga'])->name('harga');
        Route::post('harga', [AdminController::class, 'updateHarga'])->name('harga.update');
        Route::post('harga/kunci', [AdminController::class, 'toggleKunciHarga'])->name('harga.kunci');

        Route::get('pencairan', [AdminController::class, 'pencairan'])->name('pencairan');
        Route::post('pencairan/{pencairan}/setujui', [AdminController::class, 'setujuiPencairan'])->name('pencairan.setujui');
        Route::post('pencairan/{pencairan}/tolak', [AdminController::class, 'tolakPencairan'])->name('pencairan.tolak');

        Route::get('saldo/{user}', [AdminController::class, 'detailSaldo'])->name('saldo');
        Route::get('laporan', [AdminController::class, 'laporan'])->name('laporan');
    });
});