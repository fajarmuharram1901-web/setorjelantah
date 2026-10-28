# SetorJelantah — Versi Laravel

Ini adalah hasil konversi dari versi PHP native ke Laravel. File-file di sini adalah
bagian **khusus aplikasi** (migration, model, controller, route, view). Anda perlu
membuat project Laravel kosong dulu di komputer Anda sendiri (karena environment saya
tidak punya Composer/PHP), lalu menimpakan file-file ini ke dalamnya.

## 1. Buat project Laravel baru

Jalankan di terminal (bukan di sini — di komputer Anda yang sudah ada Composer & PHP):

```bash
composer create-project laravel/laravel setorjelantah
cd setorjelantah
composer require laravel/breeze --dev
php artisan breeze:install blade
```

Pilih opsi **Blade** saat ditanya stack Breeze (bukan React/Vue), lalu jawab "no" untuk
Dark mode & Pest kalau ditanya (opsional, terserah Anda).

## 2. Timpa / tambahkan file dari sini

Salin folder-folder berikut dari paket ini ke project Laravel Anda, **timpa** file yang
sudah ada dengan nama sama:

```
database/migrations/2026_01_01_000001_add_role_and_saldo_to_users_table.php   → tambahkan
database/migrations/2026_01_01_000002_create_harga_table.php                  → tambahkan
database/migrations/2026_01_01_000003_create_setoran_table.php                → tambahkan
database/seeders/DatabaseSeeder.php                                           → timpa
app/Models/User.php                                                          → timpa
app/Models/Harga.php                                                         → tambahkan
app/Models/Setoran.php                                                       → tambahkan
app/Http/Middleware/EnsureRole.php                                           → tambahkan
app/Http/Controllers/DashboardController.php                                 → tambahkan
app/Http/Controllers/HomeController.php                                      → tambahkan
app/Http/Controllers/SetoranController.php                                   → tambahkan
app/Http/Controllers/PengepulController.php                                  → tambahkan
app/Http/Controllers/AdminController.php                                     → tambahkan
app/Http/Controllers/Auth/RegisteredUserController.php                       → timpa
routes/web.php                                                               → timpa
resources/views/layouts/app.blade.php                                       → tambahkan
resources/views/partials/status-badge.blade.php                             → tambahkan
resources/views/welcome.blade.php                                           → timpa
resources/views/auth/login.blade.php                                        → timpa
resources/views/auth/register.blade.php                                     → timpa
resources/views/user/*.blade.php                                            → tambahkan
resources/views/pengepul/*.blade.php                                        → tambahkan
resources/views/admin/*.blade.php                                           → tambahkan
public/css/style.css                                                        → tambahkan
```

**Catatan soal `bootstrap/app.php.snippet`**: ini bukan file yang ditimpa langsung.
Buka `bootstrap/app.php` yang sudah ada di project Anda, cari bagian `->withMiddleware(...)`,
lalu tambahkan alias `role` seperti dicontohkan di file snippet tersebut:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => \App\Http\Middleware\EnsureRole::class,
    ]);
})
```

Kalau project Anda Laravel 10 ke bawah (tidak ada `bootstrap/app.php` gaya baru), daftarkan
lewat `app/Http/Kernel.php` di array `$middlewareAliases` sebagai gantinya.

## 3. Setup database

Edit `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=setorjelantah
DB_USERNAME=root
DB_PASSWORD=
```

Buat database kosong `setorjelantah` di phpMyAdmin, lalu jalankan:

```bash
php artisan migrate
php artisan db:seed
```

Ini otomatis membuat semua tabel + akun admin default.

## 4. Jalankan

```bash
php artisan serve
```

Buka `http://127.0.0.1:8000`.

## Akun default

- **Admin**: `admin@jelantah.local` / `admin123` — segera ganti setelah login pertama
  (belum ada halaman ganti password; bisa ditambah lewat `php artisan make:controller ProfileController` nanti).
- Akun **pengepul** dibuat lewat menu Kelola Pengguna setelah login sebagai admin.
- Akun **user** (rumah tangga) mendaftar sendiri lewat halaman Daftar.

## Kenapa tidak pakai Tailwind bawaan Breeze?

Breeze secara default scaffold pakai Tailwind (butuh `npm install && npm run build`).
Supaya tidak perlu setup Node/Vite tambahan dan tetap konsisten dengan desain
"putih bersih + tema minyak" yang sudah dibuat sebelumnya, file `login.blade.php` dan
`register.blade.php` di paket ini sudah ditulis ulang memakai layout & CSS sendiri
(`public/css/style.css`), bukan komponen Tailwind Breeze. Anda tetap dapat semua logic
auth (hashing password, session, validasi, remember me) dari Breeze — hanya tampilannya
yang diganti.

## Perbedaan struktur dari versi native PHP

| Native PHP | Laravel |
|---|---|
| `config/database.php` + helper `url()` | `.env` + `asset()`/`route()` otomatis benar di folder manapun |
| `includes/auth.php` (`requireRole()`) | Middleware `role:admin` / `role:pengepul` / `role:user` di route |
| File fisik per halaman (`user/dashboard.php`) | Controller + route bernama (`user.dashboard`) |
| Query manual (`$db->prepare(...)`) | Eloquent (`Setoran::where(...)->get()`) |
| `database.sql` diimpor manual | `php artisan migrate` (versioned, bisa rollback) |

## Pengembangan selanjutnya

Sama seperti versi native: tracking proses daur ulang ke biodiesel belum termasuk di sini,
tapi struktur migration (`setoran`, `harga`) sudah dibuat modular jadi tinggal tambah
migration baru (misal tabel `batch_produksi`) kalau sudah siap ke tahap itu.
