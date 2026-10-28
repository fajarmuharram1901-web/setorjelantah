<?php

namespace App\Support;

use App\Rules\NamaLengkap;

/**
 * Sumber tunggal aturan validasi untuk setiap formulir.
 *
 * Controller memakai daftar ini untuk validasi di server, dan peramban
 * memakai hasil {@see untukPeramban()} untuk validasi langsung saat
 * pengguna mengetik. Karena datanya satu sumber, pesan di layar dan pesan
 * dari server tidak akan berbeda.
 */
class AturanValidasi
{
    /**
     * Aturan + pesan Indonesia untuk setiap formulir.
     *
     * Kunci form: daftar, masuk, masukAdmin, editPengguna, tambahPengepul,
     * profil, gantiSandi, ajukanSetoran, verifikasiSetoran, ajukanPencairan,
     * updateHarga, setujuiPencairan, tolakPencairan, gantiHargaKunci.
     *
     * @return array<string, array{aturan: array, pesan: array}>
     */
    public static function semua(): array
    {
        return [
            // ---------- Autentikasi ----------
            'daftar' => [
                'aturan' => [
                    'name' => ['required', 'string', 'max:100', new NamaLengkap],
                    'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
                    'no_hp' => ['nullable', 'numeric', 'digits_between:8,15'],
                    'alamat' => ['nullable', 'string', 'max:500'],
                    'password' => ['required', 'string', 'min:8', 'confirmed'],
                ],
                'pesan' => [
                    'name.required' => 'Nama lengkap wajib diisi.',
                    'name.max' => 'Nama lengkap maksimal 100 huruf.',
                    'email.required' => 'Email wajib diisi.',
                    'email.email' => 'Format email tidak valid. Contoh: nama@email.com',
                    'email.unique' => 'Email ini sudah terdaftar. Silakan masuk atau pakai email lain.',
                    'password.required' => 'Kata sandi wajib diisi.',
                    'password.min' => 'Kata sandi minimal 8 karakter.',
                    'password.confirmed' => 'Konfirmasi kata sandi tidak sama.',
                    'no_hp.numeric' => 'Nomor HP hanya boleh berisi angka.',
                    'no_hp.digits_between' => 'Nomor HP harus 8-15 angka, tanpa spasi atau tanda baca.',
                    'alamat.max' => 'Alamat maksimal 500 karakter.',
                ],
            ],

            'masuk' => [
                'aturan' => [
                    'email' => ['required', 'string', 'email', 'max:255'],
                    'password' => ['required', 'string'],
                ],
                'pesan' => [
                    'email.required' => 'Email wajib diisi.',
                    'email.email' => 'Format email tidak valid. Contoh: nama@email.com',
                    'password.required' => 'Kata sandi wajib diisi.',
                ],
            ],

            'lupaSandi' => [
                'aturan' => [
                    'email' => ['required', 'string', 'email', 'max:255'],
                ],
                'pesan' => [
                    'email.required' => 'Email wajib diisi.',
                    'email.email' => 'Format email tidak valid. Contoh: nama@email.com',
                ],
            ],

            'resetSandi' => [
                'aturan' => [
                    'token' => ['required'],
                    'email' => ['required', 'string', 'email', 'max:255'],
                    'password' => ['required', 'string', 'min:8', 'confirmed'],
                ],
                'pesan' => [
                    'email.required' => 'Email wajib diisi.',
                    'email.email' => 'Format email tidak valid. Contoh: nama@email.com',
                    'password.required' => 'Kata sandi wajib diisi.',
                    'password.min' => 'Kata sandi minimal 8 karakter.',
                    'password.confirmed' => 'Konfirmasi kata sandi tidak sama.',
                ],
            ],

            'konfirmasiSandi' => [
                'aturan' => [
                    'password' => ['required', 'string'],
                ],
                'pesan' => [
                    'password.required' => 'Kata sandi wajib diisi.',
                    // Dicek manual di controller karena harus membandingkan
                    // dengan kata sandi yang tersimpan.
                    'password.sandi' => 'Kata sandi yang dimasukkan salah.',
                ],
            ],

            // ---------- Profil ----------
            'profil' => [
                'aturan' => [
                    'name' => ['required', 'string', 'max:100', new NamaLengkap],
                    'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,{id}'],
                    'no_hp' => ['nullable', 'numeric', 'digits_between:8,15'],
                    'alamat' => ['nullable', 'string', 'max:500'],
                ],
                'pesan' => [
                    'name.required' => 'Nama lengkap wajib diisi.',
                    'name.max' => 'Nama lengkap maksimal 100 huruf.',
                    'email.required' => 'Email wajib diisi.',
                    'email.email' => 'Format email tidak valid. Contoh: nama@email.com',
                    'email.unique' => 'Email ini sudah dipakai akun lain.',
                    'no_hp.numeric' => 'Nomor HP hanya boleh berisi angka.',
                    'no_hp.digits_between' => 'Nomor HP harus 8-15 angka, tanpa spasi atau tanda baca.',
                    'alamat.max' => 'Alamat maksimal 500 karakter.',
                ],
            ],

            'gantiSandi' => [
                'aturan' => [
                    // current_password wajib ada: tanpa itu siapa pun bisa
                    // mengganti kata sandi akun orang lain hanya dengan tahu ID.
                    'current_password' => ['required', 'current_password'],
                    'password' => ['required', 'string', 'min:8', 'confirmed'],
                ],
                'pesan' => [
                    'current_password.required' => 'Kata sandi sekarang wajib diisi.',
                    'current_password.current_password' => 'Kata sandi sekarang salah.',
                    'password.required' => 'Kata sandi baru wajib diisi.',
                    'password.min' => 'Kata sandi baru minimal 8 karakter.',
                    'password.confirmed' => 'Konfirmasi kata sandi tidak sama.',
                ],
            ],

            // ---------- Admin: pengguna ----------
            'editPengguna' => [
                'aturan' => [
                    'name' => ['required', 'string', 'max:100', new NamaLengkap],
                    'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,{id}'],
                    'no_hp' => ['nullable', 'numeric', 'digits_between:8,15'],
                    'role' => ['required', 'in:user,pengepul'],
                ],
                'pesan' => [
                    'name.required' => 'Nama lengkap wajib diisi.',
                    'name.max' => 'Nama lengkap maksimal 100 huruf.',
                    'email.required' => 'Email wajib diisi.',
                    'email.email' => 'Format email tidak valid. Contoh: nama@email.com',
                    'email.unique' => 'Email ini sudah dipakai akun lain.',
                    'no_hp.numeric' => 'Nomor HP hanya boleh berisi angka.',
                    'no_hp.digits_between' => 'Nomor HP harus 8-15 angka, tanpa spasi atau tanda baca.',
                    'role.required' => 'Peran wajib dipilih.',
                    'role.in' => 'Peran harus Rumah Tangga atau Pengepul.',
                ],
            ],

