@extends('admin.layout')
@section('title', 'Pages')

@section('content')
    <div class="flex items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold">Pages</h1>
            <p class="text-sm text-zinc-500 mt-0.5">{{ count($pages) }} static page{{ count($pages) === 1 ? '' : 's' }}</p>
        </div>
        <a href="/admin/pages/create" class="bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold px-4 py-2.5 rounded-lg transition flex items-center gap-1.5 shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
            New page
        </a>
    </div>

    @php
        $palette = ['about' => 'blue', 'privacy-policy' => 'green', 'terms' => 'purple', 'contact' => 'amber'];
        $colors = ['blue' => 'bg-blue-500/15 text-blue-400', 'green' => 'bg-green-500/15 text-green-400', 'purple' => 'bg-purple-500/15 text-purple-400', 'amber' => 'bg-amber-500/15 text-amber-400', 'zinc' => 'bg-zinc-700/40 text-zinc-400'];
    @endphp

    @if (count($pages))
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($pages as $p)
                @php $c = $colors[$palette[$p->slug] ?? 'zinc']; @endphp
                <div class="bg-zinc-900 rounded-xl border border-zinc-800 p-4 flex flex-col hover:border-zinc-700 transition">
                    <div class="flex items-start justify-between mb-3">
                        <span class="w-10 h-10 rounded-lg {{ $c }} flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        </span>
                        <code class="text-[11px] text-zinc-500 bg-zinc-800 px-2 py-0.5 rounded">/page/{{ $p->slug }}</code>
                    </div>
                    <h2 class="font-semibold truncate">{{ $p->title }}</h2>
                    <p class="text-xs text-zinc-500 mt-1 line-clamp-2 flex-1">{{ $p->meta_description ?: \Illuminate\Support\Str::limit(strip_tags($p->body ?? ''), 90) }}</p>
                    <div class="flex items-center justify-between mt-4 pt-3 border-t border-zinc-800/60">
                        <span class="text-[11px] text-zinc-600">Updated {{ $p->updated_at->format('d M Y') }}</span>
                        <div class="flex items-center gap-1">
                            <a href="/admin/pages/{{ $p->id }}/edit" class="w-7 h-7 rounded-md bg-zinc-800 hover:bg-zinc-700 flex items-center justify-center text-zinc-400 hover:text-white transition" title="Edit"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg></a>
                            <a href="/page/{{ $p->slug }}" target="_blank" class="w-7 h-7 rounded-md bg-zinc-800 hover:bg-zinc-700 flex items-center justify-center text-zinc-400 hover:text-white transition" title="View"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg></a>
                            <form method="POST" action="/admin/pages/{{ $p->id }}" onsubmit="return confirm('Delete this page?')">
                                @csrf @method('DELETE')
                                <button class="w-7 h-7 rounded-md bg-zinc-800 hover:bg-red-500/20 flex items-center justify-center text-zinc-400 hover:text-red-400 transition" title="Delete"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-zinc-900 rounded-xl border border-zinc-800 p-10 text-center text-zinc-500">No pages yet. <a href="/admin/pages/create" class="text-blue-400 hover:text-white">Create one →</a></div>
    @endif
@endsection