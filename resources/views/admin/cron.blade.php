@extends('admin.layout')
@section('title', 'Cron health')

@section('content')
    <div class="max-w-3xl">
        <div class="mb-5">
            <h1 class="text-xl font-bold">Cron health</h1>
            <p class="text-sm text-zinc-500 mt-0.5">Status of scheduled tasks (live scores, fixtures).</p>
        </div>

        {{-- Status banner --}}
        @if ($healthy)
            <div class="flex items-center gap-3 bg-green-500/10 border border-green-500/30 rounded-xl p-4 mb-5">
                <i class="fa-solid fa-circle-check text-green-400 text-xl"></i>
                <div>
                    <div class="font-semibold text-green-400">Scheduler is running</div>
                    <div class="text-xs text-zinc-400">Last heartbeat {{ $hb->diffForHumans() }}.</div>
                </div>
            </div>
        @else
            <div class="flex items-center gap-3 bg-red-500/10 border border-red-500/30 rounded-xl p-4 mb-5">
                <i class="fa-solid fa-triangle-exclamation text-red-400 text-xl"></i>
                <div>
                    <div class="font-semibold text-red-400">Scheduler not running</div>
                    <div class="text-xs text-zinc-400">{{ $hb ? 'Last seen ' . $hb->diffForHumans() . '.' : 'Never ran yet.' }} Add the cron command below on your server.</div>
                </div>
            </div>
        @endif

        {{-- Tasks --}}
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl overflow-hidden mb-5">
            <table class="w-full text-sm">
                <thead class="text-xs text-zinc-500 border-b border-zinc-800">
                    <tr><th class="text-left font-medium px-4 py-3">Task</th><th class="text-left font-medium px-4 py-3">Schedule</th><th class="text-left font-medium px-4 py-3">Last run</th></tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60">
                    @foreach ($tasks as $t)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $t['name'] }}</td>
                            <td class="px-4 py-3 text-zinc-400">{{ $t['schedule'] }}</td>
                            <td class="px-4 py-3 text-zinc-400">{{ $t['last'] ? $t['last']->diffForHumans() : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Setup command --}}
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-4">
            <div class="text-sm font-semibold mb-1">Server setup</div>
            <p class="text-xs text-zinc-500 mb-3">Add this line to your server's crontab (<code class="text-zinc-400">crontab -e</code>) so the scheduler runs every minute:</p>
            <pre class="bg-zinc-950 border border-zinc-800 rounded-lg p-3 text-xs text-zinc-300 overflow-x-auto whitespace-pre-wrap">{{ $command }}</pre>
        </div>
    </div>
@endsection