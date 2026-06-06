@props(['fixtures', 'name' => 'fixture_id', 'selected' => null])

@php
    $sel = $selected !== null && $selected !== '' ? (int) $selected : null;
    $options = $fixtures->map(fn ($f) => [
        'id'    => $f->id,
        'label' => $f->homeTeam->name . ' vs ' . $f->awayTeam->name . ' — ' . $f->kickoff_at->format('d M Y · H:i'),
    ])->values();
    $selectedLabel = optional($options->firstWhere('id', $sel))['label'] ?? '';
@endphp

<div x-data="matchPicker(@js($options), @js($sel), @js($selectedLabel))" @click.outside="open = false" class="relative">
    <input type="hidden" name="{{ $name }}" :value="selectedId">
    <div class="relative">
        <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-zinc-500 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7" /><path d="M21 21l-4-4" /></svg>
        <input type="text" x-model="query" @focus="open = true" placeholder="Search match by team…" class="w-full bg-zinc-800 border border-zinc-700 rounded-lg pl-9 pr-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition placeholder-zinc-500">
    </div>
    <div x-show="open" x-cloak class="absolute z-20 mt-1 w-full max-h-64 overflow-y-auto bg-zinc-800 border border-zinc-700 rounded-lg shadow-xl [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar-thumb]:bg-zinc-600 [&::-webkit-scrollbar-thumb]:rounded-full">
        <template x-for="opt in filtered" :key="opt.id">
            <button type="button" @click="select(opt)" class="block w-full text-left px-3 py-2 text-sm hover:bg-zinc-700 transition" :class="opt.id === selectedId ? 'text-blue-400 bg-zinc-700/40' : 'text-zinc-200'" x-text="opt.label"></button>
        </template>
        <div x-show="filtered.length === 0" x-cloak class="px-3 py-3 text-sm text-zinc-500 text-center">No match found.</div>
    </div>
</div>