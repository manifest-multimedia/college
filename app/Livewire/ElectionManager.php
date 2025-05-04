<?php

namespace App\Livewire;

use App\Models\Election;
use Illuminate\Container\Attributes\Log;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as Logger;
class ElectionManager extends Component
{
    use WithPagination, WithFileUploads;
    
    public $name;
    public $description;
    public $start_time;
    public $end_time;
    public $voting_session_duration = 30;
    public $is_active = false;
    
    public $electionId;
    public $isEditing = false;
    public $confirmingDeletion = false;
    public $electionIdToDelete;
    
    public $showCreateForm = false;
    public $searchQuery = '';
    
    public $templateElectionId;
    
    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'start_time' => 'required|date',
        'end_time' => 'required|date|after:start_time',
        'voting_session_duration' => 'required|integer|min:1|max:120',
        'is_active' => 'boolean',
    ];
    
    public function updatingSearchQuery()
    {
        $this->resetPage();
    }
    
    public function create()
    {
        $this->resetErrorBag();
        $this->reset(['name', 'description', 'start_time', 'end_time', 'isEditing', 'electionId', 'templateElectionId']);
        $this->voting_session_duration = 30;
        $this->is_active = false;
        
        // Set default start and end times to tomorrow and a week from now
        $this->start_time = now()->addDay()->format('Y-m-d\TH:i');
        $this->end_time = now()->addDays(8)->format('Y-m-d\TH:i');
        
        $this->showCreateForm = true;
    }
    
    public function save()
    {
        $validated = $this->validate();
        DB::beginTransaction();
        try {
            if ($this->isEditing) {
                $election = Election::findOrFail($this->electionId);
                $election->update($validated);
                
                // Log the update action
                \App\Models\ElectionAuditLog::log(
                    $election,
                    'admin',
                    auth()->id(),
                    'election_updated',
                    'Updated election: ' . $election->name
                );
                
                $this->dispatch('alert', [
                    'type' => 'success',
                    'message' => 'Election updated successfully!'
                ]);
            } else {
                if ($this->templateElectionId) {
                    // Clone the template election
                    $template = Election::findOrFail($this->templateElectionId);
                    $election = $template->cloneAsTemplate(
                        $validated['name'],
                        $validated['description'],
                        $validated['start_time'],
                        $validated['end_time']
                    );
                    $election->voting_session_duration = $validated['voting_session_duration'];
                    $election->is_active = $validated['is_active'];
                    $election->save();
                    
                    // Log the clone action
                    \App\Models\ElectionAuditLog::log(
                        $election,
                        'admin',
                        auth()->id(),
                        'election_cloned',
                        'Created new election from template: ' . $template->name,
                        ['template_id' => $template->id]
                    );
                } else {
                    // Create a new election
                    $election = Election::create($validated);
                    
                    // Log the creation action
                    \App\Models\ElectionAuditLog::log(
                        $election,
                        'admin',
                        auth()->id(),
                        'election_created',
                        'Created new election: ' . $election->name
                    );
                }
                
                $this->dispatch('alert', [
                    'type' => 'success',
                    'message' => 'Election created successfully!'
                ]);
            }
            
            DB::commit();
            $this->reset(['name', 'description', 'start_time', 'end_time', 'isEditing', 'electionId', 'templateElectionId']);
            $this->showCreateForm = false;
            
        } catch (\Exception $e) {
            dd($e->getMessage());
            // Log the error
            Logger::error('Election creation/update error: ' . $e->getMessage());
            DB::rollBack();
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
    
    public function edit(Election $election)
    {
        $this->resetErrorBag();
        $this->isEditing = true;
        $this->electionId = $election->id;
        $this->name = $election->name;
        $this->description = $election->description;
        $this->start_time = $election->start_time->format('Y-m-d\TH:i');
        $this->end_time = $election->end_time->format('Y-m-d\TH:i');
        $this->voting_session_duration = $election->voting_session_duration;
        $this->is_active = $election->is_active;
        $this->showCreateForm = true;
    }
    
    public function confirmDelete(Election $election)
    {
        $this->confirmingDeletion = true;
        $this->electionIdToDelete = $election->id;
    }
    
    public function delete()
    {
        $election = Election::findOrFail($this->electionIdToDelete);
        $electionName = $election->name;
        
        DB::beginTransaction();
        try {
            // Log the delete action
            \App\Models\ElectionAuditLog::log(
                null,
                'admin',
                auth()->id(),
                'election_deleted',
                'Deleted election: ' . $electionName,
                ['election_id' => $election->id]
            );
            
            $election->delete();
            DB::commit();
            
            $this->confirmingDeletion = false;
            $this->electionIdToDelete = null;
            
            $this->dispatch('alert', [
                'type' => 'success',
                'message' => 'Election deleted successfully!'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }
    
    public function cancelDelete()
    {
        $this->confirmingDeletion = false;
        $this->electionIdToDelete = null;
    }
    
    public function cancelEdit()
    {
        $this->reset(['name', 'description', 'start_time', 'end_time', 'isEditing', 'electionId', 'templateElectionId']);
        $this->showCreateForm = false;
    }

    public function toggleActiveStatus(Election $election)
    {
        $wasActive = $election->is_active;
        $nowActive = !$wasActive;
        
        // If we're activating the election and its start time is in the future,
        // ask the user if they want to start it now
        if ($nowActive && $election->start_time->isFuture()) {
            $this->dispatch('confirm', [
                'title' => 'Start election now?',
                'message' => 'This election\'s start time is in the future. Would you like to set the start time to now to allow immediate voting?',
                'onConfirm' => 'startElectionNow',
                'onCancel' => 'justToggleStatus',
                'data' => ['id' => $election->id]
            ]);
            return;
        }
        
        $this->justToggleStatus($election->id);
    }
    
    public function justToggleStatus($electionId)
    {
        $election = Election::findOrFail($electionId);
        $election->update(['is_active' => !$election->is_active]);
        
        \App\Models\ElectionAuditLog::log(
            $election,
            'admin',
            auth()->id(),
            'election_status_changed',
            'Changed election status to: ' . ($election->is_active ? 'active' : 'inactive')
        );
        
        $message = $election->is_active ? 'Election activated' : 'Election deactivated';
        $this->dispatch('alert', [
            'type' => 'success',
            'message' => $message
        ]);
    }
    
    public function startElectionNow($electionId)
    {
        $election = Election::findOrFail($electionId);
        $election->update([
            'is_active' => true,
            'start_time' => now()
        ]);
        
        \App\Models\ElectionAuditLog::log(
            $election,
            'admin',
            auth()->id(),
            'election_started_now',
            'Activated election and set start time to now'
        );
        
        $this->dispatch('alert', [
            'type' => 'success',
            'message' => 'Election activated and set to start immediately'
        ]);
    }
    
    public function render()
    {
        $searchQuery = '%' . $this->searchQuery . '%';
        
        $elections = Election::where('name', 'like', $searchQuery)
            ->orderByDesc('created_at')
            ->paginate(10);
            
        $templateElections = Election::orderByDesc('created_at')->get();
        
        return view('livewire.election-manager', [
            'elections' => $elections,
            'templateElections' => $templateElections
        ])->layout('components.dashboard.default', ['title' => 'Manage Elections']);
    }
}