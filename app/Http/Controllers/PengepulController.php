<?php

namespace App\Http\Controllers;

use App\Models\Harga;
use App\Models\Setoran;
use App\Support\Background;
use App\Support\Saldo;
use App\Support\AturanValidasi;
use App\Support\Statistik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PengepulController extends Controller
{
    // GET /pengepul/dashboard
    public function dashboard(Request $request)
    {
        return view('pengepul.dashboard', $this->dataDashboard($request));
    }

    // GET /pengepul/menunggu (AJAX polling)
    public function menunggu(Request $request)
    {
        $menunggu = Setoran::with('user')->menunggu()->oldest('tanggal_ajukan')->get();

        if ($request->ajax()) {
            return response()->json([
                'jumlah' => $menunggu->count(),
                'html' => view('pengepul.partials.menunggu', ['menunggu' => $menunggu])->render(),
            ]);
        }

        return view('pengepul.partials.menunggu', ['menunggu' => $menunggu]);
    }

    // POST /pengepul/ambil/{setoran}
    public function ambil(Request $request, Setoran $setoran)
    {
        if ($setoran->status === 'menunggu') {
            $setoran->update([
                'status' => 'diproses',
                'pengepul_id' => $request->user()->id,
            ]);

            $setoran->load('user');
            Background::artisan([
                'notif:send',
                (string) $setoran->user_id,
                (string) $setoran->id,
                'ambil',
            ]);

            session()->flash('success', 'Tugas berhasil diambil. Silakan konfirmasi setelah mengecek di lokasi.');
        }

        return redirect()->route('pengepul.dashboard');
    }

    // POST /pengepul/tolak/{setoran}
    public function tolak(Request $request, Setoran $setoran)
    {
        if ($setoran->status === 'menunggu') {
            $setoran->update(['status' => 'dibatalkan']);

            Background::artisan([
                'notif:send',
                (string) $setoran->user_id,
                (string) $setoran->id,
                'tolak',
            ]);

            session()->flash('success', 'Pengajuan ditolak. Warga telah diberi tahu melalui notifikasi.');
        }

        return redirect()->route('pengepul.dashboard');
    }

    // GET /pengepul/verifikasi/{setoran}
    public function show(Request $request, Setoran $setoran)
    {
        if ($setoran->pengepul_id !== $request->user()->id || $setoran->status !== 'diproses') {
            $setoran = null;
        }

        return view('pengepul.verifikasi', [
            'setoran' => $setoran,
            'hargaMinyak' => Harga::hargaMinyak(),
            'hargaEndapan' => Harga::hargaEndapan(),
            'maksKg' => Harga::MAKS_KG,
        ]);
    }

    // POST /pengepul/verifikasi/{setoran}
    public function konfirmasi(Request $request, Setoran $setoran)
    {
        abort_unless(
            $setoran->pengepul_id === $request->user()->id && $setoran->status === 'diproses',
            404,
            'Data setoran tidak ditemukan atau sudah diproses pengepul lain.'
        );

        $maks = Harga::MAKS_KG;

        // Minyak dan endapan ditimbang terpisah, keduanya wajib diisi.
        $validasi = AturanValidasi::siap('verifikasiSetoran');
        $data = $request->validate($validasi['aturan'], $validasi['pesan']);

        $minyak = (float) $data['minyak_kg'];
        $endapan = (float) $data['endapan_kg'];

        if ($minyak + $endapan <= 0) {
            return back()->withInput()->withErrors([
                'minyak_kg' => 'Total minyak dan endapan tidak boleh nol.',
            ]);
        }

        if ($minyak + $endapan > $maks) {
            return back()->withInput()->withErrors([
                'minyak_kg' => 'Total berat minyak dan endapan maksimal ' . $maks . ' kg.',
            ]);
        }

        $hargaMinyak = Harga::hargaMinyak();
        $hargaEndapan = Harga::hargaEndapan();
        $hitungan = Setoran::hitungNilai($minyak, $endapan, $hargaMinyak, $hargaEndapan);

        $pathBukti = $request->file('bukti_foto')?->store('bukti', 'public');

        DB::transaction(function () use ($setoran, $data, $hitungan, $hargaMinyak, $hargaEndapan, $minyak, $endapan, $pathBukti) {
            $setoran->update([
                'minyak_kg' => $minyak,
                'endapan_kg' => $endapan,
                'catatan_pengepul' => $data['catatan_pengepul'] ?? null,
                'bukti_foto' => $pathBukti,
                'harga_minyak_saat_itu' => $hargaMinyak,
                'harga_endapan_saat_itu' => $hargaEndapan,
                'total_saldo' => $hitungan['nilai'],
                'status' => 'selesai',
                'tanggal_selesai' => now(),
            ]);

            // Tambah saldo warga sekaligus catat di buku besar.
            $rincian = sprintf(
                'Setoran #%d - minyak %s kg x Rp%s + endapan %s kg x Rp%s',
                $setoran->id,
                rtrim(rtrim(number_format($minyak, 2, ',', '.'), '0'), ','),
                number_format($hargaMinyak, 0, ',', '.'),
                rtrim(rtrim(number_format($endapan, 2, ',', '.'), '0'), ','),
                number_format($hargaEndapan, 0, ',', '.')
            );

            Saldo::kredit(
                user: $setoran->user,
                jumlah: $hitungan['nilai'],
                tipe: 'setoran',
                keterangan: $rincian,
                referensiId: (int) $setoran->id,
                referensiTipe: 'setoran',
            );
        });

        Background::artisan([
            'notif:send',
            (string) $setoran->user_id,
            (string) $setoran->id,
            'selesai',
        ]);

        return redirect()->route('pengepul.dashboard')
            ->with('success', 'Setoran dikonfirmasi. Saldo warga bertambah Rp' . number_format($hitungan['nilai'], 0, ',', '.') . '.');
    }

    // GET /pengepul/riwayat
    public function riwayat(Request $request)
    {
        $status = $request->query('status');
        $status = in_array($status, ['menunggu', 'diproses', 'selesai', 'dibatalkan'], true) ? $status : null;

        $tugas = $request->user()->tugasPengepul()
            ->with('user')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest('tanggal_ajukan')
            ->paginate(10)
            ->withQueryString();

        return view('pengepul.riwayat', ['tugas' => $tugas, 'status' => $status]);
    }

    // ---------- Private ----------

    /**
     * @return array<string, mixed>
     */
    private function dataDashboard(Request $request): array
    {
        $pengepul = $request->user();
        $id = $pengepul->id;
        $selesai = Setoran::query()->selesai()->where('pengepul_id', $id);

        return [
            'pengepul' => $pengepul,
            'menunggu' => Setoran::with('user')->menunggu()->oldest('tanggal_ajukan')->get(),
            'diproses' => Setoran::with('user')->diproses()->where('pengepul_id', $id)->oldest('tanggal_ajukan')->get(),
            'selesai' => (clone $selesai)->latest('tanggal_selesai')->take(5)->get(),
            'jumlahSelesai' => (clone $selesai)->count(),
            'totalMinyak' => (float) (clone $selesai)->sum('minyak_kg'),
            'totalEndapan' => (float) (clone $selesai)->sum('endapan_kg'),
            'totalKg' => (float) (clone $selesai)->sum('minyak_kg') + (float) (clone $selesai)->sum('endapan_kg'),
            'totalNilai' => (float) (clone $selesai)->sum('total_saldo'),
            'deret' => Statistik::deretHarian(10),
            'hargaMinyak' => Harga::hargaMinyak(),
            'hargaEndapan' => Harga::hargaEndapan(),
        ];
    }
}
