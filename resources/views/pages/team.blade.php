@extends('layouts.app')

@section('title', $team->name . ' - LiveScore')
@section('description', $team->name . ' fixtures, results, full squad, player stats and league standings. Follow ' . $team->name . ' live scores and match schedule on LiveScore.')
@section('schema')
<script type="application/ld+json">{!! json_encode(array_filter([
    '@context' => 'https://schema.org',
    '@type' => 'SportsTeam',
    'name' => $team->name,
    'sport' => 'Soccer',
    'logo' => $team->logo_url,
    'url' => url()->current(),
]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endsection
@section('og_image', $team->logo_url)
@section('no-left', 'yes')
@section('no-right', 'yes')

@section('content')
    @php
        $tabs = ['overview' => 'Overview'];
        if ($standings->isNotEmpty()) {
            $tabs['table'] = 'Table';
        }
        $tabs['fixtures'] = 'Fixtures';
        $tabs['squad'] = 'Squad';
        $tabs['playerstats'] = 'Player stats';
        $tabs['teamstats'] = 'Team stats';
        $tabs['transfers'] = 'Transfers';
        $tabs['history'] = 'History';
        $tabs['news'] = 'News';
    @endphp

    <div x-data="{ tab: 'overview' }">

        @php
        $btnActive = $darkText ? 'bg-zinc-900 text-white border-zinc-900' : 'bg-white text-zinc-900 border-white';
        $btnIdle = $darkText ? 'border-zinc-900/40 text-zinc-900 hover:bg-black/10' : 'border-white/40 text-white hover:bg-white/10';
    @endphp
    {{-- Header (warna ikut jersey tim) --}}
    <div class="relative overflow-hidden rounded-lg mb-4 {{ $teamColor ? '' : 'bg-zinc-900' }}" @if ($teamColor) style="background-color: #{{ $teamColor }};" @endif>
        @if ($teamColor)<div class="absolute inset-0 bg-gradient-to-br from-white/15 via-transparent to-black/30 pointer-events-none"></div>@endif
        <div class="relative p-4 sm:p-6 flex flex-wrap items-center gap-3 sm:gap-4">
            <span class="w-16 h-16 sm:w-20 sm:h-20 flex items-center justify-center shrink-0">
                @if ($team->logo_url)
                    <img src="{{ $team->logo_url }}" alt="{{ $team->name }}" class="w-full h-full object-contain" />
                @else
                    <span class="text-xl font-bold {{ $darkText ? 'text-zinc-700' : 'text-zinc-200' }}">{{ $team->short_name }}</span>
                @endif
            </span>
            <div class="min-w-0 flex-1">
                <h1 class="text-xl sm:text-3xl font-bold truncate {{ $darkText ? 'text-zinc-900' : 'text-white' }}">{{ $team->name }}</h1>
                <a href="/league/{{ $team->league->id }}" class="text-sm transition {{ $darkText ? 'text-zinc-800 hover:text-black' : 'text-white/80 hover:text-white' }}">{{ $country }}</a>
            </div>
            <div class="flex items-center gap-2 shrink-0 w-full sm:w-auto">
                {{-- About button --}}
                <button @click="tab = 'about'"
                        :class="tab === 'about' ? '{{ $btnActive }}' : '{{ $btnIdle }}'"
                        class="flex items-center gap-1.5 px-4 py-2 rounded-full border text-sm font-semibold transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 16v-4m0-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    About
                </button>
                {{-- Follow button --}}
                <button type="button"
                        x-data="{ t: { id: {{ $team->id }}, name: @js($team->name), logo: @js($team->logo_url) } }"
                        @click="$store.favs.toggle(t)"
                        :class="$store.favs.has({{ $team->id }}) ? '{{ $btnActive }}' : '{{ $btnIdle }}'"
                        class="flex items-center gap-1.5 px-4 py-2 rounded-full border text-sm font-semibold transition">
                    <svg class="w-4 h-4" :fill="$store.favs.has({{ $team->id }}) ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.5l2.6 5.27 5.82.85-4.2 4.1.99 5.78L11.48 17l-5.2 2.5.99-5.78-4.2-4.1 5.81-.85z" />
                    </svg>
                    <span x-text="$store.favs.has({{ $team->id }}) ? 'Following' : 'Follow'"></span>
                </button>
            </div>
        </div>
    </div>

    <div>
        {{-- Tab menu --}}
        <div class="flex gap-1 border-b border-zinc-800 overflow-x-auto overflow-y-hidden mb-4 [&::-webkit-scrollbar]:hidden">
            @foreach ($tabs as $key => $label)
                <button @click="tab = '{{ $key }}'"
                        :class="tab === '{{ $key }}' ? 'text-white border-blue-500' : 'text-zinc-400 border-transparent hover:text-white hover:bg-zinc-800/40'"
                        class="px-4 py-2.5 text-sm font-bold tracking-tight border-b-2 -mb-px whitespace-nowrap cursor-pointer rounded-t-lg transition-all">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- OVERVIEW --}}
        <div x-show="tab === 'overview'"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0">

            <div class="grid lg:grid-cols-3 gap-3 sm:gap-4 items-start">

                {{-- ===== KIRI (2 kolom): Team form + Next match, terus Table ===== --}}
                <div class="lg:col-span-2 space-y-3 sm:space-y-4">

                    <div class="grid sm:grid-cols-2 gap-3 sm:gap-4 items-start">
                        {{-- Team form --}}
                        <div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
                            <h2 class="text-sm font-semibold mb-4">Team form</h2>
                            @if ($formMatches->isNotEmpty())
                                <div class="flex gap-3 justify-between">
                                    @foreach ($formMatches as $m)
                                        <a href="/match/{{ $m['id'] }}" class="flex flex-col items-center gap-2 group flex-1 min-w-0">
                                            <span class="px-2.5 py-1.5 rounded-lg text-sm font-bold text-white shadow-md transition group-hover:scale-105 {{ $m['res'] === 'W' ? 'bg-green-600' : ($m['res'] === 'L' ? 'bg-red-600' : 'bg-zinc-600') }}">{{ $m['score'] }}</span>
                                            <x-team-badge :team="$m['opp']->name" :logo="$m['opp']->logo_url" size="xl" />
                                            <span class="text-[10px] text-zinc-500 group-hover:text-zinc-300 transition truncate max-w-full">{{ $m['opp']->short_name }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-zinc-500 text-sm">No recent matches.</p>
                            @endif
                        </div>

                        {{-- Next match --}}
                        <div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-sm font-semibold">Next match</h2>
                                @if ($nextMatch)
                                    <span class="flex items-center gap-1.5 text-xs text-zinc-400 min-w-0">
                                        @if ($nextMatch->league->logo_url)
                                            <span class="w-4 h-4 rounded-full bg-white flex items-center justify-center shrink-0 p-0.5">
                                                <img src="{{ $nextMatch->league->logo_url }}" alt="" class="w-full h-full object-contain" />
                                            </span>
                                        @endif
                                        <span class="truncate">{{ $nextMatch->league->name }}</span>
                                    </span>
                                @endif
                            </div>
                            @if ($nextMatch)
                                <a href="/match/{{ $nextMatch->id }}" class="flex items-center justify-between gap-2 hover:opacity-90 transition">
                                    <div class="flex flex-col items-center gap-2 flex-1 min-w-0">
                                        <x-team-badge :team="$nextMatch->homeTeam->name" :logo="$nextMatch->homeTeam->logo_url" size="lg" />
                                        <span class="text-xs text-center truncate w-full">{{ $nextMatch->homeTeam->short_name }}</span>
                                    </div>
                                    <div class="text-center shrink-0 px-2">
                                        <div class="text-xl font-bold leading-none">{{ $nextMatch->kickoff_at->format('H:i') }}</div>
                                        <div class="text-[11px] text-zinc-500 mt-1">{{ $nextMatch->kickoff_at->format('d M') }}</div>
                                    </div>
                                    <div class="flex flex-col items-center gap-2 flex-1 min-w-0">
                                        <x-team-badge :team="$nextMatch->awayTeam->name" :logo="$nextMatch->awayTeam->logo_url" size="lg" />
                                        <span class="text-xs text-center truncate w-full">{{ $nextMatch->awayTeam->short_name }}</span>
                                    </div>
                                </a>
                            @else
                                <div class="flex flex-col items-center justify-center py-6 text-zinc-500">
                                    <svg class="w-10 h-10 mb-2 text-zinc-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <p class="text-sm">No upcoming match</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Table --}}
                    @if ($standings->isNotEmpty())
                    <div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h2 class="text-sm font-semibold">{{ $team->league->name }}</h2>
                            <button type="button" @click="tab = 'table'" class="text-xs text-blue-400 hover:text-white transition">Full table</button>
                        </div>
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-[10px] text-zinc-500 border-b border-zinc-800">
                                    <th class="font-medium text-left pb-2 pl-2">#</th>
                                    <th class="font-medium text-left pb-2">Team</th>
                                    <th class="font-medium text-center pb-2 w-7">P</th>
                                    <th class="font-medium text-center pb-2 w-7 hidden sm:table-cell">W</th>
                                    <th class="font-medium text-center pb-2 w-7 hidden sm:table-cell">D</th>
                                    <th class="font-medium text-center pb-2 w-7 hidden sm:table-cell">L</th>
                                    <th class="font-medium text-center pb-2 w-9">GD</th>
                                    <th class="font-medium text-center pb-2 w-9 pr-2">Pts</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($standings as $i => $row)
                                    @php
                                        $me = $row['team']->id === $team->id;
                                        $pos = $i + 1;
                                        $zone = $pos <= 4 ? 'bg-blue-500' : ($pos >= $standings->count() - 2 ? 'bg-red-500' : 'bg-transparent');
                                    @endphp
                                    <tr class="group {{ $me ? 'bg-blue-500/10' : '' }} hover:bg-zinc-800/60 transition">
                                        <td class="py-2 pl-2">
                                            <div class="flex items-center gap-2">
                                                <span class="w-0.5 h-5 rounded-full {{ $zone }}"></span>
                                                <span class="text-zinc-400">{{ $pos }}</span>
                                            </div>
                                        </td>
                                        <td class="py-2">
                                            <a href="/team/{{ $row['team']->id }}" class="flex items-center gap-2 min-w-0">
                                                <x-team-badge :team="$row['team']->name" :logo="$row['team']->logo_url" size="sm" />
                                                <span class="truncate {{ $me ? 'font-semibold text-white' : 'text-zinc-200' }} group-hover:text-white transition">{{ $row['team']->name }}</span>
                                            </a>
                                        </td>
                                        <td class="text-center text-zinc-400">{{ $row['played'] }}</td>
                                        <td class="text-center text-zinc-400 hidden sm:table-cell">{{ $row['won'] }}</td>
                                        <td class="text-center text-zinc-400 hidden sm:table-cell">{{ $row['drawn'] }}</td>
                                        <td class="text-center text-zinc-400 hidden sm:table-cell">{{ $row['lost'] }}</td>
                                        <td class="text-center text-zinc-400">{{ ($row['gf'] - $row['ga']) > 0 ? '+' : '' }}{{ $row['gf'] - $row['ga'] }}</td>
                                        <td class="text-center font-bold pr-2">{{ $row['points'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                            <div class="flex items-center gap-4 mt-3 text-[10px] text-zinc-500">
                                <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-blue-500"></span> Champions League</span>
                                <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-red-500"></span> Relegation</span>
                            </div>
                        </div>
                    @endif
                    {{-- Top players --}}
                    @if ($topRated->isNotEmpty() || $topScorers->isNotEmpty() || $topAssists->isNotEmpty())
                        <div class="grid sm:grid-cols-3 gap-3 sm:gap-4">
                            <x-top-players title="Top rated" :rows="$topRated" valueKey="rating" :team="$team" />
                            <x-top-players title="Top scorers" :rows="$topScorers" valueKey="goals" :team="$team" />
                            <x-top-players title="Top assists" :rows="$topAssists" valueKey="assists" :team="$team" />
                        </div>
                    @endif
                    {{-- Coach win percentage --}}
                    @if (! empty($seasonStats))
                    <div class="bg-zinc-900 rounded-lg p-3 sm:p-4 mt-4">
                        <h2 class="text-sm font-semibold">Coach win percentage</h2>
                        <p class="text-xs text-zinc-500 mb-6">Points per game</p>
                        @php $maxP = max(1, collect($seasonStats)->max('winpct')); @endphp
                        <div class="flex items-end gap-4 h-44 px-2">
                            @foreach ($seasonStats as $s)
                                <div x-data="{ show: false }"
                                    @mouseenter="show = true" @mouseleave="show = false"
                                    @click="show = !show" @click.outside="show = false"
                                    class="relative flex-1 flex flex-col items-center justify-end h-full cursor-pointer select-none">
                                    <div x-show="show" x-cloak
                                        x-transition:enter="transition ease-out duration-150"
                                        x-transition:enter-start="opacity-0 translate-y-1"
                                        x-transition:enter-end="opacity-100 translate-y-0"
                                        class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 z-20 w-44 bg-zinc-950 border border-zinc-700 rounded-lg p-3 shadow-xl text-left pointer-events-none">
                                        <div class="text-xs font-semibold mb-1.5 truncate">{{ $s['coach'] ?? '—' }} · {{ $s['label'] }}</div>
                                        <div class="flex justify-between text-[11px] text-zinc-400 py-0.5"><span>Win rate</span><span class="text-white font-semibold">{{ $s['winpct'] }}%</span></div>
                                        <div class="flex justify-between text-[11px] text-zinc-400 py-0.5"><span>Points/game</span><span class="text-white font-semibold">{{ $s['ppg'] }}</span></div>
                                        <div class="flex justify-between text-[11px] text-zinc-400 py-0.5"><span>Record</span><span class="text-white font-semibold">{{ $s['w'] }}W {{ $s['d'] }}D {{ $s['l'] }}L</span></div>
                                    </div>
                                    <span class="text-[10px] font-bold text-white bg-blue-600 px-1.5 py-0.5 rounded">{{ $s['winpct'] }}%</span>
                                    <span class="text-[10px] text-zinc-400 mt-0.5 mb-1">{{ $s['ppg'] }} Pts</span>
                                    <div class="w-full max-w-[3.5rem] rounded-t bg-gradient-to-t transition-all"
                                        :class="show ? 'from-blue-600 to-blue-400' : 'from-blue-700 to-blue-500'"
                                        style="height: {{ round($s['winpct'] / $maxP * 100) }}%"></div>
                                </div>
                            @endforeach
                        </div>
                        <div class="flex gap-4 border-t border-zinc-800 pt-3 mt-1 px-2">
                            @foreach ($seasonStats as $s)
                                    <div class="flex-1 flex flex-col items-center gap-1 min-w-0">
                                        <div class="w-9 h-9 rounded-full bg-zinc-800 overflow-hidden shrink-0">
                                            @if ($s['coachPhoto'])<img src="{{ $s['coachPhoto'] }}" alt="{{ $s['coach'] }}" class="w-full h-full object-cover" />@endif
                                        </div>
                                        <div class="text-[11px] text-zinc-200 text-center truncate w-full">{{ $s['coach'] ?? '—' }}</div>
                                        <div class="text-[10px] text-zinc-500">{{ $s['label'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    {{-- News preview --}}
                    <div class="mt-4">
                        <div class="flex items-center justify-between mb-3">
                            <h2 class="text-sm font-semibold">Latest news</h2>
                            <button @click="tab = 'news'"
                                    class="text-xs text-blue-400 hover:text-blue-300 font-medium">
                                See all
                            </button>
                        </div>
                        <x-news-list :team="$team" :limit="3" compact />
                    </div>
                </div>

                {{-- ===== KANAN (1 kolom): Last starting XI + Fixtures ===== --}}
                <div class="space-y-3 sm:space-y-4">
                    <div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-sm font-semibold">Last starting XI</h2>
                            @if ($lastFixture && $lastFixture->league)
                                <a href="/league/{{ $lastFixture->league->id }}" class="flex items-center gap-1.5 text-xs text-zinc-400 hover:text-white transition min-w-0">
                                    @if ($lastFixture->league->logo_url)
                                        <span class="w-4 h-4 rounded-full bg-white flex items-center justify-center shrink-0 p-0.5">
                                            <img src="{{ $lastFixture->league->logo_url }}" alt="" class="w-full h-full object-contain" />
                                        </span>
                                    @endif
                                    <span class="truncate">{{ $lastFixture->league->name }}</span>
                                </a>
                            @endif
                        </div>
                        @if ($lastXI && ! empty($lastXI['startXI']) && collect($lastXI['startXI'])->every(fn ($p) => ! empty($p['player']['grid'])))
                            @php
                                $xiRows = collect($lastXI['startXI'])->groupBy(fn ($p) => (int) explode(':', $p['player']['grid'])[0])->sortKeysDesc();
                                $xiCol = fn ($pl) => $pl->sortBy(fn ($p) => (int) explode(':', $p['player']['grid'])[1]);
                            @endphp
                            <div class="rounded-lg bg-gradient-to-b from-green-700/25 to-green-800/25 border border-green-900/40 py-4 space-y-4">
                                @foreach ($xiRows as $rowPlayers)
                                    <div class="flex justify-around px-1">
                                        @foreach ($xiCol($rowPlayers) as $p)
                                            <x-lineup-player :player="$p" :stat="$xiStats[$p['player']['id']] ?? []" />
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-zinc-500 text-sm">No lineup data available.</p>
                        @endif
                    </div>

                    {{-- Fixtures preview --}}
                    <div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h2 class="text-sm font-semibold">Fixtures</h2>
                            <button type="button" @click="tab = 'fixtures'" class="flex items-center gap-0.5 text-xs text-blue-400 hover:text-white transition">
                                See all
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                            </button>
                        </div>
                        @php $fxPrev = $upcoming->isNotEmpty() ? $upcoming->take(3) : $recent->take(3); @endphp
                        <div class="-mx-4 divide-y divide-zinc-800">
                            @foreach ($fxPrev as $fixture)
                                <x-match-card :fixture="$fixture" />
                            @endforeach
                        </div>
                    </div>
                    {{-- Stadium --}}
                    @if ($venue && ! empty($venue['name']))
                    <div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
                        <h2 class="text-sm font-semibold mb-3">Stadium</h2>
                        <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($venue['name'] . ' ' . ($venue['city'] ?? '')) }}"
                        target="_blank" rel="noopener" class="flex items-center gap-3 group">
                        @if (! empty($venue['image']))
                            <span class="w-12 h-12 rounded-xl bg-zinc-800 flex items-center justify-center shrink-0 p-2.5">
                                <img src="{{ $venue['image'] }}" alt="{{ $venue['name'] }}" class="w-full h-full object-contain" />
                            </span>
                        @endif
                        <div class="min-w-0">
                            <div class="font-semibold truncate group-hover:text-blue-400 transition flex items-center gap-1">
                                {{ $venue['name'] }}
                                <svg class="w-3 h-3 text-zinc-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                            </div>
                            <div class="text-xs text-zinc-500">{{ $venue['city'] ?? '' }}</div>
                        </div>
                    </a>
                            <div class="grid grid-cols-2 divide-x divide-zinc-800 text-center border-t border-zinc-800 pt-4 mt-4">
                                @if (! empty($venue['capacity']))
                                    <div>
                                        <div class="font-bold text-base">{{ number_format($venue['capacity']) }}</div>
                                        <div class="text-[10px] text-zinc-500 mt-0.5">Capacity</div>
                                    </div>
                                @endif
                                @if (! empty($venue['surface']))
                                    <div>
                                        <div class="font-bold text-base capitalize">{{ $venue['surface'] }}</div>
                                        <div class="text-[10px] text-zinc-500 mt-0.5">Surface</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

                {{-- TABLE --}}
                @if ($standings->isNotEmpty())
                <div x-show="tab === 'table'" x-cloak
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
                        <div class="flex items-center gap-2 mb-4">
                            @if ($team->league->logo_url)
                                <span class="w-6 h-6 rounded-full bg-white flex items-center justify-center shrink-0 p-0.5">
                                    <img src="{{ $team->league->logo_url }}" alt="" class="w-full h-full object-contain" />
                                </span>
                            @endif
                            <h2 class="text-base font-bold">{{ $team->league->name }}</h2>
                        </div>
                        <div class="overflow-x-auto [&::-webkit-scrollbar]:hidden">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-[10px] uppercase tracking-wide text-zinc-500 border-b border-zinc-800">
                                        <th class="font-medium text-left pb-2.5 pl-2 w-9">#</th>
                                        <th class="font-medium text-left pb-2.5">Team</th>
                                        <th class="font-medium text-center pb-2.5 w-8">P</th>
                                        <th class="font-medium text-center pb-2.5 w-8 hidden sm:table-cell">W</th>
                                        <th class="font-medium text-center pb-2.5 w-8 hidden sm:table-cell">D</th>
                                        <th class="font-medium text-center pb-2.5 w-8 hidden sm:table-cell">L</th>
                                        <th class="font-medium text-center pb-2.5 w-10 hidden md:table-cell">GF</th>
                                        <th class="font-medium text-center pb-2.5 w-10 hidden md:table-cell">GA</th>
                                        <th class="font-medium text-center pb-2.5 w-10">GD</th>
                                        <th class="font-medium text-center pb-2.5 w-11 pr-2">Pts</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-800/60">
                                    @foreach ($standings as $i => $row)
                                        @php
                                            $me = $row['team']->id === $team->id;
                                            $pos = $i + 1;
                                            $gd = $row['gf'] - $row['ga'];
                                            $zone = $pos <= 4 ? 'bg-blue-500' : ($pos >= $standings->count() - 2 ? 'bg-red-500' : 'bg-transparent');
                                        @endphp
                                        <tr class="group {{ $me ? 'bg-blue-500/10' : '' }} hover:bg-zinc-800/60 transition">
                                            <td class="py-3 pl-2">
                                                <div class="flex items-center gap-2">
                                                    <span class="w-1 h-6 rounded-full {{ $zone }}"></span>
                                                    <span class="tabular-nums {{ $me ? 'text-white font-semibold' : 'text-zinc-400' }}">{{ $pos }}</span>
                                                </div>
                                            </td>
                                            <td class="py-3">
                                                <a href="/team/{{ $row['team']->id }}" class="flex items-center gap-2.5 min-w-0">
                                                    <x-team-badge :team="$row['team']->name" :logo="$row['team']->logo_url" size="sm" />
                                                    <span class="truncate {{ $me ? 'font-semibold text-white' : 'text-zinc-200' }} group-hover:text-white transition">
                                                        <span class="sm:hidden">{{ $row['team']->short_name }}</span>
                                                        <span class="hidden sm:inline">{{ $row['team']->name }}</span>
                                                    </span>
                                                </a>
                                            </td>
                                            <td class="text-center text-zinc-400 tabular-nums">{{ $row['played'] }}</td>
                                            <td class="text-center text-zinc-400 tabular-nums hidden sm:table-cell">{{ $row['won'] }}</td>
                                            <td class="text-center text-zinc-400 tabular-nums hidden sm:table-cell">{{ $row['drawn'] }}</td>
                                            <td class="text-center text-zinc-400 tabular-nums hidden sm:table-cell">{{ $row['lost'] }}</td>
                                            <td class="text-center text-zinc-400 tabular-nums hidden md:table-cell">{{ $row['gf'] }}</td>
                                            <td class="text-center text-zinc-400 tabular-nums hidden md:table-cell">{{ $row['ga'] }}</td>
                                            <td class="text-center tabular-nums {{ $gd > 0 ? 'text-green-400' : ($gd < 0 ? 'text-red-400' : 'text-zinc-400') }}">{{ $gd > 0 ? '+' : '' }}{{ $gd }}</td>
                                            <td class="text-center pr-2">
                                                <span class="inline-block min-w-[1.75rem] px-1.5 py-0.5 rounded font-bold tabular-nums {{ $me ? 'bg-blue-600 text-white' : 'text-white' }}">{{ $row['points'] }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="flex items-center gap-4 mt-4 text-[10px] text-zinc-500">
                            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-blue-500"></span> Champions League</span>
                            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-red-500"></span> Relegation</span>
                        </div>
                    </div>
                </div>
                @endif

                        {{-- FIXTURES --}}
        <div x-show="tab === 'fixtures'" x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="space-y-3 sm:space-y-4">

            {{-- Upcoming --}}
            @if ($upcoming->isNotEmpty())
            <div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
                <h2 class="text-sm font-semibold mb-3">Upcoming</h2>
                <div class="-mx-3 sm:-mx-4 divide-y divide-zinc-800">
                    @foreach ($upcoming as $fixture)
                        <x-team-fixture-row :fixture="$fixture" :team="$team" />
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Results --}}
            @if ($recent->isNotEmpty())
            <div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
                <h2 class="text-sm font-semibold mb-3">Results</h2>
                <div class="-mx-3 sm:-mx-4 divide-y divide-zinc-800">
                    @foreach ($recent as $fixture)
                        <x-team-fixture-row :fixture="$fixture" :team="$team" />
                    @endforeach
                </div>
            </div>
            @endif

            @if ($upcoming->isEmpty() && $recent->isEmpty())
            <div class="bg-zinc-900 rounded-lg p-10 text-center text-zinc-500">No fixtures available.</div>
            @endif
        </div>

                {{-- SQUAD --}}
                <div x-show="tab === 'squad'" x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="space-y-3 sm:space-y-4">

            {{-- Coach --}}
            @if ($coach)
            <div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
                <h2 class="text-[10px] uppercase tracking-wide text-zinc-500 mb-3">Coach</h2>
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-full bg-zinc-800 overflow-hidden shrink-0">
                        @if (!empty($coach['photo']))<img src="{{ $coach['photo'] }}" alt="{{ $coach['name'] }}" class="w-full h-full object-cover" />@endif
                    </span>
                    <div class="min-w-0">
                        <div class="font-semibold truncate">{{ $coach['name'] }}</div>
                        <div class="text-xs text-zinc-500">{{ $coach['nationality'] ?? '' }}</div>
                    </div>
                </div>
            </div>
            @endif

            @forelse ($squadGroups as $group => $players)
            <div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
                <h2 class="text-sm font-semibold mb-2">{{ $group }}</h2>
                <div class="overflow-x-auto [&::-webkit-scrollbar]:hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-[10px] uppercase tracking-wide text-zinc-500 border-b border-zinc-800">
                                <th class="font-medium text-left pb-2">Player</th>
                                <th class="font-medium text-center pb-2 w-12">Shirt</th>
                                <th class="font-medium text-left pb-2 hidden sm:table-cell">Country</th>
                                <th class="font-medium text-center pb-2 w-12">Age</th>
                                <th class="font-medium text-center pb-2 w-16 hidden md:table-cell">Height</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-800/60">
                            @foreach ($players as $p)
                            <tr class="group hover:bg-zinc-800/60 transition">
                                <td class="py-2.5">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span class="w-9 h-9 rounded-full bg-zinc-800 overflow-hidden shrink-0">
                                            @if ($p['photo'])<img src="{{ $p['photo'] }}" alt="{{ $p['name'] }}" class="w-full h-full object-cover" />@endif
                                        </span>
                                        <span class="truncate font-medium text-zinc-100 group-hover:text-white transition">{{ $p['name'] }}</span>
                                    </div>
                                </td>
                                <td class="text-center text-zinc-400 tabular-nums">{{ $p['number'] ?? '—' }}</td>
                                <td class="text-left text-zinc-300 hidden sm:table-cell">{{ $p['nationality'] ?? '—' }}</td>
                                <td class="text-center text-zinc-400 tabular-nums">{{ $p['age'] ?? '—' }}</td>
                                <td class="text-center text-zinc-400 tabular-nums hidden md:table-cell">{{ $p['height'] ? $p['height'] . ' cm' : '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @empty
            <div class="bg-zinc-900 rounded-lg p-10 text-center text-zinc-500">No squad data available.</div>
            @endforelse
        </div>

        {{-- PLAYER STATS --}}
        <div x-show="tab === 'playerstats'" x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0">
            @if (count($playerLeaders))
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                    @foreach ($playerLeaders as $card)
                        <x-top-players :title="$card['title']" :rows="$card['rows']" :valueKey="$card['key']" :team="$team" />
                    @endforeach
                </div>
            @else
                <div class="bg-zinc-900 rounded-lg p-10 text-center text-zinc-500">No player stats available.</div>
            @endif
        </div>

                {{-- TEAM STATS --}}
                <div x-show="tab === 'teamstats'" x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0">
            @if ($teamStats)
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 items-start">

                    {{-- Matches --}}
                    <div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
                        <h2 class="text-sm font-semibold mb-3">Matches</h2>
                        @php $tot = max(1, $teamStats['played']); @endphp
                        <div class="flex h-2 rounded-full overflow-hidden mb-3 bg-zinc-800">
                            <div class="bg-green-600" style="width: {{ $teamStats['wins'] / $tot * 100 }}%"></div>
                            <div class="bg-zinc-500" style="width: {{ $teamStats['draws'] / $tot * 100 }}%"></div>
                            <div class="bg-red-600" style="width: {{ $teamStats['loses'] / $tot * 100 }}%"></div>
                        </div>
                        <div class="flex justify-between py-1.5 text-sm border-b border-zinc-800/60"><span class="text-zinc-400">Played</span><span class="font-semibold tabular-nums">{{ $teamStats['played'] }}</span></div>
                        <div class="flex justify-between py-1.5 text-sm border-b border-zinc-800/60"><span class="text-zinc-400">Wins</span><span class="font-semibold tabular-nums text-green-400">{{ $teamStats['wins'] }}</span></div>
                        <div class="flex justify-between py-1.5 text-sm border-b border-zinc-800/60"><span class="text-zinc-400">Draws</span><span class="font-semibold tabular-nums">{{ $teamStats['draws'] }}</span></div>
                        <div class="flex justify-between py-1.5 text-sm"><span class="text-zinc-400">Losses</span><span class="font-semibold tabular-nums text-red-400">{{ $teamStats['loses'] }}</span></div>
                    </div>

                    {{-- Goals --}}
                    <div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
                        <h2 class="text-sm font-semibold mb-3">Goals</h2>
                        <div class="flex justify-between py-1.5 text-sm border-b border-zinc-800/60"><span class="text-zinc-400">Scored</span><span class="font-semibold tabular-nums">{{ $teamStats['gf'] }} <span class="text-zinc-500 font-normal">({{ $teamStats['gfAvg'] }}/game)</span></span></div>
                        <div class="flex justify-between py-1.5 text-sm border-b border-zinc-800/60"><span class="text-zinc-400">Conceded</span><span class="font-semibold tabular-nums">{{ $teamStats['ga'] }} <span class="text-zinc-500 font-normal">({{ $teamStats['gaAvg'] }}/game)</span></span></div>
                        <div class="flex justify-between py-1.5 text-sm border-b border-zinc-800/60"><span class="text-zinc-400">Clean sheets</span><span class="font-semibold tabular-nums">{{ $teamStats['cleanSheet'] }}</span></div>
                        <div class="flex justify-between py-1.5 text-sm"><span class="text-zinc-400">Failed to score</span><span class="font-semibold tabular-nums">{{ $teamStats['failedScore'] }}</span></div>
                    </div>

                    {{-- Records & discipline --}}
                    <div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
                        <h2 class="text-sm font-semibold mb-3">Records</h2>
                        @if ($teamStats['biggestWin'])
                        <div class="flex justify-between py-1.5 text-sm border-b border-zinc-800/60"><span class="text-zinc-400">Biggest win</span><span class="font-semibold tabular-nums">{{ $teamStats['biggestWin'] }}</span></div>
                        @endif
                        <div class="flex justify-between py-1.5 text-sm border-b border-zinc-800/60"><span class="text-zinc-400">Longest win streak</span><span class="font-semibold tabular-nums">{{ $teamStats['streakWins'] }}</span></div>
                        @if ($teamStats['formation'])
                        <div class="flex justify-between py-1.5 text-sm border-b border-zinc-800/60"><span class="text-zinc-400">Top formation</span><span class="font-semibold tabular-nums">{{ $teamStats['formation'] }}</span></div>
                        @endif
                        <div class="flex justify-between py-1.5 text-sm border-b border-zinc-800/60"><span class="text-zinc-400">Penalties</span><span class="font-semibold tabular-nums">{{ $teamStats['penScored'] }}/{{ $teamStats['penTotal'] }}</span></div>
                        <div class="flex justify-between py-1.5 text-sm border-b border-zinc-800/60"><span class="text-zinc-400 flex items-center gap-1.5"><span class="w-3 h-4 rounded-sm bg-yellow-500"></span> Yellow cards</span><span class="font-semibold tabular-nums">{{ $teamStats['yellow'] }}</span></div>
                        <div class="flex justify-between py-1.5 text-sm"><span class="text-zinc-400 flex items-center gap-1.5"><span class="w-3 h-4 rounded-sm bg-red-600"></span> Red cards</span><span class="font-semibold tabular-nums">{{ $teamStats['red'] }}</span></div>
                    </div>

                </div>
            @else
                <div class="bg-zinc-900 rounded-lg p-10 text-center text-zinc-500">No team statistics available.</div>
            @endif
        </div>

            {{-- TRANSFERS --}}
            <div x-show="tab === 'transfers'" x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0">
            @if ($transfersIn->isNotEmpty() || $transfersOut->isNotEmpty())
                <div class="grid lg:grid-cols-2 gap-3 sm:gap-4 items-start">

                    {{-- IN --}}
                    <div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
                        <h2 class="text-sm font-semibold mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                            Transfers in
                        </h2>
                        <div class="divide-y divide-zinc-800/60">
                            @forelse ($transfersIn as $t)
                                <div class="flex items-center gap-3 py-2.5">
                                    <span class="w-9 h-9 rounded-full bg-zinc-800 overflow-hidden shrink-0">
                                        @if ($t['pid'])<img src="https://media.api-sports.io/football/players/{{ $t['pid'] }}.png" alt="" class="w-full h-full object-cover" />@endif
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="text-sm font-medium text-zinc-100 truncate">{{ $t['player'] }}</div>
                                        <div class="text-[11px] text-zinc-500 flex items-center gap-1 min-w-0">
                                            <span>from</span>
                                            @if ($t['outLogo'])<img src="{{ $t['outLogo'] }}" alt="" class="w-3.5 h-3.5 object-contain shrink-0" />@endif
                                            <span class="truncate">{{ $t['outName'] }}</span>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <div class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-zinc-800 text-zinc-300">{{ $t['type'] }}</div>
                                        @if ($t['date'])<div class="text-[10px] text-zinc-600 mt-0.5">{{ rescue(fn () => \Illuminate\Support\Carbon::parse($t['date'])->format('M Y'), $t['date']) }}</div>@endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-zinc-500 text-sm py-2">No incoming transfers.</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- OUT --}}
                    <div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
                        <h2 class="text-sm font-semibold mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18" /></svg>
                            Transfers out
                        </h2>
                        <div class="divide-y divide-zinc-800/60">
                            @forelse ($transfersOut as $t)
                                <div class="flex items-center gap-3 py-2.5">
                                    <span class="w-9 h-9 rounded-full bg-zinc-800 overflow-hidden shrink-0">
                                        @if ($t['pid'])<img src="https://media.api-sports.io/football/players/{{ $t['pid'] }}.png" alt="" class="w-full h-full object-cover" />@endif
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="text-sm font-medium text-zinc-100 truncate">{{ $t['player'] }}</div>
                                        <div class="text-[11px] text-zinc-500 flex items-center gap-1 min-w-0">
                                            <span>to</span>
                                            @if ($t['inLogo'])<img src="{{ $t['inLogo'] }}" alt="" class="w-3.5 h-3.5 object-contain shrink-0" />@endif
                                            <span class="truncate">{{ $t['inName'] }}</span>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <div class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-zinc-800 text-zinc-300">{{ $t['type'] }}</div>
                                        @if ($t['date'])<div class="text-[10px] text-zinc-600 mt-0.5">{{ rescue(fn () => \Illuminate\Support\Carbon::parse($t['date'])->format('M Y'), $t['date']) }}</div>@endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-zinc-500 text-sm py-2">No outgoing transfers.</p>
                            @endforelse
                        </div>
                    </div>

                </div>
            @else
                <div class="bg-zinc-900 rounded-lg p-10 text-center text-zinc-500">No transfer data available.</div>
            @endif
        </div>

        {{-- HISTORY --}}
        <div x-show="tab === 'history'" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="space-y-3 sm:space-y-4">
            @if (! empty($seasonStats))
                {{-- Season by season --}}
                <div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
                    <h2 class="text-sm font-semibold mb-3">Season by season</h2>
                    <div class="overflow-x-auto [&::-webkit-scrollbar]:hidden">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-[10px] uppercase tracking-wide text-zinc-500 border-b border-zinc-800">
                                    <th class="font-medium text-left pb-2">Season</th>
                                    <th class="font-medium text-left pb-2 hidden sm:table-cell">Coach</th>
                                    <th class="font-medium text-center pb-2 w-8">P</th>
                                    <th class="font-medium text-center pb-2 w-8">W</th>
                                    <th class="font-medium text-center pb-2 w-8">D</th>
                                    <th class="font-medium text-center pb-2 w-8">L</th>
                                    <th class="font-medium text-center pb-2 w-12">Win%</th>
                                    <th class="font-medium text-center pb-2 w-12">PPG</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-800/60">
                                @foreach (array_reverse($seasonStats) as $s)
                                    @php $pp = $s['w'] + $s['d'] + $s['l']; @endphp
                                    <tr class="hover:bg-zinc-800/60 transition">
                                        <td class="py-2.5 font-medium">{{ $s['label'] }}</td>
                                        <td class="py-2.5 hidden sm:table-cell">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <span class="w-6 h-6 rounded-full bg-zinc-800 overflow-hidden shrink-0">
                                                    @if (! empty($s['coachPhoto']))<img src="{{ $s['coachPhoto'] }}" alt="" class="w-full h-full object-cover" />@endif
                                                </span>
                                                <span class="truncate text-zinc-300">{{ $s['coach'] ?? '—' }}</span>
                                            </div>
                                        </td>
                                        <td class="text-center text-zinc-400 tabular-nums">{{ $pp }}</td>
                                        <td class="text-center text-green-400 tabular-nums">{{ $s['w'] }}</td>
                                        <td class="text-center text-zinc-400 tabular-nums">{{ $s['d'] }}</td>
                                        <td class="text-center text-red-400 tabular-nums">{{ $s['l'] }}</td>
                                        <td class="text-center font-semibold tabular-nums">{{ $s['winpct'] }}%</td>
                                        <td class="text-center text-zinc-400 tabular-nums">{{ $s['ppg'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="text-[11px] text-zinc-600 mt-3">Based on competitive matches from 2023 onwards.</p>
                </div>

                {{-- Honours (placeholder, diisi via CMS nanti) --}}
                <div class="bg-zinc-900 rounded-lg p-4 flex items-center gap-3 text-zinc-500">
                    <svg class="w-8 h-8 text-zinc-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14v7m-4 0h8M5 5h14M7 5v2a5 5 0 0010 0V5" /></svg>
                    <div>
                        <div class="text-sm font-medium text-zinc-300">Honours &amp; trophies</div>
                        <div class="text-xs">Coming soon — added manually via CMS (not in the data feed).</div>
                    </div>
                </div>
            @else
                <div class="bg-zinc-900 rounded-lg p-10 text-center text-zinc-500">No history available.</div>
            @endif
        </div>

        {{-- NEWS --}}
        <div x-show="tab === 'news'" x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0">
            <div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
                <x-news-list :team="$team" />
            </div>
        </div>
                {{-- ABOUT --}}
                <div x-show="tab === 'about'" x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0">
            <div class="grid lg:grid-cols-3 gap-3 sm:gap-4 items-start">

                {{-- KIRI: info klub + pelatih --}}
                <div class="lg:col-span-2 space-y-3 sm:space-y-4">
                    <div class="bg-zinc-900 rounded-lg p-4 sm:p-5">
                        <div class="flex items-center gap-4 mb-5">
                            <span class="w-14 h-14 flex items-center justify-center shrink-0">
                                @if ($team->logo_url)
                                    <img src="{{ $team->logo_url }}" alt="{{ $team->name }}" class="w-full h-full object-contain" />
                                @endif
                            </span>
                            <div>
                                <h2 class="text-lg font-bold">{{ $team->name }}</h2>
                                <p class="text-sm text-zinc-400">{{ $country }}</p>
                            </div>
                        </div>
                        <div class="grid sm:grid-cols-2 gap-px bg-zinc-800 rounded-lg overflow-hidden">
                            <div class="bg-zinc-900 p-3 sm:p-4">
                                <div class="text-[11px] uppercase tracking-wide text-zinc-500 mb-1">Country</div>
                                <div class="text-sm font-semibold">{{ $country }}</div>
                            </div>
                            @if ($founded)
                            <div class="bg-zinc-900 p-3 sm:p-4">
                                <div class="text-[11px] uppercase tracking-wide text-zinc-500 mb-1">Founded</div>
                                <div class="text-sm font-semibold">{{ $founded }}</div>
                            </div>
                            @endif
                            @if ($venue)
                            <div class="bg-zinc-900 p-3 sm:p-4">
                                <div class="text-[11px] uppercase tracking-wide text-zinc-500 mb-1">Stadium</div>
                                <div class="text-sm font-semibold">{{ $venue['name'] ?? '—' }}</div>
                            </div>
                            <div class="bg-zinc-900 p-3 sm:p-4">
                                <div class="text-[11px] uppercase tracking-wide text-zinc-500 mb-1">Capacity</div>
                                <div class="text-sm font-semibold">{{ $venue['capacity'] ? number_format($venue['capacity']) : '—' }}</div>
                            </div>
                            <div class="bg-zinc-900 p-3 sm:p-4">
                                <div class="text-[11px] uppercase tracking-wide text-zinc-500 mb-1">City</div>
                                <div class="text-sm font-semibold">{{ $venue['city'] ?? '—' }}</div>
                            </div>
                            <div class="bg-zinc-900 p-3 sm:p-4">
                                <div class="text-[11px] uppercase tracking-wide text-zinc-500 mb-1">Surface</div>
                                <div class="text-sm font-semibold capitalize">{{ $venue['surface'] ?? '—' }}</div>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Pelatih --}}
                    @if ($coach)
                    <div class="bg-zinc-900 rounded-lg p-4 sm:p-5">
                        <h2 class="text-sm font-semibold mb-4">Head coach</h2>
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-full bg-zinc-800 overflow-hidden shrink-0">
                                @if (!empty($coach['photo']))
                                    <img src="{{ $coach['photo'] }}" alt="{{ $coach['name'] }}" class="w-full h-full object-cover" />
                                @endif
                            </div>
                            <div class="min-w-0">
                                <div class="text-base font-bold truncate">{{ $coach['name'] }}</div>
                                <div class="text-sm text-zinc-400">
                                    {{ $coach['nationality'] ?? '' }}@if (!empty($coach['age'])) · {{ $coach['age'] }} yrs @endif
                                </div>
                                @if (!empty($coach['since']))
                                    <div class="text-xs text-zinc-500 mt-1">Since {{ substr($coach['since'], 0, 4) }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- KANAN: foto stadion --}}
                @if ($venue)
                <div class="space-y-3 sm:space-y-4">
                    <div class="bg-zinc-900 rounded-lg overflow-hidden">
                        @if (!empty($venue['image']))
                            <img src="{{ $venue['image'] }}" alt="{{ $venue['name'] }}" class="w-full h-40 object-cover" />
                        @endif
                        <div class="p-4">
                            <h3 class="text-sm font-bold">{{ $venue['name'] ?? 'Stadium' }}</h3>
                            <p class="text-xs text-zinc-400 mt-0.5">{{ $venue['address'] ?? '' }}{{ !empty($venue['city']) ? ', ' . $venue['city'] : '' }}</p>
                            <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode(($venue['name'] ?? '') . ' ' . ($venue['city'] ?? '')) }}"
                                target="_blank" rel="noopener"
                                class="inline-flex items-center gap-1.5 mt-3 text-xs text-blue-400 hover:text-blue-300 font-medium">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                View on Google Maps
                            </a>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    </div>

        
        </div>
    </div>
@endsection