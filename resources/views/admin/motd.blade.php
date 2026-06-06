@extends('admin.layout')
@section('title', 'Match of the Day')

@section('content')
    <div class="mb-5">
        <h1 class="text-xl font-bold">Match of the Day</h1>
        <p class="text-sm text-zinc-500 mt-0.5">Pilih 1 match unggulan yang tampil di hero homepage.</p>
    </div>

    <form method="POST" action="/admin/motd" class="space-y-4 max-w-2xl" x-data="{ loading: false }" @submit="loading = true">
        @csrf
        <div class="bg-zinc-900 rounded-xl border border-zinc-800 p-4 space-y-4">
            <div>
                <label class="block text-xs text-zinc-400 mb-1">Match</label>
                <x-match-picker :fixtures="$fixtures" :selected="$current" />
                <p class="text-[11px] text-zinc-600 mt-1">Cuma match upcoming (terjadwal). Kosongin lalu Save buat hapus.</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" :disabled="loading" class="bg-blue-600 hover:bg-blue-500 disabled:opacity-60 text-white font-semibold px-5 py-2.5 rounded-lg text-sm transition flex items-center gap-2">
                <svg x-show="loading" x-cloak class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                <span>Save</span>
            </button>
            @if ($current)
                <a href="/match/{{ $current }}" target="_blank" class="text-sm text-zinc-400 hover:text-white px-3 py-2.5 transition">Preview current ↗</a>
            @endif
        </div>
    </form>
@endsection