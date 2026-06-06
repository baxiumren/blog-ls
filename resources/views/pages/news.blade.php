@extends('layouts.app')

@section('title', 'Football News')
@section('description', 'Latest football news, transfer rumours, match previews and expert analysis from the Premier League, Champions League, World Cup and more.')
@section('no-left', 'yes')
@section('no-right', 'yes')

@section('content')
<h1 class="text-2xl font-bold mb-4">News<span class="text-zinc-500 font-semibold">{{ ! empty($category) ? ' · ' . $category : '' }}</span></h1>

<form method="GET" action="/news" class="relative max-w-sm mb-5">
    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-zinc-500 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7" /><path d="M21 21l-4-4" /></svg>
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search news…" class="w-full bg-zinc-900 border border-zinc-800 rounded-lg pl-9 pr-4 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition placeholder-zinc-600">
</form>

@if (request('q'))
    <p class="text-sm text-zinc-400 mb-4">Search results for "<span class="text-white font-medium">{{ request('q') }}</span>" — {{ $articles->total() }} found.</p>
@endif

@if (! empty($trending) && $trending->isNotEmpty())
<div class="bg-zinc-900 rounded-xl border border-zinc-800 p-4 mb-5">
    <h2 class="text-sm font-semibold mb-3 flex items-center gap-1.5">
        <svg class="w-4 h-4 text-orange-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2c.5 4-2.5 5.5-2.5 8.5a2.5 2.5 0 005 0c0-1 .5-2 .5-2s2 2.5 2 5a5 5 0 01-10 0c0-4 5-6 5-11.5z"/></svg>
        Trending this week
    </h2>
    <div class="space-y-2.5">
        @foreach ($trending as $i => $row)
            <a href="/news/{{ $row['article']->slug }}" class="flex items-center gap-3 group">
                <span class="text-lg font-black text-zinc-700 w-5 tabular-nums shrink-0">{{ $i + 1 }}</span>
                <p class="text-sm truncate flex-1 group-hover:text-white transition">{{ $row['article']->title }}</p>
                <span class="text-xs text-zinc-500 shrink-0 tabular-nums">{{ number_format($row['views']) }} views</span>
            </a>
        @endforeach
    </div>
</div>
@endif

    {{-- Featured --}}
    @if ($featured->isNotEmpty())
        @php $hero = $featured->first(); $subs = $featured->slice(1)->take(4); @endphp
        <div class="grid lg:grid-cols-2 gap-4 mb-6">
            <a href="/news/{{ $hero->slug }}" class="group relative rounded-xl overflow-hidden bg-zinc-900 border border-zinc-800 min-h-[260px] flex">
                @if ($hero->image)<img src="{{ asset('storage/' . $hero->image) }}" alt="{{ $hero->title }}" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition duration-500">@endif
                <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent"></div>
                <div class="relative mt-auto p-5">
                    <span class="inline-block text-[10px] font-semibold uppercase tracking-wide bg-amber-500 text-black px-2 py-0.5 rounded mb-2">⭐ {{ $hero->category }}</span>
                    <h2 class="text-xl font-bold leading-tight group-hover:text-blue-300 transition">{{ $hero->title }}</h2>
                    <p class="text-sm text-zinc-300 mt-1 line-clamp-2">{{ $hero->displayExcerpt(140) }}</p>
                    <span class="text-[11px] text-zinc-400 mt-2 block">{{ $hero->published_at->diffForHumans() }}</span>
                </div>
            </a>
            <div class="grid sm:grid-cols-2 gap-4">
                @foreach ($subs as $s)
                    <a href="/news/{{ $s->slug }}" class="group rounded-xl overflow-hidden bg-zinc-900 border border-zinc-800 flex flex-col">
                        <div class="aspect-video bg-zinc-800 overflow-hidden">@if ($s->image)<img src="{{ asset('storage/' . $s->image) }}" alt="" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">@endif</div>
                        <div class="p-3 flex-1">
                            <span class="text-[10px] font-semibold uppercase tracking-wide text-blue-400">{{ $s->category }}</span>
                            <p class="text-sm font-semibold leading-snug mt-1 line-clamp-2 group-hover:text-white transition">{{ $s->title }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Category filter --}}
    @if ($categories->isNotEmpty())
        <div class="flex gap-2 overflow-x-auto [&::-webkit-scrollbar]:hidden mb-4 pb-1">
            <a href="/news" class="px-3 py-1.5 rounded-full text-sm whitespace-nowrap {{ ! $category ? 'bg-blue-600 text-white' : 'bg-zinc-900 text-zinc-400 hover:text-white border border-zinc-800' }} transition">All</a>
            @foreach ($categories as $cat)
                <a href="/news/category/{{ \Illuminate\Support\Str::slug($cat) }}" class="px-3 py-1.5 rounded-full text-sm whitespace-nowrap {{ $category === $cat ? 'bg-blue-600 text-white' : 'bg-zinc-900 text-zinc-400 hover:text-white border border-zinc-800' }} transition">{{ $cat }}</a>
            @endforeach
        </div>
    @endif

    <x-ad-slot format="leaderboard" />

    {{-- Grid --}}
    @if ($articles->count())
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($articles as $a)
                <a href="/news/{{ $a->slug }}" class="group rounded-xl overflow-hidden bg-zinc-900 border border-zinc-800 flex flex-col">
                    <div class="aspect-video bg-zinc-800 overflow-hidden">@if ($a->image)<img src="{{ asset('storage/' . $a->image) }}" alt="" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">@else<div class="w-full h-full flex items-center justify-center text-zinc-700 text-xs">No image</div>@endif</div>
                    <div class="p-3 flex-1 flex flex-col">
                        <span class="text-[10px] font-semibold uppercase tracking-wide text-blue-400">{{ $a->category }}</span>
                        <p class="text-sm font-semibold leading-snug mt-1 line-clamp-2 group-hover:text-white transition">{{ $a->title }}</p>
                        <p class="text-xs text-zinc-500 mt-1.5 line-clamp-2">{{ $a->displayExcerpt(120) }}</p>
                        <span class="text-[11px] text-zinc-600 mt-auto pt-2">{{ $a->published_at->diffForHumans() }}</span>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-6">{{ $articles->links() }}</div>
    @elseif ($featured->isEmpty())
        <div class="bg-zinc-900 rounded-xl border border-zinc-800 p-10 text-center text-zinc-500">No news yet. Check back soon. 📰</div>
    @endif
@endsection