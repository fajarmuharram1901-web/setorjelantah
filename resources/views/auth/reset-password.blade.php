@php
    $title = 'Atur Ulang Kata Sandi';
@endphp

<x-guest-layout :title="$title">
    <h1>Atur Ulang Kata Sandi</h1>
    <p class="auth-sub">Buat kata sandi baru untuk akun Anda.</p>

    <form data-validasi data-form="resetSandi" novalidate method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}"
                   required autofocus autocomplete="username">
        </div>

        <div>
            <label for="password">Kata Sandi Baru</label>
            <input id="password" type="password" name="password" required autocomplete="new-password">
        </div>

        <div>
            <label for="password_confirmation">Ulangi Kata Sandi</label>
            <input id="password_confirmation" type="password" name="password_confirmation"
                   required autocomplete="new-password">
        </div>

        <button type="submit" class="btn" style="width:100%;">Simpan Kata Sandi Baru</button>

                <x-form-validasi :form="'resetSandi'" />
    </form>
</x-guest-layout>
