<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        $this->configureRateLimiting();
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        $limits = config('business.rate_limits');

        // Authentication endpoints - strict limits
        RateLimiter::for('auth', function (Request $request) use ($limits) {
            return Limit::perMinute($limits['auth_per_minute'])->by($request->ip());
        });

        // General API endpoints - moderate limits
        RateLimiter::for('api', function (Request $request) use ($limits) {
            return $request->user()
                ? Limit::perMinute($limits['api_authenticated_per_minute'])->by($request->user()->id)
                : Limit::perMinute($limits['api_guest_per_minute'])->by($request->ip());
        });

        // Profile updates - once per day protection
        RateLimiter::for('profile', function (Request $request) use ($limits) {
            return Limit::perDay($limits['profile_updates_per_day'])->by($request->user()->id);
        });

        // Points claiming - prevent abuse
        RateLimiter::for('points', function (Request $request) use ($limits) {
            return Limit::perMinute($limits['points_claims_per_minute'])->by($request->user()->id);
        });

        // Admin operations - very restricted
        RateLimiter::for('admin', function (Request $request) use ($limits) {
            return Limit::perMinute($limits['admin_operations_per_minute'])->by($request->user()->id);
        });
    }
}
