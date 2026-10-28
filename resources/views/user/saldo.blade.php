@extends('layouts.dashboard', ['title' => 'Saldo & Transaksi', 'subtitle' => 'Buku besar saldo dan riwayat pencairan Anda'])

@section('content')

<div class="page-head">
    <div>
        <h1 class="page-title">Saldo &amp; Transaksi</h1>
        <p class="page-sub">Setiap perubahan saldo tercatat di sini, jadi bisa ditelusuri kembali.</p>
    </div>
    <div class="panel-actions">
        <a href="{{ route('user.pencairan.create') }}" class="btn">
            <x-icon name="cash-out" :size="15" /> Pencairkan Saldo
        </a>
    </div>
</div>

<div class="grid-2-panel">
    <div>
        <div class="balance-card">
            <p class="label">Saldo Saat Ini</p>
            <p class="amount">Rp{{ number_format($user->saldo, 0, ',', '.') }}</p>
            <div class="foot">
                <div>
                    <span>Bisa dicairkan</span>
                    <strong>Rp{{ number_format($saldoTersedia, 0, ',', '.') }}</strong>
                </div>
                <div>
                    <span>Sedang dipesan</span>
                    <strong>Rp{{ number_format($user->saldo - $saldoTersedia, 0, ',', '.') }}</strong>
                </div>
                <div>
                    <span>Harga minyak / kg</span>
                    <strong>Rp{{ number_format($hargaMinyak, 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>

        <div class="stat-grid stat-grid-4" style="margin-top:20px;">
            <x-stat-card label="Total Masuk"
                         :value="'Rp' . number_format($totalMasuk, 0, ',', '.')"
                         icon="trend-up" accent="#55703C" tint="#EAF0E2"
                         meta="Dari setoran" small />
            <x-stat-card label="Total Keluar"
                         :value="'Rp' . number_format($totalKeluar, 0, ',', '.')"
                         icon="trend-down" accent="#A63D28" tint="#FBEAE6"
                         meta="Dari pencairan" small />
            <x-stat-card label="Permintaan Menunggu"
                         :value="$pencairans->where('status', 'menunggu')->count()"
                         icon="clock" accent="#DB9A26" tint="#FBF0DD"
                         meta="Menunggu admin" small />
        </div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <div>
                <h2>Penerimaan 10 Hari Terakhir</h2>
                <p class="panel-sub">Nilai setoran selesai per hari</p>
            </div>
        </div>
        <div class="panel-body">
            <x-bar-chart :data="$deret" kunci="nilai" format="rupiah" />
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <h2>Riwayat Transaksi Saldo</h2>
    </div>
    <div class="panel-body panel-body-flush">
        @if ($transaksi->isEmpty())
            <x-empty title="Belum ada transaksi"
                     message="Transaksi muncul otomatis setelah pengepul mengonfirmasi setoran Anda."
                     icon="wallet" />
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jenis</th>
                            <th>Keterangan</th>
                            <th class="num">Saldo Sebelumnya</th>
                            <th class="num">Perubahan</th>
                            <th class="num">Saldo Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transaksi as $t)
                            <tr>
                                <td>
                                    <span class="cell-title">{{ $t->created_at->format('d M Y') }}</span><br>
                                    <small class="cell-sub">{{ $t->created_at->format('H:i') }}</small>
                                </td>
                                <td>{{ $t->labelTipe() }}</td>
                                <td style="max-width:320px;">{{ $t->keterangan ?? '-' }}</td>
                                <td class="num text-muted">Rp{{ number_format($t->saldo_sebelum, 0, ',', '.') }}</td>
                                <td class="num">
                                    <strong style="color: {{ (float) $t->jumlah >= 0 ? 'var(--olive)' : 'var(--rust)' }};">
                                        {{ (float) $t->jumlah >= 0 ? '+' : '-' }}Rp{{ number_format(abs((float) $t->jumlah), 0, ',', '.') }}
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

<div class="panel">
    <div class="panel-head">
        <h2>Riwayat Pencairan</h2>
    </div>
    <div class="panel-body panel-body-flush">
        @if ($pencairans->isEmpty())
            <x-empty title="Belum pernah pencairan"
                     message="Anda bisa mencairkan saldo minimal Rp{{ number_format($minPencairan, 0, ',', '.') }}."
                     icon="cash-out"
                     action-label="Ajukan Pencairan"
                     :action-route="route('user.pencairan.create')" />
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Diminta</th>
                            <th>Metode</th>
                            <th>Tujuan</th>
                            <th class="num">Jumlah</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pencairans as $p)
                            <tr>
                                <td class="cell-title">{{ $p->kode }}</td>
                                <td>{{ $p->created_at->format('d M Y H:i') }}</td>
                                <td>{{ $p->labelMetode() }}</td>
                                <td>
                                    {{ $p->tujuan }}<br>
                                    <small class="cell-sub">{{ $p->nama_penerima }}</small>
                                </td>
                                <td class="num"><strong>Rp{{ number_format($p->jumlah, 0, ',', '.') }}</strong></td>
                                <td>
                                    <span class="badge badge-{{ $p->badgeStatus() }}">{{ ucfirst($p->status) }}</span>
                                    @if ($p->alasan_tolak)
                                        <br><small class="cell-sub">{{ $p->alasan_tolak }}</small>
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if ($p->status === 'menunggu')
                                        <form method="POST" action="{{ route('user.pencairan.batal', $p) }}"
                                              data-konfirmasi="Batalkan permintaan pencairan ini?" data-judul="Batalkan pencairan" data-ok="Ya, batalkan"
                                              style="max-width:none;margin:0;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-secondary">Batalkan</button>
                                        </form>
                                    @elseif ($p->diproses_pada)
                                        <small class="cell-sub">{{ $p->diproses_pada->format('d M Y') }}</small>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if ($pencairans->hasPages())
        {{ $pencairans->links() }}
    @endif
</div>

@endsection