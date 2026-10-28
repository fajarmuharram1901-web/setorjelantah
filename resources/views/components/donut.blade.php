@props([
    'data' => [],   // array of ['label' =>, 'nilai' =>, 'warna' =>]
    'kunci' => 'jumlah',
    'totalLabel' => 'Total',
])

@php
    $total = 0;
    foreach ($data as $potongan) {
        $total += (float) ($potongan[$kunci] ?? 0);
    }

    // Susun gradient conic sesuai urutan data.
    $sudut = 0;
    $bagian = [];
    foreach ($data as $potongan) {
        $nilai = (float) ($potongan[$kunci] ?? 0);
        if ($nilai <= 0) {
            continue;
        }
        $porsi = ($nilai / max($total, 1)) * 100;
        $akhir = $sudut + $porsi;
        $warna = $potongan['warna'] ?? '#BE7E1D';
        $bagian[] = "{$warna} {$sudut}% {$akhir}%";
        $sudut = $akhir;
    }

    $gaya = $bagian ? 'conic-gradient(' . implode(', ', $bagian) . ')' : 'conic-gradient(var(--line-soft) 0 100%)';
@endphp

<div class="donut-wrap">
    <div class="donut" style="background: {{ $gaya }};">
        <div class="donut-center">
            <strong>{{ number_format($total, 0, ',', '.') }}</strong>
            <span>{{ $totalLabel }}</span>
        </div>
    </div>

    <ul class="legend-list">
        @forelse ($data as $potongan)
            @php $nilai = (float) ($potongan[$kunci] ?? 0); @endphp
            <li class="legend-row">
                <span class="legend-swatch" style="background: {{ $potongan['warna'] ?? '#BE7E1D' }};"></span>
                <span class="legend-name">{{ $potongan['label'] ?? '' }}</span>
                <span class="legend-val">{{ number_format($nilai, 0, ',', '.') }}</span>
            </li>
        @empty
            <li class="legend-row"><span class="text-muted">Belum ada data.</span></li>
        @endforelse
    </ul>
</div>
