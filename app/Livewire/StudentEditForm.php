<?php

namespace App\Livewire;

use App\Models\Cohort;
use App\Models\Student;
use Livewire\Component;
use App\Models\CollegeClass;
use Illuminate\Support\Facades\Log;

class StudentEditForm extends Component
{
    public $studentId;
    public $first_name;
    public $last_name;
    public $other_name;
    public $student_id_number;
    public $email;
    public $phone;
    public $college_class_id;
    public $cohort_id;
    public $status;
    
    public $classes = [];
    public $cohorts = [];
    public $statuses = ['Active', 'Inactive', 'Pending', 'Graduated'];
    
    protected $rules = [
        'first_name' => 'required|string|max:100',
        'last_name' => 'required|string|max:100',
        'other_name' => 'nullable|string|max:100',
        'student_id_number' => 'required|string|max:50',
        'email' => 'required|email|max:100',
        'phone' => 'nullable|string|max:20',
        'college_class_id' => 'required|integer|exists:college_classes,id',
        'cohort_id' => 'required|integer|exists:cohorts,id',
        'status' => 'required|string|in:Active,Inactive,Pending,Graduated',
    ];
    
    public function mount($studentId)
    {
        $this->studentId = $studentId;
        $this->loadStudent();
        $this->loadDropdowns();
    }
    
    public function loadStudent()
    {
        try {
            $student = Student::findOrFail($this->studentId);
            
            $this->first_name = $student->first_name;
            $this->last_name = $student->last_name;
            $this->other_name = $student->other_name;
            $this->student_id_number = $student->student_id;
            $this->email = $student->email;
            $this->phone = $student->phone;
            $this->college_class_id = $student->college_class_id;
            $this->cohort_id = $student->cohort_id;
            $this->status = $student->status ?? 'Active';
        } catch (\Exception $e) {
            Log::error('Error loading student for edit: ' . $e->getMessage());
            session()->flash('error', 'Failed to load student information for editing.');
        }
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
    
    public function updateStudent()
    {
        $this->validate();
        
        try {
            $student = Student::findOrFail($this->studentId);
            
            $student->first_name = $this->first_name;
            $student->last_name = $this->last_name;
            $student->other_name = $this->other_name;
            $student->student_id = $this->student_id_number;
            $student->email = $this->email;
            $student->phone = $this->phone;
            $student->college_class_id = $this->college_class_id;
            $student->cohort_id = $this->cohort_id;
            $student->status = $this->status;
            
            $student->save();
            
            // Update the user email if it exists
            if ($student->user) {
                $student->user->email = $this->email;
                $student->user->save();
            }
            
            session()->flash('success', 'Student information updated successfully.');
            
            return redirect()->route('students.show', $this->studentId);
        } catch (\Exception $e) {
            Log::error('Error updating student: ' . $e->getMessage());
            session()->flash('error', 'Failed to update student information.');
        }
    }
    
    public function render()
    {
        return view('livewire.student-edit-form');
    }
}