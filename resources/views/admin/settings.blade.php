@extends('admin.layout')
@section('title', 'Settings')

@section('content')

<div class="flex flex-col lg:flex-row gap-4 items-start">
    {{-- Side tabs (sticky wrapper) --}}
    <div x-data="{ y: 0, sync() { this.y = window.innerWidth >= 1024 ? Math.max(0, window.scrollY - 40) : 0; } }"
        x-init="sync()" @scroll.window="sync()" @resize.window="sync()"
        :style="`transform: translateY(${y}px)`"
        class="w-full lg:w-60 shrink-0 self-start">
        <div class="mb-5">
            <h1 class="text-xl font-bold">Settings</h1>
            <p class="text-sm text-zinc-500 mt-5">Configure your site preferences.</p>
        </div>
            @php $sections = collect($groups)->groupBy('section', true); @endphp
            <div class="bg-zinc-900 rounded-xl border border-zinc-800 p-2 space-y-3">
                @foreach ($sections as $sectionName => $items)
                    <div>
                        <div class="text-[10px] font-semibold uppercase tracking-wider text-zinc-600 px-2 mb-1.5">{{ $sectionName }}</div>
                        <div class="flex lg:flex-col gap-1 overflow-x-auto [&::-webkit-scrollbar]:hidden">
                            @foreach ($items as $key => $g)
                                <a href="/admin/settings/{{ $key }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium whitespace-nowrap {{ $group === $key ? 'bg-blue-600 text-white' : 'text-zinc-400 hover:bg-zinc-800 hover:text-white' }} transition">
                                    <i class="{{ $g['icon'] }} fa-fw text-sm w-4 text-center shrink-0"></i>
                                    {{ $g['title'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Form --}}
        <form method="POST" action="/admin/settings/{{ $group }}" enctype="multipart/form-data" class="flex-1 min-w-0 space-y-4" x-data="{ loading: false }" @submit="loading = true">
            @csrf
            <div class="bg-zinc-900 rounded-xl border border-zinc-800 overflow-hidden">
                <div class="flex items-start gap-3 px-4 py-3 border-b border-zinc-800">
                    <span class="w-9 h-9 rounded-lg bg-blue-500/15 text-blue-400 flex items-center justify-center shrink-0"><i class="{{ $config['icon'] }} text-base"></i></span>
                    <div>
                        <h2 class="text-sm font-semibold">{{ $config['title'] }}</h2>
                        <p class="text-[11px] text-zinc-500">{{ $config['desc'] }}</p>
                    </div>
                </div>
                <div class="divide-y divide-zinc-800/50">
                    @foreach ($config['fields'] as $key => $field)
                        @php $val = $values[$key] ?? ''; $type = $field['type'] ?? 'text'; @endphp
                        <div class="p-4">
                            @if ($type === 'toggle')
                                <label class="flex items-center justify-between gap-4 cursor-pointer">
                                    <span>
                                        <span class="block text-sm">{{ $field['label'] }}</span>
                                        @if (! empty($field['hint']))<span class="block text-[11px] text-zinc-600 mt-0.5">{{ $field['hint'] }}</span>@endif
                                    </span>
                                    <span x-data="{ on: {{ $val === '1' ? 'true' : 'false' }} }" class="shrink-0">
                                        <input type="hidden" :value="on ? 1 : 0" name="{{ $key }}">
                                        <button type="button" @click="on = !on" :class="on ? 'bg-blue-600' : 'bg-zinc-700'" class="relative w-10 h-6 rounded-full transition">
                                            <span :class="on ? 'translate-x-4' : 'translate-x-0'" class="absolute top-1 left-1 w-4 h-4 rounded-full bg-white transition"></span>
                                        </button>
                                    </span>
                                </label>
                            @elseif ($type === 'file')
                                <label class="block text-xs text-zinc-400 mb-1">{{ $field['label'] }}</label>
                                <div class="flex items-start gap-4">
                                    <div class="flex-1 min-w-0">
                                        <input type="file" name="{{ $key }}" class="w-full text-xs text-zinc-400 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-zinc-700 file:text-white file:text-xs file:cursor-pointer bg-zinc-800 border border-zinc-700 rounded-lg p-1.5">
                                        @if ($val)
                                            <label class="flex items-center gap-2 text-[11px] text-red-400 mt-2 cursor-pointer">
                                                <input type="checkbox" name="{{ $key }}_delete" value="1" class="rounded bg-zinc-800 border-zinc-700"> Delete uploaded file
                                            </label>
                                        @endif
                                        @if (! empty($field['hint']))<p class="text-[11px] text-zinc-600 mt-1">{{ $field['hint'] }}</p>@endif
                                    </div>
                                    @if ($val)
                                        <div class="w-20 h-20 rounded-lg bg-zinc-800 border border-zinc-700 flex items-center justify-center overflow-hidden shrink-0">
                                            <img src="{{ asset('storage/' . $val) }}" alt="" class="max-w-full max-h-full object-contain">
                                        </div>
                                    @endif
                                </div>
                            @else
                                <label class="block text-xs text-zinc-400 mb-1">{{ $field['label'] }}</label>
                                @if ($type === 'textarea')
                                    <textarea name="{{ $key }}" rows="{{ ! empty($field['placeholder']) ? 5 : 4 }}" placeholder="{{ $field['placeholder'] ?? '' }}" class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm font-mono focus:outline-none focus:border-blue-500 transition placeholder-zinc-600">{{ $val }}</textarea>
                                @elseif ($type === 'select')
                                    <select name="{{ $key }}" class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500 transition">
                                        @foreach ($field['options'] as $ov => $ol)
                                            <option value="{{ $ov }}" {{ (string) $val === (string) $ov ? 'selected' : '' }}>{{ $ol }}</option>
                                        @endforeach
                                    </select>
                                @elseif ($type === 'secret')
                                    <input type="password" name="{{ $key }}" autocomplete="new-password" placeholder="{{ $val ? '•••••••• (leave blank to keep current)' : 'Paste your key' }}" class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm font-mono focus:outline-none focus:border-blue-500 transition">
                                @elseif ($type === 'color')
                                    <div x-data="{ c: '{{ $val ?: '#2563eb' }}', presets: ['#2563eb','#dc2626','#e11d48','#ea580c','#d97706','#16a34a','#059669','#0d9488','#0891b2','#4f46e5','#7c3aed','#9333ea','#db2777'] }">
                                        <div class="flex flex-wrap gap-2 mb-3">
                                            <template x-for="p in presets" :key="p">
                                                <button type="button" @click="c = p" :style="`background-color: ${p}`"
                                                    class="w-7 h-7 rounded-full border-2 transition"
                                                    :class="c.toLowerCase() === p ? 'border-white scale-110' : 'border-transparent hover:scale-110'"
                                                    :title="p"></button>
                                            </template>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <input type="color" name="{{ $key }}" x-model="c" class="w-10 h-10 rounded-lg bg-zinc-800 border border-zinc-700 cursor-pointer p-1">
                                            <input type="text" x-model="c" readonly class="bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2 text-sm text-zinc-300 w-28 font-mono">
                                        </div>
                                    </div>
                                @elseif ($type === 'number')
                                    <input type="number" name="{{ $key }}" value="{{ $val }}" class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition">
                                @else
                                    <input type="text" name="{{ $key }}" value="{{ $val }}" class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition">
                                @endif
                                @if (! empty($field['hint']) && $type !== 'file')<p class="text-[11px] text-zinc-600 mt-1">{{ $field['hint'] }}</p>@endif
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            <button type="submit" :disabled="loading" class="bg-blue-600 hover:bg-blue-500 disabled:opacity-60 disabled:cursor-not-allowed text-white font-semibold px-5 py-2.5 rounded-lg text-sm transition flex items-center gap-2">
                <svg x-show="loading" x-cloak class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                <span>Save changes</span>
            </button>
        </form>
    </div>
@endsection