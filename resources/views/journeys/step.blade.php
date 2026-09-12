@extends('layouts.app')

@section('title', 'Step '.$current->step_number.': '.$current->title.' — '.$journey->title)

@section('content')
<section class="shell py-14 sm:py-20">
    <nav class="mb-8 text-sm text-white/55">
        <a href="{{ route('journeys.index') }}" class="hover:text-cyan">Journeys</a>
        <span class="mx-2">/</span>
        <a href="{{ route('journeys.show', $journey) }}" class="hover:text-cyan">{{ $journey->title }}</a>
        <span class="mx-2">/</span>
        <span class="text-white">Step {{ $current->step_number }}</span>
    </nav>

    <div class="mb-6">
        <div class="h-2 overflow-hidden rounded-full bg-white/10">
            <div class="h-full rounded-full bg-teal transition-all"
                 style="width: {{ ($current->step_number / max($journey->steps->count(), 1)) * 100 }}%"></div>
        </div>
        <p class="mt-2 text-xs text-white/50">
            Step {{ $current->step_number }} of {{ $journey->steps->count() }}
        </p>
    </div>

    <div class="grid gap-10 lg:grid-cols-[1.35fr_0.65fr]">
        <div class="fade-up">
            <div class="flex flex-wrap items-center gap-2">
                @if ($current->is_verified)
                    <span class="rounded-full bg-teal/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-teal">Verified step</span>
                @endif
                <x-ai-suggest-tags
                    :platform="$current->recommended_platform"
                    :model="$current->recommended_model"
                />
            </div>

            <h1 class="mt-4 font-display text-4xl font-extrabold tracking-tight">
                Step {{ $current->step_number }}: {{ $current->title }}
            </h1>
            @if ($current->goal)
                <p class="mt-3 text-base text-teal">Goal: {{ $current->goal }}</p>
            @endif
            @if ($current->instructions)
                <p class="mt-4 text-sm leading-relaxed text-white/70">{{ $current->instructions }}</p>
            @endif

            <div class="surface mt-8 rounded-3xl p-6 sm:p-8">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/45">Copy this prompt</p>
                    <livewire:copy-text-button :body="$current->prompt_body" />
                </div>
                <div class="prompt-body">{{ $current->prompt_body }}</div>
            </div>

            @if ($current->tip_note)
                <aside class="mt-6 rounded-3xl border border-amber/25 bg-amber/5 p-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-amber">How to use this step</p>
                    <p class="mt-3 text-sm leading-relaxed text-white/80">{{ $current->tip_note }}</p>
                </aside>
            @endif

            <div class="mt-8 flex flex-wrap items-center justify-between gap-3">
                @if ($previous)
                    <a href="{{ route('journeys.step', [$journey, $previous->step_number]) }}" class="btn-secondary">
                        ← Step {{ $previous->step_number }}
                    </a>
                @else
                    <a href="{{ route('journeys.show', $journey) }}" class="btn-ghost">Overview</a>
                @endif

                @if ($next)
                    <a href="{{ route('journeys.step', [$journey, $next->step_number]) }}" class="btn-primary">
                        Next: {{ $next->title }} →
                    </a>
                @else
                    <a href="{{ route('journeys.show', $journey) }}" class="btn-primary">Journey complete</a>
                @endif
            </div>
        </div>

        <aside class="fade-up-delay">
            <div class="surface rounded-3xl p-5">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/45">Roadmap</p>
                <ol class="mt-4 space-y-2">
                    @foreach ($journey->steps as $step)
                        <li>
                            <a href="{{ route('journeys.step', [$journey, $step->step_number]) }}"
                               @class([
                                   'block rounded-xl px-3 py-2 text-sm transition',
                                   'bg-teal/10 font-semibold text-teal' => $step->step_number === $current->step_number,
                                   'text-white/70 hover:bg-white/10 hover:text-white' => $step->step_number !== $current->step_number,
                               ])>
                                {{ $step->step_number }}. {{ $step->title }}
                            </a>
                        </li>
                    @endforeach
                </ol>
            </div>
        </aside>
    </div>
</section>
@endsection
