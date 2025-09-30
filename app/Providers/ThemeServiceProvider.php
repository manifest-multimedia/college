<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;

class ThemeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton('theme', function ($app) {
            return new \App\Services\ThemeService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share branding configuration with all views
        View::share('branding', config('branding'));
        
        // Register theme-specific view paths
        $this->registerThemeViews();
        
        // Register Blade directives for theming
        $this->registerBladeDirectives();
        
        // Share theme variables with views
        $this->shareThemeVariables();
    }

    /**
     * Register theme-specific view paths
     */
    protected function registerThemeViews(): void
    {
        $theme = config('branding.auth_theme', 'default');
        
        // Add theme-specific view paths for custom-auth
        $themePath = resource_path("views/custom-auth/themes/{$theme}");
        if (is_dir($themePath)) {
            View::addNamespace('theme', $themePath);
        }
        
        // Add fallback to default theme
        $defaultPath = resource_path('views/custom-auth/themes/default');
        if (is_dir($defaultPath)) {
            View::addNamespace('theme-default', $defaultPath);
        }
    }

    /**
     * Register Blade directives for theming
     */
    protected function registerBladeDirectives(): void
    {
        // @theme('view-name') - loads theme-specific view with fallback
        Blade::directive('theme', function ($expression) {
            return "<?php echo view()->first([
                'theme::' . {$expression},
                'theme-default::' . {$expression},
                'custom-auth.' . {$expression}
            ]); ?>";
        });

        // @themeStyle('style-name') - loads theme-specific CSS
        Blade::directive('themeStyle', function ($expression) {
            $theme = config('branding.auth_theme', 'default');
            return "<?php echo '<link rel=\"stylesheet\" href=\"' . asset(\"css/themes/{$theme}/\" . {$expression} . '.css') . '\">'; ?>";
        });

        // @brandColor('color-name') - outputs brand color CSS variable
        Blade::directive('brandColor', function ($expression) {
            return "<?php echo 'var(--brand-' . {$expression} . ')'; ?>";
        });
    }

    /**
     * Share theme variables with views
     */
    protected function shareThemeVariables(): void
    {
        View::composer('*', function ($view) {
            $colors = config('branding.colors', []);
            $cssVariables = [];
            
            foreach ($colors as $name => $value) {
                $cssVariables["--brand-{$name}"] = $value;
            }
            
            $view->with('brandColors', $cssVariables);
            $view->with('currentTheme', config('branding.auth_theme', 'default'));
        });
    }
}