@extends('layouts.app')

@section('title', 'Beranda')

@section('content')

<section class="hero">
    <div class="hero-copy">
        <span class="pill">Minyak jelantah bekas &rarr; diolah ulang jadi biodiesel</span>
        <h1>Setor minyak jelantah, dapat saldo</h1>
        <p>SetorJelantah menghubungkan rumah tangga dengan pengepul untuk mengumpulkan minyak jelantah bekas.
           Ajukan setoran, pengepul datang menimbang, dan saldo Anda bertambah otomatis.</p>
        <div class="hero-price">Rp{{ number_format($hargaMinyak, 0, ',', '.') }} / kg minyak</div>
        <div class="hero-actions">
            <a href="{{ route('register') }}" class="btn">Daftar Sekarang</a>
            <a href="{{ route('login') }}" class="btn btn-secondary">Masuk</a>
        </div>
    </div>

    <div class="hero-art">
        <svg viewBox="0 0 200 240" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <defs>
                <linearGradient id="minyak" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#F2C230"/>
                    <stop offset="45%" stop-color="#DB9A26"/>
                    <stop offset="100%" stop-color="#A96A16"/>
                </linearGradient>
            </defs>
            <path d="M100 15
                     C140 70 175 112 175 152
                     C175 199 142 227 100 227
                     C58 227 25 199 25 152
                     C25 112 60 70 100 15 Z"
                  fill="url(#minyak)" stroke="#8C5A12" stroke-width="3"/>
            <path d="M100 55C82 92 55 122 55 150C55 178 75 197 100 197"
                  fill="none" stroke="#FBF0DD" stroke-width="2" stroke-linecap="round" stroke-dasharray="1 7" opacity="0.5"/>
            <circle cx="72" cy="140" r="6" fill="#FFFFFF" opacity="0.55"/>
        </svg>
    </div>
</section>

{{-- Angka nyata dari basis data, supaya beranda tidak sekadar janji. --}}
@if ($totalKg > 0)
    <div class="stat-grid stat-grid-4">
        <x-stat-card label="Minyak Terkumpul"
                     :value="number_format($totalMinyak, 1, ',', '.') . ' kg'"
                     icon="droplet" accent="#DB9A26" tint="#FBF0DD"
                     :meta="$jumlahSetoran . ' setoran berhasil'" />
        <x-stat-card label="Endapan Terkumpul"
                     :value="number_format($totalEndapan, 1, ',', '.') . ' kg'"
                     icon="scale" accent="#5A3310" tint="#F1E7D6"
                     meta="Dihargai terpisah" />
        <x-stat-card label="Nilai Dibayarkan"
                     :value="'Rp' . number_format($totalNilai, 0, ',', '.')"
                     icon="wallet" accent="#55703C" tint="#EAF0E2"
                     :meta="$jumlahWarga . ' rumah tangga terlibat'" />
        <x-stat-card label="Pengepul Aktif"
                     :value="$jumlahPengepul"
                     icon="truck" accent="#3E5E7E" tint="#E9F0F6"
                     meta="Siap menjemput" />
    </div>
@endif

<div class="panel">
    <div class="panel-head">
        <div>
            <h2>Dua Jenis Berat, Dua Harga</h2>
            <p class="panel-sub">Minyak dan endapan ditimbang terpisah, masing-masing punya harga per kilogram.</p>
        </div>
    </div>
    <div class="panel-body">
        <div class="grid grid-2">
            <div style="display:flex;gap:12px;align-items:flex-start;">
                <span class="quality-dot" style="background:#DB9A26;width:12px;height:12px;flex:0 0 12px;margin-top:5px;"></span>
                <div>
                    <strong style="font-size:0.92rem;">Minyak jelantah</strong>
                    <p class="text-muted" style="margin:2px 0 6px; font-size:0.84rem;">
                        Minyak goreng bekas yang sudah disaring, dibayar penuh sesuai harga minyak.
                    </p>
                    <span class="quality-chip" style="background:#DB9A2626; border-color:#DB9A2680; color:#6B4310;">
                        Rp{{ number_format($hargaMinyak, 0, ',', '.') }} / kg
                    </span>
                </div>
            </div>

            <div style="display:flex;gap:12px;align-items:flex-start;">
                <span class="quality-dot" style="background:#5A3310;width:12px;height:12px;flex:0 0 12px;margin-top:5px;"></span>
                <div>
                    <strong style="font-size:0.92rem;">Endapan</strong>
                    <p class="text-muted" style="margin:2px 0 6px; font-size:0.84rem;">
                        Endapan gosong di dasar wadah. Tetap bernilai, tapi dihargai lebih rendah per kg.
                    </p>
                    <span class="quality-chip" style="background:#5A331026; border-color:#5A331080; color:#6B4310;">
                        Rp{{ number_format($hargaEndapan, 0, ',', '.') }} / kg
                    </span>
                </div>
            </div>
        </div>

        <div class="alert alert-warning" style="margin:20px 0 0;">
            Satu pengajuan maksimal {{ number_format($maksKg, 0, ',', '.') }} kg untuk minyak dan endapan.
        </div>
    </div>
</div>

<div class="grid grid-3">
    <div class="card">
        <h3>1. Ajukan Setoran</h3>
        <p class="text-muted">Masukkan perkiraan berat (total minyak + endapan) dan alamat penjemputan.</p>
    </div>
    <div class="card">
        <h3>2. Pengepul Menimbang</h3>
        <p class="text-muted">Pengepul datang, menimbang minyak dan endapan secara terpisah, lalu mengonfirmasi.</p>
    </div>
    <div class="card">
        <h3>3. Saldo Bertambah</h3>
        <p class="text-muted">Nilai minyak dan endapan dijumlahkan, masuk ke saldo, lalu bisa Anda cairkan.</p>
    </div>
</div>

@endsection
