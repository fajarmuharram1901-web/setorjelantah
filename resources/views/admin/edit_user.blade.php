@extends('layouts.dashboard', ['title' => 'Edit Pengguna', 'subtitle' => 'Perbarui data akun ' . $user->name])

@section('content')

<div class="page-head">
    <div>
        <h1 class="page-title">Edit Akun</h1>
        <p class="page-sub">Ubah identitas, peran, dan kontak pengguna.</p>
    </div>
    <div class="panel-actions">
        <a href="{{ route('admin.pengguna') }}" class="btn btn-secondary">
            <x-icon name="arrow-right" :size="15" /> Kembali
        </a>
    </div>
</div>

<div class="panel form-narrow" style="max-width:100%;">
    <div class="panel-head">
        <h2>Data {{ $user->name }}</h2>
        <span class="badge badge-secondary">{{ $user->labelPeran() }}</span>
    </div>
    <div class="panel-body">
        <form data-validasi data-form="editPengguna" novalidate method="POST" action="{{ route('admin.pengguna.update', $user) }}" style="max-width:100%;">
            @csrf
            @method('PUT')

            <div>
                <label for="nama">Nama</label>
                <input id="nama" type="text" name="name" value="{{ old('name', $user->name) }}" required>
            </div>

            <div>
                <label for="email">Email</label>
                <input id="email" type="email" name="email" data-abaikan-id="{{ $user->id }}" value="{{ old('email', $user->email) }}" required>
            </div>

            <div class="form-grid-2">
                <div>
                    <label for="no_hp">No. HP</label>
                    <input id="no_hp" type="text" inputmode="numeric" name="no_hp" value="{{ old('no_hp', $user->no_hp) }}">
                </div>
                <div>
                    <label for="role">Peran</label>
                    <select id="role" name="role" required>
                        @foreach (['user' => 'Rumah Tangga', 'pengepul' => 'Pengepul'] as $k => $v)
                            <option value="{{ $k }}" @selected(old('role', $user->role) === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label for="alamat">Alamat</label>
                <textarea id="alamat" name="alamat" rows="3">{{ old('alamat', $user->alamat) }}</textarea>
            </div>

            <div>
                <button type="submit" class="btn">Simpan Perubahan</button>

                <x-form-validasi :form="'editPengguna'" :pengganti="['id' => $user->id]" />
            </div>
        </form>
    </div>
</div>

@endsection
