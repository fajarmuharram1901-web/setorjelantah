@extends('layouts.dashboard', ['title' => 'Pencairan Saldo', 'subtitle' => 'Ubah saldo menjadi uang tunai atau transfer'])

@section('content')

<div class="page-head">
    <div>
        <h1 class="page-title">Pencairan Saldo</h1>
        <p class="page-sub">Permintaan Anda akan diverifikasi admin, lalu saldo dipotong otomatis.</p>
    </div>
    <div class="panel-actions">
        <a href="{{ route('user.saldo') }}" class="btn btn-secondary">
            <x-icon name="arrow-right" :size="15" /> Kembali ke Saldo
        </a>
    </div>
</div>

<div class="grid-2-panel">
    <div class="panel">
        <div class="panel-head">
            <h2>Form Permintaan</h2>
        </div>
        <div class="panel-body">
            <form data-validasi data-form="pencairan" novalidate method="POST" action="{{ route('user.pencairan.store') }}" style="max-width:100%;">
                @csrf

                <div style="padding:13px 15px;background:var(--gold-tint);border:1px solid #EDD9AC;border-radius:8px;margin-bottom:4px;">
                    <p class="stat-label" style="margin:0;">Saldo tersedia</p>
                    <p style="font-family:var(--font-display);font-size:1.5rem;font-weight:600;color:var(--gold-deep);margin:2px 0 0;">
                        Rp{{ number_format($saldoTersedia, 0, ',', '.') }}
                    </p>
                    <p class="form-hint" style="margin:2px 0 0;">Minimum pencairan Rp{{ number_format($minPencairan, 0, ',', '.') }}.</p>
                </div>

                <div>
                    <label for="jumlah">Jumlah Pencairan (Rp)</label>
                    <input id="jumlah" type="number" step="1" min="{{ (int) $minPencairan }}"
                           max="{{ (int) floor($saldoTersedia) }}"
                           name="jumlah" value="{{ old('jumlah') }}" required>
                    <p class="form-hint">
                        Boleh diisi bebas dalam rupiah. Minimal Rp{{ number_format($minPencairan, 0, ',', '.') }},
                        maksimal Rp{{ number_format($saldoTersedia, 0, ',', '.') }}.
                    </p>
                </div>

                <div>
                    <label for="metode">Metode Pencairan</label>
                    <select id="metode" name="metode" required data-dropdown>
                        <option value="ewallet" @selected(old('metode') === 'ewallet')>E-Wallet (GoPay, OVO, DANA, ShopeePay)</option>
                        <option value="transfer" @selected(old('metode') === 'transfer')>Transfer Bank</option>
                        <option value="tunai" @selected(old('metode') === 'tunai')>Tunai / Ambil di Kios</option>
                    </select>
                </div>

                <div>
                    <label for="tujuan">Nomor Tujuan</label>
                    <input id="tujuan" type="text" name="tujuan" value="{{ old('tujuan') }}"
                           placeholder="Nomor HP atau rekening" required>
                    <p class="form-hint" id="petunjuk-tujuan">Nomor HP e-wallet penerima, contoh: 08123456789.</p>
                </div>

                <div>
                    <label for="nama_penerima">Nama Penerima</label>
                    <input id="nama_penerima" type="text" name="nama_penerima"
                           value="{{ old('nama_penerima', $user->name) }}" required>
                </div>

                <div>
                    <button type="submit" class="btn">Kirim Permintaan</button>
                </div>
            <x-form-validasi :form="'pencairan'" :pengganti="['maksSaldo' => (int) floor($saldoTersedia)]" />
    </form>
        </div>
    </div>

    <div>
        <div class="panel">
            <div class="panel-head">
                <h3>Riwayat Permintaan Terakhir</h3>
            </div>
            <div class="panel-body panel-body-flush">
                @if ($riwayat->isEmpty())
                    <x-empty title="Belum ada permintaan" message="Permintaan yang Anda kirim akan muncul di sini." icon="cash-out" />
                @else
                    <ul class="timeline" style="padding:0 18px;">
                        @foreach ($riwayat as $p)
                            <li>
                                <span class="timeline-dot @if ($p->status === 'disetujui') is-success @elseif ($p->status === 'ditolak') is-danger @endif">
                                    <x-icon :name="$p->status === 'disetujui' ? 'check' : ($p->status === 'ditolak' ? 'x' : 'clock')" :size="14" />
                                </span>
                                <div class="timeline-body">
                                    <p>
                                        <strong>Rp{{ number_format($p->jumlah, 0, ',', '.') }}</strong>
                                        &middot; {{ $p->labelMetode() }}
                                    </p>
                                    <small>
                                        {{ $p->kode }} &middot; {{ $p->created_at->format('d M Y H:i') }}
                                        &middot; <span class="badge badge-{{ $p->badgeStatus() }}">{{ ucfirst($p->status) }}</span>
                                    </small>
                                    @if ($p->alasan_tolak)
                                        <small style="display:block;color:var(--rust);">{{ $p->alasan_tolak }}</small>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <div class="alert alert-warning">
            Pencairan diproses setelah disetujui admin. Saldo dipotong saat disetujui, bukan saat dikirim.
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    const metode = document.getElementById('metode');
    const petunjuk = document.getElementById('petunjuk-tujuan');
    if (!metode || !petunjuk) return;

    const contoh = {
        ewallet: 'Nomor HP e-wallet penerima, contoh: 08123456789.',
        transfer: 'Nomor rekening penerima, contoh: 1234567890 a.n. Nama Penerima.',
        tunai: 'Tulis Kios terdekat, pencairan diambil langsung di kios.'
    };

    metode.addEventListener('change', function () {
        petunjuk.textContent = contoh[metode.value] || '';
    });
})();
</script>
@endpush