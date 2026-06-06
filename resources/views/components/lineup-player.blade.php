@props(['player', 'stat' => [], 'events' => []])

@php
    $name = \Illuminate\Support\Str::afterLast($player['player']['name'] ?? '', ' ');
    $num = $player['player']['number'] ?? '';
    $photo = $stat['photo'] ?? null;
    $rating = $stat['rating'] ?? null;
    $captain = $stat['captain'] ?? false;
    $ratingColor = $rating === null
        ? 'bg-zinc-600'
        : ((float) $rating >= 7 ? 'bg-green-600' : ((float) $rating >= 6 ? 'bg-amber-500' : 'bg-red-600'));

    $goals = $events['goals'] ?? 0;
    $og = $events['og'] ?? false;
    $yellow = $events['yellow'] ?? false;
    $red = $events['red'] ?? false;
    $subOff = $events['subOff'] ?? false;

    $fullName = $stat['name'] ?? $name;
    $pos = $stat['position'] ?? ($player['player']['pos'] ?? '');
    $posLabel = ['G' => 'Goalkeeper', 'D' => 'Defender', 'M' => 'Midfielder', 'F' => 'Attacker'][$pos] ?? $pos;

    $fmt = fn ($v) => ($v === null || $v === '') ? '0' : $v;
    $statList = [];
    $featured = [
        'Minutes' => ($stat['minutes'] ?? null) !== null ? $stat['minutes'] . "'" : '–',
        'Goals'   => $fmt($stat['goals'] ?? null),
        'Assists' => $fmt($stat['assists'] ?? null),
    ];
    if (($stat['shots'] ?? null) !== null) $statList['Shots (on target)'] = $fmt($stat['shots']) . ' (' . $fmt($stat['shots_on'] ?? 0) . ')';
    if (($stat['passes'] ?? null) !== null) $statList['Passes'] = $fmt($stat['passes']) . ' (' . $fmt($stat['pass_acc'] ?? 0) . '%)';
    if (($stat['key_passes'] ?? null) !== null) $statList['Key passes'] = $fmt($stat['key_passes']);
    if (($stat['tackles'] ?? null) !== null) $statList['Tackles'] = $fmt($stat['tackles']);
    if (($stat['interceptions'] ?? null) !== null) $statList['Interceptions'] = $fmt($stat['interceptions']);
    if (($stat['duels'] ?? null) !== null) $statList['Duels won'] = $fmt($stat['duels_won'] ?? 0) . '/' . $fmt($stat['duels']);
    if (($stat['dribbles'] ?? null) !== null) $statList['Dribbles'] = $fmt($stat['dribbles_won'] ?? 0) . '/' . $fmt($stat['dribbles']);
    if (($stat['saves'] ?? null) !== null) $statList['Saves'] = $fmt($stat['saves']);
    if (($stat['fouls_committed'] ?? null) !== null || ($stat['fouls_drawn'] ?? null) !== null) $statList['Fouls (won/committed)'] = $fmt($stat['fouls_drawn'] ?? 0) . ' / ' . $fmt($stat['fouls_committed'] ?? 0);
@endphp

