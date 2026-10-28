@extends('layouts.dashboard', ['title' => 'Riwayat Setoran', 'subtitle' => 'Semua pengajuan minyak yang pernah Anda kirim'])

@section('content')

<div class="page-head">
    <div>
        <h1 class="page-title">Riwayat Setoran</h1>
        <p class="page-sub">Saring berdasarkan status, kata kunci, atau rentang tanggal.</p>
    </div>
    <div class="panel-actions">
        <a href="{{ route('user.saldo') }}" class="btn btn-secondary">
            <x-icon name="wallet" :size="15" /> Saldo &amp; Transaksi
        </a>
        <a href="{{ route('user.ajukan') }}" class="btn">
            <x-icon name="plus-circle" :size="15" /> Ajukan Setor
        </a>
    </div>
</div>

<div class="panel">
    <form method="GET" action="{{ route('user.riwayat') }}" class="filters">
        <div class="field field-grow">
            <label for="cari">Kata Kunci</label>
            <input id="cari" type="search" name="cari" value="{{ $filter['cari'] }}"
                   placeholder="Alamat atau catatan...">
        </div>

        <div class="field">
            <label for="status">Status</label>
            <select id="status" name="status" data-dropdown data-auto-submit>
                <option value="">Semua status</option>
                @foreach (['menunggu' => 'Menunggu', 'diproses' => 'Diproses', 'selesai' => 'Selesai', 'dibatalkan' => 'Dibatalkan'] as $k => $v)
                    <option value="{{ $k }}" @selected($filter['status'] === $k)>{{ $v }}</option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <label for="dari">Dari Tanggal</label>
            <input id="dari" type="date" name="dari" value="{{ $filter['dari'] }}">
        </div>

        <div class="field">
            <label for="sampai">Sampai Tanggal</label>
            <input id="sampai" type="date" name="sampai" value="{{ $filter['sampai'] }}">
        </div>

        <div class="field" style="flex:0 0 auto;max-width:none;">
            <button type="submit" class="btn btn-sm">
                <x-icon name="filter" :size="14" /> Terapkan
            </button>
            <a href="{{ route('user.riwayat') }}" class="btn btn-sm btn-secondary">Reset</a>
        </div>
    </form>

    <div class="filter-summary">
        Menampilkan <strong>{{ $semua->total() }}</strong> setoran
        @if ($semua->total() > 0)
            &middot; halaman {{ $semua->firstItem() }}&ndash;{{ $semua->lastItem() }} dari {{ $semua->total() }}
        @endif
    </div>

    <div class="panel-body panel-body-flush">
        @if ($semua->isEmpty())
            <x-empty title="Tidak ada setoran yang cocok"
                     message="Coba longgarkan penyaring, atau kirim pengajuan setoran baru."
                     icon="search"
                     action-label="Ajukan Setor"
                     :action-route="route('user.ajukan')" />
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th class="num">Perkiraan</th>
                            <th class="num">Minyak</th>
                            <th class="num">Endapan</th>
                            <th class="num">Total Berat</th>
                            <th>Pengepul</th>
                            <th class="num">Total</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($semua as $s)
                            <tr>
                                <td>
                                    <span class="cell-title">{{ $s->tanggal_ajukan->format('d M Y') }}</span><br>
                                    <small class="cell-sub">#{{ $s->id }}</small>
                                </td>
                                <td class="num"><x-perkiraan :setoran="$s" :desimal="2" /></td>
                                <td class="num">
                                    @if ($s->minyak_kg !== null)
                                        {{ number_format($s->minyak_kg, 2, ',', '.') }} kg
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="num">
                                    @if ($s->endapan_kg !== null)
                                        {{ number_format($s->endapan_kg, 2, ',', '.') }} kg
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="num">
                                    @if ($s->sudahSelesai())
                                        <strong>{{ number_format($s->totalTerimaKg(), 2, ',', '.') }} kg</strong>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $s->pengepul?->name ?? '-' }}</td>
                                <td class="num">
                                    @if ($s->total_saldo !== null)
                                        <strong>Rp{{ number_format($s->total_saldo, 0, ',', '.') }}</strong>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td><x-status-badge :status="$s->status" /></td>
                                <td class="text-right">
                                    <a href="{{ route('user.detail', $s) }}" class="btn btn-sm btn-secondary">Detail</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if ($semua->hasPages())
        {{ $semua->links() }}
    @endif
</div>

@endsection