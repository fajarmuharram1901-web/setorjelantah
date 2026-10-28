{{-- Bagian dashboard admin yang di-refresh otomatis lewat polling AJAX. --}}

<div class="stat-grid stat-grid-4">
    <x-stat-card label="Total Minyak"
                 :value="number_format($totalMinyak, 1, ',', '.') . ' kg'"
                 icon="droplet" accent="#DB9A26" tint="#FBF0DD"
                 :trend="$ringkasan['tren_kg']"
                 trend-label="14 hari"
                 :meta="number_format($totalKg, 1, ',', '.') . ' kg termasuk endapan'" />
    <x-stat-card label="Nilai Disalurkan"
                 :value="'Rp' . number_format($totalDisalurkan, 0, ',', '.')"
                 icon="wallet" accent="#55703C" tint="#EAF0E2"
                 :trend="$ringkasan['tren_nilai']"
                 trend-label="14 hari"
                 :meta="'Saldo warga Rp' . number_format($saldoSeluruhWarga, 0, ',', '.')" />
    <x-stat-card label="Pengguna Terdaftar"
                 :value="$totalUser"
                 icon="users" accent="#3E5E7E" tint="#E9F0F6"
                 :meta="$totalPengepul . ' pengepul aktif'" />
    <x-stat-card label="Harga Minyak / kg"
                 :value="'Rp' . number_format($hargaMinyak, 0, ',', '.')"
                 icon="tag" accent="#BE7E1D" tint="#FBF0DD"
                 :meta="'Endapan Rp' . number_format($hargaEndapan, 0, ',', '.') . ' / kg'" />
</div>

<div class="stat-grid stat-grid-4">
    <x-stat-card label="Menunggu Diambil"
                 :value="$menunggu"
                 icon="inbox" accent="#DB9A26" tint="#FBF0DD" small
                 meta="Perlu pengepul" />
    <x-stat-card label="Sedang Diproses"
                 :value="$diproses"
                 icon="truck" accent="#3E5E7E" tint="#E9F0F6" small
                 meta="Dikerjakan pengepul" />
    <x-stat-card label="Permintaan Pencairan"
                 :value="$jumlahPencairan"
                 icon="cash-out" accent="#A63D28" tint="#FBEAE6" small
                 :meta="'Total Rp' . number_format($nilaiPencairan, 0, ',', '.')" />
    <x-stat-card label="Mutasi Tercatat"
                 :value="$jumlahTransaksi"
                 icon="history" accent="#8C5A12" tint="#FBF0DD" small
                 meta="Baris buku besar" />
</div>

<div class="grid-2-panel">
    <div class="panel">
        <div class="panel-head">
            <div>
                <h2>Nilai 14 Hari Terakhir</h2>
                <p class="panel-sub">Setoran yang selesai per hari</p>
            </div>
            <div class="panel-actions">
                <a href="{{ route('admin.laporan') }}" class="btn btn-sm btn-secondary">Buka Laporan</a>
            </div>
        </div>
        <div class="panel-body">
            <x-bar-chart :data="$deret" kunci="nilai" format="rupiah" />
        </div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <div>
                <h2>Komposisi Berat</h2>
                <p class="panel-sub">Minyak dan endapan yang sudah diterima</p>
            </div>
        </div>
        <div class="panel-body">
            @php
                $total = $totalMinyak + $totalEndapan;
                $pMinyak = $total > 0 ? ($totalMinyak / $total) * 100 : 0;
                $pEndapan = $total > 0 ? ($totalEndapan / $total) * 100 : 0;
            @endphp

            @if ($total > 0)
                <div class="quality-bar" style="height:14px;">
                    <span style="width: {{ $pMinyak }}%; background:#DB9A26;" title="Minyak"></span>
                    <span style="width: {{ $pEndapan }}%; background:#5A3310;" title="Endapan"></span>
                </div>

                <div style="display:flex;gap:16px;margin-top:12px;flex-wrap:wrap;font-size:0.84rem;">
                    <span style="display:inline-flex;align-items:center;gap:6px;">
                        <span class="legend-swatch" style="background:#DB9A26;"></span>
                        Minyak <strong>{{ number_format($totalMinyak, 1, ',', '.') }} kg</strong>
                    </span>
                    <span style="display:inline-flex;align-items:center;gap:6px;">
                        <span class="legend-swatch" style="background:#5A3310;"></span>
                        Endapan <strong>{{ number_format($totalEndapan, 1, ',', '.') }} kg</strong>
                    </span>
                </div>
            @else
                <p class="text-muted" style="margin:0;">Belum ada minyak yang selesai disetor.</p>
            @endif
        </div>
    </div>
