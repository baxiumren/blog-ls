<?php

namespace App\Providers;

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
        try {
            \Illuminate\Support\Facades\View::share('settings', \App\Models\Setting::allCached());
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\View::share('settings', []);
        }
    }
}
