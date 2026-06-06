@extends('layouts.app')

@section('title', 'All Football Leagues & Competitions - LiveScore')
@section('description', 'Browse all football leagues and competitions — Premier League, La Liga, Serie A, Champions League, World Cup and more. Tables, fixtures and results on LiveScore.')
@section('schema')
<script type="application/ld+json">{!! json_encode([
    '@'.'context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'name' => 'All Football Leagues & Competitions',
    'url' => url()->current(),
], JSON_UNESCAPED_SLASHES) !!}</script>
@endsection

@section('content')

    <h1 class="text-2xl font-bold">All Leagues</h1>
    <p class="text-sm text-zinc-500 mt-1 mb-4">{{ count($leagues) }} competitions</p>

    <div x-data="{ q: '' }">
        {{-- Search --}}
        <div class="relative mb-4">
            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-zinc-500 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="7" /><path d="M21 21l-4-4" />
            </svg>
            <input type="text" x-model="q" placeholder="Filter leagues or country…"
                   class="w-full bg-zinc-900 border border-zinc-800 rounded-lg text-sm pl-9 pr-4 py-2.5 text-white placeholder-zinc-500 focus:outline-none focus:border-blue-500 transition">
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach ($leagues as $league)
                @php $country = $meta[$league->code][0] ?? 'Football'; $flag = $meta[$league->code][1] ?? '⚽'; @endphp
                <a href="/league/{{ $league->id }}"
                    x-show="'{{ strtolower($league->name . ' ' . $league->country) }}'.includes(q.toLowerCase().trim())"
                    class="flex items-center gap-3 bg-zinc-900 rounded-xl p-3.5 border border-transparent hover:border-zinc-700 hover:bg-zinc-800 transition group">
                    @if ($league->logo_url)
                        <span class="w-11 h-11 rounded-xl bg-white flex items-center justify-center shrink-0 p-1.5">
                            <img src="{{ $league->logo_url }}" alt="{{ $league->name }}" class="w-full h-full object-contain" />
                        </span>
                    @else
                        <span class="w-11 h-11 rounded-xl {{ $league->color }} flex items-center justify-center text-xs font-bold shrink-0">{{ $league->code }}</span>
                    @endif
                    <div class="min-w-0 flex-1">
                        <div class="text-sm font-semibold truncate group-hover:text-white transition">{{ $league->name }}</div>
                        <div class="text-xs text-zinc-500 truncate flex items-center gap-1.5">
                            @if ($league->flag)<img src="{{ $league->flag }}" alt="" class="w-3.5 h-3 object-cover rounded-sm shrink-0" />@endif
                            {{ $league->country ?? 'International' }}
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-zinc-600 group-hover:text-zinc-400 transition shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                </a>
            @endforeach
        </div>
    </div>
@endsection