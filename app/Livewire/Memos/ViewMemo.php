<?php

namespace App\Livewire\Memos;

use App\Models\Memo;
use App\Models\User;
use App\Models\Department;
use App\Services\Memo\MemoService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ViewMemo extends Component
{
    public $memo;
    public $memoId;
    public $comment = '';
    public $forwardToUserId = null;
    public $forwardToDepartmentId = null;
    public $forwardType = 'user';
    public $users = [];
    public $departments = [];
    public $currentTab = 'details';
    
    protected $memoService;

    protected $rules = [
        'comment' => 'nullable|string',
        'forwardToUserId' => 'required_if:forwardType,user|nullable|exists:users,id',
        'forwardToDepartmentId' => 'required_if:forwardType,department|nullable|exists:departments,id',
    ];

    public function boot(MemoService $memoService)
    {
        $this->memoService = $memoService;
    }

    public function mount($id)
    {
        $this->memoId = $id;
        $this->loadMemo();
        $this->loadUsers();
        $this->loadDepartments();
    }

    public function loadMemo()
    {
        try {
            $this->memo = Memo::with([
                'user', 
                'department', 
                'recipient', 
                'recipientDepartment',
                'attachments',
                'actions' => function ($query) {
                    $query->with(['user', 'forwardedToUser', 'forwardedToDepartment'])
                          ->orderBy('created_at', 'desc');
                }
            ])->findOrFail($this->memoId);
        } catch (\Exception $e) {
            session()->flash('error', 'Memo not found.');
            return redirect()->route('memos');
        }
    }

    public function loadUsers()
    {
        // Load staff users only (exclude Student and Parent roles)
        $this->users = User::whereHas('roles', function($query) {
                $query->whereNotIn('name', ['Student', 'Parent']);
            })
            ->orWhere(function($query) {
                // Also include users with legacy 'role' column (for backward compatibility)
                $query->whereNotIn('role', ['Student', 'Parent'])
                      ->whereDoesntHave('roles'); // Only if they don't have Spatie roles assigned
            })
            ->where('id', '!=', Auth::id())
            ->orderBy('name')
            ->get();
    }

    public function loadDepartments()
    {
        // Load all departments
        $this->departments = Department::orderBy('name')->get();
    }

    public function updatedForwardType()
    {
        // Reset recipient values when changing type
        if ($this->forwardType === 'user') {
            $this->forwardToDepartmentId = null;
        } else {
            $this->forwardToUserId = null;
        }
    }

    public function setActiveTab($tab)
    {
        $this->currentTab = $tab;
    }

    public function forwardMemo()
    {
        $this->validate([
            'comment' => 'nullable|string',
            'forwardToUserId' => 'required_if:forwardType,user|nullable|exists:users,id',
            'forwardToDepartmentId' => 'required_if:forwardType,department|nullable|exists:departments,id',
        ]);

        $data = [
            'comment' => $this->comment,
        ];

        if ($this->forwardType === 'user') {
            $data['forward_to_user_id'] = $this->forwardToUserId;
        } else {
            $data['forward_to_department_id'] = $this->forwardToDepartmentId;
        }

        $result = $this->memoService->forwardMemo($this->memoId, $data);

        if ($result['success']) {
            $this->loadMemo();
            $this->comment = '';
            $this->forwardToUserId = null;
            $this->forwardToDepartmentId = null;
            session()->flash('success', 'Memo forwarded successfully.');
        } else {
            session()->flash('error', $result['message']);
        }
    }

    public function approveMemo()
    {
        $result = $this->memoService->approveMemo($this->memoId, [
            'comment' => $this->comment,
        ]);

        if ($result['success']) {
            $this->loadMemo();
            $this->comment = '';
            session()->flash('success', 'Memo approved successfully.');
        } else {
            session()->flash('error', $result['message']);
        }
    }

    public function rejectMemo()
    {
        $this->validate([
            'comment' => 'required|string',
        ]);

        $result = $this->memoService->rejectMemo($this->memoId, [
            'comment' => $this->comment,
        ]);

        if ($result['success']) {
            $this->loadMemo();
            $this->comment = '';
            session()->flash('success', 'Memo rejected successfully.');
        } else {
            session()->flash('error', $result['message']);
        }
    }

    public function completeMemo()
    {
        $result = $this->memoService->completeMemo($this->memoId, [
            'comment' => $this->comment,
        ]);

        if ($result['success']) {
            $this->loadMemo();
            $this->comment = '';
            session()->flash('success', 'Memo marked as completed successfully.');
        } else {
            session()->flash('error', $result['message']);
        }
    }

    public function markAsProcured()
    {
        $result = $this->memoService->markAsProcured($this->memoId, [
            'comment' => $this->comment,
        ]);

        if ($result['success']) {
            $this->loadMemo();
            $this->comment = '';
            session()->flash('success', 'Items marked as procured successfully.');
        } else {
            session()->flash('error', $result['message']);
        }
    }

    public function markAsDelivered()
    {
        $result = $this->memoService->markAsDelivered($this->memoId, [
            'comment' => $this->comment,
        ]);

        if ($result['success']) {
            $this->loadMemo();
            $this->comment = '';
            session()->flash('success', 'Items marked as delivered successfully.');
        } else {
            session()->flash('error', $result['message']);
        }
    }

    public function markAsAudited()
    {
        $result = $this->memoService->markAsAudited($this->memoId, [
            'comment' => $this->comment,
        ]);

        if ($result['success']) {
            $this->loadMemo();
            $this->comment = '';
            session()->flash('success', 'Items marked as audited successfully.');
        } else {
            session()->flash('error', $result['message']);
        }
    }

    public function deleteAttachment($attachmentId)
    {
        $result = $this->memoService->deleteAttachment($attachmentId);

        if ($result) {
            $this->loadMemo();
            session()->flash('success', 'Attachment deleted successfully.');
        } else {
            session()->flash('error', 'Failed to delete attachment.');
        }
    }

    public function render()
    {
        return view('livewire.memos.view-memo')
            ->layout('components.dashboard.default');
    }
}
