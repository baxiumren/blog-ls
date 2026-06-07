<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') - LiveScore</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-zinc-950 text-white min-h-screen" x-data="{ sidebar: false, userMenu: false }">

    {{-- Backdrop mobile --}}
    <div x-show="sidebar" x-cloak @click="sidebar = false" x-transition.opacity class="fixed inset-0 bg-black/60 z-40 md:hidden"></div>

    {{-- Sidebar --}}
    <aside class="fixed top-0 z-50 w-64 bg-zinc-900 border-r border-zinc-800 h-screen flex flex-col transition-transform duration-200 md:translate-x-0"
           :class="sidebar ? 'translate-x-0' : '-translate-x-full'">
        <div class="h-14 flex items-center gap-2.5 px-4 border-b border-zinc-800 shrink-0">
            <span class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center">
                <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M13 2L3 14h7v8l10-12h-7z" /></svg>
            </span>
            <div class="leading-tight">
                <div class="font-bold text-sm">LiveScore</div>
                <div class="text-[10px] text-zinc-500">Admin Panel</div>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto p-3 text-sm [&::-webkit-scrollbar]:hidden">
            @php
                $groups = [
                    'Main' => [
                        ['Dashboard', '/admin', 'admin', 'fa-solid fa-gauge-high'],
                        ['Articles', '/admin/articles', 'admin/articles*', 'fa-solid fa-newspaper'],
                        ['Predictions', '/admin/predictions', 'admin/predictions*', 'fa-solid fa-lightbulb', false, false, 'show_tips'],
                        ['Highlights', '/admin/highlights', 'admin/highlights*', 'fa-solid fa-circle-play', false, false, 'highlights_enabled'],
                        ['Match of Day', '/admin/motd', 'admin/motd*', 'fa-solid fa-star', false, false, 'motd_enabled'],
                        ['Comments', '/admin/comments', 'admin/comments*', 'fa-solid fa-comments', false, false, 'comments_enabled'],
                    ],
                    'Management' => [
                        ['Users', '/admin/users', 'admin/users*', 'fa-solid fa-users', false, true],
                        ['Settings', '/admin/settings/general', 'admin/settings*', 'fa-solid fa-gear', false, true],
                        ['Pages', '/admin/pages', 'admin/pages*', 'fa-solid fa-file-lines'],
                        ['Leagues', '/admin/leagues', 'admin/leagues*', 'fa-solid fa-trophy', false, true],
                        ['Subscribers', '/admin/subscribers', 'admin/subscribers*', 'fa-solid fa-envelope', false, true, 'newsletter_enabled'],
                        ['Newsletter', '/admin/newsletter', 'admin/newsletter*', 'fa-solid fa-paper-plane', false, true, 'newsletter_enabled'],
                    ],
                    'System' => [
                        ['Cron health', '/admin/cron', 'admin/cron*', 'fa-solid fa-heart-pulse', false, true],
                        ['Cache', '/admin/cache', 'admin/cache*', 'fa-solid fa-database', false, true],
                        ['Health', '/admin/health', 'admin/health*', 'fa-solid fa-stethoscope', false, true],
                        ['Domains', '/admin/domains', 'admin/domains*', 'fa-solid fa-globe', false, true],
                        ['Logs', '/admin/logs', 'admin/logs*', 'fa-solid fa-file-lines', false, true],
                        ['Sitemap & SEO', '/admin/seo', 'admin/seo*', 'fa-solid fa-sitemap', false, true],
                    ],
                    'Account' => [
                        ['View site', '/', '', 'fa-solid fa-arrow-up-right-from-square', true],
                    ],
                ];
            @endphp
            @foreach ($groups as $label => $items)
                <div class="text-[10px] font-semibold uppercase tracking-wider text-zinc-600 px-3 mt-4 mb-1 first:mt-0">{{ $label }}</div>
                @foreach ($items as $item)
                    @php [$name, $url, $active, $icon] = $item; $ext = $item[4] ?? false; $adminOnly = $item[5] ?? false; $gate = $item[6] ?? null; @endphp
                    @if ((! $adminOnly || auth()->user()->isAdmin()) && (! $gate || ($settings[$gate] ?? '1') !== '0'))
                        <a href="{{ $url }}" @if ($ext) target="_blank" @endif
                        class="flex items-center gap-3 px-3 py-2 rounded-lg {{ $active && request()->is($active) ? 'bg-blue-600 text-white' : 'text-zinc-400 hover:bg-zinc-800 hover:text-white' }} transition">
                        <i class="{{ $icon }} fa-fw text-base w-5 text-center shrink-0"></i>
                            {{ $name }}
                        </a>
                    @endif
                @endforeach
            @endforeach
        </nav>

        <form method="POST" action="/admin/logout" class="p-3 border-t border-zinc-800 shrink-0">
            @csrf
            <button class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-red-400 hover:bg-red-500/10 text-sm transition">
                <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                Logout
            </button>
        </form>
    </aside>

    {{-- Main area --}}
    <div class="md:ml-64">
        {{-- Top header --}}
        <header class="sticky top-0 z-30 bg-zinc-900/80 backdrop-blur border-b border-zinc-800 h-14 flex items-center gap-3 px-4">
            <button @click="sidebar = true" class="md:hidden text-zinc-300 hover:text-white transition">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
            </button>
            <div class="flex items-center gap-2 text-sm text-zinc-500">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                <span class="text-zinc-700">/</span>
                <span class="font-medium text-white">@yield('title', 'Dashboard')</span>
            </div>
            <div class="flex-1"></div>
            <a href="/admin/articles/create" class="hidden sm:flex bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                New article
            </a>
            <div class="relative" @click.outside="userMenu = false">
                <button @click="userMenu = !userMenu" class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-sm font-bold">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                    <div class="hidden sm:block text-left leading-tight">
                        <div class="text-xs font-semibold">{{ auth()->user()->name }}</div>
                        <div class="text-[10px] text-zinc-500">Admin</div>
                    </div>
                </button>
                <div x-show="userMenu" x-cloak x-transition class="absolute right-0 mt-2 w-48 bg-zinc-900 border border-zinc-800 rounded-xl shadow-xl overflow-hidden">
                    <div class="px-3 py-2 border-b border-zinc-800">
                        <div class="text-sm font-medium truncate">{{ auth()->user()->name }}</div>
                        <div class="text-[11px] text-zinc-500 truncate">{{ auth()->user()->email }}</div>
                    </div>
                    <a href="/admin/profile" class="block px-3 py-2 text-sm text-zinc-400 hover:bg-zinc-800 hover:text-white transition">My profile</a>
                    <a href="/" target="_blank" class="block px-3 py-2 text-sm text-zinc-400 hover:bg-zinc-800 hover:text-white transition">View site ↗</a>
                    <form method="POST" action="/admin/logout">
                        @csrf
                        <button class="w-full text-left px-3 py-2 text-sm text-red-400 hover:bg-red-500/10 transition">Logout</button>
                    </form>
                </div>
            </div>
        </header>

        <main class="p-4 sm:p-6">
            <div class="max-w-6xl mx-auto">
                @yield('content')
            </div>
        </main>
    </div>

    {{-- Toast (tetap) --}}
    @php $flash = session('ok') ? ['msg' => session('ok'), 'type' => 'success'] : (session('error') ? ['msg' => session('error'), 'type' => 'error'] : null); @endphp
    @if ($flash)
        <div x-data="{ show: true }" x-show="show" x-cloak x-init="setTimeout(() => show = false, 4000)"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed top-4 right-4 z-[60] max-w-xs">
            <div class="flex items-start gap-3 bg-zinc-900 border {{ $flash['type'] === 'error' ? 'border-red-500/40' : 'border-green-500/40' }} rounded-xl shadow-xl p-3">
                <span class="w-7 h-7 rounded-full {{ $flash['type'] === 'error' ? 'bg-red-500/15 text-red-400' : 'bg-green-500/15 text-green-400' }} flex items-center justify-center shrink-0">
                    @if ($flash['type'] === 'error')<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>@else<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>@endif
                </span>
                <p class="text-sm text-zinc-200 flex-1 pt-0.5">{{ $flash['msg'] }}</p>
                <button @click="show = false" class="text-zinc-500 hover:text-white transition shrink-0"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></button>
            </div>
        </div>
    @endif
</body>
</html>