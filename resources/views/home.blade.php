@extends('layouts.app')

@section('title', 'bAI — Prompt Chaos Lab')

@section('content')
<section class="shell relative min-h-[88vh] overflow-hidden pb-20 pt-14 sm:pt-20">
    <div class="absolute inset-x-0 top-8 -z-10 h-[78%] overflow-hidden rounded-[2.5rem]"
         aria-hidden="true">
        <div class="absolute inset-0 bg-gradient-to-br from-magenta/40 via-ink to-cyan/30"></div>
        <div class="absolute -right-16 top-8 h-80 w-80 rounded-full bg-lime/40 blur-3xl"></div>
        <div class="absolute bottom-0 left-10 h-72 w-72 rounded-full bg-coral/35 blur-3xl"></div>
        <div class="absolute right-1/3 top-1/3 h-52 w-52 rounded-full bg-sun/30 blur-2xl"></div>
    </div>

    <div class="relative fade-up">
        <p class="crazy-chip mb-6 bg-coral text-deep">Verified · Loud · Step-by-step</p>
        <h1 class="mega-title max-w-6xl">
            Learn AI<br>Language
        </h1>
        <p class="font-soft mt-8 max-w-3xl text-2xl leading-snug text-white/85 sm:text-3xl md:text-4xl">
            {{ number_format($stats['verified']) }}+ verified prompts. Click a goal. Get every step.
            Become a great YouTuber — or anything else — one wild prompt at a time.
        </p>
        <div class="mt-10 flex flex-wrap gap-4 fade-up-delay">
            <a href="{{ route('journeys.show', 'become-a-great-youtuber') }}" class="btn-primary text-lg sm:text-xl">
                Become a Great YouTuber
            </a>
            <a href="#journeys" class="btn-secondary text-lg sm:text-xl">All journeys</a>
            <a href="#library" class="btn bg-cyan text-deep hover:bg-magenta hover:text-white text-lg sm:text-xl">
                Browse library
            </a>
        </div>

        <div class="mt-12 flex flex-wrap gap-8 fade-up-delay-2">
            <div>
                <p class="stat-bomb" data-count="{{ $stats['prompts'] }}">{{ number_format($stats['prompts']) }}</p>
                <p class="font-accent mt-2 text-sm font-bold uppercase tracking-[0.2em] text-cyan">Prompts</p>
            </div>
            <div>
                <p class="stat-bomb text-magenta">{{ number_format($stats['verified']) }}</p>
                <p class="font-accent mt-2 text-sm font-bold uppercase tracking-[0.2em] text-lime">Verified</p>
            </div>
            <div>
                <p class="stat-bomb text-coral">{{ number_format($stats['journeys']) }}</p>
                <p class="font-accent mt-2 text-sm font-bold uppercase tracking-[0.2em] text-sun">Journeys</p>
            </div>
        </div>
    </div>
</section>

<div class="relative z-10 overflow-hidden border-y-4 border-lime bg-ink py-4">
    <div class="marquee-track font-display text-2xl uppercase text-lime sm:text-4xl">
        @foreach ([1, 2] as $loopCopy)
            <span>Fintech Designer</span><span class="text-magenta">✦</span>
            <span class="text-cyan">Forex Writer Pro</span><span class="text-sun">✦</span>
            <span class="text-coral">Travel Creator</span><span class="text-lime">✦</span>
            <span>Image Creator</span><span class="text-magenta">✦</span>
            <span class="text-cyan">Shorts Maker</span><span class="text-sun">✦</span>
            <span class="text-coral">Media Manager</span><span class="text-lime">✦</span>
            <span>YouTuber Playbook</span><span class="text-magenta">✦</span>
        @endforeach
    </div>
</div>

<section id="journeys" class="shell py-20" data-reveal>
    <div class="mb-10 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="section-title">Step-by-step<br>journeys</h2>
            <p class="font-soft mt-4 max-w-xl text-xl text-white/70 sm:text-2xl">
                One click. Giant roadmap. Copy each prompt and keep going.
            </p>
        </div>
        <a href="{{ route('journeys.index') }}" class="btn-secondary">View all</a>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        @foreach ($featuredJourneys as $index => $journey)
            <a href="{{ route('journeys.show', $journey) }}"
               class="surface tilt-card group rounded-[2rem] p-7 sm:p-9"
               style="animation-delay: {{ $index * 0.08 }}s">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="crazy-chip bg-lime text-deep">Verified</span>
                    <span class="font-accent text-sm font-bold uppercase tracking-wider text-cyan">
                        {{ $journey->steps_count }} steps
                    </span>
                </div>
                <h3 class="font-display mt-6 text-3xl uppercase leading-none text-white transition group-hover:text-lime sm:text-5xl">
                    {{ $journey->title }}
                </h3>
                <p class="font-soft mt-4 text-xl text-white/70">{{ $journey->tagline }}</p>
                <p class="font-accent mt-6 text-lg font-extrabold uppercase text-magenta group-hover:text-sun">
                    Start journey →
                </p>
            </a>
        @endforeach
    </div>
</section>

<section id="library" class="shell py-16" data-reveal>
    <div class="mb-10 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="section-title">Categories</h2>
            <p class="font-soft mt-4 text-xl text-white/70 sm:text-2xl">Hover hard. Click louder. Learn faster.</p>
        </div>
        <a href="{{ route('search') }}" class="btn-primary">Search prompts</a>
    </div>

    <div class="border-y-4 border-white/15">
        @foreach ($categories as $category)
            <a href="{{ route('categories.show', $category) }}" class="interactive-row group">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="font-display text-3xl uppercase leading-none text-white transition group-hover:text-sun sm:text-5xl md:text-6xl">
                            {{ $category->name }}
                        </h3>
                        <p class="font-soft mt-3 max-w-2xl text-lg text-white/60 sm:text-xl">
                            {{ $category->description }}
                        </p>
                    </div>
                    <div class="flex items-center gap-5 font-accent text-sm font-bold uppercase tracking-wider text-cyan sm:text-base">
                        <span>{{ $category->subcategories_count }} specialties</span>
                        <span class="text-magenta">{{ $category->prompts_count }} prompts</span>
                        <span class="text-lime opacity-0 transition group-hover:opacity-100">Open →</span>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</section>

@if ($bestPrompts->isNotEmpty())
<section class="shell py-20" data-reveal>
    <h2 class="section-title mb-4">Marked best</h2>
    <p class="font-soft mb-10 text-xl text-white/70 sm:text-2xl">The loudest verified role prompts.</p>

    <div class="grid gap-6 md:grid-cols-2">
        @foreach ($bestPrompts as $prompt)
            <a href="{{ route('prompts.show', [$prompt->subcategory->category, $prompt->subcategory, $prompt]) }}"
               class="surface tilt-card group rounded-[2rem] p-7">
                <p class="font-accent text-sm font-bold uppercase tracking-[0.18em] text-cyan">
                    {{ $prompt->subcategory->category->name }} · {{ $prompt->subcategory->name }}
                </p>
                <div class="mt-3">
                    <x-ai-suggest-tags
                        :platform="$prompt->recommended_platform"
                        :model="$prompt->recommended_model"
                        tone="dark"
                    />
                </div>
                <h3 class="font-display mt-4 text-2xl uppercase leading-tight text-white group-hover:text-lime sm:text-4xl">
                    {{ $prompt->title }}
                </h3>
                <p class="mt-4 line-clamp-3 text-base leading-relaxed text-white/65 sm:text-lg">{{ $prompt->body }}</p>
            </a>
        @endforeach
    </div>
</section>
@endif
@endsection
