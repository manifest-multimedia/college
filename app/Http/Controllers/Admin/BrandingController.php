<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ThemeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

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

        if (! $this->themeService->themeExists($theme)) {
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
                'auth_theme' => 'required|string|in:'.implode(',', array_keys($this->themeService->getAvailableThemes())),
            ]);

            $this->updateEnvFile([
                'AUTH_THEME' => $request->auth_theme,
            ]);

            $this->refreshConfiguration();

            return redirect()->back()->with('success', 'Authentication theme updated successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Theme update failed: '.$e->getMessage());

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
                'INSTITUTION_NAME' => '"'.$request->institution_name.'"',
                'INSTITUTION_SHORT_NAME' => $request->institution_short_name ? '"'.$request->institution_short_name.'"' : null,
                'SUPPORT_EMAIL' => $request->support_email,
                'INSTITUTION_PHONE' => $request->phone,
                'INSTITUTION_ADDRESS' => $request->address ? '"'.$request->address.'"' : null,
            ]);

            $this->refreshConfiguration();

            return redirect()->back()->with('success', 'Institution information updated successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Institution update failed: '.$e->getMessage());

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

            $this->refreshConfiguration();

            return redirect()->back()->with('success', 'URL configuration updated successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('URLs update failed: '.$e->getMessage());

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

            $this->refreshConfiguration();

            return redirect()->back()->with('success', 'Color scheme updated successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Colors update failed: '.$e->getMessage());

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
                'show_institution_name' => 'nullable|in:0,1',
                'enable_animations' => 'nullable|in:0,1',
                'show_auth_central_button' => 'nullable|in:0,1',
                'show_regular_login' => 'nullable|in:0,1',
                'lecturer_access_mode' => 'nullable|string|in:exam_creator,course_assignment',
            ]);

            // Convert string values to proper boolean strings for environment
            $showInstitutionName = $request->show_institution_name == '1' ? 'true' : 'false';
            $enableAnimations = $request->enable_animations == '1' ? 'true' : 'false';
            $showAuthCentralButton = $request->show_auth_central_button == '1' ? 'true' : 'false';
            $showRegularLogin = $request->show_regular_login == '1' ? 'true' : 'false';

            $envUpdates = [
                'AUTH_CARD_STYLE' => $request->card_style,
                'SHOW_INSTITUTION_NAME' => $showInstitutionName,
                'ENABLE_AUTH_ANIMATIONS' => $enableAnimations,
                'SHOW_AUTH_CENTRAL_BUTTON' => $showAuthCentralButton,
                'SHOW_REGULAR_LOGIN' => $showRegularLogin,
            ];

            // Only update lecturer access mode if provided
            if ($request->has('lecturer_access_mode')) {
                $envUpdates['LECTURER_ACCESS_MODE'] = $request->lecturer_access_mode;
            }

            $this->updateEnvFile($envUpdates);

            $this->refreshConfiguration();

            return redirect()->back()->with('success', 'Theme settings updated successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Theme settings update failed: '.$e->getMessage());

            return redirect()->back()->with('error', 'Failed to update theme settings. Please try again.');
        }
    }

    /**
     * Update student ID generation settings
     */
    public function updateStudentIdSettings(Request $request)
    {
        try {
            $request->validate([
                'student_id_format' => 'required|string|in:structured,simple,custom',
                'student_id_custom_pattern' => 'nullable|string|max:255',
                'institution_prefix' => 'required|string|max:50',
                'institution_simple' => 'required|string|max:20',
                'enable_alphabetical_ordering' => 'nullable|in:0,1',
                'sequence_reset_yearly' => 'nullable|in:0,1',
                'sequence_start' => 'required|integer|min:1|max:999',
            ]);

            // Convert string values to proper boolean strings for environment
            $enableAlphabeticalOrdering = $request->enable_alphabetical_ordering == '1' ? 'true' : 'false';
            $sequenceResetYearly = $request->sequence_reset_yearly == '1' ? 'true' : 'false';

            $envUpdates = [
                'STUDENT_ID_FORMAT' => $request->student_id_format,
                'STUDENT_ID_ALPHABETICAL_ORDERING' => $enableAlphabeticalOrdering,
                'STUDENT_ID_INSTITUTION_PREFIX' => '"'.$request->institution_prefix.'"',
                'STUDENT_ID_INSTITUTION_SIMPLE' => '"'.$request->institution_simple.'"',
                'STUDENT_ID_SEQUENCE_RESET_YEARLY' => $sequenceResetYearly,
                'STUDENT_ID_SEQUENCE_START' => $request->sequence_start,
            ];

            // Only set custom pattern if format is custom
            if ($request->student_id_format === 'custom') {
                if (empty($request->student_id_custom_pattern)) {
                    return redirect()->back()->withErrors(['student_id_custom_pattern' => 'Custom pattern is required when using custom format.'])->withInput();
                }
                $envUpdates['STUDENT_ID_CUSTOM_PATTERN'] = '"'.$request->student_id_custom_pattern.'"';
            } else {
                // Clear custom pattern if not using custom format
                $envUpdates['STUDENT_ID_CUSTOM_PATTERN'] = 'null';
            }

            $this->updateEnvFile($envUpdates);
            $this->refreshConfiguration();

            return redirect()->back()->with('success', 'Student ID generation settings updated successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Student ID settings update failed: '.$e->getMessage());

            return redirect()->back()->with('error', 'Failed to update student ID settings. Please try again.');
        }
    }

    /**
     * Update branding configuration (legacy method - kept for compatibility)
     */
    public function update(Request $request)
    {
        try {
            $request->validate([
                'auth_theme' => 'required|string|in:'.implode(',', array_keys($this->themeService->getAvailableThemes())),
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
                'show_institution_name' => 'nullable|in:0,1',
                'show_background_pattern' => 'nullable|in:0,1',
                'enable_animations' => 'nullable|in:0,1',
                'card_style' => 'required|string|in:elevated,flat,bordered',
            ]);

            // Convert checkbox values to proper boolean strings
            $showInstitutionName = $request->show_institution_name == '1' ? 'true' : 'false';
            $showBackgroundPattern = $request->show_background_pattern == '1' ? 'true' : 'false';
            $enableAnimations = $request->enable_animations == '1' ? 'true' : 'false';

            // Update .env file
            $this->updateEnvFile([
                'AUTH_THEME' => $request->auth_theme,
                'INSTITUTION_NAME' => '"'.$request->institution_name.'"',
                'INSTITUTION_SHORT_NAME' => $request->institution_short_name ? '"'.$request->institution_short_name.'"' : null,
                'STAFF_MAIL_URL' => $request->staff_mail_url,
                'STUDENT_PORTAL_URL' => $request->student_portal_url,
                'INSTITUTION_WEBSITE_URL' => $request->website_url,
                'SUPPORT_EMAIL' => $request->support_email,
                'INSTITUTION_PHONE' => $request->phone,
                'INSTITUTION_ADDRESS' => $request->address ? '"'.$request->address.'"' : null,
                'PRIMARY_COLOR' => $request->primary_color,
                'SECONDARY_COLOR' => $request->secondary_color,
                'ACCENT_COLOR' => $request->accent_color,
                'SUCCESS_COLOR' => $request->success_color,
                'WARNING_COLOR' => $request->warning_color,
                'DANGER_COLOR' => $request->danger_color,
                'SHOW_INSTITUTION_NAME' => $showInstitutionName,
                'SHOW_BACKGROUND_PATTERN' => $showBackgroundPattern,
                'ENABLE_AUTH_ANIMATIONS' => $enableAnimations,
                'AUTH_CARD_STYLE' => $request->card_style,
            ]);

            // Clear configuration cache
            $this->refreshConfiguration();

            return redirect()->back()->with('success', 'Branding configuration updated successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Branding configuration update failed: '.$e->getMessage());

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
                'size' => $file->getSize(),
            ]);

            // Determine the correct upload path based on environment
            $logoDir = $this->getLogoUploadPath();

            Log::info('Using upload path', ['path' => $logoDir]);

            // Create the directory if it doesn't exist
            if (! file_exists($logoDir)) {
                if (! mkdir($logoDir, 0775, true)) {
                    Log::error('Failed to create upload directory', ['path' => $logoDir]);

                    return redirect()->back()->with('error', 'Failed to create upload directory.');
                }
                // Try to set permissions, but don't fail if it doesn't work
                @chmod($logoDir, 0775);
                @chgrp($logoDir, 'www-data');
            }

            // Ensure directory is writable
            if (! is_writable($logoDir)) {
                Log::error('Upload directory is not writable', [
                    'path' => $logoDir,
                    'permissions' => substr(sprintf('%o', fileperms($logoDir)), -4),
                    'owner' => function_exists('posix_getpwuid') ? posix_getpwuid(fileowner($logoDir))['name'] ?? 'unknown' : 'unknown',
                    'group' => function_exists('posix_getgrgid') ? posix_getgrgid(filegroup($logoDir))['name'] ?? 'unknown' : 'unknown',
                ]);

                return redirect()->back()->with('error', 'Upload directory is not writable. Please check permissions.');
            }            // Check if directory is writable
            if (! is_writable($logoDir)) {
                Log::error('Logos directory is not writable', [
                    'path' => $logoDir,
                    'permissions' => substr(sprintf('%o', fileperms($logoDir)), -4),
                    'owner' => function_exists('posix_getpwuid') ? posix_getpwuid(fileowner($logoDir))['name'] ?? 'unknown' : 'unknown',
                    'group' => function_exists('posix_getgrgid') ? posix_getgrgid(filegroup($logoDir))['name'] ?? 'unknown' : 'unknown',
                ]);
                throw new \Exception('Upload directory is not writable. Please check permissions.');
            }

            // Generate filename with timestamp to avoid conflicts
            $extension = $file->getClientOriginalExtension();
            $filename = $logoType.'-logo-'.time().'.'.$extension;
            $webPath = '/images/logos/'.$filename;

            Log::info('Moving file', ['from' => $file->getPathname(), 'to' => $logoDir.'/'.$filename]);

            // Move file
            $moved = $file->move($logoDir, $filename);

            if (! $moved) {
                throw new \Exception('Failed to move uploaded file to destination.');
            }

            // Set proper file permissions and ownership (use @ to suppress errors in production)
            $fullPath = $logoDir.'/'.$filename;
            @chmod($fullPath, 0664);
            @chgrp($fullPath, 'www-data');

            Log::info('File moved successfully', ['path' => $fullPath]);

            // Verify file was created
            if (! File::exists($fullPath)) {
                throw new \Exception('File was not created at the expected location.');
            }

            // Update .env file
            $envKey = 'COLLEGE_LOGO_'.strtoupper($logoType);
            $this->updateEnvFile([$envKey => $webPath]);

            Log::info('Updated env file', ['key' => $envKey, 'value' => $webPath]);

            // Test if .env file is still valid after update
            try {
                // Clear and regenerate configuration cache
                $this->refreshConfiguration();

                // Verify the new configuration can be loaded
                config('branding.logo.'.$logoType);
            } catch (\Exception $configException) {
                Log::error('Configuration became invalid after env update', ['error' => $configException->getMessage()]);
                throw new \Exception('Configuration update failed. The .env file may be corrupted.');
            }

            return redirect()->back()->with('success', ucfirst($logoType).' logo uploaded successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Logo upload validation failed', ['errors' => $e->validator->errors()]);

            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Logo upload failed: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'logo_type' => $request->logo_type ?? 'unknown',
            ]);

            return redirect()->back()->with('error', 'Failed to upload logo: '.$e->getMessage());
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
            return '"'.str_replace('"', '\\"', $value).'"';
        }

        return $value;
    }

    /**
     * Get the correct logo upload path based on environment and deployment structure
     */
    protected function getLogoUploadPath(): string
    {
        $publicImagesPath = public_path('images');
        $logoPath = public_path('images/logos');

        // Check if we're in a deployment structure with shared directories
        if (is_link($publicImagesPath)) {
            // Follow the symlink to get the real shared directory
            $realPath = readlink($publicImagesPath);
            $logoPath = $realPath.'/logos';
            Log::info('Detected symlinked images directory', [
                'symlink' => $publicImagesPath,
                'real_path' => $realPath,
                'logo_path' => $logoPath,
            ]);
        } elseif (app()->environment('production')) {
            // In production, try to use shared directory if it exists
            $sharedPath = base_path('../shared/public/images/logos');
            if (file_exists(dirname($sharedPath))) {
                $logoPath = $sharedPath;
                Log::info('Using production shared directory', ['path' => $logoPath]);
            }
        }

        return $logoPath;
    }

    /**
     * Refresh configuration cache for both development and production environments
     */
    protected function refreshConfiguration(): void
    {
        Cache::forget('config');
        Artisan::call('config:clear');

        // In production environments, we need to regenerate the config cache
        // since Laravel caches configuration for performance
        if (app()->environment('production')) {
            Artisan::call('config:cache');
            Log::info('Configuration cache regenerated for production environment');
        }
    }
}
