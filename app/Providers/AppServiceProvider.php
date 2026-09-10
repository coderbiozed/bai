<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $renderUrl = env('RENDER_EXTERNAL_URL');
        if (is_string($renderUrl) && $renderUrl !== '') {
            URL::forceRootUrl(rtrim($renderUrl, '/'));
            URL::forceScheme('https');
        }
    }
}
