@props(['league'])

<a href="/league/{{ $league->id }}" class="flex items-center gap-3 px-2 py-2 rounded-lg hover:bg-zinc-800 transition">
    @if ($league->logo_url)
        <span class="w-6 h-6 rounded-full bg-white flex items-center justify-center shrink-0 p-0.5">
            <img src="{{ $league->logo_url }}" alt="{{ $league->name }}" class="w-full h-full object-contain" />
        </span>
    @else
        <span class="w-6 h-6 rounded-full {{ $league->color }} flex items-center justify-center text-[10px] font-bold shrink-0">
            {{ $league->code }}
        </span>
    @endif
    <span class="flex-1 min-w-0 truncate text-sm text-zinc-200">{{ $league->name }}</span>
</a>