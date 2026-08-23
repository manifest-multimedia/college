<?php

namespace Tests\Feature;

use App\Services\BrandingSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class BrandingSettingsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_branding_changes_are_persisted_without_writing_the_environment_file(): void
    {
        $service = app(BrandingSettingsService::class);

        $service->update([
            'AUTH_THEME' => 'modern',
            'INSTITUTION_SHORT_NAME' => 'C360',
            'SHOW_REGULAR_LOGIN' => 'false',
        ]);

        $this->assertDatabaseHas('system_settings', [
            'key' => 'branding.configuration.overrides',
            'group' => 'branding',
        ]);
        $this->assertSame('modern', config('branding.auth_theme'));
        $this->assertSame('C360', config('branding.institution.short_name'));
        $this->assertFalse(config('branding.theme_settings.show_regular_login'));

        config([
            'branding.auth_theme' => 'default',
            'branding.institution.short_name' => 'College',
            'branding.theme_settings.show_regular_login' => true,
        ]);
        Cache::forget(BrandingSettingsService::CACHE_KEY);

        app(BrandingSettingsService::class)->apply();

        $this->assertSame('modern', config('branding.auth_theme'));
        $this->assertSame('C360', config('branding.institution.short_name'));
        $this->assertFalse(config('branding.theme_settings.show_regular_login'));
    }
}
