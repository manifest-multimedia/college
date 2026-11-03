<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\PermissionObserver;
use App\Observers\RoleObserver;
use App\Services\Communication\Chat\MCP\ExamManagementMCPService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register MCP Service
        $this->app->singleton(ExamManagementMCPService::class);
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

        Gate::define('manage-canteen-qualifications', fn (User $user) => $user->hasAnyRole(['System', 'Super Admin', 'Administrator', 'Finance Manager'])
            || $user->checkPermissionTo('canteen.manage qualifications'));

        Gate::define('manage-canteen-meals', fn (User $user) => $user->hasAnyRole(['System', 'Super Admin', 'Administrator'])
            || $user->checkPermissionTo('canteen.manage meals'));

        // Register Role and Permission observers for AuthCentral sync
        Role::observe(RoleObserver::class);
        Permission::observe(PermissionObserver::class);
    }
}
