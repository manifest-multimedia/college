<?php

namespace App\Livewire\Settings;

use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class UserDepartmentAssignment extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';

    public $departmentFilter = '';

    public $sortField = 'name';

    public $sortDirection = 'asc';

    // Form properties
    public $userId;

    public $userName;

    public $selectedDepartments = [];

    public $departmentHeadRoles = [];

    public $isOpen = false;

    protected $listeners = [
        'closeModalAction' => 'closeModal',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDepartmentFilter()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function assignDepartments($id)
    {
        try {
            $user = User::findOrFail($id);
            $this->userId = $user->id;
            $this->userName = $user->name;

            // Get current department assignments
            $userDepartments = $user->departments()->get();
            $this->selectedDepartments = $userDepartments->pluck('id')->toArray();

            // Get departments where user is head
            $this->departmentHeadRoles = $userDepartments->filter(function ($dept) {
                return $dept->pivot->is_head;
            })->pluck('id')->toArray();

            $this->isOpen = true;
            $this->dispatch('modalStateChanged', ['isOpen' => true]);
            $this->dispatch('userDepartmentDataLoaded');

        } catch (\Exception $e) {
            Log::error('Error loading user department data: '.$e->getMessage(), [
                'user_id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);
            session()->flash('error', 'Failed to load user department data.');
        }
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->reset(['userId', 'userName', 'selectedDepartments', 'departmentHeadRoles']);
        $this->dispatch('closeModal');
    }

    public function saveAssignments()
    {
        try {
            $user = User::findOrFail($this->userId);

            // Begin transaction
            DB::beginTransaction();

            // Sync departments with head status
            $syncData = [];
            foreach ($this->selectedDepartments as $departmentId) {
                $syncData[$departmentId] = [
                    'is_head' => in_array($departmentId, $this->departmentHeadRoles),
                ];
            }

            $user->departments()->sync($syncData);

            // Commit transaction
            DB::commit();

            session()->flash('success', 'Department assignments updated successfully.');
            $this->closeModal();

        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();

            Log::error('Error saving department assignments: '.$e->getMessage(), [
                'user_id' => $this->userId,
                'trace' => $e->getTraceAsString(),
            ]);
            session()->flash('error', 'Failed to save department assignments.');
        }
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, function ($query) {
                return $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->departmentFilter, function ($query) {
                return $query->whereHas('departments', function ($q) {
                    $q->where('departments.id', $this->departmentFilter);
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        $departments = Department::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('livewire.settings.user-department-assignment', [
            'users' => $users,
            'departments' => $departments,
        ]);
    }
}
