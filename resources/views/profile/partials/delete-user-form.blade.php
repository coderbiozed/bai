<section>
    <header>
        <h2 class="font-display text-2xl uppercase text-coral">{{ __('Delete Account') }}</h2>
        <p class="font-soft mt-2 text-white/70">{{ __('Once your account is deleted, all of its resources and data will be permanently deleted.') }}</p>
    </header>

    <form method="post" action="{{ route('profile.destroy') }}" class="mt-6 space-y-5"
          onsubmit="return confirm('Delete your account permanently?')">
        @csrf
        @method('delete')

        <div>
            <label for="password" class="font-accent mb-2 block text-sm font-bold uppercase tracking-wider text-cyan">Password</label>
            <input id="password" name="password" type="password" placeholder="{{ __('Password') }}"
                   class="w-full rounded-2xl border-2 border-white/20 bg-white px-4 py-3 text-deep outline-none focus:border-coral">
            <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2 text-coral" />
        </div>

        <button type="submit" class="btn bg-coral text-deep hover:bg-magenta hover:text-white">
            {{ __('Delete Account') }}
        </button>
    </form>
</section>
