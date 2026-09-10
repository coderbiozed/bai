@extends('layouts.app')

@section('title', 'Edit prompt — bAI')

@section('content')
<section class="shell py-14 sm:py-20">
    <div class="mx-auto max-w-2xl fade-up">
        <h1 class="font-display text-4xl font-extrabold tracking-tight">Edit prompt</h1>
        <p class="mt-3 text-sm text-white/70">Edits create a version history entry for future refine/review.</p>

        <form method="POST" action="{{ route('prompts.update', [$category, $subcategory, $prompt]) }}"
              class="surface mt-10 rounded-3xl p-6 sm:p-8">
            @csrf
            @method('PUT')
            @include('prompts._form', ['prompt' => $prompt, 'submitLabel' => 'Update prompt'])
        </form>
    </div>
</section>
@endsection
