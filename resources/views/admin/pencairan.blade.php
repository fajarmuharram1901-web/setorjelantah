@extends('layouts.dashboard', ['title' => 'Pencairan Saldo', 'subtitle' => 'Verifikasi permintaan pencairan dari warga'])

@section('content')

<div class="page-head">
    <div>
        <h1 class="page-title">Permintaan Pencairan</h1>
        <p class="page-sub">Setujui untuk memotong saldo warga, atau tolak dengan alasan yang jelas.</p>
    </div>
    <div class="panel-actions">
        <a href="{{ route('admin.laporan') }}" class="btn btn-secondary">
            <x-icon name="chart" :size="15" /> Laporan
        </a>
    </div>
</div>

<div class="stat-grid stat-grid-4">
    <x-stat-card label="Menunggu"
                 :value="$jumlahMenunggu"
                 icon="clock" accent="#DB9A26" tint="#FBF0DD" small
                 :meta="'Total Rp' . number_format($totalMenunggu, 0, ',', '.')" />
    <x-stat-card label="Sudah Disetujui"
                 :value="'Rp' . number_format($totalDisetujui, 0, ',', '.')"
                 icon="check" accent="#55703C" tint="#EAF0E2" small
                 meta="Total cair" />
    <x-stat-card label="Saldo Seluruh Warga"
                 :value="'Rp' . number_format($saldoBerjalan, 0, ',', '.')"
                 icon="wallet" accent="#BE7E1D" tint="#FBF0DD" small
                 meta="Kewajiban sistem" />
    <x-stat-card label="Tersedia Tanpa Tunggu"
                 :value="'Rp' . number_format(max(0, $saldoBerjalan - $totalMenunggu), 0, ',', '.')"
                 icon="scale" accent="#3E5E7E" tint="#E9F0F6" small
                 meta="Saldo dikurangi yang menunggu" />
</div>

<div class="panel">
    <div class="panel-head">
        <div class="tabs" style="margin:0;border:0;">
            <a href="{{ route('admin.pencairan') }}" class="tab{{ $status === null ? ' is-active' : '' }}">Semua</a>
            @foreach (['menunggu' => 'Menunggu', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak'] as $k => $v)
                <a href="{{ route('admin.pencairan', ['status' => $k]) }}"
                   class="tab{{ $status === $k ? ' is-active' : '' }}">{{ $v }}</a>
            @endforeach
        </div>
    </div>

    <div class="panel-body panel-body-flush">
        @if ($daftar->isEmpty())
            <x-empty title="Tidak ada permintaan"
                     message="Permintaan pencairan dari warga akan muncul di sini."
                     icon="cash-out" />
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Warga</th>
                            <th>Diminta</th>
                            <th>Metode</th>
                            <th>Tujuan</th>
                            <th class="num">Jumlah</th>
                            <th class="num">Saldo Warga</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($daftar as $p)
                            <tr>
                                <td class="cell-title">{{ $p->kode }}</td>
                                <td>
                                    <span class="cell-title">{{ $p->user->name }}</span><br>
                                    <small class="cell-sub">{{ $p->user->no_hp_formatted ?? '-' }}</small>
                                </td>
                                <td>{{ $p->created_at->format('d M Y H:i') }}</td>
                                <td>{{ $p->labelMetode() }}</td>
                                <td>
                                    {{ $p->tujuan }}<br>
                                    <small class="cell-sub">{{ $p->nama_penerima }}</small>
                                </td>
                                <td class="num"><strong>Rp{{ number_format($p->jumlah, 0, ',', '.') }}</strong></td>
                                <td class="num text-muted">Rp{{ number_format($p->user->saldo, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge badge-{{ $p->badgeStatus() }}">{{ ucfirst($p->status) }}</span>
                                    @if ($p->admin)
                                        <br><small class="cell-sub">oleh {{ $p->admin->name }}</small>
                                    @endif
                                    @if ($p->alasan_tolak)
                                        <br><small style="color:var(--rust);">{{ $p->alasan_tolak }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if ($p->status === 'menunggu')
                                        @php $cukup = (float) $p->user->saldo >= (float) $p->jumlah; @endphp
                                        <div class="table-actions">
                                            @if ($cukup)
                                                <form method="POST" action="{{ route('admin.pencairan.setujui', $p) }}"
                                                      style="max-width:none;margin:0;"
                                                      data-konfirmasi="Setujui pencairan Rp{{ number_format($p->jumlah, 0, ',', '.') }}?" data-judul="Setujui pencairan" data-ok="Ya, setujui">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm">Setujui</button>
                                                <x-form-validasi :form="'tolakPencairan'" />
    </form>
                                            @else
                                                <span class="badge badge-danger">Saldo kurang</span>
                                            @endif

                                            <button type="button" class="btn btn-sm btn-danger"
                                                    onclick="tolakPencairan({{ $p->id }})">Tolak</button>
                                        </div>

                                        <form data-validasi data-form="tolakPencairan" novalidate id="form-tolak-{{ $p->id }}" method="POST"
                                              action="{{ route('admin.pencairan.tolak', $p) }}"
                                              style="max-width:none;margin:8px 0 0;display:none;gap:6px;">
                                            @csrf
                                            <input type="text" name="alasan_tolak" placeholder="Alasan penolakan" required
                                                   style="padding:6px 9px;font-size:0.8rem;">
                                            <button type="submit" class="btn btn-sm btn-danger">Kirim</button>
                                        </form>
                                    @elseif ($p->diproses_pada)
                                        <small class="cell-sub">{{ $p->diproses_pada->format('d M Y H:i') }}</small>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if ($daftar->hasPages())
        {{ $daftar->links() }}
    @endif
</div>

@endsection

@push('scripts')
<script>
function tolakPencairan(id) {
    const form = document.getElementById('form-tolak-' + id);
    if (form) form.style.display = 'flex';
}
</script>
@endpush