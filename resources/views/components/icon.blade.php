@props(['name' => 'grid', 'size' => 17])

@php
    // Ikon garis 24x24, digambar inline agar tidak perlu paket ikon.
    $paths = [
        'grid' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>',
        'home' => '<path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V20a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V9.5"/><path d="M9.5 21v-6h5v6"/>',
        'droplet' => '<path d="M12 2.7C9.2 7.3 5.5 11.6 5.5 15.4A6.5 6.5 0 0 0 18.5 15.4C18.5 11.6 14.8 7.3 12 2.7Z"/>',
        'plus-circle' => '<circle cx="12" cy="12" r="9"/><path d="M12 8.5v7M8.5 12h7"/>',
        'history' => '<path d="M3.5 12a8.5 8.5 0 1 0 2.6-6.1"/><path d="M3 4.5V9h4.5"/><path d="M12 7.5V12l3 2"/>',
        'wallet' => '<path d="M3 7.5A2.5 2.5 0 0 1 5.5 5H18a2 2 0 0 1 2 2v1"/><path d="M3 7.5V17a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-3"/><path d="M21 10.5h-4.5a2 2 0 0 0 0 5H21Z"/>',
        'cash-out' => '<path d="M12 3v11"/><path d="M8.5 10.5 12 14l3.5-3.5"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/>',
        'users' => '<path d="M16 20v-1.5a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4V20"/><circle cx="9.5" cy="7" r="3.5"/><path d="M21 20v-1.5a4 4 0 0 0-3-3.85"/><path d="M16 4.15a4 4 0 0 1 0 7.7"/>',
        'tag' => '<path d="M3.5 11.5V5a1.5 1.5 0 0 1 1.5-1.5h6.5L21 13l-7.5 7.5Z"/><circle cx="7.8" cy="7.8" r="1.4"/>',
        'chart' => '<path d="M4 20V4"/><path d="M4 20h16"/><rect x="7" y="12" width="3" height="5"/><rect x="12" y="8" width="3" height="9"/><rect x="17" y="14" width="3" height="3"/>',
        'bell' => '<path d="M18 8.5a6 6 0 0 0-12 0c0 7-2.5 8.5-2.5 8.5h17S18 15.5 18 8.5"/><path d="M13.7 20.5a2 2 0 0 1-3.4 0"/>',
        'search' => '<circle cx="11" cy="11" r="7"/><path d="M20 20l-3.6-3.6"/>',
        'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'logout' => '<path d="M14 20H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h8"/><path d="M17 15l3-3-3-3"/><path d="M20 12H10"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 20.5a8 8 0 0 1 16 0"/>',
        'check' => '<path d="M4.5 12.5 9 17l10.5-10.5"/>',
        'x' => '<path d="M6 6l12 12M18 6 6 18"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5.5l3.5 2"/>',
        'truck' => '<path d="M3 7.5A1.5 1.5 0 0 1 4.5 6H14v10H3Z"/><path d="M14 10h3.5L21 13v3h-7Z"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/>',
        'camera' => '<path d="M4 8.5A1.5 1.5 0 0 1 5.5 7h1.7l1.2-2h6.2L15.8 7h2.7A1.5 1.5 0 0 1 20 8.5v9A1.5 1.5 0 0 1 18.5 19h-13A1.5 1.5 0 0 1 4 17.5Z"/><circle cx="12" cy="13" r="3.2"/>',
        'star' => '<path d="m12 4 2.5 5.2 5.5.8-4 3.9 1 5.6-5-2.7-5 2.7 1-5.6-4-3.9 5.5-.8Z"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M12 3v2.5M12 18.5V21M21 12h-2.5M5.5 12H3M18.4 5.6l-1.8 1.8M7.4 16.6l-1.8 1.8M18.4 18.4l-1.8-1.8M7.4 7.4 5.6 5.6"/>',
        'trend-up' => '<path d="M3 17l6-6 4 4 8-8"/><path d="M15 7h6v6"/>',
        'trend-down' => '<path d="M3 7l6 6 4-4 8 8"/><path d="M15 17h6v-6"/>',
        'filter' => '<path d="M3 5h18l-7 8v6l-4 2v-8Z"/>',
        'download' => '<path d="M12 3v12"/><path d="M8 11.5 12 15.5l4-4"/><path d="M4 20h16"/>',
        'alert' => '<path d="M12 3.5 21 19.5H3Z"/><path d="M12 9.5v4.5"/><path d="M12 17.2v.1"/>',
        'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5.5"/><path d="M12 7.8v.1"/>',
        'arrow-right' => '<path d="M4 12h15"/><path d="M13.5 6.5 20 12l-6.5 5.5"/>',
        'scale' => '<path d="M12 4v16"/><path d="M6 8h12"/><path d="M6 8 3 15h6Z"/><path d="M18 8l-3 7h6Z"/>',
        'inbox' => '<path d="M4 13h4l1.5 3h5L16 13h4"/><path d="M5.5 5h13l1.5 8v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-5Z"/>',
    ];

    $svg = $paths[$name] ?? $paths['grid'];
@endphp

<svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="currentColor"
     stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
    {!! $svg !!}
</svg>
