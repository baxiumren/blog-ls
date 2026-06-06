@extends('admin.layout')
@section('title', 'Highlights')

@section('content')
    <div class="flex items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold">Highlights</h1>
            <p class="text-sm text-zinc-500 mt-0.5">{{ $highlights->total() }} video highlight{{ $highlights->total() === 1 ? '' : 's' }}</p>
        </div>
        <a href="/admin/highlights/create" class="bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold px-4 py-2.5 rounded-lg transition flex items-center gap-1.5 shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
            New highlight
        </a>
    </div>

    <div class="bg-zinc-900 rounded-xl border border-zinc-800 overflow-hidden">
        <div class="overflow-x-auto [&::-webkit-scrollbar]:hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-[10px] uppercase tracking-wide text-zinc-500 border-b border-zinc-800">
                        <th class="text-left font-medium px-4 py-2.5">Match</th>
                        <th class="text-left font-medium px-2 py-2.5 hidden sm:table-cell">Title</th>
                        <th class="text-right font-medium px-4 py-2.5 w-24">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60">
                    @forelse ($highlights as $hl)
                        <tr class="hover:bg-zinc-800/30 transition">
                            <td class="px-4 py-3">
                                @if ($hl->fixture)
                                    <div class="font-medium truncate max-w-[240px]">{{ $hl->fixture->homeTeam->name }} vs {{ $hl->fixture->awayTeam->name }}</div>
                                    <div class="text-[11px] text-zinc-500">{{ $hl->fixture->kickoff_at->format('d M Y') }}</div>
                                @else
                                    <span class="text-zinc-500">— match deleted —</span>
                                @endif
                            </td>
                            <td class="px-2 py-3 text-zinc-400 text-xs hidden sm:table-cell truncate max-w-[200px]">{{ $hl->title ?: '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ $hl->youtube_url }}" target="_blank" class="w-7 h-7 rounded-md bg-zinc-800 hover:bg-zinc-700 flex items-center justify-center text-zinc-400 hover:text-white transition" title="Watch"><svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z" /></svg></a>
                                    <a href="/admin/highlights/{{ $hl->id }}/edit" class="w-7 h-7 rounded-md bg-zinc-800 hover:bg-zinc-700 flex items-center justify-center text-zinc-400 hover:text-white transition" title="Edit"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg></a>
                                    <form method="POST" action="/admin/highlights/{{ $hl->id }}" onsubmit="return confirm('Delete this highlight?')">
                                        @csrf @method('DELETE')
                                        <button class="w-7 h-7 rounded-md bg-zinc-800 hover:bg-red-500/20 flex items-center justify-center text-zinc-400 hover:text-red-400 transition" title="Delete"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-12 text-center text-zinc-500">No highlights yet. <a href="/admin/highlights/create" class="text-blue-400 hover:text-white">Add one →</a></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($highlights->hasPages())<div class="mt-4">{{ $highlights->links() }}</div>@endif
@endsection