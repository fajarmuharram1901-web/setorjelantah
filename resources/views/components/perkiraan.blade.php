@props([
    'setoran',
    'desimal' => 1,
])

{{-- Ringkasan perkiraan warga: minyak dan endapan, dua baris. --}}
<span class="cell-title">
    {{ number_format($setoran->perkiraanMinyakKg(), $desimal, ',', '.') }} kg
</span><br>
<small class="cell-sub">
    + {{ number_format($setoran->perkiraanEndapanKg(), $desimal, ',', '.') }} kg endapan
</small>
