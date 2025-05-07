<?php

namespace App\Livewire\Memos;

use App\Models\Memo;
use App\Models\Department;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class MemoList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Filters
    public $statusFilter = '';
    public $priorityFilter = '';
    public $searchTerm = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $viewType = 'all';

    // For department filter
    public $departments = [];
    public $selectedDepartment = '';

    // For user search
    public $users = [];
    public $selectedUser = '';

    protected $queryString = [
        'statusFilter' => ['except' => ''],
        'priorityFilter' => ['except' => ''],
        'searchTerm' => ['except' => ''],
        'viewType' => ['except' => 'all'],
        'selectedDepartment' => ['except' => ''],
        'selectedUser' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
    ];

    public function mount()
    {
        // Load departments for filter
        $this->departments = Department::orderBy('name')->get();
        
        // Load users for filter (limit to a reasonable number or implement a search)
        $this->users = User::orderBy('name')->limit(100)->get();
    }

    public function resetFilters()
    {
        $this->statusFilter = '';
        $this->priorityFilter = '';
        $this->searchTerm = '';
        $this->viewType = 'all';
        $this->selectedDepartment = '';
        $this->selectedUser = '';
        $this->dateFrom = '';
        $this->dateTo = '';
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedPriorityFilter()
    {
        $this->resetPage();
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();
    }

    public function updatedViewType()
    {
        $this->resetPage();
    }

    public function updatedSelectedDepartment()
    {
        $this->resetPage();
    }

    public function updatedSelectedUser()
    {
        $this->resetPage();
    }

    public function render()
    {
        $memos = $this->buildQuery()->paginate(10);
        
        return view('livewire.memos.memo-list', [
            'memos' => $memos,
        ])
        ->layout('components.dashboard.default');
    }

    protected function buildQuery(): Builder
    {
        $query = Memo::with(['user', 'department', 'recipient', 'recipientDepartment']);

        // Filter by status
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        // Filter by priority
        if ($this->priorityFilter) {
            $query->where('priority', $this->priorityFilter);
        }

        // Search by title, reference or description
        if ($this->searchTerm) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('reference_number', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $this->searchTerm . '%');
            });
        }

        // Filter by date range
        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        // Filter by selected department
        if ($this->selectedDepartment) {
            $query->where(function ($q) {
                $q->where('department_id', $this->selectedDepartment)
                  ->orWhere('recipient_department_id', $this->selectedDepartment);
            });
        }

        // Filter by selected user
        if ($this->selectedUser) {
            $query->where(function ($q) {
                $q->where('user_id', $this->selectedUser)
                  ->orWhere('recipient_id', $this->selectedUser);
            });
        }

        // Apply view type filter
        switch ($this->viewType) {
            case 'created_by_me':
                $query->where('user_id', Auth::id());
                break;
            case 'to_me':
                $query->where('recipient_id', Auth::id());
                break;
            case 'to_my_department':
                $myDepartmentId = Auth::user()->department_id ?? null;
                if ($myDepartmentId) {
                    $query->where('recipient_department_id', $myDepartmentId);
                }
                break;
            case 'pending_approval':
                // Memos that require current user's approval
                $query->where('status', 'pending')
                      ->where(function ($q) {
                          $q->where('recipient_id', Auth::id())
                            ->orWhere(function ($q2) {
                                $myDepartmentId = Auth::user()->department_id ?? null;
                                if ($myDepartmentId) {
                                    $q2->where('recipient_department_id', $myDepartmentId);
                                }
                            });
                      });
                break;
            case 'needs_action':
                // Memos that require some action from the current user
                $query->whereIn('status', ['pending', 'forwarded'])
                      ->where(function ($q) {
                          $q->where('recipient_id', Auth::id())
                            ->orWhere(function ($q2) {
                                $myDepartmentId = Auth::user()->department_id ?? null;
                                if ($myDepartmentId) {
                                    $q2->where('recipient_department_id', $myDepartmentId);
                                }
                            });
                      });
                break;
            case 'recent_activity':
                // Memos with recent activity (last 7 days)
                $query->where('updated_at', '>=', now()->subDays(7));
                break;
            // 'all' case doesn't need special handling
        }

        return $query->orderBy('created_at', 'desc');
    }
}
