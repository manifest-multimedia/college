<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ThemeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class BrandingController extends Controller
{
    protected ThemeService $themeService;

    public function __construct(ThemeService $themeService)
    {
        $this->themeService = $themeService;
    }

    /**
     * Display branding configuration page
     */
    public function index()
    {
        $currentTheme = $this->themeService->getCurrentTheme();
        $availableThemes = $this->themeService->getAvailableThemes();
        $institution = $this->themeService->getInstitution();
        $logos = $this->themeService->getLogos();
        $colors = config('branding.colors', []);
        $themeSettings = $this->themeService->getThemeSettings();

        return view('admin.branding.index', compact(
            'currentTheme',
            'availableThemes',
            'institution',
            'logos',
            'colors',
            'themeSettings'
        ));
    }

    /**
     * Preview a theme
     */
    public function preview(Request $request)
    {
        $theme = $request->get('theme', 'default');
        
        if (!$this->themeService->themeExists($theme)) {
            abort(404, 'Theme not found');
        }

        // Temporarily set theme for preview
        config(['branding.auth_theme' => $theme]);
        
        return view('auth.login');
    }

    /**
     * Update branding configuration
     */
    public function update(Request $request)
    {
        $request->validate([
            'auth_theme' => 'required|string|in:' . implode(',', array_keys($this->themeService->getAvailableThemes())),
            'institution_name' => 'required|string|max:255',
            'institution_short_name' => 'nullable|string|max:50',
            'staff_mail_url' => 'required|url',
            'student_portal_url' => 'nullable|url',
            'website_url' => 'nullable|url',
            'support_email' => 'required|email',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'primary_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'secondary_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'accent_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'success_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'warning_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'danger_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'show_institution_name' => 'boolean',
            'show_background_pattern' => 'boolean',
            'enable_animations' => 'boolean',
            'card_style' => 'required|string|in:elevated,flat,bordered',
        ]);

        // Update .env file
        $this->updateEnvFile([
            'AUTH_THEME' => $request->auth_theme,
            'INSTITUTION_NAME' => '"' . $request->institution_name . '"',
            'INSTITUTION_SHORT_NAME' => $request->institution_short_name ? '"' . $request->institution_short_name . '"' : null,
            'STAFF_MAIL_URL' => $request->staff_mail_url,
            'STUDENT_PORTAL_URL' => $request->student_portal_url,
            'INSTITUTION_WEBSITE_URL' => $request->website_url,
            'SUPPORT_EMAIL' => $request->support_email,
            'INSTITUTION_PHONE' => $request->phone,
            'INSTITUTION_ADDRESS' => $request->address ? '"' . $request->address . '"' : null,
            'PRIMARY_COLOR' => $request->primary_color,
            'SECONDARY_COLOR' => $request->secondary_color,
            'ACCENT_COLOR' => $request->accent_color,
            'SUCCESS_COLOR' => $request->success_color,
            'WARNING_COLOR' => $request->warning_color,
            'DANGER_COLOR' => $request->danger_color,
            'SHOW_INSTITUTION_NAME' => $request->show_institution_name ? 'true' : 'false',
            'SHOW_BACKGROUND_PATTERN' => $request->show_background_pattern ? 'true' : 'false',
            'ENABLE_AUTH_ANIMATIONS' => $request->enable_animations ? 'true' : 'false',
            'AUTH_CARD_STYLE' => $request->card_style,
        ]);

        // Clear configuration cache
        Cache::forget('config');

        return redirect()->back()->with('success', 'Branding configuration updated successfully!');
    }

    /**
     * Upload logo
     */
    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo_type' => 'required|string|in:primary,white,favicon,auth',
            'logo' => 'required|file|mimes:jpg,jpeg,png,svg,ico|max:2048',
        ]);

        $logoType = $request->logo_type;
        $file = $request->file('logo');
        
        // Create logos directory if it doesn't exist
        $logoDir = public_path('images/logos');
        if (!File::exists($logoDir)) {
            File::makeDirectory($logoDir, 0755, true);
        }

        // Generate filename
        $filename = $logoType . '-logo.' . $file->getClientOriginalExtension();
        $path = '/images/logos/' . $filename;
        
        // Move file
        $file->move($logoDir, $filename);

        // Update .env file
        $envKey = 'COLLEGE_LOGO_' . strtoupper($logoType);
        $this->updateEnvFile([$envKey => $path]);

        // Clear configuration cache
        Cache::forget('config');

        return redirect()->back()->with('success', ucfirst($logoType) . ' logo uploaded successfully!');
    }

    /**
     * Update .env file with new values
     */
    protected function updateEnvFile(array $values): void
    {
        $envFile = base_path('.env');
        $envContent = File::get($envFile);

        foreach ($values as $key => $value) {
            if ($value === null) {
                // Remove the line if value is null
                $envContent = preg_replace("/^{$key}=.*$/m", '', $envContent);
            } else {
                if (preg_match("/^{$key}=.*$/m", $envContent)) {
                    // Update existing key
                    $envContent = preg_replace("/^{$key}=.*$/m", "{$key}={$value}", $envContent);
                } else {
                    // Add new key
                    $envContent .= "\n{$key}={$value}";
                }
            }
        }

        File::put($envFile, $envContent);
    }
}