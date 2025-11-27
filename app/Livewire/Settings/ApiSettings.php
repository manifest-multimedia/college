<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Component;

class ApiSettings extends Component
{
    // AuthCentral Configuration
    public $authcentralLoginUrl;

    public $authcentralApiUrl;

    public $authcentralSignupUrl;

    public $authcentralStudentRegistrationUrl;

    // Password Sync Settings
    public $passwordSyncEnabled;

    public $passwordSyncApiKey;

    public $showApiKey = false;

    // CIS Webhook URL (read-only)
    public $cisWebhookUrl;

    // Connection Test Results
    public $testConnectionResult = null;

    public $testConnectionStatus = null;

    // API Key Management
    public $newlyGeneratedKey = null;

    public $showGeneratedKey = false;

    public $keyGeneratedAt = null;

    public $keyLastUsedAt = null;

    public function mount()
    {
        $this->loadSettings();
    }

    private function loadSettings()
    {
        // Load AuthCentral URLs from config/database
        $this->authcentralLoginUrl = $this->getSetting('authcentral_login_url')
            ?? config('authentication.authcentral.login_url');
        $this->authcentralApiUrl = $this->getSetting('authcentral_api_url')
            ?? config('authentication.authcentral.api_url');
        $this->authcentralSignupUrl = $this->getSetting('authcentral_signup_url')
            ?? config('authentication.authcentral.signup_url');
        $this->authcentralStudentRegistrationUrl = $this->getSetting('authcentral_student_registration_url')
            ?? config('authentication.authcentral.student_registration_url');

        // Load Password Sync Settings
        $this->passwordSyncEnabled = $this->getSetting('password_sync_enabled', true);
        $this->passwordSyncApiKey = $this->getSetting('password_sync_api_key')
            ?? config('authentication.password_sync.api_key');

        // Set CIS Webhook URL (read-only)
        $this->cisWebhookUrl = url('/api/auth-sync/password');

        // Load key metadata
        $this->keyGeneratedAt = $this->getSetting('password_sync_api_key_generated_at');
        $this->keyLastUsedAt = $this->getSetting('password_sync_api_key_last_used_at');
    }

    private function getSetting($key, $default = null)
    {
        $setting = DB::table('settings')->where('key', $key)->first();

        if ($setting) {
            // Handle boolean values
            if ($setting->value === 'true') {
                return true;
            }
            if ($setting->value === 'false') {
                return false;
            }

            return $setting->value;
        }

        return $default;
    }

    private function setSetting($key, $value, $description = null)
    {
        DB::table('settings')->updateOrInsert(
            ['key' => $key],
            [
                'value' => is_bool($value) ? ($value ? 'true' : 'false') : $value,
                'description' => $description,
                'updated_at' => now(),
            ]
        );
    }

    public function saveAuthCentralSettings()
    {
        $this->validate([
            'authcentralLoginUrl' => 'required|url',
            'authcentralApiUrl' => 'required|url',
            'authcentralSignupUrl' => 'nullable|url',
            'authcentralStudentRegistrationUrl' => 'nullable|url',
        ]);

        try {
            DB::beginTransaction();

            $this->setSetting('authcentral_login_url', $this->authcentralLoginUrl, 'AuthCentral Login URL');
            $this->setSetting('authcentral_api_url', $this->authcentralApiUrl, 'AuthCentral API URL');
            $this->setSetting('authcentral_signup_url', $this->authcentralSignupUrl, 'AuthCentral Signup URL');
            $this->setSetting('authcentral_student_registration_url', $this->authcentralStudentRegistrationUrl, 'AuthCentral Student Registration URL');

            DB::commit();

            // Clear config cache to ensure new values are used
            Cache::forget('config.authentication');

            Log::info('AuthCentral settings updated', [
                'user_id' => auth()->id(),
                'login_url' => $this->authcentralLoginUrl,
            ]);

            session()->flash('success', 'AuthCentral settings saved successfully.');
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Failed to save AuthCentral settings', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            session()->flash('error', 'Failed to save settings: '.$e->getMessage());
        }
    }

    public function savePasswordSyncSettings()
    {
        try {
            DB::beginTransaction();

            $this->setSetting('password_sync_enabled', $this->passwordSyncEnabled, 'Enable Password Synchronization');

            DB::commit();

            Log::info('Password sync settings updated', [
                'user_id' => auth()->id(),
                'enabled' => $this->passwordSyncEnabled,
            ]);

            session()->flash('success', 'Password sync settings saved successfully.');
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Failed to save password sync settings', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            session()->flash('error', 'Failed to save settings: '.$e->getMessage());
        }
    }

