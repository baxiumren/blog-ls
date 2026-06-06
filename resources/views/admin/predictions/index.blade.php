@extends('admin.layout')
@section('title', 'Predictions')

@section('content')
    <div class="flex items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold">Predictions</h1>
            <p class="text-sm text-zinc-500 mt-0.5">{{ $predictions->total() }} prediction{{ $predictions->total() === 1 ? '' : 's' }}</p>
        </div>
        <a href="/admin/predictions/create" class="bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold px-4 py-2.5 rounded-lg transition flex items-center gap-1.5 shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
            New prediction
        </a>
    </div>

    <div class="bg-zinc-900 rounded-xl border border-zinc-800 overflow-hidden">
        <div class="overflow-x-auto [&::-webkit-scrollbar]:hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-[10px] uppercase tracking-wide text-zinc-500 border-b border-zinc-800">
                        <th class="text-left font-medium px-4 py-2.5">Match</th>
                        <th class="text-left font-medium px-2 py-2.5 w-32">Tip</th>
                        <th class="text-center font-medium px-2 py-2.5 w-24 hidden sm:table-cell">Confidence</th>
                        <th class="text-center font-medium px-2 py-2.5 w-20">Status</th>
                        <th class="text-right font-medium px-4 py-2.5 w-24">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60">
                    @forelse ($predictions as $p)
                        <tr class="hover:bg-zinc-800/30 transition">
                            <td class="px-4 py-3">
                                @if ($p->fixture)
                                    <div class="font-medium truncate max-w-[240px]">{{ $p->fixture->homeTeam->name }} vs {{ $p->fixture->awayTeam->name }}</div>
                                    <div class="text-[11px] text-zinc-500">{{ $p->fixture->kickoff_at->format('d M Y · H:i') }}</div>
                                @else
                                    <span class="text-zinc-500">— match deleted —</span>
                                @endif
                            </td>
                            <td class="px-2 py-3"><span class="text-xs bg-blue-500/15 text-blue-400 px-2 py-0.5 rounded">{{ $p->tip }}</span>@if ($p->predicted_score)<div class="text-[11px] text-zinc-500 mt-1">{{ $p->predicted_score }}</div>@endif</td>
                            <td class="px-2 py-3 text-center hidden sm:table-cell"><span class="text-amber-400 text-xs">{{ str_repeat('★', $p->confidence) }}{{ str_repeat('☆', 5 - $p->confidence) }}</span></td>
                            <td class="px-2 py-3 text-center">@if ($p->published_at)<span class="text-[10px] bg-green-500/15 text-green-400 px-2 py-0.5 rounded">Live</span>@else<span class="text-[10px] bg-amber-500/15 text-amber-400 px-2 py-0.5 rounded">Draft</span>@endif</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="/admin/predictions/{{ $p->id }}/edit" class="w-7 h-7 rounded-md bg-zinc-800 hover:bg-zinc-700 flex items-center justify-center text-zinc-400 hover:text-white transition" title="Edit"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg></a>
                                    @if ($p->fixture)<a href="/match/{{ $p->fixture_id }}" target="_blank" class="w-7 h-7 rounded-md bg-zinc-800 hover:bg-zinc-700 flex items-center justify-center text-zinc-400 hover:text-white transition" title="View"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg></a>@endif
                                    <form method="POST" action="/admin/predictions/{{ $p->id }}" onsubmit="return confirm('Delete this prediction?')">
                                        @csrf @method('DELETE')
                                        <button class="w-7 h-7 rounded-md bg-zinc-800 hover:bg-red-500/20 flex items-center justify-center text-zinc-400 hover:text-red-400 transition" title="Delete"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-12 text-center text-zinc-500">No predictions yet. <a href="/admin/predictions/create" class="text-blue-400 hover:text-white">Create one →</a></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($predictions->hasPages())<div class="mt-4">{{ $predictions->links() }}</div>@endif
@endsection