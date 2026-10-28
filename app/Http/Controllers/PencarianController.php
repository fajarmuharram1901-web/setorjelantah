<?php

namespace App\Http\Controllers;

use App\Models\Setoran;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Pencarian global dari search bar di navbar.
 */
class PencarianController extends Controller
{
    // GET /pencarian?q=...
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json(['query' => $q, 'results' => [], 'message' => 'Ketik minimal 2 karakter.']);
        }

        $user = Auth::user();
        $hasil = [];

        // 1. Setoran milik pengguna (warga) atau yang ditangani (pengepul).
        $setoran = Setoran::query()
            ->when($user->isUser(), fn ($query) => $query->where('user_id', $user->id))
            ->when($user->isPengepul(), fn ($query) => $query->where('pengepul_id', $user->id))
            ->when($user->isAdmin(), fn ($query) => $query->with('user', 'pengepul'))
            ->where(function ($inner) use ($q, $user) {
                $inner->where('alamat_jemput', 'like', '%' . $q . '%')
                    ->orWhere('catatan', 'like', '%' . $q . '%')
                    ->orWhere('catatan_pengepul', 'like', '%' . $q . '%')
                    ->orWhere('id', 'like', '%' . $q . '%');

                // Pencarian nama warga/pengepul hanya untuk admin.
                if ($user->isAdmin()) {
                    $inner->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%' . $q . '%'))
                        ->orWhereHas('pengepul', fn ($u) => $u->where('name', 'like', '%' . $q . '%'));
                }
            })
            ->latest('tanggal_ajukan')
            ->limit(6)
            ->get();

        foreach ($setoran as $s) {
            $hasil[] = [
                'jenis' => 'Setoran',
                'judul' => 'Setoran #' . $s->id,
                'detail' => $s->user?->name . ' - ' . Str::limit($s->alamat_jemput, 42),
                'label' => ucfirst((string) $s->status),
                'url' => $user->isUser()
                    ? route('user.detail', $s)
                    : ($user->isPengepul() ? route('pengepul.verifikasi', $s) : route('admin.laporan', ['cari' => $s->id])),
            ];
        }

        // 2. Admin bisa mencari pengguna.
        if ($user->isAdmin()) {
            $pengguna = User::query()
                ->where(function ($inner) use ($q) {
                    $inner->where('name', 'like', '%' . $q . '%')
                        ->orWhere('email', 'like', '%' . $q . '%');
                })
                ->limit(5)
                ->get();

            foreach ($pengguna as $p) {
                $hasil[] = [
                    'jenis' => 'Pengguna',
                    'judul' => $p->name,
                    'detail' => $p->email,
                    'label' => $p->labelPeran(),
                    'url' => $p->isAdmin() ? route('admin.dashboard') : route('admin.saldo', $p),
                ];
            }
        }

        // 3. Warga juga bisa mencari daftar mutasi saldo.
        if ($user->isUser()) {
            $transaksi = Transaksi::query()
                ->where('user_id', $user->id)
                ->where('keterangan', 'like', '%' . $q . '%')
                ->latest('id')
                ->limit(4)
                ->get();

            foreach ($transaksi as $t) {
                $hasil[] = [
                    'jenis' => 'Transaksi',
                    'judul' => $t->labelTipe(),
                    'detail' => Str::limit((string) $t->keterangan, 42),
                    'label' => 'Rp' . number_format(abs((float) $t->jumlah), 0, ',', '.'),
                    'url' => route('user.saldo'),
                ];
            }
        }

        return response()->json([
            'query' => $q,
            'results' => $hasil,
            'message' => $hasil === [] ? 'Tidak ada hasil untuk "' . $q . '".' : null,
        ]);
    }
}
