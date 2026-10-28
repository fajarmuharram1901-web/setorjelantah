@extends('layouts.dashboard', ['title' => 'Buku Besar ' . $user->name, 'subtitle' => 'Riwayat saldo pengguna'])

@section('content')

<div class="page-head">
    <div>
        <h1 class="page-title">Buku Besar {{ $user->name }}</h1>
        <p class="page-sub">{{ $user->email }} &middot; {{ $user->labelPeran() }}</p>
    </div>
    <div class="panel-actions">
        <a href="{{ route('admin.pengguna') }}" class="btn btn-secondary">
            <x-icon name="arrow-right" :size="15" /> Kembali ke Pengguna
        </a>
    </div>
</div>

<div class="grid-2-panel">
    <div>
        <div class="balance-card">
            <p class="label">Saldo {{ $user->name }}</p>
            <p class="amount">Rp{{ number_format($user->saldo, 0, ',', '.') }}</p>
            <div class="foot">
                <div>
                    <span>Bisa dicairkan</span>
                    <strong>Rp{{ number_format($user->saldoTersedia(), 0, ',', '.') }}</strong>
                </div>
                <div>
                    <span>Total setoran</span>
                    <strong>{{ $user->isUser() ? $user->setoran()->count() : $user->tugasPengepul()->count() }}</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <h2>Pencairan Terakhir</h2>
        </div>
        <div class="panel-body panel-body-flush">
            @if ($pencairans->isEmpty())
                <x-empty title="Belum ada pencairan" message="Pengguna ini belum pernah requesting pencairan." icon="cash-out" />
            @else
                <div class="table-wrap">
                    <table class="compact">
                        <thead><tr><th>Kode</th><th class="num">Jumlah</th><th>Status</th><th>Diminta</th></tr></thead>
                        <tbody>
                            @foreach ($pencairans as $p)
                                <tr>
                                    <td class="cell-title">{{ $p->kode }}</td>
                                    <td class="num">Rp{{ number_format($p->jumlah, 0, ',', '.') }}</td>
                                    <td><span class="badge badge-{{ $p->badgeStatus() }}">{{ ucfirst($p->status) }}</span></td>
                                    <td>{{ $p->created_at->format('d M Y') }}</td>
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
    <div class="panel-head">
        <h2>Mutasi Saldo</h2>
    </div>
    <div class="panel-body panel-body-flush">
        @if ($transaksi->isEmpty())
            <x-empty title="Belum ada mutasi" message="Setiap perubahan saldo tercatat di sini." icon="history" />
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th><th>Jenis</th><th>Keterangan</th>
                            <th class="num">Saldo Sebelumnya</th><th class="num">Perubahan</th><th class="num">Saldo Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transaksi as $t)
                            <tr>
                                <td>{{ $t->created_at->format('d M Y H:i') }}</td>
                                <td>{{ $t->labelTipe() }}</td>
                                <td style="max-width:300px;">{{ $t->keterangan ?? '-' }}</td>
                                <td class="num text-muted">Rp{{ number_format($t->saldo_sebelum, 0, ',', '.') }}</td>
                                <td class="num">
                                    <strong style="color: {{ $t->jumlah >= 0 ? 'var(--olive)' : 'var(--rust)' }};">
                                        {{ $t->jumlah >= 0 ? '+' : '-' }}Rp{{ number_format(abs((float) $t->jumlah), 0, ',', '.') }}
                                    </strong>
                                </td>
                                <td class="num">Rp{{ number_format($t->saldo_sesudah, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if ($transaksi->hasPages())
        {{ $transaksi->links() }}
    @endif
</div>

@endsection
