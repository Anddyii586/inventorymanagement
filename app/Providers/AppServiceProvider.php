<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Events\Logout;
use App\Listeners\LogoutSsoSession;

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
        // Register logout event listener to logout SSO session
        \Illuminate\Support\Facades\Event::listen(
            Logout::class,
            LogoutSsoSession::class
        );
    }
}
