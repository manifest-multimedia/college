<?php

namespace App\Services;

class ThemeService
{
    /**
     * Get the current authentication theme
     */
    public function getCurrentTheme(): string
    {
        return config('branding.auth_theme', 'default');
    }

    /**
     * Get available themes
     */
    public function getAvailableThemes(): array
    {
        return config('branding.available_themes', []);
    }

    /**
     * Get theme-specific view name with fallback
     */
    public function getThemeView(string $view): string
    {
        $theme = $this->getCurrentTheme();

        $candidates = [
            "custom-auth.themes.{$theme}.{$view}",
            "custom-auth.themes.default.{$view}",
            "custom-auth.{$view}",
        ];

        foreach ($candidates as $candidate) {
            if (view()->exists($candidate)) {
                return $candidate;
            }
        }

        return "custom-auth.{$view}";
    }

    /**
     * Get institution configuration
     */
    public function getInstitution(): array
    {
        return config('branding.institution', []);
    }

    /**
     * Get logo configuration
     */
    public function getLogos(): array
    {
        return config('branding.logo', []);
    }

    /**
     * Get primary logo path
     */
    public function getLogo(string $type = 'primary'): string
    {
        $logos = $this->getLogos();

        return $logos[$type] ?? $logos['primary'] ?? '/images/logos/default-logo.svg';
    }

    /**
     * Get brand colors as CSS variables
     */
    public function getBrandColorVariables(): array
    {
        $colors = config('branding.colors', []);
        $variables = [];

        foreach ($colors as $name => $value) {
            $variables["--brand-{$name}"] = $value;
        }

        return $variables;
    }

    /**
     * Get staff mail URL
     */
    public function getStaffMailUrl(): string
    {
        return config('branding.institution.staff_mail_url', 'https://mail.google.com');
    }

    /**
     * Check if theme exists
     */
    public function themeExists(string $theme): bool
    {
        $available = $this->getAvailableThemes();

        return isset($available[$theme]);
    }

    /**
     * Get theme configuration
     */
    public function getThemeConfig(string $theme): array
    {
        $available = $this->getAvailableThemes();

        return $available[$theme] ?? [];
    }

    /**
     * Get theme settings
     */
    public function getThemeSettings(): array
    {
        return config('branding.theme_settings', []);
    }
}
