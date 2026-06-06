@props(['team'])

@php
    $players = collect($team['startXI'] ?? []);
    // cek semua pemain punya 'grid' (posisi) — kalau nggak, pakai list biasa
    $hasGrid = $players->isNotEmpty() && $players->every(fn ($p) => ! empty($p['player']['grid']));
@endphp

<div>
    <div class="flex items-center justify-between mb-2">
        <span class="text-sm font-semibold">{{ $team['team']['name'] ?? '' }}</span>
        <span class="text-xs text-zinc-500">{{ $team['formation'] ?? '' }}</span>
    </div>

    @if ($hasGrid)
        {{-- Lapangan --}}
        <div class="rounded-lg bg-gradient-to-b from-green-700/30 to-green-900/30 border border-green-900/40 p-3 space-y-5">
            @php
                // kelompokin per baris (grid "row:col"), baris paling tinggi (penyerang) di atas
                $rows = $players
                    ->groupBy(fn ($p) => (int) explode(':', $p['player']['grid'])[0])
                    ->sortKeysDesc();
            @endphp
            @foreach ($rows as $rowPlayers)
                <div class="flex justify-around items-start">
                    @foreach ($rowPlayers->sortBy(fn ($p) => (int) explode(':', $p['player']['grid'])[1]) as $p)
                        <div class="flex flex-col items-center gap-1 w-14">
                            <span class="w-7 h-7 rounded-full bg-zinc-900/80 border border-white/40 flex items-center justify-center text-xs font-bold text-white">{{ $p['player']['number'] ?? '' }}</span>
                            <span class="text-[10px] text-white text-center leading-tight truncate w-full">{{ \Illuminate\Support\Str::afterLast($p['player']['name'] ?? '', ' ') }}</span>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    @else
        {{-- Fallback: list biasa kalau gak ada data grid --}}
        <div class="space-y-1">
            @foreach ($players as $p)
                <div class="flex items-center gap-2 text-sm">
                    <span class="w-6 text-center text-xs text-zinc-500">{{ $p['player']['number'] ?? '' }}</span>
                    <span class="text-zinc-200 flex-1 truncate">{{ $p['player']['name'] ?? '' }}</span>
                    <span class="text-[10px] text-zinc-600">{{ $p['player']['pos'] ?? '' }}</span>
                </div>
            @endforeach
        </div>
    @endif

    @if (! empty($team['coach']['name']))
        <p class="text-xs text-zinc-500 mt-2">Coach: {{ $team['coach']['name'] }}</p>
    @endif

    <p class="text-[10px] uppercase tracking-wide text-zinc-500 mb-1 mt-3">Substitutes</p>
    <div class="flex flex-wrap gap-x-3 gap-y-1">
        @foreach ($team['substitutes'] ?? [] as $p)
            <span class="text-xs text-zinc-400">{{ $p['player']['number'] ?? '' }} {{ $p['player']['name'] ?? '' }}</span>
        @endforeach
    </div>
</div>