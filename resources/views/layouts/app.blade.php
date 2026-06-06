<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
    $siteName = ! empty($settings['site_name']) ? $settings['site_name'] : 'LiveScore';
    $titleSuffix = $settings['meta_title_suffix'] ?? '';
    $pageTitle = trim($__env->yieldContent('title'));
    $metaTitle = $pageTitle ? $pageTitle . $titleSuffix : $siteName . ' — Live Football Scores, Fixtures & Results';
    $themeColor = ! empty($settings['theme_color']) ? $settings['theme_color'] : '#2563eb';
    $metaDesc = trim($__env->yieldContent('description')) ?: ($settings['meta_description'] ?? 'Live football scores, fixtures, results, standings and team & player stats from the Premier League, La Liga, World Cup and more.');
    $metaTitle = str_replace('LiveScore', $siteName, $metaTitle);
    $metaDesc = str_replace('LiveScore', $siteName, $metaDesc);
    $faviconUrl = ! empty($settings['favicon']) ? asset('storage/' . $settings['favicon']) : '';
    $logoUrl = ! empty($settings['logo']) ? asset('storage/' . $settings['logo']) : '';
    $ogDefault = ! empty($settings['og_image']) ? asset('storage/' . $settings['og_image']) : '';
    $ogImage = trim($__env->yieldContent('og_image')) ?: $ogDefault;
    $pageRobots = trim($__env->yieldContent('robots')) ?: 'index, follow';
    @endphp
    <title>{{ $metaTitle }}</title>
    <meta name="description" content="{{ $metaDesc }}">
    <link rel="canonical" href="{{ \App\Models\Domain::to(request()->getPathInfo()) }}">
    <link rel="alternate" type="application/rss+xml" title="{{ $siteName }} RSS" href="/feed">
    <meta name="robots" content="{{ $pageRobots }}">
    @if (! empty($settings['google_verification']))<meta name="google-site-verification" content="{{ $settings['google_verification'] }}">@endif
    @if (! empty($settings['bing_verification']))<meta name="msvalidate.01" content="{{ $settings['bing_verification'] }}">@endif

    {{-- Open Graph (preview pas di-share) --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDesc }}">
    <meta property="og:url" content="{{ \App\Models\Domain::to(request()->getPathInfo()) }}">
    @if ($ogImage)<meta property="og:image" content="{{ $ogImage }}">@endif

    {{-- Twitter --}}
    <meta name="twitter:card" content="{{ $ogImage ? 'summary_large_image' : 'summary' }}">
    @if (! empty($settings['twitter_handle']))<meta name="twitter:site" content="{{ '@' . $settings['twitter_handle'] }}">@endif
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDesc }}">
    @if ($ogImage)<meta name="twitter:image" content="{{ $ogImage }}">@endif

    {{-- Structured data --}}
    <script type="application/ld+json">{!! json_encode([
        '@'.'context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => $siteName,
        'url' => \App\Models\Domain::activeBaseUrl(),
    ], JSON_UNESCAPED_SLASHES) !!}</script>
    <script type="application/ld+json">{!! json_encode(array_merge([
        '@'.'context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => $siteName,
        'url' => \App\Models\Domain::activeBaseUrl(),
    ], $logoUrl ? ['logo' => $logoUrl] : []), JSON_UNESCAPED_SLASHES) !!}</script>
    @yield('schema')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @php $customFont = $settings['font_family'] ?? ''; @endphp
    @if (! empty($customFont))
    <link href="https://fonts.googleapis.com/css2?family={{ str_replace(' ', '+', $customFont) }}:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>:root{ --font-sans: '{{ $customFont }}', ui-sans-serif, system-ui, sans-serif; } body{ font-family: var(--font-sans); }</style>
    @endif
    @if (! empty($settings['ga_id'] ?? null))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $settings['ga_id'] }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ $settings['ga_id'] }}');
    </script>
    @endif
    @if (! empty($settings['gtm_id']))
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','{{ $settings['gtm_id'] }}');</script>
    @endif
    @if (! empty($settings['adsense_client']))
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ $settings['adsense_client'] }}" crossorigin="anonymous"></script>
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="theme-color" content="{{ $themeColor }}">
    @if ($faviconUrl)<link rel="icon" href="{{ $faviconUrl }}">@endif
    <link rel="apple-touch-icon" href="/icon-192.png">
    @if (! empty($settings['accent_color']))
    <style>
    :root{
        --color-blue-600: {{ $settings['accent_color'] }};
        --color-blue-500: color-mix(in srgb, {{ $settings['accent_color'] }} 86%, #fff);
        --color-blue-400: color-mix(in srgb, {{ $settings['accent_color'] }} 70%, #fff);
        --color-blue-300: color-mix(in srgb, {{ $settings['accent_color'] }} 55%, #fff);
        --color-blue-700: color-mix(in srgb, {{ $settings['accent_color'] }} 82%, #000);
        --color-blue-800: color-mix(in srgb, {{ $settings['accent_color'] }} 66%, #000);
        --color-blue-900: color-mix(in srgb, {{ $settings['accent_color'] }} 52%, #000);
    }
    </style>
    @endif
    @if (! empty($settings['custom_css']))<style>{!! $settings['custom_css'] !!}</style>@endif
    @if (! empty($settings['head_code'])){!! $settings['head_code'] !!}@endif
</head>
<body class="bg-zinc-950 text-white min-h-screen pb-20 lg:pb-0">
    @if (! empty($settings['gtm_id']))
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $settings['gtm_id'] }}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    @endif
    {{-- Loading bar atas --}}
    <div id="loadbar" class="fixed top-0 left-0 h-0.5 bg-blue-500 z-[100]"
    style="width:0; opacity:0; transition: width 8s cubic-bezier(0.1,0.7,0.1,1), opacity 0.3s;"></div>

    {{-- Announcement bar --}}
    @php
    $annOn = ($settings['announcement_enabled'] ?? '0') === '1';
    $annMsg = trim($settings['announcement_message'] ?? '');
    @endphp
    @if ($annOn && $annMsg !== '')
        @php
            $annColor = $settings['announcement_color'] ?? 'blue';
            $annLink = trim($settings['announcement_link'] ?? '');
            $annLinkText = $settings['announcement_link_text'] ?? '';
            $annDismiss = ($settings['announcement_dismissible'] ?? '1') === '1';
            $annKey = 'ann-' . substr(md5($annMsg), 0, 8);
            $annBg = ['blue' => 'bg-blue-600', 'green' => 'bg-green-600', 'amber' => 'bg-amber-500', 'red' => 'bg-red-600'][$annColor] ?? 'bg-blue-600';
        @endphp
        <div @if ($annDismiss) x-data="{ show: localStorage.getItem('{{ $annKey }}') !== '1' }" x-show="show" x-cloak @endif
            class="{{ $annBg }} text-white text-sm">
            <div class="max-w-6xl mx-auto px-4 py-2 flex items-center justify-center gap-3 text-center">
                <span>📢 {{ $annMsg }}@if ($annLink !== '')<a href="{{ $annLink }}" class="underline font-semibold ml-1.5">{{ $annLinkText ?: 'Learn more' }}</a>@endif</span>
                @if ($annDismiss)
                    <button @click="show = false; localStorage.setItem('{{ $annKey }}', '1')" class="shrink-0 text-lg leading-none opacity-80 hover:opacity-100" aria-label="Close">&times;</button>
                @endif
            </div>
        </div>
    @endif

    {{-- ===== HEADER (nanti diganti <x-header />) ===== --}}
    <x-header />

    @php
    $noLeft  = \Illuminate\Support\Facades\View::hasSection('no-left');
    $noRight = \Illuminate\Support\Facades\View::hasSection('no-right');
    $mainSpan = ($noLeft && $noRight) ? 'lg:col-span-12' : (($noLeft || $noRight) ? 'lg:col-span-9' : 'lg:col-span-6');
    @endphp

    {{-- ===== BODY ===== --}}
    <div class="max-w-6xl mx-auto px-4 py-6 grid grid-cols-1 lg:grid-cols-12 gap-4">

        {{-- KIRI --}}
        @unless ($noLeft)
            <x-sidebar />
        @endunless

        {{-- TENGAH (lebar nyesuain ada/gak sidebar) --}}
        <main class="{{ $mainSpan }}">
            <x-ad-slot />
            @yield('content')
        </main>

        {{-- KANAN --}}
        @unless ($noRight)
            <aside class="hidden lg:block lg:col-span-3 space-y-4 h-fit">
                @hasSection('rightbar')
                    @yield('rightbar')
                @else
                    <div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
                                                <h2 class="text-sm font-semibold mb-3">Latest news</h2>
                        <x-news-list :limit="4" compact />
                    </div>
                    <div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
                        <x-standings />
                    </div>
                @endif
            </aside>
        @endunless

    </div>
        {{-- Footer --}}
        <footer class="border-t border-zinc-800 mt-8 bg-zinc-950">
            @php
                $footerLeagues = \Illuminate\Support\Facades\Cache::remember('footer.leagues', 3600, fn () => \App\Models\League::orderBy('priority')->orderBy('name')->take(8)->get(['id', 'name']));
                $siteName = ! empty($settings['site_name']) ? $settings['site_name'] : 'LiveScore';
            @endphp
            @if (($settings['newsletter_enabled'] ?? '1') !== '0')
            <div class="border-b border-zinc-800/60">
                <div class="max-w-6xl mx-auto px-4 py-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div>
                        <div class="font-semibold">📧 Stay updated</div>
                        <p class="text-sm text-zinc-500">Get the latest scores, news and tips in your inbox.</p>
                    </div>
                    <x-newsletter />
                </div>
            </div>
            @endif
            <div class="max-w-6xl mx-auto px-4 py-8 grid grid-cols-2 md:grid-cols-4 gap-6 text-sm">
                {{-- Brand --}}
                <div class="col-span-2 md:col-span-1">
                    @if (! empty($settings['logo']))
                        <img src="{{ asset('storage/' . $settings['logo']) }}" alt="{{ $siteName }}" class="h-8 w-auto mb-1">
                    @else
                        <div class="font-bold text-white text-base">{{ $siteName }}</div>
                    @endif
                    @if (! empty($settings['site_tagline']))<p class="text-zinc-500 text-xs mt-1">{{ $settings['site_tagline'] }}</p>@endif
                    <div class="flex items-center gap-3 mt-3">
                        @if (! empty($settings['telegram_url']))<a href="{{ $settings['telegram_url'] }}" target="_blank" rel="noopener" class="text-zinc-400 hover:text-white transition" title="Telegram">Telegram</a>@endif
                        @if (! empty($settings['twitter_url']))<a href="{{ $settings['twitter_url'] }}" target="_blank" rel="noopener" class="text-zinc-400 hover:text-white transition" title="Twitter">Twitter</a>@endif
                        @if (! empty($settings['instagram_url']))<a href="{{ $settings['instagram_url'] }}" target="_blank" rel="noopener" class="text-zinc-400 hover:text-white transition" title="Instagram">Instagram</a>@endif
                        @if (! empty($settings['facebook_url']))<a href="{{ $settings['facebook_url'] }}" target="_blank" rel="noopener" class="text-zinc-400 hover:text-white transition" title="Facebook">Facebook</a>@endif
                        @if (! empty($settings['youtube_url']))<a href="{{ $settings['youtube_url'] }}" target="_blank" rel="noopener" class="text-zinc-400 hover:text-white transition" title="YouTube">YouTube</a>@endif
                        @if (! empty($settings['tiktok_url']))<a href="{{ $settings['tiktok_url'] }}" target="_blank" rel="noopener" class="text-zinc-400 hover:text-white transition" title="TikTok">TikTok</a>@endif
                    </div>
                </div>

                {{-- Competitions (bonus SEO internal linking) --}}
                <div>
                    <div class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500 mb-2.5">Competitions</div>
                    <ul class="space-y-1.5">
                        @foreach ($footerLeagues as $fl)
                            <li><a href="/league/{{ $fl->id }}" class="text-zinc-400 hover:text-white transition">{{ $fl->name }}</a></li>
                        @endforeach
                    </ul>
                </div>

                {{-- Quick links --}}
                <div>
                    <div class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500 mb-2.5">Explore</div>
                    <ul class="space-y-1.5">
                        <li><a href="/" class="text-zinc-400 hover:text-white transition">Matches</a></li>
                        @if (($settings['news_enabled'] ?? '1') !== '0')
                        <li><a href="/news" class="text-zinc-400 hover:text-white transition">News</a></li>
                        @endif
                        @if (($settings['show_tips'] ?? '1') !== '0')<li><a href="/tips" class="text-zinc-400 hover:text-white transition">Tips</a></li>@endif
                        <li><a href="/leagues" class="text-zinc-400 hover:text-white transition">Leagues</a></li>
                        @if (($settings['show_transfers'] ?? '1') !== '0')<li><a href="/transfers" class="text-zinc-400 hover:text-white transition">Transfers</a></li>@endif
                        @if (($settings['highlights_enabled'] ?? '1') !== '0')<li><a href="/highlights" class="text-zinc-400 hover:text-white transition">Highlights</a></li>@endif
                    </ul>
                </div>

                {{-- Company --}}
                <div>
                    <div class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500 mb-2.5">Company</div>
                    <ul class="space-y-1.5">
                        <li><a href="/page/about" class="text-zinc-400 hover:text-white transition">About</a></li>
                        <li><a href="/page/contact" class="text-zinc-400 hover:text-white transition">Contact</a></li>
                        <li><a href="/page/privacy-policy" class="text-zinc-400 hover:text-white transition">Privacy Policy</a></li>
                        <li><a href="/page/terms" class="text-zinc-400 hover:text-white transition">Terms</a></li>
                    </ul>
                </div>
            </div>

            {{-- Bottom bar --}}
            <div class="border-t border-zinc-800/60">
                <div class="max-w-6xl mx-auto px-4 py-4 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-zinc-600">
                    <span>© {{ now()->year }} {{ $siteName }}. All rights reserved.</span>
                    <span>{{ $settings['footer_text'] ?? 'Scores & data for informational purposes only.' }}</span>
                </div>
            </div>
        </footer>

            {{-- Cookie consent --}}
    @php
    $cookieMsg  = $settings['cookie_message'] ?? '';
    $cookieBtn  = $settings['cookie_button'] ?? '';
    $cookieLink = $settings['cookie_link_text'] ?? '';
    $cLayout = $settings['cookie_layout'] ?? 'box';
    $cPosY = $settings['cookie_position_y'] ?? 'bottom';
    $cPosX = $settings['cookie_position_x'] ?? 'right';
    $cDefaultMsg = 'We use cookies to improve your experience and for analytics & ads. By using this site, you agree to our policy.';
    if ($cLayout === 'bar') {
        $cWrap = 'inset-x-0 ' . ($cPosY === 'top' ? 'top-0' : 'bottom-16 sm:bottom-0');
    } else {
        $cy = $cPosY === 'top' ? 'top-4' : 'bottom-16 sm:bottom-4';
        $cx = $cPosX === 'left' ? 'sm:left-4' : ($cPosX === 'center' ? 'sm:left-1/2 sm:-translate-x-1/2' : 'sm:right-4');
        $cWrap = "inset-x-0 sm:inset-x-auto $cy $cx sm:max-w-sm";
    }
    @endphp
    @if (($settings['cookie_enabled'] ?? '1') !== '0')
    <div x-data="{ ok: localStorage.getItem('cookieOk') === '1' }" x-show="!ok" x-cloak
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
        class="fixed z-[55] {{ $cWrap }}">
        @if ($cLayout === 'bar')
            <div class="bg-zinc-900/95 backdrop-blur border-y border-zinc-700 shadow-xl px-4 py-3">
                <div class="max-w-5xl mx-auto flex flex-col sm:flex-row items-center gap-3">
                    <p class="text-sm text-zinc-300 leading-relaxed flex-1 text-center sm:text-left">🍪 {{ $cookieMsg ?: $cDefaultMsg }}</p>
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="/page/privacy-policy" class="text-sm text-zinc-400 hover:text-white px-3 py-2 transition whitespace-nowrap">{{ $cookieLink ?: 'Privacy Policy' }}</a>
                        <button @click="ok = true; localStorage.setItem('cookieOk', '1')" class="bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold px-5 py-2 rounded-lg transition whitespace-nowrap">{{ $cookieBtn ?: 'Accept' }}</button>
                    </div>
                </div>
            </div>
        @else
            <div class="m-3 sm:m-0 bg-zinc-900 border border-zinc-700 rounded-xl shadow-xl p-4">
                <p class="text-sm text-zinc-300 leading-relaxed">🍪 {{ $cookieMsg ?: $cDefaultMsg }}</p>
                <div class="flex items-center gap-2 mt-3">
                    <button @click="ok = true; localStorage.setItem('cookieOk', '1')" class="flex-1 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">{{ $cookieBtn ?: 'Accept' }}</button>
                    <a href="/page/privacy-policy" class="text-sm text-zinc-400 hover:text-white px-3 py-2 transition whitespace-nowrap">{{ $cookieLink ?: 'Privacy Policy' }}</a>
                </div>
            </div>
        @endif
    </div>
    @endif
    
    <x-bottom-nav />
    @if (! empty($settings['custom_js']))<script>{!! $settings['custom_js'] !!}</script>@endif

</body>
</html>