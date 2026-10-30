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
        // Otomatis mendeteksi folder public_html jika dihosting di cPanel
        foreach ([
            base_path('../../public_html'),
            base_path('../public_html'),
        ] as $candidate) {
            if (is_dir($candidate)) {
                $this->app->usePublicPath(realpath($candidate));
                break;
            }
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