</div>

<div class="grid-2-panel-wide">
    <div class="panel">
        <div class="panel-head">
            <div>
                <h2>Status Pengajuan</h2>
                <p class="panel-sub">Sebaran seluruh setoran</p>
            </div>
        </div>
        <div class="panel-body">
            <x-donut :data="$donut" kunci="jumlah" total-label="Setoran" />
        </div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <div>
                <h2>Warga Kontributor Terbanyak</h2>
                <p class="panel-sub">Berdasarkan nilai minyak yang disetor</p>
            </div>
        </div>
        <div class="panel-body panel-body-flush">
            @if ($topWarga->isEmpty())
                <x-empty title="Belum ada data" message="Belum ada setoran selesai." icon="users" />
            @else
                <div class="table-wrap">
                    <table class="compact">
                        <thead>
                            <tr><th>#</th><th>Warga</th><th class="num">Setoran</th><th class="num">Berat</th><th class="num">Nilai</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($topWarga as $i => $w)
                                <tr>
                                    <td class="text-muted">{{ $i + 1 }}</td>
                                    <td class="cell-title">{{ $w->nama }}</td>
                                    <td class="num">{{ $w->jumlah }}</td>
                                    <td class="num">{{ number_format((float) $w->kg, 1, ',', '.') }} kg</td>
                                    <td class="num"><strong>Rp{{ number_format((float) $w->nilai, 0, ',', '.') }}</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="grid-2-panel-wide">
    <div class="panel">
        <div class="panel-head">
            <div>
                <h2>Pengepul Teraktif</h2>
                <p class="panel-sub">Berdasarkan volume yang sudah dikonfirmasi</p>
            </div>
        </div>
        <div class="panel-body panel-body-flush">
            @if ($topPengepul->isEmpty())
                <x-empty title="Belum ada data" message="Belum ada pengepul yang menyelesaikan tugas." icon="truck" />
            @else
                <div class="table-wrap">
                    <table class="compact">
                        <thead>
                            <tr><th>#</th><th>Pengepul</th><th class="num">Tugas</th><th class="num">Berat</th><th class="num">Nilai</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($topPengepul as $i => $p)
                                <tr>
                                    <td class="text-muted">{{ $i + 1 }}</td>
                                    <td class="cell-title">{{ $p->nama }}</td>
                                    <td class="num">{{ $p->jumlah }}</td>
                                    <td class="num">{{ number_format((float) $p->kg, 1, ',', '.') }} kg</td>
                                    <td class="num"><strong>Rp{{ number_format((float) $p->nilai, 0, ',', '.') }}</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <div>
                <h2>Setoran Terbaru</h2>
                <p class="panel-sub">Delapan pengajuan terakhir</p>
            </div>
        </div>
        <div class="panel-body panel-body-flush">
            @if ($terbaru->isEmpty())
                <x-empty title="Belum ada setoran" message="Pengajuan warga akan muncul di sini." icon="droplet" />
            @else
                <div class="table-wrap">
                    <table class="compact">
                        <thead>
                            <tr><th>Tanggal</th><th>Warga</th><th class="num">Minyak</th><th class="num">Endapan</th><th class="num">Total</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($terbaru as $s)
                                <tr>
                                    <td>{{ $s->tanggal_ajukan->format('d/m/Y') }}</td>
                                    <td class="cell-title">{{ $s->user?->name ?? '-' }}</td>
                                    <td class="num">{{ $s->minyak_kg !== null ? number_format($s->minyak_kg, 1, ',', '.') : number_format($s->perkiraanMinyakKg(), 1, ',', '.') }}</td>
                                    <td class="num">{{ $s->endapan_kg !== null ? number_format($s->endapan_kg, 1, ',', '.') : '-' }}</td>
                                    <td class="num">
                                        @if ($s->total_saldo !== null)
                                            <strong>Rp{{ number_format($s->total_saldo, 0, ',', '.') }}</strong>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td><x-status-badge :status="$s->status" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
