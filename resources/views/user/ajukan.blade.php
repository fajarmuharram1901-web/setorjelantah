@extends('layouts.dashboard', ['title' => 'Ajukan Setoran', 'subtitle' => 'Kirim pengajuan penjemputan minyak jelantah'])

@section('content')

<div class="page-head">
    <div>
        <h1 class="page-title">Ajukan Setoran</h1>
        <p class="page-sub">Perkirakan minyak dan endapan secara terpisah. Pengepul akan menimbangnya di lokasi untuk memastikan.</p>
    </div>
</div>

<div class="grid-2-panel">
    <div class="panel">
        <div class="panel-head">
            <div>
                <h2>Data Pengajuan</h2>
                <p class="panel-sub">Kolom bertanda bintang wajib diisi. Isi 0 bila tidak ada.</p>
            </div>
        </div>
        <div class="panel-body">
            <form data-validasi data-form="ajukanSetoran" novalidate method="POST" action="{{ route('user.ajukan.store') }}" style="max-width:100%;">
                @csrf

                <div>
                    <label for="perkiraan_minyak_kg">Perkiraan Minyak (kg) *</label>
                    <input id="perkiraan_minyak_kg" type="number" step="0.1" min="0" max="{{ $maksKg }}"
                           name="perkiraan_minyak_kg" value="{{ old('perkiraan_minyak_kg') }}"
                           placeholder="0" required>
                </div>

                <div>
                    <label for="perkiraan_endapan_kg">Perkiraan Endapan (kg) *</label>
                    <input id="perkiraan_endapan_kg" type="number" step="0.1" min="0" max="{{ $maksKg }}"
                           name="perkiraan_endapan_kg" value="{{ old('perkiraan_endapan_kg') }}"
                           placeholder="0" required>
                    <p class="form-hint">
                        Endapan adalah sisa gosong di dasar wadah. Dihargai
                        Rp{{ number_format($hargaEndapan, 0, ',', '.') }} per kg, lebih murah dari minyak.
                    </p>
                </div>

                <div>
                    <label for="alamat_jemput">Alamat Penjemputan *</label>
                    <textarea id="alamat_jemput" name="alamat_jemput" rows="3" required
                              placeholder="Contoh: Jl. Mawar No. 12, RT 03 / RW 05">{{ old('alamat_jemput', auth()->user()->alamat) }}</textarea>
                </div>

                <div>
                    <label for="catatan">Catatan untuk Pengepul (opsional)</label>
                    <textarea id="catatan" name="catatan" rows="2"
                              placeholder="Contoh: bisa dijemput setelah jam 16.00, ada banyak kemasan.">{{ old('catatan') }}</textarea>
                </div>

                <div style="padding:13px 15px;background:var(--paper-warm);border:1px solid var(--line-soft);border-radius:8px;">
                    <p class="stat-label" style="margin:0;">Perkiraan nilai setoran</p>

                    <dl class="kv" style="margin-top:6px;">
                        <div>
                            <dt>Minyak <small id="ringkas-minyak" class="text-muted">0 kg</small></dt>
                            <dd id="nilai-minyak">Rp0</dd>
                        </div>
                        <div>
                            <dt>Endapan <small id="ringkas-endapan" class="text-muted">0 kg</small></dt>
                            <dd id="nilai-endapan">Rp0</dd>
                        </div>
                        <div>
                            <dt>Total berat</dt>
                            <dd id="total-berat">0 kg</dd>
                        </div>
                    </dl>

                    <p style="font-family:var(--font-display);font-size:1.5rem;font-weight:600;color:var(--gold-deep);margin:8px 0 0;"
                       id="total-nilai">Rp0</p>
                    <p class="form-hint" style="margin:4px 0 0;">
                        Batas satu pengajuan {{ number_format($maksKg, 0, ',', '.') }} kg untuk minyak dan endapan.
                    </p>
                </div>

                <button type="submit" class="btn">Kirim Pengajuan</button>
            <x-form-validasi :form="'ajukanSetoran'" />
    </form>
        </div>
    </div>

    <div>
        <div class="panel">
            <div class="panel-head">
                <div>
                    <h2>Harga Saat Ini</h2>
                    <p class="panel-sub">Ditetapkan admin di halaman Harga</p>
                </div>
            </div>
            <div class="panel-body">
                <dl class="kv">
                    <div><dt>Minyak jelantah</dt><dd>Rp{{ number_format($hargaMinyak, 0, ',', '.') }} / kg</dd></div>
                    <div><dt>Endapan</dt><dd>Rp{{ number_format($hargaEndapan, 0, ',', '.') }} / kg</dd></div>
                    <div><dt>Batas berat per setoran</dt><dd>{{ number_format($maksKg, 0, ',', '.') }} kg</dd></div>
                </dl>

                <div class="alert alert-warning" style="margin:18px 0 0;">
                    Endapan dibayar terpisah dari minyak, jadi makin banyak endapan
                    nilainya makin kecil dibanding minyaknya.
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <h3>Cara Pengepul Menghitung</h3>
            </div>
            <div class="panel-body">
                <p class="text-muted" style="margin-top:0;font-size:0.86rem;">
                    Saat penjemputan, pengepul menimbang dua hal terpisah:
                </p>
                <ol style="margin:0;padding-left:18px;font-size:0.86rem;line-height:1.7;color:var(--ink-soft);">
                    <li><strong style="color:var(--ink);">Minyak</strong> - dibayar penuh sesuai harga minyak per kg.</li>
                    <li><strong style="color:var(--ink);">Endapan</strong> - endapan di dasar wadah, dihargai lebih rendah per kg.</li>
                </ol>
                <p class="text-muted" style="margin:12px 0 0;font-size:0.82rem;">
                    Contoh: perkiraan minyak 95 kg dan endapan 5 kg. Kalau harga minyak
                    Rp{{ number_format($hargaMinyak, 0, ',', '.') }}, maka 95 &times; Rp{{ number_format($hargaMinyak, 0, ',', '.') }}
                    + 5 &times; Rp{{ number_format($hargaEndapan, 0, ',', '.') }} =
                    Rp{{ number_format(95 * $hargaMinyak + 5 * $hargaEndapan, 0, ',', '.') }}.
                </p>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    const inputMinyak = document.getElementById('perkiraan_minyak_kg');
    const inputEndapan = document.getElementById('perkiraan_endapan_kg');
    const totalNilai = document.getElementById('total-nilai');
    if (!inputMinyak || !inputEndapan || !totalNilai) return;

    const hargaMinyak = {{ (float) $hargaMinyak }};
    const hargaEndapan = {{ (float) $hargaEndapan }};
    const maks = {{ (float) $maksKg }};

    const rupiah = (n) => 'Rp' + Math.round(n).toLocaleString('id-ID');
    const kilogram = (n) => n.toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' kg';

    function hitung() {
        const minyak = Math.max(0, Number(inputMinyak.value) || 0);
        const endapan = Math.max(0, Number(inputEndapan.value) || 0);
        const total = minyak + endapan;

        const nilaiMinyak = Math.round(minyak * hargaMinyak);
        const nilaiEndapan = Math.round(endapan * hargaEndapan);

        document.getElementById('nilai-minyak').textContent = rupiah(nilaiMinyak);
        document.getElementById('nilai-endapan').textContent = rupiah(nilaiEndapan);
        document.getElementById('ringkas-minyak').textContent = kilogram(minyak);
        document.getElementById('ringkas-endapan').textContent = kilogram(endapan);
        document.getElementById('total-berat').textContent = kilogram(total);

        const lewat = total > maks;
        totalNilai.textContent = rupiah(nilaiMinyak + nilaiEndapan)
            + (lewat ? ' - melebihi batas ' + maks + ' kg' : '');
        totalNilai.style.color = lewat ? 'var(--rust)' : 'var(--gold-deep)';
    }

    inputMinyak.addEventListener('input', hitung);
    inputEndapan.addEventListener('input', hitung);
    hitung();
})();
</script>
@endpush
