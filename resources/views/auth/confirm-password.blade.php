@php
    $title = 'Konfirmasi Kata Sandi';
@endphp

<x-guest-layout :title="$title">
    <h1>Konfirmasi Kata Sandi</h1>
    <p class="auth-sub">Ini area aman. Masukkan kata sandi Anda sekali lagi untuk melanjutkan.</p>

    <form data-validasi data-form="konfirmasiSandi" novalidate method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div>
            <label for="password">Kata Sandi</label>
            <input id="password" type="password" name="password" required autocomplete="current-password" autofocus>
        </div>

        <button type="submit" class="btn" style="width:100%;">Konfirmasi</button>

                <x-form-validasi :form="'konfirmasiSandi'" />
    </form>
</x-guest-layout>
