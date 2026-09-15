<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="bAI — a loud verified prompt library with step-by-step journeys.">
    <title>@yield('title', $title ?? 'bAI')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans" x-data>
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden" aria-hidden="true">
        <div class="float-orb" style="width: 22rem; height: 22rem; left: -4rem; top: 10%; background: #ff2ebd;"></div>
        <div class="float-orb" style="width: 28rem; height: 28rem; right: -6rem; top: 20%; background: #2de2e6; animation-delay: -2s;"></div>
        <div class="float-orb" style="width: 18rem; height: 18rem; left: 35%; bottom: 5%; background: #c8f542; animation-delay: -4s;"></div>
    </div>

    <header class="shell pt-6">
        <nav class="flex flex-wrap items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="group flex flex-col sm:flex-row sm:items-baseline sm:gap-3">
                <span class="font-display text-3xl leading-none text-lime transition duration-300 group-hover:text-magenta sm:text-5xl">
                    bAI
                </span>
                <span class="font-accent text-sm font-bold uppercase tracking-[0.2em] text-cyan sm:text-base">
                    Prompt Chaos Lab
                </span>
            </a>

            <div class="flex flex-wrap items-center gap-2 sm:gap-4">
                <a href="{{ route('categories.show', 'instant-solutions') }}" class="nav-link hidden md:inline text-sun">Instant</a>
                <a href="{{ route('journeys.index') }}" class="nav-link hidden md:inline">Journeys</a>
                <a href="{{ route('search') }}" class="nav-link hidden md:inline">Search</a>
                <a href="{{ route('guide') }}" class="nav-link hidden lg:inline">Guide</a>

                @auth
                    <a href="{{ route('prompts.create') }}" class="btn-primary">New prompt</a>
                    <a href="{{ route('profile.edit') }}" class="nav-link">{{ Auth::user()->name }}</a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="btn-secondary py-3 px-5 text-sm">Log out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="nav-link">Log in</a>
                    <a href="{{ route('register') }}" class="btn-primary">Sign up</a>
                @endauth
            </div>
        </nav>
    </header>

    @if (session('success'))
        <div class="shell mt-4">
            <div class="surface rounded-2xl border-lime px-5 py-4 font-accent text-lg font-bold text-lime">
                {{ session('success') }}
            </div>
        </div>
    @endif

    <main>
        @isset($slot)
            {{ $slot }}
        @else
            @yield('content')
        @endisset
    </main>

    <footer class="shell mt-28 border-t-4 border-lime/40 py-14">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="font-display text-4xl text-magenta sm:text-6xl">bAI</p>
                <p class="font-soft mt-4 max-w-xl text-xl text-white/70">
                    Verified prompts. Giant steps. Loud craft. No “world’s best” fluff.
                </p>
            </div>
            <div class="flex flex-wrap gap-5 font-accent text-lg font-bold uppercase">
                <a href="{{ route('journeys.index') }}" class="text-cyan hover:text-lime">Journeys</a>
                <a href="{{ route('guide') }}" class="text-sun hover:text-coral">Guide</a>
                <a href="{{ route('search') }}" class="text-magenta hover:text-cyan">Search</a>
                @guest
                    <a href="{{ route('login') }}" class="text-lime hover:text-sun">Log in</a>
                @endguest
            </div>
        </div>
    </footer>

    @livewireScripts
    @stack('scripts')
</body>
</html>
