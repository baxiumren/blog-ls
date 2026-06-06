@props(['title', 'rows', 'valueKey', 'team'])

<div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
    <h2 class="text-sm font-semibold mb-3">{{ $title }}</h2>
    @if ($rows->isNotEmpty())
        <div class="space-y-3">
            @foreach ($rows as $r)
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-zinc-800 overflow-hidden shrink-0">
                        @if ($r['photo'])<img src="{{ $r['photo'] }}" alt="{{ $r['name'] }}" class="w-full h-full object-cover" />@endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-sm text-zinc-100 truncate">{{ $r['name'] }}</div>
                        <div class="text-[11px] text-zinc-500 flex items-center gap-1 min-w-0">
                            <x-team-badge :team="$team->name" :logo="$team->logo_url" size="sm" />
                            <span class="truncate">{{ $team->name }}</span>
                        </div>
                    </div>
                    <span class="shrink-0 text-xs font-bold text-white bg-blue-600 px-2.5 py-1 rounded-full min-w-[2.25rem] text-center">{{ $r[$valueKey] }}</span>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-zinc-500 text-sm">No data.</p>
    @endif
</div>