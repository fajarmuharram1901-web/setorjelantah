@php
    $title = 'Masuk';
@endphp

<x-guest-layout :title="$title">
    <h1>Masuk</h1>
    <p class="auth-sub">Gunakan akun yang sudah terdaftar untuk melanjutkan.</p>

    <form data-validasi data-form="masuk" method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        <div>
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   placeholder="contoh: nama@email.com" required autofocus autocomplete="username">
        </div>

        <div>
            <label for="password">Kata Sandi</label>
            <input id="password" type="password" name="password" required autocomplete="current-password">
        </div>

        <div class="flex-between" style="margin:0;">
            <label class="check" style="align-items:center;">
                <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                <span class="check-kotak" aria-hidden="true"></span>
                <span>Ingat saya</span>
            </label>
            <a href="{{ route('password.request') }}" style="font-size:0.85rem;">Lupa kata sandi?</a>
        </div>

        <button type="submit" class="btn" style="width:100%;">Masuk</button>

        <x-form-validasi :form="'masuk'" />
    </form>

    <p class="auth-foot">Belum punya akun? <a href="{{ route('register') }}">Daftar di sini</a></p>
</x-guest-layout>