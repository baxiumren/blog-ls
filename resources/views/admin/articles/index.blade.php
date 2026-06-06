@extends('admin.layout')
@section('title', 'Articles')

@section('content')
    <div class="flex items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold">Articles</h1>
            <p class="text-sm text-zinc-500 mt-0.5">{{ $articles->total() }} article{{ $articles->total() === 1 ? '' : 's' }}</p>
        </div>
        <a href="/admin/articles/create" class="bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold px-4 py-2.5 rounded-lg transition flex items-center gap-1.5 shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
            New article
        </a>
    </div>

    <form method="GET" class="flex flex-wrap items-center gap-2 mb-4">
        <div class="relative flex-1 min-w-[200px]">
            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7" /><path d="M21 21l-4-4" /></svg>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search articles…" class="w-full bg-zinc-900 border border-zinc-800 rounded-lg text-sm pl-9 pr-4 py-2.5 focus:outline-none focus:border-blue-500 transition placeholder-zinc-600">
        </div>
        <select name="status" onchange="this.form.submit()" class="bg-zinc-900 border border-zinc-800 rounded-lg text-sm px-3 py-2.5 text-white focus:outline-none focus:border-blue-500 cursor-pointer">
            <option value="">All status</option>
            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
        </select>
        <button type="submit" class="bg-zinc-800 hover:bg-zinc-700 text-sm px-4 py-2.5 rounded-lg transition">Search</button>
    </form>

    <div class="bg-zinc-900 rounded-xl border border-zinc-800 overflow-hidden">
        <div class="overflow-x-auto [&::-webkit-scrollbar]:hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-[10px] uppercase tracking-wide text-zinc-500 border-b border-zinc-800">
                        <th class="text-left font-medium px-4 py-2.5">Article</th>
                        <th class="text-left font-medium px-2 py-2.5 hidden md:table-cell w-28">Category</th>
                        <th class="text-left font-medium px-2 py-2.5 hidden sm:table-cell w-24">Date</th>
                        <th class="text-center font-medium px-2 py-2.5 w-16">Views</th>
                        <th class="text-center font-medium px-2 py-2.5 w-20">Status</th>
                        <th class="text-right font-medium px-4 py-2.5 w-24">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60">
                    @forelse ($articles as $a)
                        <tr class="hover:bg-zinc-800/30 transition">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-12 h-9 rounded bg-zinc-800 overflow-hidden shrink-0">@if ($a->image)<img src="{{ asset('storage/' . $a->image) }}" alt="" class="w-full h-full object-cover" />@endif</div>
                                    <div class="min-w-0">
                                        <div class="font-medium truncate max-w-[240px]">{{ $a->title }}</div>
                                        <div class="text-[11px] text-zinc-500 truncate">{{ $a->slug }}@if ($a->league) · {{ $a->league->name }}@endif</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-2 py-3 hidden md:table-cell"><span class="text-xs bg-zinc-800 text-zinc-300 px-2 py-0.5 rounded">{{ $a->category }}</span></td>
                            <td class="px-2 py-3 text-zinc-500 text-xs hidden sm:table-cell">{{ $a->created_at->format('d M Y') }}</td>
                            <td class="px-2 py-3 text-center text-xs text-zinc-400 tabular-nums">{{ number_format($a->views) }}</td>
                            <td class="px-2 py-3 text-center">@if (! $a->published_at)
                                <span class="text-[10px] bg-amber-500/15 text-amber-400 px-2 py-0.5 rounded">Draft</span>
                            @elseif ($a->published_at->isFuture())
                                <span class="text-[10px] bg-blue-500/15 text-blue-400 px-2 py-0.5 rounded">Scheduled</span>
                            @else
                                <span class="text-[10px] bg-green-500/15 text-green-400 px-2 py-0.5 rounded">Live</span>
                            @endif</td>
                            <td class="px-4 py-3">
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
                        <tr><td colspan="6" class="px-4 py-12 text-center text-zinc-500">No articles found. <a href="/admin/articles/create" class="text-blue-400 hover:text-white">Create one →</a></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($articles->hasPages())
        <div class="mt-4">{{ $articles->links() }}</div>
    @endif
@endsection