            'tambahPengepul' => [
                'aturan' => [
                    'name' => ['required', 'string', 'max:100', new NamaLengkap],
                    'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
                    'password' => ['required', 'string', 'min:8'],
                    'no_hp' => ['nullable', 'numeric', 'digits_between:8,15'],
                ],
                'pesan' => [
                    'name.required' => 'Nama lengkap wajib diisi.',
                    'name.max' => 'Nama lengkap maksimal 100 huruf.',
                    'email.required' => 'Email wajib diisi.',
                    'email.email' => 'Format email tidak valid. Contoh: nama@email.com',
                    'email.unique' => 'Email ini sudah dipakai akun lain.',
                    'password.required' => 'Kata sandi wajib diisi.',
                    'password.min' => 'Kata sandi minimal 8 karakter.',
                    'no_hp.numeric' => 'Nomor HP hanya boleh berisi angka.',
                    'no_hp.digits_between' => 'Nomor HP harus 8-15 angka, tanpa spasi atau tanda baca.',
                ],
            ],

            // ---------- Admin: harga ----------
            'updateHarga' => [
                'aturan' => [
                    'harga_minyak_kg' => ['required', 'numeric', 'min:1', 'max:100000'],
                    'harga_endapan_kg' => ['required', 'numeric', 'min:0', 'max:100000'],
                ],
                'pesan' => [
                    'harga_minyak_kg.required' => 'Harga minyak wajib diisi.',
                    'harga_minyak_kg.min' => 'Harga minyak minimal Rp1 per kg.',
                    'harga_minyak_kg.max' => 'Harga minyak maksimal Rp100.000 per kg.',
                    'harga_endapan_kg.required' => 'Harga endapan wajib diisi.',
                    'harga_endapan_kg.min' => 'Harga endapan tidak boleh negatif. Isi 0 bila tidak dibayar.',
                    'harga_endapan_kg.max' => 'Harga endapan maksimal Rp100.000 per kg.',
                ],
            ],

