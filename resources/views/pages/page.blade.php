@extends('layouts.app')

@php
    $siteName = ! empty($settings['site_name']) ? $settings['site_name'] : 'LiveScore';
    $pageBody = str_replace('{{SITE}}', $siteName, $page->body ?? '');
    $pageMeta = str_replace('{{SITE}}', $siteName, $page->meta_description ?: $page->title);
@endphp

@section('title', $page->title)
@section('description', $pageMeta)
@section('no-left', 'yes')
@section('no-right', 'yes')

@section('schema')
<script type="application/ld+json">{!! json_encode(array_filter([
    '@'.'context' => 'https://schema.org',
    '@type' => 'WebPage',
    'name' => $page->title,
    'description' => $pageMeta,
    'url' => url()->current(),
    'dateModified' => $page->updated_at->toIso8601String(),
]), JSON_UNESCAPED_SLASHES) !!}</script>
<script type="application/ld+json">{!! json_encode([
    '@'.'context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => $page->title, 'item' => url()->current()],
    ],
], JSON_UNESCAPED_SLASHES) !!}</script>
@endsection

@section('content')
    <article class="max-w-3xl mx-auto">
        <nav class="text-xs text-zinc-500 mb-3"><a href="/" class="hover:text-white transition">Home</a> <span class="text-zinc-700">/</span> {{ $page->title }}</nav>
        <h1 class="text-2xl sm:text-3xl font-bold mb-4">{{ $page->title }}</h1>
        <div class="article-body">{!! \Illuminate\Support\Str::markdown($pageBody) !!}</div>
        <p class="text-xs text-zinc-600 mt-8 pt-4 border-t border-zinc-800">Last updated {{ $page->updated_at->format('d F Y') }}</p>
    </article>
@endsection