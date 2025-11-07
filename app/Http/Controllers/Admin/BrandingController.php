<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ThemeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

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
     * Update authentication theme
     */
    public function updateTheme(Request $request)
    {
        try {
            $request->validate([
                'auth_theme' => 'required|string|in:' . implode(',', array_keys($this->themeService->getAvailableThemes())),
            ]);

            $this->updateEnvFile([
                'AUTH_THEME' => $request->auth_theme,
            ]);

            Cache::forget('config');
            Artisan::call('config:clear');

            return redirect()->back()->with('success', 'Authentication theme updated successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Theme update failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update theme. Please try again.');
        }
    }

    /**
     * Update institution information
     */
    public function updateInstitution(Request $request)
    {
        try {
            $request->validate([
                'institution_name' => 'required|string|max:255',
                'institution_short_name' => 'nullable|string|max:50',
                'support_email' => 'required|email',
                'phone' => 'nullable|string|max:50',
                'address' => 'nullable|string|max:500',
            ]);

            $this->updateEnvFile([
                'INSTITUTION_NAME' => '"' . $request->institution_name . '"',
                'INSTITUTION_SHORT_NAME' => $request->institution_short_name ? '"' . $request->institution_short_name . '"' : null,
                'SUPPORT_EMAIL' => $request->support_email,
                'INSTITUTION_PHONE' => $request->phone,
                'INSTITUTION_ADDRESS' => $request->address ? '"' . $request->address . '"' : null,
            ]);

            Cache::forget('config');
            Artisan::call('config:clear');

            return redirect()->back()->with('success', 'Institution information updated successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Institution update failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update institution information. Please try again.');
        }
    }

    /**
     * Update URLs configuration
     */
    public function updateUrls(Request $request)
    {
        try {
            $request->validate([
                'staff_mail_url' => 'required|url',
                'student_portal_url' => 'nullable|url',
                'website_url' => 'nullable|url',
            ]);

            $this->updateEnvFile([
                'STAFF_MAIL_URL' => $request->staff_mail_url,
                'STUDENT_PORTAL_URL' => $request->student_portal_url,
                'INSTITUTION_WEBSITE_URL' => $request->website_url,
            ]);

            Cache::forget('config');
            Artisan::call('config:clear');

            return redirect()->back()->with('success', 'URL configuration updated successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('URLs update failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update URL configuration. Please try again.');
        }
    }

    /**
     * Update color scheme
     */
    public function updateColors(Request $request)
    {
        try {
            $request->validate([
                'primary_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'secondary_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'accent_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'success_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'warning_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'danger_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            ]);

            $this->updateEnvFile([
                'PRIMARY_COLOR' => $request->primary_color,
                'SECONDARY_COLOR' => $request->secondary_color,
                'ACCENT_COLOR' => $request->accent_color,
                'SUCCESS_COLOR' => $request->success_color,
                'WARNING_COLOR' => $request->warning_color,
                'DANGER_COLOR' => $request->danger_color,
            ]);

            Cache::forget('config');
            Artisan::call('config:clear');

            return redirect()->back()->with('success', 'Color scheme updated successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Colors update failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update color scheme. Please try again.');
        }
    }

    /**
     * Update theme settings
     */
    public function updateThemeSettings(Request $request)
    {
        try {
            $request->validate([
                'card_style' => 'required|string|in:elevated,flat,bordered',
                'show_institution_name' => 'boolean',
                'enable_animations' => 'boolean',
            ]);

            $this->updateEnvFile([
                'AUTH_CARD_STYLE' => $request->card_style,
                'SHOW_INSTITUTION_NAME' => $request->show_institution_name ? 'true' : 'false',
                'ENABLE_AUTH_ANIMATIONS' => $request->enable_animations ? 'true' : 'false',
            ]);

            Cache::forget('config');
            Artisan::call('config:clear');

            return redirect()->back()->with('success', 'Theme settings updated successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Theme settings update failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update theme settings. Please try again.');
        }
    }

    /**
     * Update branding configuration (legacy method - kept for compatibility)
     */
    public function update(Request $request)
    {
        try {
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
            
            Artisan::call('config:clear');

            return redirect()->back()->with('success', 'Branding configuration updated successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Branding configuration update failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update branding configuration. Please try again or contact support.');
        }
    }

    /**
     * Upload logo
     */
    public function uploadLogo(Request $request)
    {
        try {
            $request->validate([
                'logo_type' => 'required|string|in:primary,white,favicon,auth,app',
                'logo' => 'required|file|mimes:jpg,jpeg,png,svg,ico|max:2048',
            ]);

            $logoType = $request->logo_type;
            $file = $request->file('logo');
            
            Log::info('Logo upload started', [
                'logo_type' => $logoType,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize()
            ]);
            
            // Create logos directory if it doesn't exist
            $logoDir = public_path('images/logos');
            if (!File::exists($logoDir)) {
                $created = File::makeDirectory($logoDir, 0755, true);
                Log::info('Created logos directory', ['path' => $logoDir, 'success' => $created]);
            }

            // Check if directory is writable
            if (!is_writable($logoDir)) {
                Log::error('Logos directory is not writable', ['path' => $logoDir]);
                throw new \Exception('Upload directory is not writable. Please check permissions.');
            }

            // Generate filename with timestamp to avoid conflicts
            $extension = $file->getClientOriginalExtension();
            $filename = $logoType . '-logo-' . time() . '.' . $extension;
            $path = '/images/logos/' . $filename;
            
            Log::info('Moving file', ['from' => $file->getPathname(), 'to' => $logoDir . '/' . $filename]);
            
            // Move file
            $moved = $file->move($logoDir, $filename);
            
            if (!$moved) {
                throw new \Exception('Failed to move uploaded file to destination.');
            }

            Log::info('File moved successfully', ['path' => $logoDir . '/' . $filename]);

            // Verify file was created
            $fullPath = $logoDir . '/' . $filename;
            if (!File::exists($fullPath)) {
                throw new \Exception('File was not created at the expected location.');
            }

            // Update .env file
            $envKey = 'COLLEGE_LOGO_' . strtoupper($logoType);
            $this->updateEnvFile([$envKey => $path]);

            Log::info('Updated env file', ['key' => $envKey, 'value' => $path]);

            // Test if .env file is still valid after update
            try {
                // Clear configuration cache
                Cache::forget('config');
                Artisan::call('config:clear');
                
                // Verify the new configuration can be loaded
                config('branding.logo.' . $logoType);
            } catch (\Exception $configException) {
                Log::error('Configuration became invalid after env update', ['error' => $configException->getMessage()]);
                throw new \Exception('Configuration update failed. The .env file may be corrupted.');
            }

            return redirect()->back()->with('success', ucfirst($logoType) . ' logo uploaded successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Logo upload validation failed', ['errors' => $e->validator->errors()]);
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Logo upload failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'logo_type' => $request->logo_type ?? 'unknown'
            ]);
            return redirect()->back()->with('error', 'Failed to upload logo: ' . $e->getMessage());
        }
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
                // Properly escape and quote the value
                $escapedValue = $this->formatEnvValue($value);
                
                if (preg_match("/^{$key}=.*$/m", $envContent)) {
                    // Update existing key
                    $envContent = preg_replace("/^{$key}=.*$/m", "{$key}={$escapedValue}", $envContent);
                } else {
                    // Add new key
                    $envContent .= "\n{$key}={$escapedValue}";
                }
            }
        }

        File::put($envFile, $envContent);
    }

    /**
     * Format a value for .env file
     */
    protected function formatEnvValue($value): string
    {
        // If value already has quotes, return as is
        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            return $value;
        }

        // If value contains spaces, quotes, or special characters, wrap in double quotes
        if (preg_match('/[\s"\'#\\\\]/', $value)) {
            return '"' . str_replace('"', '\\"', $value) . '"';
        }

        return $value;
    }
}