            // ---------- Admin: pencairan ----------
            'tolakPencairan' => [
                'aturan' => [
                    'alasan_tolak' => ['required', 'string', 'max:500'],
                ],
                'pesan' => [
                    'alasan_tolak.required' => 'Sertakan alasan penolakan agar warga tahu.',
                    'alasan_tolak.max' => 'Alasan penolakan maksimal 500 karakter.',
                ],
            ],

            // ---------- Warga: setoran ----------
            'ajukanSetoran' => [
                'aturan' => [
                    'perkiraan_minyak_kg' => ['required', 'numeric', 'min:0', 'max:1000'],
                    'perkiraan_endapan_kg' => ['required', 'numeric', 'min:0', 'max:1000'],
                    'alamat_jemput' => ['required', 'string', 'max:500'],
                    'catatan' => ['nullable', 'string', 'max:1000'],
                ],
                'pesan' => [
                    'perkiraan_minyak_kg.required' => 'Perkiraan minyak wajib diisi. Isi 0 jika tidak ada minyak.',
                    'perkiraan_minyak_kg.max' => 'Perkiraan minyak maksimal 1.000 kg.',
                    'perkiraan_endapan_kg.required' => 'Perkiraan endapan wajib diisi. Isi 0 jika tidak ada endapan.',
                    'perkiraan_endapan_kg.max' => 'Perkiraan endapan maksimal 1.000 kg.',
                    'alamat_jemput.required' => 'Alamat penjemputan wajib diisi.',
                    'alamat_jemput.max' => 'Alamat penjemputan maksimal 500 karakter.',
                    'catatan.max' => 'Catatan maksimal 1.000 karakter.',
                ],
            ],

            'pencairan' => [
                'aturan' => [
                    'jumlah' => ['required', 'numeric', 'min:10000', 'max:{maksSaldo}'],
                    'metode' => ['required', 'in:ewallet,transfer,tunai'],
                    'tujuan' => ['required', 'string', 'max:120'],
                    'nama_penerima' => ['required', 'string', 'max:100', new NamaLengkap],
                ],
                'pesan' => [
                    'jumlah.required' => 'Jumlah pencairan wajib diisi.',
                    'jumlah.min' => 'Pencairan minimal Rp10.000.',
                    'jumlah.max' => 'Jumlah pencairan melebihi saldo tersedia Anda.',
                    'metode.required' => 'Metode pencairan wajib dipilih.',
                    'metode.in' => 'Metode pencairan tidak dikenal.',
                    'tujuan.required' => 'Nomor rekening atau nomor HP tujuan wajib diisi.',
                    'tujuan.max' => 'Nomor tujuan maksimal 120 karakter.',
                    'nama_penerima.required' => 'Nama pemilik rekening wajib diisi.',
                    'nama_penerima.max' => 'Nama pemilik rekening maksimal 100 huruf.',
                ],
            ],

