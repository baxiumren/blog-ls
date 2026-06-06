@props(['event'])

@php
    $type = $event['type'] ?? '';
    $detail = $event['detail'] ?? '';
@endphp

@if ($type === 'Goal')
    {{-- Bola sepak (gol) --}}
    <svg class="w-4 h-4 text-zinc-100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4">
        <circle cx="12" cy="12" r="9" />
        <path d="M12 8.2l3.1 2.25-1.18 3.65h-3.84L8.9 10.45 12 8.2z" fill="currentColor" stroke="none" />
        <path d="M12 3.2v2.4M5.4 8.6l2.1 1.1M18.6 8.6l-2.1 1.1M8.3 19.2l1.3-2.1M15.7 19.2l-1.3-2.1" stroke-linecap="round" />
    </svg>
@elseif ($type === 'Card')
    {{-- Kartu (kuning/merah) --}}
    <span class="inline-block w-2.5 h-3.5 rounded-[2px] {{ str_contains($detail, 'Red') ? 'bg-red-500' : 'bg-yellow-400' }}"></span>
@elseif ($type === 'subst')
    {{-- Pergantian (panah tukar) --}}
    <svg class="w-4 h-4 text-green-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h11l-3-3m3 3l-3 3M17 17H6l3 3m-3-3l3-3" />
    </svg>
@else
    {{-- Lainnya --}}
    <span class="inline-block w-1.5 h-1.5 rounded-full bg-zinc-500"></span>
@endif