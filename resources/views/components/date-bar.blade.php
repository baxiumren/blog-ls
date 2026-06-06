@props(['selected' => null])

@php
$selected = $selected ?? now();
$today = now()->toDateString();
$prev = $selected->copy()->subDay()->toDateString();
$next = $selected->copy()->addDay()->toDateString();

// 5 hari di sekitar tanggal terpilih
$days = [];
for ($i = -2; $i <= 2; $i++) {
    $date = $selected->copy()->addDays($i);
    $days[] = [
        'url'    => $date->toDateString(),
        'label'  => $date->toDateString() === $today ? 'Today' : $date->format('D d'),
        'active' => $i === 0,
    ];
}
@endphp

<div class="flex items-center gap-1 bg-zinc-900 rounded-lg p-1 mb-4 w-fit max-w-full mx-auto">

{{-- Panah hari sebelumnya --}}
<a href="/?date={{ $prev }}" aria-label="Previous day"
    class="px-2 py-2 text-zinc-400 hover:text-white hover:bg-zinc-800 rounded-md transition shrink-0">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
    </svg>
</a>

{{-- Chip hari --}}
<div class="flex items-center gap-1 flex-1 overflow-x-auto">
    @foreach ($days as $day)
        <a href="/?date={{ $day['url'] }}" class="px-4 py-2 text-sm font-medium rounded-md whitespace-nowrap transition
            {{ $loop->first || $loop->last ? 'hidden sm:block' : '' }}
            {{ $day['active'] ? 'bg-blue-600 text-white' : 'text-zinc-400 hover:text-white hover:bg-zinc-800' }}">
            {{ $day['label'] }}
        </a>
    @endforeach
</div>

{{-- Tombol kalender + dropdown animatif (Alpine) --}}
<div x-data="calendar('{{ $selected->toDateString() }}')" class="relative shrink-0">

    {{-- Tombol pemicu --}}
    <button type="button" @click="open = !open" aria-label="Pick date"
            class="px-2 py-2 text-zinc-400 hover:text-white hover:bg-zinc-800 rounded-md transition">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
    </button>

    {{-- Panel kalender --}}
    <div x-show="open" x-cloak @click.outside="open = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
         class="absolute right-0 mt-2 z-50 w-72 bg-zinc-900 border border-zinc-800 rounded-xl shadow-2xl p-3">

        {{-- Header: ganti bulan --}}
        <div class="flex items-center justify-between mb-3">
            <button type="button" @click="prevMonth()" class="p-1.5 rounded-lg text-zinc-400 hover:text-white hover:bg-zinc-800 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <span class="text-sm font-semibold" x-text="monthLabel"></span>
            <button type="button" @click="nextMonth()" class="p-1.5 rounded-lg text-zinc-400 hover:text-white hover:bg-zinc-800 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>

        {{-- Nama hari --}}
        <div class="grid grid-cols-7 gap-1 mb-1">
            <template x-for="d in ['Su','Mo','Tu','We','Th','Fr','Sa']" :key="d">
                <span class="text-center text-[10px] font-medium text-zinc-500 py-1" x-text="d"></span>
            </template>
        </div>

        {{-- Grid tanggal --}}
        <div class="space-y-1">
            <template x-for="(week, wi) in weeks" :key="wi">
                <div class="grid grid-cols-7 gap-1">
                    <template x-for="(day, di) in week" :key="di">
                        <div>
                            <button type="button" x-show="day" @click="pick(day)"
                                    x-text="day ? day.getDate() : ''"
                                    :class="isSelected(day) ? 'bg-blue-600 text-white' : (isToday(day) ? 'bg-zinc-800 text-blue-400 font-semibold' : 'text-zinc-300 hover:bg-zinc-800')"
                                    class="w-full aspect-square flex items-center justify-center text-xs rounded-lg transition"></button>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>
</div>

{{-- Panah hari berikutnya --}}
<a href="/?date={{ $next }}" aria-label="Next day"
    class="px-2 py-2 text-zinc-400 hover:text-white hover:bg-zinc-800 rounded-md transition shrink-0">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
    </svg>
</a>
</div>