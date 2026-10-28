@extends('layouts.dashboard', ['title' => 'Dashboard Pengepul', 'subtitle' => 'Tugas menunggu, setoran diproses, dan hasil kerja Anda'])

@section('content')

<div class="page-head">
    <div>
        <h1 class="page-title">Dashboard Pengepul</h1>
        <p class="page-sub">Ambil tugas yang tersedia, timbang minyak dan endapannya, lalu konfirmasi agar saldo warga bertambah.</p>
    </div>
    <div class="panel-actions">
        <a href="{{ route('pengepul.riwayat') }}" class="btn btn-secondary">
            <x-icon name="history" :size="15" /> Riwayat Tugas
        </a>
    </div>
</div>

<div class="stat-grid stat-grid-4">
    <x-stat-card label="Menunggu Diambil"
                 :value="count($menunggu)"
                 icon="inbox" accent="#DB9A26" tint="#FBF0DD"
                 :meta="count($menunggu) > 0 ? 'Tugas tersedia untuk diambil' : 'Tidak ada tugas baru'" />
    <x-stat-card label="Sedang Diproses"
                 :value="count($diproses)"
                 icon="truck" accent="#3E5E7E" tint="#E9F0F6"
                 meta="Tugas milik Anda" />
    <x-stat-card label="Minyak Ditimbang"
                 :value="number_format($totalMinyak, 1, ',', '.') . ' kg'"
                 icon="droplet" accent="#DB9A26" tint="#FBF0DD"
                 :meta="$jumlahSelesai . ' setoran selesai'" />
    <x-stat-card label="Endapan Ditimbang"
                 :value="number_format($totalEndapan, 1, ',', '.') . ' kg'"
                 icon="scale" accent="#5A3310" tint="#F1E7D6"
                 :meta="'Total Rp' . number_format($totalNilai, 0, ',', '.')" />
</div>

<div class="grid-2-panel">
    <div class="panel">
        <div class="panel-head">
            <div>
                <h2>Hasil Kerja 10 Hari Terakhir</h2>
                <p class="panel-sub">Nilai setoran yang Anda konfirmasi per hari</p>
            </div>
        </div>
        <div class="panel-body">
            <x-bar-chart :data="$deret" kunci="nilai" format="rupiah" />
        </div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <div>
                <h2>Komposisi Berat</h2>
                <p class="panel-sub">Minyak dan endapan yang sudah Anda timbang</p>
            </div>
        </div>
        <div class="panel-body">
            @php
                $total = $totalMinyak + $totalEndapan;
                $pMinyak = $total > 0 ? ($totalMinyak / $total) * 100 : 0;
                $pEndapan = $total > 0 ? ($totalEndapan / $total) * 100 : 0;
            @endphp

            @if ($total > 0)
                <div class="quality-bar" style="height:14px;">
                    <span style="width: {{ $pMinyak }}%; background:#DB9A26;" title="Minyak"></span>
                    <span style="width: {{ $pEndapan }}%; background:#5A3310;" title="Endapan"></span>
                </div>

                <div style="display:flex;gap:16px;margin-top:12px;flex-wrap:wrap;font-size:0.84rem;">
                    <span style="display:inline-flex;align-items:center;gap:6px;">
                        <span class="legend-swatch" style="background:#DB9A26;"></span>
                        Minyak <strong>{{ number_format($totalMinyak, 1, ',', '.') }} kg</strong>
                    </span>
                    <span style="display:inline-flex;align-items:center;gap:6px;">
                        <span class="legend-swatch" style="background:#5A3310;"></span>
                        Endapan <strong>{{ number_format($totalEndapan, 1, ',', '.') }} kg</strong>
                    </span>
                </div>

                <dl class="kv" style="margin-top:14px;">
                    <div><dt>Harga minyak</dt><dd>Rp{{ number_format($hargaMinyak, 0, ',', '.') }} / kg</dd></div>
                    <div><dt>Harga endapan</dt><dd>Rp{{ number_format($hargaEndapan, 0, ',', '.') }} / kg</dd></div>
                </dl>
            @else
                <p class="text-muted" style="margin:0;">Belum ada setoran yang Anda selesaikan.</p>
            @endif
        </div>
    </div>
</div>

<div id="container-menunggu">
    @include('pengepul.partials.menunggu', ['menunggu' => $menunggu])
</div>

<div class="panel">
    <div class="panel-head">
        <div>
            <h2>Sedang Diproses Anda ({{ count($diproses) }})</h2>
            <p class="panel-sub">Konfirmasi segera setelah menimbang di lokasi</p>
        </div>
    </div>
    <div class="panel-body panel-body-flush">
        @if (count($diproses) === 0)
            <x-empty title="Tidak ada tugas diproses"
                     message="Ambil salah satu tugas di bawah untuk mulai bekerja."
                     icon="truck" />
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Warga</th>
                            <th class="num">Perkiraan</th>
                            <th>Alamat</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($diproses as $s)
                            <tr>
                                <td>{{ $s->tanggal_ajukan->format('d M Y') }}</td>
                                <td>
                                    <span class="cell-title">{{ $s->user->name }}</span><br>
                                    <small class="cell-sub">{{ $s->user->no_hp_formatted ?? 'Tanpa nomor HP' }}</small>
                                </td>
                                <td class="num"><x-perkiraan :setoran="$s" /></td>
                                <td style="max-width:240px;">{{ $s->alamat_jemput }}</td>
                                <td class="text-right">
                                    <a href="{{ route('pengepul.verifikasi', $s) }}" class="btn btn-sm">Konfirmasi</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    const wadah = document.getElementById('container-menunggu');
    if (!wadah) return;

    let jumlahAwal = {{ count($menunggu) }};

    async function segarkan() {
        try {
            const res = await fetch('{{ route('pengepul.menunggu') }}', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) return;
            const data = await res.json();
            wadah.innerHTML = data.html;
            if (data.jumlah > jumlahAwal) {
                wadah.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
            jumlahAwal = data.jumlah;
        } catch (e) { /* coba lagi nanti */ }
    }

    setInterval(segarkan, 15000);
})();
</script>
@endpush
