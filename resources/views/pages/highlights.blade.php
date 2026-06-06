@extends('layouts.app')

@section('title', 'Match Highlights')
@section('description', 'Watch the latest football match highlights — goals, key moments and full match recaps from top leagues and competitions.')
@section('no-left', 'yes')
@section('no-right', 'yes')

@section('content')
    <h1 class="text-2xl font-bold mb-1">Highlights</h1>
    <p class="text-sm text-zinc-500 mb-4">Latest match highlights & recaps.</p>

    @if ($highlights->count())
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($highlights as $hl)
                @if ($hl->fixture && $hl->youtubeId())
                    <a href="/match/{{ $hl->fixture_id }}" class="group block bg-zinc-900 rounded-xl border border-zinc-800 hover:border-zinc-700 overflow-hidden transition">
                        <div class="relative aspect-video bg-black">
                            <img src="https://img.youtube.com/vi/{{ $hl->youtubeId() }}/hqdefault.jpg" alt="" loading="lazy" class="w-full h-full object-cover group-hover:opacity-80 transition">
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="w-12 h-12 rounded-full bg-red-600/90 flex items-center justify-center group-hover:scale-110 transition shadow-lg">
                                    <svg class="w-5 h-5 text-white ml-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z" /></svg>
                                </span>
                            </div>
                        </div>
                        <div class="p-3">
                            <div class="text-[11px] text-zinc-500 mb-1 truncate">{{ $hl->fixture->league->name ?? '' }} · {{ $hl->fixture->kickoff_at->format('d M Y') }}</div>
                            <div class="text-sm font-medium truncate group-hover:text-white transition">{{ $hl->fixture->homeTeam->name }} vs {{ $hl->fixture->awayTeam->name }}</div>
                            @if ($hl->title)<div class="text-xs text-zinc-500 truncate mt-0.5">{{ $hl->title }}</div>@endif
                        </div>
                    </a>
                @endif
            @endforeach
        </div>
        <div class="mt-6">{{ $highlights->links() }}</div>
    @else
        <div class="bg-zinc-900 rounded-xl border border-zinc-800 p-10 text-center text-zinc-500">No highlights yet. Check back soon. 🎬</div>
    @endif
@endsection