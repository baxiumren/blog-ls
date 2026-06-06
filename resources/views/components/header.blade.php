<header class="bg-black border-b border-zinc-800 sticky top-0 z-50">
    <div class="max-w-6xl mx-auto px-4 h-14 flex items-center justify-between">

        {{-- Logo --}}
        <a href="/" class="flex items-center gap-2 shrink-0">
            @if (! empty($settings['logo']))
                <img src="{{ asset('storage/' . $settings['logo']) }}" alt="{{ $settings['site_name'] ?? 'Home' }}" class="h-8 w-auto">
            @else
                <span class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M13 2L3 14h7v8l10-12h-7z" />
                    </svg>
                </span>
                <span class="text-xl font-bold">{{ ! empty($settings['site_name']) ? $settings['site_name'] : 'LiveScore' }}</span>
            @endif
        </a>

        {{-- Menu tengah (Matches = aktif) --}}
        <nav class="hidden md:flex items-center gap-6 text-sm font-medium">
            <a href="/" class="{{ request()->is('/') ? 'text-white' : 'text-zinc-400 hover:text-white transition' }}">Matches</a>
            @if (($settings['news_enabled'] ?? '1') !== '0')
            <a href="/news" class="{{ request()->is('news') ? 'text-white' : 'text-zinc-400 hover:text-white transition' }}">News</a>
            @endif
            @if (($settings['show_tips'] ?? '1') !== '0')<a href="/tips" class="{{ request()->is('tips') ? 'text-white' : 'text-zinc-400 hover:text-white transition' }}">Tips</a>@endif
            <a href="/leagues" class="{{ request()->is('league*') ? 'text-white' : 'text-zinc-400 hover:text-white transition' }}">Leagues</a>
            @if (($settings['show_transfers'] ?? '1') !== '0')<a href="/transfers" class="{{ request()->is('transfers') ? 'text-white' : 'text-zinc-400 hover:text-white transition' }}">Transfers</a>@endif
            @if (($settings['highlights_enabled'] ?? '1') !== '0')<a href="/highlights" class="{{ request()->is('highlights') ? 'text-white' : 'text-zinc-400 hover:text-white transition' }}">Highlights</a>@endif
        </nav>

        {{-- Kanan: search (live dropdown) --}}
        <div x-data="searchBox()" @click.outside="open = false" @keydown.escape="open = false" class="relative">
            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-zinc-500 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="7" /><path d="M21 21l-4-4" />
            </svg>
            <input type="text" x-model="q"
                @input.debounce.250ms="search()"
                @focus="q.trim().length >= 2 && (open = true)"
                placeholder="Search teams, leagues"
                class="bg-zinc-900 border border-zinc-800 rounded-full text-sm pl-9 pr-4 py-1.5 w-36 sm:w-44 focus:w-56 focus:outline-none focus:border-blue-500 transition-all text-white placeholder-zinc-500">

            {{-- Dropdown hasil --}}
            <div x-show="open" x-cloak
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="absolute right-0 mt-2 w-64 sm:w-72 bg-zinc-900 border border-zinc-800 rounded-xl shadow-xl overflow-hidden z-50 max-h-96 overflow-y-auto">

                <template x-if="loading">
                    <div class="px-4 py-3 text-sm text-zinc-500">Searching…</div>
                </template>

                <template x-for="item in results" :key="item.type + '-' + item.id">
                    <a :href="item.url" class="flex items-center gap-3 px-3 py-2.5 hover:bg-zinc-800 transition">
                        <span class="w-7 h-7 flex items-center justify-center shrink-0">
                            <img :src="item.logo" alt="" class="w-full h-full object-contain" x-show="item.logo" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="text-sm text-white truncate" x-text="item.name"></div>
                            <div class="text-[10px] uppercase tracking-wide text-zinc-500" x-text="item.label"></div>
                        </div>
                    </a>
                </template>

                <template x-if="!loading && results.length === 0">
                    <div class="px-4 py-3 text-sm text-zinc-500">No results found</div>
                </template>
            </div>
        </div>
    </div>
</header>