            // ---------- Pengepul ----------
            'verifikasiSetoran' => [
                'aturan' => [
                    'minyak_kg' => ['required', 'numeric', 'min:0', 'max:1000'],
                    'endapan_kg' => ['required', 'numeric', 'min:0', 'max:1000'],
                    'catatan_pengepul' => ['nullable', 'string', 'max:1000'],
                    'bukti_foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
                ],
                'pesan' => [
                    'minyak_kg.required' => 'Berat minyak wajib diisi. Isi 0 jika tidak ada minyak.',
                    'minyak_kg.max' => 'Berat minyak maksimal 1.000 kg.',
                    'endapan_kg.required' => 'Berat endapan wajib diisi. Isi 0 jika tidak ada endapan.',
                    'endapan_kg.max' => 'Berat endapan maksimal 1.000 kg.',
                    'catatan_pengepul.max' => 'Catatan maksimal 1.000 karakter.',
                    'bukti_foto.image' => 'Berkas bukti harus berupa gambar.',
                    'bukti_foto.mimes' => 'Format gambar harus JPG, PNG, atau WEBP.',
                    'bukti_foto.max' => 'Ukuran bukti foto maksimal 4 MB.',
                ],
            ],
        ];
    }

    /**
     * Ambil satu set aturan berdasarkan nama formulir.
     *
     * @return array{aturan: array, pesan: array}
     */
    public static function untuk(string $form): array
    {
        return self::semua()[$form] ?? ['aturan' => [], 'pesan' => []];
    }

    /**
     * Aturan siap pakai untuk $request->validate(), dengan {id} dan
     * {maksSaldo} diganti nilai sebenarnya.
     *
     * @return array{aturan: array, pesan: array}
     */
    public static function siap(string $form, array $pengganti = []): array
    {
        $data = self::untuk($form);

        $ganti = static function ($nilai) use ($pengganti) {
            // Aturan bisa berupa objek (mis. NamaLengkap), bukan string,
            // jadi jangan disalin dengan str_replace.
            if (! is_string($nilai)) {
                return $nilai;
            }

            foreach ($pengganti as $kunci => $nilaiBaru) {
                $nilai = str_replace('{' . $kunci . '}', (string) $nilaiBaru, $nilai);
            }

            return $nilai;
        };

        $aturan = array_map(
            static fn ($baris) => array_map($ganti, $baris),
            $data['aturan']
        );

        $pesan = array_map($ganti, $data['pesan']);

        return ['aturan' => $aturan, 'pesan' => $pesan];
    }

    /**
     * Ubah aturan Laravel menjadi bentuk yang dipahami JavaScript.
     *
     * Aturan yang tidak bisa dicek di peramban (misalnya unique) ditandai
     * supaya mesin validasi memanggil server.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function untukPeramban(string $form, array $pengganti = []): array
    {
        $data = self::siap($form, $pengganti);
        $hasil = [];

        foreach ($data['aturan'] as $kolom => $baris) {
            $hasil[$kolom] = self::terjemahkan($kolom, $baris, $data['pesan']);
        }

        return $hasil;
    }

    /**
     * Ganti penanda :attribute, :min, dan :max dengan teks yang enak dibaca.
     *
     * Tanpa ini pesan seperti ":attribute maksimal :max karakter." akan
     * tampil apa adanya di layar.
     */
    private static function isi(string $teks, string $label, ?string $min = null, ?string $max = null): string
    {
        return str_replace(
            [':attribute', ':min', ':max'],
            [$label, (string) $min, (string) $max],
            $teks
        );
    }

    /**
     * Terjemahkan satu baris aturan ke deskripsi peramban.
     *
     * @return array<string, mixed>
     */
    private static function terjemahkan(string $kolom, array $baris, array $pesan): array
    {
        $label = self::label($kolom);

        $keterangan = [
            'wajib' => false,
            'tipe' => 'teks',
            'label' => $label,
            'pesan' => [],
        ];

        // Semua pesan di baris ini sudah mendapat nama kolom aslinya.
        $pesan = array_map(
            static fn ($teks) => is_string($teks) ? self::isi($teks, $label) : $teks,
            $pesan
        );

        foreach ($baris as $aturan) {
            if ($aturan instanceof NamaLengkap) {
                $keterangan['tipe'] = 'nama';
                $keterangan['pesan']['pola'] = NamaLengkap::pesanPeramban();
                continue;
            }

            if (! is_string($aturan)) {
                continue;
            }

            [$nama, $argumen] = array_pad(explode(':', $aturan, 2), 2, null);

            switch ($nama) {
                case 'required':
                    $keterangan['wajib'] = true;
                    $keterangan['pesan']['kosong'] ??= self::isi(
                        $pesan[$kolom . '.required'] ?? ':attribute wajib diisi.',
                        $label
                    );
                    break;

                case 'nullable':
                    // kolom opsional, tidak ada pemeriksaan tambahan
                    break;

                case 'email':
                    $keterangan['tipe'] = 'email';
                    $keterangan['pesan']['format'] ??= self::isi(
                        $pesan[$kolom . '.email'] ?? 'Format email tidak valid.',
                        $label
                    );
                    break;

                case 'numeric':
                    $keterangan['tipe'] = 'angka';
                    $keterangan['pesan']['angka'] ??= self::isi(
                        $pesan[$kolom . '.numeric'] ?? ':attribute harus berupa angka.',
                        $label
                    );
                    break;

                case 'integer':
                    $keterangan['tipe'] = 'angka';
                    $keterangan['bulat'] = true;
                    break;

                case 'image':
                case 'file':
                    $keterangan['tipe'] = 'berkas';
                    break;

                case 'mimes':
                    $keterangan['format'] = $argumen;
                    $keterangan['pesan']['format'] ??= ($pesan[$kolom . '.mimes'] ?? 'Format berkas tidak sesuai.');
                    break;

                case 'max':
                    if ($keterangan['tipe'] === 'angka') {
                        $keterangan['maksAngka'] = (float) $argumen;
                        $keterangan['pesan']['maksAngka'] = self::isi(
                            $pesan[$kolom . '.max'] ?? ':attribute maksimal bernilai :max.',
                            $label,
                            null,
                            $argumen
                        );
                    } else {
                        $keterangan['maks'] = (int) $argumen;
                        $keterangan['pesan']['maks'] = self::isi(
                            $pesan[$kolom . '.max'] ?? ':attribute maksimal :max karakter.',
                            $label,
                            null,
                            $argumen
                        );
                    }
                    break;

                case 'min':
                    if ($keterangan['tipe'] === 'angka') {
                        $keterangan['minAngka'] = (float) $argumen;
                        $keterangan['pesan']['minAngka'] = self::isi(
                            $pesan[$kolom . '.min'] ?? ':attribute minimal bernilai :min.',
                            $label,
                            $argumen
                        );
                    } else {
                        $keterangan['min'] = (int) $argumen;
                        $keterangan['pesan']['min'] = self::isi(
                            $pesan[$kolom . '.min'] ?? ':attribute minimal :min karakter.',
                            $label,
                            $argumen
                        );
                    }
                    break;

                case 'confirmed':
                    $keterangan['tipe'] = 'sandi';
                    $keterangan['pesan']['koncoc'] = $pesan[$kolom . '.confirmed'] ?? 'Konfirmasi kata sandi tidak sama.';
                    break;

                case 'current_password':
                    $keterangan['pesan']['kini'] = $pesan[$kolom . '.current_password'] ?? 'Kata sandi sekarang salah.';
                    break;

                case 'in':
                    $keterangan['pilihan'] = explode(',', (string) $argumen);
                    $keterangan['pesan']['pilihan'] = $pesan[$kolom . '.in'] ?? 'Pilihan tidak valid.';
                    break;

                case 'unique':
                    // hanya server yang bisa memastikan, dicek lewat AJAX
                    $keterangan['server'] = true;
                    $keterangan['pesan']['server'] = $pesan[$kolom . '.unique'] ?? 'Nilai ini sudah dipakai.';
                    break;
            }
        }

        return $keterangan;
    }

    /**
     * Nama kolom yang enak dibaca untuk ditampilkan di pesan.
     */
    private static function label(string $kolom): string
    {
        $daftar = [
            'name' => 'Nama lengkap',
            'email' => 'Email',
            'password' => 'Kata sandi',
            'password_confirmation' => 'Konfirmasi kata sandi',
            'current_password' => 'Kata sandi sekarang',
            'no_hp' => 'Nomor HP',
            'alamat' => 'Alamat',
            'alamat_jemput' => 'Alamat penjemputan',
            'catatan' => 'Catatan',
            'catatan_pengepul' => 'Catatan pengepul',
            'role' => 'Peran',
            'jumlah' => 'Jumlah pencairan',
            'metode' => 'Metode pencairan',
            'tujuan' => 'Nomor tujuan',
            'nama_penerima' => 'Nama pemilik rekening',
            'alasan_tolak' => 'Alasan penolakan',
            'harga_minyak_kg' => 'Harga minyak per kilogram',
            'harga_endapan_kg' => 'Harga endapan per kilogram',
            'perkiraan_minyak_kg' => 'Perkiraan minyak',
            'perkiraan_endapan_kg' => 'Perkiraan endapan',
            'minyak_kg' => 'Berat minyak',
            'endapan_kg' => 'Berat endapan',
            'bukti_foto' => 'Bukti foto',
        ];

        return $daftar[$kolom] ?? ucfirst(str_replace('_', ' ', $kolom));
    }
}