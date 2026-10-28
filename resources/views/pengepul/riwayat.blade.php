@extends('layouts.dashboard', ['title' => 'Riwayat Tugas', 'subtitle' => 'Semua setoran yang pernah Anda tangani'])

@section('content')

<div class="page-head">
    <div>
        <h1 class="page-title">Riwayat Tugas</h1>
        <p class="page-sub">Termasuk berat minyak, endapan, dan nilai yang sudah dikonfirmasi.</p>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <div class="tabs" style="margin:0;border:0;">
            <a href="{{ route('pengepul.riwayat') }}"
               class="tab{{ $status === null ? ' is-active' : '' }}">Semua</a>
            @foreach (['diproses' => 'Diproses', 'selesai' => 'Selesai', 'dibatalkan' => 'Dibatalkan'] as $k => $v)
                <a href="{{ route('pengepul.riwayat', ['status' => $k]) }}"
                   class="tab{{ $status === $k ? ' is-active' : '' }}">{{ $v }}</a>
            @endforeach
        </div>
    </div>

    <div class="panel-body panel-body-flush">
        @if ($tugas->isEmpty())
            <x-empty title="Belum ada tugas"
                     message="Tugas yang Anda ambil akan muncul di sini."
                     icon="truck"
                     action-label="Buka Dashboard"
                     :action-route="route('pengepul.dashboard')" />
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Warga</th>
                            <th class="num">Perkiraan</th>
                            <th class="num">Minyak</th>
                            <th class="num">Endapan</th>
                            <th class="num">Nilai</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tugas as $s)
                            <tr>
                                <td>
                                    <span class="cell-title">{{ $s->tanggal_ajukan->format('d M Y') }}</span><br>
                                    <small class="cell-sub">#{{ $s->id }}</small>
                                </td>
                                <td>{{ $s->user->name }}</td>
                                <td class="num"><x-perkiraan :setoran="$s" /></td>
                                <td class="num">
                                    {{ $s->minyak_kg !== null ? number_format($s->minyak_kg, 1, ',', '.') . ' kg' : '-' }}
                                </td>
                                <td class="num">
                                    {{ $s->endapan_kg !== null ? number_format($s->endapan_kg, 1, ',', '.') . ' kg' : '-' }}
                                </td>
                                <td class="num">
                                    @if ($s->total_saldo !== null)
                                        <strong>Rp{{ number_format($s->total_saldo, 0, ',', '.') }}</strong>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td><x-status-badge :status="$s->status" /></td>
                                <td class="text-right">
                                    @if ($s->bukti_foto)
                                        <a href="{{ \Illuminate\Support\Facades\Storage::url($s->bukti_foto) }}"
                                           target="_blank" rel="noopener" class="btn btn-sm btn-secondary">Foto</a>
                                    @endif
                                    @if ($s->status === 'diproses')
                                        <a href="{{ route('pengepul.verifikasi', $s) }}" class="btn btn-sm">Konfirmasi</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if ($tugas->hasPages())
        {{ $tugas->links() }}
    @endif
</div>

@endsection
