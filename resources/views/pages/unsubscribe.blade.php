@extends('layouts.app')
@section('title', 'Unsubscribed')
@section('no-left', 'yes')
@section('no-right', 'yes')
@section('content')
    <div class="min-h-[40vh] flex flex-col items-center justify-center text-center py-12">
        <div class="text-5xl mb-3">👋</div>
        <h1 class="text-2xl font-bold">You're unsubscribed</h1>
        <p class="text-zinc-500 text-sm mt-2 max-w-sm">You won't receive any more emails from us. Changed your mind? You can re-subscribe anytime from the site footer.</p>
        <a href="/" class="inline-block bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition mt-6">← Back home</a>
    </div>
@endsection