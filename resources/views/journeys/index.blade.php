@extends('layouts.app')

@section('title', 'Step-by-step journeys — bAI')

@section('content')
<section class="shell py-14 sm:py-20">
    <div class="max-w-2xl fade-up">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-teal">Verified playbooks</p>
        <h1 class="mt-3 font-display text-4xl font-extrabold tracking-tight sm:text-5xl">Click a goal. Follow every step.</h1>
        <p class="mt-4 text-base text-white/70">Each journey is a verified sequence. Copy the prompt, complete the step, then continue.</p>
    </div>

    <div class="mt-12 grid gap-5 md:grid-cols-2 fade-up-delay">
        @foreach ($journeys as $journey)
            <a href="{{ route('journeys.show', $journey) }}"
               class="surface group rounded-3xl p-6 transition hover:-translate-y-1 hover:border-teal/30">
                <div class="flex flex-wrap items-center gap-2">
                    @if ($journey->is_verified)
                        <span class="rounded-full bg-teal/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-teal">Verified</span>
                    @endif
                    @if ($journey->is_featured)
                        <span class="rounded-full bg-amber/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-amber">Featured</span>
                    @endif
                    <span class="text-xs text-white/45">{{ $journey->steps_count }} steps</span>
                </div>
                <h2 class="mt-4 font-display text-2xl font-bold tracking-tight group-hover:text-teal">{{ $journey->title }}</h2>
                <p class="mt-2 text-sm text-white/70">{{ $journey->tagline }}</p>
            </a>
        @endforeach
    </div>
</section>
@endsection
