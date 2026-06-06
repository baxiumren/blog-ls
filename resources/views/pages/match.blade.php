@extends('layouts.app')

@section('title', $fixture->homeTeam->name . ' vs ' . $fixture->awayTeam->name . ' - LiveScore')
@section('description', $fixture->homeTeam->name . ' vs ' . $fixture->awayTeam->name . ' — live score, lineups, stats, head-to-head and match details on LiveScore.')
@section('schema')
@php
    $matchSchema = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'SportsEvent',
        'name' => $fixture->homeTeam->name . ' vs ' . $fixture->awayTeam->name,
        'sport' => 'Soccer',
        'startDate' => optional($fixture->kickoff_at)->toIso8601String(),
        'url' => url()->current(),
        'homeTeam' => ['@type' => 'SportsTeam', 'name' => $fixture->homeTeam->name],
        'awayTeam' => ['@type' => 'SportsTeam', 'name' => $fixture->awayTeam->name],
    ]);
@endphp
<script type="application/ld+json">{!! json_encode($matchSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endsection

@section('no-left', 'yes')

@section('content')

    @php
    $formColor = fn ($r) => $r === 'W' ? 'bg-green-500' : ($r === 'L' ? 'bg-red-500' : 'bg-zinc-500');

    // Siapa yang kalah (buat dicoret) — termasuk lewat adu penalti
    $homeLost = false;
    $awayLost = false;
    if ($fixture->status === 'finished') {
        if ($fixture->home_score !== $fixture->away_score) {
            $homeLost = $fixture->home_score < $fixture->away_score;
            $awayLost = $fixture->away_score < $fixture->home_score;
        } elseif (($info['penHome'] ?? null) !== null) {
            $homeLost = $info['penHome'] < $info['penAway'];
            $awayLost = $info['penAway'] < $info['penHome'];
        }
    }
    @endphp

    {{-- Mini sticky bar (muncul pas scroll) --}}
    <div x-data="{ show: false }" @scroll.window="show = window.scrollY > 280"
        x-show="show" x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        class="fixed top-14 inset-x-0 z-40 bg-zinc-950/95 backdrop-blur border-b border-zinc-800">
        <div class="max-w-6xl mx-auto px-4 h-12 flex items-center justify-center gap-4">
            <div class="flex items-center gap-2">
                <span class="text-sm font-semibold {{ $homeLost ? 'line-through text-zinc-500' : '' }}">{{ $fixture->homeTeam->short_name }}</span>
                <x-team-badge :team="$fixture->homeTeam->name" :logo="$fixture->homeTeam->logo_url" size="sm" />
            </div>
            <span class="text-sm font-bold whitespace-nowrap">
                @if ($fixture->status === 'scheduled')
                    {{ $fixture->kickoff_at->format('H:i') }}
                @else
                    {{ $fixture->home_score }} - {{ $fixture->away_score }}
                @endif
            </span>
            <div class="flex items-center gap-2">
                <x-team-badge :team="$fixture->awayTeam->name" :logo="$fixture->awayTeam->logo_url" size="sm" />
                <span class="text-sm font-semibold {{ $awayLost ? 'line-through text-zinc-500' : '' }}">{{ $fixture->awayTeam->short_name }}</span>
            </div>
        </div>
    </div>

    {{-- Tombol balik --}}
    <a href="/" class="inline-flex items-center gap-1 text-sm text-zinc-400 hover:text-white mb-4">
        &larr; Back to matches
    </a>

    {{-- Papan skor --}}
    <div class="bg-zinc-900 rounded-lg p-4 sm:p-6 animate-in">
        <a href="/league/{{ $fixture->league->id }}" class="flex items-center justify-center gap-2 text-xs text-zinc-400 hover:text-white transition mb-4">
            @if ($fixture->league->logo_url)
                <span class="w-4 h-4 rounded-full bg-white flex items-center justify-center shrink-0 p-0.5">
                    <img src="{{ $fixture->league->logo_url }}" alt="{{ $fixture->league->name }}" class="w-full h-full object-contain" />
                </span>
            @endif
            <span>{{ $fixture->league->name }}</span>
        </a>
        <div class="flex items-center justify-center gap-4 sm:gap-8">
            <div class="flex flex-col items-center gap-2 w-24">
                <x-team-badge :team="$fixture->homeTeam->name" :logo="$fixture->homeTeam->logo_url" size="lg" />
                    <div class="lg:h-10 flex items-center justify-center">
                    <a href="/team/{{ $fixture->home_team_id }}" class="text-sm font-medium text-center hover:text-blue-400 transition {{ $homeLost ? 'line-through text-zinc-500' : '' }}">
                        <span class="lg:hidden">{{ $fixture->homeTeam->short_name }}</span>
                        <span class="hidden lg:inline">{{ $fixture->homeTeam->name }}</span>
                    </a>
                </div>
                    @if (! empty($homeForm))
                        <div class="flex gap-0.5">
                            @foreach ($homeForm as $r)
                                <span class="w-1.5 h-1.5 rounded-full {{ $formColor($r) }}"></span>
                            @endforeach
                        </div>
                    @endif
            </div>
            <div class="text-center">
                @if ($fixture->status === 'scheduled')
                    <div class="text-3xl font-bold">{{ $fixture->kickoff_at->format('H:i') }}</div>
                    <div class="text-xs text-zinc-500 mt-1">{{ $fixture->kickoff_at->format('D, d M') }}</div>
                @else
                    <div class="text-4xl font-bold whitespace-nowrap shrink-0">{{ $fixture->home_score }} - {{ $fixture->away_score }}</div>
                    @if (($info['penHome'] ?? null) !== null)
                        <div class="text-xs font-semibold text-zinc-400 mt-1">Pen: {{ $info['penHome'] }} - {{ $info['penAway'] }}</div>
                    @endif
                    @if ($fixture->status === 'live')
                        <div class="text-xs font-bold text-green-500 mt-1">{{ $fixture->minute }}'</div>
                    @else
                        <div class="text-xs font-medium text-zinc-500 mt-1">Full Time</div>
                    @endif
                @endif
            </div>
            <div class="flex flex-col items-center gap-2 w-24">
                <x-team-badge :team="$fixture->awayTeam->name" :logo="$fixture->awayTeam->logo_url" size="lg" />
                    <div class="lg:h-10 flex items-center justify-center">
                        <a href="/team/{{ $fixture->away_team_id }}" class="text-sm font-medium text-center hover:text-blue-400 transition {{ $awayLost ? 'line-through text-zinc-500' : '' }}">
                            <span class="lg:hidden">{{ $fixture->awayTeam->short_name }}</span>
                            <span class="hidden lg:inline">{{ $fixture->awayTeam->name }}</span>
                        </a>
                    </span>
                </div>
                    @if (! empty($awayForm))
                        <div class="flex gap-0.5">
                            @foreach ($awayForm as $r)
                                <span class="w-1.5 h-1.5 rounded-full {{ $formColor($r) }}"></span>
                            @endforeach
                        </div>
                    @endif
            </div>
        </div>

        {{-- Ringkasan pencetak gol (di bawah skor) --}}
        @php
            $goalRows = collect($events)
                ->filter(fn ($e) => ($e['type'] ?? '') === 'Goal' && in_array($e['detail'] ?? '', ['Normal Goal', 'Penalty', 'Own Goal']))
                ->map(function ($e) use ($fixture) {
                    $isHomeTeam = (int) ($e['team']['id'] ?? 0) === (int) $fixture->homeTeam->api_id;
                    $scoringHome = ($e['detail'] === 'Own Goal') ? ! $isHomeTeam : $isHomeTeam;
                    $tag = $e['detail'] === 'Penalty' ? ' (P)' : ($e['detail'] === 'Own Goal' ? ' (OG)' : '');
                    return ['name' => $e['player']['name'] ?? '', 'min' => $e['time']['elapsed'] ?? '', 'home' => $scoringHome, 'tag' => $tag];
                });
        @endphp
        @if ($goalRows->isNotEmpty())
            <div class="flex items-start justify-center gap-3 mt-4 pt-4 border-t border-zinc-800 text-xs text-zinc-400">
                <div class="flex-1 text-right space-y-0.5">
                    @foreach ($goalRows->where('home', true) as $g)
                        <div>{{ $g['name'] }} {{ $g['min'] }}'{{ $g['tag'] }}</div>
                    @endforeach
                </div>
                <div class="shrink-0 pt-0.5"><x-event-icon :event="['type' => 'Goal']" /></div>
                <div class="flex-1 text-left space-y-0.5">
                    @foreach ($goalRows->where('home', false) as $g)
                        <div>{{ $g['name'] }} {{ $g['min'] }}'{{ $g['tag'] }}</div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

        {{-- Video highlights --}}
        @if ($highlight && $highlight->youtubeId() && ($settings['highlights_enabled'] ?? '1') !== '0')
            <div class="bg-zinc-900 rounded-lg p-4 mb-4 animate-in">
                <h2 class="text-sm font-semibold mb-3 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 24 24"><path d="M10 15l5.19-3L10 9v6zm12-3c0 5.52-4.48 10-10 10S2 17.52 2 12 6.48 2 12 2s10 4.48 10 10z"/></svg>
                    {{ $highlight->title ?: 'Match highlights' }}
                </h2>
                <div class="aspect-video w-full rounded-lg overflow-hidden bg-black">
                    <iframe class="w-full h-full" src="https://www.youtube.com/embed/{{ $highlight->youtubeId() }}" title="Highlights" frameborder="0" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>
            </div>
        @endif

        {{-- Editor prediction --}}
        @if ($matchPrediction && ($settings['show_tips'] ?? '1') !== '0')
        <div class="relative overflow-hidden rounded-xl mb-4 border border-blue-500/30 bg-gradient-to-br from-blue-600/15 via-zinc-900 to-zinc-900 animate-in">
            <div class="absolute -right-8 -top-8 w-32 h-32 bg-blue-600/10 rounded-full blur-2xl pointer-events-none"></div>
            <div class="relative p-4 sm:p-5">
                <div class="flex items-center justify-between gap-2 mb-4">
                    <h2 class="text-sm font-bold flex items-center gap-2 uppercase tracking-wide">
                        <svg class="w-4 h-4 text-blue-400" fill="currentColor" viewBox="0 0 24 24"><path d="M13 2L3 14h7v8l10-12h-7z" /></svg>
                        Expert Prediction
                    </h2>
                    <div class="flex items-center gap-1" title="Confidence {{ $matchPrediction->confidence }}/5">
                        @for ($i = 1; $i <= 5; $i++)
                            <span class="w-1.5 h-4 rounded-full {{ $i <= $matchPrediction->confidence ? 'bg-amber-400' : 'bg-zinc-700' }}"></span>
                        @endfor
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="flex-1">
                        <div class="text-[10px] uppercase tracking-wider text-zinc-500 mb-1.5">Our tip</div>
                        <span class="inline-flex items-center gap-2 bg-blue-600 text-white text-base font-bold px-4 py-1.5 rounded-lg shadow-lg shadow-blue-600/20">{{ $matchPrediction->tip }}</span>
                    </div>
                    @if ($matchPrediction->predicted_score)
                        <div class="text-center sm:border-l sm:border-zinc-700/50 sm:pl-4">
                            <div class="text-[10px] uppercase tracking-wider text-zinc-500 mb-1.5">Predicted score</div>
                            <div class="flex items-center justify-center gap-2">
                                <span class="text-xs text-zinc-400 truncate max-w-[64px]">{{ $fixture->homeTeam->short_name }}</span>
                                <span class="text-xl font-black tabular-nums text-white">{{ $matchPrediction->predicted_score }}</span>
                                <span class="text-xs text-zinc-400 truncate max-w-[64px]">{{ $fixture->awayTeam->short_name }}</span>
                            </div>
                        </div>
                    @endif
                </div>
                @if ($matchPrediction->body)
                    <div class="article-body text-sm text-zinc-300 mt-4 pt-4 border-t border-zinc-800/60">{!! \Illuminate\Support\Str::markdown($matchPrediction->body) !!}</div>
                @endif
            </div>
        </div>
    @endif

    {{-- Poll: Who will win (sembunyi kalau match selesai) --}}
    @if ($fixture->status !== 'finished')
    <div class="bg-zinc-900 rounded-lg p-4 mb-4 animate-in" x-data="matchPoll({{ $fixture->id }}, @js($pollCounts))">
        <h2 class="text-sm font-semibold mb-3">Who will win?</h2>
        @php
            $pollOpts = [
                ['k' => 'home', 'label' => $fixture->homeTeam->short_name, 'logo' => $fixture->homeTeam->logo_url],
                ['k' => 'draw', 'label' => 'Draw', 'logo' => null],
                ['k' => 'away', 'label' => $fixture->awayTeam->short_name, 'logo' => $fixture->awayTeam->logo_url],
            ];
        @endphp
        <div class="grid grid-cols-3 gap-2">
            @foreach ($pollOpts as $o)
                <button type="button" @click="vote('{{ $o['k'] }}')" :disabled="voted"
                        :class="choice === '{{ $o['k'] }}' ? 'border-blue-500 bg-blue-500/10' : 'border-zinc-800 hover:border-zinc-600'"
                        class="flex flex-col items-center gap-2 py-3 rounded-lg border transition disabled:cursor-default">
                    @if ($o['logo'])
                        <span class="w-8 h-8 rounded-full bg-white flex items-center justify-center p-0.5 shrink-0"><img src="{{ $o['logo'] }}" alt="" class="w-full h-full object-contain"></span>
                    @else
                        <span class="w-8 h-8 rounded-full bg-zinc-700 flex items-center justify-center text-sm font-bold shrink-0">=</span>
                    @endif
                    <span class="text-xs font-medium truncate max-w-full px-1">{{ $o['label'] }}</span>
                    <span x-show="voted" x-cloak class="text-[11px] font-semibold" :class="choice === '{{ $o['k'] }}' ? 'text-blue-400' : 'text-zinc-500'" x-text="pct('{{ $o['k'] }}') + '%'"></span>
                </button>
            @endforeach
        </div>

        @if (! empty($settings['community_url']))
            <div x-show="voted" x-cloak x-transition class="mt-4 text-center">
                <p class="text-sm text-zinc-300 mb-2">Prediksimu tersimpan! Yuk adu tebakan bareng komunitas.</p>
                <a href="{{ $settings['community_url'] }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-full bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold transition">{{ $settings['community_label'] ?: 'Gabung Komunitas' }}</a>
            </div>
        @endif

        <p class="text-[11px] text-zinc-500 mt-3" x-text="(voted ? '✓ Voted · ' : '') + counts.total + (counts.total === 1 ? ' vote' : ' votes')"></p>
    </div>
    @endif

    <div class="bg-zinc-900 rounded-lg p-4 mb-4">
        <x-share :title="$fixture->homeTeam->name . ' vs ' . $fixture->awayTeam->name" />
    </div>

    <x-ad-slot format="rectangle" />

    {{-- Tab (Alpine) --}}
    <div x-data="{ tab: 'overview' }" class="animate-in">

        {{-- Menu tab --}}
        <div class="flex gap-1 mt-4 border-b border-zinc-800">
            @php
                $tabs = ['overview' => 'Overview', 'stats' => 'Stats', 'lineups' => 'Lineups'];
                if ($standings->isNotEmpty()) {
                    $tabs['table'] = 'Table';
                }
                $tabs['h2h'] = 'H2H';
            @endphp
            @foreach ($tabs as $key => $label)
                <button @click="tab = '{{ $key }}'"
                        :class="tab === '{{ $key }}' ? 'text-white border-blue-500' : 'text-zinc-400 border-transparent hover:text-white'"
                        class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- OVERVIEW: timeline events --}}
        <div x-show="tab === 'overview'"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="bg-zinc-900 rounded-lg p-3 sm:p-4 mt-4">
            {{-- Info match --}}
            @if (! empty($info['round']) || ! empty($info['venue']) || ! empty($info['referee']))
                <div class="text-center text-xs text-zinc-400 font-medium mb-4 pb-4 border-b border-zinc-800 space-x-1.5">
                    @if (! empty($info['round']))<span class="text-zinc-100 font-semibold">{{ $info['round'] }}</span>@endif
                    @if (! empty($info['venue']))<span>· {{ $info['venue'] }}{{ $info['city'] ? ', ' . $info['city'] : '' }}</span>@endif
                    @if (! empty($info['referee']))<span>· Ref: {{ $info['referee'] }}</span>@endif
                    <span>· {{ $fixture->kickoff_at->format('d M Y, H:i') }}</span>
                </div>
            @endif

            @if ($fixture->status === 'scheduled')
                <p class="text-center text-zinc-500 text-sm py-6">Match hasn't started yet.</p>
            @elseif (count($events) === 0)
                <p class="text-center text-zinc-500 text-sm py-6">No events available for this match.</p>
                @else
                @php $hs = 0; $as = 0; $htShown = false; @endphp
                <div class="space-y-3">
                    @foreach ($events as $e)
                        @php $elapsed = $e['time']['elapsed'] ?? 0; @endphp

                        {{-- Penanda Half Time --}}
                        @if (! $htShown && $elapsed >= 46)
                            <div class="flex items-center gap-3 py-1">
                                <div class="flex-1 h-px bg-zinc-800"></div>
                                <span class="text-[10px] uppercase tracking-wide text-zinc-500">Half Time {{ $hs }}-{{ $as }}</span>
                                <div class="flex-1 h-px bg-zinc-800"></div>
                            </div>
                            @php $htShown = true; @endphp
                        @endif

                        @php
                            $isHome = (int) ($e['team']['id'] ?? 0) === (int) $fixture->homeTeam->api_id;
                            $isGoal = ($e['type'] ?? '') === 'Goal' && in_array($e['detail'] ?? '', ['Normal Goal', 'Penalty', 'Own Goal']);
                            if ($isGoal) {
                                $scoringHome = ($e['detail'] === 'Own Goal') ? ! $isHome : $isHome;
                                $scoringHome ? $hs++ : $as++;
                            }
                        @endphp

                        <div class="flex items-start gap-2 text-sm">
                            {{-- sisi home --}}
                            <div class="flex-1 flex items-center justify-end gap-2 text-right">
                                @if ($isHome)
                                    <x-event-detail :event="$e" />
                                    <x-team-badge :team="$e['team']['name'] ?? ''" :logo="$e['team']['logo'] ?? null" size="sm" />
                                @endif
                            </div>
                            {{-- tengah --}}
                            <div class="flex flex-col items-center shrink-0 w-14">
                                <x-event-icon :event="$e" />
                                <span class="text-xs font-semibold text-zinc-400 mt-0.5">{{ $elapsed }}'</span>
                                @if ($isGoal)
                                    <span class="text-[10px] font-bold text-zinc-300">{{ $hs }}-{{ $as }}</span>
                                @endif
                            </div>
                            {{-- sisi away --}}
                            <div class="flex-1 flex items-center justify-start gap-2 text-left">
                                @if (! $isHome)
                                    <x-team-badge :team="$e['team']['name'] ?? ''" :logo="$e['team']['logo'] ?? null" size="sm" />
                                    <x-event-detail :event="$e" />
                                @endif
                            </div>
                        </div>
                    @endforeach

                    {{-- Penanda Full Time --}}
                    @if ($fixture->status === 'finished')
                        <div class="flex items-center gap-3 py-1">
                            <div class="flex-1 h-px bg-zinc-800"></div>
                            <span class="text-[10px] uppercase tracking-wide text-zinc-500">Full Time</span>
                            <div class="flex-1 h-px bg-zinc-800"></div>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- STATS --}}
        <div x-show="tab === 'stats'" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="bg-zinc-900 rounded-lg p-3 sm:p-4 mt-4">
            @if (count($statRows) === 0)
                <p class="text-center text-zinc-500 text-sm py-6">No stats available for this match.</p>
                @else
                @php
                    $statLabel = [
                        'Ball Possession' => 'Possession',
                        'expected_goals' => 'Expected Goals (xG)',
                        'goals_prevented' => 'Goals Prevented',
                        'Shots on Goal' => 'Shots on Target',
                        'Shots off Goal' => 'Shots off Target',
                        'Shots insidebox' => 'Shots Inside Box',
                        'Shots outsidebox' => 'Shots Outside Box',
                        'Total passes' => 'Total Passes',
                        'Passes accurate' => 'Accurate Passes',
                        'Passes %' => 'Pass Accuracy',
                        'Corner Kicks' => 'Corners',
                    ];
                    $statGroups = [
                        'Top Stats' => ['Ball Possession', 'expected_goals', 'Total Shots', 'Shots on Goal'],
                        'Shots' => ['Shots off Goal', 'Blocked Shots', 'Shots insidebox', 'Shots outsidebox'],
                        'Passing' => ['Total passes', 'Passes accurate', 'Passes %'],
                        'Discipline' => ['Fouls', 'Yellow Cards', 'Red Cards', 'Offsides'],
                        'Other' => ['Corner Kicks', 'Goalkeeper Saves', 'goals_prevented'],
                    ];
                    $byType = collect($statRows)->keyBy('type');
                @endphp
                <div class="space-y-5">
                    @foreach ($statGroups as $groupTitle => $types)
                        @php $rows = collect($types)->map(fn ($t) => $byType[$t] ?? null)->filter(); @endphp
                        @if ($rows->isNotEmpty())
                            <div>
                                <h4 class="text-[10px] font-semibold uppercase tracking-wide text-zinc-500 mb-2">{{ $groupTitle }}</h4>
                                <div class="space-y-3">
                                    @foreach ($rows as $row)
                                        @php
                                            $h = (float) str_replace('%', '', $row['home'] ?? 0);
                                            $a = (float) str_replace('%', '', $row['away'] ?? 0);
                                            $sum = $h + $a;
                                            $hp = $sum > 0 ? round($h / $sum * 100) : 50;
                                        @endphp
                                        <div>
                                            <div class="flex items-center justify-between text-xs mb-1">
                                                <span class="w-12 font-semibold {{ $h >= $a ? 'text-blue-400' : 'text-zinc-500' }}">{{ $row['home'] ?? 0 }}</span>
                                                <span class="text-zinc-400 text-center flex-1">{{ $statLabel[$row['type']] ?? $row['type'] }}</span>
                                                <span class="w-12 text-right font-semibold {{ $a >= $h ? 'text-blue-400' : 'text-zinc-500' }}">{{ $row['away'] ?? 0 }}</span>
                                            </div>
                                            <div class="flex h-1.5 rounded-full overflow-hidden gap-px">
                                                <div class="bg-blue-500 transition-[width] duration-700 ease-out" :style="tab === 'stats' ? 'width: {{ $hp }}%' : 'width: 0%'"></div>
                                                <div class="bg-zinc-500 flex-1"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>

        {{-- LINEUPS --}}
        <div x-show="tab === 'lineups'" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0" 
        class="bg-zinc-900 rounded-lg p-3 sm:p-4 mt-4">
            @if (count($lineups) === 0)
                <p class="text-center text-zinc-500 text-sm py-6">No lineups available for this match.</p>
            @else
                <x-lineup-board :lineups="$lineups" :pstats="$pstats" :subbedIn="$subbedIn" :pevents="$playerEvents" />
                @if ($lineupPredicted ?? false)
                <p class="text-center text-xs text-amber-400 mb-3 pb-3 border-b border-zinc-800">Predicted lineup — based on each team's last match</p>
                @endif
            @endif
        </div>

        {{-- TABLE --}}
        <div x-show="tab === 'table'"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="bg-zinc-900 rounded-lg p-3 sm:p-4 mt-4">
            @if ($standings->isEmpty())
                <p class="text-center text-zinc-500 text-sm py-6">No table for this competition.</p>
                @else
                @php $total = $standings->count(); @endphp
                <div class="flex items-center text-[10px] text-zinc-500 px-1 mb-1">
                    <span class="w-2 mr-1 shrink-0"></span>
                    <span class="w-5">#</span>
                    <span class="flex-1">Team</span>
                    <span class="w-6 text-center">P</span>
                    <span class="w-6 text-center hidden sm:block">W</span>
                    <span class="w-6 text-center hidden sm:block">D</span>
                    <span class="w-6 text-center hidden sm:block">L</span>
                    <span class="w-12 text-center hidden md:block">GF:GA</span>
                    <span class="w-8 text-center">GD</span>
                    <span class="w-7 text-center">Pts</span>
                </div>
                <div class="space-y-0.5">
                    @foreach ($standings as $i => $row)
                        @php
                            $hl = in_array($row['team']->id, [$fixture->home_team_id, $fixture->away_team_id]);
                            $gd = $row['gf'] - $row['ga'];
                            $zone = $i < 4 ? 'bg-blue-500' : ($i >= $total - 3 ? 'bg-red-500' : 'bg-transparent');
                        @endphp
                        <div class="flex items-center text-sm py-1 pr-1 rounded {{ $hl ? 'bg-zinc-800' : '' }}">
                            <span class="w-1 h-5 rounded-full mr-1 shrink-0 {{ $zone }}"></span>
                            <span class="w-5 text-zinc-400">{{ $i + 1 }}</span>
                            <div class="flex-1 flex items-center gap-2 min-w-0">
                                <x-team-badge :team="$row['team']->name" :logo="$row['team']->logo_url" size="sm" />
                                <a href="/team/{{ $row['team']->id }}" class="text-zinc-200 truncate hover:text-white transition">{{ $row['team']->name }}</a>
                            </div>
                            <span class="w-6 text-center text-zinc-400">{{ $row['played'] }}</span>
                            <span class="w-6 text-center text-zinc-400 hidden sm:block">{{ $row['won'] }}</span>
                            <span class="w-6 text-center text-zinc-400 hidden sm:block">{{ $row['drawn'] }}</span>
                            <span class="w-6 text-center text-zinc-400 hidden sm:block">{{ $row['lost'] }}</span>
                            <span class="w-12 text-center text-zinc-400 hidden md:block">{{ $row['gf'] }}:{{ $row['ga'] }}</span>
                            <span class="w-8 text-center text-zinc-400">{{ $gd > 0 ? '+' : '' }}{{ $gd }}</span>
                            <span class="w-7 text-center font-semibold">{{ $row['points'] }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="flex items-center gap-4 mt-3 text-[10px] text-zinc-500">
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-blue-500"></span> Qualification</span>
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-500"></span> Relegation</span>
                </div>
            @endif
        </div>
            {{-- HEAD-TO-HEAD --}}
            <div x-show="tab === 'h2h'" x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="bg-zinc-900 rounded-lg p-3 sm:p-4 mt-4">
            @if (count($h2h) === 0)
                <p class="text-center text-zinc-500 text-sm py-6">No head-to-head data.</p>
                @else
                @php
                    $homeApi = (int) $fixture->homeTeam->api_id;
                    $leagueApi = (int) $fixture->league->api_id;
                    $hw = 0; $dd = 0; $aw = 0;
                    foreach ($h2h as $m) {
                        $gh = $m['goals']['home'] ?? null; $ga = $m['goals']['away'] ?? null;
                        if ($gh === null || $ga === null) continue;
                        $homeIsH = (int) ($m['teams']['home']['id'] ?? 0) === $homeApi;
                        $x = $homeIsH ? $gh : $ga; $y = $homeIsH ? $ga : $gh;
                        $x > $y ? $hw++ : ($x < $y ? $aw++ : $dd++);
                    }
                @endphp

                {{-- Ringkasan: logo | Wins | Draws | Wins | logo --}}
                <div class="flex items-center justify-between gap-1 mb-4 pb-4 border-b border-zinc-800">
                    <span class="w-12 h-12 rounded-full bg-white flex items-center justify-center shrink-0 p-1">
                        <img src="{{ $fixture->homeTeam->logo_url }}" alt="" class="w-full h-full object-contain" />
                    </span>
                    <div class="text-center">
                        <div class="w-11 h-11 rounded-full bg-blue-600 flex items-center justify-center text-base font-bold mx-auto">{{ $hw }}</div>
                        <div class="text-[10px] text-zinc-400 mt-1">Wins</div>
                    </div>
                    <div class="text-center">
                        <div class="w-11 h-11 rounded-full bg-zinc-600 flex items-center justify-center text-base font-bold mx-auto">{{ $dd }}</div>
                        <div class="text-[10px] text-zinc-400 mt-1">Draws</div>
                    </div>
                    <div class="text-center">
                        <div class="w-11 h-11 rounded-full bg-red-600 flex items-center justify-center text-base font-bold mx-auto">{{ $aw }}</div>
                        <div class="text-[10px] text-zinc-400 mt-1">Wins</div>
                    </div>
                    <span class="w-12 h-12 rounded-full bg-white flex items-center justify-center shrink-0 p-1">
                        <img src="{{ $fixture->awayTeam->logo_url }}" alt="" class="w-full h-full object-contain" />
                    </span>
                </div>

                {{-- Filter + daftar --}}
                <div x-data="{ filter: 'all' }">
                    <div class="flex gap-2 mb-3">
                        <button @click="filter='all'" :class="filter==='all' ? 'bg-white text-black' : 'bg-zinc-800 text-zinc-300 hover:text-white'" class="px-3 py-1 rounded-full text-xs font-medium transition">All</button>
                        <button @click="filter='home'" :class="filter==='home' ? 'bg-white text-black' : 'bg-zinc-800 text-zinc-300 hover:text-white'" class="px-3 py-1 rounded-full text-xs font-medium transition">{{ $fixture->homeTeam->short_name }} home</button>
                        <button @click="filter='comp'" :class="filter==='comp' ? 'bg-white text-black' : 'bg-zinc-800 text-zinc-300 hover:text-white'" class="px-3 py-1 rounded-full text-xs font-medium transition">This competition</button>
                    </div>

                    <div class="divide-y divide-zinc-800">
                        @foreach ($h2h as $m)
                            @php
                                $gh = $m['goals']['home'] ?? null;
                                $ga = $m['goals']['away'] ?? null;
                                $homeWon = ($gh !== null && $ga !== null && $gh > $ga);
                                $awayWon = ($gh !== null && $ga !== null && $ga > $gh);
                                $isHomeMatch = (int) ($m['teams']['home']['id'] ?? 0) === $homeApi;
                                $isSameComp = (int) ($m['league']['id'] ?? 0) === $leagueApi;
                            @endphp
                            <div x-show="filter === 'all' || (filter === 'home' && {{ $isHomeMatch ? 'true' : 'false' }}) || (filter === 'comp' && {{ $isSameComp ? 'true' : 'false' }})"
                                 x-transition.opacity.duration.300ms
                                 class="flex items-center gap-2 py-2 text-sm">
                                <div class="w-20 shrink-0">
                                    <div class="text-[11px] text-zinc-500">{{ \Illuminate\Support\Carbon::parse($m['fixture']['date'])->format('d M y') }}</div>
                                    <div class="text-[10px] text-zinc-600 truncate">{{ $m['league']['name'] ?? '' }}</div>
                                </div>
                                <div class="flex-1 flex items-center justify-end gap-2 min-w-0">
                                    <span class="truncate {{ $homeWon ? 'font-semibold text-zinc-100' : 'text-zinc-500' }}">{{ $m['teams']['home']['name'] ?? '' }}</span>
                                    <x-team-badge :team="$m['teams']['home']['name'] ?? ''" :logo="$m['teams']['home']['logo'] ?? null" size="sm" />
                                </div>
                                <span class="shrink-0 font-semibold w-12 text-center">{{ $gh ?? '-' }}-{{ $ga ?? '-' }}</span>
                                <div class="flex-1 flex items-center gap-2 min-w-0">
                                    <x-team-badge :team="$m['teams']['away']['name'] ?? ''" :logo="$m['teams']['away']['logo'] ?? null" size="sm" />
                                    <span class="truncate {{ $awayWon ? 'font-semibold text-zinc-100' : 'text-zinc-500' }}">{{ $m['teams']['away']['name'] ?? '' }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
        <div class="bg-zinc-900 rounded-lg p-3 sm:p-4 mt-4">
        <h2 class="text-sm font-semibold mb-3">Related news</h2>
        <x-news-list :context="$fixture->homeTeam->short_name . ' vs ' . $fixture->awayTeam->short_name" :limit="4" />
    </div>
@endsection

@section('rightbar')
    {{-- Match Info --}}
    <div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
        <h3 class="text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-3">Match Info</h3>
        <dl class="space-y-2 text-sm">
            <div class="flex justify-between gap-3"><dt class="text-zinc-500 shrink-0">Competition</dt><dd class="text-zinc-200 text-right">{{ $fixture->league->name }}</dd></div>
            @if (! empty($info['round']))
                <div class="flex justify-between gap-3"><dt class="text-zinc-500 shrink-0">Round</dt><dd class="text-zinc-200 text-right">{{ $info['round'] }}</dd></div>
            @endif
            <div class="flex justify-between gap-3"><dt class="text-zinc-500 shrink-0">Date</dt><dd class="text-zinc-200 text-right">{{ $fixture->kickoff_at->format('d M Y, H:i') }}</dd></div>
            @if (! empty($info['venue']))
                <div class="flex justify-between gap-3"><dt class="text-zinc-500 shrink-0">Venue</dt><dd class="text-zinc-200 text-right">{{ $info['venue'] }}</dd></div>
            @endif
            @if (! empty($info['referee']))
                <div class="flex justify-between gap-3"><dt class="text-zinc-500 shrink-0">Referee</dt><dd class="text-zinc-200 text-right">{{ $info['referee'] }}</dd></div>
            @endif
        </dl>
    </div>

    {{-- Who will win? --}}
    @php $pp = $prediction['predictions'] ?? null; @endphp
    @if ($pp && $fixture->status !== 'finished')
        @php
            $ph = (int) str_replace('%', '', $pp['percent']['home'] ?? '0');
            $pd = (int) str_replace('%', '', $pp['percent']['draw'] ?? '0');
            $pa = (int) str_replace('%', '', $pp['percent']['away'] ?? '0');
        @endphp
        <div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-3">Who will win?</h3>
            <div x-data="{ go: false }" x-init="$nextTick(() => go = true)" class="flex h-2 rounded-full overflow-hidden gap-px mb-2">
                <div class="bg-blue-500 transition-[width] duration-700 ease-out" :style="go ? 'width: {{ $ph }}%' : 'width: 0%'"></div>
                <div class="bg-zinc-500 transition-[width] duration-700 ease-out" :style="go ? 'width: {{ $pd }}%' : 'width: 0%'"></div>
                <div class="bg-red-500 transition-[width] duration-700 ease-out" :style="go ? 'width: {{ $pa }}%' : 'width: 0%'"></div>
            </div>
            <div class="flex justify-between text-xs font-semibold">
                <span class="text-blue-400">{{ $ph }}%</span>
                <span class="text-zinc-400">{{ $pd }}%</span>
                <span class="text-red-400">{{ $pa }}%</span>
            </div>
            <div class="flex justify-between text-[10px] text-zinc-500 mt-1 gap-2">
                <span class="truncate">{{ $fixture->homeTeam->name }}</span>
                <span class="shrink-0">Draw</span>
                <span class="truncate text-right">{{ $fixture->awayTeam->name }}</span>
            </div>
            @if (! empty($pp['advice']))
                <p class="text-xs text-zinc-400 mt-3 pt-3 border-t border-zinc-800">{{ $pp['advice'] }}</p>
            @endif
        </div>
    @endif

    {{-- Form --}}
    <div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
        <h3 class="text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-3">Form</h3>
        <div class="space-y-2 text-sm">
            <div class="flex items-center justify-between gap-2">
                <span class="truncate">{{ $fixture->homeTeam->name }}</span>
                <div class="flex gap-0.5 shrink-0">@foreach ($homeForm as $r)<span class="w-1.5 h-1.5 rounded-full {{ $formColor($r) }}"></span>@endforeach</div>
            </div>
            <div class="flex items-center justify-between gap-2">
                <span class="truncate">{{ $fixture->awayTeam->name }}</span>
                <div class="flex gap-0.5 shrink-0">@foreach ($awayForm as $r)<span class="w-1.5 h-1.5 rounded-full {{ $formColor($r) }}"></span>@endforeach</div>
            </div>
        </div>
    </div>
@endsection