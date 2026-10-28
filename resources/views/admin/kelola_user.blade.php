@extends('layouts.dashboard', ['title' => 'Kelola Pengguna', 'subtitle' => 'Akun warga, pengepul, dan administrator'])

@section('content')

<div class="page-head">
    <div>
        <h1 class="page-title">Kelola Pengguna</h1>
        <p class="page-sub">Total {{ $totalUser }} rumah tangga dan {{ $totalPengepul }} pengepul.</p>
    </div>
</div>

<div class="grid-2-panel">
    <div class="panel">
        <div class="panel-head">
            <h2>Buat Akun Pengepul</h2>
        </div>
        <div class="panel-body">
            <p class="text-muted" style="margin-top:0;font-size:0.86rem;">
                Akun pengepul dibuat oleh admin. Pengepul bisa mengambil tugas, mengukur, dan mengonfirmasi setoran.
            </p>
            <form data-validasi data-form="tambahPengepul" novalidate method="POST" action="{{ route('admin.pengguna.pengepul') }}" style="max-width:100%;">
                @csrf
                <div class="form-grid-2">
                    <div>
                        <label for="nama_baru">Nama</label>
                        <input id="nama_baru" type="text" name="name" value="{{ old('name') }}" required>
                    </div>
                    <div>
                        <label for="email_baru">Email</label>
                        <input id="email_baru" type="email" name="email" value="{{ old('email') }}" required>
                    </div>
                    <div>
                        <label for="no_hp_baru">No. HP</label>
                        <input id="no_hp_baru" type="text" inputmode="numeric" name="no_hp" value="{{ old('no_hp') }}">
                    </div>
                    <div>
                        <label for="pw_baru">Kata Sandi</label>
                        <input id="pw_baru" type="password" name="password" required minlength="6">
                    </div>
                </div>
                <button type="submit" class="btn">Buat Akun Pengepul</button>
            <x-form-validasi :form="'tambahPengepul'" />
    </form>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <h2>Daftar Pengepul ({{ $pengepulList->count() }})</h2>
        </div>
        <div class="panel-body panel-body-flush">
            @if ($pengepulList->isEmpty())
                <x-empty title="Belum ada pengepul" message="Buat akun pengepul di formulir sebelah." icon="truck" />
            @else
                <div class="table-wrap">
                    <table class="compact">
                        <thead><tr><th>Nama</th><th>Email</th><th class="num">Saldo</th><th></th></tr></thead>
                        <tbody>
                            @foreach ($pengepulList as $p)
                                <tr>
                                    <td class="cell-title">{{ $p->name }}</td>
                                    <td style="font-size:0.82rem;">{{ $p->email }}</td>
                                    <td class="num">Rp{{ number_format($p->saldo, 0, ',', '.') }}</td>
                                    <td class="text-right">
                                        <div class="table-actions" style="justify-content:flex-end;">
                                            <a href="{{ route('admin.pengguna.edit', $p) }}" class="btn btn-sm btn-secondary">Edit</a>
                                            <form method="POST" action="{{ route('admin.pengguna.hapus', $p) }}"
                                                  style="max-width:none;margin:0;" data-konfirmasi="Hapus akun ini? Tindakan ini tidak bisa dibatalkan." data-judul="Hapus akun" data-ok="Ya, hapus" data-bahaya>
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="panel">
    <form method="GET" action="{{ route('admin.pengguna') }}" class="filters">
        <div class="field field-grow">
            <label for="cari">Cari Pengguna</label>
            <input id="cari" type="search" name="cari" value="{{ $cari }}" placeholder="Nama atau email...">
        </div>
        <div class="field">
            <label for="peran">Peran</label>
            <select id="peran" name="peran" data-dropdown data-auto-submit>
                <option value="">Semua peran</option>
                @foreach (['user' => 'Rumah Tangga', 'pengepul' => 'Pengepul', 'admin' => 'Admin'] as $k => $v)
                    <option value="{{ $k }}" @selected($peran === $k)>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div style="display:flex;gap:8px;">
            <button type="submit" class="btn btn-sm">
                <x-icon name="filter" :size="14" /> Terapkan
            </button>
            <a href="{{ route('admin.pengguna') }}" class="btn btn-sm btn-secondary">Reset</a>
        </div>
    </form>

    <div class="filter-summary">
        Menampilkan <strong>{{ $pengguna->total() }}</strong> akun
        @if ($pengguna->total() > 0)
            &middot; halaman {{ $pengguna->firstItem() }}&ndash;{{ $pengguna->lastItem() }}
        @endif
    </div>

    <div class="panel-body panel-body-flush">
        @if ($pengguna->isEmpty())
            <x-empty title="Tidak ada pengguna yang cocok" message="Coba kata kunci atau peran lain." icon="search" />
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Nama</th><th>Email</th><th>Peran</th><th>No. HP</th>
                            <th class="num">Saldo</th><th class="num">Setoran</th><th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pengguna as $u)
                            <tr>
                                <td class="cell-title">{{ $u->name }}</td>
                                <td style="font-size:0.84rem;">{{ $u->email }}</td>
                                <td>
                                    @php
                                        $warnaPeran = ['admin' => 'danger', 'pengepul' => 'info', 'user' => 'success'][$u->role] ?? 'secondary';
                                    @endphp
                                    <span class="badge badge-{{ $warnaPeran }}">{{ $u->labelPeran() }}</span>
                                </td>
                                <td>{{ $u->no_hp_formatted ?? '-' }}</td>
                                <td class="num">
                                    <strong>Rp{{ number_format($u->saldo, 0, ',', '.') }}</strong>
                                    @if ($u->saldo > 0)
                                        <br><a href="{{ route('admin.saldo', $u) }}" class="cell-sub">Lihat buku besar</a>
                                    @endif
                                </td>
                                <td class="num">{{ $u->isUser() ? $u->setoran()->count() : $u->tugasPengepul()->count() }}</td>
                                <td>
                                    <div class="table-actions">
                                        @if ($u->isAdmin())
                                            <span class="text-muted" style="font-size:0.8rem;">Tidak dapat diubah</span>
                                        @else
                                            <a href="{{ route('admin.pengguna.edit', $u) }}" class="btn btn-sm btn-secondary">Edit</a>
                                            <form method="POST" action="{{ route('admin.pengguna.hapus', $u) }}"
                                                  style="max-width:none;margin:0;" data-konfirmasi="Hapus akun ini? Tindakan ini tidak bisa dibatalkan." data-judul="Hapus akun" data-ok="Ya, hapus" data-bahaya>
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if ($pengguna->hasPages())
        {{ $pengguna->links('vendor.pagination.custom') }}
    @endif
</div>

@endsection