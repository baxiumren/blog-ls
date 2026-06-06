@extends('layouts.app')

@section('title', 'Matches - LiveScore')
@section('description', 'Live football scores, fixtures and results from the Premier League, La Liga, Serie A, Bundesliga, World Cup and more. Real-time match updates, standings and stats on LiveScore.')
@section('schema')
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'name' => 'Live Football Scores & Matches',
    'url' => url('/'),
], JSON_UNESCAPED_SLASHES) !!}</script>
@endsection

@section('content')
@if ($motd && ($settings['motd_enabled'] ?? '1') !== '0')
<a href="/match/{{ $motd->id }}" class="block relative overflow-hidden rounded-2xl mb-5 bg-gradient-to-br from-blue-700 via-blue-900 to-zinc-950 border border-blue-500/30 group">
    <div class="absolute -right-10 -top-10 w-48 h-48 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="relative p-5 sm:p-6">
        <div class="flex items-center justify-between mb-4">
            <span class="inline-flex items-center gap-1 text-[11px] font-bold uppercase tracking-wider bg-amber-400 text-black px-2.5 py-1 rounded-full">⭐ Match of the Day</span>
            <span class="text-xs text-blue-200/70 truncate max-w-[40%]">{{ $motd->league->name ?? '' }}</span>
        </div>
        <div class="flex items-center justify-center gap-4 sm:gap-8">
            <div class="flex-1 flex flex-col items-center gap-2 text-center min-w-0">
                <x-team-badge :team="$motd->homeTeam->name" :logo="$motd->homeTeam->logo_url" size="lg" />
                <span class="text-sm font-semibold truncate max-w-full">{{ $motd->homeTeam->name }}</span>
            </div>
            <div class="text-center shrink-0" x-data="countdown('{{ $motd->kickoff_at->toIso8601String() }}')">
                <div class="text-2xl sm:text-3xl font-black tabular-nums">{{ $motd->kickoff_at->format('H:i') }}</div>
                <div class="text-[11px] text-blue-200/70">{{ $motd->kickoff_at->format('D, d M') }}</div>
                <div x-show="!started" x-cloak class="mt-1 text-xs font-semibold text-amber-300"><span x-text="days"></span>d <span x-text="hours"></span>h <span x-text="mins"></span>m</div>
                <div x-show="started" x-cloak class="mt-1 text-xs font-semibold text-green-400">Kicking off! ⚽</div>
            </div>
            <div class="flex-1 flex flex-col items-center gap-2 text-center min-w-0">
                <x-team-badge :team="$motd->awayTeam->name" :logo="$motd->awayTeam->logo_url" size="lg" />
                <span class="text-sm font-semibold truncate max-w-full">{{ $motd->awayTeam->name }}</span>
            </div>
        </div>
        <div class="text-center mt-4">
            <span class="text-xs text-blue-200/80 group-hover:text-white transition">View match details →</span>
        </div>
    </div>
</a>
@endif
    <div class="text-center mb-4">
        <h1 class="text-2xl font-bold">
            {{ $selectedDate->isToday() ? "Today's Matches" : $selectedDate->format('l, d M') }}
        </h1>
        @if ($totalMatches > 0)
            <p class="text-sm text-zinc-500 mt-1">{{ $totalMatches }} {{ \Illuminate\Support\Str::plural('match', $totalMatches) }}</p>
        @endif
    </div>

    <x-date-bar :selected="$selectedDate" />

    <x-match-filter :selected="$selectedDate" :active="$filter" />

    {{-- Your teams (dari localStorage) --}}
    <div x-data x-show="$store.favs.items.length" x-cloak class="mb-4 bg-zinc-900 rounded-lg p-3 sm:p-4">
        <div class="flex items-center justify-between mb-2.5">
            <h2 class="text-sm font-semibold flex items-center gap-1.5">
                <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 24 24"><path d="M11.48 3.5l2.6 5.27 5.82.85-4.2 4.1.99 5.78L11.48 17l-5.2 2.5.99-5.78-4.2-4.1 5.81-.85z" /></svg>
                Your teams
            </h2>
        </div>
        <div class="flex gap-2 flex-wrap">
            <template x-for="t in $store.favs.items" :key="t.id">
                <a :href="'/team/' + t.id" class="flex items-center gap-2 bg-zinc-800 hover:bg-zinc-700 rounded-full pl-1.5 pr-3 py-1 transition group">
                    <span class="w-6 h-6 rounded-full bg-white/10 flex items-center justify-center overflow-hidden shrink-0">
                        <template x-if="t.logo"><img :src="t.logo" :alt="t.name" class="w-full h-full object-contain"></template>
                    </span>
                    <span class="text-sm text-zinc-200 group-hover:text-white" x-text="t.name"></span>
                    <button type="button" @click.prevent="$store.favs.toggle(t)" class="text-zinc-500 hover:text-red-400 transition" title="Unfollow">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </a>
            </template>
        </div>
    </div>

    @forelse ($leagues as $league)
        <x-league-group :league="$league" />
    @empty
        <div class="bg-zinc-900 rounded-lg p-10 text-center">
            <svg class="w-12 h-12 mx-auto mb-3 text-zinc-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <p class="text-zinc-400 font-medium">No matches on this day</p>
            <p class="text-zinc-600 text-sm mt-1">Try another date above.</p>
        </div>
    @endforelse

    @if ($totalMatches > $limit)
    <div class="mt-4 text-center">
        @if (! $showAll)
            <a href="?date={{ $selectedDate->toDateString() }}{{ $filter !== 'all' ? '&filter=' . $filter : '' }}&all=1"
            class="inline-block px-5 py-2 text-sm font-medium text-blue-500 hover:text-white hover:bg-zinc-800 rounded-lg transition">
                Show all {{ $totalMatches }} matches
            </a>
        @else
            <a href="?date={{ $selectedDate->toDateString() }}{{ $filter !== 'all' ? '&filter=' . $filter : '' }}"
            class="inline-block px-5 py-2 text-sm font-medium text-zinc-400 hover:text-white hover:bg-zinc-800 rounded-lg transition">
                Show less
            </a>
        @endif
    </div>
    @endif

    <x-ad-slot format="leaderboard" />

    {{-- Latest News --}}
    @if (($settings['news_enabled'] ?? '1') !== '0' && $news->isNotEmpty())
        <section class="mt-8">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-bold">Latest News</h2>
                <a href="/news" class="text-sm text-blue-400 hover:text-white transition">See all →</a>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($news as $a)
                    <a href="/news/{{ $a->slug }}" class="group rounded-xl overflow-hidden bg-zinc-900 border border-zinc-800 flex flex-col">
                        <div class="aspect-video bg-zinc-800 overflow-hidden">
                            @if ($a->image)<img src="{{ asset('storage/' . $a->image) }}" alt="" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">@else<div class="w-full h-full flex items-center justify-center text-zinc-700 text-xs">No image</div>@endif
                        </div>
                        <div class="p-3 flex-1 flex flex-col">
                            <span class="text-[10px] font-semibold uppercase tracking-wide text-blue-400">{{ $a->category }}</span>
                            <p class="text-sm font-semibold leading-snug mt-1 line-clamp-2 group-hover:text-white transition">{{ $a->title }}</p>
                            <span class="text-[11px] text-zinc-600 mt-auto pt-2">{{ $a->published_at->diffForHumans() }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
@endsection