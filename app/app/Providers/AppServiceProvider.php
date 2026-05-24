<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
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
        // Throw an exception when attempting to fill an unfillable attribute.
        Model::preventSilentlyDiscardingAttributes($this->app->isLocal());

        RateLimiter::for('tmdb', function () {
            return Limit::perSecond(40);
        });
    }
}
