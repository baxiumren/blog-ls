@extends('admin.layout')
@section('title', 'Cache')

@section('content')
    <div class="max-w-3xl">
        <div class="mb-5">
            <h1 class="text-xl font-bold">Cache</h1>
            <p class="text-sm text-zinc-500 mt-0.5">Clear cached data if something looks out of date.</p>
        </div>

        @if (session('ok'))
            <div class="flex items-center gap-3 bg-green-500/10 border border-green-500/30 rounded-xl p-4 mb-5">
                <i class="fa-solid fa-circle-check text-green-400 text-lg"></i>
                <span class="text-sm text-green-400 font-medium">{{ session('ok') }}</span>
            </div>
        @endif

        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-5">
            <div class="flex items-center justify-between gap-4 mb-4 pb-4 border-b border-zinc-800">
                <div>
                    <div class="text-sm font-semibold">Cache driver</div>
                    <div class="text-xs text-zinc-500 mt-0.5">Currently using <code class="text-zinc-300">{{ $driver }}</code></div>
                </div>
                <i class="fa-solid fa-database text-2xl text-zinc-700"></i>
            </div>

            <div class="text-xs text-zinc-500 mb-2">Clearing will refresh:</div>
            <div class="grid sm:grid-cols-2 gap-2 mb-5">
                @foreach ($items as $label => $key)
                    <div class="flex items-center gap-2 text-sm text-zinc-400">
                        <i class="fa-solid fa-circle text-[5px] text-zinc-600"></i> {{ $label }}
                    </div>
                @endforeach
            </div>

            <form method="POST" action="/admin/cache/clear">
                @csrf
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold px-4 py-2.5 rounded-lg transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-broom"></i> Clear all caches
                </button>
            </form>
            <p class="text-[11px] text-zinc-600 mt-3 text-center">Safe — this only clears cached files, never your data.</p>
        </div>
    </div>
@endsection