<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'SetorJelantah' }}</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ui.css') }}?v={{ config('app.asset_version', '1') }}">
</head>
<body class="auth-body">
    <div class="auth-wrap">
        <div class="auth-aside">
            <a href="{{ route('home') }}" class="brand" style="color:#FBF0DD;">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M12 2.5C9.2 7.1 5.5 11.4 5.5 15.2C5.5 19.1 8.4 21.5 12 21.5C15.6 21.5 18.5 19.1 18.5 15.2C18.5 11.4 14.8 7.1 12 2.5Z" fill="#BE7E1D"/>
                    <path d="M9.3 15.6C9.3 17.2 10.5 18.3 12 18.3" stroke="#FBF0DD" stroke-width="1.3" stroke-linecap="round"/>
                </svg>
                SetorJelantah
            </a>

            <h2>Kumpulkan minyak jelantah,<br>jadi saldo.</h2>

            {{-- Gradasi warna minyak: dari minyak jernih ke endapan pekat. --}}
            <div class="auth-gradient" aria-hidden="true"></div>

            <ul class="auth-points">
                <li><strong>Dihitung per kilogram</strong><span>Minyak dan endapan ditimbang terpisah, masing-masing punya harga sendiri.</span></li>
                <li><strong>Harga transparan</strong><span>Harga minyak dan endapan ditetapkan admin dan bisa dilihat semua warga.</span></li>
                <li><strong>Saldo bisa dicairkan</strong><span>Uang minyak ditransfer ke e-wallet, bank, atau diambil di kios.</span></li>
            </ul>
        </div>

        <div class="auth-card">
            {{-- Pesan error / sukses wajib tampil di halaman auth, kalau tidak
                 pengguna tidak pernah tahu kalau emailnya salah atau tidak terdaftar. --}}
            @include('components.flash')
            {{ $slot }}
        </div>
    </div>
    <script>
        // Pemeriksaan email unik saat pengguna mengetik.
        window.AturanValidasiServer = {
            email: function (nilai, selesai) {
                const t = setTimeout(function () {
                    fetch('{{ route('validasi.email') }}?email=' + encodeURIComponent(nilai), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                        .then(function (r) { return r.json(); })
                        .then(function (d) { selesai(!!d.dipakai); })
                        .catch(function () { selesai(false); });
                }, 400);

                return t;
            }
        };
    </script>
    <script src="{{ asset('js/validasi.js') }}?v={{ config('app.asset_version', '1') }}"></script>
    <script src="{{ asset('js/ui.js') }}?v={{ config('app.asset_version', '1') }}"></script>
</body>
</html>