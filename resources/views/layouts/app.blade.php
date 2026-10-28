<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Beranda') - SetorJelantah</title>
    <meta name="description" content="SetorJelantah: kumpulkan minyak jelantah rumah tangga, pengepul menjemput, saldo bertambah.">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
<header class="navbar">
    <div class="navbar-inner">
        <a href="{{ auth()->check() ? route('dashboard') : route('home') }}" class="brand">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M12 2.5C9.2 7.1 5.5 11.4 5.5 15.2C5.5 19.1 8.4 21.5 12 21.5C15.6 21.5 18.5 19.1 18.5 15.2C18.5 11.4 14.8 7.1 12 2.5Z" fill="#BE7E1D"/>
                <path d="M9.3 15.6C9.3 17.2 10.5 18.3 12 18.3" stroke="#FBF0DD" stroke-width="1.3" stroke-linecap="round"/>
            </svg>
            SetorJelantah
        </a>
        <nav>
            @auth
                {{-- Landing page hanya untuk tamu, jadi yang sudah login
                     langsung diarahkan ke dashboardnya masing-masing. --}}
                <a href="{{ route('dashboard') }}">Dashboard</a>
                <form method="POST" action="{{ route('logout') }}" style="display:inline;max-width:none;">
                    @csrf
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">Keluar</a>
                </form>
            @else
                <a href="{{ route('home') }}">Beranda</a>
                <a href="{{ route('login') }}">Masuk</a>
                <a href="{{ route('register') }}">Daftar</a>
            @endauth
        </nav>
    </div>
</header>

<main class="container">
    @include('components.flash')
    @yield('content')
</main>

<footer class="footer">
    <p>&copy; {{ date('Y') }} Kelompok 3 Golongan D &middot; PK3K</p>
</footer>
</body>
</html>
