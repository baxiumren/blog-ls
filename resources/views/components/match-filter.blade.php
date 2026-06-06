@props(['selected', 'active' => 'all'])

@php
    $date = $selected->toDateString();
    $tabs = [
        'all'      => 'All',
        'live'     => 'Live',
        'finished' => 'Finished',
        'upcoming' => 'Upcoming',
    ];
@endphp

<div class="flex items-center justify-center gap-1.5 mb-4">
    @foreach ($tabs as $key => $label)
        <a href="/?date={{ $date }}{{ $key === 'all' ? '' : '&filter=' . $key }}"
           class="px-3 py-1.5 text-xs font-semibold uppercase tracking-wide rounded-full transition
           {{ $active === $key ? 'bg-blue-600 text-white' : 'bg-zinc-900 text-zinc-400 hover:text-white hover:bg-zinc-800' }}">
            {{ $label }}
        </a>
    @endforeach
</div>