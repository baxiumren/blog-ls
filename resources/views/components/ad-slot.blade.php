@props(['format' => 'horizontal'])

@php
    $adsEnabled = ($settings['ads_enabled'] ?? '0') === '1';
    $codeKey = match ($format) {
        'leaderboard', 'horizontal' => 'ad_horizontal',
        'rectangle', 'inarticle'    => 'ad_rectangle',
        'sidebar'                   => 'ad_vertical',
        default                     => 'ad_horizontal',
    };
    $adCode = $settings[$codeKey] ?? null;
    // [max-width, tinggi placeholder] per format (ukuran standar AdSense)
    $formats = [
        'leaderboard' => ['max-w-[728px]', 'h-[90px]'],   // banner lebar (atas halaman)
        'horizontal'  => ['max-w-3xl',     'h-[90px]'],    // banner umum
        'rectangle'   => ['max-w-[336px]', 'h-[280px]'],   // kotak medium (konten/sidebar)
        'inarticle'   => ['max-w-full',    'h-[180px]'],   // dalam artikel (fluid)
        'sidebar'     => ['max-w-[300px]', 'h-[600px]'],   // vertikal (sidebar)
    ];
    [$maxw, $h] = $formats[$format] ?? $formats['horizontal'];
@endphp

@if ($adsEnabled)
    <div class="mb-4 flex justify-center">
        @if (! empty($adCode))
            <div class="w-full {{ $maxw }}">{!! $adCode !!}</div>
        @else
            <div class="w-full {{ $maxw }} {{ $h }} rounded-lg border border-dashed border-zinc-700 bg-zinc-900/40 flex items-center justify-center text-zinc-600 text-[10px] uppercase tracking-widest">
                Ad · {{ $format }}
            </div>
        @endif
    </div>
@endif