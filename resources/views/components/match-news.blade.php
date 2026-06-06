@props(['fixture'])

@php
    // DUMMY sementara — nanti diganti berita asli dari CMS (Tahap 2),
    // di-tag ke tim/match ini. Strukturnya udah siap.
    $news = [
        ['title' => 'Preview: ' . $fixture->homeTeam->name . ' vs ' . $fixture->awayTeam->name . ' — what to expect', 'time' => '2h ago'],
        ['title' => 'Predicted lineups & team news ahead of the match', 'time' => '5h ago'],
        ['title' => 'Key player to watch from ' . $fixture->homeTeam->name . ' & ' . $fixture->awayTeam->name, 'time' => '8h ago'],
    ];
@endphp

<div class="mt-6">
    <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-500 mb-3">Related News</h2>
    <div class="space-y-3">
        @foreach ($news as $n)
            <a href="#" class="flex gap-3 bg-zinc-900 rounded-lg p-3 hover:bg-zinc-800 transition group">
                <div class="w-24 h-16 rounded-md bg-zinc-800 shrink-0 flex items-center justify-center text-[10px] text-zinc-600">IMG</div>
                <div class="min-w-0 flex flex-col justify-center">
                    <p class="text-sm font-medium text-zinc-100 line-clamp-2 group-hover:text-white">{{ $n['title'] }}</p>
                    <span class="text-[11px] text-zinc-500 mt-1">{{ $n['time'] }}</span>
                </div>
            </a>
        @endforeach
    </div>
</div>