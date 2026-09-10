@extends('layouts.app')

@section('title', 'New prompt — bAI')

@section('content')
<section class="shell py-14 sm:py-20">
    <div class="mx-auto max-w-2xl fade-up">
        <h1 class="font-display text-4xl font-extrabold tracking-tight">New prompt</h1>
        <p class="mt-3 text-sm text-white/70">Specify role, domain, standards, and desired behavior — skip “world’s best”.</p>

        <form method="POST" action="{{ route('prompts.store') }}" class="surface mt-10 rounded-3xl p-6 sm:p-8">
            @csrf
            @include('prompts._form', ['prompt' => null, 'submitLabel' => 'Save prompt'])
        </form>
    </div>
</section>
@endsection
