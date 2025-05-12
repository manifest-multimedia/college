<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Student;
use App\Models\CollegeClass;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class DashboardCounters extends Component
{
    public $totalUsers = 0;
    public $activeStaff = 0;
    public $totalStudents = 0;
    public $activePrograms = 0;
    public $smsCredits = 0;
    public $totalExams = 0;

    public function mount()
    {
        try {
            // Get total users
            $this->totalUsers = User::count();
            
            // Get active staff (users that don't have student or parent role)
            $studentRole = Role::where('name', 'Student')->first();
            $parentRole = Role::where('name', 'Parent')->first();
            
            $excludeRoleIds = [];
            if ($studentRole) {
                $excludeRoleIds[] = $studentRole->id;
            }
            if ($parentRole) {
                $excludeRoleIds[] = $parentRole->id;
            }
            
            if (!empty($excludeRoleIds)) {
                $this->activeStaff = User::whereHas('roles', function ($query) use ($excludeRoleIds) {
                    $query->whereNotIn('id', $excludeRoleIds);
                })->count();
            } else {
                $this->activeStaff = User::count();
            }
            
            // Get total students
            $this->totalStudents = Student::count();
            
            // Get active programs (college classes)
            $this->activePrograms = CollegeClass::count();
        } catch (\Exception $e) {
            Log::error('Error loading dashboard counters: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.dashboard-counters');
    }
}