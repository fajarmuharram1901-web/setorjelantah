<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\AturanValidasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Aturan "nama lengkap hanya huruf", validasi langsung di peramban, dan
 * bahasa Indonesia untuk seluruh pesan.
 */
class ValidasiNamaTest extends TestCase
{
    use RefreshDatabase;

    /** Nama yang harus diterima. */
    private const NAMA_VALID = [
        'satu kata' => 'Budi',
        'dua kata' => 'Siti Aminah',
        'tanda hubung' => 'Anne-Marie Wijaya',
        'apostrof' => "Ma'ruf",
        'nama gelar' => 'St. Clara',
        'huruf beraksen' => 'José Muñoz',
    ];

    /** Nama yang harus ditolak karena ada angka atau simbol. */
    private const NAMA_TIDAK_VALID = [
        'mengandung angka' => 'Budi 123',
        'penuh angka' => '12345',
        'simbol bintang' => 'Budi*',
        'emoji' => 'Budi :)',
        'nomor HP' => '08123456789',
        'atsign' => 'budi@mail.com',
        'garis miring' => 'Budi/Lani',
        'hanya tanda baca' => '...',
    ];

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function warga(): User
    {
        return User::factory()->create(['role' => 'user']);
    }

    private function daftar(string $nama, string $email): \Illuminate\Testing\TestResponse
    {
        // Pendaftaran otomatis 로그 masuk. Request berikutnya harus dilakukan
        // sebagai tamu, kalau tidak middleware guest Simply arahkan ke dashboard.
        auth()->logout();

        return $this->post('/register', [
            'name' => $nama,
            'email' => $email,
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ]);
    }

    // ---------- Validasi di server ----------

    public function test_pendaftaran_menerima_nama_yang_sah(): void
    {
        foreach (self::NAMA_VALID as $keterangan => $nama) {
            $email = ' sah-' . md5($keterangan) . '@contoh.test';

            $this->daftar($nama, $email)->assertSessionHasNoErrors();

            $this->assertDatabaseHas('users', ['name' => $nama]);
        }
    }

    public function test_pendaftaran_menolak_nama_berangka_atau_bersimbol(): void
    {
        foreach (self::NAMA_TIDAK_VALID as $keterangan => $nama) {
            $this->daftar($nama, 'gagal-' . md5($keterangan) . '@contoh.test')
                ->assertSessionHasErrors('name');

            $this->assertDatabaseMissing('users', ['name' => $nama]);
        }
    }

    public function test_pesan_gagal_nama_berbahasa_indonesia(): void
    {
        $this->daftar('Budi99', 'uji@contoh.test')->assertSessionHasErrors('name');

        $pesan = session('errors')->first('name');

        $this->assertStringContainsString('hanya boleh berisi huruf', $pesan);
        $this->assertStringNotContainsString('The name field', $pesan);
    }

    public function test_edit_pengguna_menolak_nama_berangka(): void
    {
        $admin = $this->admin();
        $warga = $this->warga();

        $this->actingAs($admin)
            ->put(route('admin.pengguna.update', $warga), [
                'name' => 'Siti99',
                'email' => $warga->email,
                'role' => 'user',
            ])
            ->assertSessionHasErrors('name');

        $this->assertNotSame('Siti99', $warga->fresh()->name);
    }

