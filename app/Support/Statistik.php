<?php

namespace App\Support;

use App\Models\Setoran;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Perhitungan angka untuk dashboard.
 *
 * Semua query dikelompokkan per tanggal dengan fungsi yang sesuai untuk tiap
 * driver database, sehingga kodenya tetap jalan di MySQL, SQLite, dan PostgreSQL.
 */
class Statistik
{
    /** Ekspresi SQL untuk mengambil bagian tanggal dari sebuah kolom timestamp. */
    public static function exprTanggal(string $kolom): string
    {
        $kolom = trim($kolom);

        return match (DB::getDriverName()) {
            'sqlite' => "date({$kolom})",
            'pgsql' => "({$kolom})::date",
            default => "DATE({$kolom})",
        };
    }

    /**
     * Deret harian untuk N hari terakhir.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function deretHarian(int $hari = 14, string $kolomTanggal = 'tanggal_selesai'): array
    {
        $hari = max(1, min($hari, 90));
        $expr = self::exprTanggal($kolomTanggal);
        $sejak = now()->subDays($hari - 1)->startOfDay();

        $baris = Setoran::query()
            ->selesai()
            ->whereNotNull($kolomTanggal)
            ->where($kolomTanggal, '>=', $sejak)
            ->selectRaw("{$expr} as tanggal, SUM(minyak_kg) as minyak, SUM(endapan_kg) as endapan, SUM(total_saldo) as nilai, COUNT(*) as jumlah")
            ->groupBy('tanggal')
            ->get()
            ->keyBy('tanggal');

        $hasil = [];

        for ($i = $hari - 1; $i >= 0; $i--) {
            $tanggal = now()->subDays($i)->format('Y-m-d');
            $row = $baris->get($tanggal);

            $hasil[] = [
                'tanggal' => $tanggal,
                'label' => now()->subDays($i)->format('d/m'),
                'minyak' => round((float) ($row->minyak ?? 0), 2),
                'endapan' => round((float) ($row->endapan ?? 0), 2),
                'kg' => round((float) ($row->minyak ?? 0) + (float) ($row->endapan ?? 0), 2),
                'nilai' => round((float) ($row->nilai ?? 0), 2),
                'jumlah' => (int) ($row->jumlah ?? 0),
            ];
        }

        return $hasil;
    }

    /**
     * Jumlah setoran per status.
     *
     * @return array<string, int>
     */
    public static function komposisiStatus(): array
    {
        $baris = Setoran::query()
            ->selectRaw('status, COUNT(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        $hasil = [];

        foreach (['menunggu', 'diproses', 'selesai', 'dibatalkan'] as $status) {
            $hasil[$status] = (int) ($baris[$status] ?? 0);
        }

        return $hasil;
    }

    /**
     * Urutan status untuk grafik donut (hanya yang punya data).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function donutStatus(): array
    {
        $warna = [
            'menunggu' => '#DB9A26',
            'diproses' => '#3E5E7E',
            'selesai' => '#55703C',
            'dibatalkan' => '#A63D28',
        ];

        $hasil = [];

        foreach (self::komposisiStatus() as $status => $jumlah) {
            if ($jumlah > 0) {
                $hasil[] = [
                    'status' => $status,
                    'label' => ucfirst($status),
                    'jumlah' => $jumlah,
                    'warna' => $warna[$status],
                ];
            }
        }

        return $hasil;
    }

    /**
     * Pemisahan minyak dan endapan dalam satu deret, untuk grafik bertumpuk.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function deretMinyakEndapan(int $hari = 14): array
    {
        $deret = self::deretHarian($hari);

        return array_map(fn (array $baris) => [
            'label' => $baris['label'],
            'minyak' => $baris['minyak'],
            'endapan' => $baris['endapan'],
        ], $deret);
    }

    /**
     * Total minyak, endapan, berat gabungan, dan nilainya pada periode tertentu.
     *
     * @return array<string, float>
     */
    public static function totalBermassa(?string $dari = null, ?string $sampai = null): array
    {
        $query = Setoran::query()->selesai()->rentang($dari, $sampai);

        $minyak = (float) $query->sum('minyak_kg');
        $endapan = (float) $query->sum('endapan_kg');

        return [
            'minyak' => round($minyak, 2),
            'endapan' => round($endapan, 2),
            'total' => round($minyak + $endapan, 2),
            'nilai' => round((float) $query->sum('total_saldo'), 2),
        ];
    }

    /**
     * Peringkat warga yang paling besar kontribusinya.
     *
     * @return \Illuminate\Support\Collection<int, object>
     */
    public static function topWarga(int $limit = 5)
    {
        return Setoran::query()
            ->selesai()
            ->whereNotNull('user_id')
            ->selectRaw('user_id, COUNT(*) as jumlah, SUM(minyak_kg + endapan_kg) as kg, SUM(total_saldo) as nilai')
            ->groupBy('user_id')
            ->orderByDesc('nilai')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $row->nama = User::find($row->user_id)?->name ?? '-';

                return $row;
            });
    }

    /**
     * Pengepul dengan volume terbesar.
     *
     * @return \Illuminate\Support\Collection<int, object>
     */
    public static function topPengepul(int $limit = 5)
    {
        return Setoran::query()
            ->selesai()
            ->whereNotNull('pengepul_id')
            ->selectRaw('pengepul_id, COUNT(*) as jumlah, SUM(minyak_kg + endapan_kg) as kg, SUM(total_saldo) as nilai')
            ->groupBy('pengepul_id')
            ->orderByDesc('nilai')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $row->nama = User::find($row->pengepul_id)?->name ?? '-';

                return $row;
            });
    }

    /**
     * Persentase perubahan dibanding periode sebelumnya dengan panjang sama.
     */
    public static function tren(float $sekarang, float $sebelumnya): float
    {
        if ($sebelumnya <= 0.0) {
            return $sekarang > 0 ? 100.0 : 0.0;
        }

        return round((($sekarang - $sebelumnya) / $sebelumnya) * 100, 1);
    }

    /**
     * Ringkasan satu blok untuk kartu statistik.
     *
     * @return array<string, float>
     */
    public static function ringkasan(int $hari = 14): array
    {
        $deret = self::deretHarian($hari);

        $totalNilai = array_sum(array_column($deret, 'nilai'));
        $totalKg = array_sum(array_column($deret, 'kg'));
        $jumlahSetoran = array_sum(array_column($deret, 'jumlah'));

        // Bandingkan paruh awal vs paruh akhir deret ini.
        $tengah = (int) floor(count($deret) / 2);
        $sebelum = array_slice($deret, 0, $tengah);
        $sesudah = array_slice($deret, $tengah);

        $kgAwal = array_sum(array_column($sebelum, 'kg'));
        $kgAkhir = array_sum(array_column($sesudah, 'kg'));
        $nilaiAwal = array_sum(array_column($sebelum, 'nilai'));
        $nilaiAkhir = array_sum(array_column($sesudah, 'nilai'));

        return [
            'total_kg' => $totalKg,
            'total_nilai' => $totalNilai,
            'jumlah_setoran' => $jumlahSetoran,
            'tren_kg' => self::tren($kgAkhir, $kgAwal),
            'tren_nilai' => self::tren($nilaiAkhir, $nilaiAwal),
            'rata_kg' => $jumlahSetoran > 0 ? round($totalKg / $jumlahSetoran, 2) : 0.0,
        ];
    }
}
