# API untuk Aplikasi Seluler

Dokumen ini menjelaskan endpoint JSON yang dipakai aplikasi Flutter di
`C:\Users\rinaa\Documents\setorjelantah_flutterv`. Semua rute berada di bawah
prefix `/api` dan didefinisikan di `routes/api.php`.

## Autentikasi

Seluruh endpoint terkunci memakai token **Laravel Sanctum**. Kirim header:

```
Accept: application/json
Authorization: Bearer <token>
```

| Metode | Rute                        | Keterangan                                |
| ------ | --------------------------- | ----------------------------------------- |
| POST   | `/api/login`                | Masuk, mengembalikan `{token, user}`      |
| POST   | `/api/register`             | Daftar warga baru, langsung mendapat token |
| POST   | `/api/logout`               | Cabut token yang sedang dipakai            |
| GET    | `/api/me`                   | Profil pengguna yang sedang masuk         |

`register` memerlukan `password_confirmation`, karena aturan validasi memakai
`confirmed` — sama persis dengan form versi web.

Contoh:

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"warga@contoh.id","password":"rahasia123"}'
```

## Publik

| Metode | Rute                  | Keterangan                                       |
| ------ | --------------------- | ------------------------------------------------ |
| GET    | `/api/harga`          | Harga minyak dan endapan per kg                  |
| GET    | `/api/pengaturan`     | Minimum pencairan, batas kg, aturan jeda harga     |
| GET    | `/api/validasi/email` | Cek cepat apakah email sudah dipakai              |

## Profil dan notifikasi (semua role)

| Metode | Rute                              | Keterangan                    |
| ------ | --------------------------------- | ----------------------------- |
| GET    | `/api/profil`                     | Ambil data diri               |
| PATCH  | `/api/profil`                     | Ubah nama, email, HP, alamat  |
| PUT    | `/api/profil/sandi`               | Ganti kata sandi              |
| GET    | `/api/notifikasi`                 | Daftar notifikasi             |
| GET    | `/api/notifikasi/count`           | Jumlah belum dibaca           |
| POST   | `/api/notifikasi/{id}/read`       | Tandai satu sudah dibaca      |
| POST   | `/api/notifikasi/read-all`        | Tandai semua sudah dibaca     |
| GET    | `/api/pencarian?q=`               | Pencarian global              |

## Rumah tangga (`role:user`)

| Metode | Rute                                | Keterangan                       |
| ------ | ----------------------------------- | -------------------------------- |
| GET    | `/api/user/dashboard`               | Saldo, statistik, tren, terbaru  |
| GET    | `/api/user/formin`                  | Harga dan batas untuk form       |
| POST   | `/api/user/ajukan`                  | Kirim pengajuan setoran          |
| GET    | `/api/user/riwayat`                 | Riwayat + penyaring              |
| GET    | `/api/user/setoran/{id}`            | Detail setoran                   |
| GET    | `/api/user/saldo`                   | Saldo, mutasi, pencairan         |
| POST   | `/api/user/pencairan`               | Ajukan pencairan                 |
| POST   | `/api/user/pencairan/{id}/batal`    | Batalkan yang masih menunggu     |

## Pengepul (`role:pengepul`)

| Metode | Rute                               | Keterangan                        |
| ------ | ---------------------------------- | --------------------------------- |
| GET    | `/api/pengepul/dashboard`          | Antrean, tugas aktif, statistik   |
| GET    | `/api/pengepul/menunggu`           | Antrean saja, untuk polling       |
| POST   | `/api/pengepul/ambil/{id}`         | Ambil tugas                       |
| POST   | `/api/pengepul/tolak/{id}`         | Tolak pengajuan                   |
| GET    | `/api/pengepul/verifikasi/{id}`    | Data tugas yang akan ditimbang   |
| POST   | `/api/pengepul/verifikasi/{id}`    | Kirim hasil timbang (+ foto)      |
| GET    | `/api/pengepul/riwayat`            | Riwayat tugas                    |

Endpoint verifikasi menerima `multipart/form-data` bila foto bukti dikirim:

```
minyak_kg, endapan_kg, catatan_pengepul, bukti_foto (opsional)
```

## Admin (`role:admin`)

| Metode | Rute                                    | Keterangan               |
| ------ | --------------------------------------- | ------------------------ |
| GET    | `/api/admin/dashboard`                  | Ringkasan operasional    |
| GET    | `/api/admin/pengguna`                   | Daftar pengguna          |
| POST   | `/api/admin/pengguna/pengepul`          | Buat akun pengepul       |
| PUT    | `/api/admin/pengguna/{id}`              | Ubah data/peran          |
| DELETE | `/api/admin/pengguna/{id}`              | Hapus akun               |
| GET    | `/api/admin/harga`                      | Harga + status jeda      |
| POST   | `/api/admin/harga`                      | Ubah harga               |
| POST   | `/api/admin/harga/kunci`                | Nyalakan/matikan jeda    |
| GET    | `/api/admin/pencairan`                  | Permintaan pencairan     |
| POST   | `/api/admin/pencairan/{id}/setujui`     | Setujui, potong saldo    |
| POST   | `/api/admin/pencairan/{id}/tolak`       | Tolak dengan alasan      |
| GET    | `/api/admin/saldo/{id}`                 | Mutasi saldo satu warga  |
| GET    | `/api/admin/laporan`                    | Laporan periodik         |

---

## Bentuk respons

### Daftar berpaginasi

Resource yang dikembalikan langsung memakai bentuk bawaan Laravel:

```json
{
  "data": [ ... ],
  "links": { ... },
  "meta": { "current_page": 1, "total": 12, "last_page": 2 }
}
```

### Daftar tanpa paginasi

Antrean pengepul dan daftar grafik dikirim sebagai daftar polos, atau
`{data, meta}` kalau memang perlu. Semuanya memakai kunci `data` supaya sisi
klien selalu tahu di mana letaknya daftar.

### Galat validasi

Bentuknya sama dengan Laravel, sehingga aplikasi bisa menandai input yang salah:

```json
{
  "message": "Nama lengkap wajib diisi. (and 2 more errors)",
  "errors": {
    "perkiraan_minyak_kg": ["Isi perkiraan minyak atau endapan, jangan keduanya 0."],
    "alamat_jemput": ["Alamat penjemputan wajib diisi."]
  }
}
```

### Kode status

| Kode | Arti                                                    |
| ---- | ------------------------------------------------------- |
| 401  | Token hilang, salah, atau kedaluwarsa                   |
| 403  | Akun tidak punya akses ke endpoint tersebut              |
| 404  | Data tidak ada, atau bukan milik pengguna yang meminta   |
| 422  | Validasi gagal, `errors` berisi pesan per kolom          |
| 429  | Terlalu banyak permintaan (endpoint validasi email)      |

## Catatan tambahan

- **Satu perangkat satu token.** Setiap kali pengguna berhasil masuk, token
  lama dengan nama perangkat yang sama dicabut.
- **Kolom `no_hp` bisa berupa angka.** Kolomnya pernah diubah ke `bigint`,
  jadi sisi klien harus siap menerima angka maupun teks.
- **Saldo tersedia berbeda dari saldo tercatat.** Saldo yang tercatat sudah
  dipotong begitu pencairan disetujui, sedangkan `saldo_tersedia` juga
  menahan permintaan yang masih menunggu persetujuan admin.
- **CORS** hanya relevan untuk versi web. Android dan iOS tidak melewatinya.
  Daftar origin diatur lewat `CORS_ALLOWED_ORIGINS` di `.env`.