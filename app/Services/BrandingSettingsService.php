<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class BrandingSettingsService
{
    public const CACHE_KEY = 'branding.configuration.overrides';

    /**
     * Settings managed from the branding screen are deliberately stored outside
     * .env.  The web user must never need write access to deployment secrets.
     */
    private const ENVIRONMENT_KEY_MAP = [
        'AUTH_THEME' => 'branding.auth_theme',
        'EXAM_PORTAL_THEME' => 'branding.exam_portal_theme',
        'COLLEGE_LOGO_PRIMARY' => 'branding.logo.primary',
        'COLLEGE_LOGO_WHITE' => 'branding.logo.white',
        'COLLEGE_LOGO_FAVICON' => 'branding.logo.favicon',
        'COLLEGE_LOGO_AUTH' => 'branding.logo.auth',
        'COLLEGE_LOGO_APP' => 'branding.logo.app',
        'PRIMARY_COLOR' => 'branding.colors.primary',
        'SECONDARY_COLOR' => 'branding.colors.secondary',
        'ACCENT_COLOR' => 'branding.colors.accent',
        'SUCCESS_COLOR' => 'branding.colors.success',
        'WARNING_COLOR' => 'branding.colors.warning',
        'DANGER_COLOR' => 'branding.colors.danger',
        'INSTITUTION_NAME' => 'branding.institution.name',
        'INSTITUTION_SHORT_NAME' => 'branding.institution.short_name',
        'STAFF_MAIL_URL' => 'branding.institution.staff_mail_url',
        'STUDENT_PORTAL_URL' => 'branding.institution.student_portal_url',
        'INSTITUTION_WEBSITE_URL' => 'branding.institution.website_url',
        'SUPPORT_EMAIL' => 'branding.institution.support_email',
        'INSTITUTION_PHONE' => 'branding.institution.phone',
        'INSTITUTION_ADDRESS' => 'branding.institution.address',
        'SHOW_INSTITUTION_NAME' => 'branding.theme_settings.show_institution_name',
        'SHOW_BACKGROUND_PATTERN' => 'branding.theme_settings.show_background_pattern',
        'ENABLE_AUTH_ANIMATIONS' => 'branding.theme_settings.enable_animations',
        'AUTH_CARD_STYLE' => 'branding.theme_settings.card_style',
        'SHOW_AUTH_CENTRAL_BUTTON' => 'branding.theme_settings.show_auth_central_button',
        'SHOW_REGULAR_LOGIN' => 'branding.theme_settings.show_regular_login',
        'LECTURER_ACCESS_MODE' => 'branding.theme_settings.lecturer_access_mode',
        'STUDENT_ID_FORMAT' => 'branding.student_id.format',
        'STUDENT_ID_CUSTOM_PATTERN' => 'branding.student_id.custom_pattern',
        'STUDENT_ID_ALPHABETICAL_ORDERING' => 'branding.student_id.enable_alphabetical_ordering',
        'STUDENT_ID_INSTITUTION_PREFIX' => 'branding.student_id.institution_prefix',
        'STUDENT_ID_INSTITUTION_SIMPLE' => 'branding.student_id.institution_simple',
        'STUDENT_ID_SEQUENCE_RESET_YEARLY' => 'branding.student_id.sequence_reset_yearly',
        'STUDENT_ID_SEQUENCE_START' => 'branding.student_id.sequence_start',
    ];

    public function apply(): void
    {
        foreach ($this->overrides() as $key => $value) {
            config([$key => $value]);
        }
    }

    public function update(array $environmentValues): void
    {
        $overrides = $this->overrides();

        foreach ($environmentValues as $environmentKey => $value) {
            $configKey = self::ENVIRONMENT_KEY_MAP[$environmentKey] ?? null;

            if (! $configKey) {
                continue;
            }

            if ($value === null || $value === 'null') {
                unset($overrides[$configKey]);
                continue;
            }

            $overrides[$configKey] = $this->normaliseValue($value);
        }

        DB::table('system_settings')->updateOrInsert(
            ['key' => 'branding.configuration.overrides'],
            [
                'value' => json_encode($overrides, JSON_THROW_ON_ERROR),
                'type' => 'json',
                'description' => 'Institution branding settings managed from the administration portal.',
                'is_active' => true,
                'group' => 'branding',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        Cache::forget(self::CACHE_KEY);
        $this->apply();
    }

    private function overrides(): array
    {
        try {
            if (! Schema::hasTable('system_settings')) {
                return [];
            }

            return Cache::rememberForever(self::CACHE_KEY, function (): array {
                $json = DB::table('system_settings')
                    ->where('key', 'branding.configuration.overrides')
                    ->where('is_active', true)
                    ->value('value');

                $overrides = is_string($json) ? json_decode($json, true) : null;

                return is_array($overrides) ? $overrides : [];
            });
        } catch (Throwable) {
            // Branding must fall back to the deployment configuration while the
            // database is unavailable (for example during initial setup).
            return [];
        }
    }

    private function normaliseValue(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $value = trim($value);

        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            $value = substr($value, 1, -1);
        }

        return match (strtolower($value)) {
            'true' => true,
            'false' => false,
            default => $value,
        };
    }
}
