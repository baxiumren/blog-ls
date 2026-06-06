@php
    // Dummy sementara — diganti data asli dari DB pas Tahap 2 (News)
    $news = [
        ['title' => 'Transfer window heats up as clubs prepare summer bids', 'time' => '2h'],
        ['title' => 'World Cup 2026: the groups to watch this summer', 'time' => '5h'],
        ['title' => 'Manager rumours: who is on the hot seat right now?', 'time' => '8h'],
    ];
@endphp

<h3 class="text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-3">Latest News</h3>

<div class="space-y-3">
    @foreach ($news as $item)
        <a href="#" class="flex gap-3 group">
            <div class="w-16 h-12 rounded-md bg-zinc-800 shrink-0"></div>
            <div class="min-w-0">
                <p class="text-sm text-zinc-200 leading-snug line-clamp-2 group-hover:text-white">{{ $item['title'] }}</p>
                <span class="text-[11px] text-zinc-500">{{ $item['time'] }} ago</span>
            </div>
        </a>
    @endforeach
</div>