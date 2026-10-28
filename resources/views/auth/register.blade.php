@php
    $title = 'Daftar Akun';
@endphp

<x-guest-layout :title="$title">
    <h1>Daftar Akun</h1>
    <p class="auth-sub">Mulai kumpulkan minyak jelantah dan kumpulkan saldo Anda.</p>

    <form data-validasi data-form="daftar" method="POST" action="{{ route('register') }}">
        @csrf

        <div>
            <label for="nama">Nama Lengkap</label>
            <input id="nama" type="text" name="name" value="{{ old('name') }}"
                   required autofocus autocomplete="name">
        </div>

        <div>
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   required autocomplete="username">
        </div>

        <div>
            <label for="no_hp">Nomor HP (opsional)</label>
            <input id="no_hp" type="text" inputmode="numeric" name="no_hp" value="{{ old('no_hp') }}"
                   placeholder="81234567890" autocomplete="tel">
            <p class="form-hint">Tanpa angka 0 di depan, dipakai pengepul saat menjemput.</p>
        </div>

        <div>
            <label for="alamat">Alamat (opsional)</label>
            <textarea id="alamat" name="alamat" rows="2" placeholder="Alamat yang mudah dijemput">{{ old('alamat') }}</textarea>
        </div>

        <div>
            <label for="password">Kata Sandi</label>
            <input id="password" type="password" name="password" required autocomplete="new-password">
        </div>

        <div>
            <label for="password_confirmation">Ulangi Kata Sandi</label>
            <input id="password_confirmation" type="password" name="password_confirmation"
                   required autocomplete="new-password">
        </div>

        <label class="check">
            <input type="checkbox" name="setuju" value="1" required data-gerbang="#tombol-daftar"
                   {{ old('setuju') ? 'checked' : '' }}>
            <span class="check-kotak" aria-hidden="true"></span>
            <span>Saya setuju data setoran dan alamat saya dipakai pengepul untuk penjemputan.</span>
        </label>

        <button type="submit" id="tombol-daftar" class="btn" style="width:100%;">Daftar</button>
    
        <x-form-validasi :form="'daftar'" />
    </form>

    <p class="auth-foot">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p>
</x-guest-layout>