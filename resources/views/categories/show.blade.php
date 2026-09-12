@extends('layouts.app')

@section('title', $category->name.' — bAI')

@section('content')
<section class="shell py-14 sm:py-20">
    <nav class="mb-8 text-sm text-white/55">
        <a href="{{ route('home') }}" class="hover:text-cyan">Library</a>
        <span class="mx-2">/</span>
        <span class="text-white">{{ $category->name }}</span>
    </nav>

    <div class="max-w-2xl fade-up">
        <h1 class="font-display text-4xl font-extrabold tracking-tight sm:text-5xl">{{ $category->name }}</h1>
        <p class="mt-4 text-base text-white/70">{{ $category->description }}</p>
    </div>

    <div class="mt-12 divide-y divide-white/15 border-y border-white/15 fade-up-delay">
        @forelse ($category->subcategories as $subcategory)
            <a href="{{ route('subcategories.show', [$category, $subcategory]) }}"
               class="group flex items-center justify-between gap-4 py-6">
                <div>
                    <h2 class="font-display text-2xl font-bold tracking-tight transition group-hover:text-teal">
                        {{ $subcategory->name }}
                    </h2>
                    @if ($subcategory->description)
                        <p class="mt-2 text-sm text-white/60">{{ $subcategory->description }}</p>
                    @endif
                </div>
                <div class="shrink-0 text-sm text-white/50">
                    {{ $subcategory->prompts_count }}
                    {{ Str::plural('prompt', $subcategory->prompts_count) }}
                </div>
            </a>
        @empty
            <p class="py-10 text-white/60">No specialties yet.</p>
        @endforelse
    </div>
</section>
@endsection
