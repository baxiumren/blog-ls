@props(['player', 'stat' => [], 'minute' => null])

@php
    $pl = $player['player'] ?? [];
    $name = \Illuminate\Support\Str::afterLast($pl['name'] ?? '', ' ');
    $num = $pl['number'] ?? '';
    $pos = $pl['pos'] ?? '';
    $photo = $stat['photo'] ?? null;
    $rating = $stat['rating'] ?? null;
    $posLabel = ['G' => 'Keeper', 'D' => 'Defender', 'M' => 'Midfielder', 'F' => 'Attacker'][$pos] ?? $pos;
    $rColor = $rating === null ? 'bg-zinc-600' : ((float) $rating >= 7 ? 'bg-green-600' : ((float) $rating >= 6 ? 'bg-amber-500' : 'bg-red-600'));
@endphp

<div class="flex flex-col items-center text-center py-3">
    <div class="relative mb-1">
        <div class="w-12 h-12 rounded-full bg-zinc-800 overflow-hidden border border-white/10">
            @if ($photo)<img src="{{ $photo }}" alt="{{ $name }}" class="w-full h-full object-cover" />@endif
        </div>
        @if ($minute !== null)
            <span class="absolute -top-1 -left-2 text-[9px] font-bold text-green-500 bg-zinc-900 rounded-full px-1 py-px border border-green-600/40">↑{{ $minute }}'</span>
        @endif
        @if ($rating !== null)
            <span class="absolute -top-1.5 -right-2 text-[9px] font-bold text-white px-1 py-px rounded {{ $rColor }}">{{ $rating }}</span>
        @endif
    </div>
    <div class="text-sm text-zinc-100 leading-tight">{{ $num }} {{ $name }}</div>
    <div class="text-[10px] text-zinc-500">{{ $posLabel }}</div>
</div>