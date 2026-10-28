@extends('layouts.dashboard', ['title' => 'Dashboard Admin', 'subtitle' => 'Ringkasan operasional, keuangan, dan berat minyak'])

@section('content')

<div class="page-head">
    <div>
        <h1 class="page-title">Ringkasan Operasional</h1>
        <p class="page-sub">Pantau berat minyak dan endapan, nilai yang dibagikan, status pengajuan, serta permintaan pencairan.</p>
    </div>
    <div class="panel-actions">
        <a href="{{ route('admin.laporan') }}" class="btn btn-secondary">
            <x-icon name="chart" :size="15" /> Laporan
        </a>
        <a href="{{ route('admin.laporan.unduh') }}" class="btn">
            <x-icon name="download" :size="15" /> Ekspor CSV
        </a>
    </div>
</div>

<div id="container-admin">
    @include('admin.partials.dashboard-content', [
        'totalUser' => $totalUser,
        'totalPengepul' => $totalPengepul,
        'menunggu' => $menunggu,
        'diproses' => $diproses,
        'totalMinyak' => $totalMinyak,
        'totalEndapan' => $totalEndapan,
        'totalKg' => $totalKg,
        'totalDisalurkan' => $totalDisalurkan,
        'hargaMinyak' => $hargaMinyak,
        'hargaEndapan' => $hargaEndapan,
        'terbaru' => $terbaru,
        'deret' => $deret,
        'ringkasan' => $ringkasan,
        'donut' => $donut,
        'topWarga' => $topWarga,
        'topPengepul' => $topPengepul,
        'jumlahPencairan' => $jumlahPencairan,
        'nilaiPencairan' => $nilaiPencairan,
        'saldoSeluruhWarga' => $saldoSeluruhWarga,
        'jumlahTransaksi' => $jumlahTransaksi,
    ])
</div>

@endsection

@push('scripts')
<script>
(function () {
    const wadah = document.getElementById('container-admin');
    if (!wadah) return;

    async function segarkan() {
        try {
            const res = await fetch('{{ route('admin.dashboard.data') }}', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) return;
            const data = await res.json();
            if (data.html && data.html.length > 0) {
                wadah.innerHTML = data.html;
            }
        } catch (e) { /* coba lagi nanti */ }
    }

    setInterval(segarkan, 20000);
})();
</script>
@endpush
