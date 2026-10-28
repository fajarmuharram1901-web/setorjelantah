@props(['title' => 'Dashboard', 'subtitle' => null])

@php
    $user = auth()->user();
    $role = $user?->role;
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} - SetorJelantah</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ config('app.asset_version', '1') }}">
    <link rel="stylesheet" href="{{ asset('css/ui.css') }}?v={{ config('app.asset_version', '1') }}">
</head>
<body>
<div class="shell" id="shell">

    @include('components.sidebar')

    <div class="shell-backdrop" id="shell-backdrop"></div>

    <div class="shell-main">
        <header class="shell-topbar">
            <button class="shell-toggle" id="shell-toggle" type="button" aria-label="Buka menu">
                <x-icon name="menu" :size="18" />
            </button>

            <div>
                <h1 class="topbar-title">{{ $title }}</h1>
                @if ($subtitle)
                    <p class="topbar-crumb">{{ $subtitle }}</p>
                @endif
            </div>

            <div class="topbar-spacer"></div>

            <div class="topbar-search">
                <x-icon name="search" :size="15" />
                <input type="search" id="topbar-search-input" placeholder="Cari setoran, pengguna, transaksi..."
                       aria-label="Cari" autocomplete="off"
                       value="{{ request('q', '') }}"
                       role="combobox" aria-expanded="false" aria-controls="topbar-search-results">
                <div class="search-results" id="topbar-search-results" hidden></div>
            </div>

            <a href="{{ route('notifikasi.index') }}" class="bell" title="Notifikasi" aria-label="Notifikasi">
                <x-icon name="bell" :size="17" />
                @if ($user && $user->unreadNotifications->isNotEmpty())
                    <span class="bell-badge" id="nav-notif-count">{{ $user->unreadNotifications->count() }}</span>
                @else
                    <span class="bell-badge" id="nav-notif-count" style="display:none;"></span>
                @endif
            </a>

            @if ($user)
                <a href="{{ route('profile.edit') }}" class="user-chip">
                    <span class="avatar">{{ $user->inisial() }}</span>
                    <span>
                        <span class="user-chip-name">{{ $user->name }}</span><br>
                        <span class="user-chip-role">{{ $user->labelPeran() }}</span>
                    </span>
                </a>
            @endif
        </header>

        <main class="shell-content">
            @include('components.flash')
            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')

<script>
    // Pemeriksaan email unik saat pengguna mengetik. Kolom yang sedang diedit
    // dikecualikan supaya admin tidak diberi tahu emailnya sendiri "dipakai".
    window.AturanValidasiServer = {
        email: function (nilai, selesai) {
            const lewati = document.querySelector('form[data-validasi] input[name="email"]');
            const id = lewati ? (lewati.dataset.abaikanId || '') : '';

            setTimeout(function () {
                fetch('{{ route('validasi.email') }}?email=' + encodeURIComponent(nilai)
                        + (id ? '&abaikan=' + encodeURIComponent(id) : ''), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(function (r) { return r.json(); })
                    .then(function (d) { selesai(!!d.dipakai); })
                    .catch(function () { selesai(false); });
            }, 400);
        }
    };
</script>
<script src="{{ asset('js/validasi.js') }}?v={{ config('app.asset_version', '1') }}"></script>
<script src="{{ asset('js/ui.js') }}?v={{ config('app.asset_version', '1') }}"></script>
<script>
(function () {
    const shell = document.getElementById('shell');
    const toggle = document.getElementById('shell-toggle');
    const backdrop = document.getElementById('shell-backdrop');

    if (toggle && shell) {
        toggle.addEventListener('click', function () { shell.classList.toggle('is-open'); });
        if (backdrop) {
            backdrop.addEventListener('click', function () { shell.classList.remove('is-open'); });
        }
    }

    // Pencarian global dengan hasil yang muncul saat mengetik.
    const cari = document.getElementById('topbar-search-input');
    const kotakHasil = document.getElementById('topbar-search-results');

    if (cari && kotakHasil) {
        let pengingat = null;

        function gambarHasil(data) {
            if (!data.results.length) {
                kotakHasil.innerHTML = '<p class="search-empty">' +
                    (data.message || 'Tidak ada hasil.') + '</p>';
            } else {
                kotakHasil.innerHTML = data.results.map(function (r) {
                    return '<a class="search-item" href="' + r.url + '">' +
                        '<span class="search-kind">' + r.jenis + '</span>' +
                        '<span class="search-body">' +
                        '<strong>' + r.judul + '</strong>' +
                        '<small>' + r.detail + '</small>' +
                        '</span>' +
                        '<span class="search-label">' + r.label + '</span>' +
                        '</a>';
                }).join('');
            }
            kotakHasil.hidden = false;
            cari.setAttribute('aria-expanded', 'true');
        }

        async function jalankanCari(kata) {
            try {
                const res = await fetch('{{ route('pencarian') }}?q=' + encodeURIComponent(kata), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!res.ok) return;
                gambarHasil(await res.json());
            } catch (e) { /* biarkan, coba lagi nanti */ }
        }

        cari.addEventListener('input', function () {
            const kata = cari.value.trim();
            clearTimeout(pengingat);

            if (kata.length < 2) {
                kotakHasil.hidden = true;
                cari.setAttribute('aria-expanded', 'false');
                return;
            }

            // Debounce supaya tidak memanggil server pada tiap ketikan.
            pengingat = setTimeout(function () { jalankanCari(kata); }, 300);
        });

        cari.addEventListener('focus', function () {
            if (cari.value.trim().length >= 2) jalankanCari(cari.value.trim());
        });

        document.addEventListener('click', function (e) {
            if (!kotakHasil.contains(e.target) && e.target !== cari) {
                kotakHasil.hidden = true;
                cari.setAttribute('aria-expanded', 'false');
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                kotakHasil.hidden = true;
                cari.setAttribute('aria-expanded', 'false');
                cari.blur();
            }
        });
    }

    // Notifikasi: hitung ulang secara berkala.
    const counter = document.getElementById('nav-notif-count');
    if (counter) {
        async function refresh() {
            try {
                const res = await fetch('{{ route('notifikasi.count') }}', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!res.ok) return;
                const data = await res.json();
                const jumlah = Number(data.jumlah) || 0;
                counter.textContent = jumlah > 99 ? '99+' : jumlah;
                counter.style.display = jumlah > 0 ? 'inline-block' : 'none';
            } catch (e) { /* coba lagi nanti */ }
        }
        setInterval(refresh, 15000);
    }

    // Hilangkan pesan flash setelah beberapa detik.
    document.querySelectorAll('.alert').forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity .45s ease';
            alert.style.opacity = '0';
            setTimeout(function () { alert.remove(); }, 450);
        }, 5000);
    });
})();
</script>
</body>
</html>