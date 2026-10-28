<?php

namespace App\Http\Controllers;

use App\Models\Harga;
use App\Models\Setoran;
use App\Models\User;
use App\Support\Statistik;

class HomeController extends Controller
{
    public function index()
    {
        $bermassa = Statistik::totalBermassa();
        $jumlahSelesai = Setoran::query()->selesai()->count();

        return view('welcome', [
            'hargaMinyak' => Harga::hargaMinyak(),
            'hargaEndapan' => Harga::hargaEndapan(),
            'maksKg' => Harga::MAKS_KG,
            'totalMinyak' => $bermassa['minyak'],
            'totalEndapan' => $bermassa['endapan'],
            'totalKg' => $bermassa['total'],
            'totalNilai' => $bermassa['nilai'],
            'jumlahSetoran' => $jumlahSelesai,
            'jumlahWarga' => User::where('role', 'user')->count(),
            'jumlahPengepul' => User::where('role', 'pengepul')->count(),
            'rataKg' => $jumlahSelesai > 0 ? round($bermassa['total'] / $jumlahSelesai, 2) : 0.0,
        ]);
    }
}
