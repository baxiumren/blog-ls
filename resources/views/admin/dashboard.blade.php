@extends('admin.layout')
@section('title', 'Dashboard')

@section('content')
    {{-- Welcome banner --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-700 to-blue-500 p-6 mb-5">
        <div class="absolute -right-10 -top-10 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">Welcome back, {{ auth()->user()->name }} 👋</h1>
                <p class="text-blue-100/80 text-sm mt-1">Here's what's happening with your site today.</p>
            </div>
            <div class="flex items-center gap-5 sm:gap-6 text-center">
                <div><div class="text-2xl font-bold tabular-nums">{{ number_format($totalViews) }}</div><div class="text-[11px] text-blue-100/70">Total views</div></div>
                <div class="w-px h-8 bg-white/20"></div>
                <div><div class="text-2xl font-bold tabular-nums">{{ number_format($viewsToday) }}</div><div class="text-[11px] text-blue-100/70">Today</div></div>
                <div class="w-px h-8 bg-white/20"></div>
                <div><div class="text-2xl font-bold tabular-nums">{{ $totalArticles }}</div><div class="text-[11px] text-blue-100/70">Articles</div></div>
            </div>
        </div>
    </div>

    {{-- Quick actions --}}
    <div class="flex flex-wrap gap-2 mb-4">
        <a href="/admin/articles/create" class="bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold px-4 py-2 rounded-lg transition flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg> New article
        </a>
        <a href="/admin/pages/create" class="bg-zinc-900 border border-zinc-800 hover:bg-zinc-800 text-sm font-medium px-4 py-2 rounded-lg transition">+ New page</a>
        <a href="/admin/leagues" class="bg-zinc-900 border border-zinc-800 hover:bg-zinc-800 text-sm font-medium px-4 py-2 rounded-lg transition">Manage leagues</a>
        <a href="/admin/settings/general" class="bg-zinc-900 border border-zinc-800 hover:bg-zinc-800 text-sm font-medium px-4 py-2 rounded-lg transition">Settings</a>
    </div>

    {{-- Stat cards --}}
    @php
        $stats = [
            ['Total views', number_format($totalViews), 'blue', 'M15 12a3 3 0 11-6 0 3 3 0 016 0z|M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z', null, null],
            ['Views today', number_format($viewsToday), 'green', 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6', null, $todayTrend],
            ['Unique today', number_format($uniqueToday), 'purple', 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-5a4 4 0 11-8 0 4 4 0 018 0zm6 3a3 3 0 10-2-5.3', null, null],
            ['Articles', $totalArticles . ' · ' . $publishedArticles . ' live', 'amber', 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', '/admin/articles', null],
        ];
        $colors = ['blue' => 'bg-blue-500/15 text-blue-400', 'green' => 'bg-green-500/15 text-green-400', 'purple' => 'bg-purple-500/15 text-purple-400', 'amber' => 'bg-amber-500/15 text-amber-400'];
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-4">
        @foreach ($stats as [$lbl, $val, $color, $icon, $link, $trend])
            <div class="bg-zinc-900 rounded-xl p-4 border border-zinc-800">
                <div class="flex items-start justify-between">
                    <span class="w-9 h-9 rounded-lg {{ $colors[$color] }} flex items-center justify-center"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">@foreach (explode('|', $icon) as $p)<path stroke-linecap="round" stroke-linejoin="round" d="{{ $p }}" />@endforeach</svg></span>
                    @if (! is_null($trend))<span class="text-[11px] font-medium {{ $trend >= 0 ? 'text-green-400' : 'text-red-400' }}">{{ $trend >= 0 ? '↑' : '↓' }} {{ abs($trend) }}%</span>@elseif ($link)<a href="{{ $link }}" class="text-[11px] text-blue-400 hover:text-white transition">Manage</a>@endif
                </div>
                <div class="text-xl font-bold tabular-nums mt-3">{{ $val }}</div>
                <div class="text-xs text-zinc-500 mt-0.5">{{ $lbl }}</div>
            </div>
        @endforeach
    </div>

    {{-- Content library --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-4">
        @foreach ($library as [$lbl, $val, $link])
            <a @if ($link) href="{{ $link }}" @endif class="bg-zinc-900 rounded-xl border border-zinc-800 p-3 block @if ($link) hover:border-zinc-700 @endif transition">
                <div class="text-lg font-bold tabular-nums">{{ number_format($val) }}</div>
                <div class="text-[11px] text-zinc-500">{{ $lbl }}</div>
            </a>
        @endforeach
    </div>

    <div class="grid lg:grid-cols-3 gap-4 items-start">
        {{-- Recent articles table --}}
        <div class="lg:col-span-2 bg-zinc-900 rounded-xl border border-zinc-800 overflow-hidden">
            <div class="flex items-center justify-between p-4 border-b border-zinc-800">
                <h2 class="text-sm font-semibold">Recent articles</h2>
                <a href="/admin/articles" class="text-xs text-blue-400 hover:text-white transition">View all</a>
            </div>
            <div class="overflow-x-auto [&::-webkit-scrollbar]:hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-[10px] uppercase tracking-wide text-zinc-500 border-b border-zinc-800">
                            <th class="text-left font-medium px-4 py-2 w-8">#</th>
                            <th class="text-left font-medium px-2 py-2">Article</th>
                            <th class="text-left font-medium px-2 py-2 hidden sm:table-cell w-24">Date</th>
                            <th class="text-center font-medium px-2 py-2 w-16">Views</th>
                            <th class="text-right font-medium px-4 py-2 w-24">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800/60">
                        @forelse ($recentArticles as $i => $a)
                            <tr class="hover:bg-zinc-800/30 transition">
                                <td class="px-4 py-2.5 text-zinc-500 tabular-nums">{{ $i + 1 }}</td>
                                <td class="px-2 py-2.5">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="w-10 h-7 rounded bg-zinc-800 overflow-hidden shrink-0">@if ($a->image)<img src="{{ asset('storage/' . $a->image) }}" alt="" class="w-full h-full object-cover" />@endif</div>
                                        <div class="min-w-0">
                                            <div class="font-medium truncate max-w-[180px]">{{ $a->title }}</div>
                                            <div class="text-[11px] text-zinc-500 truncate">{{ $a->slug }}@if (! $a->published_at) <span class="text-amber-400">· draft</span>@endif</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-2 py-2.5 text-zinc-500 text-xs hidden sm:table-cell">{{ $a->created_at->format('d M Y') }}</td>
                                <td class="px-2 py-2.5 text-center"><span class="text-xs text-zinc-400 tabular-nums">{{ number_format($a->views) }}</span></td>
                                <td class="px-4 py-2.5">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="/admin/articles/{{ $a->id }}/edit" class="w-7 h-7 rounded-md bg-zinc-800 hover:bg-zinc-700 flex items-center justify-center text-zinc-400 hover:text-white transition" title="Edit"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg></a>
                                        <a href="/news/{{ $a->slug }}" target="_blank" class="w-7 h-7 rounded-md bg-zinc-800 hover:bg-zinc-700 flex items-center justify-center text-zinc-400 hover:text-white transition" title="View"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg></a>
                                        <form method="POST" action="/admin/articles/{{ $a->id }}" onsubmit="return confirm('Delete this article?')">
                                            @csrf @method('DELETE')
                                            <button class="w-7 h-7 rounded-md bg-zinc-800 hover:bg-red-500/20 flex items-center justify-center text-zinc-400 hover:text-red-400 transition" title="Delete"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-10 text-center text-zinc-500">No articles yet. <a href="/admin/articles/create" class="text-blue-400 hover:text-white">Create one →</a></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Right column --}}
        <div class="space-y-4">
            {{-- API usage --}}
            @if (! empty($apiStatus['requests']))
                @php
                    $cur = $apiStatus['requests']['current'] ?? 0;
                    $lim = $apiStatus['requests']['limit_day'] ?? 0;
                    $pct = $lim > 0 ? min(100, round($cur / $lim * 100)) : 0;
                    $plan = $apiStatus['subscription']['plan'] ?? '—';
                    $bar = $pct > 90 ? 'bg-red-500' : ($pct > 70 ? 'bg-amber-500' : 'bg-green-500');
                @endphp
                <div class="bg-zinc-900 rounded-xl p-4 border border-zinc-800">
                    <div class="flex items-center justify-between mb-1">
                        <h2 class="text-sm font-semibold flex items-center gap-2"><i class="fa-solid fa-server text-zinc-500"></i> API usage</h2>
                        <span class="text-[10px] bg-blue-500/15 text-blue-400 px-2 py-0.5 rounded">{{ $plan }}</span>
                    </div>
                    <div class="flex items-end justify-between mb-2">
                        <div class="text-2xl font-bold tabular-nums">{{ number_format($cur) }}<span class="text-sm text-zinc-500 font-normal"> / {{ number_format($lim) }}</span></div>
                        <div class="text-xs text-zinc-500">{{ $pct }}%</div>
                    </div>
                    <div class="h-2 bg-zinc-800 rounded-full overflow-hidden">
                        <div class="h-full {{ $bar }}" style="width: {{ $pct }}%"></div>
                    </div>
                    <p class="text-[11px] text-zinc-600 mt-2">Requests used today · resets daily · auto-updates every 10 min.</p>
                </div>
            @endif
            {{-- Visitors chart --}}
            <div class="bg-zinc-900 rounded-xl p-4 border border-zinc-800">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold">Visitors</h2>
                    <div class="flex items-center gap-2">
                        @if (! is_null($weekTrend))<span class="text-[11px] font-medium {{ $weekTrend >= 0 ? 'text-green-400' : 'text-red-400' }}">{{ $weekTrend >= 0 ? '↑' : '↓' }} {{ abs($weekTrend) }}%</span>@endif
                        <span class="text-xs text-zinc-500">{{ number_format($weekTotal) }} · 7d</span>
                    </div>
                </div>
                @php $maxC = max(1, collect($days)->max('count')); @endphp
                <div class="flex items-end justify-between gap-1.5 h-32">
                    @foreach ($days as $d)
                        <div x-data="{ h: false }" @mouseenter="h = true" @mouseleave="h = false" class="relative flex-1 flex flex-col items-center justify-end h-full gap-1.5 group">
                            <div x-show="h" x-cloak class="absolute -top-1 bg-zinc-950 border border-zinc-700 rounded px-1.5 py-0.5 text-[10px] font-semibold z-10">{{ $d['count'] }}</div>
                            <div class="w-full max-w-[1.75rem] rounded-t bg-gradient-to-t from-blue-700/60 to-blue-500 group-hover:to-blue-400 transition-all" style="height: {{ max(2, round($d['count'] / $maxC * 100)) }}%"></div>
                            <span class="text-[9px] text-zinc-500">{{ $d['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Traffic sources --}}
            <div class="bg-zinc-900 rounded-xl p-4 border border-zinc-800">
                <h2 class="text-sm font-semibold mb-3">Traffic sources</h2>
                @php $maxR = max(1, $referrers->max() ?? 1); @endphp
                <div class="space-y-2.5">
                    @forelse ($referrers as $host => $count)
                        <div>
                            <div class="flex items-center justify-between gap-2 text-xs mb-1">
                                <span class="text-zinc-300 truncate">{{ $host }}</span>
                                <span class="text-zinc-500 tabular-nums shrink-0">{{ number_format($count) }}</span>
                            </div>
                            <div class="h-1.5 rounded-full bg-zinc-800 overflow-hidden"><div class="h-full bg-purple-500 rounded-full" style="width: {{ round($count / $maxR * 100) }}%"></div></div>
                        </div>
                    @empty
                        <p class="text-zinc-500 text-xs py-2">No referrer data yet.</p>
                    @endforelse
                </div>
            </div>

            {{-- Most-read articles --}}
            <div class="bg-zinc-900 rounded-xl p-4 border border-zinc-800">
                <h2 class="text-sm font-semibold mb-3">Most-read articles</h2>
                <div class="space-y-3">
                    @forelse ($topArticles as $i => $row)
                        <a href="/news/{{ $row['article']->slug }}" target="_blank" class="flex items-center gap-3 group">
                            <span class="text-sm font-bold text-zinc-600 w-4 tabular-nums shrink-0">{{ $i + 1 }}</span>
                            <div class="w-10 h-7 rounded bg-zinc-800 overflow-hidden shrink-0">@if ($row['article']->image)<img src="{{ asset('storage/' . $row['article']->image) }}" alt="" class="w-full h-full object-cover" />@endif</div>
                            <p class="text-sm truncate flex-1 group-hover:text-white transition">{{ $row['article']->title }}</p>
                            <span class="text-xs text-zinc-500 tabular-nums shrink-0">{{ number_format($row['views']) }}</span>
                        </a>
                    @empty
                        <p class="text-zinc-500 text-xs py-2">No reads yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection