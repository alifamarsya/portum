@php
    $cls = $class ?? 'w-5 h-5';
    $sw = $stroke ?? 1.7;
    $icons = [
        'home'        => '<path d="M4 11.2 12 4.5l8 6.7"/><path d="M5.5 9.8V19a1 1 0 0 0 1 1H9.8v-5.2a1.7 1.7 0 0 1 1.7-1.7h1a1.7 1.7 0 0 1 1.7 1.7V20h3.3a1 1 0 0 0 1-1V9.8"/>',
        'chart'       => '<rect x="3.5" y="12.5" width="4" height="8" rx="1"/><rect x="10" y="8.5" width="4" height="12" rx="1"/><rect x="16.5" y="4.5" width="4" height="16" rx="1"/>',
        'truck'       => '<rect x="2.5" y="7.5" width="11" height="9" rx="1.2"/><path d="M13.5 10.5H17l3 3v3h-2"/><circle cx="7" cy="18" r="1.6"/><circle cx="16.5" cy="18" r="1.6"/><path d="M13.5 16.4H10"/>',
        'archive'     => '<rect x="3" y="4.5" width="18" height="4" rx="1"/><path d="M4.5 8.5V18a1.2 1.2 0 0 0 1.2 1.2h12.6A1.2 1.2 0 0 0 19.5 18V8.5"/><path d="M10 12.5h4"/>',
        'wrench'      => '<path d="M14.7 6.3a3.8 3.8 0 0 0-5 4.6L4.3 16.3a1.7 1.7 0 0 0 2.4 2.4l5.4-5.4a3.8 3.8 0 0 0 4.6-5l-2.4 2.4-2-2 2.4-2.4Z"/>',
        'inbox'       => '<path d="M4 12.5h4.3l1.4 2.4h4.6l1.4-2.4H20"/><path d="M5.2 6.8 4 12.5V18a1.3 1.3 0 0 0 1.3 1.3h13.4A1.3 1.3 0 0 0 20 18v-5.5l-1.2-5.7a1.3 1.3 0 0 0-1.27-1.05H6.47A1.3 1.3 0 0 0 5.2 6.8Z"/>',
        'book'        => '<path d="M12 6.3c-1.6-1-4-1.3-6-1.1a1 1 0 0 0-.9 1v11a.9.9 0 0 0 1 .9c2-.2 4.5.1 5.9 1.2 1.4-1.1 3.9-1.4 5.9-1.2a.9.9 0 0 0 1-.9v-11a1 1 0 0 0-.9-1c-2-.2-4.4.1-6 1.1Z"/><path d="M12 6.3V19.3"/>',
        'sliders'     => '<path d="M4 6h9M17 6h3M4 12h3M9 12h11M4 18h13M19 18h1"/><circle cx="15" cy="6" r="1.8"/><circle cx="7" cy="12" r="1.8"/><circle cx="17" cy="18" r="1.8"/>',
        'users'       => '<circle cx="8.5" cy="8" r="3"/><path d="M2.8 19c.6-3 2.9-5 5.7-5s5.1 2 5.7 5"/><circle cx="17" cy="9" r="2.4"/><path d="M15.7 14.2c2.2.4 3.9 2.1 4.4 4.6"/>',
        'shield'      => '<path d="M12 3.5 19 6v5.3c0 4.2-2.8 7.4-7 8.7-4.2-1.3-7-4.5-7-8.7V6l7-2.5Z"/><path d="m9.2 12 1.9 1.9 3.7-3.9"/>',
        'clock'       => '<circle cx="12" cy="12" r="8.2"/><path d="M12 7.5V12l3 2"/>',
        'logout'      => '<path d="M9.5 20H6a1.6 1.6 0 0 1-1.6-1.6V5.6A1.6 1.6 0 0 1 6 4h3.5"/><path d="M15.5 16.3 20 12l-4.5-4.3M20 12H9.7"/>',
        'search'      => '<circle cx="10.6" cy="10.6" r="6.1"/><path d="m19.5 19.5-4.2-4.2"/>',
        'plus'        => '<path d="M12 5.5v13M5.5 12h13"/>',
        'pencil'      => '<path d="m14.3 4.8 4.9 4.9L7.8 18.1l-5 1 1-5Z"/><path d="m12.3 6.8 4.9 4.9"/>',
        'trash'       => '<path d="M4.5 7h15"/><path d="M9.5 7V5.3A1.3 1.3 0 0 1 10.8 4h2.4a1.3 1.3 0 0 1 1.3 1.3V7"/><path d="M6.5 7 7.3 19a1.5 1.5 0 0 0 1.5 1.4h6.4a1.5 1.5 0 0 0 1.5-1.4L17.5 7"/><path d="M10.3 11v6M13.7 11v6"/>',
        'chevron-down'=> '<path d="m6 9 6 6 6-6"/>',
        'chevron-right'=>'<path d="m9 6 6 6-6 6"/>',
        'check-circle'=> '<circle cx="12" cy="12" r="8.2"/><path d="m8.3 12.3 2.4 2.4 5-5.4"/>',
        'x-circle'    => '<circle cx="12" cy="12" r="8.2"/><path d="m9.2 9.2 5.6 5.6M14.8 9.2l-5.6 5.6"/>',
        'alert'       => '<path d="M12 4.2 21 19.5H3L12 4.2Z"/><path d="M12 10v4.2"/><circle cx="12" cy="17" r=".15" fill="currentColor" stroke-width="2.6"/>',
        'bank'        => '<path d="M3.5 9.5 12 4l8.5 5.5"/><path d="M4.5 9.5h15V19a1 1 0 0 1-1 1H5.5a1 1 0 0 1-1-1V9.5Z"/><path d="M8 13v4M12 13v4M16 13v4"/>',
        'key'         => '<circle cx="7.3" cy="14.7" r="3.3"/><path d="m9.6 12.4 7.9-7.9M15 6l2 2M17.9 3.1l2 2"/>',
        'link'        => '<path d="M9 15 15 9"/><path d="M11 6.5 12.4 5a3.7 3.7 0 0 1 5.2 5.2l-1.5 1.4M13 17.5 11.6 19a3.7 3.7 0 0 1-5.2-5.2l1.5-1.4"/>',
        'file-text'   => '<path d="M7 3.5h7l4 4V19a1.2 1.2 0 0 1-1.2 1.2H7A1.2 1.2 0 0 1 5.8 19V4.7A1.2 1.2 0 0 1 7 3.5Z"/><path d="M14 3.5V8h4.2"/><path d="M8.5 12.5h7M8.5 15.8h4.5"/>',
        'trend-up'    => '<path d="m4 16 5.5-5.5L13 14l7-7"/><path d="M16.5 7H20v3.5"/>',
        'building'    => '<rect x="5" y="3.5" width="10" height="17" rx="1"/><path d="M15 9.5h4.5v10a1 1 0 0 1-1 1H15"/><path d="M8 7.5h1M8 11h1M8 14.5h1M11.5 7.5h1M11.5 11h1M11.5 14.5h1"/>',
        'bell'        => '<path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9Z"/><path d="M10 21h4"/>',
        'menu'        => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'lock'        => '<rect x="5" y="10.5" width="14" height="9" rx="1.4"/><path d="M8 10.5V7.8a4 4 0 0 1 8 0v2.7"/>',
    ];
    $svg = $icons[$name] ?? $icons['file-text'];
@endphp
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="{{ $sw }}" stroke-linecap="round" stroke-linejoin="round" class="{{ $cls }}">{!! $svg !!}</svg>
