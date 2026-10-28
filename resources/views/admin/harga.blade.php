@extends('layouts.dashboard', ['title' => 'Harga Minyak & Endapan', 'subtitle' => 'Harga dasar per kilogram untuk minyak dan endapan'])

@section('content')

<div class="page-head">
    <div>
        <h1 class="page-title">Harga per Kilogram</h1>
        <p class="page-sub">Minyak dan endapan dibayar terpisah, masing-masing punya harga per kilogram sendiri.</p>
    </div>
</div>

{{-- Sakelar buka/kunci harga. Admin yang memutuskan apakah aturan jeda
     7 hari dijalankan, jadi harga bisa dibuka bebas saat demonstrasi/ujian. --}}
<div class="panel" style="margin-bottom:20px;">
    <div class="panel-body" style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
        <span class="badge {{ $jedaAktif ? 'badge-warning' : 'badge-secondary' }}" style="font-size:0.8rem;padding:6px 12px;">
            {{ $jedaAktif ? 'Kunci Harga: AKTIF' : 'Kunci Harga: TERBUKA' }}
        </span>

        <div style="flex:1 1 320px;min-width:260px;">
            <p style="margin:0;font-size:0.88rem;font-weight:600;color:var(--ink);">
                @if ($jedaAktif)
                    Harga hanya bisa diubah sekali dalam {{ \App\Models\Harga::JEDA_HARGA_HARI }} hari.
                @else
                    Harga bebas diubah kapan saja.
                @endif
            </p>
            <p class="form-hint" style="margin:2px 0 0;">
                @if ($jedaAktif)
                    Buka kunci dulu bila sedang demonstrasi atau ujian, lalu kunci kembali setelahnya.
                @else
                    Aturan jeda {{ \App\Models\Harga::JEDA_HARGA_HARI }} hari sedang dimatikan. Kunci kembali setelah demonstrasi selesai.
                @endif
            </p>
        </div>

        <form method="POST" action="{{ route('admin.harga.kunci') }}" style="max-width:none;margin:0;">
            @csrf
            <label class="switch">
                <input type="checkbox" role="switch" data-switch {{ $jedaAktif ? 'checked' : '' }}
                       data-judul="Kunci Harga"
                       data-label-on="Kunci aktif" data-label-off="Kunci terbuka"
                       data-pesan-on="Aktifkan kunci harga? Perubahan dibatasi sekali dalam {{ \App\Models\Harga::JEDA_HARGA_HARI }} hari."
                       data-pesan-off="Buka kunci harga? Harga akan bisa diubah kapan saja.">
                <span class="switch-track"><span class="switch-thumb"></span></span>
                <span class="switch-label">{{ $jedaAktif ? 'Kunci aktif' : 'Kunci terbuka' }}</span>
            </label>
            <noscript><button type="submit" class="btn btn-sm btn-secondary">Ubah kunci</button></noscript>
        <x-form-validasi :form="'updateHarga'" />
    </form>
    </div>
</div>

