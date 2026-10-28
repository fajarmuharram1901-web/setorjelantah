@extends('layouts.dashboard', ['title' => 'Konfirmasi Setoran', 'subtitle' => 'Timbang minyak dan endapan di lokasi'])

@section('content')

<div class="page-head">
    <div>
        <h1 class="page-title">Konfirmasi Setoran</h1>
        @if ($setoran)
            <p class="page-sub">Pengajuan #{{ $setoran->id }} dari {{ $setoran->user->name }}</p>
        @endif
    </div>
    <div class="panel-actions">
        <a href="{{ route('pengepul.dashboard') }}" class="btn btn-secondary">
            <x-icon name="arrow-right" :size="15" /> Kembali ke Dashboard
        </a>
    </div>
</div>

@if (! $setoran)
    <div class="panel">
        <x-empty title="Tugas tidak ditemukan"
                 message="Setoran ini sudah selesai, dibatalkan, atau bukan tugas Anda."
                 icon="alert"
                 action-label="Kembali ke Dashboard"
                 :action-route="route('pengepul.dashboard')" />
    </div>
@else
    <div class="grid-2-panel">
        <div class="panel">
            <div class="panel-head">
                <h2>Data di Lokasi</h2>
            </div>
            <div class="panel-body">
                <dl class="kv">
                    <div><dt>Warga</dt><dd>{{ $setoran->user->name }}</dd></div>
                    <div><dt>No. HP</dt><dd>{{ $setoran->user->no_hp_formatted ?? '-' }}</dd></div>
                    <div><dt>Alamat</dt><dd style="max-width:62%;">{{ $setoran->alamat_jemput }}</dd></div>
                    <div><dt>Perkiraan minyak</dt><dd>{{ number_format($setoran->perkiraanMinyakKg(), 2, ',', '.') }} kg</dd></div>
                    <div><dt>Perkiraan endapan</dt><dd>{{ number_format($setoran->perkiraanEndapanKg(), 2, ',', '.') }} kg</dd></div>
                    <div><dt>Total perkiraan warga</dt><dd><strong>{{ number_format($setoran->perkiraanTotalKg(), 2, ',', '.') }} kg</strong></dd></div>
                    @if ($setoran->catatan)
                        <div><dt>Catatan warga</dt><dd style="max-width:62%;">{{ $setoran->catatan }}</dd></div>
                    @endif
                    <div><dt>Harga minyak saat ini</dt><dd>Rp{{ number_format($hargaMinyak, 0, ',', '.') }} / kg</dd></div>
                    <div><dt>Harga endapan saat ini</dt><dd>Rp{{ number_format($hargaEndapan, 0, ',', '.') }} / kg</dd></div>
                </dl>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <div>
                    <h2>Hasil Timbangan</h2>
                    <p class="panel-sub">Masukkan minyak dan endapan secara terpisah.</p>
                </div>
            </div>
            <div class="panel-body">
                <form data-validasi data-form="verifikasiSetoran" novalidate method="POST" action="{{ route('pengepul.verifikasi.store', $setoran) }}"
                      enctype="multipart/form-data" style="max-width:100%;">
                    @csrf

                    <div>
                        <label for="minyak_kg">Minyak Jelantah (kg) *</label>
                        <input id="minyak_kg" type="number" step="0.1" min="0" max="{{ $maksKg }}"
                               name="minyak_kg" value="{{ old('minyak_kg') }}" required>
                        <p class="form-hint">Isi 0 jika tidak ada minyak. Maksimal {{ number_format($maksKg, 0, ',', '.') }} kg.</p>
                    </div>

                    <div>
                        <label for="endapan_kg">Endapan (kg) *</label>
                        <input id="endapan_kg" type="number" step="0.1" min="0" max="{{ $maksKg }}"
                               name="endapan_kg" value="{{ old('endapan_kg') }}" required>
                        <p class="form-hint">Endapan di dasar wadah, dihargai lebih rendah per kg.</p>
                    </div>

                    <div>
                        <label for="catatan_pengepul">Catatan untuk Warga (opsional)</label>
                        <textarea id="catatan_pengepul" name="catatan_pengepul" rows="2"
                                  placeholder="Contoh: ada lapisan air di dasar wadah, tidak ikut ditimbang.">{{ old('catatan_pengepul') }}</textarea>
                    </div>

                    <div>
                        <label for="bukti_foto">Bukti Foto (opsional, maks 4 MB)</label>
                        <input id="bukti_foto" type="file" name="bukti_foto" accept="image/*">
                        <p class="form-hint">Foto kondisi minyak di lokasi sebagai bukti sorta.</p>
                    </div>

                    <div style="padding:13px 15px;background:var(--paper-warm);border:1px solid var(--line-soft);border-radius:8px;">
                        <p class="stat-label" style="margin:0;">Rincian nilai</p>
                        <dl class="kv" style="margin-top:6px;">
                            <div><dt>Minyak</dt><dd id="nilai-minyak">Rp0</dd></div>
                            <div><dt>Endapan</dt><dd id="nilai-endapan">Rp0</dd></div>
                        </dl>
                        <p style="font-family:var(--font-display);font-size:1.5rem;font-weight:600;color:var(--gold-deep);margin:8px 0 0;"
                           id="total-harga">Rp0</p>
                        <p class="form-hint" style="margin:4px 0 0;">Total ini yang masuk ke saldo warga.</p>
                    </div>

                    <button type="submit" class="btn">Konfirmasi &amp; Selesaikan</button>
                <x-form-validasi :form="'verifikasiSetoran'" />
    </form>
            </div>
        </div>
    </div>
@endif

@endsection

@push('scripts')
<script>
(function () {
    const minyak = document.getElementById('minyak_kg');
    const endapan = document.getElementById('endapan_kg');
    const total = document.getElementById('total-harga');
    if (!minyak || !endapan || !total) return;

    const hargaMinyak = {{ (float) $hargaMinyak }};
    const hargaEndapan = {{ (float) $hargaEndapan }};
    const maks = {{ (float) $maksKg }};
    const rupiah = (n) => 'Rp' + Math.round(n).toLocaleString('id-ID');

    function hitung() {
        const m = Number(minyak.value) || 0;
        const e = Number(endapan.value) || 0;
        const nm = Math.round(m * hargaMinyak);
        const ne = Math.round(e * hargaEndapan);

        document.getElementById('nilai-minyak').textContent = rupiah(nm) + ' (' + m + ' kg)';
        document.getElementById('nilai-endapan').textContent = rupiah(ne) + ' (' + e + ' kg)';
        total.textContent = rupiah(nm + ne);

        // Peringatan bila total melebihi batas.
        const lewat = (m + e) > maks;
        total.style.color = lewat ? 'var(--rust)' : 'var(--gold-deep)';
        if (lewat) {
            total.textContent += ' - melebihi batas ' + maks + ' kg';
        }
    }

    minyak.addEventListener('input', hitung);
    endapan.addEventListener('input', hitung);
    hitung();
})();
</script>
@endpush
