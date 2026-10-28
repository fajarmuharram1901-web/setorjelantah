<?php

namespace Tests\Feature;

use App\Models\Harga;
use App\Models\Pencairan;
use App\Models\Pengaturan;
use App\Models\Setoran;
use App\Models\Transaksi;
use App\Models\User;
use App\Support\Saldo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class AlurSetorJelantahTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function warga(): User
    {
        return User::factory()->create(['role' => 'user', 'saldo' => 0]);
    }

    private function pengepul(): User
    {
        return User::factory()->create(['role' => 'pengepul']);
    }

    /** Pasang harga yang berlaku: minyak 10.000/kg, endapan 5.000/kg. */
    private function pasangHarga(float $minyak = 10000, float $endapan = 5000): Harga
    {
        return Harga::create([
            'harga_minyak_kg' => $minyak,
            'harga_endapan_kg' => $endapan,
            'updated_by' => $this->admin()->id,
        ]);
    }

    private function setoran(array $atribut = []): Setoran
    {
        return Setoran::create(array_merge([
            'user_id' => $this->warga()->id,
            'perkiraan_kg' => 100,
            'alamat_jemput' => 'Jl. Mawar No. 1',
            'status' => 'menunggu',
            'tanggal_ajukan' => now(),
        ], $atribut));
    }

    // ---------- Satuan kilogram & batas 1000 kg ----------

    public function test_satuan_menjadi_kilogram_dan_tidak_ada_lagi_liter_atau_gradasi(): void
    {
        $kolom = Schema::getColumnListing('setoran');

        $this->assertContains('perkiraan_kg', $kolom);
        $this->assertContains('minyak_kg', $kolom);
        $this->assertContains('endapan_kg', $kolom);

        // Satuan liter dan fitur yang dihapus tidak boleh ada lagi.
        $this->assertNotContains('perkiraan_liter', $kolom);
        $this->assertNotContains('liter_aktual', $kolom);
        $this->assertNotContains('kualitas', $kolom);
        $this->assertNotContains('bintang', $kolom);
        $this->assertNotContains('ulasan', $kolom);
    }

    public function test_harga_memiliki_kolom_minyak_dan_endapan(): void
    {
        $kolom = Schema::getColumnListing('harga');

        $this->assertContains('harga_minyak_kg', $kolom);
        $this->assertContains('harga_endapan_kg', $kolom);
        $this->assertNotContains('harga_per_liter', $kolom);
    }

    public function test_harga_aktif_membaca_kedua_jenis_harga(): void
    {
        $this->pasangHarga(12000, 4500);

        $this->assertSame(12000.0, Harga::hargaMinyak());
        $this->assertSame(4500.0, Harga::hargaEndapan());
    }

    public function test_harga_maksimum_dan_berat_maksimum_sesuai_ketentuan(): void
    {
        $this->assertSame(100000, Harga::MAKS_HARGA_MINYAK);
        $this->assertSame(1000, Harga::MAKS_KG);
    }

    // ---------- Perhitungan minyak + endapan ----------

    public function test_nilai_setoran_menjumlahkan_minyak_dan_endapan(): void
    {
        // 95 kg minyak x 10.000 = 950.000 ; 5 kg endapan x 5.000 = 25.000
        $hasil = Setoran::hitungNilai(95, 5, 10000, 5000);

        $this->assertSame(950000.0, $hasil['minyak']);
        $this->assertSame(25000.0, $hasil['endapan']);
        $this->assertSame(975000.0, $hasil['nilai']);
    }

    public function test_total_terima_menjumlahkan_keduanya(): void
    {
        $setoran = $this->setoran(['minyak_kg' => 95, 'endapan_kg' => 5]);

        $this->assertSame(100.0, $setoran->totalTerimaKg());
    }

    public function test_selisih_membandingkan_dengan_perkiraan_warga(): void
    {
        // Perkiraan 100 kg, diterima 95 + 5 = 100 kg -> selisih nol.
        $tepat = $this->setoran(['perkiraan_kg' => 100, 'minyak_kg' => 95, 'endapan_kg' => 5]);
        $this->assertSame(0.0, $tepat->selisihKg());

        // Perkiraan 100 kg, diterima 90 + 5 = 95 kg -> selisih -5 kg.
        $kurang = $this->setoran(['perkiraan_kg' => 100, 'minyak_kg' => 90, 'endapan_kg' => 5]);
        $this->assertSame(-5.0, $kurang->selisihKg());
    }

    public function test_pengepul_mengonfirmasi_minyak_dan_endapan_terpisah(): void
    {
        $this->pasangHarga(10000, 5000);

        $warga = $this->warga();
        $pengepul = $this->pengepul();

        $setoran = $this->setoran([
            'user_id' => $warga->id,
            'status' => 'diproses',
            'pengepul_id' => $pengepul->id,
        ]);

        $this->actingAs($pengepul)
            ->post(route('pengepul.verifikasi.store', $setoran), [
                'minyak_kg' => 95,
                'endapan_kg' => 5,
                'catatan_pengepul' => 'Endapan di dasar wadah.',
            ])
            ->assertRedirect(route('pengepul.dashboard'));

        $setoran->refresh();

        $this->assertSame('selesai', $setoran->status);
        $this->assertSame('95.00', $setoran->minyak_kg);
        $this->assertSame('5.00', $setoran->endapan_kg);
        $this->assertSame('975000.00', $setoran->total_saldo);
        $this->assertSame('975000.00', $warga->fresh()->saldo);
        $this->assertSame(1, Transaksi::where('tipe', 'setoran')->count());
    }

    public function test_admin_tidak_boleh_masih_bisa_ubah_harga_di_atas_batas(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.harga.update'), [
                'harga_minyak_kg' => 500000,
                'harga_endapan_kg' => 5000,
            ])
            ->assertSessionHasErrors('harga_minyak_kg');
    }

    public function test_admin_bisa_memperbarui_kedua_harga(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.harga.update'), [
                'harga_minyak_kg' => 12000,
                'harga_endapan_kg' => 4500,
            ])
            ->assertRedirect(route('admin.harga'));

        $this->assertSame(12000.0, Harga::hargaMinyak());
        $this->assertSame(4500.0, Harga::hargaEndapan());
    }

    // ---------- Batas input ----------

    public function test_warga_tidak_bisa_mengZXJ_lebih_dari_1000_kg(): void
    {
        $this->actingAs($this->warga())
            ->post(route('user.ajukan.store'), [
                'perkiraan_minyak_kg' => 1400,
                'perkiraan_endapan_kg' => 100,
                'alamat_jemput' => 'Jl. Mawar No. 1',
            ])
            ->assertSessionHasErrors('perkiraan_minyak_kg');

        $this->assertDatabaseCount('setoran', 0);
    }

    public function test_pengepul_tidak_bisa_menimbang_lebih_dari_1000_kg(): void
    {
        $this->pasangHarga();
        $pengepul = $this->pengepul();

        $setoran = $this->setoran(['status' => 'diproses', 'pengepul_id' => $pengepul->id]);

        $this->actingAs($pengepul)
            ->post(route('pengepul.verifikasi.store', $setoran), [
                'minyak_kg' => 800,
                'endapan_kg' => 400, // total 1200 kg, melebihi batas
            ])
            ->assertSessionHasErrors('minyak_kg');

        $this->assertSame('diproses', $setoran->fresh()->status);
    }

    public function test_pengepul_tidak_bisa_menimbang_nol_kosong(): void
    {
        $this->pasangHarga();
        $pengepul = $this->pengepul();

        $setoran = $this->setoran(['status' => 'diproses', 'pengepul_id' => $pengepul->id]);

        $this->actingAs($pengepul)
            ->post(route('pengepul.verifikasi.store', $setoran), [
                'minyak_kg' => 0,
                'endapan_kg' => 0,
            ])
            ->assertSessionHasErrors('minyak_kg');
    }

    // ---------- Buku besar & pencairan ----------

    public function test_kredit_saldo_mencatat_buku_besar(): void
    {
        $warga = $this->warga();

        Saldo::kredit($warga, 975000, 'setoran', 'Setoran uji', 1, 'setoran');

        $this->assertSame('975000.00', $warga->fresh()->saldo);

        $transaksi = Transaksi::first();
        $this->assertSame('setoran', $transaksi->tipe);
        $this->assertSame('0.00', $transaksi->saldo_sebelum);
        $this->assertSame('975000.00', $transaksi->saldo_sesudah);
    }

    public function test_saldo_tersedia_hanya_mengurangi_permintaan_menunggu(): void
    {
        $warga = $this->warga();
        Saldo::kredit($warga, 100000, 'setoran', 'Setoran uji');

        $this->assertSame(100000.0, $warga->saldoTersedia());

        Pencairan::create([
            'user_id' => $warga->id,
            'jumlah' => 40000,
            'metode' => 'ewallet',
            'tujuan' => '08123456789',
            'nama_penerima' => $warga->name,
            'status' => 'menunggu',
        ]);

        // 40rb sedang dipesan, jadi hanya 60rb yang bebas dicairkan.
        $this->assertSame(60000.0, $warga->fresh()->saldoTersedia());
    }

    public function test_admin_menolak_pencairan_di_atas_saldo(): void
    {
        $warga = $this->warga();
        $admin = $this->admin();

        $pencairan = Pencairan::create([
            'user_id' => $warga->id,
            'jumlah' => 50000,
            'metode' => 'ewallet',
            'tujuan' => '08123456789',
            'nama_penerima' => $warga->name,
            'status' => 'menunggu',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.pencairan.setujui', $pencairan))
            ->assertStatus(422);

        $this->assertSame('menunggu', $pencairan->fresh()->status);
    }

    public function test_admin_menyetujui_pencairan_dan_mengurangi_saldo(): void
    {
        $warga = $this->warga();
        $admin = $this->admin();
        Saldo::kredit($warga, 100000, 'setoran', 'Setoran uji');

        $pencairan = Pencairan::create([
            'user_id' => $warga->id,
            'jumlah' => 40000,
            'metode' => 'transfer',
            'tujuan' => '1234567890',
            'nama_penerima' => $warga->name,
            'status' => 'menunggu',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.pencairan.setujui', $pencairan))
            ->assertRedirect(route('admin.pencairan'));

        $this->assertSame('disetujui', $pencairan->fresh()->status);
        $this->assertSame('60000.00', $warga->fresh()->saldo);
        $this->assertSame(60000.0, $warga->fresh()->saldoTersedia());
    }

    public function test_permintaan_pencairan_menolak_jumlah_melebihi_saldo(): void
    {
        $warga = $this->warga();
        Saldo::kredit($warga, 20000, 'setoran', 'Setoran uji');

        $this->actingAs($warga)
            ->post(route('user.pencairan.store'), [
                'jumlah' => 25000,
                'metode' => 'ewallet',
                'tujuan' => '0812',
                'nama_penerima' => $warga->name,
            ])
            ->assertSessionHasErrors('jumlah');

        $this->assertSame(0, Pencairan::count());
    }

    // ---------- Rating sudah dihapus ----------

    public function test_tidak_ada_lagi_endpoint_penilaian(): void
    {
        // Rating dihapus total, jadi form penilaian tidak boleh tersedia.
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('user.nilai'));
    }

    // ---------- Akses berbasis peran ----------

    public function test_setiap_halaman_dasar_peran_dapat_dirender(): void
    {
        $this->pasangHarga();

        $warga = $this->warga();
        $pengepul = $this->pengepul();
        $admin = $this->admin();

        $setoranSelesai = $this->setoran([
            'user_id' => $warga->id,
            'status' => 'selesai',
            'pengepul_id' => $pengepul->id,
            'minyak_kg' => 95,
            'endapan_kg' => 5,
            'harga_minyak_saat_itu' => 10000,
            'harga_endapan_saat_itu' => 5000,
            'total_saldo' => 975000,
            'tanggal_selesai' => now(),
        ]);

        $setoranProses = $this->setoran([
            'user_id' => $warga->id,
            'status' => 'diproses',
            'pengepul_id' => $pengepul->id,
        ]);

        Saldo::kredit($warga, 100000, 'setoran', 'Setoran uji');

        $pencairan = Pencairan::create([
            'user_id' => $warga->id,
            'jumlah' => 25000,
            'metode' => 'ewallet',
            'tujuan' => '08123456789',
            'nama_penerima' => $warga->name,
            'status' => 'menunggu',
        ]);

        $halaman = [
            [$warga, 'user.dashboard'],
            [$warga, 'user.ajukan'],
            [$warga, 'user.riwayat'],
            [$warga, 'user.saldo'],
            [$warga, 'user.pencairan.create'],
            [$warga, 'user.detail', $setoranSelesai],
            [$warga, 'notifikasi.index'],
            [$warga, 'profile.edit'],
            [$warga, 'user.dashboard.data'],
            [$pengepul, 'pengepul.dashboard'],
            [$pengepul, 'pengepul.riwayat'],
            [$pengepul, 'pengepul.verifikasi', $setoranProses],
            [$pengepul, 'pengepul.menunggu'],
            [$pengepul, 'notifikasi.index'],
            [$pengepul, 'profile.edit'],
            [$admin, 'admin.dashboard'],
            [$admin, 'admin.dashboard.data'],
            [$admin, 'admin.pengguna'],
            [$admin, 'admin.pengguna.edit', $warga],
            [$admin, 'admin.harga'],
            [$admin, 'admin.pencairan'],
            [$admin, 'admin.saldo', $warga],
            [$admin, 'admin.laporan'],
            [$admin, 'admin.laporan.unduh'],
            [$admin, 'notifikasi.index'],
            [$admin, 'profile.edit'],
        ];

        foreach ($halaman as $item) {
            $pengguna = $item[0];
            $namaRute = $item[1];
            $argumen = $item[2] ?? null;

            $respons = $this->actingAs($pengguna)->get(route($namaRute, $argumen !== null ? [$argumen] : []));

            $this->assertSame(200, $respons->getStatusCode(), "Halaman {$namaRute} gagal dirender.");
        }
    }

    public function test_pencarian_globus_berfungsi(): void
    {
        $this->pasangHarga();
        $admin = $this->admin();
        $warga = $this->warga();

        $this->setoran(['user_id' => $warga->id, 'alamat_jemput' => 'Jl. Mawar No. 12']);

        $respons = $this->actingAs($admin)->getJson(route('pencarian', ['q' => 'Mawar']));
        $respons->assertOk();

        $hasil = $respons->json('results');
        $this->assertNotEmpty($hasil);
        $this->assertStringContainsString('Mawar', $hasil[0]['detail']);

        // Pencarian terlalu pendek tidak searching.
        $this->actingAs($admin)->getJson(route('pencarian', ['q' => 'a']))
            ->assertOk()
            ->assertJsonPath('results', []);
    }

    public function test_halaman_dashboard_bisa_diakses_semua_peran(): void
    {
        $this->pasangHarga();

        $this->actingAs($this->warga())->get(route('user.dashboard'))->assertOk();
        $this->actingAs($this->pengepul())->get(route('pengepul.dashboard'))->assertOk();
        $this->actingAs($this->admin())->get(route('admin.dashboard'))->assertOk();
    }

    public function test_peran_tidak_bisa_membuka_halaman_peran_lain(): void
    {
        $this->actingAs($this->warga())->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($this->warga())->get(route('pengepul.dashboard'))->assertForbidden();
        $this->actingAs($this->pengepul())->get(route('admin.laporan'))->assertForbidden();
        $this->actingAs($this->admin())->get(route('user.saldo'))->assertForbidden();
    }

    public function test_admin_tidak_bisa_mengubah_kata_sandi_lewat_halaman_edit(): void
    {
        $admin = $this->admin();
        $warga = $this->warga();

        $sandiAwal = $warga->password;

        // Field password dikirim manual, seolah-olah form masih punya kolom itu.
        $this->actingAs($admin)
            ->put(route('admin.pengguna.update', $warga), [
                'name' => $warga->name,
                'email' => $warga->email,
                'role' => 'user',
                'password' => 'KataSandiBaru123',
            ])
            ->assertRedirect(route('admin.pengguna'));

        $warga->refresh();

        // Data lain tetap tersimpan, tapi kata sandi harus tidak berubah.
        $this->assertSame($sandiAwal, $warga->password, 'Kata sandi tidak boleh berubah lewat edit.');
        $this->assertTrue(
            \Illuminate\Support\Facades\Hash::check('password', $warga->password),
            'Kata sandi asli harus tetap berlaku.'
        );
    }

    public function test_form_edit_pengguna_tidak_menampilkan_kolom_kata_sandi(): void
    {
        $halaman = $this->actingAs($this->admin())
            ->get(route('admin.pengguna.edit', $this->warga()));

        $halaman->assertOk();
        $halaman->assertDontSee('name="password"', false);
        $halaman->assertDontSee('Kata Sandi Baru');
    }
    public function test_harga_pertama_kali_selalu_boleh_diubah(): void
    {
        $this->assertFalse(Harga::dalamJeda());
        $this->assertSame(0, Harga::sisaHari());
        $this->assertNull(Harga::bolehUbahPada());
    }

    public function test_harga_ditolak_perubahan_kurang_dari_tujuh_hari(): void
    {
        $this->pasangHarga(10000, 5000);

        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.harga.update'), [
                'harga_minyak_kg' => 15000,
                'harga_endapan_kg' => 6000,
            ])
            ->assertRedirect(route('admin.harga'))
            ->assertSessionHas('error');

        // Harga lama harus tetap berlaku.
        $this->assertSame(10000.0, Harga::hargaMinyak());
        $this->assertSame(5000.0, Harga::hargaEndapan());
    }

    public function test_harga_boleh_diubah_setelah_tujuh_hari(): void
    {
        $ini = $this->pasangHarga(10000, 5000);
        $ini->forceFill(['created_at' => now()->subDays(8)])->save();

        $this->assertFalse(Harga::dalamJeda());
        $this->assertSame(0, Harga::sisaHari());

        $this->actingAs($this->admin())
            ->post(route('admin.harga.update'), [
                'harga_minyak_kg' => 15000,
                'harga_endapan_kg' => 6000,
            ])
            ->assertRedirect(route('admin.harga'))
            ->assertSessionHas('success');

        $this->assertSame(15000.0, Harga::hargaMinyak());
        $this->assertSame(6000.0, Harga::hargaEndapan());
    }

    public function test_sisa_hari_menurun_saat_mendekati_jeda_berakhir(): void
    {
        $ini = $this->pasangHarga();
        $ini->forceFill(['created_at' => now()->subDays(5)])->save();

        $this->assertTrue(Harga::dalamJeda());
        $this->assertSame(2, Harga::sisaHari());

        $boleh = Harga::bolehUbahPada();
        $this->assertNotNull($boleh);
        $this->assertEqualsWithDelta(
            now()->addDays(2)->getTimestamp(),
            $boleh->getTimestamp(),
            60
        );
    }

    public function test_halaman_harga_menampilkan_kunci_saat_dalam_jeda(): void
    {
        $ini = $this->pasangHarga();
        $ini->forceFill(['created_at' => now()->subDays(2)])->save();

        $halaman = $this->actingAs($this->admin())->get(route('admin.harga'));

        $halaman->assertOk();
        $halaman->assertSee('Harga sedang dikunci');
        $halaman->assertSee('5 hari', false);
        $halaman->assertSee('Harga Terkunci');
    }

    public function test_harga_menerima_angka_rupiah_ganjil(): void
    {
        // Nilai bebas dalam rupiah, bukan hanya kelipatan tertentu.
        $this->actingAs($this->admin())
            ->post(route('admin.harga.update'), [
                'harga_minyak_kg' => 9999,
                'harga_endapan_kg' => 4999,
            ])
            ->assertRedirect(route('admin.harga'));

        $this->assertSame(9999.0, Harga::hargaMinyak());
        $this->assertSame(4999.0, Harga::hargaEndapan());
    }
    public function test_kunci_harga_aktif_secara_bawaan(): void
    {
        $this->assertTrue(Harga::jedaAktif());
        $this->assertTrue(Pengaturan::hargaTerkunci());
    }

    public function test_admin_tidak_bisa_membuka_kunci_harga_sendiri(): void
    {
        $this->actingAs($this->warga())
            ->post(route('admin.harga.kunci'))
            ->assertForbidden();

        $this->assertTrue(Pengaturan::hargaTerkunci(), 'Warga tidak boleh mengubah status kunci.');
    }

    public function test_membuka_kunci_harga_membolehkan_perubahan_langsung(): void
    {
        $this->pasangHarga(10000, 5000);

        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.harga.kunci'))
            ->assertRedirect(route('admin.harga'));

        $this->assertFalse(Pengaturan::hargaTerkunci());
        $this->assertFalse(Harga::dalamJeda());
        $this->assertNull(Harga::bolehUbahPada());

        // Harga bisa langsung diubah, meski jeda 7 hari belum lewat.
        $this->actingAs($admin)
            ->post(route('admin.harga.update'), [
                'harga_minyak_kg' => 12345,
                'harga_endapan_kg' => 5555,
            ])
            ->assertRedirect(route('admin.harga'))
            ->assertSessionHas('success');

        $this->assertSame(12345.0, Harga::hargaMinyak());
        $this->assertSame(5555.0, Harga::hargaEndapan());
    }

    public function test_mengunci_kembali_mengaktifkan_aturan_jeda(): void
    {
        $ini = $this->pasangHarga(10000, 5000);
        $ini->forceFill(['created_at' => now()])->save();

        $admin = $this->admin();

        // Buka dulu supaya bisa mengubah harga.
        $this->actingAs($admin)->post(route('admin.harga.kunci'));

        $this->actingAs($admin)->post(route('admin.harga.update'), [
            'harga_minyak_kg' => 13000,
            'harga_endapan_kg' => 5000,
        ])->assertSessionHas('success');

        // Kunci lagi, jeda harus berlaku untuk perubahan berikutnya.
        $this->actingAs($admin)->post(route('admin.harga.kunci'));
        $this->assertTrue(Pengaturan::hargaTerkunci());

        $this->actingAs($admin)
            ->post(route('admin.harga.update'), [
                'harga_minyak_kg' => 20000,
                'harga_endapan_kg' => 5000,
            ])
            ->assertSessionHas('error');

        $this->assertSame(13000.0, Harga::hargaMinyak());
    }

    public function test_halaman_harga_menampilkan_sakelar(): void
    {
        // Ajakan membuka kunci hanya muncul selama jeda masih berjalan,
        // jadi harus ada harga yang baru saja dibuat lebih dulu.
        $this->pasangHarga();

        $halaman = $this->actingAs($this->admin())->get(route('admin.harga'));

        $halaman->assertOk();
        $halaman->assertSee('Kunci Harga: AKTIF');
        $halaman->assertSee('Buka Kunci Harga');
        $halaman->assertSee(route('admin.harga.kunci'), false);
    }
    public function test_tamu_diarahkan_ke_halaman_masuk(): void
    {
        $this->get(route('user.dashboard'))->assertRedirect(route('login'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_redirect_setelah_login_sesuai_peran(): void
    {
        $this->actingAs($this->warga())->get(route('dashboard'))->assertRedirect(route('user.dashboard'));
        $this->actingAs($this->pengepul())->get(route('dashboard'))->assertRedirect(route('pengepul.dashboard'));
        $this->actingAs($this->admin())->get(route('dashboard'))->assertRedirect(route('admin.dashboard'));
    }

    // ---------- Pengajuan ----------

    public function test_warga_mengajukan_setoran_dalam_kilogram(): void
    {
        $this->pasangHarga();
        $warga = $this->warga();

        $this->actingAs($warga)
            ->post(route('user.ajukan.store'), [
                'perkiraan_minyak_kg' => 95,
                'perkiraan_endapan_kg' => 5,
                'alamat_jemput' => 'Jl. Kenari No. 7',
                'catatan' => 'Pagi sebelum jam 9.',
            ])
            ->assertRedirect(route('user.dashboard'));

        $this->assertDatabaseHas('setoran', [
            'user_id' => $warga->id,
            'perkiraan_kg' => 100,
            'status' => 'menunggu',
        ]);
    }

    public function test_pengajuan_menyimpan_rincian_perkiraan_minyak_dan_endapan(): void
    {
        $this->pasangHarga(10000, 5000);
        $warga = $this->warga();

        $this->actingAs($warga)
            ->post(route('user.ajukan.store'), [
                'perkiraan_minyak_kg' => 95,
                'perkiraan_endapan_kg' => 5,
                'alamat_jemput' => 'Jl. Kenari No. 7',
            ])
            ->assertRedirect(route('user.dashboard'));

        $setoran = Setoran::where('user_id', $warga->id)->latest('id')->first();

        $this->assertSame('95.00', $setoran->perkiraan_minyak_kg);
        $this->assertSame('5.00', $setoran->perkiraan_endapan_kg);

        // Total perkiraan tetap disimpan supaya laporan lama tidak berubah.
        $this->assertSame('100.00', $setoran->perkiraan_kg);
        $this->assertSame(95.0, $setoran->perkiraanMinyakKg());
        $this->assertSame(5.0, $setoran->perkiraanEndapanKg());
        $this->assertSame(100.0, $setoran->perkiraanTotalKg());
    }

    public function test_total_perkiraan_tidak_boleh_melebihi_1000_kg(): void
    {
        // Masing-masing di bawah batas, tapi gabungannya 1.200 kg.
        $this->actingAs($this->warga())
            ->post(route('user.ajukan.store'), [
                'perkiraan_minyak_kg' => 600,
                'perkiraan_endapan_kg' => 600,
                'alamat_jemput' => 'Jl. Kenari No. 7',
            ])
            ->assertSessionHasErrors('perkiraan_minyak_kg');

        $this->assertDatabaseCount('setoran', 0);
    }

    public function test_kedua_perkiraan_wajib_diisi(): void
    {
        $this->actingAs($this->warga())
            ->post(route('user.ajukan.store'), [
                'alamat_jemput' => 'Jl. Kenari No. 7',
            ])
            ->assertSessionHasErrors(['perkiraan_minyak_kg', 'perkiraan_endapan_kg']);

        $this->assertDatabaseCount('setoran', 0);
    }

    public function test_endapan_nol_diterima_untuk_minyak_murni(): void
    {
        $this->actingAs($this->warga())
            ->post(route('user.ajukan.store'), [
                'perkiraan_minyak_kg' => 20,
                'perkiraan_endapan_kg' => 0,
                'alamat_jemput' => 'Jl. Kenari No. 7',
            ])
            ->assertRedirect(route('user.dashboard'));

        $setoran = Setoran::latest('id')->first();

        $this->assertSame('20.00', $setoran->perkiraan_minyak_kg);
        $this->assertSame('0.00', $setoran->perkiraan_endapan_kg);
        $this->assertSame(20.0, $setoran->perkiraanTotalKg());
    }

    public function test_data_lama_dianggap_minyak_sepenuhnya(): void
    {
        // Setoran lama hanya punya satu angka total, tanpa rincian.
        $setoran = $this->setoran([
            'perkiraan_kg' => 100,
            'perkiraan_minyak_kg' => null,
            'perkiraan_endapan_kg' => null,
        ]);

        $this->assertSame(100.0, $setoran->perkiraanMinyakKg());
        $this->assertSame(0.0, $setoran->perkiraanEndapanKg());
        $this->assertSame(100.0, $setoran->perkiraanTotalKg());
    }

    public function test_pengajuan_menolak_berat_kosong(): void
    {
        $this->actingAs($this->warga())
            ->post(route('user.ajukan.store'), [
                'perkiraan_minyak_kg' => 0,
                'perkiraan_endapan_kg' => 0,
                'alamat_jemput' => 'Jl. Kenari No. 7',
            ])
            ->assertSessionHasErrors('perkiraan_minyak_kg');

        $this->assertDatabaseCount('setoran', 0);
    }

    // ---------- Login & pesan error ----------

    public function test_login_memberi_pesan_khusus_saat_email_belum_terdaftar(): void
    {
        $this->post(route('login'), [
            'email' => 'tidak-ada@example.com',
            'password' => 'apapun123',
        ])->assertSessionHasErrors('email');

        $pesan = session('errors')->first('email');
        $this->assertStringContainsString('belum terdaftar', $pesan);
    }

    public function test_login_memberi_pesan_khusus_saat_kata_sandi_salah(): void
    {
        $user = $this->warga();

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'salah-sekali',
        ])->assertSessionHasErrors('password');

        $this->assertStringContainsString('Kata sandi salah', session('errors')->first('password'));
    }

    public function test_login_berhasil_dengan_kata_sandi_benar(): void
    {
        $user = User::factory()->create(['role' => 'user', 'password' => 'rahasia123']);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'rahasia123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_halaman_lupa_password_menampilkan_pesan_bila_email_tidak_terdaftar(): void
    {
        $this->post(route('password.email'), ['email' => 'tidak-ada@example.com'])
            ->assertSessionHasErrors('email');

        $this->assertStringContainsString('belum terdaftar', session('errors')->first('email'));
    }

    public function test_halaman_lupa_password_menerima_email_terdaftar(): void
    {
        \Illuminate\Support\Facades\Notification::fake();

        $user = $this->warga();

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status');

        \Illuminate\Support\Facades\Notification::assertSentTo($user, \App\Notifications\ResetPassword::class);
    }

    public function test_halaman_lupa_password_menampilkan_kembali_email_yang_diketik(): void
    {
        $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => 'tidak-ada@example.com'])
            ->assertRedirect(route('password.request'))
            ->assertSessionHasInput('email');
    }

    // ---------- Landing page hanya untuk tamu ----------

    public function test_tamu_bisa_membuka_landing_page_dan_menemukan_pilihan_masuk_daftar(): void
    {
        $this->pasangHarga();

        $respons = $this->get(route('home'));

        $respons->assertOk();
        $respons->assertSee(route('login'));
        $respons->assertSee(route('register'));
        $respons->assertSee('Masuk');
        $respons->assertSee('Daftar');
    }

    public function test_yang_sudah_login_diarahkan_ke_dashboard_bukan_landing_page(): void
    {
        $warga = $this->warga();
        $pengepul = $this->pengepul();
        $admin = $this->admin();

        $this->actingAs($warga)->get(route('home'))->assertRedirect(route('dashboard'));
        $this->actingAs($pengepul)->get(route('home'))->assertRedirect(route('dashboard'));
        $this->actingAs($admin)->get(route('home'))->assertRedirect(route('dashboard'));
    }

    public function test_setelah_diarahkan_dashboard_mengikuti_peran_masing_masing(): void
    {
        $pengguna = [
            [$this->warga(), 'user.dashboard'],
            [$this->pengepul(), 'pengepul.dashboard'],
            [$this->admin(), 'admin.dashboard'],
        ];

        foreach ($pengguna as [$akun, $dashboardPeran]) {
            $this->actingAs($akun)->get(route('home'))->assertRedirect(route('dashboard'));
            $this->actingAs($akun)->get(route('dashboard'))->assertRedirect(route($dashboardPeran));
        }
    }

    public function test_navbar_tidak_menawarkan_beranda_kepada_yang_sudah_login(): void
    {
        $this->pasangHarga();

        $tamu = $this->get(route('home'));
        $tamu->assertOk();
        $tamu->assertSee('href="' . route('home') . '"', false);

        $sudahLogin = $this->actingAs($this->warga())->get(route('user.dashboard'));
        $sudahLogin->assertOk();
        $sudahLogin->assertDontSee('href="' . route('home') . '"', false);
        $sudahLogin->assertSee(route('dashboard'));
    }

    public function test_halaman_auth_juga_menolak_yang_sudah_login(): void
    {
        $warga = $this->warga();

        foreach (['login', 'register', 'password.request'] as $namaRute) {
            $this->actingAs($warga)
                ->get(route($namaRute))
                ->assertRedirect(route('dashboard'));
        }
    }

    // ---------- Laporan & ekspor CSV ----------

    public function test_ekspor_csv_memiliki_kolom_minyak_dan_endapan(): void
    {
        $this->pasangHarga(10000, 5000);
        $admin = $this->admin();

        $this->setoran([
            'status' => 'selesai',
            'minyak_kg' => 95,
            'endapan_kg' => 5,
            'harga_minyak_saat_itu' => 10000,
            'harga_endapan_saat_itu' => 5000,
            'total_saldo' => 975000,
            'tanggal_selesai' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.laporan.unduh'));
        $response->assertOk();

        $isi = $response->streamedContent();

        $this->assertStringContainsString('Minyak (kg)', $isi);
        $this->assertStringContainsString('Endapan (kg)', $isi);
        $this->assertStringContainsString('Total Bayar', $isi);
        $this->assertStringContainsString('REKAP', $isi);
    }

    public function test_angka_pada_csv_ditulis_polos_agar_bisa_dijumlahkan_excel(): void
    {
        $this->pasangHarga(10000, 5000);
        $admin = $this->admin();

        $this->setoran([
            'status' => 'selesai',
            'minyak_kg' => 95,
            'endapan_kg' => 5,
            'harga_minyak_saat_itu' => 10000,
            'harga_endapan_saat_itu' => 5000,
            'total_saldo' => 975000,
            'tanggal_selesai' => now(),
        ]);

        $isi = $this->actingAs($admin)->get(route('admin.laporan.unduh'))->streamedContent();

        // Tidak boleh ada pemisah ribuan gaya Indonesia di dalam angka.
        $this->assertStringNotContainsString('975.000', $isi);
        $this->assertStringContainsString('975000', $isi);
    }

    // ---------- Penjaga nama kolom ----------

    /**
     * SQLite tidak mengeluh kalau ORDER BY memakai kolom yang tidak dikenal:
     * identifier itu diperlakukan sebagai konstanta string, jadi urutannya
     * salah tanpa error. MySQL justru menolak. Test ini memeriksa nama kolom
     * secara statis supaya salah ketik ketahuan lebih awal.
     */
    public function test_nama_kolom_dalam_query_sesuai_skema(): void
    {
        $kolomValid = ['id' => true];

        foreach (Schema::getTables() as $tabel) {
            $nama = is_array($tabel) ? ($tabel['name'] ?? null) : ($tabel->name ?? null);
            if (! $nama) {
                continue;
            }
            foreach (Schema::getColumns($nama) as $kolom) {
                $kolomValid[is_array($kolom) ? $kolom['name'] : $kolom->name] = true;
            }
        }

        $metode = [
            'latest', 'oldest', 'orderBy', 'orderByDesc', 'orderByRaw',
            'where', 'whereNot', 'orWhere', 'whereIn', 'whereNotIn', 'whereNull',
            'whereNotNull', 'whereBetween', 'whereDate', 'whereMonth', 'whereYear',
            'whereTime', 'whereDay', 'sum', 'avg', 'min', 'max', 'count',
            'groupBy', 'value', 'firstWhere', 'pluck',
        ];
        $pola = '/\b(?:' . implode('|', $metode) . ')\(\s*\'([a-z_][a-z0-9_]*)\'/i';

        $kemungkinan = [];
        $alias = [];
        $berkas = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(app_path()));

        foreach ($berkas as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $isi = file_get_contents($file->getPathname());

            // Alias dari selectRaw (mis. "as tanggal") juga sah dipakai
            // di groupBy/pluck, jadi ikut dikumpulkan.
            preg_match_all('/\bas\s+([a-z_][a-z0-9_]*)/i', $isi, $cocokAlias);
            foreach ($cocokAlias[1] as $nama) {
                $alias[$nama] = true;
            }

            preg_match_all($pola, $isi, $cocok, PREG_SET_ORDER);
            foreach ($cocok as $temu) {
                $nama = $temu[1];

                if (! isset($kolomValid[$nama])) {
                    $kemungkinan[$nama][] = $file->getFilename();
                }
            }
        }

        // Buang nama yang sebenarnya cuma alias selectRaw.
        foreach (array_keys($alias) as $nama) {
            unset($kemungkinan[$nama]);
        }

        $sisa = [];
        foreach ($kemungkinan as $nama => $berkasDipakai) {
            $sisa[] = $nama . ' -> ' . implode(', ', array_unique($berkasDipakai));
        }
        sort($sisa);

        $this->assertSame(
            [],
            $sisa,
            "Ada nama kolom pada query yang tidak ada di skema:\n" . implode("\n", $sisa)
        );
    }

    public function test_pengurutan_setoran_memakai_tanggal_ajukan(): void
    {
        $wargaId = $this->warga()->id;

        $lama = $this->setoran(['user_id' => $wargaId, 'tanggal_ajukan' => now()->subDays(3)]);
        $tengah = $this->setoran(['user_id' => $wargaId, 'tanggal_ajukan' => now()->subDay()]);
        $baru = $this->setoran(['user_id' => $wargaId, 'tanggal_ajukan' => now()]);

        $ids = Setoran::latest('tanggal_ajukan')->pluck('id')->all();

        $this->assertSame(
            [$baru->id, $tengah->id, $lama->id],
            array_slice($ids, 0, 3),
            'Setoran harus urut dari tanggal paling baru.'
        );
    }
}
