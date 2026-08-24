<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register MCP Service
        $this->app->singleton(\App\Services\Communication\Chat\MCP\ExamManagementMCPService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Ensure signed URLs generated on HTTPS production sites stay HTTPS
        // when TLS is terminated by a reverse proxy.
        if (app()->environment('production')
            && parse_url((string) config('app.url'), PHP_URL_SCHEME) === 'https') {
            URL::forceScheme('https');
        }

        Paginator::useBootstrapFive();

        // System is the explicit unrestricted staff role. This covers Laravel
        // gates and Blade @can checks; student-only routes remain protected by
        // their dedicated role middleware and are not affected here.
        Gate::before(function (User $user): ?bool {
            return $user->isSystemUser() ? true : null;
        });

        // Register Role and Permission observers for AuthCentral sync
        \Spatie\Permission\Models\Role::observe(\App\Observers\RoleObserver::class);
        \Spatie\Permission\Models\Permission::observe(\App\Observers\PermissionObserver::class);
    }
}
