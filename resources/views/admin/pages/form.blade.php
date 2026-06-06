@extends('admin.layout')
@section('title', $page->exists ? 'Edit page' : 'New page')

@section('content')
    <form method="POST" action="{{ $page->exists ? '/admin/pages/' . $page->id : '/admin/pages' }}" x-data="{ loading: false }" @submit="loading = true">
        @csrf
        @if ($page->exists) @method('PUT') @endif

        <div class="flex items-center justify-between gap-3 mb-5">
            <div class="flex items-center gap-3 min-w-0">
                <a href="/admin/pages" class="w-9 h-9 rounded-lg bg-zinc-900 border border-zinc-800 hover:bg-zinc-800 flex items-center justify-center text-zinc-400 hover:text-white transition shrink-0"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg></a>
                <h1 class="text-xl font-bold truncate">{{ $page->exists ? 'Edit page' : 'New page' }}</h1>
            </div>
            <button type="submit" :disabled="loading" class="bg-blue-600 hover:bg-blue-500 disabled:opacity-60 disabled:cursor-not-allowed text-white font-semibold px-5 py-2.5 rounded-lg text-sm transition flex items-center gap-2 shrink-0">
                <svg x-show="loading" x-cloak class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                <span>{{ $page->exists ? 'Update' : 'Create' }}</span>
            </button>
        </div>

        @if ($errors->any())
            <div class="mb-4 text-sm text-red-400 bg-red-500/10 border border-red-500/30 rounded-lg px-3 py-2">
                @foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach
            </div>
        @endif

        <div class="bg-zinc-900 rounded-xl border border-zinc-800 p-4 space-y-4">
            <div>
                <label class="block text-xs text-zinc-400 mb-1">Title *</label>
                <input type="text" name="title" value="{{ old('title', $page->title) }}" required class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition">
            </div>
            <div>
                <label class="block text-xs text-zinc-400 mb-1">Slug <span class="text-zinc-600">(URL — kosongin = auto dari title)</span></label>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-zinc-500 shrink-0">/page/</span>
                    <input type="text" name="slug" value="{{ old('slug', $page->slug) }}" placeholder="auto" class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition">
                </div>
            </div>
            <div>
                <label class="block text-xs text-zinc-400 mb-1">Meta description <span class="text-zinc-600">(SEO)</span></label>
                <input type="text" name="meta_description" value="{{ old('meta_description', $page->meta_description) }}" maxlength="255" class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition">
            </div>
            <div>
                <label class="block text-xs text-zinc-400 mb-1">Body</label>
                <textarea name="body" rows="16" class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm font-mono leading-relaxed focus:outline-none focus:border-blue-500 transition">{{ old('body', $page->body) }}</textarea>
                <p class="text-[11px] text-zinc-500 mt-1.5">Supports <b>Markdown</b>: <code class="text-zinc-400">**bold**</code> · <code class="text-zinc-400">## Heading</code> · <code class="text-zinc-400">- list</code> · <code class="text-zinc-400">[text](url)</code></p>
            </div>
        </div>
    </form>
@endsection