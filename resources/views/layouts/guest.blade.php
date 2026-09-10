<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'bAI') }} — Auth</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans">
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden" aria-hidden="true">
        <div class="float-orb" style="width: 22rem; height: 22rem; left: -4rem; top: 10%; background: #ff2ebd;"></div>
        <div class="float-orb" style="width: 28rem; height: 28rem; right: -6rem; top: 20%; background: #2de2e6; animation-delay: -2s;"></div>
    </div>

    <div class="shell flex min-h-screen flex-col items-center justify-center py-12">
        <a href="{{ route('home') }}" class="mb-8 text-center">
            <span class="font-display block text-5xl text-lime sm:text-7xl">bAI</span>
            <span class="font-accent mt-2 block text-sm font-bold uppercase tracking-[0.22em] text-cyan">Prompt Chaos Lab</span>
        </a>

        <div class="surface w-full max-w-lg rounded-[2rem] p-8 sm:p-10">
            {{ $slot }}
        </div>
    </div>
</body>
</html>
