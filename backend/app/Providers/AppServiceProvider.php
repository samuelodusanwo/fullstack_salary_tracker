<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;

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
        //
        $this->configureRateLimiters();
    }

    protected function configureRateLimiters()
    {
        RateLimiter::for('api_limiter', function (Request $request) {
            return Limit::perMinute(120)->by($request->ip())
                ->response(function (Request $request, array $headers) {
                    $time = $headers['Retry-After'] ?? 1;
                    return response()->json([
                        'status' => false,
                        'status_code' => 429,
                        'message' => "Too many requests, try again in {$time} seconds.",
                        'data' => null,
                        'errors' => ['request' => "Too many requests, please try again in {$time} seconds."]
                    ], 429);
                });
        });
    }
}
