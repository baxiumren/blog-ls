@props(['event'])

@php
    $type = $event['type'] ?? '';
    $detail = $event['detail'] ?? '';
    $player = $event['player']['name'] ?? '-';
    $assist = $event['assist']['name'] ?? null;
@endphp

@if ($type === 'subst')
    <div class="leading-tight">
        @if ($assist)
            <div class="text-green-400 text-sm">↑ {{ $assist }}</div>
        @endif
        <div class="text-red-400/80 text-xs">↓ {{ $player }}</div>
    </div>
@elseif ($type === 'Goal')
    <div class="leading-tight">
        <div class="text-zinc-100 text-sm font-medium">{{ $player }}</div>
        @if ($detail !== 'Normal Goal')
            <div class="text-amber-400/80 text-xs">{{ $detail }}</div>
        @elseif ($assist)
            <div class="text-zinc-500 text-xs">assist: {{ $assist }}</div>
        @endif
    </div>
@else
    <div class="leading-tight">
        <div class="text-zinc-200 text-sm">{{ $player }}</div>
        <div class="text-zinc-500 text-xs">{{ $detail ?: $type }}</div>
    </div>
@endif