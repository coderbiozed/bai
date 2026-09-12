@extends('layouts.app')

@section('title', ($q ? "Search: {$q}" : 'Search').' — bAI')

@section('content')
<section class="shell py-14 sm:py-20">
    <div class="max-w-2xl fade-up">
        <h1 class="font-display text-4xl font-extrabold tracking-tight">Search prompts</h1>
        <p class="mt-3 text-sm text-white/70">Find prompts across every category and specialty.</p>
    </div>

    <form method="GET" action="{{ route('search') }}" class="mt-8 fade-up-delay">
        <div class="flex flex-col gap-3 sm:flex-row">
            <input type="search" name="q" value="{{ $q }}" placeholder="React, SEO, Laravel, photo…"
                   class="w-full flex-1 rounded-full border border-white/20 bg-white text-deep px-5 py-3 text-sm outline-none focus:border-teal">
            <button type="submit" class="btn-primary">Search</button>
        </div>
    </form>

    <div class="mt-10 space-y-4 fade-up-delay-2">
        @forelse ($prompts as $prompt)
            <a href="{{ route('prompts.show', [$prompt->subcategory->category, $prompt->subcategory, $prompt]) }}"
               class="surface block rounded-3xl p-6 transition hover:-translate-y-0.5 hover:border-teal/25">
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-teal">
                    {{ $prompt->subcategory->category->name }} · {{ $prompt->subcategory->name }}
                </p>
                <div class="mt-2 flex flex-wrap items-center gap-2">
                    <h2 class="font-display text-xl font-bold tracking-tight">{{ $prompt->title }}</h2>
                    @if ($prompt->is_best)
                        <span class="rounded-full border border-lime/40 bg-lime/15 px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-lime">Best</span>
                    @endif
                    <x-ai-suggest-tags :platform="$prompt->recommended_platform" :model="$prompt->recommended_model" />
                </div>
                <p class="mt-3 line-clamp-2 text-sm text-white/75">{{ $prompt->body }}</p>
            </a>
        @empty
            <div class="surface rounded-3xl p-10 text-center text-white/60">
                @if ($q === '')
                    Type a keyword to search the library.
                @else
                    No prompts matched “{{ $q }}”.
                @endif
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $prompts->links() }}
    </div>
</section>
@endsection
