@extends('layouts.app')

@section('title', 'Profile — bAI')

@section('content')
<section class="shell py-14 sm:py-20">
    <h1 class="font-display text-4xl uppercase text-lime sm:text-6xl">Profile</h1>
    <p class="font-soft mt-3 text-xl text-white/70">Manage your account.</p>

    <div class="mt-10 space-y-6">
        <div class="surface rounded-[2rem] p-6 sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>
        <div class="surface rounded-[2rem] p-6 sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>
        <div class="surface rounded-[2rem] p-6 sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</section>
@endsection
