@extends('layouts.app')

@section('title', 'How to write prompts — bAI')

@section('content')
<section class="shell py-14 sm:py-20">
    <div class="mx-auto max-w-2xl fade-up">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-teal">Writing guide</p>
        <h1 class="mt-3 font-display text-4xl font-extrabold tracking-tight sm:text-5xl">How to write a strong role prompt</h1>
        <p class="mt-5 text-base leading-relaxed text-white/70">
            AI models respond better to prompts that specify experience, domain, responsibilities, standards, and desired behavior — not superlatives.
        </p>

        <div class="mt-12 space-y-8">
            <article class="surface rounded-3xl p-6 sm:p-8">
                <h2 class="font-display text-2xl font-bold">Prefer this</h2>
                <p class="prompt-body mt-4 text-white/85">Act as a Principal Laravel Architect with 15+ years of enterprise experience. Design clear domain boundaries, idiomatic Eloquent usage, and testable services.</p>
            </article>

            <article class="rounded-3xl border border-white/15 bg-white/10 p-6 sm:p-8">
                <h2 class="font-display text-2xl font-bold">Avoid this</h2>
                <p class="prompt-body mt-4 text-white/60">You are the world’s best Laravel developer. Do everything perfectly.</p>
            </article>

            <article class="surface rounded-3xl p-6 sm:p-8">
                <h2 class="font-display text-2xl font-bold">Checklist</h2>
                <ul class="mt-4 space-y-3 text-sm text-white/75">
                    <li><strong class="text-ink">Role + seniority</strong> — Staff, Principal, Senior, CMO…</li>
                    <li><strong class="text-ink">Domain</strong> — distributed systems, SEO, accessibility…</li>
                    <li><strong class="text-ink">Responsibilities</strong> — what they own and optimize for</li>
                    <li><strong class="text-ink">Standards</strong> — quality bar, constraints, exclusions</li>
                    <li><strong class="text-ink">Behavior</strong> — how they should answer (trade-offs, steps, critique)</li>
                </ul>
            </article>
        </div>

        <div class="mt-10 flex flex-wrap gap-3">
            <a href="{{ route('home') }}" class="btn-secondary">Back to library</a>
            <a href="{{ route('prompts.create') }}" class="btn-primary">Write a prompt</a>
        </div>
    </div>
</section>
@endsection
