<?php

namespace App\Http\Controllers;

use App\Models\Harga;
use App\Models\Setoran;
use App\Support\AturanValidasi;
use App\Support\Statistik;
use Illuminate\Http\Request;

class SetoranController extends Controller
{
    // GET /user/dashboard
    public function dashboard(Request $request)
    {
        return view('user.dashboard', $this->ringkasanDashboard($request));
    }

    // GET /user/dashboard/data (AJAX polling)
    public function dashboardData(Request $request)
    {
        $html = view('user.partials.dashboard-content', $this->ringkasanDashboard($request))->render();

        return response()->json(['html' => $html]);
    }

    // GET /user/ajukan
    public function create()
    {
        return view('user.ajukan', [
            'hargaMinyak' => Harga::hargaMinyak(),
            'hargaEndapan' => Harga::hargaEndapan(),
            'maksKg' => Harga::MAKS_KG,
        ]);
    }

    // POST /user/ajukan
    public function store(Request $request)
    {
        $maks = Harga::MAKS_KG;

        $validasi = AturanValidasi::siap('ajukanSetoran');
        $data = $request->validate($validasi['aturan'], $validasi['pesan']);

        $minyak = (float) $data['perkiraan_minyak_kg'];
        $endapan = (float) $data['perkiraan_endapan_kg'];
        $total = round($minyak + $endapan, 2);

        if ($total <= 0) {
            return back()->withInput()->withErrors([
                'perkiraan_minyak_kg' => 'Isi perkiraan minyak atau endapan, jangan keduanya 0.',
            ]);
        }

        if ($total > $maks) {
            return back()->withInput()->withErrors([
                'perkiraan_minyak_kg' => 'Total perkiraan minyak dan endapan maksimal '
                    . number_format($maks, 0, ',', '.') . ' kg per pengajuan.',
            ]);
        }

        $request->user()->setoran()->create([
            'perkiraan_kg' => $total,
            'perkiraan_minyak_kg' => $minyak,
            'perkiraan_endapan_kg' => $endapan,
            'alamat_jemput' => $data['alamat_jemput'],
            'catatan' => $data['catatan'] ?? null,
            'tanggal_ajukan' => now(),
        ]);

        return redirect()->route('user.dashboard')
            ->with('success', 'Pengajuan setoran berhasil dikirim. Menunggu pengepul memproses.');
    }

    // GET /user/riwayat
    public function riwayat(Request $request)
    {
        $filter = $this->filterRiwayat($request);

        $semua = $request->user()->setoran()
            ->with('pengepul')
            ->when($filter['status'], fn ($q) => $q->where('status', $filter['status']))
            ->when($filter['cari'], function ($q) use ($filter) {
                $q->where(function ($inner) use ($filter) {
                    $inner->where('alamat_jemput', 'like', '%' . $filter['cari'] . '%')
                        ->orWhere('catatan', 'like', '%' . $filter['cari'] . '%');
                });
            })
            ->when($filter['dari'], fn ($q) => $q->whereDate('tanggal_ajukan', '>=', $filter['dari']))
            ->when($filter['sampai'], fn ($q) => $q->whereDate('tanggal_ajukan', '<=', $filter['sampai']))
            ->latest('tanggal_ajukan')
            ->paginate(10)
            ->withQueryString();

        return view('user.riwayat', ['semua' => $semua, 'filter' => $filter]);
    }

    // GET /user/setoran/{setoran} (detail)
    public function detail(Request $request, Setoran $setoran)
    {
        abort_unless($setoran->user_id === $request->user()->id, 403);

        return view('user.detail', ['setoran' => $setoran->load('pengepul')]);
    }

    // ---------- Private ----------

    /**
     * Data yang dipakai oleh halaman dashboard dan partial AJAX-nya.
     */
    private function ringkasanDashboard(Request $request): array
    {
        $user = $request->user();
        $setoran = $user->setoran();
        $selesai = (clone $setoran)->selesai();

        $minyak = (float) (clone $selesai)->sum('minyak_kg');
        $endapan = (float) (clone $selesai)->sum('endapan_kg');

        return [
            'user' => $user,
            'setoranTerbaru' => (clone $setoran)->with('pengepul')->latest('tanggal_ajukan')->take(5)->get(),
            'menunggu' => (clone $setoran)->where('status', 'menunggu')->count(),
            'diproses' => (clone $setoran)->where('status', 'diproses')->count(),
            'totalMinyak' => $minyak,
            'totalEndapan' => $endapan,
            'totalKg' => $minyak + $endapan,
            'totalNilai' => (float) (clone $selesai)->sum('total_saldo'),
            'jumlahSelesai' => (clone $selesai)->count(),
            'deret' => Statistik::deretHarian(10),
            'saldoTersedia' => $user->saldoTersedia(),
            'bisaPencair' => $user->bisaPencair(),
            'menungguPencairan' => $user->pencairans()->where('status', 'menunggu')->count(),
            'hargaMinyak' => Harga::hargaMinyak(),
            'hargaEndapan' => Harga::hargaEndapan(),
        ];
    }

    /**
     * Normalisasi input filter riwayat.
     *
     * @return array<string, mixed>
     */
    private function filterRiwayat(Request $request): array
    {
        $status = $request->query('status');

        return [
            'status' => in_array($status, ['menunggu', 'diproses', 'selesai', 'dibatalkan'], true) ? $status : null,
            'cari' => trim((string) $request->query('cari', '')) ?: null,
            'dari' => $this->tanggalValid($request->query('dari')),
            'sampai' => $this->tanggalValid($request->query('sampai')),
        ];
    }

    private function tanggalValid(?string $nilai): ?string
    {
        if (! is_string($nilai) || $nilai === '') {
            return null;
        }

        return date('Y-m-d', strtotime($nilai)) ?: null;
    }
}
