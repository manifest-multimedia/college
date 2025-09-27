<?php

namespace App\Livewire\Memos;

use App\Models\Department;
use App\Models\User;
use App\Services\Memo\MemoService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;

class CreateMemo extends Component
{
    use WithFileUploads;

    // Form fields
    public $title = '';
    public $description = '';
    public $priority = 'medium';
    public $requestedAction = '';
    public $recipientType = 'user'; // 'user' or 'department'
    public $recipientId = null;
    public $recipientDepartmentId = null;
    public $status = 'pending'; // default to pending, could be 'draft'
    public $attachments = [];

    // Data for dropdowns
    public $users = [];
    public $departments = [];

    // MemoService instance
    protected $memoService;

    protected $rules = [
        'title' => 'required|string|min:5|max:255',
        'description' => 'required|string|min:10',
        'priority' => 'required|in:low,medium,high',
        'requestedAction' => 'nullable|string|max:255',
        'recipientType' => 'required|in:user,department',
        'recipientId' => 'required_if:recipientType,user|nullable|exists:users,id',
        'recipientDepartmentId' => 'required_if:recipientType,department|nullable|exists:departments,id',
        'status' => 'required|in:draft,pending',
        'attachments.*' => 'nullable|file|max:10240', // 10MB max per file
    ];

    protected $messages = [
        'title.required' => 'Please enter a title for the memo.',
        'description.required' => 'Please provide a description for the memo.',
        'recipientId.required_if' => 'Please select a recipient for the memo.',
        'recipientDepartmentId.required_if' => 'Please select a recipient department for the memo.',
        'attachments.*.max' => 'Attachments must be less than 10MB in size.',
    ];

    public function boot(MemoService $memoService)
    {
        $this->memoService = $memoService;
    }

    public function mount()
    {
        $this->loadUsers();
        $this->loadDepartments();
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

    public function updatedRecipientType()
    {
        // Reset recipient values when changing type
        if ($this->recipientType === 'user') {
            $this->recipientDepartmentId = null;
        } else {
            $this->recipientId = null;
        }
    }

    public function saveDraft()
    {
        $this->status = 'draft';
        $this->saveMemo();
    }

    public function saveAsPending()
    {
        $this->status = 'pending';
        $this->saveMemo();
    }

    protected function saveMemo()
    {
        $this->validate();

        // Prepare data for creating memo
        $data = [
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'requested_action' => $this->requestedAction,
            'status' => $this->status,
        ];

        // Add recipient info based on type
        if ($this->recipientType === 'user') {
            $data['recipient_id'] = $this->recipientId;
        } else {
            $data['recipient_department_id'] = $this->recipientDepartmentId;
        }

        // Create the memo using the service
        $result = $this->memoService->createMemo($data, $this->attachments);

        if ($result['success']) {
            session()->flash('success', 'Memo created successfully.');
            return redirect()->route('memo.view', $result['memo']->id);
        } else {
            session()->flash('error', $result['message']);
        }
    }

    public function removeAttachment($index)
    {
        // Remove attachment from the attachments array
        if (isset($this->attachments[$index])) {
            array_splice($this->attachments, $index, 1);
        }
    }

    public function render()
    {
        return view('livewire.memos.create-memo')
            ->layout('components.dashboard.default');
    }
}
