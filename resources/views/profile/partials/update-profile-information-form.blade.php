<section>
    <header>
        <h2 class="font-display text-2xl uppercase text-lime">{{ __('Profile Information') }}</h2>
        <p class="font-soft mt-2 text-white/70">{{ __("Update your account's profile information and email address.") }}</p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="font-accent mb-2 block text-sm font-bold uppercase tracking-wider text-cyan">Name</label>
            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name"
                   class="w-full rounded-2xl border-2 border-white/20 bg-white px-4 py-3 text-deep outline-none focus:border-lime">
            <x-input-error class="mt-2 text-coral" :messages="$errors->get('name')" />
        </div>

        <div>
            <label for="email" class="font-accent mb-2 block text-sm font-bold uppercase tracking-wider text-cyan">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username"
                   class="w-full rounded-2xl border-2 border-white/20 bg-white px-4 py-3 text-deep outline-none focus:border-lime">
            <x-input-error class="mt-2 text-coral" :messages="$errors->get('email')" />
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="btn-primary">{{ __('Save') }}</button>
            @if (session('status') === 'profile-updated')
                <p class="font-accent text-sm font-bold text-lime">{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
