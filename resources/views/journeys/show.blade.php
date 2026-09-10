@extends('layouts.app')

@section('title', $journey->title.' — bAI')

@section('content')
<section class="shell py-14 sm:py-20">
    <nav class="mb-8 text-sm text-white/55">
        <a href="{{ route('home') }}" class="hover:text-teal">Library</a>
        <span class="mx-2">/</span>
        <a href="{{ route('journeys.index') }}" class="hover:text-teal">Journeys</a>
        <span class="mx-2">/</span>
        <span class="text-ink">{{ $journey->title }}</span>
    </nav>

    <div class="grid gap-10 lg:grid-cols-[1.2fr_0.8fr]">
        <div class="fade-up">
            <div class="flex flex-wrap items-center gap-2">
                @if ($journey->is_verified)
                    <span class="rounded-full bg-teal/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-teal">Verified</span>
                @endif
                @if ($journey->is_free)
                    <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-white/60">Free</span>
                @endif
            </div>

            <h1 class="mt-4 font-display text-4xl font-extrabold tracking-tight sm:text-5xl">{{ $journey->title }}</h1>
            <p class="mt-4 text-lg text-white/75">{{ $journey->tagline }}</p>
            <p class="mt-4 text-sm leading-relaxed text-white/70">{{ $journey->description }}</p>

            @if ($journey->outcome)
                <div class="surface mt-8 rounded-3xl p-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/45">You will finish with</p>
                    <p class="mt-3 text-sm leading-relaxed text-white/80">{{ $journey->outcome }}</p>
                </div>
            @endif

            <div class="mt-8">
                <a href="{{ route('journeys.step', [$journey, 1]) }}" class="btn-primary">
                    Start step 1
                </a>
            </div>
        </div>

        <aside class="fade-up-delay">
            <div class="surface rounded-3xl p-6">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/45">All steps</p>
                <ol class="mt-5 space-y-3">
                    @foreach ($journey->steps as $step)
                        <li>
                            <a href="{{ route('journeys.step', [$journey, $step->step_number]) }}"
                               class="flex gap-3 rounded-2xl px-3 py-3 transition hover:bg-teal/5">
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-deep text-xs font-semibold text-paper">
                                    {{ $step->step_number }}
                                </span>
                                <span>
                                    <span class="block text-sm font-semibold text-ink">{{ $step->title }}</span>
                                    @if ($step->goal)
                                        <span class="mt-0.5 block text-xs text-white/55">{{ $step->goal }}</span>
                                    @endif
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ol>
            </div>
        </aside>
    </div>
</section>
@endsection
