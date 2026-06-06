@props(['lineups', 'pstats' => [], 'subbedIn' => [], 'pevents' => []])

@php
    $home = $lineups[0] ?? null;
    $away = $lineups[1] ?? null;

    $rowsOf = function ($team, $desc) {
        $g = collect($team['startXI'] ?? [])
            ->groupBy(fn ($p) => (int) explode(':', $p['player']['grid'] ?? '1:1')[0]);
        return $desc ? $g->sortKeysDesc() : $g->sortKeys();
    };
    $colSort = fn ($players) => $players->sortBy(fn ($p) => (int) explode(':', $p['player']['grid'] ?? '1:1')[1]);
    $hasGrid = function ($team) {
        $x = collect($team['startXI'] ?? []);
        return $x->isNotEmpty() && $x->every(fn ($p) => ! empty($p['player']['grid']));
    };
    $teamRating = function ($team) use ($pstats) {
        $rs = collect($team['startXI'] ?? [])
            ->map(fn ($p) => $pstats[$p['player']['id']]['rating'] ?? null)
            ->filter()->map(fn ($r) => (float) $r);
        return $rs->count() ? round($rs->avg(), 1) : null;
    };
    $rColor = fn ($r) => $r === null ? 'bg-zinc-600' : ((float) $r >= 7 ? 'bg-green-600' : ((float) $r >= 6 ? 'bg-amber-500' : 'bg-red-600'));
    $subsOf = function ($team) use ($subbedIn) {
        $subs = collect($team['substitutes'] ?? []);
        return [
            'on'    => $subs->filter(fn ($p) => isset($subbedIn[$p['player']['id'] ?? 0])),
            'bench' => $subs->reject(fn ($p) => isset($subbedIn[$p['player']['id'] ?? 0])),
        ];
    };
@endphp

