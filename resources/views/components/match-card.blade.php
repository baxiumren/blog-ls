@props(['fixture'])

@php
    $finished = $fixture->status === 'finished';
    $homeLost = $finished && $fixture->home_score < $fixture->away_score;
    $awayLost = $finished && $fixture->away_score < $fixture->home_score;

    // Hasil tiap tim: W menang, L kalah, D seri (cuma kalau udah selesai)
    $homeResult = $finished ? ($fixture->home_score > $fixture->away_score ? 'W' : ($fixture->home_score < $fixture->away_score ? 'L' : 'D')) : null;
    $awayResult = $finished ? ($fixture->away_score > $fixture->home_score ? 'W' : ($fixture->away_score < $fixture->home_score ? 'L' : 'D')) : null;

    // Warna badge sesuai hasil
    $resultColor = fn ($r) => $r === 'W' ? 'bg-green-600 text-white' : ($r === 'L' ? 'bg-red-600 text-white' : 'bg-zinc-600 text-zinc-200');
@endphp

<a href="/match/{{ $fixture->id }}" class="flex items-center gap-4 px-3 py-2.5 rounded-lg hover:bg-zinc-800 transition">

    {{-- Status / waktu (kolom kiri) --}}
    <div class="w-12 shrink-0 text-center">
        @if ($fixture->status === 'live')
            <div class="flex items-center justify-center gap-1">
                <span class="relative flex h-1.5 w-1.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-500 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-red-500"></span>
                </span>
                <span class="text-xs font-bold text-green-500">{{ $fixture->minute }}'</span>
            </div>
        @elseif ($fixture->status === 'finished')
            <div class="flex flex-col items-center leading-tight">
                <span class="text-xs text-zinc-400">{{ $fixture->kickoff_at->format('H:i') }}</span>
                <span class="text-[10px] font-medium text-zinc-500">FT</span>
            </div>
        @else
            <span class="text-xs text-zinc-400">{{ $fixture->kickoff_at->format('H:i') }}</span>
        @endif
    </div>

    {{-- Dua tim --}}
    <div class="flex-1 space-y-1.5">

        {{-- Tim tuan rumah --}}
        <div class="flex items-center justify-between {{ $homeLost ? 'opacity-50' : '' }}">
            <div class="flex items-center gap-2">
                <x-team-badge :team="$fixture->homeTeam->name" :logo="$fixture->homeTeam->logo_url" />
                <span class="text-sm text-zinc-100">{{ $fixture->homeTeam->name }}</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-sm font-semibold w-4 text-right">{{ $fixture->home_score }}</span>
                @if ($finished)
                    <span class="w-4 h-4 flex items-center justify-center rounded text-[10px] font-bold {{ $resultColor($homeResult) }}">{{ $homeResult }}</span>
                @endif
            </div>
        </div>

        {{-- Tim tamu --}}
        <div class="flex items-center justify-between {{ $awayLost ? 'opacity-50' : '' }}">
            <div class="flex items-center gap-2">
                <x-team-badge :team="$fixture->awayTeam->name" :logo="$fixture->awayTeam->logo_url" />
                <span class="text-sm text-zinc-100">{{ $fixture->awayTeam->name }}</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-sm font-semibold w-4 text-right">{{ $fixture->away_score }}</span>
                @if ($finished)
                    <span class="w-4 h-4 flex items-center justify-center rounded text-[10px] font-bold {{ $resultColor($awayResult) }}">{{ $awayResult }}</span>
                @endif
            </div>
        </div>

    </div>
</a>