<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\WithFileUploads;

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
    
    public function mount()
    {
        try {
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
                ->get()
                ->keyBy('key')
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
            Log::error('Error loading general settings: ' . $e->getMessage());
            session()->flash('error', 'Failed to load settings. Please try again later.');
        }
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
                $path = $this->schoolLogo->store('public/logos');
                $publicPath = str_replace('public/', 'storage/', $path);
                $this->updateSetting('school_logo', $publicPath);
                $this->currentLogo = $publicPath;
                $this->schoolLogo = null;
            }
            
            DB::commit();
            session()->flash('success', 'Settings saved successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error saving general settings: ' . $e->getMessage());
            session()->flash('error', 'Failed to save settings. Please try again later.');
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
        ]);
    }
}