@if ($home && $away && $hasGrid($home) && $hasGrid($away))
    @php $ar = $teamRating($away); $hr = $teamRating($home); @endphp

    {{-- ========== MOBILE: lapangan VERTIKAL ========== --}}
    <div class="lg:hidden">
        <div class="flex items-center gap-2 mb-1">
            @if ($ar !== null)<span class="text-xs font-bold text-white px-1.5 py-0.5 rounded {{ $rColor($ar) }}">{{ $ar }}</span>@endif
            <span class="w-5 h-5 rounded-full bg-white flex items-center justify-center shrink-0 p-0.5"><img src="{{ $away['team']['logo'] ?? '' }}" alt="" class="w-full h-full object-contain" /></span>
            <span class="text-sm font-semibold">{{ $away['team']['name'] ?? '' }}</span>
            <span class="text-xs text-zinc-500 ml-auto">{{ $away['formation'] ?? '' }}</span>
        </div>
        <div class="relative rounded-lg bg-gradient-to-b from-green-700/30 via-green-800/30 to-green-700/30 border border-green-900/40 py-6 overflow-hidden">
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute left-0 right-0 top-1/2 border-t border-white/15"></div>
                <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-20 h-20 rounded-full border border-white/15"></div>
                <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-1.5 h-1.5 rounded-full bg-white/25"></div>
                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-44 h-14 border border-t-0 border-white/15"></div>
                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-24 h-6 border border-t-0 border-white/15"></div>
                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-12 h-1.5 bg-white/10 border border-white/25"></div>
                <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-44 h-14 border border-b-0 border-white/15"></div>
                <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-24 h-6 border border-b-0 border-white/15"></div>
                <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-12 h-1.5 bg-white/10 border border-white/25"></div>
            </div>
            <div class="relative z-10 space-y-5">
                @foreach ($rowsOf($away, false) as $rowPlayers)
                    <div class="flex justify-around px-1">@foreach ($colSort($rowPlayers) as $p)<x-lineup-player :player="$p" :stat="$pstats[$p['player']['id']] ?? []" :events="$pevents[$p['player']['id']] ?? []" />@endforeach</div>
                @endforeach
                <div class="h-2"></div>
                @foreach ($rowsOf($home, true) as $rowPlayers)
                    <div class="flex justify-around px-1">@foreach ($colSort($rowPlayers) as $p)<x-lineup-player :player="$p" :stat="$pstats[$p['player']['id']] ?? []" :events="$pevents[$p['player']['id']] ?? []" />@endforeach</div>
                @endforeach
            </div>
        </div>
        <div class="flex items-center gap-2 mt-1">
            @if ($hr !== null)<span class="text-xs font-bold text-white px-1.5 py-0.5 rounded {{ $rColor($hr) }}">{{ $hr }}</span>@endif
            <span class="w-5 h-5 rounded-full bg-white flex items-center justify-center shrink-0 p-0.5"><img src="{{ $home['team']['logo'] ?? '' }}" alt="" class="w-full h-full object-contain" /></span>
            <span class="text-sm font-semibold">{{ $home['team']['name'] ?? '' }}</span>
            <span class="text-xs text-zinc-500 ml-auto">{{ $home['formation'] ?? '' }}</span>
        </div>
    </div>

    {{-- ========== DESKTOP: lapangan HORIZONTAL ========== --}}
    <div class="hidden lg:block">
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-2">
                @if ($hr !== null)<span class="text-xs font-bold text-white px-1.5 py-0.5 rounded {{ $rColor($hr) }}">{{ $hr }}</span>@endif
                <span class="w-5 h-5 rounded-full bg-white flex items-center justify-center shrink-0 p-0.5"><img src="{{ $home['team']['logo'] ?? '' }}" alt="" class="w-full h-full object-contain" /></span>
                <span class="text-sm font-semibold">{{ $home['team']['name'] ?? '' }}</span>
                <span class="text-xs text-zinc-500">{{ $home['formation'] ?? '' }}</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-zinc-500">{{ $away['formation'] ?? '' }}</span>
                <span class="text-sm font-semibold">{{ $away['team']['name'] ?? '' }}</span>
                <span class="w-5 h-5 rounded-full bg-white flex items-center justify-center shrink-0 p-0.5"><img src="{{ $away['team']['logo'] ?? '' }}" alt="" class="w-full h-full object-contain" /></span>
                @if ($ar !== null)<span class="text-xs font-bold text-white px-1.5 py-0.5 rounded {{ $rColor($ar) }}">{{ $ar }}</span>@endif
            </div>
        </div>
        <div class="relative rounded-lg bg-gradient-to-r from-green-700/30 via-green-800/30 to-green-700/30 border border-green-900/40 h-[26rem] overflow-hidden">
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute top-0 bottom-0 left-1/2 border-l border-white/15"></div>
                <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-24 h-24 rounded-full border border-white/15"></div>
                <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-1.5 h-1.5 rounded-full bg-white/25"></div>
                <div class="absolute left-0 top-1/2 -translate-y-1/2 h-48 w-16 border border-l-0 border-white/15"></div>
                <div class="absolute left-0 top-1/2 -translate-y-1/2 h-24 w-7 border border-l-0 border-white/15"></div>
                <div class="absolute left-0 top-1/2 -translate-y-1/2 h-14 w-1.5 bg-white/10 border border-white/25"></div>
                <div class="absolute right-0 top-1/2 -translate-y-1/2 h-48 w-16 border border-r-0 border-white/15"></div>
                <div class="absolute right-0 top-1/2 -translate-y-1/2 h-24 w-7 border border-r-0 border-white/15"></div>
                <div class="absolute right-0 top-1/2 -translate-y-1/2 h-14 w-1.5 bg-white/10 border border-white/25"></div>
            </div>
            <div class="relative z-10 flex items-stretch w-full h-full px-2">
                @foreach ($rowsOf($home, false) as $rowPlayers)
                    <div class="flex flex-col justify-around items-center flex-1">@foreach ($colSort($rowPlayers) as $p)<x-lineup-player :player="$p" :stat="$pstats[$p['player']['id']] ?? []" :events="$pevents[$p['player']['id']] ?? []" />@endforeach</div>
                @endforeach
                @foreach ($rowsOf($away, true) as $rowPlayers)
                    <div class="flex flex-col justify-around items-center flex-1">@foreach ($colSort($rowPlayers) as $p)<x-lineup-player :player="$p" :stat="$pstats[$p['player']['id']] ?? []" :events="$pevents[$p['player']['id']] ?? []" />@endforeach</div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ========== Coach / Subs / Bench (sama) ========== --}}
    <h3 class="text-center text-xs font-semibold uppercase tracking-wide text-zinc-500 mt-6 mb-2">Coach</h3>
    <div class="grid grid-cols-2 gap-x-6">
        @foreach ([$home, $away] as $t)
            <div class="flex items-center gap-2">
                <div class="w-9 h-9 rounded-full bg-zinc-800 overflow-hidden shrink-0">
                    @if (! empty($t['coach']['photo']))<img src="{{ $t['coach']['photo'] }}" alt="" class="w-full h-full object-cover" />@endif
                </div>
                <div class="min-w-0">
                    <div class="text-sm text-zinc-100 truncate">{{ $t['coach']['name'] ?? '' }}</div>
                    <div class="text-[10px] text-zinc-500">Coach</div>
                </div>
            </div>
        @endforeach
    </div>

    <h3 class="text-center text-xs font-semibold uppercase tracking-wide text-zinc-500 mt-6 mb-2">Substitutes</h3>
    <div class="grid grid-cols-2 gap-x-2">
        @foreach ([$home, $away] as $t)
            <div>
                @foreach ($subsOf($t)['on'] as $p)
                    <x-lineup-sub :player="$p" :stat="$pstats[$p['player']['id']] ?? []" :minute="$subbedIn[$p['player']['id']] ?? null" />
                @endforeach
            </div>
        @endforeach
    </div>

    <h3 class="text-center text-xs font-semibold uppercase tracking-wide text-zinc-500 mt-6 mb-2">Bench</h3>
    <div class="grid grid-cols-2 gap-x-2">
        @foreach ([$home, $away] as $t)
            <div>
                @foreach ($subsOf($t)['bench'] as $p)
                    <x-lineup-sub :player="$p" :stat="$pstats[$p['player']['id']] ?? []" />
                @endforeach
            </div>
        @endforeach
    </div>
@else
    <div class="grid sm:grid-cols-2 gap-6">
        @foreach ($lineups as $team)
            <x-lineup-pitch :team="$team" />
        @endforeach
    </div>
@endif