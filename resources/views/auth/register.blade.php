<x-guest-layout>
    <h1 class="font-display mb-2 text-3xl uppercase text-magenta sm:text-4xl">Sign up</h1>
    <p class="font-soft mb-8 text-lg text-white/70">Save prompts, mark best, and manage your library.</p>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <div>
            <label for="name" class="font-accent mb-2 block text-sm font-bold uppercase tracking-wider text-cyan">Name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                   class="w-full rounded-2xl border-2 border-white/20 bg-white px-4 py-3 text-deep outline-none focus:border-lime">
            <x-input-error :messages="$errors->get('name')" class="mt-2 text-coral" />
        </div>

        <div>
            <label for="email" class="font-accent mb-2 block text-sm font-bold uppercase tracking-wider text-cyan">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                   class="w-full rounded-2xl border-2 border-white/20 bg-white px-4 py-3 text-deep outline-none focus:border-lime">
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-coral" />
        </div>

        <div>
            <label for="password" class="font-accent mb-2 block text-sm font-bold uppercase tracking-wider text-cyan">Password</label>
            <input id="password" type="password" name="password" required autocomplete="new-password"
                   class="w-full rounded-2xl border-2 border-white/20 bg-white px-4 py-3 text-deep outline-none focus:border-lime">
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-coral" />
        </div>

        <div>
            <label for="password_confirmation" class="font-accent mb-2 block text-sm font-bold uppercase tracking-wider text-cyan">Confirm password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                   class="w-full rounded-2xl border-2 border-white/20 bg-white px-4 py-3 text-deep outline-none focus:border-lime">
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-coral" />
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
            <a href="{{ route('login') }}" class="font-accent text-sm font-bold uppercase text-cyan hover:text-lime">
                Already registered?
            </a>
            <button type="submit" class="btn-primary">Create account</button>
        </div>
    </form>
</x-guest-layout>
