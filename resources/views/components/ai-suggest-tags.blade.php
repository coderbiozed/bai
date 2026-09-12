@props([
    'platform' => null,
    'model' => null,
    'tone' => 'dark',
])

@php
    $platform = is_string($platform) ? trim($platform) : '';
    $model = is_string($model) ? trim($model) : '';
    // Dark UI default: bright tags with borders so they stay readable on purple surfaces.
    $platformClass = 'rounded-full border border-cyan/40 bg-cyan/20 px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wider text-cyan';
    $modelClass = 'rounded-full border border-magenta/40 bg-magenta/20 px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wider text-magenta';
@endphp

@if ($platform !== '' || $model !== '')
    <span class="inline-flex flex-wrap items-center gap-1.5" {{ $attributes }}>
        @if ($platform !== '')
            <span class="{{ $platformClass }}" title="Suggested AI platform">{{ $platform }}</span>
        @endif
        @if ($model !== '')
            <span class="{{ $modelClass }}" title="Suggested model">{{ $model }}</span>
        @endif
    </span>
@endif
