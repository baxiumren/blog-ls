@extends('layouts.app')

@section('title', $settings['maintenance_title'] ?? 'Under maintenance')
@section('description', 'Our site is currently undergoing scheduled maintenance.')
@section('robots', 'noindex, nofollow')
@section('no-left', 'yes')
@section('no-right', 'yes')

@section('content')
    <div class="min-h-[55vh] flex flex-col items-center justify-center text-center py-16">
        @if (! empty($settings['maintenance_image']))
        <img src="{{ asset('storage/' . $settings['maintenance_image']) }}" alt="Maintenance" class="w-48 sm:w-60 h-auto mb-6">
    @else
        <div class="relative w-28 h-28 mb-4">
            <svg class="w-20 h-20 absolute top-0 left-0 text-blue-500 animate-spin" style="animation-duration: 9s" viewBox="0 0 24 24" fill="currentColor">
                <path d="M19.43 12.98c.04-.32.07-.64.07-.98 0-.34-.03-.66-.07-.98l2.11-1.65c.19-.15.24-.42.12-.64l-2-3.46c-.12-.22-.39-.3-.61-.22l-2.49 1c-.52-.4-1.08-.73-1.69-.98l-.38-2.65C14.46 2.18 14.25 2 14 2h-4c-.25 0-.46.18-.49.42l-.38 2.65c-.61.25-1.17.59-1.69.98l-2.49-1c-.23-.09-.49 0-.61.22l-2 3.46c-.13.22-.07.49.12.64l2.11 1.65c-.04.32-.07.65-.07.98 0 .33.03.66.07.98l-2.11 1.65c-.19.15-.24.42-.12.64l2 3.46c.12.22.39.3.61.22l2.49-1c.52.4 1.08.73 1.69.98l.38 2.65c.03.24.24.42.49.42h4c.25 0 .46-.18.49-.42l.38-2.65c.61-.25 1.17-.59 1.69-.98l2.49 1c.23.09.49 0 .61-.22l2-3.46c.12-.22.07-.49-.12-.64l-2.11-1.65zM12 15.5c-1.93 0-3.5-1.57-3.5-3.5s1.57-3.5 3.5-3.5 3.5 1.57 3.5 3.5-1.57 3.5-3.5 3.5z" />
            </svg>
            <svg class="w-12 h-12 absolute bottom-0 right-0 text-blue-400 animate-spin" style="animation-duration: 6s; animation-direction: reverse" viewBox="0 0 24 24" fill="currentColor">
                <path d="M19.43 12.98c.04-.32.07-.64.07-.98 0-.34-.03-.66-.07-.98l2.11-1.65c.19-.15.24-.42.12-.64l-2-3.46c-.12-.22-.39-.3-.61-.22l-2.49 1c-.52-.4-1.08-.73-1.69-.98l-.38-2.65C14.46 2.18 14.25 2 14 2h-4c-.25 0-.46.18-.49.42l-.38 2.65c-.61.25-1.17.59-1.69.98l-2.49-1c-.23-.09-.49 0-.61.22l-2 3.46c-.13.22-.07.49.12.64l2.11 1.65c-.04.32-.07.65-.07.98 0 .33.03.66.07.98l-2.11 1.65c-.19.15-.24.42-.12.64l2 3.46c.12.22.39.3.61.22l2.49-1c.52.4 1.08.73 1.69.98l.38 2.65c.03.24.24.42.49.42h4c.25 0 .46-.18.49-.42l.38-2.65c.61-.25 1.17-.59 1.69-.98l2.49 1c.23.09.49 0 .61-.22l2-3.46c.12-.22.07-.49-.12-.64l-2.11-1.65zM12 15.5c-1.93 0-3.5-1.57-3.5-3.5s1.57-3.5 3.5-3.5 3.5 1.57 3.5 3.5-1.57 3.5-3.5 3.5z" />
            </svg>
        </div>
    @endif
        <h1 class="text-2xl sm:text-3xl font-bold">{{ $settings['maintenance_title'] ?? "We'll be back soon" }}</h1>
        <p class="text-zinc-500 text-sm mt-3 max-w-md leading-relaxed">{{ $settings['maintenance_message'] ?? 'Our site is undergoing scheduled maintenance. Please check back shortly. Thanks for your patience!' }}</p>

        @if (! empty($settings['twitter_url']) || ! empty($settings['telegram_url']) || ! empty($settings['instagram_url']))
            <div class="flex items-center gap-4 mt-6 text-sm">
                <span class="text-zinc-600">Follow us:</span>
                @if (! empty($settings['telegram_url']))<a href="{{ $settings['telegram_url'] }}" target="_blank" rel="noopener" class="text-blue-400 hover:text-white transition">Telegram</a>@endif
                @if (! empty($settings['twitter_url']))<a href="{{ $settings['twitter_url'] }}" target="_blank" rel="noopener" class="text-blue-400 hover:text-white transition">Twitter</a>@endif
                @if (! empty($settings['instagram_url']))<a href="{{ $settings['instagram_url'] }}" target="_blank" rel="noopener" class="text-blue-400 hover:text-white transition">Instagram</a>@endif
            </div>
        @endif
    </div>
@endsection