@extends('admin.layout')
@section('title', 'Newsletter')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="mb-5">
            <h1 class="text-xl font-bold">Send newsletter</h1>
            <p class="text-sm text-zinc-500 mt-0.5">Broadcast an email to all {{ $count }} subscriber{{ $count === 1 ? '' : 's' }}.</p>
        </div>

        @if (session('ok'))
            <div class="mb-4 bg-green-500/10 border border-green-500/30 text-green-400 text-sm px-4 py-3 rounded-lg">{{ session('ok') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 bg-red-500/10 border border-red-500/30 text-red-400 text-sm px-4 py-3 rounded-lg">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-4 bg-red-500/10 border border-red-500/30 text-red-400 text-sm px-4 py-3 rounded-lg">{{ $errors->first() }}</div>
        @endif

        @if ($count === 0)
            <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-8 text-center">
                <div class="text-4xl mb-2">📭</div>
                <p class="text-zinc-400 text-sm">No subscribers yet. Once people sign up via the footer, you can email them here.</p>
            </div>
        @else
            <form method="POST" action="/admin/newsletter" x-data="{ body: '', subject: '' }">
                @csrf
                <div class="bg-zinc-900 border border-zinc-800 rounded-xl p-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-zinc-300 mb-1.5">Subject</label>
                        <input type="text" name="subject" x-model="subject" maxlength="200" required
                            placeholder="e.g. This week in football ⚽"
                            class="w-full bg-zinc-950 border border-zinc-800 rounded-lg px-3.5 py-2.5 text-sm focus:border-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-sm font-medium text-zinc-300">Message</label>
                            <span class="text-[11px] text-zinc-600">Markdown supported — **bold**, [link](url), # heading</span>
                        </div>
                        <textarea name="body" x-model="body" rows="12" required
                            placeholder="Write your update here…"
                            class="w-full bg-zinc-950 border border-zinc-800 rounded-lg px-3.5 py-2.5 text-sm font-mono leading-relaxed focus:border-blue-500 focus:outline-none resize-y"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-between mt-4">
                    <p class="text-xs text-zinc-600">Each email includes an unsubscribe link automatically.</p>
                    <button type="submit"
                        onclick="return confirm('Send this email to all {{ $count }} subscribers?')"
                        class="bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition flex items-center gap-2 disabled:opacity-50"
                        :disabled="!subject.trim() || !body.trim()">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>
                        Send to {{ $count }}
                    </button>
                </div>
            </form>
        @endif
    </div>
@endsection