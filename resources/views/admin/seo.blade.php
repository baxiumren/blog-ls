@extends('admin.layout')
@section('title', 'Sitemap & SEO')

@section('content')
    <div class="max-w-3xl">
        <div class="mb-5">
            <h1 class="text-xl font-bold">Sitemap &amp; SEO</h1>
            <p class="text-sm text-zinc-500 mt-0.5">Auto-generated sitemap and robots.txt for search engines.</p>
        </div>

        @if (session('ok'))
            <div class="mb-4 bg-green-500/10 border border-green-500/30 text-green-400 text-sm px-4 py-3 rounded-lg flex items-center gap-2"><i class="fa-solid fa-circle-check"></i> {{ session('ok') }}</div>
        @endif

        {{-- Sitemap --}}
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-5 mb-4">
            <div class="flex items-start justify-between gap-4 mb-3">
                <div>
                    <h2 class="text-sm font-semibold flex items-center gap-2"><i class="fa-solid fa-sitemap text-blue-400"></i> Sitemap</h2>
                    <p class="text-xs text-zinc-500 mt-1">Auto-generated from every match, league, team, article and page. Updates automatically.</p>
                </div>
                <span class="text-[11px] px-2 py-0.5 rounded shrink-0 {{ $cached ? 'bg-green-500/15 text-green-400' : 'bg-zinc-800 text-zinc-400' }}">{{ $cached ? 'Cached' : 'Not cached yet' }}</span>
            </div>

            <div class="flex items-center justify-between gap-3 bg-zinc-950 border border-zinc-800 rounded-lg px-3 py-2.5 mb-3">
                <code class="text-xs text-zinc-300 truncate">{{ $base }}/sitemap.xml</code>
                <a href="/sitemap.xml" target="_blank" class="text-xs text-blue-400 hover:text-white whitespace-nowrap shrink-0">View <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a>
            </div>

            <div class="flex items-center justify-between">
                <span class="text-xs text-zinc-500">{{ $urlCount !== null ? number_format($urlCount) . ' URLs' : 'Open "View" once to generate' }}</span>
                <form method="POST" action="/admin/seo/sitemap-clear">
                    @csrf
                    <button class="text-xs bg-zinc-800 hover:bg-zinc-700 text-zinc-300 hover:text-white px-3 py-1.5 rounded-lg transition flex items-center gap-1.5"><i class="fa-solid fa-rotate"></i> Refresh sitemap</button>
                </form>
            </div>
        </div>

        {{-- Robots --}}
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-5">
            <h2 class="text-sm font-semibold flex items-center gap-2 mb-1"><i class="fa-solid fa-robot text-blue-400"></i> robots.txt</h2>
            <p class="text-xs text-zinc-500 mb-3">Already allows everything &amp; blocks /admin &amp; /install. Add your own rules below if needed.</p>

            <div class="flex items-center justify-between gap-3 bg-zinc-950 border border-zinc-800 rounded-lg px-3 py-2.5 mb-4">
                <code class="text-xs text-zinc-300 truncate">{{ $base }}/robots.txt</code>
                <a href="/robots.txt" target="_blank" class="text-xs text-blue-400 hover:text-white whitespace-nowrap shrink-0">View <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i></a>
            </div>

            <form method="POST" action="/admin/seo/robots">
                @csrf
                <label class="block text-xs text-zinc-400 mb-1">Custom rules <span class="text-zinc-600">(optional)</span></label>
                <textarea name="robots_custom" rows="4" placeholder="e.g.&#10;Disallow: /tips&#10;Crawl-delay: 5" class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm font-mono focus:outline-none focus:border-blue-500 transition placeholder-zinc-600">{{ $robotsCustom }}</textarea>
                <p class="text-[11px] text-zinc-600 mt-1">Added after the default rules, before the Sitemap line. Leave blank for defaults.</p>
                <button class="bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold px-4 py-2 rounded-lg transition mt-3">Save rules</button>
            </form>
        </div>

        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4 mt-4 text-xs text-zinc-500">
            <p><i class="fa-solid fa-circle-info"></i> Submit your sitemap to <b class="text-zinc-400">Google Search Console</b> for faster indexing. Verification code goes in Settings → SEO.</p>
        </div>
    </div>
@endsection
