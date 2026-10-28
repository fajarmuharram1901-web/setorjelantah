<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PencairanResource;
use App\Http\Resources\SetoranResource;
use App\Http\Resources\TransaksiResource;
use App\Http\Resources\UserResource;
use App\Models\Harga;
use App\Models\Pengaturan;
use App\Models\Pencairan;
use App\Models\Setoran;
use App\Models\Transaksi;
use App\Models\User;
use App\Support\ApiData;
use App\Support\AturanValidasi;
use App\Support\Saldo;
use App\Support\Statistik;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Endpoint khusus administrator. */
class AdminController extends Controller
{
    // GET /api/admin/dashboard
    public function dashboard(): JsonResponse
    {
        $bermassa = Statistik::totalBermassa();

        return response()->json([
            'ringkasan' => [
                'total_user' => User::where('role', 'user')->count(),
                'total_pengepul' => User::where('role', 'pengepul')->count(),
                'menunggu' => Setoran::menunggu()->count(),
                'diproses' => Setoran::diproses()->count(),
                'total_minyak_kg' => $bermassa['minyak'],
                'total_endapan_kg' => $bermassa['endapan'],
                'total_kg' => $bermassa['total'],
                'total_disalurkan' => $bermassa['nilai'],
                'jumlah_pencairan_menunggu' => Pencairan::menunggu()->count(),
                'nilai_pencairan_menunggu' => (float) Pencairan::menunggu()->sum('jumlah'),
                'saldo_seluruh_warga' => (float) User::where('role', 'user')->sum('saldo'),
                'jumlah_transaksi' => Transaksi::count(),
            ],
            'harga' => [
                'minyak' => Harga::hargaMinyak(),
                'endapan' => Harga::hargaEndapan(),
            ],
            'deret' => Statistik::deretHarian(14),
            'ringkasan_periode' => Statistik::ringkasan(14),
            'donut' => Statistik::donutStatus(),
            'top_warga' => Statistik::topWarga(5)->map(fn ($row) => [
                'user_id' => $row->user_id,
                'nama' => $row->nama,
                'jumlah' => (int) $row->jumlah,
                'kg' => round((float) $row->kg, 2),
                'nilai' => round((float) $row->nilai, 2),
            ]),
            'top_pengepul' => Statistik::topPengepul(5)->map(fn ($row) => [
                'pengepul_id' => $row->pengepul_id,
                'nama' => $row->nama,
                'jumlah' => (int) $row->jumlah,
                'kg' => round((float) $row->kg, 2),
                'nilai' => round((float) $row->nilai, 2),
            ]),
            'setoran_terbaru' => ApiData::polos(SetoranResource::collection(
                Setoran::with(['user', 'pengepul'])->latest('tanggal_ajukan')->take(8)->get()
            )),
        ]);
    }

    // ---- Pengguna ----

    // GET /api/admin/pengguna
    public function pengguna(Request $request)
    {
        $cari = trim((string) $request->query('cari', '')) ?: null;
        $peran = $request->query('peran');
        $peran = in_array($peran, ['user', 'pengepul', 'admin'], true) ? $peran : null;

        $daftar = User::query()
            ->when($cari, function ($q) use ($cari) {
                $q->where(function ($inner) use ($cari) {
                    $inner->where('name', 'like', '%'.$cari.'%')
                        ->orWhere('email', 'like', '%'.$cari.'%');
                });
            })
            ->when($peran, fn ($q) => $q->where('role', $peran))
            ->orderByRaw("CASE role WHEN 'admin' THEN 0 WHEN 'pengepul' THEN 1 ELSE 2 END")
            ->orderBy('name')
            ->paginate($request->integer('per_page', 15));

        return UserResource::collection($daftar)->additional([
            'meta_tambahan' => [
                'total_user' => User::where('role', 'user')->count(),
                'total_pengepul' => User::where('role', 'pengepul')->count(),
            ],
        ]);
    }

