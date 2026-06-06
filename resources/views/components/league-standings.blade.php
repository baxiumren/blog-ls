@props(['name', 'rows', 'teamIdMap', 'showForm' => false])

@php
    $zoneColor = function ($desc) {
        if (! $desc) return null;
        $d = strtolower($desc);
        if (str_contains($d, 'possible')) return ['bg-yellow-500', 'Possible qualification'];
        if (str_contains($d, 'relegation')) return ['bg-red-500', 'Relegation'];
        if (str_contains($d, 'champions')) return ['bg-blue-500', 'Champions League'];
        if (str_contains($d, 'europa')) return ['bg-orange-500', 'Europa League'];
        if (str_contains($d, 'conference')) return ['bg-teal-500', 'Conference League'];
        if (str_contains($d, 'qualification') || str_contains($d, 'promotion') || str_contains($d, 'next stage')) return ['bg-green-500', 'Qualification'];
        return ['bg-zinc-500', ucfirst($desc)];
    };
    $zones = collect($rows)->map(fn ($r) => $zoneColor($r['description'] ?? null))->filter()->unique(fn ($z) => $z[1])->values();
@endphp

<div class="bg-zinc-900 rounded-lg p-3 sm:p-4">
    <h2 class="text-sm font-semibold mb-3">{{ $name }}</h2>
    <div class="overflow-x-auto [&::-webkit-scrollbar]:hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-[10px] uppercase tracking-wide text-zinc-500 border-b border-zinc-800">
                    <th class="font-medium text-left pb-2 pl-2 w-8">#</th>
                    <th class="font-medium text-left pb-2">Team</th>
                    <th class="font-medium text-center pb-2 w-8">P</th>
                    <th class="font-medium text-center pb-2 w-8 hidden sm:table-cell">W</th>
                    <th class="font-medium text-center pb-2 w-8 hidden sm:table-cell">D</th>
                    <th class="font-medium text-center pb-2 w-8 hidden sm:table-cell">L</th>
                    <th class="font-medium text-center pb-2 w-10">GD</th>
                    <th class="font-medium text-center pb-2 w-10">Pts</th>
                    @if ($showForm)<th class="font-medium text-center pb-2 w-24 hidden md:table-cell">Form</th>@endif
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800/60">
                @foreach ($rows as $r)
                    @php $localId = $teamIdMap[$r['teamApiId']] ?? null; $zc = $zoneColor($r['description'] ?? null); @endphp
                    <tr class="group hover:bg-zinc-800/60 transition">
                        <td class="py-2.5 pl-2">
                            <div class="flex items-center gap-2">
                                <span class="w-0.5 h-5 rounded-full {{ $zc ? $zc[0] : 'bg-transparent' }}"></span>
                                <span class="text-zinc-400 tabular-nums">{{ $r['rank'] }}</span>
                            </div>
                        </td>
                        <td class="py-2.5">
                            @if ($localId)<a href="/team/{{ $localId }}" class="flex items-center gap-2 min-w-0 group-hover:text-white transition">@else<span class="flex items-center gap-2 min-w-0">@endif
                                @if ($r['logo'])<img src="{{ $r['logo'] }}" alt="" class="w-5 h-5 object-contain shrink-0" />@endif
                                <span class="truncate">{{ $r['team'] }}</span>
                            @if ($localId)</a>@else</span>@endif
                        </td>
                        <td class="text-center text-zinc-400 tabular-nums">{{ $r['played'] }}</td>
                        <td class="text-center text-zinc-400 tabular-nums hidden sm:table-cell">{{ $r['win'] }}</td>
                        <td class="text-center text-zinc-400 tabular-nums hidden sm:table-cell">{{ $r['draw'] }}</td>
                        <td class="text-center text-zinc-400 tabular-nums hidden sm:table-cell">{{ $r['lose'] }}</td>
                        <td class="text-center tabular-nums {{ $r['gd'] > 0 ? 'text-green-400' : ($r['gd'] < 0 ? 'text-red-400' : 'text-zinc-400') }}">{{ $r['gd'] > 0 ? '+' : '' }}{{ $r['gd'] }}</td>
                        <td class="text-center font-bold tabular-nums">{{ $r['points'] }}</td>
                        @if ($showForm)
                            <td class="text-center hidden md:table-cell">
                                <div class="flex items-center justify-center gap-0.5">
                                    @foreach (str_split($r['form'] ?? '') as $res)
                                        <span class="w-4 h-4 rounded-[3px] text-[8px] font-bold leading-4 text-white {{ $res === 'W' ? 'bg-green-600' : ($res === 'L' ? 'bg-red-600' : 'bg-zinc-600') }}">{{ $res }}</span>
                                    @endforeach
                                </div>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if ($zones->isNotEmpty())
        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-3 text-[10px] text-zinc-500">
            @foreach ($zones as $z)
                <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full {{ $z[0] }}"></span> {{ $z[1] }}</span>
            @endforeach
        </div>
    @endif
</div>