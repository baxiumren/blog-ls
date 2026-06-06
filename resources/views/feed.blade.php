<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
<title>{{ $site }}</title>
<link>{{ url('/') }}</link>
<description>Latest football news, predictions and analysis.</description>
<atom:link href="{{ url('/feed') }}" rel="self" type="application/rss+xml" />
@foreach ($articles as $a)
<item>
<title><![CDATA[{{ $a->title }}]]></title>
<link>{{ url('/news/' . $a->slug) }}</link>
<guid isPermaLink="true">{{ url('/news/' . $a->slug) }}</guid>
<pubDate>{{ $a->published_at->toRssString() }}</pubDate>
<description><![CDATA[{{ $a->excerpt ?? \Illuminate\Support\Str::limit(strip_tags($a->body), 200) }}]]></description>
@if ($a->category)<category><![CDATA[{{ $a->category }}]]></category>@endif
</item>
@endforeach
</channel>
</rss>