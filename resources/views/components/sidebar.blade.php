@php
    $all = \App\Models\League::orderBy('id')->get();
    $byCode = $all->keyBy('code');

    // Liga unggulan buat "Top leagues" (urutan = prioritas tampil)
    $topCodes = ['WC', 'PL', 'CL', 'LL', 'SA', 'BL', 'L1'];
    $top = collect($topCodes)->map(fn ($c) => $byCode[$c] ?? null)->filter();
@endphp

<div class="hidden lg:block lg:col-span-3 space-y-6 bg-zinc-900 rounded-lg p-3 sm:p-4 h-fit">

    {{-- Favourites --}}
    <div>
        <h3 class="text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-2 px-2">Favourites</h3>
        <p class="text-zinc-600 text-sm px-2">No favourites yet</p>
    </div>

    {{-- Top leagues --}}
    <div>
        <h3 class="text-xs font-semibold uppercase tracking-wide text-zinc-500 mb-2 px-2">Top leagues</h3>
        <ul class="space-y-1">
            @foreach ($top as $league)
                <li><x-league-item :league="$league" /></li>
            @endforeach
        </ul>
    </div>

    {{-- All leagues (collapse, default TERTUTUP) --}}
    <details class="group">
        <summary class="flex items-center justify-between cursor-pointer list-none px-2 py-2 rounded-lg hover:bg-zinc-800 transition [&::-webkit-details-marker]:hidden">
            <span class="text-xs font-semibold uppercase tracking-wide text-zinc-500">All leagues</span>
            <svg class="w-4 h-4 text-zinc-500 transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </summary>
        <ul class="space-y-1 mt-1">
            @foreach ($all as $league)
                <li><x-league-item :league="$league" /></li>
            @endforeach
        </ul>
    </details>

</div>