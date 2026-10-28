@props([
    'label',
    'value',
    'icon' => 'chart',
    'accent' => '#BE7E1D',
    'tint' => '#FBF0DD',
    'trend' => null,
    'trendLabel' => null,
    'meta' => null,
    'small' => false,
])

@php
    // Nilai panjang seperti "Rp1.400.000" prone berpatah di tengah angka kalau
    // font tetap besar, jadi ukuran hurufnya diturunkan bertahap.
    $panjang = mb_strlen(strip_tags((string) $value));
    $kelasNilai = $small ? ' stat-value-sm' : '';

    if ($panjang > 14) {
        $kelasNilai .= ' stat-value-xs';
    } elseif ($panjang > 9) {
        $kelasNilai .= ' stat-value-md';
    }
@endphp

<div class="stat-card" style="--accent: {{ $accent }}; --accent-tint: {{ $tint }};">
    <span class="stat-icon"><x-icon :name="$icon" :size="19" /></span>
    <div class="stat-body">
        <p class="stat-label">{{ $label }}</p>
        <p class="stat-value{{ $kelasNilai }}">{{ $value }}</p>
        @if ($trend !== null || $meta)
            <div class="stat-meta">
                @if ($trend !== null)
                    @php
                        $kelas = $trend > 0.05 ? 'trend-up' : ($trend < -0.05 ? 'trend-down' : 'trend-flat');
                        $arah = $trend > 0.05 ? 'up' : ($trend < -0.05 ? 'down' : 'flat');
                    @endphp
                    <span class="trend trend-{{ $kelas }}">
                        <x-icon :name="'trend-' . $arah" :size="12" />
                        {{ $trend > 0 ? '+' : '' }}{{ number_format($trend, 1) }}%
                    </span>
                    @if ($trendLabel)
                        <span>{{ $trendLabel }}</span>
                    @endif
                @endif
                @if ($meta)
                    <span>{{ $meta }}</span>
                @endif
            </div>
        @endif
    </div>
</div>
