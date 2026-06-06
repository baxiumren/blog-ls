@extends('admin.layout')
@section('title', 'Logs')

@section('content')
    <div class="flex items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold">Logs</h1>
            <p class="text-sm text-zinc-500 mt-0.5">Latest errors &amp; warnings · {{ number_format($size / 1024, 1) }} KB</p>
        </div>
        @if (count($entries))
            <form method="POST" action="/admin/logs/clear" onsubmit="return confirm('Clear all logs?')">
                @csrf
                <button class="bg-zinc-800 hover:bg-red-500/20 text-zinc-300 hover:text-red-400 text-sm font-medium px-4 py-2.5 rounded-lg transition flex items-center gap-2"><i class="fa-solid fa-trash"></i> Clear logs</button>
            </form>
        @endif
    </div>

    @if (session('ok'))<div class="mb-4 bg-green-500/10 border border-green-500/30 text-green-400 text-sm px-4 py-3 rounded-lg">{{ session('ok') }}</div>@endif

    @if (! count($entries))
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-10 text-center">
            <div class="text-4xl mb-2">✅</div>
            <p class="text-zinc-400 text-sm">No recent log entries — everything looks clean.</p>
        </div>
    @else
        <div class="space-y-2">
            @foreach ($entries as $e)
                @php $lc = ['ERROR' => 'bg-red-500/15 text-red-400', 'CRITICAL' => 'bg-red-500/15 text-red-400', 'WARNING' => 'bg-amber-500/15 text-amber-400', 'INFO' => 'bg-blue-500/15 text-blue-400'][$e['level']] ?? 'bg-zinc-700 text-zinc-300'; @endphp
                <div x-data="{ open: false }" class="bg-zinc-900 border border-zinc-800 rounded-lg overflow-hidden">
                    <button @click="open = ! open" class="w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-zinc-800/40 transition">
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded shrink-0 {{ $lc }}">{{ $e['level'] }}</span>
                        <span class="text-xs text-zinc-500 shrink-0 tabular-nums">{{ $e['time'] }}</span>
                        <span class="text-sm text-zinc-300 truncate flex-1">{{ \Illuminate\Support\Str::limit(strtok($e['message'], "\n"), 100) }}</span>
                        <i class="fa-solid fa-chevron-down text-xs text-zinc-600" :class="open && 'rotate-180'"></i>
                    </button>
                    <div x-show="open" x-cloak class="border-t border-zinc-800 bg-zinc-950/50">
                        <pre class="text-[11px] text-zinc-400 p-4 overflow-x-auto whitespace-pre-wrap break-words">{{ $e['message'] }}</pre>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection