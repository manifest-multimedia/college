<?php

namespace App\Livewire\Finance;

use App\Models\CourseRegistration;
use App\Models\Student;
use App\Models\Subject;
use App\Models\AcademicYear;
use App\Models\Semester;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class CourseRegistrationApprovals extends Component
{
    use WithPagination;
    
    public $selectedAcademicYear;
    public $selectedSemester;
    public $filterStatus = 'pending'; // pending, approved, all
    public $search = '';
    public $perPage = 15;
    
    public $academicYears;
    public $semesters;
    
    protected $paginationTheme = 'bootstrap';
    
    public function mount()
    {
        $this->academicYears = AcademicYear::orderBy('name', 'desc')->get();
        $this->semesters = Semester::orderBy('id')->get();
        
        // Set current academic year and semester as defaults
        $currentAcademicYear = AcademicYear::where('is_current', true)->first();
        $currentSemester = Semester::where('is_current', true)->first();
        
        $this->selectedAcademicYear = $currentAcademicYear?->id ?? $this->academicYears->first()?->id;
        $this->selectedSemester = $currentSemester?->id ?? $this->semesters->first()?->id;
    }
    
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatingFilterStatus()
    {
        $this->resetPage();
    }
    
    public function updatingSelectedAcademicYear()
    {
        $this->resetPage();
    }
    
    public function updatingSelectedSemester()
    {
        $this->resetPage();
    }
    
    public function approveRegistration($registrationId)
    {
        $registration = CourseRegistration::findOrFail($registrationId);
        
        DB::transaction(function () use ($registration) {
            $registration->update([
                'is_approved' => true,
                'approved_at' => now(),
                'approved_by' => auth()->id(),
            ]);
        });
        
        session()->flash('success', 'Course registration approved successfully for ' . $registration->student->full_name);
        
        // Refresh the component
        $this->dispatch('registration-updated');
    }
    
    public function rejectRegistration($registrationId, $reason = null)
    {
        $registration = CourseRegistration::findOrFail($registrationId);
        
        DB::transaction(function () use ($registration, $reason) {
            $registration->update([
                'is_approved' => false,
                'rejection_reason' => $reason,
                'rejected_at' => now(),
                'rejected_by' => auth()->id(),
            ]);
        });
        
        session()->flash('success', 'Course registration rejected for ' . $registration->student->full_name);
        
        // Refresh the component
        $this->dispatch('registration-updated');
    }
    
    public function bulkApprove($registrationIds)
    {
        if (empty($registrationIds)) {
            session()->flash('error', 'No registrations selected.');
            return;
        }
        
        DB::transaction(function () use ($registrationIds) {
            CourseRegistration::whereIn('id', $registrationIds)
                ->update([
                    'is_approved' => true,
                    'approved_at' => now(),
                    'approved_by' => auth()->id(),
                ]);
        });
        
        $count = count($registrationIds);
        session()->flash('success', "Successfully approved {$count} course registrations.");
        
        // Refresh the component
        $this->dispatch('registration-updated');
    }
    
    public function getRegistrationsProperty()
    {
        $query = CourseRegistration::with([
            'student', 
            'subject', 
            'academicYear', 
            'semester'
        ]);
        
        // Filter by academic year and semester
        if ($this->selectedAcademicYear) {
            $query->where('academic_year_id', $this->selectedAcademicYear);
        }
        
        if ($this->selectedSemester) {
            $query->where('semester_id', $this->selectedSemester);
        }
        
        // Filter by approval status
        switch ($this->filterStatus) {
            case 'pending':
                $query->where('is_approved', false)->whereNull('rejected_at');
                break;
            case 'approved':
                $query->where('is_approved', true);
                break;
            case 'rejected':
                $query->where('is_approved', false)->whereNotNull('rejected_at');
                break;
            // 'all' doesn't add any filter
        }
        
        // Search functionality
        if ($this->search) {
            $query->whereHas('student', function($q) {
                $q->where('first_name', 'like', '%' . $this->search . '%')
                  ->orWhere('last_name', 'like', '%' . $this->search . '%')
                  ->orWhere('student_id', 'like', '%' . $this->search . '%');
            })->orWhereHas('subject', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('course_code', 'like', '%' . $this->search . '%');
            });
        }
        
        return $query->orderBy('registered_at', 'desc')
            ->paginate($this->perPage);
    }
    
    public function getStatsProperty()
    {
        $baseQuery = CourseRegistration::query();
        
        if ($this->selectedAcademicYear) {
            $baseQuery->where('academic_year_id', $this->selectedAcademicYear);
        }
        
        if ($this->selectedSemester) {
            $baseQuery->where('semester_id', $this->selectedSemester);
        }
        
        return [
            'total' => $baseQuery->count(),
            'pending' => $baseQuery->where('is_approved', false)->whereNull('rejected_at')->count(),
            'approved' => $baseQuery->where('is_approved', true)->count(),
            'rejected' => $baseQuery->where('is_approved', false)->whereNotNull('rejected_at')->count(),
        ];
    }
    
    public function render()
    {
        return view('livewire.finance.course-registration-approvals', [
            'registrations' => $this->registrations,
            'stats' => $this->stats,
        ]);
    }
}