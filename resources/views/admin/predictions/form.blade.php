@extends('admin.layout')
@section('title', $prediction->exists ? 'Edit prediction' : 'New prediction')

@section('content')
    <form method="POST" action="{{ $prediction->exists ? '/admin/predictions/' . $prediction->id : '/admin/predictions' }}" x-data="{ loading: false }" @submit="loading = true">
        @csrf
        @if ($prediction->exists) @method('PUT') @endif

        <div class="flex items-center justify-between gap-3 mb-5">
            <div class="flex items-center gap-3 min-w-0">
                <a href="/admin/predictions" class="w-9 h-9 rounded-lg bg-zinc-900 border border-zinc-800 hover:bg-zinc-800 flex items-center justify-center text-zinc-400 hover:text-white transition shrink-0"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg></a>
                <h1 class="text-xl font-bold truncate">{{ $prediction->exists ? 'Edit prediction' : 'New prediction' }}</h1>
            </div>
            <button type="submit" :disabled="loading" class="bg-blue-600 hover:bg-blue-500 disabled:opacity-60 disabled:cursor-not-allowed text-white font-semibold px-5 py-2.5 rounded-lg text-sm transition flex items-center gap-2 shrink-0">
                <svg x-show="loading" x-cloak class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                <span>{{ $prediction->exists ? 'Update' : 'Create' }}</span>
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
                <x-match-picker :fixtures="$fixtures" :selected="old('fixture_id', $prediction->fixture_id)" />
                <p class="text-[11px] text-zinc-600 mt-1">Cuma match terjadwal (upcoming) yang muncul.</p>
            </div>
            <div class="grid sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs text-zinc-400 mb-1">Tip *</label>
                    <input type="text" name="tip" list="tips" value="{{ old('tip', $prediction->tip) }}" required class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition">
                    <datalist id="tips"><option>Home Win</option><option>Draw</option><option>Away Win</option><option>Over 2.5 Goals</option><option>Under 2.5 Goals</option><option>Both Teams to Score</option><option>Home or Draw</option><option>Away or Draw</option></datalist>
                </div>
                <div>
                    <label class="block text-xs text-zinc-400 mb-1">Score <span class="text-zinc-600">(opt)</span></label>
                    <input type="text" name="predicted_score" value="{{ old('predicted_score', $prediction->predicted_score) }}" placeholder="2-1" maxlength="10" class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition">
                </div>
            </div>
            <div>
                <label class="block text-xs text-zinc-400 mb-1">Confidence *</label>
                <select name="confidence" class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition">
                    @for ($i = 1; $i <= 5; $i++)<option value="{{ $i }}" {{ old('confidence', $prediction->confidence ?? 3) == $i ? 'selected' : '' }}>{{ str_repeat('★', $i) }} ({{ $i }}/5)</option>@endfor
                </select>
            </div>
            <div>
                <label class="block text-xs text-zinc-400 mb-1">Analysis <span class="text-zinc-600">(opt, Markdown)</span></label>
                <textarea name="body" rows="8" class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm font-mono focus:outline-none focus:border-blue-500 transition">{{ old('body', $prediction->body) }}</textarea>
            </div>
            <label class="flex items-center gap-2.5 text-sm cursor-pointer">
                <input type="checkbox" name="published" value="1" {{ old('published', $prediction->published_at) ? 'checked' : '' }} class="rounded bg-zinc-800 border-zinc-700 text-blue-600 focus:ring-0">
                <span>Publish now <span class="text-zinc-500">(uncheck = draft)</span></span>
            </label>
        </div>
    </form>
@endsection