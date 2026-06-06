@extends('admin.layout')
@section('title', 'Health')

@section('content')
    <div class="max-w-3xl">
        <div class="mb-5">
            <h1 class="text-xl font-bold">Health</h1>
            <p class="text-sm text-zinc-500 mt-0.5">Server &amp; application diagnostics.</p>
        </div>

        <div class="bg-zinc-900 border border-zinc-800 rounded-xl overflow-hidden mb-5">
            <div class="divide-y divide-zinc-800/60">
                @foreach ($checks as [$label, $value, $state])
                    @php
                        $dot = ['ok' => 'text-green-400', 'warn' => 'text-amber-400', 'fail' => 'text-red-400', 'info' => 'text-zinc-600'][$state];
                        $icon = ['ok' => 'fa-circle-check', 'warn' => 'fa-triangle-exclamation', 'fail' => 'fa-circle-xmark', 'info' => 'fa-circle-info'][$state];
                    @endphp
                    <div class="flex items-center justify-between gap-4 px-4 py-3">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid {{ $icon }} {{ $dot }}"></i>
                            <span class="text-sm font-medium">{{ $label }}</span>
                        </div>
                        <span class="text-sm text-zinc-400 text-right">{{ $value }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        @if ($usedPct !== null)
            <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4">
                <div class="flex items-center justify-between text-sm mb-2">
                    <span class="font-semibold">Disk usage</span>
                    <span class="text-zinc-400">{{ $usedPct }}% used · {{ $freeGb }} GB free of {{ $totalGb }} GB</span>
                </div>
                <div class="h-2 bg-zinc-800 rounded-full overflow-hidden">
                    <div class="h-full {{ $usedPct > 90 ? 'bg-red-500' : ($usedPct > 75 ? 'bg-amber-500' : 'bg-blue-600') }}" style="width: {{ $usedPct }}%"></div>
                </div>
            </div>
        @endif
    </div>
@endsection