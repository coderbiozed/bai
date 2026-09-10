@props([
    'platform' => null,
    'model' => null,
    'tone' => 'light',
])

@php
    $platform = is_string($platform) ? trim($platform) : '';
    $model = is_string($model) ? trim($model) : '';
    $platformClass = $tone === 'dark'
        ? 'rounded-full bg-cyan/15 px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wider text-cyan'
        : 'rounded-full bg-cyan/10 px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wider text-cyan';
    $modelClass = $tone === 'dark'
        ? 'rounded-full bg-magenta/15 px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wider text-magenta'
        : 'rounded-full bg-magenta/10 px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wider text-magenta';
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
