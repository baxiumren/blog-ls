@extends('layouts.app')

@section('title', 'Football Predictions & Betting Tips')
@section('description', 'Expert football match predictions, score forecasts and betting tips for upcoming fixtures across the top leagues and competitions.')
@section('no-left', 'yes')
@section('no-right', 'yes')

@section('content')
    <h1 class="text-2xl font-bold mb-1">Predictions & Tips</h1>
    <p class="text-sm text-zinc-500 mb-4">Our match forecasts for upcoming fixtures.</p>

    @if ($tips->count())
        <div class="grid sm:grid-cols-2 gap-4">
            @foreach ($tips as $p)
                @if ($p->fixture)
                    <a href="/match/{{ $p->fixture_id }}" class="block bg-zinc-900 rounded-xl border border-zinc-800 hover:border-zinc-700 p-4 transition">
                        <div class="flex items-center justify-between text-[11px] text-zinc-500 mb-2">
                            <span class="truncate">{{ $p->fixture->league->name ?? '' }}</span>
                            <span class="shrink-0">{{ $p->fixture->kickoff_at->format('d M · H:i') }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <div class="flex items-center gap-2 min-w-0">
                                <x-team-badge :team="$p->fixture->homeTeam->name" :logo="$p->fixture->homeTeam->logo_url" size="sm" />
                                <span class="text-sm font-medium truncate">{{ $p->fixture->homeTeam->short_name }}</span>
                            </div>
                            <span class="text-xs text-zinc-600 shrink-0">vs</span>
                            <div class="flex items-center gap-2 min-w-0 justify-end">
                                <span class="text-sm font-medium truncate">{{ $p->fixture->awayTeam->short_name }}</span>
                                <x-team-badge :team="$p->fixture->awayTeam->name" :logo="$p->fixture->awayTeam->logo_url" size="sm" />
                            </div>
                        </div>
                        <div class="flex items-center justify-between gap-2 pt-3 border-t border-zinc-800">
                            <span class="bg-blue-600 text-white text-xs font-semibold px-3 py-1 rounded-full">{{ $p->tip }}</span>
                            <div class="flex items-center gap-2">
                                @if ($p->predicted_score)<span class="text-xs text-zinc-400 tabular-nums">{{ $p->predicted_score }}</span>@endif
                                <span class="text-amber-400 text-xs">{{ str_repeat('★', $p->confidence) }}</span>
                            </div>
                        </div>
                    </a>
                @endif
            @endforeach
        </div>
        <div class="mt-6">{{ $tips->links() }}</div>
    @else
        <div class="bg-zinc-900 rounded-xl border border-zinc-800 p-10 text-center text-zinc-500">No predictions yet. Check back soon. 🔮</div>
    @endif
@endsection