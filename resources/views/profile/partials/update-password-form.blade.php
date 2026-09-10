<section>
    <header>
        <h2 class="font-display text-2xl uppercase text-sun">{{ __('Update Password') }}</h2>
        <p class="font-soft mt-2 text-white/70">{{ __('Ensure your account is using a long, random password to stay secure.') }}</p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="font-accent mb-2 block text-sm font-bold uppercase tracking-wider text-cyan">Current password</label>
            <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password"
                   class="w-full rounded-2xl border-2 border-white/20 bg-white px-4 py-3 text-deep outline-none focus:border-lime">
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2 text-coral" />
        </div>

        <div>
            <label for="update_password_password" class="font-accent mb-2 block text-sm font-bold uppercase tracking-wider text-cyan">New password</label>
            <input id="update_password_password" name="password" type="password" autocomplete="new-password"
                   class="w-full rounded-2xl border-2 border-white/20 bg-white px-4 py-3 text-deep outline-none focus:border-lime">
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2 text-coral" />
        </div>

        <div>
            <label for="update_password_password_confirmation" class="font-accent mb-2 block text-sm font-bold uppercase tracking-wider text-cyan">Confirm password</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                   class="w-full rounded-2xl border-2 border-white/20 bg-white px-4 py-3 text-deep outline-none focus:border-lime">
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2 text-coral" />
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="btn-primary">{{ __('Save') }}</button>
            @if (session('status') === 'password-updated')
                <p class="font-accent text-sm font-bold text-lime">{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
