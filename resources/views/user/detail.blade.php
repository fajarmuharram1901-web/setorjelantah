@use('Illuminate\Support\Facades\Storage')

@extends('layouts.dashboard', ['title' => 'Detail Setoran', 'subtitle' => 'Rincian berat minyak dan endapan #' . $setoran->id])

@section('content')

<div class="page-head">
    <div>
        <h1 class="page-title">Detail Setoran #{{ $setoran->id }}</h1>
        <p class="page-sub">Diajukan {{ $setoran->tanggal_ajukan->format('d F Y \p\u\k\u\l H:i') }}</p>
    </div>
    <div class="panel-actions">
        <a href="{{ route('user.riwayat') }}" class="btn btn-secondary">
            <x-icon name="arrow-right" :size="15" /> Kembali ke Riwayat
        </a>
    </div>
</div>

<div class="grid-2-panel">
    <div>
        <div class="panel">
            <div class="panel-head">
                <h2>Rincian Setoran</h2>
                <x-status-badge :status="$setoran->status" />
            </div>
            <div class="panel-body">
                <dl class="kv">
                    <div><dt>Perkiraan minyak warga</dt><dd>{{ number_format($setoran->perkiraanMinyakKg(), 2, ',', '.') }} kg</dd></div>
                    <div><dt>Perkiraan endapan warga</dt><dd>{{ number_format($setoran->perkiraanEndapanKg(), 2, ',', '.') }} kg</dd></div>
                    <div>
                        <dt>Total perkiraan warga</dt>
                        <dd><strong>{{ number_format($setoran->perkiraanTotalKg(), 2, ',', '.') }} kg</strong></dd>
                    </div>

                    @if ($setoran->sudahSelesai())
                        <div><dt>Minyak ditimbang</dt><dd>{{ number_format($setoran->minyak_kg, 2, ',', '.') }} kg</dd></div>
                        <div><dt>Endapan ditimbang</dt><dd>{{ number_format($setoran->endapan_kg, 2, ',', '.') }} kg</dd></div>
                        <div>
                            <dt>Total berat diterima</dt>
                            <dd><strong>{{ number_format($setoran->totalTerimaKg(), 2, ',', '.') }} kg</strong></dd>
                        </div>
                        @if ($setoran->selisihKg() !== null)
                            <div>
                                <dt>Selisih dari perkiraan</dt>
                                <dd>
                                    {{ $setoran->selisihKg() > 0 ? '+' : '' }}{{ number_format($setoran->selisihKg(), 2, ',', '.') }} kg
                                </dd>
                            </div>
                        @endif
                        <div><dt>Harga minyak saat itu</dt><dd>Rp{{ number_format($setoran->harga_minyak_saat_itu, 0, ',', '.') }} / kg</dd></div>
                        <div><dt>Harga endapan saat itu</dt><dd>Rp{{ number_format($setoran->harga_endapan_saat_itu, 0, ',', '.') }} / kg</dd></div>
                        <div>
                            <dt>Nilai minyak</dt>
                            <dd>Rp{{ number_format((float) $setoran->minyak_kg * (float) $setoran->harga_minyak_saat_itu, 0, ',', '.') }}</dd>
                        </div>
                        <div>
                            <dt>Nilai endapan</dt>
                            <dd>Rp{{ number_format((float) $setoran->endapan_kg * (float) $setoran->harga_endapan_saat_itu, 0, ',', '.') }}</dd>
                        </div>
                        <div>
                            <dt>Total masuk saldo</dt>
                            <dd style="color:var(--olive);"><strong>Rp{{ number_format($setoran->total_saldo, 0, ',', '.') }}</strong></dd>
                        </div>
                    @endif

                    <div><dt>Pengepul</dt><dd>{{ $setoran->pengepul?->name ?? 'Belum ditugaskan' }}</dd></div>
                    <div><dt>Ditanggalkan</dt><dd>{{ $setoran->tanggal_selesai?->format('d F Y H:i') ?? '-' }}</dd></div>
                </dl>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <h3>Alamat &amp; Catatan</h3>
            </div>
            <div class="panel-body">
                <dl class="kv">
                    <div><dt>Alamat penjemputan</dt><dd style="max-width:60%;">{{ $setoran->alamat_jemput }}</dd></div>
                    <div><dt>Catatan warga</dt><dd style="max-width:60%;">{{ $setoran->catatan ?? '-' }}</dd></div>
                    @if ($setoran->catatan_pengepul)
                        <div><dt>Catatan pengepul</dt><dd style="max-width:60%;">{{ $setoran->catatan_pengepul }}</dd></div>
                    @endif
                </dl>

                @if ($setoran->bukti_foto)
                    <div style="margin-top:16px;">
                        <p class="stat-label">Bukti foto di lokasi</p>
                        <a href="{{ Storage::url($setoran->bukti_foto) }}" target="_blank" rel="noopener">
                            <img src="{{ Storage::url($setoran->bukti_foto) }}" alt="Bukti foto minyak" class="shot" style="width:120px;height:120px;">
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <h3>Status Saat Ini</h3>
        </div>
        <div class="panel-body">
            <ul class="timeline">
                <li>
                    <span class="timeline-dot is-success"><x-icon name="check" :size="14" /></span>
                    <div class="timeline-body">
                        <p>Pengajuan dikirim</p>
                        <small>{{ $setoran->tanggal_ajukan->format('d F Y H:i') }}</small>
                    </div>
                </li>
                <li>
                    <span class="timeline-dot {{ $setoran->status !== 'menunggu' ? 'is-info' : '' }}">
                        <x-icon name="truck" :size="14" />
                    </span>
                    <div class="timeline-body">
                        <p>Diambil pengepul</p>
                        <small>{{ $setoran->pengepul ? $setoran->pengepul->name : 'Menunggu pengepul mengambil tugas' }}</small>
                    </div>
                </li>
                <li>
                    <span class="timeline-dot {{ $setoran->sudahSelesai() ? 'is-success' : '' }}">
                        <x-icon name="scale" :size="14" />
                    </span>
                    <div class="timeline-body">
                        <p>Minyak &amp; endapan ditimbang</p>
                        <small>{{ $setoran->sudahSelesai() ? number_format($setoran->minyak_kg, 1, ',', '.') . ' kg minyak, ' . number_format($setoran->endapan_kg, 1, ',', '.') . ' kg endapan' : 'Belum ditimbang' }}</small>
                    </div>
                </li>
                <li>
                    <span class="timeline-dot {{ $setoran->sudahSelesai() ? 'is-success' : '' }}">
                        <x-icon name="wallet" :size="14" />
                    </span>
                    <div class="timeline-body">
                        <p>Saldo bertambah</p>
                        <small>{{ $setoran->tanggal_selesai?->format('d F Y H:i') ?? 'Belum selesai' }}</small>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>

@endsection
