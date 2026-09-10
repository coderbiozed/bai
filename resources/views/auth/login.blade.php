<x-guest-layout>
    <h1 class="font-display mb-2 text-3xl uppercase text-lime sm:text-4xl">Log in</h1>
    <p class="font-soft mb-8 text-lg text-white/70">Access your verified prompt workspace.</p>

    <x-auth-session-status class="mb-4 font-accent text-lime" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="font-accent mb-2 block text-sm font-bold uppercase tracking-wider text-cyan">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                   class="w-full rounded-2xl border-2 border-white/20 bg-white px-4 py-3 text-deep outline-none focus:border-lime">
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-coral" />
        </div>

        <div>
            <label for="password" class="font-accent mb-2 block text-sm font-bold uppercase tracking-wider text-cyan">Password</label>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                   class="w-full rounded-2xl border-2 border-white/20 bg-white px-4 py-3 text-deep outline-none focus:border-lime">
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-coral" />
        </div>

        <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-white/70">
            <input id="remember_me" type="checkbox" name="remember" class="rounded border-white/30 text-lime focus:ring-lime">
            Remember me
        </label>

        <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="font-accent text-sm font-bold uppercase text-magenta hover:text-sun">
                    Forgot password?
                </a>
            @endif
            <button type="submit" class="btn-primary">Log in</button>
        </div>
    </form>

    <p class="mt-8 text-center text-white/60">
        New here?
        <a href="{{ route('register') }}" class="font-accent font-bold text-lime hover:text-cyan">Create an account</a>
    </p>
</x-guest-layout>
