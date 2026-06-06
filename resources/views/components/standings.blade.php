@php
    $codes = ['PL', 'LL', 'SA', 'BL', 'L1', 'MLS', 'ERE', 'POR', 'SPL', 'BRA', 'MX', 'IDN'];
    $leagues = \App\Models\League::whereIn('code', $codes)->orderBy('id')->get()
        ->map(function ($lg) {
            $rows = \Illuminate\Support\Facades\Cache::remember("standings.{$lg->code}", 600, fn () => $lg->standings());
            return ['league' => $lg, 'rows' => collect($rows)->take(12)];
        })
        ->filter(fn ($x) => $x['rows']->isNotEmpty())
        ->values();
@endphp

@if ($leagues->isNotEmpty())
<div x-data="{ idx: 0, total: {{ $leagues->count() }} }">
    @foreach ($leagues as $i => $item)
        <div x-show="idx === {{ $i }}" {{ $i === 0 ? '' : 'x-cloak' }}>

            {{-- Header: panah ngapit nama liga --}}
            <div class="flex items-center gap-2 mb-3">
                <button type="button" @click="idx = (idx - 1 + total) % total" class="w-7 h-7 rounded-full bg-zinc-800 hover:bg-zinc-700 flex items-center justify-center text-zinc-300 shrink-0 transition active:scale-90">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                </button>
                <a href="/league/{{ $item['league']->id }}" class="flex-1 flex items-center justify-center gap-2 min-w-0 group">
                    @if ($item['league']->logo_url)
                        <span class="w-5 h-5 rounded-full bg-white flex items-center justify-center shrink-0 p-0.5">
                            <img src="{{ $item['league']->logo_url }}" alt="" class="w-full h-full object-contain" />
                        </span>
                    @endif
                    <span class="text-sm font-bold truncate group-hover:text-blue-400 transition">{{ $item['league']->name }}</span>
                </a>
                <button type="button" @click="idx = (idx + 1) % total" class="w-7 h-7 rounded-full bg-zinc-800 hover:bg-zinc-700 flex items-center justify-center text-zinc-300 shrink-0 transition active:scale-90">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                </button>
            </div>

            {{-- Header kolom --}}
            <div class="flex items-center text-[10px] uppercase tracking-wide text-zinc-500 px-2 mb-1">
                <span class="w-5">#</span>
                <span class="flex-1">Team</span>
                <span class="w-6 text-center">P</span>
                <span class="w-8 text-center">GD</span>
                <span class="w-7 text-center">Pts</span>
            </div>

            {{-- Baris --}}
            <div>
                @foreach ($item['rows'] as $j => $row)
                    <div class="flex items-center text-sm rounded-md px-2 py-1.5 hover:bg-zinc-800/50 transition">
                        <span class="w-5 font-semibold {{ $j < 4 ? 'text-blue-500' : 'text-zinc-500' }}">{{ $j + 1 }}</span>
                        <a href="/team/{{ $row['team']->id }}" class="flex-1 flex items-center gap-2 min-w-0 group">
                            <x-team-badge :team="$row['team']->name" :logo="$row['team']->logo_url" size="sm" />
                            <span class="text-zinc-200 truncate group-hover:text-white transition">{{ $row['team']->name }}</span>
                        </a>
                        <span class="w-6 text-center text-zinc-400 tabular-nums">{{ $row['played'] }}</span>
                        <span class="w-8 text-center text-zinc-400 tabular-nums">{{ ($row['gf'] - $row['ga']) > 0 ? '+' : '' }}{{ $row['gf'] - $row['ga'] }}</span>
                        <span class="w-7 text-center font-bold tabular-nums">{{ $row['points'] }}</span>
                    </div>
                @endforeach
            </div>

            <a href="/league/{{ $item['league']->id }}" class="block text-center text-xs font-medium text-blue-400 hover:text-blue-300 mt-3 transition">Full table →</a>
        </div>
    @endforeach
</div>
@endif