@extends('layouts.app')

@section('title', $prompt->title.' — bAI')

@section('content')
<section class="shell py-14 sm:py-20">
    <nav class="mb-8 text-sm text-white/55">
        <a href="{{ route('home') }}" class="hover:text-cyan">Library</a>
        <span class="mx-2">/</span>
        <a href="{{ route('categories.show', $category) }}" class="hover:text-cyan">{{ $category->name }}</a>
        <span class="mx-2">/</span>
        <a href="{{ route('subcategories.show', [$category, $subcategory]) }}" class="hover:text-cyan">{{ $subcategory->name }}</a>
        <span class="mx-2">/</span>
        <span class="text-white">Prompt</span>
    </nav>

    <div class="grid gap-10 lg:grid-cols-[1.4fr_0.8fr]">
        <div class="fade-up">
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
                <span class="text-xs uppercase tracking-wider text-white/50">{{ $prompt->status }}</span>
            </div>

            <h1 class="mt-4 font-display text-4xl font-800 tracking-tight text-white sm:text-5xl">{{ $prompt->title }}</h1>

            <div class="surface mt-8 rounded-3xl p-6 sm:p-8">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/45">Prompt body</p>
                    <livewire:copy-prompt-button :promptId="$prompt->id" :body="$prompt->body" />
                </div>
                <div class="prompt-body">{{ $prompt->body }}</div>
            </div>

            @if ($prompt->tip_note)
                <aside class="mt-6 rounded-3xl border border-amber/25 bg-amber/5 p-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-amber">One tip</p>
                    <p class="mt-3 text-sm leading-relaxed text-white/80">{{ $prompt->tip_note }}</p>
                </aside>
            @endif
        </div>

        <aside class="fade-up-delay space-y-6">
            <div class="surface rounded-3xl p-6">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/45">Actions</p>
                <div class="mt-4 flex flex-col gap-2">
                    @auth
                        <a href="{{ route('prompts.edit', [$category, $subcategory, $prompt]) }}" class="btn-secondary w-full">Edit prompt</a>
                        <form method="POST" action="{{ route('prompts.destroy', [$category, $subcategory, $prompt]) }}"
                              onsubmit="return confirm('Delete this prompt?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-ghost w-full text-coral">Delete</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="btn-secondary w-full">Log in to edit</a>
                    @endauth
                </div>
                <p class="mt-5 text-xs text-white/45">Copied {{ $prompt->copy_count }} times</p>
            </div>

            @if ($prompt->recommended_platform || $prompt->recommended_model)
                <div class="surface rounded-3xl p-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/45">Best with</p>
                    <div class="mt-3">
                        <x-ai-suggest-tags :platform="$prompt->recommended_platform" :model="$prompt->recommended_model" />
                    </div>
                    <p class="mt-3 text-xs leading-relaxed text-white/55">Suggested AI platform and model for this prompt.</p>
                </div>
            @endif

            @if ($prompt->tags->isNotEmpty())
                <div class="surface rounded-3xl p-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/45">Tags</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($prompt->tags as $tag)
                            <span class="rounded-full bg-white/10 px-3 py-1 text-xs text-white/75">{{ $tag->name }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($related->isNotEmpty())
                <div class="surface rounded-3xl p-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/45">More in {{ $subcategory->name }}</p>
                    <ul class="mt-4 space-y-3">
                        @foreach ($related as $item)
                            <li>
                                <a href="{{ route('prompts.show', [$category, $subcategory, $item]) }}"
                                   class="text-sm font-medium text-white/85 transition hover:text-cyan">
                                    {{ $item->title }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </aside>
    </div>
</section>
@endsection
