<?php

namespace App\Livewire;

use App\Models\Cohort;
use App\Models\CollegeClass;
use App\Models\Student;
use App\Models\User;
use App\Services\StudentIdGenerationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;

class StudentCreateForm extends Component
{
    use WithFileUploads;

    // Personal Details
    public $first_name;

    public $last_name;

    public $other_name;

    public $gender;

    public $date_of_birth;

    public $nationality;

    public $religion;

    public $marital_status;

    // Location Details
    public $country_of_residence;

    public $home_region;

    public $home_town;

    // Contact Information
    public $email;

    public $mobile_number;

    public $gps_address;

    public $postal_address;

    public $residential_address;

    // Academic Information
    public $student_id_number;

    public $college_class_id;

    public $cohort_id;

    public $academic_year_id;

    public $status = 'Active';

    // Photo
    public $photo;

    public $classes = [];

    public $cohorts = [];

    public $academicYears = [];

    public $statuses = ['Active', 'Inactive', 'Pending', 'Graduated'];

    public $genders = ['Male', 'Female', 'Other'];

    public $religions = ['Christianity', 'Islam', 'Traditional', 'Other', 'None'];

    public $maritalStatuses = ['Single', 'Married', 'Divorced', 'Widowed'];

    protected $rules = [
        'first_name' => 'required|string|max:100',
        'last_name' => 'required|string|max:100',
        'other_name' => 'nullable|string|max:100',
        'gender' => 'nullable|string|in:Male,Female,Other',
        'date_of_birth' => 'nullable|date',
        'nationality' => 'nullable|string|max:100',
        'religion' => 'nullable|string|max:50',
        'marital_status' => 'nullable|string|in:Single,Married,Divorced,Widowed',
        'country_of_residence' => 'nullable|string|max:100',
        'home_region' => 'nullable|string|max:100',
        'home_town' => 'nullable|string|max:100',
        'student_id_number' => 'nullable|string|max:50|unique:students,student_id',
        'email' => 'required|email|max:100|unique:students,email',
        'mobile_number' => 'nullable|string|max:20',
        'gps_address' => 'nullable|string|max:255',
        'postal_address' => 'nullable|string|max:255',
        'residential_address' => 'nullable|string|max:255',
        'college_class_id' => 'required|integer|exists:college_classes,id',
        'cohort_id' => 'required|integer|exists:cohorts,id',
        'academic_year_id' => 'required|integer|exists:academic_years,id',
        'status' => 'required|string|in:Active,Inactive,Pending,Graduated',
        'photo' => 'nullable|image|max:2048',
    ];

    public function mount()
    {
        $this->loadDropdowns();
    }

    public function loadDropdowns()
    {
        try {
            $this->classes = CollegeClass::orderBy('name')->get();
            $this->cohorts = Cohort::orderBy('name')->get();
            $this->academicYears = \App\Models\AcademicYear::orderBy('name')->get();

            // Set current academic year as default if not set
            if (empty($this->academic_year_id)) {
                $currentAcademicYear = \App\Models\AcademicYear::where('is_current', true)->first();
                if ($currentAcademicYear) {
                    $this->academic_year_id = $currentAcademicYear->id;
                }
            }
        } catch (\Exception $e) {
            Log::error('Error loading dropdowns: '.$e->getMessage());
        }
    }

    public function createStudent()
    {
        $this->validate();

        try {
            // Auto-generate student ID if not provided
            if (empty($this->student_id_number)) {
                $studentIdService = new StudentIdGenerationService;
                $this->student_id_number = $studentIdService->generateStudentId(
                    $this->first_name,
                    $this->last_name,
                    $this->college_class_id,
                    $this->academic_year_id
                );
            }

            // Handle photo upload
            $photoPath = null;
            if ($this->photo) {
                $photoPath = $this->photo->store('student-photos', 'public');
            }

            // Create the student
            $student = new Student;
            $student->first_name = $this->first_name;
            $student->last_name = $this->last_name;
            $student->other_name = $this->other_name;
            $student->student_id = $this->student_id_number;
            $student->gender = $this->gender;
            $student->date_of_birth = $this->date_of_birth;
            $student->nationality = $this->nationality;
            $student->religion = $this->religion;
            $student->marital_status = $this->marital_status;
            $student->country_of_residence = $this->country_of_residence;
            $student->home_region = $this->home_region;
            $student->home_town = $this->home_town;
            $student->email = $this->email;
            $student->mobile_number = $this->mobile_number;
            $student->gps_address = $this->gps_address;
            $student->postal_address = $this->postal_address;
            $student->residential_address = $this->residential_address;
            $student->college_class_id = $this->college_class_id;
            $student->cohort_id = $this->cohort_id;
            $student->academic_year_id = $this->academic_year_id;
            $student->status = $this->status;

            if ($photoPath) {
                $student->profile_photo_path = $photoPath;
            }

            $student->save();

            // Create a user account for the student
            try {
                $user = User::create([
                    'name' => $this->first_name.' '.$this->last_name,
                    'email' => $this->email,
                    'password' => Hash::make('password'), // Default password
                ]);

                // Assign student role if it exists
                if ($user && class_exists('\Spatie\Permission\Models\Role')) {
                    $studentRole = \Spatie\Permission\Models\Role::where('name', 'Student')->first();
                    if ($studentRole) {
                        $user->assignRole($studentRole);
                    }
                }

                // Link user to student
                $student->user_id = $user->id;
                $student->save();
            } catch (\Exception $e) {
                Log::warning('Could not create user account for student: '.$e->getMessage());
            }

            session()->flash('success', 'Student created successfully.');

            return redirect()->route('students.show', $student->id);
        } catch (\Exception $e) {
            Log::error('Error creating student: '.$e->getMessage());
            session()->flash('error', 'Failed to create student: '.$e->getMessage());
        }
    }

    /**
     * Generate a unique student ID
     */
    private function generateStudentId()
    {
        $year = now()->year;

        // Get prefix from settings, default to 'STU'
        $prefix = DB::table('settings')
            ->where('key', 'school_name_prefix')
            ->value('value') ?? 'STU';

        // Get the last student ID for this year
        $lastStudent = Student::where('student_id', 'like', $prefix.$year.'%')
            ->orderBy('student_id', 'desc')
            ->first();

        if ($lastStudent) {
            // Extract the number part and increment
            $lastNumber = (int) substr($lastStudent->student_id, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix.$year.str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public function render()
    {
        return view('livewire.student-create-form');
    }
}
