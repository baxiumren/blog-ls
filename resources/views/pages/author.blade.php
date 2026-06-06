@extends('layouts.app')

@section('title', $user->name . ' — Author')
@section('description', $user->bio ?: ('Articles written by ' . $user->name . '.'))
@section('no-left', 'yes')
@section('no-right', 'yes')

@section('content')
    <div class="bg-zinc-900 rounded-xl border border-zinc-800 p-5 mb-5 flex items-start gap-4">
        <span class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-600 to-blue-500 flex items-center justify-center text-2xl font-bold shrink-0">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
        <div class="min-w-0">
            <h1 class="text-xl font-bold">{{ $user->name }}</h1>
            <p class="text-xs text-zinc-500 mt-0.5">{{ $articles->total() }} article{{ $articles->total() === 1 ? '' : 's' }}</p>
            @if ($user->bio)<p class="text-sm text-zinc-400 mt-2 leading-relaxed">{{ $user->bio }}</p>@endif
        </div>
    </div>

    @if ($articles->count())
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($articles as $a)
                <a href="/news/{{ $a->slug }}" class="group rounded-xl overflow-hidden bg-zinc-900 border border-zinc-800 hover:border-zinc-700 flex flex-col transition">
                    <div class="aspect-video bg-zinc-800 overflow-hidden">@if ($a->image)<img src="{{ asset('storage/' . $a->image) }}" alt="" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">@endif</div>
                    <div class="p-3 flex-1 flex flex-col">
                        <span class="text-[10px] font-semibold uppercase tracking-wide text-blue-400">{{ $a->category }}</span>
                        <p class="text-sm font-semibold leading-snug mt-1 line-clamp-2 group-hover:text-white transition">{{ $a->title }}</p>
                        <span class="text-[11px] text-zinc-600 mt-auto pt-2">{{ $a->published_at->diffForHumans() }}</span>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-6">{{ $articles->links() }}</div>
    @else
        <div class="bg-zinc-900 rounded-xl border border-zinc-800 p-10 text-center text-zinc-500">No articles yet.</div>
    @endif
@endsection