    public function generateApiKey()
    {
        try {
            // Check authorization
            if (! auth()->user()->hasAnyRole(['System', 'Super Admin'])) {
                session()->flash('error', 'Unauthorized action.');

                return;
            }

            DB::beginTransaction();

            // Generate a secure 64-character random API key
            $apiKey = Str::random(64);

            // Store the plain API key in settings
            $this->setSetting('password_sync_api_key', $apiKey, 'Password Sync API Key');
            $this->setSetting('password_sync_api_key_generated_at', now()->toDateTimeString(), 'API Key Generation Timestamp');

            DB::commit();

            // Set the newly generated key for display (one-time only)
            $this->newlyGeneratedKey = $apiKey;
            $this->showGeneratedKey = true;
            $this->passwordSyncApiKey = $apiKey;
            $this->keyGeneratedAt = now()->toDateTimeString();

            Log::info('New API key generated', [
                'user_id' => auth()->id(),
                'generated_at' => now(),
            ]);

            session()->flash('warning', 'New API key generated! Copy it now - you won\'t be able to see it again.');
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Failed to generate API key', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            session()->flash('error', 'Failed to generate API key: '.$e->getMessage());
        }
    }

    public function toggleShowApiKey()
    {
        $this->showApiKey = ! $this->showApiKey;
    }

    public function closeGeneratedKeyModal()
    {
        $this->showGeneratedKey = false;
        $this->newlyGeneratedKey = null;
    }

    public function testWebhookConnection()
    {
        $this->testConnectionResult = null;
        $this->testConnectionStatus = 'testing';

        try {
            // Test the health check endpoint
            $response = Http::timeout(10)->get($this->cisWebhookUrl.'-health');

            if ($response->successful()) {
                $this->testConnectionStatus = 'success';
                $this->testConnectionResult = 'Webhook endpoint is reachable and healthy.';

                Log::info('Webhook connection test successful', [
                    'user_id' => auth()->id(),
                    'endpoint' => $this->cisWebhookUrl,
                ]);
            } else {
                $this->testConnectionStatus = 'error';
                $this->testConnectionResult = 'Webhook endpoint returned error: '.$response->status();

                Log::warning('Webhook connection test failed', [
                    'user_id' => auth()->id(),
                    'status' => $response->status(),
                ]);
            }
        } catch (\Exception $e) {
            $this->testConnectionStatus = 'error';
            $this->testConnectionResult = 'Connection failed: '.$e->getMessage();

            Log::error('Webhook connection test exception', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
        }
    }

    public function testAuthCentralConnection()
    {
        $this->testConnectionResult = null;
        $this->testConnectionStatus = 'testing';

        try {
            // Test connection to AuthCentral login URL
            $response = Http::timeout(10)->get($this->authcentralLoginUrl);

            if ($response->successful()) {
                $this->testConnectionStatus = 'success';
                $this->testConnectionResult = 'Successfully connected to AuthCentral ('.$this->authcentralLoginUrl.')';

                Log::info('AuthCentral connection test successful', [
                    'user_id' => auth()->id(),
                    'url' => $this->authcentralLoginUrl,
                ]);
            } else {
                $this->testConnectionStatus = 'warning';
                $this->testConnectionResult = 'AuthCentral URL responded with status: '.$response->status();

                Log::warning('AuthCentral connection test warning', [
                    'user_id' => auth()->id(),
                    'status' => $response->status(),
                ]);
            }
        } catch (\Exception $e) {
            $this->testConnectionStatus = 'error';
            $this->testConnectionResult = 'Connection failed: '.$e->getMessage();

            Log::error('AuthCentral connection test exception', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
        }
    }

    public function getMaskedApiKey()
    {
        if (empty($this->passwordSyncApiKey)) {
            return 'Not configured';
        }

        if ($this->showApiKey) {
            return $this->passwordSyncApiKey;
        }

        // Show first 8 and last 4 characters
        $keyLength = strlen($this->passwordSyncApiKey);
        if ($keyLength < 12) {
            return str_repeat('•', $keyLength);
        }

        return substr($this->passwordSyncApiKey, 0, 8).str_repeat('•', $keyLength - 12).substr($this->passwordSyncApiKey, -4);
    }

    public function render()
    {
        return view('livewire.settings.api-settings')->with([
            'maskedApiKey' => $this->getMaskedApiKey(),
        ]);
    }
}
