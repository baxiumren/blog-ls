@props(['fixture', 'team'])

@php
    $f = $fixture;
    $home = $f->homeTeam;
    $away = $f->awayTeam;
    $isHome = $f->home_team_id === $team->id;
    $finished = $f->status === 'finished';
    $live = $f->status === 'live';

    // hasil dari sudut pandang tim ini (buat warna skor)
    $res = null;
    if ($finished) {
        $gf = $isHome ? $f->home_score : $f->away_score;
        $ga = $isHome ? $f->away_score : $f->home_score;
        $res = $gf > $ga ? 'W' : ($gf < $ga ? 'L' : 'D');
    }
    $pill = $res === 'W' ? 'bg-green-600 text-white'
          : ($res === 'L' ? 'bg-red-600 text-white'
          : ($res === 'D' ? 'bg-zinc-600 text-white' : ''));

    // pemenang → redupkan yang kalah
    $homeWon = $finished && $f->home_score > $f->away_score;
    $awayWon = $finished && $f->away_score > $f->home_score;
@endphp

<a href="/match/{{ $f->id }}" class="flex items-center gap-2 sm:gap-3 py-3 px-3 sm:px-4 hover:bg-zinc-800/40 transition">
    {{-- Tanggal --}}
    <div class="w-12 sm:w-16 shrink-0 text-[11px] leading-tight text-zinc-500">
        {{ $f->kickoff_at->format('D, M d') }}
    </div>

    {{-- Kandang (nama + logo) --}}
    <div class="flex-1 flex items-center justify-end gap-2 min-w-0">
        <span class="truncate text-sm text-right {{ $awayWon ? 'text-zinc-500' : 'text-zinc-100' }}">
            <span class="sm:hidden">{{ $home->short_name }}</span>
            <span class="hidden sm:inline">{{ $home->name }}</span>
        </span>
        <x-team-badge :team="$home->name" :logo="$home->logo_url" size="md" />
    </div>

    {{-- Skor / waktu --}}
    <div class="shrink-0 w-16 flex justify-center">
        @if ($finished)
            <span class="px-2 py-1 rounded text-sm font-bold tabular-nums {{ $pill }}">{{ $f->home_score }} - {{ $f->away_score }}</span>
        @elseif ($live)
            <span class="px-2 py-1 rounded text-sm font-bold tabular-nums bg-zinc-700 text-white">{{ $f->home_score }} - {{ $f->away_score }}</span>
        @else
            <span class="px-2 py-1 rounded text-xs font-semibold text-zinc-300 bg-zinc-800">{{ $f->kickoff_at->format('H:i') }}</span>
        @endif
    </div>

    {{-- Tandang (logo + nama) --}}
    <div class="flex-1 flex items-center gap-2 min-w-0">
        <x-team-badge :team="$away->name" :logo="$away->logo_url" size="md" />
        <span class="truncate text-sm {{ $homeWon ? 'text-zinc-500' : 'text-zinc-100' }}">
            <span class="sm:hidden">{{ $away->short_name }}</span>
            <span class="hidden sm:inline">{{ $away->name }}</span>
        </span>
    </div>

    {{-- Kompetisi (desktop aja) --}}
    <div class="hidden md:flex items-center gap-1.5 w-32 shrink-0 justify-end text-xs text-zinc-500 min-w-0">
        <span class="truncate text-right">{{ $f->league->name }}</span>
        @if ($f->league->logo_url)
            <span class="w-4 h-4 rounded-full bg-white flex items-center justify-center shrink-0 p-0.5">
                <img src="{{ $f->league->logo_url }}" alt="" class="w-full h-full object-contain" />
            </span>
        @endif
    </div>
</a>