<div x-data="{ open: false }" class="flex flex-col items-center gap-1 w-16">
    <button type="button" @click="open = true" class="relative cursor-pointer">
        <div class="w-9 h-9 rounded-full bg-zinc-800 overflow-hidden border border-white/20">
            @if ($photo)
                <img src="{{ $photo }}" alt="{{ $name }}" class="w-full h-full object-cover" />
            @else
                <span class="w-full h-full flex items-center justify-center text-xs font-bold text-white">{{ $num }}</span>
            @endif
        </div>
        @if ($rating !== null)
            <span class="absolute -top-1.5 -right-2 text-[9px] font-bold text-white px-1 py-px rounded {{ $ratingColor }}">{{ $rating }}</span>
        @endif
        @if ($captain)
            <span class="absolute -bottom-1 -left-1.5 w-3.5 h-3.5 rounded-full bg-yellow-400 text-black text-[8px] font-bold flex items-center justify-center">C</span>
        @endif
        @if ($subOff)
            <span class="absolute -top-1 -left-1.5 w-3.5 h-3.5 rounded-full bg-zinc-900 border border-red-600/50 text-red-500 text-[10px] font-bold flex items-center justify-center leading-none">↓</span>
        @endif
        <div class="absolute -bottom-1 -right-1.5 flex items-center gap-0.5">
            @if ($yellow && ! $red)<span class="w-2 h-2.5 rounded-[1px] bg-yellow-400"></span>@endif
            @if ($red)<span class="w-2 h-2.5 rounded-[1px] bg-red-500"></span>@endif
            @if ($og)<span class="text-[7px] font-bold text-white bg-zinc-700 rounded px-0.5">OG</span>@endif
            @if ($goals > 0)
                <span class="relative w-4 h-4 rounded-full bg-white border border-zinc-300 flex items-center justify-center">
                    <svg class="w-3 h-3 text-zinc-800" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="9" />
                        <path d="M12 8.2l3.1 2.25-1.18 3.65h-3.84L8.9 10.45 12 8.2z" fill="currentColor" stroke="none" />
                    </svg>
                    @if ($goals > 1)<span class="absolute -top-1.5 -right-1.5 text-[8px] font-bold text-white bg-green-600 rounded-full px-0.5 leading-tight">{{ $goals }}</span>@endif
                </span>
            @endif
        </div>
    </button>

    <button type="button" @click="open = true" class="text-[10px] text-white text-center leading-tight truncate w-full cursor-pointer">{{ $num }} {{ $name }}</button>

        {{-- Modal stats pemain --}}
        <template x-teleport="body">
            <div x-show="open" x-cloak @click="open = false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-[70] bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
    
                <div @click.stop x-show="open"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-90 translate-y-4"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="relative bg-zinc-900 rounded-2xl w-full max-w-sm border border-zinc-800 shadow-2xl overflow-hidden text-left">
    
                    {{-- Header --}}
                    <div class="relative p-5 bg-gradient-to-br from-zinc-800 to-zinc-900">
                        <button type="button" @click="open = false" class="absolute top-3 right-3 w-7 h-7 rounded-full bg-zinc-800/80 hover:bg-zinc-700 flex items-center justify-center text-zinc-400 hover:text-white transition">✕</button>
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-full bg-zinc-800 overflow-hidden border-2 border-zinc-700 shrink-0">
                                @if ($photo)<img src="{{ $photo }}" alt="" class="w-full h-full object-cover" />@endif
                            </div>
                            <div class="min-w-0">
                                <div class="text-lg font-bold truncate">{{ $fullName }}</div>
                                <div class="text-sm text-zinc-400">#{{ $num }} · {{ $posLabel }}</div>
                                @if ($rating !== null)
                                    <span class="inline-block mt-1.5 text-xs font-bold text-white px-2 py-0.5 rounded {{ $ratingColor }}">Rating {{ $rating }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
    
                    {{-- Statistik utama --}}
                    <div class="grid grid-cols-3 divide-x divide-zinc-800 border-y border-zinc-800">
                        @foreach ($featured as $label => $value)
                            <div class="py-3 text-center">
                                <div class="text-lg font-bold">{{ $value }}</div>
                                <div class="text-[10px] uppercase tracking-wide text-zinc-500">{{ $label }}</div>
                            </div>
                        @endforeach
                    </div>
    
                    {{-- Detail --}}
                    <div class="p-4 grid grid-cols-2 gap-x-5 gap-y-2 max-h-56 overflow-y-auto">
                        @forelse ($statList as $label => $value)
                            <div class="flex items-center justify-between gap-2 text-sm border-b border-zinc-800/40 pb-1.5">
                                <span class="text-zinc-400 text-xs truncate">{{ $label }}</span>
                                <span class="font-semibold shrink-0">{{ $value }}</span>
                            </div>
                        @empty
                            <p class="col-span-2 text-center text-zinc-500 text-sm py-2">No detailed stats.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </template>
</div>