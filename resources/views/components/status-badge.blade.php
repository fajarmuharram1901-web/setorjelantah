@props(['status'])

@php
    $kelas = [
        'menunggu' => 'warning',
        'diproses' => 'info',
        'selesai' => 'success',
        'dibatalkan' => 'danger',
    ][$status] ?? 'secondary';
@endphp

<span class="badge badge-{{ $kelas }}">{{ ucfirst($status) }}</span>