<div class="grid-2-panel">
    <div class="panel">
        <div class="panel-head">
            <h2>Harga yang Berlaku</h2>
        </div>
        <div class="panel-body">
            <div class="stat-grid" style="margin-bottom:16px;">
                <x-stat-card label="Minyak Jelantah"
                             :value="'Rp' . number_format($hargaMinyak, 0, ',', '.')"
                             icon="droplet" accent="#DB9A26" tint="#FBF0DD"
                             meta="Per kilogram" small />
                <x-stat-card label="Endapan"
                             :value="'Rp' . number_format($hargaEndapan, 0, ',', '.')"
                             icon="scale" accent="#5A3310" tint="#F1E7D6"
                             meta="Per kilogram" small />
            </div>

            {{-- Harga dikunci selama masa jeda 7 hari. Field tetap dinonaktifkan
                 supaya admin melihat alasannya, bukan sekadar tidak bisa menyimpan. --}}
            @if ($sisaHari > 0)
                <div class="alert alert-warning" style="margin-bottom:16px;">
                    <strong>Harga sedang dikunci.</strong> Perubahan terakhir baru
                    {{ $riwayatHarga->first()?->created_at?->diffForHumans() ?? 'beberapa waktu lalu' }},
                    sehingga harga baru bisa diubah lagi dalam
                    <strong>{{ $sisaHari }} hari</strong> lagi, yaitu pada
                    <strong>{{ $bolehUbahPada->translatedFormat('d F Y \p\u\k H:i') }} WIB</strong>.
                    <br>Butuh segera diubah? Tekan <strong>Buka Kunci Harga</strong> di atas.
                </div>
            @endif

            <form data-validasi data-form="updateHarga" novalidate method="POST" action="{{ route('admin.harga.update') }}" style="max-width:100%;">
                @csrf

                <div>
                    <label for="harga_minyak_kg">Harga Minyak per Kilogram (Rp)</label>
                    <input id="harga_minyak_kg" type="number" min="1" max="{{ $maksHarga }}" step="1"
                           name="harga_minyak_kg" value="{{ old('harga_minyak_kg', (int) round($hargaMinyak)) }}"
                           required @disabled($sisaHari > 0)>
                    <p class="form-hint">
                        Boleh diisi bebas dalam rupiah, misalnya 9000 atau 10000.
                        Batas maksimum Rp{{ number_format($maksHarga, 0, ',', '.') }} per kg.
                    </p>
                </div>

                <div>
                    <label for="harga_endapan_kg">Harga Endapan per Kilogram (Rp)</label>
                    <input id="harga_endapan_kg" type="number" min="0" max="{{ $maksHarga }}" step="1"
                           name="harga_endapan_kg" value="{{ old('harga_endapan_kg', (int) round($hargaEndapan)) }}"
                           required @disabled($sisaHari > 0)>
                    <p class="form-hint">
                        Nilai bawaan Rp5.000 per kg. Boleh 0 bila endapan tidak dibayar.
                    </p>
                </div>

                <button type="submit" class="btn" @disabled($sisaHari > 0)>
                    {{ $sisaHari > 0 ? 'Harga Terkunci' : 'Perbarui Harga' }}
                </button>
                <p class="form-hint">
                    Berlaku untuk setoran yang dikonfirmasi pengepul setelah harga ini disimpan.
                    Harga pada setoran yang sudah selesai tidak ikut berubah.<br>
                    Harga hanya dapat diubah sekali dalam
                    {{ \App\Models\Harga::JEDA_HARGA_HARI }} hari supaya nilai historis
                    tidak berubah-ubah di tengah jalan.
                </p>
            </form>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head">
            <h3>Contoh Perhitungan</h3>
        </div>
        <div class="panel-body">
            <p class="text-muted" style="margin-top:0;font-size:0.86rem;">
                Warga memperkirakan 100 kg. Di lokasi ternyata minyak 95 kg dan endapan 5 kg:
            </p>

            <div class="table-wrap">
                <table class="compact">
                    <thead><tr><th>Jenis</th><th class="num">Berat</th><th class="num">Harga/kg</th><th class="num">Nilai</th></tr></thead>
                    <tbody>
                        <tr>
                            <td>Minyak jelantah</td>
                            <td class="num">95 kg</td>
                            <td class="num">Rp{{ number_format($hargaMinyak, 0, ',', '.') }}</td>
                            <td class="num"><strong>Rp{{ number_format(95 * $hargaMinyak, 0, ',', '.') }}</strong></td>
                        </tr>
                        <tr>
                            <td>Endapan</td>
                            <td class="num">5 kg</td>
                            <td class="num">Rp{{ number_format($hargaEndapan, 0, ',', '.') }}</td>
                            <td class="num"><strong>Rp{{ number_format(5 * $hargaEndapan, 0, ',', '.') }}</strong></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-right">Total dibayar</th>
                            <th class="num">Rp{{ number_format(95 * $hargaMinyak + 5 * $hargaEndapan, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <h3>Riwayat Perubahan Harga</h3>
    </div>
    <div class="panel-body panel-body-flush">
        @if ($riwayatHarga->isEmpty())
            <x-empty title="Belum ada riwayat" message="Harga bawaan akan tampil di sini." icon="tag" />
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Minyak / kg</th><th>Endapan / kg</th><th>Diubah oleh</th><th>Tanggal</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($riwayatHarga as $h)
                            <tr>
                                <td><strong>Rp{{ number_format($h->harga_minyak_kg, 0, ',', '.') }}</strong></td>
                                <td>Rp{{ number_format($h->harga_endapan_kg, 0, ',', '.') }}</td>
                                <td>{{ $h->admin->name ?? '-' }}</td>
                                <td>{{ $h->updated_at->format('d M Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@endsection