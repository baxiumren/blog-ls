@extends('admin.layout')
@section('title', 'Subscribers')

@section('content')
    <div class="flex items-center justify-between gap-3 mb-5">
        <div>
            <h1 class="text-xl font-bold">Subscribers</h1>
            <p class="text-sm text-zinc-500 mt-0.5">{{ $subscribers->total() }} email{{ $subscribers->total() === 1 ? '' : 's' }}</p>
        </div>
        @if ($subscribers->total())
            <a href="/admin/subscribers/export" class="bg-zinc-900 border border-zinc-800 hover:bg-zinc-800 text-sm font-medium px-4 py-2.5 rounded-lg transition flex items-center gap-1.5 shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                Export CSV
            </a>
        @endif
    </div>

    <div class="bg-zinc-900 rounded-xl border border-zinc-800 overflow-hidden">
        <div class="overflow-x-auto [&::-webkit-scrollbar]:hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-[10px] uppercase tracking-wide text-zinc-500 border-b border-zinc-800">
                        <th class="text-left font-medium px-4 py-2.5">Email</th>
                        <th class="text-left font-medium px-2 py-2.5 hidden sm:table-cell w-40">Subscribed</th>
                        <th class="text-right font-medium px-4 py-2.5 w-16">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60">
                    @forelse ($subscribers as $s)
                        <tr class="hover:bg-zinc-800/30 transition">
                            <td class="px-4 py-3 font-medium truncate">{{ $s->email }}</td>
                            <td class="px-2 py-3 text-zinc-500 text-xs hidden sm:table-cell">{{ $s->created_at->format('d M Y · H:i') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end">
                                    <form method="POST" action="/admin/subscribers/{{ $s->id }}" onsubmit="return confirm('Remove this subscriber?')">
                                        @csrf @method('DELETE')
                                        <button class="w-7 h-7 rounded-md bg-zinc-800 hover:bg-red-500/20 flex items-center justify-center text-zinc-400 hover:text-red-400 transition" title="Remove"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-12 text-center text-zinc-500">No subscribers yet. The signup form is in the site footer. 📭</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($subscribers->hasPages())<div class="mt-4">{{ $subscribers->links() }}</div>@endif
@endsection