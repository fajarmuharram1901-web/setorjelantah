{{-- Bagian dashboard warga yang di-refresh otomatis lewat polling AJAX. --}}

<div class="grid-2-panel">
    <div>
        <div class="balance-card">
            <p class="label">Saldo Anda</p>
            <p class="amount">Rp{{ number_format($user->saldo, 0, ',', '.') }}</p>
            <div class="foot">
                <div>
                    <span>Sudah dicairkan / dipesan</span>
                    <strong>Rp{{ number_format($user->saldo - $saldoTersedia, 0, ',', '.') }}</strong>
                </div>
                <div>
                    <span>Bisa dicairkan</span>
                    <strong>Rp{{ number_format($saldoTersedia, 0, ',', '.') }}</strong>
                </div>
                <div>
                    <span>Harga minyak / kg</span>
                    <strong>Rp{{ number_format($hargaMinyak, 0, ',', '.') }}</strong>
                </div>
            </div>
            <div style="margin-top:16px;display:flex;gap:8px;flex-wrap:wrap;position:relative;z-index:2;">
                @if ($bisaPencair)
                    <a href="{{ route('user.pencairan.create') }}" class="btn" style="background:#FFF8EC;color:#6B4310;">
                        <x-icon name="cash-out" :size="15" /> Pencairkan Saldo
                    </a>
                @else
                    <span class="btn" style="background:rgba(255,248,236,.25);color:#FFF8EC;cursor:default;">
                        Minimal Rp{{ number_format(\App\Models\User::MIN_PENCAIRAN, 0, ',', '.') }} untuk pencairan
                    </span>
                @endif
                <a href="{{ route('user.ajukan') }}" class="btn btn-secondary" style="background:transparent;border-color:rgba(255,248,236,.5);color:#FFF8EC;">
                    <x-icon name="plus-circle" :size="15" /> Ajukan Setor
                </a>
            </div>
        </div>

        @if ($menungguPencairan > 0)
            <div class="alert alert-warning" style="margin-top:16px;">
                Ada {{ $menungguPencairan }} permintaan pencairan menunggu persetujuan admin.
                <a href="{{ route('user.saldo') }}">Lihat statusnya</a>.
            </div>
        @endif
    </div>

    <div class="stat-grid stat-grid-4" style="margin-bottom:0;">
        <x-stat-card label="Minyak Disetor"
                     :value="number_format($totalMinyak, 2, ',', '.') . ' kg'"
                     icon="droplet" accent="#BE7E1D" tint="#FBF0DD"
                     :meta="$jumlahSelesai . ' setoran selesai'" />
        <x-stat-card label="Endapan"
                     :value="number_format($totalEndapan, 2, ',', '.') . ' kg'"
                     icon="scale" accent="#A96A16" tint="#FBF0DD"
                     meta="Dihitung terpisah" />
        <x-stat-card label="Nilai Ditokokkan"
                     :value="'Rp' . number_format($totalNilai, 0, ',', '.')"
                     icon="wallet" accent="#55703C" tint="#EAF0E2"
                     meta="Minyak + endapan" />
        <x-stat-card label="Menunggu Pengepul"
                     :value="$menunggu"
                     icon="clock" accent="#DB9A26" tint="#FBF0DD"
                     :meta="$diproses > 0 ? $diproses . ' sedang diproses' : 'Menunggu diambil'" />
    </div>
</div>

<div class="grid-2-panel" style="margin-top:20px;">
    <div class="panel">
        <div class="panel-head">
            <div>
                <h2>Penerimaan 10 Hari Terakhir</h2>
                <p class="panel-sub">Nilai setoran selesai per hari</p>
            </div>
            <div class="panel-actions">
                <a href="{{ route('user.riwayat') }}" class="btn btn-sm btn-secondary">Riwayat lengkap</a>
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
                        Minyak <strong>{{ number_format($totalMinyak, 2, ',', '.') }} kg</strong>
                    </span>
                    <span style="display:inline-flex;align-items:center;gap:6px;">
                        <span class="legend-swatch" style="background:#5A3310;"></span>
                        Endapan <strong>{{ number_format($totalEndapan, 2, ',', '.') }} kg</strong>
                    </span>
                </div>

                <dl class="kv" style="margin-top:14px;">
                    <div><dt>Harga minyak</dt><dd>Rp{{ number_format($hargaMinyak, 0, ',', '.') }} / kg</dd></div>
                    <div><dt>Harga endapan</dt><dd>Rp{{ number_format($hargaEndapan, 0, ',', '.') }} / kg</dd></div>
                </dl>
            @else
                <p class="text-muted" style="margin:0;">Belum ada minyak yang selesai disetor.</p>
            @endif
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <h2>Setoran Terbaru</h2>
        <a href="{{ route('user.riwayat') }}" class="btn btn-sm btn-secondary">Lihat semua</a>
    </div>
    <div class="panel-body panel-body-flush">
        @if ($setoranTerbaru->isEmpty())
            <x-empty title="Belum ada setoran"
                     message="Ajukan setoran pertama Anda dan saldo akan bertambah otomatis setelah pengepul mengonfirmasi."
                     icon="droplet"
                     action-label="Ajukan Setor"
                     :action-route="route('user.ajukan')" />
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th class="num">Minyak</th>
                            <th class="num">Endapan</th>
                            <th class="num">Total Berat</th>
                            <th class="num">Diterima</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($setoranTerbaru as $s)
                            <tr>
                                <td>
                                    <span class="cell-title">{{ $s->tanggal_ajukan->format('d M Y') }}</span><br>
                                    <small class="cell-sub">#{{ $s->id }} &middot; {{ $s->pengepul?->name ?? 'Belum ada pengepul' }}</small>
                                </td>
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
