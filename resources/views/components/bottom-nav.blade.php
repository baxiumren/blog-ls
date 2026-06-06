<nav class="lg:hidden fixed bottom-0 inset-x-0 z-50 bg-black border-t border-zinc-800">
    <div class="flex items-center justify-around h-16">

        {{-- Matches (aktif) --}}
        <a href="/" class="flex flex-col items-center gap-1 {{ request()->is('/') ? 'text-blue-500' : 'text-zinc-500 hover:text-white transition' }}">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="9" /><path d="M12 3v18M3 12h18" />
            </svg>
            <span class="text-[10px] font-medium">Matches</span>
        </a>

        @if (($settings['news_enabled'] ?? '1') !== '0')
        {{-- News --}}
        <a href="/news" class="flex flex-col items-center gap-1 {{ request()->is('news') ? 'text-blue-500' : 'text-zinc-500 hover:text-white transition' }}">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path d="M4 5h16v14H4z" /><path d="M8 9h8M8 13h8M8 17h5" />
            </svg>
            <span class="text-[10px] font-medium">News</span>
        </a>
        @endif

        {{-- Leagues --}}
        <a href="/leagues" class="flex flex-col items-center gap-1 {{ request()->is('league*') ? 'text-blue-500' : 'text-zinc-500 hover:text-white transition' }}">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path d="M6 4h12v4a6 6 0 01-12 0z" /><path d="M9 14h6M10 18h4M8 21h8" />
            </svg>
            <span class="text-[10px] font-medium">Leagues</span>
        </a>

    </div>
</nav>