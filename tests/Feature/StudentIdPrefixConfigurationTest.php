<?php

namespace Tests\Feature;

use App\Livewire\Settings\GeneralSettings;
use App\Services\BrandingSettingsService;
use App\Services\StudentIdGenerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StudentIdPrefixConfigurationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['branding.student_id.institution_prefix' => 'COLLEGE/DEPT']);
        DB::table('settings')->where('key', 'school_name_prefix')->delete();
        Cache::forget(BrandingSettingsService::CACHE_KEY);
    }

    public function test_administration_prefix_is_persisted_and_reapplied_without_environment_configuration(): void
    {
        $environmentBefore = file_get_contents(base_path('.env'));
        app(BrandingSettingsService::class)->update(['STUDENT_ID_INSTITUTION_PREFIX' => 'PNMTC/DA']);

        config(['branding.student_id.institution_prefix' => 'COLLEGE/DEPT']);
        Cache::forget(BrandingSettingsService::CACHE_KEY);
        app(BrandingSettingsService::class)->apply();

        $this->assertSame('PNMTC/DA', app(StudentIdGenerationService::class)->configuredInstitutionPrefix());
        $this->assertSame('PNMTC/DA', app(StudentIdGenerationService::class)->getInstitutionPrefix());
        $this->assertSame($environmentBefore, file_get_contents(base_path('.env')));
    }

    public function test_legacy_school_setting_remains_a_supported_fallback(): void
    {
        DB::table('settings')->insert(['key' => 'school_name_prefix', 'value' => 'PNMTC/DA']);
        $this->assertSame('PNMTC/DA', app(StudentIdGenerationService::class)->configuredInstitutionPrefix());
        $this->assertSame('PNMTC/DA', app(StudentIdGenerationService::class)->getInstitutionPrefix());
    }

    public function test_student_id_setting_takes_precedence_over_legacy_general_setting(): void
    {
        DB::table('settings')->insert(['key' => 'school_name_prefix', 'value' => 'OTHER/DA']);
        app(BrandingSettingsService::class)->update(['STUDENT_ID_INSTITUTION_PREFIX' => 'PNMTC/DA']);
        $this->assertSame('PNMTC/DA', app(StudentIdGenerationService::class)->configuredInstitutionPrefix());
    }

    public function test_unconfigured_prefix_is_not_inferred_from_the_institution_name(): void
    {
        config(['branding.institution.short_name' => 'PNMTC']);
        $this->assertNull(app(StudentIdGenerationService::class)->configuredInstitutionPrefix());
        $this->assertSame('PNMTC/DEPT', app(StudentIdGenerationService::class)->getInstitutionPrefix());
    }

    public function test_configuration_file_does_not_load_the_deprecated_prefix_environment_variable(): void
    {
        $branding = require config_path('branding.php');
        $this->assertSame('COLLEGE/DEPT', $branding['student_id']['institution_prefix']);
    }

    public function test_general_settings_displays_the_prefix_saved_in_student_id_settings(): void
    {
        app(BrandingSettingsService::class)->update(['STUDENT_ID_INSTITUTION_PREFIX' => 'PNMTC/DA']);
        $settings = app(GeneralSettings::class);
        $settings->mount();
        $this->assertSame('PNMTC/DA', $settings->schoolNamePrefix);
    }

    public function test_general_settings_save_updates_the_shared_prefix_without_editing_env(): void
    {
        $environmentBefore = file_get_contents(base_path('.env'));
        app(BrandingSettingsService::class)->update(['STUDENT_ID_INSTITUTION_PREFIX' => 'OLD/DA']);
        $settings = app(GeneralSettings::class);
        $settings->mount();
        $settings->schoolName = 'Example College';
        $settings->schoolNamePrefix = 'pnmtc/da';
        $settings->schoolEmail = 'admin@example.test';
        $settings->schoolPhone = '0000000000';
        $settings->schoolAddress = 'Example address';
        $settings->save();

        $this->assertSame('PNMTC/DA', app(StudentIdGenerationService::class)->configuredInstitutionPrefix());
        $this->assertDatabaseHas('settings', ['key' => 'school_name_prefix', 'value' => 'PNMTC/DA']);
        $this->assertSame($environmentBefore, file_get_contents(base_path('.env')));
    }

    public function test_legacy_prefix_helper_uses_the_same_effective_prefix(): void
    {
        app(BrandingSettingsService::class)->update(['STUDENT_ID_INSTITUTION_PREFIX' => 'PNMTC/DA']);
        $this->assertSame('PNMTC/DA/RGN/26/27/', generateStudentIdPrefix('RGN', '26/27'));
    }
}
