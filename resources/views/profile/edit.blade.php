@extends('layouts.dashboard', ['title' => 'Pengaturan Akun', 'subtitle' => 'Kelola data profil dan keamanan akun Anda'])

@section('content')

<div class="page-head">
    <div>
        <h1 class="page-title">Pengaturan Akun</h1>
        <p class="page-sub">Perbarui nama, email, nomor HP, dan alamat yang dipakai pengepul untuk menjemput minyak.</p>
    </div>
</div>

<div class="grid-2-panel-wide">
    <div class="panel">
        <div class="panel-head">
            <div>
                <h2>Informasi Profil</h2>
                <p class="panel-sub">Alamat dan nomor HP membantu pengepul menemukan lokasi penjemputan.</p>
            </div>
        </div>
        <div class="panel-body">
            <form data-validasi data-form="profil" novalidate method="POST" action="{{ route('profile.update') }}" class="form-narrow">
                @csrf
                @method('PATCH')

                <div>
                    <label for="nama">Nama Lengkap</label>
                    <input id="nama" type="text" name="name" value="{{ old('name', $user->name) }}"
                           required autocomplete="name" autofocus>
                </div>

                <div>
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" data-abaikan-id="{{ $user->id }}" value="{{ old('email', $user->email) }}"
                           required autocomplete="email">
                    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                        <p class="form-hint">Email Anda belum diverifikasi. <a href="{{ route('verification.send') }}">Kirim ulang kode verifikasi</a>.</p>
                    @endif
                </div>

                <div class="form-grid-2">
                    <div>
                        <label for="no_hp">Nomor HP</label>
                        <input id="no_hp" type="text" inputmode="numeric" name="no_hp"
                               value="{{ old('no_hp', $user->no_hp) }}" placeholder="81234567890">
                        <p class="form-hint">Tanpa angka 0 di depan. Tampil sebagai {{ $user->no_hp_formatted ?? '+62...' }} di halaman pengepul.</p>
                    </div>
                    <div>
                        <label>Peran</label>
                        <input type="text" value="{{ $user->labelPeran() }}" disabled>
                        <p class="form-hint">Peran hanya bisa diubah oleh admin.</p>
                    </div>
                </div>

                <div>
                    <label for="alamat">Alamat (opsional)</label>
                    <textarea id="alamat" name="alamat" rows="3" placeholder="Alamat lengkap yang mudah dijemput">{{ old('alamat', $user->alamat) }}</textarea>
                    <p class="form-hint">Otomatis dipakai sebagai awal isian alamat penjemputan.</p>
                </div>

                <div>
                    <button type="submit" class="btn">Simpan Perubahan</button>
                </div>
            <x-form-validasi :form="'profil'" :pengganti="['id' => $user->id]" />
    <x-form-validasi :form="'gantiSandi'" />
    </form>
        </div>
    </div>

    <div>
        <div class="panel">
            <div class="panel-head">
                <div>
                    <h2>Ganti Kata Sandi</h2>
                    <p class="panel-sub">Gunakan kata sandi yang panjang dan sulit ditebak.</p>
                </div>
            </div>
            <div class="panel-body">
                <form data-validasi data-form="gantiSandi" novalidate method="POST" action="{{ route('password.update') }}" class="form-narrow">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="pw_lama">Kata Sandi Saat Ini</label>
                        <input id="pw_lama" type="password" name="current_password"
                               autocomplete="current-password" placeholder="Kata sandi sekarang">
                    </div>

                    <div>
                        <label for="pw_baru">Kata Sandi Baru</label>
                        <input id="pw_baru" type="password" name="password"
                               autocomplete="new-password" placeholder="Minimal 8 karakter">
                    </div>

                    <div>
                        <label for="pw_konfirmasi">Ulangi Kata Sandi Baru</label>
                        <input id="pw_konfirmasi" type="password" name="password_confirmation"
                               autocomplete="new-password" placeholder="Ketik ulang kata sandi baru">
                    </div>

                    <div>
                        <button type="submit" class="btn">Perbarui Kata Sandi</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <div>
                    <h2>Hapus Akun</h2>
                    <p class="panel-sub">Tindakan ini tidak dapat dibatalkan.</p>
                </div>
            </div>
            <div class="panel-body">
                @if ($user->role === 'admin')
                    <div class="alert alert-warning" style="margin:0;">
                        Akun dengan peran Administrator tidak dapat dihapus dari sini. Hubungi pengelola sistem bila perlu dihapus.
                    </div>
                @else
                    <p class="text-muted">
                        Menghapus akun akan menghapus seluruh riwayat setoran Anda. Tindakan ini permanen.
                    </p>
                    <form method="POST" action="{{ route('profile.destroy') }}" class="form-narrow"
                          data-konfirmasi="Yakin ingin menghapus akun Anda? Seluruh riwayat setoran ikut terhapus." data-judul="Hapus akun" data-ok="Ya, hapus akun" data-bahaya>
                        @csrf
                        @method('DELETE')

                        <div>
                            <label for="pw_hapus">Konfirmasi dengan kata sandi</label>
                            <input id="pw_hapus" type="password" name="password"
                                   autocomplete="current-password" placeholder="Kata sandi Anda">
                        </div>

                        <div>
                            <button type="submit" class="btn btn-danger">Hapus Akun Saya</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection