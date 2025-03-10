<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\FetchNewsCommand;

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
        Vite::prefetch(concurrency: 3);

        Route::aliasMiddleware('role', RoleMiddleware::class);
        Route::aliasMiddleware('permission', PermissionMiddleware::class);
        Route::aliasMiddleware('roles_or_permissions', RoleOrPermissionMiddleware::class);

        $this->app->booted(function () {
            $schedule = app(Schedule::class);
            $schedule->command('fetch:news')->hourly();
        });

    }
}
