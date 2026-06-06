@extends('layouts.app')

@section('title', $p['name'])
@section('description', $p['name'] . ' stats — goals, assists, appearances, ratings and career history' . ($primary ? ' for ' . $primary['team'] : '') . '. Full player profile on LiveScore.')
@section('schema')
<script type="application/ld+json">{!! json_encode(array_filter([
    '@'.'context' => 'https://schema.org',
    '@type' => 'Person',
    'name' => $p['name'],
    'image' => $p['photo'] ?? null,
    'nationality' => $p['nationality'] ?? null,
    'jobTitle' => 'Footballer',
    'url' => url()->current(),
    'affiliation' => $primary ? ['@type' => 'SportsTeam', 'name' => $primary['team']] : null,
]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endsection
@section('og_image', $p['photo'] ?? '')
@section('no-left', 'yes')
@section('no-right', 'yes')

@section('content')
    {{-- Header --}}
    <div class="rounded-lg p-4 sm:p-6 flex flex-wrap items-center gap-4 mb-3 sm:mb-4 {{ $teamColor ? '' : 'bg-zinc-900' }}"
         @if ($teamColor) style="background-color: #{{ $teamColor }};" @endif>
        <span class="w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-zinc-800 overflow-hidden shrink-0">
            @if (! empty($p['photo']))<img src="{{ $p['photo'] }}" alt="{{ $p['name'] }}" class="w-full h-full object-cover" />@endif
        </span>
        <div class="min-w-0 flex-1">
                        <h1 class="text-xl sm:text-3xl font-bold truncate {{ $darkText ? 'text-zinc-900' : 'text-white' }}">{{ $p['name'] }}</h1>{{ $p['name'] }}</h1>
            @if ($primary)
                <div class="mt-1 flex items-center gap-2 text-sm {{ $darkText ? 'text-zinc-800' : 'text-zinc-100' }}">
                    @if ($teamLocalId)<a href="/team/{{ $teamLocalId }}" class="flex items-center gap-2 hover:text-white transition">@else<span class="flex items-center gap-2">@endif
                        @if ($primary['teamLogo'])<img src="{{ $primary['teamLogo'] }}" alt="" class="w-5 h-5 object-contain" />@endif
                        <span>{{ $primary['team'] }}</span>
                    @if ($teamLocalId)</a>@else</span>@endif
                </div>
            @endif
        </div>
        @if ($summary['rating'])
            <div class="shrink-0 text-center">
                <div class="text-2xl font-bold px-3 py-1 rounded-lg {{ $summary['rating'] >= 7 ? 'bg-green-600' : ($summary['rating'] >= 6 ? 'bg-amber-600' : 'bg-red-600') }} text-white tabular-nums">{{ $summary['rating'] }}</div>
                <div class="text-[10px] text-zinc-500 mt-1">Season rating</div>
            </div>
        @endif
    </div>

    <div class="grid lg:grid-cols-3 gap-3 sm:gap-4 items-start">

        {{-- ===== KIRI ===== --}}
        <div class="lg:col-span-2 space-y-3 sm:space-y-4">

            {{-- Bio --}}
            <div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-px bg-zinc-800 rounded-lg overflow-hidden">
                    @php
                        $bioCells = [
                            ['Height', $bio['height'] ?? '—'],
                            ['Weight', $bio['weight'] ?? '—'],
                            ['Shirt', $bio['number'] ? '#' . $bio['number'] : '—'],
                            ['Age', ($bio['age'] ?? '—') . ($bio['birth'] ? ' · ' . \Illuminate\Support\Carbon::parse($bio['birth'])->format('d M Y') : '')],
                            ['Country', $bio['country'] ?? '—'],
                            ['Position', $bio['position'] ?? '—'],
                        ];
                    @endphp
                    @foreach ($bioCells as [$lbl, $val])
                        <div class="bg-zinc-900 p-3">
                            <div class="text-[11px] uppercase tracking-wide text-zinc-500 mb-1">{{ $lbl }}</div>
                            <div class="text-sm font-semibold truncate">{{ $val }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Season summary --}}
            <div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-semibold">{{ $season }}/{{ str_pad(($season + 1) % 100, 2, '0', STR_PAD_LEFT) }} season</h2>
                    @if ($seasonsList->count() > 1)
                    <select onchange="if(this.value)window.location='?season='+this.value"
                            class="bg-zinc-800 border border-zinc-700 rounded-lg text-xs px-2 py-1 text-white focus:outline-none focus:border-blue-500 cursor-pointer">
                        @foreach ($seasonsList as $s)
                            <option value="{{ $s }}" {{ $s == $season ? 'selected' : '' }}>{{ $s }}/{{ str_pad(($s + 1) % 100, 2, '0', STR_PAD_LEFT) }}</option>
                        @endforeach
                    </select>
                    @endif
                </div>
                <div class="grid grid-cols-4 gap-3">
                    @foreach ([['Goals', $summary['goals']], ['Assists', $summary['assists']], ['Started', $summary['started']], ['Matches', $summary['matches']], ['Minutes', $summary['minutes']], ['Rating', $summary['rating'] ?? '—'], ['Yellow', $summary['yellow']], ['Red', $summary['red']]] as [$lbl, $val])
                        <div class="text-center">
                            <div class="text-lg sm:text-xl font-bold tabular-nums">{{ is_numeric($val) ? number_format($val) : $val }}</div>
                            <div class="text-[10px] uppercase tracking-wide text-zinc-500 mt-0.5">{{ $lbl }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Recent matches --}}
            @if ($recentMatches->isNotEmpty())
            <div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
                <h2 class="text-sm font-semibold mb-3">Recent matches</h2>
                <div class="overflow-x-auto [&::-webkit-scrollbar]:hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-[10px] uppercase tracking-wide text-zinc-500 border-b border-zinc-800">
                                <th class="font-medium text-left pb-2">Opponent</th>
                                <th class="font-medium text-center pb-2 w-10">Res</th>
                                <th class="font-medium text-center pb-2 w-12">Min</th>
                                <th class="font-medium text-center pb-2 w-8">G</th>
                                <th class="font-medium text-center pb-2 w-8">A</th>
                                <th class="font-medium text-center pb-2 w-14">Rating</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-800/60">
                            @foreach ($recentMatches as $m)
                                <tr class="hover:bg-zinc-800/60 transition cursor-pointer" onclick="window.location='/match/{{ $m['id'] }}'">
                                    <td class="py-2.5">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="text-[10px] text-zinc-500 w-10 shrink-0">{{ $m['date']->format('d M') }}</span>
                                            @if ($m['oppLogo'])<img src="{{ $m['oppLogo'] }}" alt="" class="w-5 h-5 object-contain shrink-0" />@endif
                                            <span class="truncate">{{ $m['opp'] }}</span>
                                            <span class="text-[11px] text-zinc-500 shrink-0 tabular-nums">{{ $m['score'] }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center"><span class="inline-block w-5 h-5 rounded text-[10px] font-bold leading-5 text-white {{ $m['res'] === 'W' ? 'bg-green-600' : ($m['res'] === 'L' ? 'bg-red-600' : 'bg-zinc-600') }}">{{ $m['res'] }}</span></td>
                                    <td class="text-center text-zinc-400 tabular-nums">{{ $m['minutes'] }}'</td>
                                    <td class="text-center tabular-nums {{ $m['goals'] > 0 ? 'font-semibold' : 'text-zinc-400' }}">{{ $m['goals'] }}</td>
                                    <td class="text-center tabular-nums {{ $m['assists'] > 0 ? 'font-semibold' : 'text-zinc-400' }}">{{ $m['assists'] }}</td>
                                    <td class="text-center">
                                        @if ($m['rating'])<span class="inline-block px-1.5 py-0.5 rounded text-white text-xs font-semibold tabular-nums {{ $m['rating'] >= 7 ? 'bg-green-600' : ($m['rating'] >= 6 ? 'bg-amber-600' : 'bg-red-600') }}">{{ $m['rating'] }}</span>@else<span class="text-zinc-600">—</span>@endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Detailed stats --}}
            <div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
                <h2 class="text-sm font-semibold mb-3">Statistics <span class="text-zinc-500 font-normal">· all competitions</span></h2>
                <div class="space-y-4">
                    @foreach ($statGroups as $group => $items)
                        <div>
                            <div class="text-[11px] uppercase tracking-wide text-zinc-500 mb-1.5">{{ $group }}</div>
                            <div>
                                @foreach ($items as [$lbl, $val])
                                    <div class="flex justify-between py-1.5 text-sm border-b border-zinc-800/60 last:border-0">
                                        <span class="text-zinc-400">{{ $lbl }}</span>
                                        <span class="font-semibold tabular-nums">{{ $val === null ? '—' : (is_numeric($val) ? number_format($val) : $val) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Per competition --}}
            @if ($rows->isNotEmpty())
            <div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
                <h2 class="text-sm font-semibold mb-3">By competition</h2>
                <div class="overflow-x-auto [&::-webkit-scrollbar]:hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-[10px] uppercase tracking-wide text-zinc-500 border-b border-zinc-800">
                                <th class="font-medium text-left pb-2">Competition</th>
                                <th class="font-medium text-center pb-2 w-12">Apps</th>
                                <th class="font-medium text-center pb-2 w-12">Goals</th>
                                <th class="font-medium text-center pb-2 w-14 hidden sm:table-cell">Assists</th>
                                <th class="font-medium text-center pb-2 w-14">Rating</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-800/60">
                            @foreach ($rows as $r)
                                <tr class="hover:bg-zinc-800/60 transition">
                                    <td class="py-2.5">
                                        <div class="flex items-center gap-2 min-w-0">
                                            @if ($r['leagueLogo'])<span class="w-5 h-5 rounded-full bg-white flex items-center justify-center shrink-0 p-0.5"><img src="{{ $r['leagueLogo'] }}" alt="" class="w-full h-full object-contain" /></span>@endif
                                            <span class="truncate">{{ $r['league'] }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center text-zinc-400 tabular-nums">{{ $r['apps'] }}</td>
                                    <td class="text-center font-semibold tabular-nums">{{ $r['goals'] }}</td>
                                    <td class="text-center text-zinc-400 tabular-nums hidden sm:table-cell">{{ $r['assists'] }}</td>
                                    <td class="text-center tabular-nums">
                                        @if ($r['rating'])<span class="font-semibold {{ $r['rating'] >= 7 ? 'text-green-400' : ($r['rating'] >= 6 ? 'text-amber-400' : 'text-red-400') }}">{{ $r['rating'] }}</span>@else<span class="text-zinc-600">—</span>@endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
            {{-- About --}}
            @if (! empty($about))
            <div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
                <h2 class="text-sm font-semibold mb-2">About</h2>
                <div class="space-y-2 text-sm text-zinc-400 leading-relaxed">
                    @foreach ($about as $para)
                        <p>{{ $para }}</p>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- ===== KANAN ===== --}}
        <div class="space-y-3 sm:space-y-4">

            {{-- Career --}}
            @if ($career->isNotEmpty())
            <div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
                <h2 class="text-sm font-semibold mb-3">Career</h2>
                <div class="divide-y divide-zinc-800/60">
                    @foreach ($career as $c)
                        <div class="flex items-center gap-3 py-2.5">
                            <span class="w-7 h-7 flex items-center justify-center shrink-0">
                                @if ($c['logo'])<img src="{{ $c['logo'] }}" alt="" class="w-full h-full object-contain" />@endif
                            </span>
                            <span class="text-sm flex-1 min-w-0 truncate">{{ $c['team'] }}</span>
                            <span class="text-xs text-zinc-500 shrink-0 tabular-nums">{{ $c['from'] }}@if ($c['to'] != $c['from'])–{{ $c['to'] }}@endif</span>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Trophies --}}
            @if ($trophyWins->isNotEmpty() || $trophyRunner->isNotEmpty())
            <div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
                <h2 class="text-sm font-semibold mb-3">Trophies</h2>
                @php $winGroups = $trophyWins->groupBy('league'); @endphp
                <div class="space-y-2.5">
                    @foreach ($winGroups as $league => $items)
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-amber-400 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M5 3h14v2h2v3a4 4 0 01-4 4h-.3a5 5 0 01-3.7 2.9V18h3v2H8v-2h3v-3.1A5 5 0 017.3 12H7a4 4 0 01-4-4V5h2V3zm0 4v1a2 2 0 002 2V7H5zm14 0h-2v3a2 2 0 002-2V7z"/></svg>
                            <div class="min-w-0 flex-1">
                                <div class="text-sm truncate">{{ $league }}</div>
                                <div class="text-[11px] text-zinc-500 truncate">{{ $items->pluck('season')->implode(' · ') }}</div>
                            </div>
                            <span class="text-sm font-bold text-amber-400 shrink-0 tabular-nums">{{ $items->count() }}</span>
                        </div>
                    @endforeach
                </div>
                @if ($trophyRunner->isNotEmpty())
                    <div class="mt-3 pt-3 border-t border-zinc-800">
                        <div class="text-[10px] uppercase tracking-wide text-zinc-500 mb-2">Runner-up</div>
                        <div class="space-y-1.5">
                            @foreach ($trophyRunner->groupBy('league') as $league => $items)
                                <div class="flex items-center justify-between gap-2 text-xs">
                                    <span class="text-zinc-400 truncate">{{ $league }}</span>
                                    <span class="text-zinc-500 shrink-0 tabular-nums">{{ $items->count() }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
            @endif
        </div>
    </div>
@endsection