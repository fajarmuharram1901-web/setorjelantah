<?php

namespace App\Http\Controllers;

use App\Models\Harga;
use App\Models\Pengaturan;
use App\Models\Pencairan;
use App\Models\Setoran;
use App\Models\Transaksi;
use App\Models\User;
use App\Support\Saldo;
use App\Support\AturanValidasi;
use App\Support\Statistik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // GET /admin/dashboard
    public function dashboard()
    {
        return view('admin.dashboard', $this->dataDashboard());
    }

    // GET /admin/dashboard/data (AJAX polling)
    public function dashboardData()
    {
        $html = view('admin.partials.dashboard-content', $this->dataDashboard())->render();

        return response()->json(['html' => $html]);
    }

    // GET /admin/pengguna
    public function kelolaUser(Request $request)
    {
        $cari = trim((string) $request->query('cari', '')) ?: null;
        $peran = $request->query('peran');
        $peran = in_array($peran, ['user', 'pengepul', 'admin'], true) ? $peran : null;

        $pengguna = User::query()
            ->when($cari, function ($q) use ($cari) {
                $q->where(function ($inner) use ($cari) {
                    $inner->where('name', 'like', '%' . $cari . '%')
                        ->orWhere('email', 'like', '%' . $cari . '%');
                });
            })
            ->when($peran, fn ($q) => $q->where('role', $peran))
            ->orderByRaw("CASE role WHEN 'admin' THEN 0 WHEN 'pengepul' THEN 1 ELSE 2 END")
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.kelola_user', [
            'pengguna' => $pengguna,
            'cari' => $cari,
            'peran' => $peran,
            'totalUser' => User::where('role', 'user')->count(),
            'totalPengepul' => User::where('role', 'pengepul')->count(),
            'pengepulList' => User::where('role', 'pengepul')->orderBy('name')->get(),
        ]);
    }

    // POST /admin/pengguna/pengepul
    public function tambahPengepul(Request $request)
    {
        $validasi = AturanValidasi::siap('tambahPengepul');
        $data = $request->validate($validasi['aturan'], $validasi['pesan']);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'no_hp' => $data['no_hp'] ?? null,
            'role' => 'pengepul',
        ]);

        return redirect()->route('admin.pengguna')->with('success', 'Akun pengepul berhasil dibuat.');
    }

    // DELETE /admin/pengguna/{user}
    public function hapusUser(Request $request, User $user)
    {
        abort_if($user->isAdmin(), 422, 'Akun admin tidak dapat dihapus.');
        abort_if($user->id === $request->user()->id, 422, 'Anda tidak dapat menghapus akun sendiri.');

        $user->delete();

        return redirect()->route('admin.pengguna')->with('success', 'Akun berhasil dihapus.');
    }

    // GET /admin/pengguna/{user}/edit
    public function editUser(User $user)
    {
        if ($user->isAdmin()) {
            return redirect()->route('admin.pengguna')->with('error', 'Akun admin tidak dapat diubah.');
        }

        return view('admin.edit_user', ['user' => $user]);
    }

    // PUT /admin/pengguna/{user}
    public function updateUser(Request $request, User $user)
    {
        if ($user->isAdmin()) {
            return redirect()->route('admin.pengguna')->with('error', 'Akun admin tidak dapat diubah.');
        }

        $validasi = AturanValidasi::siap('editPengguna', ['id' => $user->id]);
        $data = $request->validate($validasi['aturan'], $validasi['pesan']);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'no_hp' => $data['no_hp'] ?? null,
            'alamat' => $data['alamat'] ?? null,
            'role' => $data['role'],
        ]);

        return redirect()->route('admin.pengguna')->with('success', 'Akun berhasil diperbarui.');
    }

    // GET /admin/harga
    public function harga()
    {
        return view('admin.harga', [
            'hargaMinyak' => Harga::hargaMinyak(),
            'hargaEndapan' => Harga::hargaEndapan(),
            'maksHarga' => Harga::MAKS_HARGA_MINYAK,
            'riwayatHarga' => Harga::with('admin')->latest('id')->take(10)->get(),
            'sisaHari' => Harga::sisaHari(),
            'bolehUbahPada' => Harga::bolehUbahPada(),
            'jedaAktif' => Harga::jedaAktif(),
        ]);
    }

    // POST /admin/harga/kunci - nyalakan atau matikan aturan jeda 7 hari
    public function toggleKunciHarga()
    {
        $sekarang = Harga::jedaAktif();

        Pengaturan::setHargaTerkunci(! $sekarang);

        return redirect()->route('admin.harga')->with(
            $sekarang ? 'success' : 'error',
            $sekarang
                ? 'Kunci harga dibuka. Harga bisa diubah kapan saja sampai Anda mengunci lagi.'
                : 'Kunci harga aktif. Perubahan harga dibatasi sekali dalam '
                    . Harga::JEDA_HARGA_HARI . ' hari.'
        );
    }

    // POST /admin/harga
    public function updateHarga(Request $request)
    {
        // Harga hanya boleh diubah sekali dalam 7 hari supaya nilai yang
        // sudah tercatat di setoran lama tidak terasa berubah-ubah.
        if (Harga::dalamJeda()) {
            $boleh = Harga::bolehUbahPada();

            return redirect()->route('admin.harga')->with(
                'error',
                'Harga belum bisa diubah. Perubahan minimal berjarak '
                    . Harga::JEDA_HARGA_HARI . ' hari, berikutnya bisa dilakukan pada '
                    . $boleh->translatedFormat('d F Y \p\u\k H:i') . ' WIB.'
            );
        }

        $validasi = AturanValidasi::siap('updateHarga');
        $data = $request->validate($validasi['aturan'], $validasi['pesan']);

        Harga::create([
            'harga_minyak_kg' => $data['harga_minyak_kg'],
            'harga_endapan_kg' => $data['harga_endapan_kg'],
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.harga')->with(
            'success',
            'Harga minyak dan endapan berhasil diperbarui. Perubahan berikutnya bisa dilakukan '
                . Harga::JEDA_HARGA_HARI . ' hari lagi.'
        );
    }

    // GET /admin/pencairan
    public function pencairan(Request $request)
    {
        $status = $request->query('status');
        $status = in_array($status, ['menunggu', 'disetujui', 'ditolak'], true) ? $status : null;

        $daftar = Pencairan::with(['user', 'admin'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest('created_at')
            ->paginate(12)
            ->withQueryString();

        return view('admin.pencairan', [
            'daftar' => $daftar,
            'status' => $status,
            'jumlahMenunggu' => Pencairan::menunggu()->count(),
            'totalMenunggu' => (float) Pencairan::menunggu()->sum('jumlah'),
            'totalDisetujui' => (float) Pencairan::where('status', 'disetujui')->sum('jumlah'),
            'saldoBerjalan' => (float) User::where('role', 'user')->sum('saldo'),
        ]);
    }

    // POST /admin/pencairan/{pencairan}/setujui
    public function setujuiPencairan(Request $request, Pencairan $pencairan)
    {
        abort_unless($pencairan->status === 'menunggu', 422, 'Permintaan ini sudah pernah diproses.');

        DB::transaction(function () use ($pencairan, $request) {
            $user = User::whereKey($pencairan->user_id)->lockForUpdate()->firstOrFail();

            abort_if(
                (float) $user->saldo < (float) $pencairan->jumlah,
                422,
                'Saldo pengguna tidak mencukupi untuk pencairan ini.'
            );

            Saldo::debit(
                user: $user,
                jumlah: (float) $pencairan->jumlah,
                tipe: 'pencairan',
                keterangan: 'Pencairan ' . $pencairan->kode . ' - ' . $pencairan->labelMetode(),
                referensiId: (int) $pencairan->id,
                referensiTipe: 'pencairan',
            );

            $pencairan->update([
                'status' => 'disetujui',
                'diproses_oleh' => $request->user()->id,
                'diproses_pada' => now(),
            ]);
        });

        return redirect()->route('admin.pencairan')->with('success', 'Pencairan ' . $pencairan->kode . ' disetujui dan saldo sudah dipotong.');
    }

    // POST /admin/pencairan/{pencairan}/tolak
    public function tolakPencairan(Request $request, Pencairan $pencairan)
    {
        abort_unless($pencairan->status === 'menunggu', 422, 'Permintaan ini sudah pernah diproses.');

        $validasi = AturanValidasi::siap('tolakPencairan');
        $data = $request->validate($validasi['aturan'], $validasi['pesan']);

        $pencairan->update([
            'status' => 'ditolak',
            'alasan_tolak' => $data['alasan_tolak'],
            'diproses_oleh' => $request->user()->id,
            'diproses_pada' => now(),
        ]);

        return redirect()->route('admin.pencairan')->with('success', 'Pencairan ' . $pencairan->kode . ' ditolak.');
    }

    // GET /admin/saldo/{user}
    public function detailSaldo(User $user)
    {
        return view('admin.detail_saldo', [
            'user' => $user,
            'transaksi' => $user->transaksi()->latest('id')->paginate(15),
            'pencairans' => $user->pencairans()->latest('id')->take(5)->get(),
        ]);
    }

    // ---------- Private ----------

    /**
     * @return array<string, mixed>
     */
    private function dataDashboard(): array
    {
        $bermassa = Statistik::totalBermassa();

        return [
            'totalUser' => User::where('role', 'user')->count(),
            'totalPengepul' => User::where('role', 'pengepul')->count(),
            'menunggu' => Setoran::menunggu()->count(),
            'diproses' => Setoran::diproses()->count(),
            'totalMinyak' => $bermassa['minyak'],
            'totalEndapan' => $bermassa['endapan'],
            'totalKg' => $bermassa['total'],
            'totalDisalurkan' => $bermassa['nilai'],
            'hargaMinyak' => Harga::hargaMinyak(),
            'hargaEndapan' => Harga::hargaEndapan(),
            'terbaru' => Setoran::with(['user', 'pengepul'])->latest('tanggal_ajukan')->take(8)->get(),
            'deret' => Statistik::deretHarian(14),
            'ringkasan' => Statistik::ringkasan(14),
            'donut' => Statistik::donutStatus(),
            'topWarga' => Statistik::topWarga(5),
            'topPengepul' => Statistik::topPengepul(5),
            'jumlahPencairan' => Pencairan::menunggu()->count(),
            'nilaiPencairan' => (float) Pencairan::menunggu()->sum('jumlah'),
            'saldoSeluruhWarga' => (float) User::where('role', 'user')->sum('saldo'),
            'jumlahTransaksi' => Transaksi::count(),
        ];
    }
}