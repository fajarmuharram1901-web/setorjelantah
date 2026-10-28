<?php

namespace App\Http\Controllers;

use App\Models\Harga;
use App\Models\Pencairan;
use App\Models\User;
use App\Support\AturanValidasi;
use App\Support\Statistik;
use Illuminate\Http\Request;

/**
 * Pencairan saldo oleh rumah tangga, plus buku besar saldo.
 */
class PencairanController extends Controller
{
    // GET /user/saldo
    public function saldo(Request $request)
    {
        $user = $request->user();

        $deret = Statistik::deretHarian(10);
        $tengah = (int) floor(count($deret) / 2);

        return view('user.saldo', [
            'user' => $user,
            'transaksi' => $user->transaksi()->latest('id')->paginate(12),
            'pencairans' => $user->pencairans()->latest('id')->paginate(8),
            'saldoTersedia' => $user->saldoTersedia(),
            'minPencairan' => User::MIN_PENCAIRAN,
            'hargaMinyak' => Harga::hargaMinyak(),
            'deret' => $deret,
            'trenNilai' => Statistik::tren(
                array_sum(array_column(array_slice($deret, $tengah), 'nilai')),
                array_sum(array_column(array_slice($deret, 0, $tengah), 'nilai')),
            ),
            'totalMasuk' => (float) $user->transaksi()->where('tipe', 'setoran')->sum('jumlah'),
            'totalKeluar' => (float) $user->transaksi()->where('tipe', 'pencairan')->sum('jumlah'),
        ]);
    }

    // GET /user/pencairan/create
    public function create(Request $request)
    {
        $user = $request->user();
        $saldoTersedia = $user->saldoTersedia();

        if ($saldoTersedia < User::MIN_PENCAIRAN) {
            return redirect()->route('user.saldo')
                ->with('error', 'Saldo tersedia minimal Rp'.number_format(User::MIN_PENCAIRAN, 0, ',', '.').' untuk bisa pencairan.');
        }

        return view('user.pencairan', [
            'user' => $user,
            'saldoTersedia' => $saldoTersedia,
            'minPencairan' => User::MIN_PENCAIRAN,
            'riwayat' => $user->pencairans()->latest('id')->take(5)->get(),
        ]);
    }

    // POST /user/pencairan
    public function store(Request $request)
    {
        $user = $request->user();
        $saldoTersedia = $user->saldoTersedia();

        $validasi = AturanValidasi::siap('pencairan', ['maksSaldo' => (int) floor($saldoTersedia)]);
        $data = $request->validate($validasi['aturan'], $validasi['pesan']);


        $pencairan = Pencairan::create([
            'user_id' => $user->id,
            'jumlah' => $data['jumlah'],
            'metode' => $data['metode'],
            'tujuan' => $data['tujuan'],
            'nama_penerima' => $data['nama_penerima'],
            'status' => 'menunggu',
        ]);

        return redirect()->route('user.saldo')
            ->with('success', 'Permintaan pencairan '.$pencairan->kode.' terkirim. Menunggu persetujuan admin.');
    }

    // POST /user/pencairan/{pencairan}/batal
    public function batal(Request $request, Pencairan $pencairan)
    {
        abort_unless($pencairan->user_id === $request->user()->id, 403);
        abort_unless($pencairan->status === 'menunggu', 422, 'Permintaan ini sudah diproses admin.');

        $pencairan->update(['status' => 'ditolak', 'alasan_tolak' => 'Dibatalkan sendiri oleh pemohon.']);

        return back()->with('success', 'Permintaan pencairan dibatalkan.');
    }
}
