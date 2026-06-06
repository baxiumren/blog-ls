@extends('admin.layout')
@section('title', $highlight->exists ? 'Edit highlight' : 'New highlight')

@section('content')
    <form method="POST" action="{{ $highlight->exists ? '/admin/highlights/' . $highlight->id : '/admin/highlights' }}" x-data="{ loading: false }" @submit="loading = true">
        @csrf
        @if ($highlight->exists) @method('PUT') @endif

        <div class="flex items-center justify-between gap-3 mb-5">
            <div class="flex items-center gap-3 min-w-0">
                <a href="/admin/highlights" class="w-9 h-9 rounded-lg bg-zinc-900 border border-zinc-800 hover:bg-zinc-800 flex items-center justify-center text-zinc-400 hover:text-white transition shrink-0"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg></a>
                <h1 class="text-xl font-bold truncate">{{ $highlight->exists ? 'Edit highlight' : 'New highlight' }}</h1>
            </div>
            <button type="submit" :disabled="loading" class="bg-blue-600 hover:bg-blue-500 disabled:opacity-60 disabled:cursor-not-allowed text-white font-semibold px-5 py-2.5 rounded-lg text-sm transition flex items-center gap-2 shrink-0">
                <svg x-show="loading" x-cloak class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                <span>{{ $highlight->exists ? 'Update' : 'Save' }}</span>
            </button>
        </div>

        @if ($errors->any())
            <div class="mb-4 text-sm text-red-400 bg-red-500/10 border border-red-500/30 rounded-lg px-3 py-2">
                @foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach
            </div>
        @endif

        <div class="bg-zinc-900 rounded-xl border border-zinc-800 p-4 space-y-4 max-w-2xl">
            <div>
                <label class="block text-xs text-zinc-400 mb-1">Match *</label>
                <x-match-picker :fixtures="$fixtures" :selected="old('fixture_id', $highlight->fixture_id)" />
                <p class="text-[11px] text-zinc-600 mt-1">Match 30 hari terakhir.</p>
            </div>
            <div>
                <label class="block text-xs text-zinc-400 mb-1">YouTube URL *</label>
                <input type="url" name="youtube_url" value="{{ old('youtube_url', $highlight->youtube_url) }}" required placeholder="https://youtu.be/xxxxxxxxxxx" class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition">
                <p class="text-[11px] text-zinc-600 mt-1">Boleh youtu.be / watch?v= / embed / shorts.</p>
            </div>
            <div>
                <label class="block text-xs text-zinc-400 mb-1">Title <span class="text-zinc-600">(optional)</span></label>
                <input type="text" name="title" value="{{ old('title', $highlight->title) }}" placeholder="Match highlights" class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition">
            </div>
        </div>
    </form>
@endsection