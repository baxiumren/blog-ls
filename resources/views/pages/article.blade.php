@extends('layouts.app')

@section('title', $article->meta_title ?: $article->title)
@section('description', $article->meta_description ?: $article->displayExcerpt(155))
@section('robots', $article->noindex ? 'noindex, follow' : 'index, follow')
@if ($article->image)
    @section('og_image', asset('storage/' . $article->image))
@endif
@section('no-right', 'yes')
@section('no-left', 'yes')

@section('schema')
<script type="application/ld+json">{!! json_encode(array_filter([
    '@context' => 'https://schema.org',
    '@type' => 'NewsArticle',
    'headline' => $article->title,
    'description' => $article->meta_description ?: $article->displayExcerpt(160),
    'image' => $article->image ? asset('storage/' . $article->image) : null,
    'datePublished' => optional($article->published_at)->toIso8601String(),
    'dateModified' => optional($article->updated_at)->toIso8601String(),
    'author' => ['@type' => 'Person', 'name' => optional($article->user)->name ?? 'Editor'],
    'publisher' => array_filter([
        '@type' => 'Organization',
        'name' => ! empty($settings['site_name']) ? $settings['site_name'] : 'LiveScore',
        'logo' => ! empty($settings['site_logo']) ? ['@type' => 'ImageObject', 'url' => $settings['site_logo']] : null,
    ]),
    'mainEntityOfPage' => url()->current(),
    'url' => url()->current(),
]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
<script type="application/ld+json">{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'News', 'item' => url('/news')],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $article->category, 'item' => url('/news/category/' . \Illuminate\Support\Str::slug($article->category))],
        ['@type' => 'ListItem', 'position' => 4, 'name' => $article->title, 'item' => url()->current()],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endsection

@section('content')
<nav class="text-xs text-zinc-500 mb-3 flex items-center gap-1.5 flex-wrap">
    <a href="/" class="hover:text-white transition">Home</a><span class="text-zinc-700">/</span>
    <a href="/news" class="hover:text-white transition">News</a><span class="text-zinc-700">/</span>
    <a href="/news/category/{{ \Illuminate\Support\Str::slug($article->category) }}" class="hover:text-white transition">{{ $article->category }}</a>
</nav>
    <article class="bg-zinc-900 rounded-lg overflow-hidden">
        @if ($article->image)
            <img src="{{ asset('storage/' . $article->image) }}" alt="{{ $article->title }}" class="w-full aspect-video max-h-[440px] object-cover bg-zinc-800" />
        @endif
        <div class="p-4 sm:p-6">
            <div class="flex flex-wrap items-center gap-2 text-xs text-zinc-500 mb-2">
                @if ($article->user)<span>· by <a href="/author/{{ $article->user->id }}" class="text-blue-400 hover:text-white transition">{{ $article->user->name }}</a></span>@endif
                <span>· {{ $article->readingTime() }} min read</span>
                @isset($views)<span>· {{ number_format($views) }} views</span>@endisset
                <span class="text-blue-400 font-semibold uppercase tracking-wide">{{ $article->category }}</span>
                @if ($article->published_at)<span>· {{ $article->published_at->format('d M Y') }}</span>@endif
                @if ($article->league)<span>· {{ $article->league->name }}</span>@endif
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold mb-3">{{ $article->title }}</h1>
            <p class="text-zinc-400 text-lg mb-4">{{ $article->displayExcerpt(200) }}</p>
            <div class="article-body text-zinc-200">{!! \Illuminate\Support\Str::markdown($article->body) !!}</div>
            @if ($article->tagList())
                <div class="flex flex-wrap gap-2 mt-5">
                    @foreach ($article->tagList() as $t)
                        <a href="/news/tag/{{ urlencode($t) }}" class="text-xs bg-zinc-800 hover:bg-zinc-700 text-zinc-300 hover:text-white px-2.5 py-1 rounded-full transition">#{{ $t }}</a>
                    @endforeach
                </div>
            @endif
            <div class="flex flex-wrap items-center gap-2 mt-6" x-data="reactions({{ $article->id }}, @js($reactions), @js($article->slug))">
                <span class="text-xs text-zinc-500 mr-1">React:</span>
                <template x-for="e in emojis" :key="e">
                    <button type="button" @click="react(e)"
                            :class="reacted === e ? 'bg-blue-500/20 ring-1 ring-blue-500/50' : 'bg-zinc-800 hover:bg-zinc-700'"
                            class="flex items-center gap-1.5 rounded-full px-3 py-1.5 text-sm transition">
                        <span x-text="e" class="text-base leading-none"></span>
                        <span class="text-xs text-zinc-400 tabular-nums" x-text="counts[e] || 0"></span>
                    </button>
                </template>
            </div>
            <div class="mt-6 pt-4 border-t border-zinc-800">
                <x-share :title="$article->title" />
            </div>
        </div>
    </article>

    <x-ad-slot format="inarticle" />

    @if ($related->isNotEmpty())
        <div class="bg-zinc-900 rounded-lg p-4 mt-4">
            <h2 class="text-sm font-semibold mb-3">Related news</h2>
            <div class="grid sm:grid-cols-2 gap-x-6">
                @foreach ($related as $r)
                    <a href="/news/{{ $r->slug }}" class="flex gap-3 py-3 border-b border-zinc-800/60 last:border-0 hover:bg-zinc-800/30 transition group -mx-1 px-1 rounded">
                        <div class="min-w-0 flex-1 flex flex-col justify-center">
                            <span class="text-[10px] font-semibold uppercase tracking-wide text-blue-400 mb-1">{{ $r->category }}</span>
                            <p class="text-sm font-medium text-zinc-100 line-clamp-2 group-hover:text-white transition">{{ $r->title }}</p>
                            <span class="text-[11px] text-zinc-500 mt-1">{{ $r->published_at->diffForHumans() }}</span>
                        </div>
                        <div class="w-24 h-16 rounded-md bg-zinc-800 overflow-hidden shrink-0 flex items-center justify-center">
                            @if ($r->image)<img src="{{ asset('storage/' . $r->image) }}" alt="" loading="lazy" decoding="async" class="w-full h-full object-cover" />@else<span class="text-[10px] text-zinc-600">IMG</span>@endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
    @if (($settings['comments_enabled'] ?? '1') !== '0')
        <div id="comments" class="bg-zinc-900 rounded-lg p-4 mt-4">
            <h2 class="text-sm font-semibold mb-4">Comments ({{ $comments->count() }})</h2>

            @if (session('comment_ok'))
                <div class="mb-4 text-sm text-green-400 bg-green-500/10 border border-green-500/30 rounded-lg px-3 py-2">{{ session('comment_ok') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-4 text-sm text-red-400 bg-red-500/10 border border-red-500/30 rounded-lg px-3 py-2">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
            @endif

            @forelse ($comments as $c)
                <div class="flex gap-3 py-3 border-b border-zinc-800/60 last:border-0">
                    <span class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-600 to-blue-500 flex items-center justify-center text-sm font-bold shrink-0">{{ strtoupper(substr($c->name, 0, 1)) }}</span>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2"><span class="text-sm font-semibold">{{ $c->name }}</span><span class="text-[11px] text-zinc-600">{{ $c->created_at->diffForHumans() }}</span></div>
                        <p class="text-sm text-zinc-300 mt-1 whitespace-pre-line break-words">{{ $c->body }}</p>
                    </div>
                </div>
            @empty
                <p class="text-sm text-zinc-500 mb-4">No comments yet. Be the first to comment! 💬</p>
            @endforelse

            <form method="POST" action="/news/{{ $article->slug }}/comment#comments" class="mt-5 space-y-3" x-data="{ loading: false }" @submit="loading = true">
                @csrf
                <input type="text" name="name" value="{{ old('name') }}" required maxlength="60" placeholder="Your name" class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition placeholder-zinc-600">
                <textarea name="body" required maxlength="1000" rows="3" placeholder="Write a comment…" class="w-full bg-zinc-800 border border-zinc-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 transition placeholder-zinc-600">{{ old('body') }}</textarea>
                <button type="submit" :disabled="loading" class="bg-blue-600 hover:bg-blue-500 disabled:opacity-60 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition flex items-center gap-2">
                    <svg x-show="loading" x-cloak class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    <span>Post comment</span>
                </button>
                <p class="text-[11px] text-zinc-600">Comments are moderated before appearing.</p>
            </form>
        </div>
    @endif
@endsection