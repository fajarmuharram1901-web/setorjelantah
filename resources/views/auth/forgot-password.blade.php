@php
    $title = 'Lupa Kata Sandi';
@endphp

<x-guest-layout :title="$title">
    <h1>Lupa Kata Sandi</h1>
    <p class="auth-sub">Masukkan email terdaftar, kami kirim tautan untuk mengatur ulang kata sandi.</p>

    <form data-validasi data-form="lupaSandi" method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf

        <div>
            <label for="email">Email</label>
            <input id="email" type="email" name="email"
                   value="{{ old('email', $emailLama ?? '') }}"
                   placeholder="contoh: nama@email.com"
                   autocomplete="email" autofocus>
        </div>

        <button type="submit" class="btn" style="width:100%;">Kirim Tautan Reset</button>
    
        <x-form-validasi :form="'lupaSandi'" />
    </form>

    <p class="auth-foot">Ingat kata sandinya? <a href="{{ route('login') }}">Kembali ke halaman masuk</a></p>
</x-guest-layout>
