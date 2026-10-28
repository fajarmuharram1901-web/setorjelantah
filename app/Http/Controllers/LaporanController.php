<?php

namespace App\Http\Controllers;

use App\Models\Harga;
use App\Models\Setoran;
use App\Support\Statistik;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Laporan setoran dengan penyaring, ringkasan, dan ekspor CSV.
 */
class LaporanController extends Controller
{
    // GET /admin/laporan
    public function index(Request $request)
    {
        $filter = $this->filter($request);

        return view('admin.laporan', [
            'filter' => $filter,
            'setoran' => $this->query($request, $filter)
                ->with(['user', 'pengepul'])
                ->latest('tanggal_ajukan')
                ->paginate(15)
                ->withQueryString(),
            'ringkasan' => $this->ringkasan($request, $filter),
            'hargaMinyak' => Harga::hargaMinyak(),
            'hargaEndapan' => Harga::hargaEndapan(),
        ]);
    }

    // GET /admin/laporan/unduh
    public function unduh(Request $request): StreamedResponse
    {
        $filter = $this->filter($request);

        $setoran = $this->query($request, $filter)
            ->with(['user', 'pengepul'])
            ->latest('tanggal_ajukan')
            ->get();

        $ringkasan = $this->ringkasan($request, $filter);
        $nama = 'laporan-setoran-' . now()->format('Ymd-His') . '.csv';

        $header = [
            'No',
            'ID Setoran',
            'Tanggal Ajukan',
            'Tanggal Selesai',
            'Nama Warga',
            'No HP',
            'Pengepul',
            'Perkiraan Minyak (kg)',
            'Perkiraan Endapan (kg)',
            'Perkiraan Total (kg)',
            'Minyak (kg)',
            'Endapan (kg)',
            'Total Berat (kg)',
            'Harga Minyak/kg',
            'Harga Endapan/kg',
            'Nilai Minyak',
            'Nilai Endapan',
            'Total Bayar',
            'Status',
            'Bukti Foto',
            'Catatan Pengepul',
            'Alamat',
        ];

        return response()->streamDownload(function () use ($setoran, $header, $ringkasan, $filter) {
            $out = fopen('php://output', 'w');

            // BOM UTF-8 supaya Excel di Windows membaca karakter Indonesia dengan benar.
            fwrite($out, "\xEF\xBB\xBF");

            // Pemisah ';' dipakai karena Excel locale Indonesia memakainya secara bawaan.
            $pemisah = ';';

            // Judul + periode, supaya file tidak mulai langsung dari header.
            fputcsv($out, ['LAPORAN SETORAN MINYAK JELANTAH'], $pemisah);
            fputcsv($out, ['Dicetak pada', now()->format('d/m/Y H:i')], $pemisah);
            fputcsv($out, ['Filter', $this->ringkasanFilter($filter)], $pemisah);
            fputcsv($out, [], $pemisah);

            fputcsv($out, $header, $pemisah);

            $no = 0;
            foreach ($setoran as $s) {
                $no++;

                $minyakKg = (float) ($s->minyak_kg ?? 0);
                $endapanKg = (float) ($s->endapan_kg ?? 0);

                $nilaiMinyak = round($minyakKg * (float) ($s->harga_minyak_saat_itu ?? 0), 2);
                $nilaiEndapan = round($endapanKg * (float) ($s->harga_endapan_saat_itu ?? 0), 2);

                fputcsv($out, [
                    $no,
                    $s->id,
                    $s->tanggal_ajukan?->format('d/m/Y H:i'),
                    $s->tanggal_selesai?->format('d/m/Y H:i') ?: '-',
                    $s->user?->name ?? '-',
                    $s->user?->no_hp_formatted ?? '-',
                    $s->pengepul?->name ?? '-',
                    $this->angka($s->perkiraanMinyakKg()),
                    $this->angka($s->perkiraanEndapanKg()),
                    $this->angka($s->perkiraanTotalKg()),
                    $this->angka($minyakKg),
                    $this->angka($endapanKg),
                    $this->angka($minyakKg + $endapanKg),
                    $this->angka($s->harga_minyak_saat_itu),
                    $this->angka($s->harga_endapan_saat_itu),
                    $this->angka($nilaiMinyak),
                    $this->angka($nilaiEndapan),
                    $this->angka($s->total_saldo),
                    $s->status,
                    $s->bukti_foto ? 'ada' : 'tidak',
                    $s->catatan_pengepul ?: '-',
                    $s->alamat_jemput,
                ], $pemisah);
            }

            // Baris rekap di akhir berkas.
            fputcsv($out, [], $pemisah);
            fputcsv($out, ['REKAP'], $pemisah);
            fputcsv($out, ['Total baris', $setoran->count()], $pemisah);
            fputcsv($out, ['Selesai', $ringkasan['jumlah_selesai']], $pemisah);
            fputcsv($out, ['Total minyak (kg)', $this->angka($ringkasan['minyak'])], $pemisah);
            fputcsv($out, ['Total endapan (kg)', $this->angka($ringkasan['endapan'])], $pemisah);
            fputcsv($out, ['Total berat (kg)', $this->angka($ringkasan['minyak'] + $ringkasan['endapan'])], $pemisah);
            fputcsv($out, ['Total nilai (Rp)', $this->angka($ringkasan['nilai'])], $pemisah);

            fclose($out);
        }, $nama, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store',
        ]);
    }

    // ---------- Private ----------

    /**
     * Angka ditulis polos (tanpa pemisah ribuan) supaya Excel bisa mengurutkan
     * dan menjumlahkan dengan benar.
     */
    private function angka(mixed $nilai): string
    {
        return rtrim(rtrim(number_format((float) ($nilai ?? 0), 2, '.', ''), '0'), '.') ?: '0';
    }

    /**
     * @return array<string, mixed>
     */
    private function filter(Request $request): array
    {
        $status = $request->query('status');
        $role = $request->query('role');

        return [
            'status' => in_array($status, ['menunggu', 'diproses', 'selesai', 'dibatalkan'], true) ? $status : null,
            'role' => in_array($role, ['user', 'pengepul'], true) ? $role : null,
            'cari' => trim((string) $request->query('cari', '')) ?: null,
            'dari' => $this->tanggalValid($request->query('dari')),
            'sampai' => $this->tanggalValid($request->query('sampai')),
        ];
    }

    /**
     * Query dasar yang dipakai halaman laporan maupun unduhan.
     */
    private function query(Request $request, array $filter): Builder
    {
        return Setoran::query()
            ->when($filter['status'], fn (Builder $q) => $q->where('status', $filter['status']))
            ->when($filter['role'], fn (Builder $q) => $q->whereHas(
                $filter['role'] === 'user' ? 'user' : 'pengepul',
                fn (Builder $r) => $r->where('role', $filter['role'])
            ))
            ->when($filter['cari'], function (Builder $q) use ($filter) {
                $cari = $filter['cari'];

                $q->where(function (Builder $inner) use ($cari) {
                    $inner->where('alamat_jemput', 'like', '%' . $cari . '%')
                        ->orWhere('catatan', 'like', '%' . $cari . '%')
                        ->orWhere('catatan_pengepul', 'like', '%' . $cari . '%')
                        ->orWhereHas('user', fn (Builder $u) => $u->where('name', 'like', '%' . $cari . '%'))
                        ->orWhereHas('pengepul', fn (Builder $u) => $u->where('name', 'like', '%' . $cari . '%'));
                });
            })
            ->when($filter['dari'], fn (Builder $q) => $q->whereDate('tanggal_ajukan', '>=', $filter['dari']))
            ->when($filter['sampai'], fn (Builder $q) => $q->whereDate('tanggal_ajukan', '<=', $filter['sampai']));
    }

    /**
     * Agregat mengikuti penyaring yang sedang aktif.
     *
     * @return array<string, mixed>
     */
    private function ringkasan(Request $request, array $filter): array
    {
        $base = $this->query($request, $filter);
        $selesai = (clone $base)->selesai();

        $minyak = (float) (clone $selesai)->sum('minyak_kg');
        $endapan = (float) (clone $selesai)->sum('endapan_kg');
        $jumlahSelesai = (clone $selesai)->count();

        return [
            'total' => (clone $base)->count(),
            'jumlah_selesai' => $jumlahSelesai,
            'minyak' => round($minyak, 2),
            'endapan' => round($endapan, 2),
            'berat' => round($minyak + $endapan, 2),
            'nilai' => round((float) (clone $selesai)->sum('total_saldo'), 2),
            'rata_kg' => $jumlahSelesai > 0 ? round(($minyak + $endapan) / $jumlahSelesai, 2) : 0.0,
            'per_status' => Statistik::komposisiStatus(),
        ];
    }

    private function ringkasanFilter(array $filter): string
    {
        $bagian = [];

        $bagian[] = $filter['status'] ? 'status=' . $filter['status'] : 'status=Semua';
        $bagian[] = $filter['role'] ? 'peran=' . $filter['role'] : 'peran=Semua';
        $bagian[] = $filter['dari'] ? 'dari=' . $filter['dari'] : 'dari=awal';
        $bagian[] = $filter['sampai'] ? 'sampai=' . $filter['sampai'] : 'sampai=akhir';
        $bagian[] = $filter['cari'] ? 'kata=' . $filter['cari'] : 'kata=(kosong)';

        return implode(', ', $bagian);
    }

    private function tanggalValid(?string $nilai): ?string
    {
        if (! is_string($nilai) || $nilai === '') {
            return null;
        }

        return date('Y-m-d', strtotime($nilai)) ?: null;
    }
}
