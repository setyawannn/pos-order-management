<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

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

        $devEnv = env("APP_ENV") == "development";

        if ($devEnv) {
            URL::forceScheme("https");

            Model::preventLazyLoading();
            Model::preventSilentlyDiscardingAttributes();
        }

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        Inertia::share([
            'flash' => function () {
                return [
                    'success' => session('success'),
                    'error' => session('error'),
                    'info' => session('info'),
                    'warning' => session('warning'),
                ];
            },
            'auth.user' => function () {
                $user = Auth::user();
                if ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                    ];
                }
                return null;
            },
        ]);
    }
}
