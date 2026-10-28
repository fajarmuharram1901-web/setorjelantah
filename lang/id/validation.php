<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pesan Validasi
    |--------------------------------------------------------------------------
    |
    | Daftar di bawah ini berisi pesan kesalahan bawaan yang dipakai oleh kelas
    | validator. Beberapa aturan memiliki beberapa versi, misalnya aturan
    | ukuran (size). Silakan ubah pesan di sini sesuai kebutuhan aplikasi.
    |
    */

    'accepted' => ':attribute harus disetujui.',
    'accepted_if' => ':attribute harus disetujui jika :other bernilai :value.',
    'active_url' => ':attribute harus berupa tautan yang valid dan dapat diakses.',
    'after' => ':attribute harus berisi tanggal setelah :date.',
    'after_or_equal' => ':attribute harus berisi tanggal setelah atau sama dengan :date.',
    'alpha' => ':attribute hanya boleh berisi huruf.',
    'alpha_dash' => ':attribute hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
    'alpha_num' => ':attribute hanya boleh berisi huruf dan angka.',
    'any_of' => ':attribute harus salah satu dari pilihan yang tersedia.',
    'array' => ':attribute harus berupa daftar data.',
    'array_keys' => ':attribute hanya boleh memiliki kunci berikut: :values.',
    'ascii' => ':attribute hanya boleh berisi karakter satu byte berupa huruf, angka, dan simbol.',
    'base64' => ':attribute harus berupa teks Base64 yang valid.',
    'before' => ':attribute harus berisi tanggal sebelum :date.',
    'before_or_equal' => ':attribute harus berisi tanggal sebelum atau sama dengan :date.',
    'between' => [
        'array' => ':attribute harus berisi antara :min sampai :max item.',
        'file' => 'Ukuran :attribute harus antara :min sampai :max kilobyte.',
        'numeric' => ':attribute harus bernilai antara :min sampai :max.',
        'string' => ':attribute harus berisi antara :min sampai :max karakter.',
    ],
    'boolean' => ':attribute harus bernilai benar atau salah.',
    'can' => ':attribute tidak diizinkan untuk Anda.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'contains' => ':attribute belum memuat nilai yang diperlukan.',
    'current_password' => 'Kata sandi yang dimasukkan salah.',
    'date' => ':attribute harus berisi tanggal yang valid.',
    'date_equals' => ':attribute harus berisi tanggal yang sama dengan :date.',
    'date_format' => ':attribute harus sesuai dengan format :format.',
    'decimal' => ':attribute hanya boleh memiliki :decimal angka di belakang koma.',
    'declined' => ':attribute harus ditolak.',
    'declined_if' => ':attribute harus ditolak jika :other bernilai :value.',
    'different' => ':attribute harus berbeda dari :other.',
    'digits' => ':attribute harus terdiri dari :digits angka.',
    'digits_between' => ':attribute harus terdiri dari :min sampai :max angka.',
    'dimensions' => 'Dimensi gambar pada :attribute tidak sesuai.',
    'distinct' => ':attribute memiliki nilai yang duplikat.',
    'doesnt_contain' => ':attribute tidak boleh memuat salah satu dari nilai berikut: :values.',
    'doesnt_end_with' => ':attribute tidak boleh diakhiri salah satu dari nilai berikut: :values.',
    'doesnt_start_with' => ':attribute tidak boleh diawali salah satu dari nilai berikut: :values.',
    'email' => 'Format :attribute tidak valid. Contoh: nama@email.com',
    'encoding' => ':attribute harus dienkode dengan :encoding.',
    'ends_with' => ':attribute harus diakhiri salah satu dari nilai berikut: :values.',
    'enum' => ':attribute yang dipilih tidak valid.',
    'exists' => ':attribute yang dipilih tidak ditemukan.',
    'extensions' => ':attribute harus memiliki salah satu ekstensi berikut: :values.',
    'file' => ':attribute harus berupa berkas.',
    'filled' => ':attribute harus diisi.',
    'gt' => [
        'array' => 'Jumlah item pada :attribute harus lebih dari :value.',
        'file' => 'Ukuran :attribute harus lebih dari :value kilobyte.',
        'numeric' => ':attribute harus bernilai lebih dari :value.',
        'string' => ':attribute harus berisi lebih dari :value karakter.',
    ],
    'gte' => [
        'array' => 'Jumlah item pada :attribute harus minimal :value.',
        'file' => 'Ukuran :attribute harus minimal :value kilobyte.',
        'numeric' => ':attribute harus bernilai minimal :value.',
        'string' => ':attribute harus berisi minimal :value karakter.',
    ],
    'hex_color' => ':attribute harus berupa kode warna heksadesimal yang valid.',
    'image' => ':attribute harus berupa gambar.',
    'in' => ':attribute yang dipilih tidak valid.',
    'in_array' => ':attribute harus terdapat di dalam :other.',
    'in_array_keys' => ':attribute harus memuat minimal salah satu kunci berikut: :values.',
    'integer' => ':attribute harus berupa bilangan bulat.',
    'ip' => ':attribute harus berupa alamat IP yang valid.',
    'ipv4' => ':attribute harus berupa alamat IPv4 yang valid.',
    'ipv6' => ':attribute harus berupa alamat IPv6 yang valid.',
    'json' => ':attribute harus berupa teks JSON yang valid.',
    'list' => ':attribute harus berupa daftar data.',
    'lowercase' => ':attribute harus memakai huruf kecil.',
    'lt' => [
        'array' => 'Jumlah item pada :attribute harus kurang dari :value.',
        'file' => 'Ukuran :attribute harus kurang dari :value kilobyte.',
        'numeric' => ':attribute harus bernilai kurang dari :value.',
        'string' => ':attribute harus berisi kurang dari :value karakter.',
    ],
    'lte' => [
        'array' => 'Jumlah item pada :attribute tidak boleh lebih dari :value.',
        'file' => 'Ukuran :attribute tidak boleh lebih dari :value kilobyte.',
        'numeric' => ':attribute tidak boleh bernilai lebih dari :value.',
        'string' => ':attribute tidak boleh berisi lebih dari :value karakter.',
    ],
    'mac_address' => ':attribute harus berupa alamat MAC yang valid.',
    'max' => [
        'array' => ':attribute maksimal berisi :max item.',
        'file' => 'Ukuran :attribute maksimal :max kilobyte.',
        'numeric' => ':attribute maksimal bernilai :max.',
        'string' => ':attribute maksimal :max karakter.',
    ],
    'max_digits' => ':attribute maksimal terdiri dari :max angka.',
    'mimes' => ':attribute harus berupa berkas dengan jenis: :values.',
    'mimetypes' => ':attribute harus berupa berkas dengan jenis: :values.',
    'min' => [
        'array' => ':attribute harus berisi minimal :min item.',
        'file' => 'Ukuran :attribute minimal :min kilobyte.',
        'numeric' => ':attribute minimal bernilai :min.',
        'string' => ':attribute minimal :min karakter.',
    ],
    'min_digits' => ':attribute minimal terdiri dari :min angka.',
    'missing' => ':attribute tidak boleh diisi.',
    'missing_if' => ':attribute tidak boleh diisi jika :other bernilai :value.',
    'missing_unless' => ':attribute tidak boleh diisi kecuali :other bernilai :value.',
    'missing_with' => ':attribute tidak boleh diisi jika :values sudah diisi.',
    'missing_with_all' => ':attribute tidak boleh diisi jika semua nilai berikut sudah diisi: :values.',
    'multiple_of' => ':attribute harus merupakan kelipatan dari :value.',
    'not_in' => ':attribute yang dipilih tidak valid.',
    'not_regex' => 'Format :attribute tidak sesuai dengan pola yang ditentukan.',
    'numeric' => ':attribute harus berupa angka.',
    'password' => [
        'letters' => ':attribute harus mengandung minimal satu huruf.',
        'mixed' => ':attribute harus mengandung huruf besar dan huruf kecil.',
        'numbers' => ':attribute harus mengandung minimal satu angka.',
        'symbols' => ':attribute harus mengandung minimal satu simbol.',
        'uncompromised' => ':attribute pernah muncul dalam kebocoran data, pilih kata sandi lain.',
    ],
    'present' => ':attribute wajib diisi, boleh dikosongkan.',
    'present_if' => ':attribute wajib diisi jika :other bernilai :value.',
    'present_unless' => ':attribute wajib diisi kecuali :other bernilai :value.',
    'present_with' => ':attribute wajib diisi jika :values sudah diisi.',
    'present_with_all' => ':attribute wajib diisi jika semua nilai berikut sudah diisi: :values.',
    'prohibited' => ':attribute tidak boleh diisi.',
    'prohibited_if' => ':attribute tidak boleh diisi jika :other bernilai :value.',
    'prohibited_if_accepted' => ':attribute tidak boleh diisi jika :other disetujui.',
    'prohibited_if_declined' => ':attribute tidak boleh diisi jika :other ditolak.',
    'prohibited_unless' => ':attribute tidak boleh diisi kecuali :other berisi salah satu dari: :values.',
    'prohibits' => 'Jika :attribute diisi, maka :other tidak boleh diisi.',
    'regex' => 'Format :attribute tidak valid.',
    'required' => ':attribute wajib diisi.',
    'required_array_keys' => ':attribute harus memuat isian untuk: :values.',
    'required_if' => ':attribute wajib diisi jika :other bernilai :value.',
    'required_if_accepted' => ':attribute wajib diisi jika :other disetujui.',
    'required_if_declined' => ':attribute wajib diisi jika :other ditolak.',
    'required_unless' => ':attribute wajib diisi kecuali :other berisi salah satu dari: :values.',
    'required_with' => ':attribute wajib diisi jika :values sudah diisi.',
    'required_with_all' => ':attribute wajib diisi jika semua nilai berikut sudah diisi: :values.',
    'required_without' => ':attribute wajib diisi jika :values belum diisi.',
    'required_without_all' => ':attribute wajib diisi jika semua nilai berikut belum diisi: :values.',
    'same' => ':attribute harus sama dengan :other.',
    'size' => [
        'array' => ':attribute harus berisi :size item.',
        'file' => 'Ukuran :attribute harus :size kilobyte.',
        'numeric' => ':attribute harus bernilai :size.',
        'string' => ':attribute harus terdiri dari :size karakter.',
    ],
    'starts_with' => ':attribute harus diawali salah satu dari nilai berikut: :values.',
    'string' => ':attribute harus berupa teks.',
    'timezone' => ':attribute harus berupa zona waktu yang valid.',
    'unique' => ':attribute sudah digunakan. Silakan gunakan nilai lain.',
    'uploaded' => ':attribute gagal diunggah. Silakan coba lagi.',
    'uppercase' => ':attribute harus memakai huruf besar.',
    'url' => ':attribute harus berupa tautan yang valid, diawali http:// atau https://.',
    'ulid' => ':attribute harus berupa ULID yang valid.',
    'uuid' => ':attribute harus berupa UUID yang valid.',

    /*
    |--------------------------------------------------------------------------
    | Pesan Validasi Kustom
    |--------------------------------------------------------------------------
    |
    | Di sini Anda dapat menulis pesan validasi kustom untuk setiap atribut
    | dengan pola "nama_atribut.nama_aturan". Contoh:
    |
    | 'email' => [
    |     'unique' => 'Email ini sudah dipakai.',
    | ],
    |
    */

    'custom' => [],

    /*
    |--------------------------------------------------------------------------
    | Nama Atribut Kustom
    |--------------------------------------------------------------------------
    |
    | Daftar di bawah ini mengganti :attribute dengan nama yang lebih mudah
    | dipahami, misalnya "alamat email" sebagai pengganti "email".
    |
    */

    'attributes' => [
        'name' => 'nama lengkap',
        'email' => 'email',
        'no_hp' => 'nomor HP',
        'password' => 'kata sandi',
        'password_confirmation' => 'konfirmasi kata sandi',
        'current_password' => 'kata sandi saat ini',
        'remember' => 'ingat saya',
        'alamat' => 'alamat',
        'role' => 'peran',
        'harga_minyak_kg' => 'harga minyak per kilogram',
        'harga_endapan_kg' => 'harga endapan per kilogram',
        'perkiraan_minyak_kg' => 'perkiraan minyak',
        'perkiraan_endapan_kg' => 'perkiraan endapan',
        'minyak_kg' => 'berat minyak',
        'endapan_kg' => 'berat endapan',
        'kualitas' => 'kualitas',
        'kualitas_minyak' => 'kualitas minyak',
        'kualitas_endapan' => 'kualitas endapan',
        'catatan' => 'catatan',
        'catatan_pengepul' => 'catatan pengepul',
        'catatan_penilai' => 'catatan penilaian',
        'bukti_foto' => 'bukti foto',
        'alamat_jemput' => 'alamat penjemputan',
        'jumlah' => 'jumlah pencairan',
        'metode' => 'metode pencairan',
        'tujuan' => 'nomor tujuan pencairan',
        'nama_penerima' => 'nama penerima',
        'alasan_tolak' => 'alasan penolakan',
        'saldo' => 'saldo',
        'tanggal' => 'tanggal',
        'kode' => 'kode',
        'token' => 'token',
        'foto' => 'foto',
    ],

];
