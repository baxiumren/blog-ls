@props(['context' => null, 'league' => null, 'team' => null, 'limit' => 6, 'compact' => false])

@php
    $q = \App\Models\Article::published()->with('league');
    if ($league) $q->where('league_id', $league->id);
    if ($team) $q->where('team_id', $team->id);
    $articles = $q->take($limit)->get();
    if ($articles->isEmpty()) {
        $articles = \App\Models\Article::published()->take($limit)->get();
    }
    $real = $articles->isNotEmpty();

    // dummy (fallback kalau belum ada artikel terbit)
    $prefix = $context ? $context . ': ' : '';
    $dummy = array_slice([
        ['cat' => 'Preview', 'title' => $prefix . 'What to expect this week', 'time' => '2h ago'],
        ['cat' => 'Transfers', 'title' => $prefix . 'Who is on the move this window?', 'time' => '5h ago'],
        ['cat' => 'Analysis', 'title' => $prefix . 'Five talking points from the latest games', 'time' => '8h ago'],
        ['cat' => 'Team news', 'title' => $prefix . 'Injury update ahead of the weekend', 'time' => '12h ago'],
        ['cat' => 'Interview', 'title' => $prefix . 'Manager praises squad depth', 'time' => '1d ago'],
        ['cat' => 'Match report', 'title' => $prefix . 'How the latest fixtures unfolded', 'time' => '1d ago'],
    ], 0, $limit);
@endphp

@if ($compact)
    <div class="space-y-3">
        @if ($real)
            @foreach ($articles as $a)
                <a href="/news/{{ $a->slug }}" class="flex gap-3 group">
                    <div class="w-16 h-12 rounded-md bg-zinc-800 overflow-hidden shrink-0 flex items-center justify-center">
                        @if ($a->image)<img src="{{ asset('storage/' . $a->image) }}" alt="" loading="lazy" decoding="async" class="w-full h-full object-cover" />@else<span class="text-[9px] text-zinc-600">IMG</span>@endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-zinc-100 line-clamp-2 group-hover:text-white transition">{{ $a->title }}</p>
                        <span class="text-[11px] text-zinc-500">{{ $a->category }} · {{ $a->published_at->diffForHumans() }}</span>
                    </div>
                </a>
            @endforeach
        @else
            @foreach ($dummy as $n)
                <a href="#" class="flex gap-3 group">
                    <div class="w-16 h-12 rounded-md bg-zinc-800 shrink-0 flex items-center justify-center text-[9px] text-zinc-600">IMG</div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-zinc-100 line-clamp-2 group-hover:text-white transition">{{ $n['title'] }}</p>
                        <span class="text-[11px] text-zinc-500">{{ $n['cat'] }} · {{ $n['time'] }}</span>
                    </div>
                </a>
            @endforeach
        @endif
    </div>
@else
    <div class="grid sm:grid-cols-2 gap-x-6">
        @if ($real)
            @foreach ($articles as $a)
                <a href="/news/{{ $a->slug }}" class="flex gap-3 py-3 border-b border-zinc-800/60 hover:bg-zinc-800/30 transition group -mx-1 px-1 rounded">
                    <div class="min-w-0 flex-1 flex flex-col justify-center">
                        <span class="text-[10px] font-semibold uppercase tracking-wide text-blue-400 mb-1">{{ $a->category }}</span>
                        <p class="text-sm font-medium text-zinc-100 line-clamp-2 group-hover:text-white transition">{{ $a->title }}</p>
                        <span class="text-[11px] text-zinc-500 mt-1">{{ $a->published_at->diffForHumans() }}</span>
                    </div>
                    <div class="w-24 h-16 rounded-md bg-zinc-800 overflow-hidden shrink-0 flex items-center justify-center">
                        @if ($a->image)<img src="{{ asset('storage/' . $a->image) }}" alt="" loading="lazy" decoding="async" class="w-full h-full object-cover" />@else<span class="text-[10px] text-zinc-600">IMG</span>@endif
                    </div>
                </a>
            @endforeach
        @else
            @foreach ($dummy as $n)
                <a href="#" class="flex gap-3 py-3 border-b border-zinc-800/60 hover:bg-zinc-800/30 transition group -mx-1 px-1 rounded">
                    <div class="min-w-0 flex-1 flex flex-col justify-center">
                        <span class="text-[10px] font-semibold uppercase tracking-wide text-blue-400 mb-1">{{ $n['cat'] }}</span>
                        <p class="text-sm font-medium text-zinc-100 line-clamp-2 group-hover:text-white transition">{{ $n['title'] }}</p>
                        <span class="text-[11px] text-zinc-500 mt-1">{{ $n['time'] }}</span>
                    </div>
                    <div class="w-24 h-16 rounded-md bg-zinc-800 shrink-0 flex items-center justify-center text-[10px] text-zinc-600">IMG</div>
                </a>
            @endforeach
        @endif
    </div>
@endif