    public function test_tambah_pengepul_menolak_nama_berangka(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.pengguna.pengepul'), [
                'name' => 'Pengepul#1',
                'email' => 'pengepul@contoh.test',
                'password' => 'rahasia123',
            ])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseMissing('users', ['email' => 'pengepul@contoh.test']);
    }

    public function test_profil_menolak_nama_berangka(): void
    {
        $warga = $this->warga();

        $this->actingAs($warga)
            ->patch(route('profile.update'), [
                'name' => 'Andi 2000',
                'email' => $warga->email,
            ])
            ->assertSessionHasErrors('name');
    }

    public function test_nama_penerima_pencairan_hanya_huruf(): void
    {
        $warga = $this->warga();
        $warga->update(['saldo' => 500000]);

        $this->actingAs($warga)
            ->post(route('user.pencairan.store'), [
                'jumlah' => 50000,
                'metode' => 'ewallet',
                'tujuan' => '08123456789',
                'nama_penerima' => 'Budi 123',
            ])
            ->assertSessionHasErrors('nama_penerima');
    }

    // ---------- Aturan yang dikirim ke peramban ----------

    public function test_aturan_peramban_menandai_kolom_nama(): void
    {
        $aturan = AturanValidasi::untukPeramban('daftar');

        $this->assertSame('nama', $aturan['name']['tipe']);
        $this->assertTrue($aturan['name']['wajib']);
        $this->assertStringContainsString('hanya boleh berisi huruf', $aturan['name']['pesan']['pola']);
    }

    public function test_aturan_peramban_tidak_berisi_placeholder(): void
    {
        foreach (array_keys(AturanValidasi::semua()) as $form) {
            $json = json_encode(AturanValidasi::untukPeramban($form), JSON_UNESCAPED_UNICODE);

            $this->assertStringNotContainsString(':attribute', $json, "Form $form masih punya :attribute");
            $this->assertStringNotContainsString(':min', $json, "Form $form masih punya :min");
            $this->assertStringNotContainsString(':max', $json, "Form $form masih punya :max");
        }
    }

    public function test_semua_form_memiliki_aturan_dan_pesan(): void
    {
        foreach (AturanValidasi::semua() as $form => $data) {
            $this->assertNotEmpty($data['aturan'], "Form $form tidak punya aturan.");
            $this->assertNotEmpty($data['pesan'], "Form $form tidak punya pesan.");
        }
    }

    // ---------- Bahasa Indonesia ----------

    public function test_locale_aplikasi_adalah_indonesia(): void
    {
        $this->assertSame('id', app()->getLocale());
    }

    public function test_pesan_validasi_bawaan_laravel_berbahasa_indonesia(): void
    {
        $validator = validator(
            ['nama' => ''],
            ['nama' => 'required'],
            [],
            ['nama' => 'Nama lengkap']
        );

        $this->assertSame('Nama lengkap wajib diisi.', $validator->errors()->first('nama'));
    }

    public function test_pesan_validasi_bawaan_tidak_ada_teks_inggris(): void
    {
        // Aturan tanpa pesan khusus harus tetap berbahasa Indonesia
        // karena diambil dari file lang/id.
        $validator = validator(
            ['no_hp' => 'bukan-angka'],
            ['no_hp' => 'numeric'],
            [],
            ['no_hp' => 'Nomor HP']
        );

        $pesan = $validator->errors()->first('no_hp');

        $this->assertStringContainsString('Nomor HP', $pesan);
        $this->assertStringNotContainsString('must be a number', $pesan);
    }

    public function test_seluruh_pesan_pendaftaran_berbahasa_indonesia(): void
    {
        $this->post('/register', [
            'name' => 'Budi99',
            'email' => 'bukan-email',
            'password' => 'pendek',
            'password_confirmation' => 'lain',
        ])->assertSessionHasErrors(['name', 'email', 'password']);

        foreach (session('errors')->all() as $pesan) {
            $this->assertDoesNotMatchRegularExpression(
                '/\bThe \w+ field\b/',
                $pesan,
                "Pesan masih bahasa Inggris: $pesan"
            );
        }
    }

    // ---------- Pemeriksaan email unik ----------

    public function test_endpoint_email_menyatakan_sudah_dipakai(): void
    {
        $ada = $this->warga();

        $respons = $this->getJson('/validasi/email?email=' . urlencode($ada->email));

        $respons->assertOk();
        $this->assertTrue($respons->json('dipakai'));
    }

    public function test_endpoint_email_menerima_email_yang_bebas(): void
    {
        $respons = $this->getJson('/validasi/email?email=bebas@contoh.test');

        $respons->assertOk();
        $this->assertFalse($respons->json('dipakai'));
    }

    public function test_endpoint_email_mengabaikan_id_yak_sedang_diedit(): void
    {
        $warga = $this->warga();

        // Tanpa id, email sendiri dianggap terpakai.
        $this->getJson('/validasi/email?email=' . urlencode($warga->email))
            ->assertOk()
            ->assertJsonPath('dipakai', true);

        // Dengan id yang sama, tidak dianggap terpakai.
        $this->getJson('/validasi/email?email=' . urlencode($warga->email) . '&abaikan=' . $warga->id)
            ->assertOk()
            ->assertJsonPath('dipakai', false);
    }

    public function test_endpoint_email_tidak_perlu_login(): void
    {
        // Halaman pendaftaran dan lupa kata sandi dipakai tamu, jadi endpoint
        // ini harus terbuka tanpa sesi.
        $this->warga();

        $this->getJson('/validasi/email?email=siap@a.test')
            ->assertOk()
            ->assertJsonPath('dipakai', false);
    }

    public function test_endpoint_email_menolak_email_tidak_sah(): void
    {
        // Permintaan JSON mendapat 422 beserta pesan Indonesia.
        $respons = $this->getJson('/validasi/email?email=bukan-email');

        $respons->assertStatus(422);
        $this->assertStringContainsString('Format email tidak valid', $respons->json('errors.email.0'));
    }
    // ---------- Formulir punya mesin validasi ----------

    public function test_halaman_memuat_mesin_validasi(): void
    {
        $warga = $this->warga();

        $halaman = [
            'daftar' => $this->get('/register'),
            'masuk' => $this->get('/login'),
            'lupa sandi' => $this->get('/forgot-password'),
            'profil' => $this->actingAs($warga)->get('/profile'),
        ];

        foreach ($halaman as $nama => $respons) {
            $respons->assertOk();
            $respons->assertSee('js/validasi.js', false);
            $respons->assertSee('data-validasi', false);
        }
    }

    public function test_semua_formulir_input_punya_aturan(): void
    {
        $warga = $this->warga();
        $warga->update(['saldo' => 500000]);
        $admin = $this->admin();
        $pengepul = User::factory()->create(['role' => 'pengepul']);

        // Setoran yang sedang dikerjakan pengepul, supaya halaman verifikasi
        // benar-benar menampilkan formulirnya.
        $setoran = \App\Models\Setoran::create([
            'user_id' => $warga->id,
            'pengepul_id' => $pengepul->id,
            'perkiraan_kg' => 100,
            'perkiraan_minyak_kg' => 95,
            'perkiraan_endapan_kg' => 5,
            'alamat_jemput' => 'Jl. Mawar No. 1',
            'status' => 'diproses',
            'tanggal_ajukan' => now(),
        ]);

        $halaman = [
            'ajukan setoran' => $this->actingAs($warga)->get(route('user.ajukan')),
            'pencairan' => $this->actingAs($warga)->get(route('user.pencairan.create')),
            'harga' => $this->actingAs($admin)->get(route('admin.harga')),
            'kelola pengguna' => $this->actingAs($admin)->get(route('admin.pengguna')),
            'edit pengguna' => $this->actingAs($admin)->get(route('admin.pengguna.edit', $warga)),
            'verifikasi setoran' => $this->actingAs($pengepul)->get(route('pengepul.verifikasi', $setoran)),
        ];

        foreach ($halaman as $nama => $respons) {
            $respons->assertOk();
            $respons->assertSee('data-validasi', false);
        }
    }
}