    // POST /api/admin/pengguna/pengepul
    public function tambahPengepul(Request $request): JsonResponse
    {
        $validasi = AturanValidasi::siap('tambahPengepul');
        $data = $request->validate($validasi['aturan'], $validasi['pesan']);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'no_hp' => $data['no_hp'] ?? null,
            'role' => 'pengepul',
        ]);

        return (new UserResource($user))
            ->additional(['message' => 'Akun pengepul berhasil dibuat.'])
            ->response()
            ->setStatusCode(201);
    }

    // PUT /api/admin/pengguna/{user}
    public function updatePengguna(Request $request, User $user): JsonResponse
    {
        if ($user->isAdmin()) {
            return response()->json(['message' => 'Akun admin tidak dapat diubah.'], 422);
        }

        $validasi = AturanValidasi::siap('editPengguna', ['id' => $user->id]);
        $data = $request->validate($validasi['aturan'], $validasi['pesan']);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'no_hp' => $data['no_hp'] ?? null,
            'role' => $data['role'],
        ]);

        return (new UserResource($user->fresh()))
            ->additional(['message' => 'Akun berhasil diperbarui.'])
            ->response();
    }

    // DELETE /api/admin/pengguna/{user}
    public function hapusPengguna(Request $request, User $user): JsonResponse
    {
        abort_if($user->isAdmin(), 422, 'Akun admin tidak dapat dihapus.');
        abort_if($user->id === $request->user()->id, 422, 'Anda tidak dapat menghapus akun sendiri.');

        $user->delete();

        return response()->json(['message' => 'Akun berhasil dihapus.']);
    }

    // ---- Harga ----

    // GET /api/admin/harga
    public function harga(): JsonResponse
    {
        return response()->json([
            'harga' => [
                'minyak' => Harga::hargaMinyak(),
                'endapan' => Harga::hargaEndapan(),
            ],
            'maks_harga' => Harga::MAKS_HARGA_MINYAK,
            'sisa_hari' => Harga::sisaHari(),
            'boleh_ubah_pada' => Harga::bolehUbahPada()?->toIso8601String(),
            'jeda_aktif' => Harga::jedaAktif(),
            'jeda_hari' => Harga::JEDA_HARGA_HARI,
            'riwayat' => Harga::with('admin')->latest('id')->take(10)->get()->map(fn ($h) => [
                'id' => $h->id,
                'harga_minyak_kg' => (float) $h->harga_minyak_kg,
                'harga_endapan_kg' => (float) $h->harga_endapan_kg,
                'admin' => $h->admin?->name,
                'created_at' => $h->created_at?->toIso8601String(),
            ]),
        ]);
    }

    // POST /api/admin/harga
    public function updateHarga(Request $request): JsonResponse
    {
        if (Harga::dalamJeda()) {
            $boleh = Harga::bolehUbahPada();

            $pesan = 'Harga belum bisa diubah. Perubahan minimal berjarak '
                .Harga::JEDA_HARGA_HARI.' hari, berikutnya bisa dilakukan pada '
                .$boleh->translatedFormat('d F Y \p\u\k H:i').' WIB.';

            return response()->json(['message' => $pesan], 422);
        }

        $validasi = AturanValidasi::siap('updateHarga');
        $data = $request->validate($validasi['aturan'], $validasi['pesan']);

        Harga::create([
            'harga_minyak_kg' => $data['harga_minyak_kg'],
            'harga_endapan_kg' => $data['harga_endapan_kg'],
            'updated_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Harga minyak dan endapan berhasil diperbarui. Perubahan berikutnya bisa dilakukan '
                .Harga::JEDA_HARGA_HARI.' hari lagi.',
            'data' => $this->harga()->getData(true),
        ]);
    }

    // POST /api/admin/harga/kunci
    public function toggleKunciHarga(): JsonResponse
    {
        $sekarang = Harga::jedaAktif();

        Pengaturan::setHargaTerkunci(! $sekarang);

        return response()->json([
            'message' => $sekarang
                ? 'Kunci harga dibuka. Harga bisa diubah kapan saja sampai Anda mengunci lagi.'
                : 'Kunci harga aktif. Perubahan harga dibatasi sekali dalam '.Harga::JEDA_HARGA_HARI.' hari.',
            'jeda_aktif' => ! $sekarang,
            'data' => $this->harga()->getData(true),
        ]);
    }

    // ---- Pencairan ----

    // GET /api/admin/pencairan
    public function pencairan(Request $request)
    {
        $status = $request->query('status');
        $status = in_array($status, ['menunggu', 'disetujui', 'ditolak'], true) ? $status : null;

        $daftar = Pencairan::with(['user', 'admin'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest('created_at')
            ->paginate($request->integer('per_page', 15));

        return PencairanResource::collection($daftar)->additional([
            'meta_tambahan' => [
                'jumlah_menunggu' => Pencairan::menunggu()->count(),
                'total_menunggu' => (float) Pencairan::menunggu()->sum('jumlah'),
                'total_disetujui' => (float) Pencairan::where('status', 'disetujui')->sum('jumlah'),
                'saldo_berjalan' => (float) User::where('role', 'user')->sum('saldo'),
            ],
        ]);
    }

    // POST /api/admin/pencairan/{pencairan}/setujui
    public function setujuiPencairan(Request $request, Pencairan $pencairan): JsonResponse
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
                keterangan: 'Pencairan '.$pencairan->kode.' - '.$pencairan->labelMetode(),
                referensiId: (int) $pencairan->id,
                referensiTipe: 'pencairan',
            );

            $pencairan->update([
                'status' => 'disetujui',
                'diproses_oleh' => $request->user()->id,
                'diproses_pada' => now(),
            ]);
        });

        return (new PencairanResource($pencairan->fresh()->load(['user', 'admin'])))
            ->additional([
                'message' => 'Pencairan '.$pencairan->kode.' disetujui dan saldo sudah dipotong.',
            ])
            ->response();
    }

    // POST /api/admin/pencairan/{pencairan}/tolak
    public function tolakPencairan(Request $request, Pencairan $pencairan): JsonResponse
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

        return (new PencairanResource($pencairan->fresh()->load(['user', 'admin'])))
            ->additional(['message' => 'Pencairan '.$pencairan->kode.' ditolak.'])
            ->response();
    }

    // GET /api/admin/saldo/{user}
    public function detailSaldo(User $user): JsonResponse
    {
        return response()->json([
            'user' => (new UserResource($user))->resolve(),
            'transaksi' => ApiData::of(TransaksiResource::collection($user->transaksi()->latest('id')->paginate(20))),
            'pencairan' => ApiData::of(PencairanResource::collection($user->pencairans()->latest('id')->take(5)->get())),
        ]);
    }

    // ---- Laporan ----

    // GET /api/admin/laporan?dari=&sampai=
    public function laporan(Request $request): JsonResponse
    {
        $dari = $this->tanggalValid($request->query('dari'));
        $sampai = $this->tanggalValid($request->query('sampai'));

        $bermassa = Statistik::totalBermassa($dari, $sampai);

        return response()->json([
            'rentang' => ['dari' => $dari, 'sampai' => $sampai],
            'total' => $bermassa,
            'deret' => Statistik::deretHarian(14),
            'donut' => Statistik::donutStatus(),
            'top_warga' => Statistik::topWarga(10)->map(fn ($row) => [
                'user_id' => $row->user_id,
                'nama' => $row->nama,
                'jumlah' => (int) $row->jumlah,
                'kg' => round((float) $row->kg, 2),
                'nilai' => round((float) $row->nilai, 2),
            ]),
            'pencairan' => Pencairan::with('user')->latest('created_at')->take(20)->get()
                ->map(fn ($p) => [
                    'kode' => $p->kode,
                    'nama' => $p->user?->name,
                    'jumlah' => (float) $p->jumlah,
                    'metode_label' => $p->labelMetode(),
                    'status' => $p->status,
                    'created_at' => $p->created_at?->toIso8601String(),
                ]),
        ]);
    }

    private function tanggalValid(?string $nilai): ?string
    {
        if (! is_string($nilai) || $nilai === '') {
            return null;
        }

        return date('Y-m-d', strtotime($nilai)) ?: null;
    }
}