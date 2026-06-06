@props(['league'])

<div x-data="{ open: true }" class="bg-zinc-900 rounded-lg overflow-hidden mb-4">

    {{-- Judul liga (klik = buka/tutup) --}}
    <button type="button" @click="open = !open" class="w-full flex items-center gap-2.5 px-3.5 py-3 border-b border-zinc-800/70 hover:bg-zinc-800/50 transition">
        @if ($league->logo_url)
        <span class="w-6 h-6 rounded-full bg-white flex items-center justify-center shrink-0 p-0.5">
            <img src="{{ $league->logo_url }}" alt="{{ $league->name }}" class="w-full h-full object-contain" />
        </span>
        @else
        <span class="w-6 h-6 rounded-full {{ $league->color }} flex items-center justify-center text-[9px] font-bold shrink-0">
            {{ $league->code }}
        </span>
        @endif
        <span class="text-sm font-semibold truncate">{{ $league->name }}</span>
        <span class="ml-auto text-[11px] font-medium text-zinc-500 tabular-nums shrink-0">{{ count($league->fixtures) }}</span>
        <svg class="w-4 h-4 text-zinc-500 transition-transform duration-300 ease-out shrink-0" :class="open ? '' : '-rotate-90'"
            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    {{-- Daftar pertandingan (yang dibuka-tutup) --}}
    <div x-show="open" x-collapse.duration.400ms>
        <div class="divide-y divide-zinc-800">
            @foreach ($league->fixtures as $fixture)
                <x-match-card :fixture="$fixture" />
            @endforeach
        </div>
    </div>

</div>