@php
    $user = auth()->user();
    $role = $user?->role ?? 'user';

    // Lencana angka pada menu, dihitung seperlunya saja.
    $lencana = [];
    if ($role === 'user') {
        $lencana['user.saldo'] = $user?->pencairans()->where('status', 'menunggu')->count() ?: null;
    } elseif ($role === 'pengepul') {
        $lencana['pengepul.dashboard'] = \App\Models\Setoran::menunggu()->count() ?: null;
    } elseif ($role === 'admin') {
        $lencana['admin.pencairan'] = \App\Models\Pencairan::menunggu()->count() ?: null;
    }

    $grup = [
        'user' => [
            ['label' => 'Menu Utama', 'items' => [
                ['route' => 'user.dashboard', 'icon' => 'grid', 'text' => 'Dashboard'],
                ['route' => 'user.ajukan', 'icon' => 'plus-circle', 'text' => 'Ajukan Setor'],
                ['route' => 'user.riwayat', 'icon' => 'history', 'text' => 'Riwayat Setoran'],
            ]],
            ['label' => 'Saldo', 'items' => [
                ['route' => 'user.saldo', 'icon' => 'wallet', 'text' => 'Saldo & Transaksi'],
                ['route' => 'user.pencairan.create', 'icon' => 'cash-out', 'text' => 'Pencairan Saldo'],
            ]],
            ['label' => 'Akun', 'items' => [
                ['route' => 'notifikasi.index', 'icon' => 'bell', 'text' => 'Notifikasi', 'count' => 'notif'],
                ['route' => 'profile.edit', 'icon' => 'settings', 'text' => 'Pengaturan'],
            ]],
        ],
        'pengepul' => [
            ['label' => 'Menu Utama', 'items' => [
                ['route' => 'pengepul.dashboard', 'icon' => 'grid', 'text' => 'Dashboard'],
                ['route' => 'pengepul.riwayat', 'icon' => 'history', 'text' => 'Riwayat Tugas'],
            ]],
            ['label' => 'Akun', 'items' => [
                ['route' => 'notifikasi.index', 'icon' => 'bell', 'text' => 'Notifikasi', 'count' => 'notif'],
                ['route' => 'profile.edit', 'icon' => 'settings', 'text' => 'Pengaturan'],
            ]],
        ],
        'admin' => [
            ['label' => 'Menu Utama', 'items' => [
                ['route' => 'admin.dashboard', 'icon' => 'grid', 'text' => 'Dashboard'],
                ['route' => 'admin.laporan', 'icon' => 'chart', 'text' => 'Laporan'],
            ]],
            ['label' => 'Operasional', 'items' => [
                ['route' => 'admin.pengguna', 'icon' => 'users', 'text' => 'Kelola Pengguna'],
                ['route' => 'admin.pencairan', 'icon' => 'wallet', 'text' => 'Pencairan Saldo'],
                ['route' => 'admin.harga', 'icon' => 'tag', 'text' => 'Harga'],
            ]],
            ['label' => 'Akun', 'items' => [
                ['route' => 'notifikasi.index', 'icon' => 'bell', 'text' => 'Notifikasi', 'count' => 'notif'],
                ['route' => 'profile.edit', 'icon' => 'settings', 'text' => 'Pengaturan'],
            ]],
        ],
    ][$role] ?? [];
@endphp

<aside class="shell-sidebar">
    <a href="{{ route('dashboard') }}" class="sidebar-brand">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M12 2.5C9.2 7.1 5.5 11.4 5.5 15.2C5.5 19.1 8.4 21.5 12 21.5C15.6 21.5 18.5 19.1 18.5 15.2C18.5 11.4 14.8 7.1 12 2.5Z" fill="#BE7E1D"/>
            <path d="M9.3 15.6C9.3 17.2 10.5 18.3 12 18.3" stroke="#FBF0DD" stroke-width="1.3" stroke-linecap="round"/>
        </svg>
        <span>SetorJelantah<small>{{ $user?->labelPeran() ?? 'Dashboard' }}</small></span>
    </a>

    <nav class="sidebar-nav">
        @foreach ($grup as $bagian)
            <div class="nav-group">
                <p class="nav-group-label">{{ $bagian['label'] }}</p>
                @foreach ($bagian['items'] as $item)
                    @php $aktif = request()->routeIs($item['route']); @endphp
                    <a href="{{ route($item['route']) }}" class="nav-item{{ $aktif ? ' is-active' : '' }}">
                        <x-icon :name="$item['icon']" />
                        <span>{{ $item['text'] }}</span>
                        @if (($item['count'] ?? null) === 'notif' && $user->unreadNotifications->isNotEmpty())
                            <span class="nav-count">{{ $user->unreadNotifications->count() }}</span>
                        @elseif (! empty($lencana[$item['route']]))
                            <span class="nav-count">{{ $lencana[$item['route']] }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        @endforeach

        <div class="nav-group">
            <form method="POST" action="{{ route('logout') }}" style="margin:0;max-width:none;">
                @csrf
                <button type="submit" class="nav-item" style="width:100%;background:none;border:0;cursor:pointer;font:inherit;">
                    <x-icon name="logout" />
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </nav>

    <div class="shell-foot">
        Kelompok 3 Golongan D &middot; PK3K<br>
        {{ now()->translatedFormat('d F Y') }}
    </div>
</aside>
