<?php

namespace App\Livewire;

use App\Models\Cohort;
use App\Models\CollegeClass;
use App\Models\AcademicYear;
use App\Imports\StudentImporter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class StudentImport extends Component
{
    use WithFileUploads;

    public $file;
    public $programId;
    public $cohortId;
    public $academicYearId;
    public $syncUsers = true;
    public $columnMapping = [];
    public $importResults = null;
    public $isProcessing = false;

    // Available fields in the student model
    public $availableFields = [
        'student_id' => 'Student ID',
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'other_name' => 'Other Name',
        'gender' => 'Gender',
        'date_of_birth' => 'Date of Birth',
        'nationality' => 'Nationality',
        'country_of_residence' => 'Country of Residence',
        'home_region' => 'Home Region',
        'home_town' => 'Home Town',
        'religion' => 'Religion',
        'mobile_number' => 'Mobile Number',
        'email' => 'Email',
        'gps_address' => 'GPS Address',
        'postal_address' => 'Postal Address',
        'residential_address' => 'Residential Address',
        'marital_status' => 'Marital Status',
        'status' => 'Status',
    ];

    // Default column mapping
    public $defaultColumnMapping = [
        'student_id' => 'student_id',
        'first_name' => 'first_name',
        'last_name' => 'last_name',
        'other_names' => 'other_name',
        'gender' => 'gender',
        'date_of_birth' => 'date_of_birth',
        'nationality' => 'nationality',
        'country' => 'country_of_residence',
        'home_region' => 'home_region',
        'home_town' => 'home_town',
        'region' => null, 
        'mobile_number' => 'mobile_number',
        'email' => 'email',
        'gps_address' => 'gps_address',
        'postal_address' => 'postal_address',
        'residential_address' => 'residential_address',
        'marital_status' => 'marital_status',
    ];

    protected $rules = [
        'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
        'programId' => 'required|exists:college_classes,id',
        'cohortId' => 'required|exists:cohorts,id',
        'academicYearId' => 'nullable|exists:academic_years,id',
        'syncUsers' => 'boolean',
    ];

    protected $messages = [
        'file.required' => 'Please select a file to import',
        'file.file' => 'The uploaded file is invalid',
        'file.mimes' => 'The file must be an Excel or CSV file',
        'file.max' => 'The file size must not exceed 10MB',
        'programId.required' => 'Please select a program',
        'cohortId.required' => 'Please select a cohort',
        'academicYearId.exists' => 'Please select a valid academic year',
    ];

    public function mount()
    {
        $this->columnMapping = $this->defaultColumnMapping;
        
        // Set current academic year as default
        $currentAcademicYear = AcademicYear::where('is_current', true)->first();
        if ($currentAcademicYear) {
            $this->academicYearId = $currentAcademicYear->id;
        }
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function import()
    {
        $this->validate();

        try {
            $this->isProcessing = true;

            // Import students from the Excel file
            $importer = new StudentImporter(
                $this->programId,
                $this->cohortId,
                $this->columnMapping,
                $this->academicYearId
            );

            Excel::import($importer, $this->file);

            // Get import results
            $this->importResults = $importer->getImportStats();
            
            // Sync user accounts if requested
            if ($this->syncUsers && ($this->importResults['created'] > 0 || $this->importResults['updated'] > 0)) {
                Artisan::call('students:sync-user-ids', ['--force' => true]);
                $this->importResults['sync_output'] = Artisan::output();
            }

            $message = 'Students imported successfully: ' . 
                $this->importResults['created'] . ' created, ' . 
                $this->importResults['updated'] . ' updated';
            
            if ($this->importResults['skipped'] > 0) {
                $message .= ', ' . $this->importResults['skipped'] . ' skipped due to validation errors';
            }
            
            if ($this->importResults['failed'] > 0) {
                $message .= ', ' . $this->importResults['failed'] . ' failed';
            }
            
            if (isset($this->importResults['ids_generated']) && $this->importResults['ids_generated'] > 0) {
                $message .= ', ' . $this->importResults['ids_generated'] . ' student IDs generated automatically';
            }
            
            session()->flash('success', $message . '.');
            
            Log::channel('daily')->info('Student import completed', $this->importResults);
            
        } catch (\Exception $e) {
            Log::error('Student import error: ' . $e->getMessage());
            session()->flash('error', 'Error importing students: ' . $e->getMessage());
        } finally {
            $this->isProcessing = false;
        }
    }

    public function resetForm()
    {
        $this->reset(['file', 'importResults', 'academicYearId']);
        $this->columnMapping = $this->defaultColumnMapping;
        
        // Reset to current academic year
        $currentAcademicYear = AcademicYear::where('is_current', true)->first();
        if ($currentAcademicYear) {
            $this->academicYearId = $currentAcademicYear->id;
        }
    }

    public function render()
    {
        return view('livewire.student-import', [
            'programs' => CollegeClass::orderBy('name')->get(),
            'cohorts' => Cohort::orderBy('name')->get(),
            'academicYears' => AcademicYear::orderBy('start_date', 'desc')->get(),
        ]);
    }
}
