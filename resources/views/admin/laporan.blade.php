@extends('layouts.dashboard', ['title' => 'Laporan Setoran', 'subtitle' => 'Saring, tinjau, dan ekspor data setoran'])

@section('content')

<div class="page-head">
    <div>
        <h1 class="page-title">Laporan Setoran</h1>
        <p class="page-sub">Ringkasan mengikuti penyaring yang aktif, termasuk saat mengunduh CSV.</p>
    </div>
    <div class="panel-actions">
        <a href="{{ route('admin.laporan.unduh', request()->query()) }}" class="btn">
            <x-icon name="download" :size="15" /> Unduh CSV
        </a>
    </div>
</div>

<div class="stat-grid stat-grid-4">
    <x-stat-card label="Total Setoran"
                 :value="$ringkasan['total']"
                 icon="inbox" accent="#3E5E7E" tint="#E9F0F6" small
                 :meta="$ringkasan['jumlah_selesai'] . ' sudah selesai'" />
    <x-stat-card label="Minyak"
                 :value="number_format($ringkasan['minyak'], 2, ',', '.') . ' kg'"
                 icon="droplet" accent="#DB9A26" tint="#FBF0DD" small
                 meta="Minyak murni" />
    <x-stat-card label="Endapan"
                 :value="number_format($ringkasan['endapan'], 2, ',', '.') . ' kg'"
                 icon="scale" accent="#5A3310" tint="#F1E7D6" small
                 :meta="'Endapan, ' . number_format($hargaEndapan, 0, ',', '.') . '/kg'" />
    <x-stat-card label="Total Nilai"
                 :value="'Rp' . number_format($ringkasan['nilai'], 0, ',', '.')"
                 icon="wallet" accent="#55703C" tint="#EAF0E2" small
                 :meta="'Rata-rata ' . number_format($ringkasan['rata_kg'], 1, ',', '.') . ' kg/setoran'" />
</div>

<div class="panel">
    <form method="GET" action="{{ route('admin.laporan') }}" class="filters">
        <div class="field field-grow">
            <label for="cari">Kata Kunci</label>
            <input id="cari" type="search" name="cari" value="{{ $filter['cari'] }}"
                   placeholder="Nama warga, pengepul, alamat...">
        </div>

        <div class="field">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="">Semua status</option>
                @foreach (['menunggu', 'diproses', 'selesai', 'dibatalkan'] as $k)
                    <option value="{{ $k }}" @selected($filter['status'] === $k)>{{ ucfirst($k) }}</option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <label for="role">Peran</label>
            <select id="role" name="role">
                <option value="">Semua peran</option>
                <option value="user" @selected($filter['role'] === 'user')>Warga</option>
                <option value="pengepul" @selected($filter['role'] === 'pengepul')>Pengepul</option>
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
            <a href="{{ route('admin.laporan') }}" class="btn btn-sm btn-secondary">Reset</a>
        </div>
    </form>

    <div class="filter-summary">
        Menampilkan <strong>{{ $setoran->total() }}</strong> baris
        @if ($setoran->total() > 0)
            &middot; halaman {{ $setoran->firstItem() }}&ndash;{{ $setoran->lastItem() }}
        @endif
        &middot; harga minyak Rp{{ number_format($hargaMinyak, 0, ',', '.') }}/kg
        &middot; endapan Rp{{ number_format($hargaEndapan, 0, ',', '.') }}/kg
    </div>

    <div class="panel-body panel-body-flush">
        @if ($setoran->isEmpty())
            <x-empty title="Tidak ada data yang cocok"
                     message="Coba longgarkan penyaring atau perluas rentang tanggal."
                     icon="search" />
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th><th>Warga</th><th>Pengepul</th>
                            <th class="num">Perkiraan</th>
                            <th class="num">Minyak</th><th class="num">Endapan</th>
                            <th class="num">Harga/kg</th><th class="num">Total</th>
                            <th>Status</th><th>Bukti</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($setoran as $s)
                            <tr>
                                <td>
                                    <span class="cell-title">{{ $s->tanggal_ajukan->format('d M Y') }}</span><br>
                                    <small class="cell-sub">#{{ $s->id }}</small>
                                </td>
                                <td>{{ $s->user?->name ?? '-' }}</td>
                                <td>{{ $s->pengepul?->name ?? '-' }}</td>
                                <td class="num"><x-perkiraan :setoran="$s" /></td>
                                <td class="num">
                                    {{ $s->minyak_kg !== null ? number_format($s->minyak_kg, 1, ',', '.') . ' kg' : '-' }}
                                </td>
                                <td class="num">
                                    {{ $s->endapan_kg !== null ? number_format($s->endapan_kg, 1, ',', '.') . ' kg' : '-' }}
                                </td>
                                <td class="num text-muted">
                                    @if ($s->harga_minyak_saat_itu)
                                        {{ number_format($s->harga_minyak_saat_itu, 0, ',', '.') }}
                                        @if ($s->harga_endapan_saat_itu)
                                            <br><small class="cell-sub">end {{ number_format($s->harga_endapan_saat_itu, 0, ',', '.') }}</small>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="num">
                                    @if ($s->total_saldo !== null)
                                        <strong>Rp{{ number_format($s->total_saldo, 0, ',', '.') }}</strong>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td><x-status-badge :status="$s->status" /></td>
                                <td>
                                    @if ($s->bukti_foto)
                                        <a href="{{ \Illuminate\Support\Facades\Storage::url($s->bukti_foto) }}"
                                           target="_blank" rel="noopener" title="Lihat bukti foto">
                                            <img src="{{ \Illuminate\Support\Facades\Storage::url($s->bukti_foto) }}"
                                                 alt="Bukti" class="shot" style="width:40px;height:40px;">
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if ($setoran->hasPages())
        {{ $setoran->links() }}
    @endif
</div>

@endsection
