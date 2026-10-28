@php
    $title = 'Verifikasi Email';
@endphp

<x-guest-layout :title="$title">
    <h1>Verifikasi Email</h1>
    <p class="auth-sub">
        Terima kasih sudah mendaftar. Buka tautan yang kami kirim ke email Anda untuk mengaktifkan akun.
    </p>

    @if (session('status') === 'verification-link-sent')
        <div class="alert alert-success">Tautan verifikasi baru sudah dikirim ke alamat email Anda.</div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}" style="max-width:none;">
        @csrf
        <button type="submit" class="btn" style="width:100%;">Kirim Ulang Email Verifikasi</button>
    </form>

    <form method="POST" action="{{ route('logout') }}" style="max-width:none;margin-top:10px;">
        @csrf
        <button type="submit" class="btn btn-secondary" style="width:100%;">Keluar</button>
    </form>
</x-guest-layout>
