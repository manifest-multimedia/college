<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

class GeneralSettings extends Component
{
    use WithFileUploads;

    public $settings = [];
    public $schoolName;
    public $schoolEmail;
    public $schoolPhone;
    public $schoolAddress;
    public $schoolLogo;
    public $schoolWebsite;
    public $systemTimeZone;
    public $academicYear;
    public $currentLogo;
    public $isFirstTimeSetup = false;
    
    public function mount()
    {
        $this->loadSettings();
    }
    
    private function loadSettings()
    {
        try {
            // Check if settings table exists
            if (!Schema::hasTable('settings')) {
                $this->isFirstTimeSetup = true;
                $this->initializeDefaultSettings();
                session()->flash('info', 'Welcome to the system settings page. Please configure your school information.');
                return;
            }
            
            // Load settings from database
            $dbSettings = DB::table('settings')
                ->whereIn('key', [
                    'school_name',
                    'school_email',
                    'school_phone',
                    'school_address',
                    'school_logo',
                    'school_website',
                    'system_timezone',
                    'current_academic_year',
                ])
                ->get();
                
            // If no settings exist yet, this is first time setup
            if ($dbSettings->isEmpty()) {
                $this->isFirstTimeSetup = true;
                $this->initializeDefaultSettings();
                session()->flash('info', 'Welcome to the system settings page. Please configure your school information.');
                return;
            }
            
            // Convert collection to key-value array
            $dbSettings = $dbSettings->keyBy('key')
                ->map(function ($item) {
                    return $item->value;
                })
                ->toArray();
            
            // Set component properties
            $this->schoolName = $dbSettings['school_name'] ?? config('app.name');
            $this->schoolEmail = $dbSettings['school_email'] ?? '';
            $this->schoolPhone = $dbSettings['school_phone'] ?? '';
            $this->schoolAddress = $dbSettings['school_address'] ?? '';
            $this->currentLogo = $dbSettings['school_logo'] ?? '';
            $this->schoolWebsite = $dbSettings['school_website'] ?? '';
            $this->systemTimeZone = $dbSettings['system_timezone'] ?? config('app.timezone');
            $this->academicYear = $dbSettings['current_academic_year'] ?? '';
            
        } catch (\Exception $e) {
            Log::error('Error loading general settings', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Initialize default values instead of showing error
            $this->initializeDefaultSettings();
            $this->isFirstTimeSetup = true;
            session()->flash('warning', 'Unable to load existing settings. Starting with default values.');
        }
    }
    
    private function initializeDefaultSettings()
    {
        // Set default values for first-time setup
        $this->schoolName = config('app.name');
        $this->schoolEmail = '';
        $this->schoolPhone = '';
        $this->schoolAddress = '';
        $this->currentLogo = '';
        $this->schoolWebsite = '';
        $this->systemTimeZone = config('app.timezone');
        $this->academicYear = date('Y').'-'.((int)date('Y')+1);
    }
    
    public function save()
    {
        $this->validate([
            'schoolName' => 'required|string|max:100',
            'schoolEmail' => 'required|email',
            'schoolPhone' => 'required|string|max:20',
            'schoolAddress' => 'required|string',
            'schoolWebsite' => 'nullable|url',
            'systemTimeZone' => 'required|string',
            'academicYear' => 'required|string',
            'schoolLogo' => 'nullable|image|max:1024', // 1MB Max
        ]);
        
        try {
            // Ensure settings table exists
            if (!Schema::hasTable('settings')) {
                Schema::create('settings', function ($table) {
                    $table->id();
                    $table->string('key')->unique();
                    $table->text('value')->nullable();
                    $table->timestamps();
                });
                
                Log::info('Settings table created automatically');
            }
            
            DB::beginTransaction();
            
            // Update or create settings
            $this->updateSetting('school_name', $this->schoolName);
            $this->updateSetting('school_email', $this->schoolEmail);
            $this->updateSetting('school_phone', $this->schoolPhone);
            $this->updateSetting('school_address', $this->schoolAddress);
            $this->updateSetting('school_website', $this->schoolWebsite);
            $this->updateSetting('system_timezone', $this->systemTimeZone);
            $this->updateSetting('current_academic_year', $this->academicYear);
            
            // Handle logo upload if provided
            if ($this->schoolLogo) {
                // Delete old logo if exists
                if ($this->currentLogo && Storage::exists(str_replace('storage/', 'public/', $this->currentLogo))) {
                    Storage::delete(str_replace('storage/', 'public/', $this->currentLogo));
                }
                
                $path = $this->schoolLogo->store('public/logos');
                $publicPath = str_replace('public/', 'storage/', $path);
                $this->updateSetting('school_logo', $publicPath);
                $this->currentLogo = $publicPath;
                $this->schoolLogo = null;
            }
            
            DB::commit();
            
            // Apply timezone setting immediately
            config(['app.timezone' => $this->systemTimeZone]);
            
            Log::info('General settings updated successfully', [
                'user_id' => auth()->id(),
                'school_name' => $this->schoolName,
                'timezone' => $this->systemTimeZone
            ]);
            
            $this->isFirstTimeSetup = false;
            session()->flash('success', 'Settings saved successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error saving general settings', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            session()->flash('error', 'Failed to save settings: ' . $e->getMessage());
        }
    }
    
    private function updateSetting($key, $value)
    {
        DB::table('settings')->updateOrInsert(
            ['key' => $key],
            ['value' => $value, 'updated_at' => now()]
        );
    }
    
    public function render()
    {
        $timezones = \DateTimeZone::listIdentifiers();
        
        return view('livewire.settings.general-settings', [
            'timezones' => $timezones,
            'isFirstTimeSetup' => $this->isFirstTimeSetup,
        ])->layout('components.dashboard.default');
    }
}