@props(['team', 'logo' => null, 'size' => 'md'])

@php
    // 1) Ambil inisial dari nama tim
    $words = explode(' ', trim($team));
    if (count($words) > 1) {
        // >1 kata → huruf depan 2 kata pertama (Man City → MC)
        $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
    } else {
        // 1 kata → 3 huruf pertama (Arsenal → ARS)
        $initials = strtoupper(substr($team, 0, 3));
    }

    // 2) Warna konsisten berdasarkan nama (tim sama = warna sama tiap kali)
    $colors = ['bg-red-600','bg-blue-600','bg-green-600','bg-purple-600','bg-orange-600','bg-pink-600','bg-indigo-600','bg-teal-600'];
    $color  = $colors[abs(crc32($team)) % count($colors)];

    // 3) Ukuran (kecil/sedang/besar)
    $sizes = [
        'sm' => 'w-5 h-5 text-[9px]',
        'md' => 'w-6 h-6 text-[10px]',
        'lg' => 'w-16 h-16 text-xl',
        'xl' => 'w-9 h-9 text-xs',
    ];
@endphp

@if ($logo)
    <img src="{{ $logo }}" alt="{{ $team }}" loading="{{ in_array($size, ['lg', 'xl']) ? 'eager' : 'lazy' }}" decoding="async" class="{{ $sizes[$size] }} object-contain shrink-0" />
@else
    <span class="{{ $sizes[$size] }} {{ $color }} rounded-full flex items-center justify-center font-bold text-white shrink-0">
        {{ $initials }}
    </span>
@endif