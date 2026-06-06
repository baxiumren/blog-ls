@props(['team', 'limit' => null])

@php
    // DUMMY — nanti diganti artikel asli dari CMS (Tahap 2), di-tag ke klub ini
    $news = [
        ['title' => $team->name . ' eye summer signing as transfer window heats up', 'cat' => 'Transfers', 'time' => '2h ago'],
        ['title' => 'Match preview: what to expect from ' . $team->name . ' this week', 'cat' => 'Preview', 'time' => '5h ago'],
        ['title' => $team->name . ' boss praises squad depth ahead of crucial run', 'cat' => 'Interview', 'time' => '8h ago'],
        ['title' => 'Injury update: key ' . $team->name . ' player nears return', 'cat' => 'Team news', 'time' => '12h ago'],
        ['title' => 'Five talking points from ' . $team->name . "'s recent form", 'cat' => 'Analysis', 'time' => '1d ago'],
    ];
    if ($limit) {
        $news = array_slice($news, 0, $limit);
    }
@endphp

<div class="space-y-3">
    @foreach ($news as $n)
        <a href="#" class="flex gap-3 bg-zinc-900 rounded-lg p-3 hover:bg-zinc-800 transition group">
            <div class="w-28 h-20 rounded-md bg-zinc-800 shrink-0 flex items-center justify-center text-[10px] text-zinc-600">IMG</div>
            <div class="min-w-0 flex flex-col justify-center">
                <span class="text-[10px] font-semibold uppercase tracking-wide text-blue-400 mb-1">{{ $n['cat'] }}</span>
                <p class="text-sm font-medium text-zinc-100 line-clamp-2 group-hover:text-white">{{ $n['title'] }}</p>
                <span class="text-[11px] text-zinc-500 mt-1">{{ $n['time'] }}</span>
            </div>
        </a>
    @endforeach
</div>