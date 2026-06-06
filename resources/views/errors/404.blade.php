@extends('layouts.app')
@section('title', 'Page not found — 404')
@section('description', 'The page you are looking for could not be found.')

@section('content')
    <div class="min-h-[50vh] flex flex-col items-center justify-center text-center py-12">
        <div class="text-7xl sm:text-8xl font-black text-zinc-800 select-none">404</div>
        <h1 class="text-xl sm:text-2xl font-bold mt-2">Page not found</h1>
        <p class="text-zinc-500 text-sm mt-2 max-w-sm">The page you're looking for doesn't exist or has been moved. Try heading back home or explore something else.</p>
        <div class="flex flex-wrap items-center justify-center gap-2 mt-6">
            <a href="/" class="bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition">← Back home</a>
            <a href="/news" class="bg-zinc-900 border border-zinc-800 hover:bg-zinc-800 text-sm font-medium px-5 py-2.5 rounded-lg transition">Read news</a>
            <a href="/leagues" class="bg-zinc-900 border border-zinc-800 hover:bg-zinc-800 text-sm font-medium px-5 py-2.5 rounded-lg transition">Browse leagues</a>
        </div>
    </div>
@endsection