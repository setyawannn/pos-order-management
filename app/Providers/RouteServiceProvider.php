<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit; // Make sure this is imported
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider; // Base ServiceProvider
use Illuminate\Http\Request; // Make sure Request is imported
use Illuminate\Support\Facades\RateLimiter; // Make sure RateLimiter is imported
use Illuminate\Support\Facades\Route; // Make sure Route facade is imported for route binding/grouping (standard)
use Inertia\Inertia;

class RouteServiceProvider extends ServiceProvider // The main class declaration
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard'; // Adjust if your dashboard is at a different path

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        // This method configures the rate limiters
        $this->configureRateLimiting();

        // This method maps your route files
        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php')); // Make sure your api.php is mapped correctly

            Route::middleware('web')
                ->group(base_path('routes/web.php')); // Make sure your web.php is mapped correctly
        });
    }

    /**
     * Configure the application's rate limiters.
     */
    protected function configureRateLimiting(): void
    {
        // Default API Rate Limiter (usually 60 requests per minute per IP)
        // This is automatically applied to routes in api.php via the 'api' middleware group.
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Specific Limiter for User Order Submission (Anonymous users are IP-based)
        RateLimiter::for('store-order', function (Request $request) {
            // Allow 5 order submissions per minute per IP address.
            // Returns a JSON response with 429 Too Many Requests status if exceeded.
            return Limit::perMinute(5)->by($request->ip())->response(function () {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many order attempts. Please try again after a moment.',
                    'errors' => ['order' => 'You are submitting orders too quickly. Please wait.']
                ], 429);
            });
        });

        // Specific Limiter for Kitchen Dashboard Refresh (per IP for anonymous, or per user for logged-in chefs)
        RateLimiter::for('kitchen-refresh', function (Request $request) {
            // Allow 10 refreshes per minute per authenticated user (if logged in) or per IP.
            return Limit::perMinute(40)->by($request->user()?->id ?: $request->ip())->response(function () {
                return response()->json([
                    'success' => false,
                    'message' => 'You are refreshing the kitchen board too quickly. Please wait.',
                    'errors' => ['refresh' => 'Too many refresh requests.']
                ], 429);
            });
        });

        // Specific Limiter for User Order Status Check (for anonymous users, IP-based)
        RateLimiter::for('user-order-status', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip())->response(function (Request $request, array $headers) {
                // IMPORTANT: Check if the request is an Inertia request
                if ($request->headers->has('X-Inertia')) { // THIS IS THE FIX
                    // If it's an Inertia request, render an Inertia error page
                    return Inertia::render('errors/TooManyRequests', [
                        'status' => 429,
                        'message' => 'You are checking order status too quickly. Please wait a moment.',
                        'title' => 'Too Many Requests'
                    ])->toResponse($request)->setStatusCode(429);
                }

                // If it's NOT an Inertia request (e.g., standard API fetch), return JSON
                return response()->json([
                    'success' => false,
                    'message' => 'You are checking order status too quickly. Please wait.',
                    'errors' => ['status' => 'Too many status check requests for this order.']
                ], 429, $headers);
            });
        });
    }
}
