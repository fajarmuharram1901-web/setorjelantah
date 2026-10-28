@props([
    'data' => [],          // array of ['label' =>, 'nilai' =>, 'warna' =>]
    'kunci' => 'nilai',
    'format' => 'number',  // number | kg | rupiah
    'height' => 190,
])

@php
    $maks = 0;
    foreach ($data as $baris) {
        $maks = max($maks, (float) ($baris[$kunci] ?? 0));
    }

    $formatNilai = function ($angka) use ($format) {
        if ($format === 'kg') {
            return number_format((float) $angka, 1, ',', '.') . ' kg';
        }
        if ($format === 'rupiah') {
            return 'Rp' . number_format((float) $angka, 0, ',', '.');
        }
        return number_format((float) $angka, 0, ',', '.');
    };
@endphp

<div class="chart" style="height: {{ $height }}px;">
    @forelse ($data as $baris)
        @php
            $nilai = (float) ($baris[$kunci] ?? 0);
            $tinggi = $maks > 0 ? max(($nilai / $maks) * 100, $nilai > 0 ? 4 : 0) : 0;
        @endphp
        <div class="chart-col">
            <div class="chart-bar{{ $nilai <= 0 ? ' is-empty' : '' }}"
                 style="height: {{ $tinggi }}%;{{ ! empty($baris['warna']) ? 'background:' . $baris['warna'] : '' }}">
                <span class="chart-bar-tip">{{ $formatNilai($nilai) }}</span>
            </div>
            <span class="chart-x">{{ $baris['label'] ?? '' }}</span>
        </div>
    @empty
        <p class="text-muted" style="width:100%;text-align:center;">Belum ada data untuk ditampilkan.</p>
    @endforelse
</div>
