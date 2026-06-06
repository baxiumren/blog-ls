@extends('layouts.app')

@section('title', $league->name . ' - LiveScore')
@section('description', $league->name . ' table, fixtures, results, top scorers and team stats. Follow the ' . $league->name . ' season live on LiveScore.')
@section('schema')
<script type="application/ld+json">{!! json_encode(array_filter([
    '@context' => 'https://schema.org',
    '@type' => 'SportsOrganization',
    'name' => $league->name,
    'sport' => 'Soccer',
    'logo' => $league->logo_url,
    'url' => url()->current(),
]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endsection
@section('og_image', $league->logo_url)
@section('no-left', 'yes')
@section('no-right', 'yes')

@section('content')
    @php
        $tabs = ['overview' => 'Overview'];
        if (! empty($standingsGroups)) {
            $tabs['table'] = 'Table';
        }
        if (! empty($koRounds)) {
            $tabs['knockout'] = 'Knockout';
        }
        $tabs['fixtures'] = 'Fixtures';
        if ($topScorers->isNotEmpty() || $topAssists->isNotEmpty()) {
            $tabs['playerstats'] = 'Player stats';
        }
        if (! empty($leagueTeamStats)) {
            $tabs['teamstats'] = 'Team stats';
        }
        $tabs['news'] = 'News';
        $multiGroup = count($standingsGroups) > 1;
    @endphp

    <div x-data="{ tab: '{{ request('round') ? 'fixtures' : 'overview' }}' }">
        {{-- Header (warna ikut liga) --}}
        <div class="relative overflow-hidden rounded-lg mb-4 {{ $league->color ?? 'bg-zinc-900' }}">
            <div class="absolute inset-0 bg-gradient-to-br from-white/15 via-transparent to-black/30 pointer-events-none"></div>
            <div class="relative p-4 sm:p-6 flex items-center gap-4">
                <span class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl bg-white flex items-center justify-center shrink-0 p-2">
                    @if ($league->logo_url)<img src="{{ $league->logo_url }}" alt="{{ $league->name }}" class="w-full h-full object-contain" />@endif
                </span>
                <div class="min-w-0 flex-1">
                    <h1 class="text-xl sm:text-3xl font-bold truncate text-white">{{ $league->name }}</h1>
                    <p class="text-sm text-white/80">{{ $country }}</p>
                </div>
                @if ($availableSeasons->count() > 1)
                    @php $twoYear = in_array($league->code, ['PL', 'LL', 'SA', 'BL', 'L1', 'CL', 'EL', 'ERE', 'POR', 'SPL', 'MX', 'IDN']); @endphp
                    <select onchange="if(this.value)window.location='?season='+this.value"
                            class="shrink-0 bg-white/15 border border-white/30 rounded-lg text-xs sm:text-sm px-2 py-1.5 text-white focus:outline-none focus:border-white cursor-pointer">
                        @foreach ($availableSeasons as $s)
                            <option class="text-zinc-900" value="{{ $s }}" {{ $s == $season ? 'selected' : '' }}>{{ $s }}@if ($twoYear)/{{ str_pad(($s + 1) % 100, 2, '0', STR_PAD_LEFT) }}@endif</option>
                        @endforeach
                    </select>
                @endif
            </div>
        </div>

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
        <div x-show="tab === 'overview'" x-cloak
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
            {{-- Countdown bar — warna ikut liga --}}
            @if ($upcoming->isNotEmpty())
                @php $next = $upcoming->first(); @endphp
                <div class="relative rounded-lg overflow-hidden {{ $league->color ?? 'bg-blue-700' }}">
                    <div class="absolute inset-0 bg-gradient-to-br from-white/25 via-transparent to-black/30 pointer-events-none"></div>
                    <div class="relative flex items-center justify-between gap-3 p-3" x-data="countdown('{{ $next->kickoff_at->toIso8601String() }}')">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="w-6 h-6 rounded-full bg-white flex items-center justify-center shrink-0 p-0.5">
                                @if ($league->logo_url)<img src="{{ $league->logo_url }}" alt="" class="w-full h-full object-contain" />@endif
                            </span>
                            <span class="text-sm font-bold text-white truncate">{{ $league->name }}</span>
                        </div>
                        <div class="flex items-center gap-2.5 shrink-0 text-white text-center" x-show="!started" x-cloak>
                            <div><div class="text-base font-bold leading-none tabular-nums" x-text="days"></div><div class="text-[9px] uppercase opacity-80">Days</div></div>
                            <div><div class="text-base font-bold leading-none tabular-nums" x-text="hours"></div><div class="text-[9px] uppercase opacity-80">Hrs</div></div>
                            <div><div class="text-base font-bold leading-none tabular-nums" x-text="mins"></div><div class="text-[9px] uppercase opacity-80">Min</div></div>
                        </div>
                        <span x-show="started" x-cloak class="text-xs font-bold text-white shrink-0">Kick off!</span>
                    </div>
                </div>
            @endif
            <div class="grid lg:grid-cols-3 gap-3 sm:gap-4 items-start mt-3 sm:mt-4">
                <div class="lg:col-span-2 {{ $multiGroup ? 'grid sm:grid-cols-2 gap-3 sm:gap-4 items-start' : 'space-y-3 sm:space-y-4' }}">
                    @forelse ($standingsGroups as $name => $rows)
                        <div class="{{ count($rows) > 6 ? 'sm:col-span-2' : '' }}">
                            <x-league-standings :name="$name" :rows="$rows" :teamIdMap="$teamIdMap" />
                        </div>
                    @empty
                        <div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
                            <h2 class="text-sm font-semibold mb-3">Recent results</h2>
                            <div class="-mx-3 sm:-mx-4 divide-y divide-zinc-800">
                                @foreach ($recent->take(8) as $f)<x-match-card :fixture="$f" />@endforeach
                            </div>
                        </div>
                    @endforelse
                </div>
                <div class="space-y-3 sm:space-y-4">
                    

                    {{-- Fixtures (dikelompokin per tanggal) --}}
                    <div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h2 class="text-sm font-semibold">Fixtures</h2>
                            <button @click="tab = 'fixtures'" class="text-xs text-blue-400 hover:text-white transition">See all</button>
                        </div>
                        @php
                            $prev = $upcoming->isNotEmpty() ? $upcoming->take(12) : $recent->take(12);
                            $byDate = $prev->groupBy(fn ($f) => $f->kickoff_at->format('l, j M'));
                        @endphp
                        @forelse ($byDate as $date => $matches)
                            <div class="text-[11px] font-semibold text-zinc-400 bg-zinc-800/40 px-3 sm:px-4 py-1.5 -mx-3 sm:-mx-4">{{ $date }}</div>
                            <div class="-mx-3 sm:-mx-4 divide-y divide-zinc-800">
                                @foreach ($matches as $f)<x-match-card :fixture="$f" />@endforeach
                            </div>
                        @empty
                            <p class="text-zinc-500 text-sm px-1">No fixtures.</p>
                        @endforelse
                    </div>
                    {{-- Top scorers preview --}}
                    @if ($topScorers->isNotEmpty())
                    <div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h2 class="text-sm font-semibold">Top scorers</h2>
                            <button @click="tab = 'playerstats'" class="text-xs text-blue-400 hover:text-white transition">See all</button>
                        </div>
                        <div class="divide-y divide-zinc-800/60">
                            @foreach ($topScorers->take(5) as $i => $pl)
                                <a href="/player/{{ $pl['id'] }}" class="flex items-center gap-3 py-2 hover:bg-zinc-800/40 transition px-1 -mx-1 rounded">
                                    <span class="w-4 text-center text-zinc-500 tabular-nums shrink-0">{{ $i + 1 }}</span>
                                    <span class="w-8 h-8 rounded-full bg-zinc-800 overflow-hidden shrink-0">
                                        @if ($pl['photo'])<img src="{{ $pl['photo'] }}" alt="" class="w-full h-full object-cover" />@endif
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="text-sm text-zinc-100 truncate">{{ $pl['name'] }}</div>
                                        <div class="text-[11px] text-zinc-500 truncate">{{ $pl['team'] }}</div>
                                    </div>
                                    <span class="shrink-0 text-sm font-bold text-white bg-blue-600 px-2 py-0.5 rounded-full tabular-nums">{{ $pl['value'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    {{-- Top assists preview --}}
                    @if ($topAssists->isNotEmpty())
                    <div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h2 class="text-sm font-semibold">Top assists</h2>
                            <button @click="tab = 'playerstats'" class="text-xs text-blue-400 hover:text-white transition">See all</button>
                        </div>
                        <div class="divide-y divide-zinc-800/60">
                            @foreach ($topAssists->take(5) as $i => $pl)
                                <a href="/player/{{ $pl['id'] }}" class="flex items-center gap-3 py-2 hover:bg-zinc-800/40 transition px-1 -mx-1 rounded">
                                    <span class="w-4 text-center text-zinc-500 tabular-nums shrink-0">{{ $i + 1 }}</span>
                                    <span class="w-8 h-8 rounded-full bg-zinc-800 overflow-hidden shrink-0">
                                        @if ($pl['photo'])<img src="{{ $pl['photo'] }}" alt="" class="w-full h-full object-cover" />@endif
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="text-sm text-zinc-100 truncate">{{ $pl['name'] }}</div>
                                        <div class="text-[11px] text-zinc-500 truncate">{{ $pl['team'] }}</div>
                                    </div>
                                    <span class="shrink-0 text-sm font-bold text-white bg-blue-600 px-2 py-0.5 rounded-full tabular-nums">{{ $pl['value'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Season leaders (team) --}}
                    @if (! empty($leagueTeamStats))
                    <div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
                        <h2 class="text-sm font-semibold mb-3">Season leaders</h2>
                        <div class="grid grid-cols-3 gap-2 text-center">
                            @foreach (array_slice($leagueTeamStats, 0, 3) as $card)
                                @php $top = $card['rows'][0] ?? null; @endphp
                                @if ($top)
                                <div class="min-w-0">
                                    <div class="text-[9px] uppercase tracking-wide text-zinc-500 mb-2 leading-tight">{{ $card['title'] }}</div>
                                    <span class="w-7 h-7 mx-auto mb-1 flex items-center justify-center">
                                        @if ($top['logo'])<img src="{{ $top['logo'] }}" alt="" class="w-full h-full object-contain" />@endif
                                    </span>
                                    <div class="text-[11px] text-zinc-300 truncate">{{ $top['team'] }}</div>
                                    <div class="text-base font-bold tabular-nums">{{ $top['value'] }}</div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- News --}}
            <div class="bg-zinc-900 rounded-lg p-3 sm:p-4 mt-3 sm:mt-4">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-semibold">News</h2>
                    <button @click="tab = 'news'" class="text-xs text-blue-400 hover:text-white transition">See more</button>
                </div>
                <x-news-list :league="$league" />
            </div>
        </div>

        {{-- TABLE --}}
        @if (! empty($standingsGroups))
        <div x-show="tab === 'table'" x-cloak
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="{{ $multiGroup ? 'grid lg:grid-cols-2 gap-3 sm:gap-4 items-start' : 'space-y-3 sm:space-y-4' }}">
                @foreach ($standingsGroups as $name => $rows)
                    <div class="{{ count($rows) > 6 ? 'lg:col-span-2' : '' }}">
                        <x-league-standings :name="$name" :rows="$rows" :teamIdMap="$teamIdMap" :showForm="true" />
                    </div>
                @endforeach
            </div>
        </div>
        @endif

            {{-- KNOCKOUT --}}
            @if (! empty($koRounds))
            <div x-show="tab === 'knockout'" x-cloak
                    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="overflow-x-auto [&::-webkit-scrollbar]:hidden pb-2">
                    <div class="flex gap-3 sm:gap-4 min-w-max">
                        @foreach ($koRounds as $roundName => $fixtures)
                            <div class="w-60 shrink-0 space-y-3">
                                <h2 class="text-xs font-semibold uppercase tracking-wide text-zinc-400 text-center">{{ $roundName }}</h2>
                                @foreach ($fixtures as $f)
                                    @php
                                        $hw = $f->status === 'finished' && $f->home_score > $f->away_score;
                                        $aw = $f->status === 'finished' && $f->away_score > $f->home_score;
                                    @endphp
                                    <a href="/match/{{ $f->id }}" class="block bg-zinc-900 rounded-lg p-2.5 hover:bg-zinc-800 transition">
                                        <div class="flex items-center justify-between gap-2 {{ $aw ? 'opacity-50' : '' }}">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <x-team-badge :team="$f->homeTeam->name" :logo="$f->homeTeam->logo_url" size="sm" />
                                                <span class="text-xs truncate {{ $hw ? 'font-semibold text-white' : 'text-zinc-300' }}">{{ $f->homeTeam->short_name }}</span>
                                            </div>
                                            <span class="text-xs font-bold tabular-nums shrink-0">{{ $f->status === 'scheduled' ? '–' : $f->home_score }}</span>
                                        </div>
                                        <div class="flex items-center justify-between gap-2 mt-1.5 {{ $hw ? 'opacity-50' : '' }}">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <x-team-badge :team="$f->awayTeam->name" :logo="$f->awayTeam->logo_url" size="sm" />
                                                <span class="text-xs truncate {{ $aw ? 'font-semibold text-white' : 'text-zinc-300' }}">{{ $f->awayTeam->short_name }}</span>
                                            </div>
                                            <span class="text-xs font-bold tabular-nums shrink-0">{{ $f->status === 'scheduled' ? '–' : $f->away_score }}</span>
                                        </div>
                                        <div class="text-[10px] text-zinc-600 mt-1.5">{{ $f->kickoff_at->format('d M') }}</div>
                                    </a>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

        {{-- FIXTURES --}}
        <div x-show="tab === 'fixtures'" x-cloak
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
             class="space-y-3 sm:space-y-4">
            @if ($rounds->isNotEmpty())
                <div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
                    {{-- Round nav --}}
                    <div class="flex items-center justify-center gap-2 sm:gap-3 mb-4">
                        @if ($prevRound)
                            <a href="?round={{ urlencode($prevRound) }}" class="w-8 h-8 rounded-full bg-zinc-800 hover:bg-zinc-700 flex items-center justify-center text-zinc-300 shrink-0 transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                            </a>
                        @else
                            <span class="w-8 h-8 rounded-full border border-zinc-800 flex items-center justify-center text-zinc-700 shrink-0"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg></span>
                        @endif
                        <select onchange="if(this.value)window.location='?round='+encodeURIComponent(this.value)"
                                class="bg-zinc-800 border border-zinc-700 rounded-lg text-sm font-semibold px-3 py-1.5 text-white text-center focus:outline-none focus:border-blue-500 cursor-pointer">
                            @foreach ($rounds as $r)
                                <option value="{{ $r }}" {{ $r == $selectedRound ? 'selected' : '' }}>{{ trim(str_replace(['Regular Season -', 'Group Stage -', 'League Stage -'], ['Round', 'Round', 'Round'], $r)) }}</option>
                            @endforeach
                        </select>
                        @if ($nextRound)
                            <a href="?round={{ urlencode($nextRound) }}" class="w-8 h-8 rounded-full bg-zinc-800 hover:bg-zinc-700 flex items-center justify-center text-zinc-300 shrink-0 transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                            </a>
                        @else
                            <span class="w-8 h-8 rounded-full border border-zinc-800 flex items-center justify-center text-zinc-700 shrink-0"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg></span>
                        @endif
                    </div>
                    {{-- Fixtures per tanggal --}}
                    @php $byDate = $roundFixtures->groupBy(fn ($f) => $f->kickoff_at->format('l, j M')); @endphp
                    @forelse ($byDate as $date => $matches)
                        <div class="text-[11px] font-semibold text-zinc-400 bg-zinc-800/40 px-3 sm:px-4 py-1.5 -mx-3 sm:-mx-4">{{ $date }}</div>
                        <div class="-mx-3 sm:-mx-4 divide-y divide-zinc-800/70">
                            @foreach ($matches as $f)<x-fixture-row :fixture="$f" />@endforeach
                        </div>
                    @empty
                        <p class="text-zinc-500 text-sm px-1 py-2">No fixtures in this round.</p>
                    @endforelse
                </div>
            @else
                @if ($upcoming->isNotEmpty())
                <div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
                    <h2 class="text-sm font-semibold mb-3">Upcoming</h2>
                    <div class="-mx-3 sm:-mx-4 divide-y divide-zinc-800/70">@foreach ($upcoming as $f)<x-fixture-row :fixture="$f" />@endforeach</div>
                </div>
                @endif
                @if ($recent->isNotEmpty())
                <div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
                    <h2 class="text-sm font-semibold mb-3">Results</h2>
                    <div class="-mx-3 sm:-mx-4 divide-y divide-zinc-800/70">@foreach ($recent as $f)<x-fixture-row :fixture="$f" />@endforeach</div>
                </div>
                @endif
            @endif
        </div>

        {{-- PLAYER STATS --}}
        @if ($topScorers->isNotEmpty() || $topAssists->isNotEmpty())
        <div x-show="tab === 'playerstats'" x-cloak
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 items-start">
                @foreach ([['Top scorers', $topScorers], ['Top assists', $topAssists], ['Most yellow cards', $topYellow]] as [$title, $list])
                    <div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
                        <h2 class="text-sm font-semibold mb-3">{{ $title }}</h2>
                        <div class="divide-y divide-zinc-800/60">
                            @foreach ($list as $i => $pl)
                                <a href="/player/{{ $pl['id'] }}" class="flex items-center gap-3 py-2 hover:bg-zinc-800/40 transition px-1 -mx-1 rounded">
                                    <span class="w-5 text-center text-zinc-500 tabular-nums shrink-0">{{ $i + 1 }}</span>
                                    <span class="w-8 h-8 rounded-full bg-zinc-800 overflow-hidden shrink-0">
                                        @if ($pl['photo'])<img src="{{ $pl['photo'] }}" alt="" class="w-full h-full object-cover" />@endif
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="text-sm text-zinc-100 truncate">{{ $pl['name'] }}</div>
                                        <div class="text-[11px] text-zinc-500 flex items-center gap-1 min-w-0">
                                            @if ($pl['teamLogo'])<img src="{{ $pl['teamLogo'] }}" alt="" class="w-3.5 h-3.5 object-contain shrink-0" />@endif
                                            <span class="truncate">{{ $pl['team'] }}</span>
                                        </div>
                                    </div>
                                    <span class="shrink-0 text-sm font-bold text-white bg-blue-600 px-2.5 py-1 rounded-full min-w-[2.25rem] text-center tabular-nums">{{ $pl['value'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- TEAM STATS --}}
        @if (! empty($leagueTeamStats))
        <div x-show="tab === 'teamstats'" x-cloak
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="grid sm:grid-cols-2 gap-3 sm:gap-4 items-start">
                @foreach ($leagueTeamStats as $card)
                    <div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
                        <h2 class="text-sm font-semibold mb-3">{{ $card['title'] }}</h2>
                        <div class="divide-y divide-zinc-800/60">
                            @foreach ($card['rows'] as $i => $r)
                                @php $lid = $teamIdMap[$r['teamApiId']] ?? null; @endphp
                                <div class="flex items-center gap-3 py-2">
                                    <span class="w-5 text-center text-zinc-500 tabular-nums shrink-0">{{ $i + 1 }}</span>
                                    @if ($r['logo'])<img src="{{ $r['logo'] }}" alt="" class="w-6 h-6 object-contain shrink-0" />@endif
                                    @if ($lid)<a href="/team/{{ $lid }}" class="text-sm truncate flex-1 hover:text-white transition">{{ $r['team'] }}</a>@else<span class="text-sm truncate flex-1">{{ $r['team'] }}</span>@endif
                                    <span class="shrink-0 text-sm font-bold text-white bg-blue-600 px-2.5 py-1 rounded-full min-w-[2.25rem] text-center tabular-nums">{{ $r['value'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- NEWS --}}
        <div x-show="tab === 'news'" x-cloak
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
                <x-news-list :league="$league" />
            </div>
        </div>
</div>
@endsection