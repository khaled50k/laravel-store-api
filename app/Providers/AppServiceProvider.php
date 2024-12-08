<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
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

      

        // Globally register the 'admin' middleware alias for \App\Http\Middleware\AdminMiddleware
        Route::aliasMiddleware('admin', \App\Http\Middleware\AdminMiddleware::class);

        // Register external subscription middleware
        Route::aliasMiddleware('check.external.subscription', \App\Http\Middleware\CheckExternalSubscription::class);
  
    }
}
