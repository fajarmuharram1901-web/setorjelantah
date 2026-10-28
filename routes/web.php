<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PencairanController;
use App\Http\Controllers\PengepulController;
use App\Http\Controllers\PencarianController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ValidasiController;
use App\Http\Controllers\SetoranController;
use Illuminate\Support\Facades\Route;

// Landing page hanya untuk tamu. Kalau sudah login, middleware 'guest'
// mengirim pengguna ke route 'dashboard' yang lalu mengarahkan sesuai peran.
Route::get('/', [HomeController::class, 'index'])
    ->middleware('guest')
    ->name('home');

// Redirect setelah login ke dashboard sesuai role - gantikan redirectByRole()
Route::get('/dashboard', [DashboardController::class, 'redirect'])
    ->middleware('auth')
    ->name('dashboard');

// ---- Profil (semua role) ----
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ---- Pemeriksaan cepat saat mengetik (email sudah dipakai atau belum) ----
Route::middleware('throttle:30,1')
    ->get('/validasi/email', [ValidasiController::class, 'email'])
    ->name('validasi.email');
// ---- Pencarian global (search bar navbar) ----
Route::middleware('auth')
    ->get('/pencarian', [PencarianController::class, 'index'])
    ->name('pencarian');

// ---- Notifikasi (semua role) ----
Route::middleware('auth')->name('notifikasi.')->group(function () {
    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('index');
    Route::get('/notifikasi/count', [NotificationController::class, 'count'])->name('count');
    Route::post('/notifikasi/{id}/read', [NotificationController::class, 'read'])->name('read');
    Route::post('/notifikasi/read-all', [NotificationController::class, 'readAll'])->name('readAll');
});

// ---- Rumah tangga (role: user) ----
Route::middleware(['auth', 'role:user'])->prefix('user')->name('user.')->group(function () {
    Route::get('/dashboard', [SetoranController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard/data', [SetoranController::class, 'dashboardData'])->name('dashboard.data');
    Route::get('/ajukan', [SetoranController::class, 'create'])->name('ajukan');
    Route::post('/ajukan', [SetoranController::class, 'store'])->name('ajukan.store');
    Route::get('/riwayat', [SetoranController::class, 'riwayat'])->name('riwayat');
    Route::get('/setoran/{setoran}', [SetoranController::class, 'detail'])->name('detail');

    // Saldo & pencairan
    Route::get('/saldo', [PencairanController::class, 'saldo'])->name('saldo');
    Route::get('/pencairan', [PencairanController::class, 'create'])->name('pencairan.create');
    Route::post('/pencairan', [PencairanController::class, 'store'])->name('pencairan.store');
    Route::post('/pencairan/{pencairan}/batal', [PencairanController::class, 'batal'])->name('pencairan.batal');
});

// ---- Pengepul ----
Route::middleware(['auth', 'role:pengepul'])->prefix('pengepul')->name('pengepul.')->group(function () {
    Route::get('/dashboard', [PengepulController::class, 'dashboard'])->name('dashboard');
    Route::get('/menunggu', [PengepulController::class, 'menunggu'])->name('menunggu');
    Route::post('/ambil/{setoran}', [PengepulController::class, 'ambil'])->name('ambil');
    Route::post('/tolak/{setoran}', [PengepulController::class, 'tolak'])->name('tolak');
    Route::get('/verifikasi/{setoran}', [PengepulController::class, 'show'])->name('verifikasi');
    Route::post('/verifikasi/{setoran}', [PengepulController::class, 'konfirmasi'])->name('verifikasi.store');
    Route::get('/riwayat', [PengepulController::class, 'riwayat'])->name('riwayat');
});

// ---- Admin ----
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard/data', [AdminController::class, 'dashboardData'])->name('dashboard.data');
    Route::get('/pengguna', [AdminController::class, 'kelolaUser'])->name('pengguna');
    Route::post('/pengguna/pengepul', [AdminController::class, 'tambahPengepul'])->name('pengguna.pengepul');
    Route::get('/pengguna/{user}/edit', [AdminController::class, 'editUser'])->name('pengguna.edit');
    Route::put('/pengguna/{user}', [AdminController::class, 'updateUser'])->name('pengguna.update');
    Route::delete('/pengguna/{user}', [AdminController::class, 'hapusUser'])->name('pengguna.hapus');
    Route::get('/harga', [AdminController::class, 'harga'])->name('harga');
    Route::post('/harga', [AdminController::class, 'updateHarga'])->name('harga.update');
Route::post('/harga/kunci', [AdminController::class, 'toggleKunciHarga'])->name('harga.kunci');

    // Permintaan pencairan saldo
    Route::get('/pencairan', [AdminController::class, 'pencairan'])->name('pencairan');
    Route::post('/pencairan/{pencairan}/setujui', [AdminController::class, 'setujuiPencairan'])->name('pencairan.setujui');
    Route::post('/pencairan/{pencairan}/tolak', [AdminController::class, 'tolakPencairan'])->name('pencairan.tolak');
    Route::get('/saldo/{user}', [AdminController::class, 'detailSaldo'])->name('saldo');

    // Laporan & ekspor
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan');
    Route::get('/laporan/unduh', [LaporanController::class, 'unduh'])->name('laporan.unduh');
});

require __DIR__.'/auth.php'; // dari Breeze: login, register, logout, dsb.
