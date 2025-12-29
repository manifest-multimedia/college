<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AssessmentSettings extends Component
{
    public $assignment_weight = 20;

    public $mid_semester_weight = 20;

    public $end_semester_weight = 60;

    public function mount()
    {
        $this->loadSettings();
    }

    public function loadSettings()
    {
        // Get settings from database or use defaults
        $settings = DB::table('system_settings')
            ->whereIn('key', ['default_assignment_weight', 'default_mid_semester_weight', 'default_end_semester_weight'])
            ->get()
            ->keyBy('key');

        $this->assignment_weight = $settings->get('default_assignment_weight')->value ?? 20;
        $this->mid_semester_weight = $settings->get('default_mid_semester_weight')->value ?? 20;
        $this->end_semester_weight = $settings->get('default_end_semester_weight')->value ?? 60;
    }

    public function saveSettings()
    {
        // Validate that weights sum to 100
        $total = $this->assignment_weight + $this->mid_semester_weight + $this->end_semester_weight;

        if ($total != 100) {
            session()->flash('error', 'The total weight must equal 100%. Current total: '.$total.'%');

            return;
        }

        // Validate individual weight ranges
        if ($this->assignment_weight < 0 || $this->assignment_weight > 100) {
            session()->flash('error', 'Assignment weight must be between 0 and 100%');

            return;
        }

        if ($this->mid_semester_weight < 0 || $this->mid_semester_weight > 100) {
            session()->flash('error', 'Mid-Semester weight must be between 0 and 100%');

            return;
        }

        if ($this->end_semester_weight < 0 || $this->end_semester_weight > 100) {
            session()->flash('error', 'End-Semester weight must be between 0 and 100%');

            return;
        }

        try {
            DB::beginTransaction();

            // Update or insert settings
            $settings = [
                'default_assignment_weight' => $this->assignment_weight,
                'default_mid_semester_weight' => $this->mid_semester_weight,
                'default_end_semester_weight' => $this->end_semester_weight,
            ];

            foreach ($settings as $key => $value) {
                DB::table('system_settings')->updateOrInsert(
                    ['key' => $key],
                    [
                        'value' => $value,
                        'updated_at' => now(),
                        'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                    ]
                );
            }

            DB::commit();

            session()->flash('success', 'Assessment weight settings saved successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to save settings: '.$e->getMessage());
        }
    }

    public function resetToDefaults()
    {
        $this->assignment_weight = 20;
        $this->mid_semester_weight = 20;
        $this->end_semester_weight = 60;

        session()->flash('info', 'Settings reset to default values. Click "Save Settings" to apply.');
    }

    public function render()
    {
        return view('livewire.settings.assessment-settings');
    }
}
