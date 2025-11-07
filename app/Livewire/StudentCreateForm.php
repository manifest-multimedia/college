<?php

namespace App\Livewire;

use App\Models\Cohort;
use App\Models\Student;
use App\Models\User;
use Livewire\Component;
use App\Models\CollegeClass;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentCreateForm extends Component
{
    public $first_name;
    public $last_name;
    public $other_name;
    public $student_id_number;
    public $email;
    public $mobile_number;
    public $college_class_id;
    public $cohort_id;
    public $status = 'Active';
    
    public $classes = [];
    public $cohorts = [];
    public $statuses = ['Active', 'Inactive', 'Pending', 'Graduated'];
    
    protected $rules = [
        'first_name' => 'required|string|max:100',
        'last_name' => 'required|string|max:100',
        'other_name' => 'nullable|string|max:100',
        'student_id_number' => 'required|string|max:50|unique:students,student_id',
        'email' => 'required|email|max:100|unique:students,email',
        'mobile_number' => 'nullable|string|max:20',
        'college_class_id' => 'required|integer|exists:college_classes,id',
        'cohort_id' => 'required|integer|exists:cohorts,id',
        'status' => 'required|string|in:Active,Inactive,Pending,Graduated',
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
        } catch (\Exception $e) {
            Log::error('Error loading dropdowns: ' . $e->getMessage());
        }
    }
    
    public function createStudent()
    {
        $this->validate();
        
        try {
            // Create the student
            $student = new Student();
            $student->first_name = $this->first_name;
            $student->last_name = $this->last_name;
            $student->other_name = $this->other_name;
            $student->student_id = $this->student_id_number;
            $student->email = $this->email;
            $student->mobile_number = $this->mobile_number;
            $student->college_class_id = $this->college_class_id;
            $student->cohort_id = $this->cohort_id;
            $student->status = $this->status;
            
            $student->save();
            
            // Create a user account for the student (optional)
            try {
                $user = User::create([
                    'name' => $this->first_name . ' ' . $this->last_name,
                    'email' => $this->email,
                    'password' => Hash::make(Str::random(12)), // Generate random password
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
                Log::warning('Could not create user account for student: ' . $e->getMessage());
            }
            
            session()->flash('success', 'Student created successfully.');
            
            return redirect()->route('students.show', $student->id);
        } catch (\Exception $e) {
            Log::error('Error creating student: ' . $e->getMessage());
            session()->flash('error', 'Failed to create student: ' . $e->getMessage());
        }
    }
    
    public function render()
    {
        return view('livewire.student-create-form');
    }
}
