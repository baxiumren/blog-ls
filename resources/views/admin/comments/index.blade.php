@extends('admin.layout')
@section('title', 'Comments')

@section('content')
    <div class="mb-5">
        <h1 class="text-xl font-bold">Comments</h1>
        <p class="text-sm text-zinc-500 mt-0.5">{{ $comments->total() }} total · <span class="text-amber-400">{{ $pending }} pending</span></p>
    </div>

    <div class="space-y-3">
        @forelse ($comments as $c)
            <div class="bg-zinc-900 rounded-xl border border-zinc-800 p-4 {{ ! $c->approved ? 'border-l-2 border-l-amber-500' : '' }}">
                <div class="flex items-start gap-3">
                    <span class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-600 to-blue-500 flex items-center justify-center text-sm font-bold shrink-0">{{ strtoupper(substr($c->name, 0, 1)) }}</span>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-sm font-semibold">{{ $c->name }}</span>
                            @if ($c->approved)<span class="text-[10px] bg-green-500/15 text-green-400 px-2 py-0.5 rounded">Approved</span>@else<span class="text-[10px] bg-amber-500/15 text-amber-400 px-2 py-0.5 rounded">Pending</span>@endif
                            <span class="text-[11px] text-zinc-600">{{ $c->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-sm text-zinc-300 mt-1.5 whitespace-pre-line break-words">{{ $c->body }}</p>
                        <div class="text-[11px] text-zinc-500 mt-2">
                            on @if ($c->article)<a href="/news/{{ $c->article->slug }}" target="_blank" class="text-blue-400 hover:text-white">{{ \Illuminate\Support\Str::limit($c->article->title, 50) }}</a>@else— deleted —@endif
                        </div>
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        <form method="POST" action="/admin/comments/{{ $c->id }}/approve">
                            @csrf
                            <button class="w-7 h-7 rounded-md {{ $c->approved ? 'bg-zinc-800 hover:bg-zinc-700 text-zinc-400' : 'bg-green-500/15 hover:bg-green-500/25 text-green-400' }} flex items-center justify-center transition" title="{{ $c->approved ? 'Hide' : 'Approve' }}">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            </button>
                        </form>
                        <form method="POST" action="/admin/comments/{{ $c->id }}" onsubmit="return confirm('Delete this comment?')">
                            @csrf @method('DELETE')
                            <button class="w-7 h-7 rounded-md bg-zinc-800 hover:bg-red-500/20 flex items-center justify-center text-zinc-400 hover:text-red-400 transition" title="Delete"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-zinc-900 rounded-xl border border-zinc-800 p-10 text-center text-zinc-500">No comments yet. 💬</div>
        @endforelse
    </div>

    @if ($comments->hasPages())<div class="mt-4">{{ $comments->links() }}</div>@endif
@endsection