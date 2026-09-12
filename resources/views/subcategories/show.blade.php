@extends('layouts.app')

@section('title', $subcategory->name.' — bAI')

@section('content')
<section class="shell py-14 sm:py-20">
    <nav class="mb-8 text-sm text-white/55">
        <a href="{{ route('home') }}" class="hover:text-cyan">Library</a>
        <span class="mx-2">/</span>
        <a href="{{ route('categories.show', $category) }}" class="hover:text-cyan">{{ $category->name }}</a>
        <span class="mx-2">/</span>
        <span class="text-white">{{ $subcategory->name }}</span>
    </nav>

    <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between fade-up">
        <div class="max-w-2xl">
            <h1 class="font-display text-4xl font-800 tracking-tight sm:text-5xl">{{ $subcategory->name }}</h1>
            @if ($subcategory->description)
                <p class="mt-4 text-base text-white/70">{{ $subcategory->description }}</p>
            @endif
        </div>
        @auth
            <a href="{{ route('prompts.create', ['subcategory_id' => $subcategory->id]) }}" class="btn-primary shrink-0">
                Add prompt
            </a>
        @else
            <a href="{{ route('login') }}" class="btn-secondary shrink-0">Log in to add</a>
        @endauth
    </div>

    <div class="mt-12 space-y-4 fade-up-delay">
        @forelse ($subcategory->prompts as $prompt)
            <a href="{{ route('prompts.show', [$category, $subcategory, $prompt]) }}"
               class="surface block rounded-3xl p-6 transition hover:-translate-y-0.5 hover:border-teal/25">
                <div class="flex flex-wrap items-center gap-2">
                    @if ($prompt->is_best)
                        <span class="rounded-full border border-lime/40 bg-lime/15 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-lime">Best</span>
                    @endif
                    @if ($prompt->is_verified)
                        <span class="rounded-full border border-white/25 bg-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-white">Verified</span>
                    @endif
                    @if ($prompt->is_free)
                        <span class="rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-white/75">Free</span>
                    @endif
                    <x-ai-suggest-tags :platform="$prompt->recommended_platform" :model="$prompt->recommended_model" />
                </div>
                <h2 class="mt-3 font-display text-xl font-700 tracking-tight text-white">{{ $prompt->title }}</h2>
                <p class="mt-3 line-clamp-2 text-sm leading-relaxed text-white/75">{{ $prompt->body }}</p>
                @if ($prompt->tags->isNotEmpty())
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($prompt->tags as $tag)
                            <span class="text-xs text-white/55">#{{ $tag->name }}</span>
                        @endforeach
                    </div>
                @endif
            </a>
        @empty
            <div class="surface rounded-3xl p-10 text-center">
                <p class="text-white/70">No prompts in this specialty yet.</p>
                <a href="{{ route('prompts.create', ['subcategory_id' => $subcategory->id]) }}" class="btn-primary mt-5">
                    Create the first one
                </a>
            </div>
        @endforelse
    </div>
</section>
@endsection
