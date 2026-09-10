@extends('layouts.app')

@section('title', $prompt->title.' — bAI')

@section('content')
<section class="shell py-14 sm:py-20">
    <nav class="mb-8 text-sm text-ink/50">
        <a href="{{ route('home') }}" class="hover:text-teal">Library</a>
        <span class="mx-2">/</span>
        <a href="{{ route('categories.show', $category) }}" class="hover:text-teal">{{ $category->name }}</a>
        <span class="mx-2">/</span>
        <a href="{{ route('subcategories.show', [$category, $subcategory]) }}" class="hover:text-teal">{{ $subcategory->name }}</a>
        <span class="mx-2">/</span>
        <span class="text-ink">Prompt</span>
    </nav>

    <div class="grid gap-10 lg:grid-cols-[1.4fr_0.8fr]">
        <div class="fade-up">
            <div class="flex flex-wrap items-center gap-2">
                @if ($prompt->is_best)
                    <span class="rounded-full bg-teal/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-teal">Best</span>
                @endif
                @if ($prompt->is_verified)
                    <span class="rounded-full bg-ink text-paper px-3 py-1 text-xs font-semibold uppercase tracking-wider">Verified</span>
                @endif
                @if ($prompt->is_free)
                    <span class="rounded-full bg-ink/5 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-ink/55">Free</span>
                @endif
                <x-ai-suggest-tags :platform="$prompt->recommended_platform" :model="$prompt->recommended_model" />
                <span class="text-xs uppercase tracking-wider text-ink/40">{{ $prompt->status }}</span>
            </div>

            <h1 class="mt-4 font-display text-4xl font-800 tracking-tight sm:text-5xl">{{ $prompt->title }}</h1>

            <div class="surface mt-8 rounded-3xl p-6 sm:p-8">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-ink/40">Prompt body</p>
                    <livewire:copy-prompt-button :promptId="$prompt->id" :body="$prompt->body" />
                </div>
                <div class="prompt-body">{{ $prompt->body }}</div>
            </div>

            @if ($prompt->tip_note)
                <aside class="mt-6 rounded-3xl border border-amber/25 bg-amber/5 p-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-amber">One tip</p>
                    <p class="mt-3 text-sm leading-relaxed text-ink/75">{{ $prompt->tip_note }}</p>
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
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-ink/40">Best with</p>
                    <div class="mt-3">
                        <x-ai-suggest-tags :platform="$prompt->recommended_platform" :model="$prompt->recommended_model" />
                    </div>
                    <p class="mt-3 text-xs leading-relaxed text-ink/50">Suggested AI platform and model for this prompt.</p>
                </div>
            @endif

            @if ($prompt->tags->isNotEmpty())
                <div class="surface rounded-3xl p-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-ink/40">Tags</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($prompt->tags as $tag)
                            <span class="rounded-full bg-ink/5 px-3 py-1 text-xs text-ink/60">{{ $tag->name }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($related->isNotEmpty())
                <div class="surface rounded-3xl p-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-ink/40">More in {{ $subcategory->name }}</p>
                    <ul class="mt-4 space-y-3">
                        @foreach ($related as $item)
                            <li>
                                <a href="{{ route('prompts.show', [$category, $subcategory, $item]) }}"
                                   class="text-sm font-medium text-ink transition hover:text-teal">
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
