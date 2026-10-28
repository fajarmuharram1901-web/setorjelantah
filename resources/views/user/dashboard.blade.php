@extends('layouts.dashboard', ['title' => 'Dashboard Saya', 'subtitle' => 'Ringkasan setoran dan saldo Anda'])

@section('content')

<div class="page-head">
    <div>
        <h1 class="page-title">Halo, {{ $user->name }}</h1>
        <p class="page-sub">Pantau setoran minyak, endapan, dan saldo yang Anda kumpulkan.</p>
    </div>
    <div class="panel-actions">
        <a href="{{ route('user.saldo') }}" class="btn btn-secondary">
            <x-icon name="wallet" :size="15" /> Saldo &amp; Transaksi
        </a>
        <a href="{{ route('user.ajukan') }}" class="btn">
            <x-icon name="plus-circle" :size="15" /> Ajukan Setor
        </a>
    </div>
</div>

<div id="container-user">
    @include('user.partials.dashboard-content', [
        'user' => $user,
        'setoranTerbaru' => $setoranTerbaru,
        'menunggu' => $menunggu,
        'diproses' => $diproses,
        'totalMinyak' => $totalMinyak,
        'totalEndapan' => $totalEndapan,
        'totalKg' => $totalKg,
        'totalNilai' => $totalNilai,
        'jumlahSelesai' => $jumlahSelesai,
        'deret' => $deret,
        'saldoTersedia' => $saldoTersedia,
        'bisaPencair' => $bisaPencair,
        'menungguPencairan' => $menungguPencairan,
        'hargaMinyak' => $hargaMinyak,
        'hargaEndapan' => $hargaEndapan,
    ])
</div>

@endsection

@push('scripts')
<script>
(function () {
    const wadah = document.getElementById('container-user');
    if (!wadah) return;

    // Muat ulang sebentar supaya angka tidak terlihat basi, tanpa memberatkan server.
    async function segarkan() {
        try {
            const res = await fetch('{{ route('user.dashboard.data') }}', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) return;
            const data = await res.json();
            if (data.html && data.html.length > 0) {
                wadah.innerHTML = data.html;
            }
        } catch (e) { /* coba lagi nanti */ }
    }

    setInterval(segarkan, 15000);
})();
</script>
@endpush
