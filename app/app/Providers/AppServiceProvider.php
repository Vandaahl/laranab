<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;
use Illuminate\Support\Facades;

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

        // Make the categories available in the nav component.
        Facades\View::composer('components.nav', function (View $view) {
            $categories = Category::whereNull('parent_id')
                ->with('children')
                ->get();

            $view->with('categories', $categories);
        });
    }
}
