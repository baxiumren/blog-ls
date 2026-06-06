@extends('admin.layout')
@section('title', 'Leagues')

@section('content')
    <div x-data="{ loading: false, q: '' }">
        <div class="flex items-center justify-between gap-3 mb-1">
            <h1 class="text-xl font-bold">Leagues</h1>
            <span class="text-xs text-zinc-500">{{ count($leagues) }} leagues</span>
        </div>
        <p class="text-sm text-zinc-500 mb-4">Atur urutan tampil di homepage. <span class="text-zinc-400">Angka kecil = tampil duluan.</span> ⭐ = ke-pin.</p>

        {{-- Search --}}
        <div class="relative mb-4 max-w-sm">
            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7" /><path d="M21 21l-4-4" /></svg>
            <input type="text" x-model="q" placeholder="Search league or country…" class="w-full bg-zinc-900 border border-zinc-800 rounded-lg text-sm pl-9 pr-4 py-2.5 focus:outline-none focus:border-blue-500 transition placeholder-zinc-600">
        </div>

        <form method="POST" action="/admin/leagues" @submit="loading = true">
            @csrf
            <div class="bg-zinc-900 rounded-xl border border-zinc-800 overflow-hidden">
                <div class="overflow-x-auto [&::-webkit-scrollbar]:hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-[10px] uppercase tracking-wide text-zinc-500 border-b border-zinc-800">
                                <th class="text-left font-medium px-4 py-2.5 w-24">Priority</th>
                                <th class="text-left font-medium px-2 py-2.5">League</th>
                                <th class="text-left font-medium px-2 py-2.5 hidden sm:table-cell">Country</th>
                                <th class="text-left font-medium px-2 py-2.5 hidden sm:table-cell w-20">Code</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-800/60">
                            @foreach ($leagues as $l)
                                <tr x-show="q === '' || @js(\Illuminate\Support\Str::lower($l->name . ' ' . ($l->country ?? '') . ' ' . $l->code)).includes(q.toLowerCase())"
                                    class="hover:bg-zinc-800/30 transition {{ $l->priority < 100 ? 'bg-amber-500/[0.04]' : '' }}">
                                    <td class="px-4 py-2">
                                        <div class="flex items-center gap-1.5">
                                            @if ($l->priority < 100)<span class="text-amber-400 text-xs">⭐</span>@endif
                                            <input type="number" name="priority[{{ $l->id }}]" value="{{ $l->priority }}" class="w-14 bg-zinc-800 border border-zinc-700 rounded-lg px-2 py-1.5 text-sm text-center tabular-nums focus:outline-none focus:border-blue-500 transition">
                                        </div>
                                    </td>
                                    <td class="px-2 py-2">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <div class="w-6 h-6 shrink-0 flex items-center justify-center">@if ($l->logo)<img src="{{ $l->logo }}" alt="" class="max-w-full max-h-full object-contain">@endif</div>
                                            <span class="font-medium truncate">{{ $l->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-2 py-2 text-zinc-400 text-xs hidden sm:table-cell">{{ $l->country }}</td>
                                    <td class="px-2 py-2 text-zinc-500 text-xs hidden sm:table-cell">{{ $l->code }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="sticky bottom-0 mt-4 py-3 bg-zinc-950/80 backdrop-blur flex items-center gap-3">
                <button type="submit" :disabled="loading" class="bg-blue-600 hover:bg-blue-500 disabled:opacity-60 disabled:cursor-not-allowed text-white font-semibold px-5 py-2.5 rounded-lg text-sm transition flex items-center gap-2">
                    <svg x-show="loading" x-cloak class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    <span>Save order</span>
                </button>
                <span class="text-xs text-zinc-600">Tip: pin liga populer dengan angka 1-20.</span>
            </div>
        </form>
    </div>
@endsection