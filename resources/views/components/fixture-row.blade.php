@props(['fixture'])

@php
    $f = $fixture;
    $finished = $f->status === 'finished';
    $live = $f->status === 'live';
    $homeWon = $finished && $f->home_score > $f->away_score;
    $awayWon = $finished && $f->away_score > $f->home_score;
@endphp

<a href="/match/{{ $f->id }}" data-fx="{{ $f->api_id }}" class="flex items-center gap-2 sm:gap-3 py-2.5 px-3 sm:px-4 hover:bg-zinc-800/40 transition">
    <div class="w-12 shrink-0 text-center" data-fx-status>
        @if ($live)
            <span class="text-xs font-bold text-green-500">{{ $f->minute }}'</span>
        @elseif ($finished)
            <span class="text-[10px] font-medium text-zinc-500">FT</span>
        @else
            <span class="text-xs text-zinc-400">{{ $f->kickoff_at->format('H:i') }}</span>
        @endif
    </div>
    <div class="flex-1 flex items-center justify-end gap-2 min-w-0">
        <span class="truncate text-sm text-right {{ $awayWon ? 'text-zinc-500' : 'text-zinc-100' }}">
            <span class="sm:hidden">{{ $f->homeTeam->short_name }}</span><span class="hidden sm:inline">{{ $f->homeTeam->name }}</span>
        </span>
        <x-team-badge :team="$f->homeTeam->name" :logo="$f->homeTeam->logo_url" size="md" />
    </div>
    <div class="shrink-0 w-14 text-center" data-fx-score>
        @if ($finished || $live)
            <span class="text-sm font-bold tabular-nums">{{ $f->home_score }} - {{ $f->away_score }}</span>
        @else
            <span class="text-xs text-zinc-500">vs</span>
        @endif
    </div>
    <div class="flex-1 flex items-center gap-2 min-w-0">
        <x-team-badge :team="$f->awayTeam->name" :logo="$f->awayTeam->logo_url" size="md" />
        <span class="truncate text-sm {{ $homeWon ? 'text-zinc-500' : 'text-zinc-100' }}">
            <span class="sm:hidden">{{ $f->awayTeam->short_name }}</span><span class="hidden sm:inline">{{ $f->awayTeam->name }}</span>
        </span>
    </div>
</a>