<?php

namespace App\Providers;

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
        // Register Role and Permission observers for AuthCentral sync
        \Spatie\Permission\Models\Role::observe(\App\Observers\RoleObserver::class);
        \Spatie\Permission\Models\Permission::observe(\App\Observers\PermissionObserver::class);
    }
}
