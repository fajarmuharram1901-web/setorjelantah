# SetorJelantah

Aplikasi web sederhana untuk mengelola setoran minyak jelantah (used cooking oil) dari rumah tangga,
mirip konsep uCollect tapi **tanpa mesin otomatis** — verifikasi liter dilakukan manual oleh pengepul.
Versi ini fokus pada bagian **pengumpulan (collection)**; fitur tracking daur ulang ke biodiesel
bisa dikembangkan di tahap berikutnya.

## Role

- **User (rumah tangga)** — daftar sendiri, ajukan setoran (perkiraan liter + alamat), lihat saldo & riwayat.
- **Pengepul** — akun dibuat oleh admin, mengambil tugas yang menunggu, mengonfirmasi liter aktual hasil
  pengukuran di lokasi. Saat dikonfirmasi, saldo user otomatis bertambah (liter aktual × harga/liter berlaku).
- **Admin** — kelola akun pengepul & user, atur harga per liter, pantau seluruh transaksi.

## Instalasi (XAMPP / Laragon)

1. Salin folder `minyak-jelantah-app` ke `htdocs` (XAMPP) atau `www` (Laragon).
2. Buat database dengan mengimpor `database.sql` lewat phpMyAdmin, atau:
   ```
   mysql -u root -p < database.sql
   ```
3. Cek kredensial di `config/database.php` (default: host `localhost`, user `root`, password kosong).
4. Buka `http://localhost/minyak-jelantah-app/` di browser.

## Akun default

- **Admin**: `admin@jelantah.local` / `admin123` — **segera ganti password setelah login pertama**
  (belum ada halaman ganti password bawaan; ubah langsung lewat database sementara ini).
- Akun pengepul dibuat lewat menu **Kelola Pengguna** setelah login sebagai admin.
- Akun user (rumah tangga) mendaftar sendiri lewat halaman **Daftar**.

## Struktur Folder

```
minyak-jelantah-app/
├── config/database.php     # Koneksi PDO
├── includes/                # auth, functions, header, footer
├── assets/css/style.css
├── user/                    # dashboard, ajukan setor, riwayat
├── pengepul/                 # dashboard, verifikasi/konfirmasi liter
├── admin/                    # dashboard, kelola user, atur harga
├── index.php / login.php / register.php / logout.php
└── database.sql
```

## Alur Utama

1. User mengajukan setoran (perkiraan liter, alamat) → status `menunggu`.
2. Pengepul mengambil tugas → status `diproses`.
3. Pengepul datang ke lokasi, mengukur liter aktual secara manual, lalu mengonfirmasi di sistem →
   status `selesai`, saldo user bertambah otomatis (liter aktual × harga/liter yang berlaku saat itu).
4. Admin bisa memantau semua transaksi dan mengubah harga per liter kapan saja.

## Pengembangan Selanjutnya (belum termasuk di versi ini)

- Tracking proses daur ulang minyak jelantah menjadi biodiesel (batch produksi, hasil, dsb).
- Penarikan saldo (withdraw) oleh user.
- Upload foto bukti setoran.
- Notifikasi (email/WhatsApp) saat status berubah.
