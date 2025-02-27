<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('evi')
                ->group(base_path('routes/api.php'));

            Route::middleware('api')
                ->prefix('eviweb')
                ->group(base_path('routes/web.php'));

            Route::middleware('web')
                ->prefix('evifiles')
                ->group(base_path('routes/web.php'));

            Route::middleware('api')
                ->prefix('sipd-api')
                ->group(base_path('routes/sipd_api_brks.php'));

            Route::middleware('api')
                ->prefix('evi-brks')
                ->group(base_path('routes/brks.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
