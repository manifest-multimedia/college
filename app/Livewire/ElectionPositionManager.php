<?php

namespace App\Livewire;

use App\Models\Election;
use App\Models\ElectionPosition;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ElectionPositionManager extends Component
{
    use WithPagination;

    public $election;
    public $positions = [];
    public $newPositions = [];

    public $name;
    public $description;
    public $max_votes_allowed = 1;
    public $display_order = 0;

    public $positionId;
    public $isEditing = false;
    public $confirmingDeletion = false;
    public $positionIdToDelete;

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'max_votes_allowed' => 'required|integer|min:1|max:10',
        'display_order' => 'required|integer|min:0',
    ];

    public function mount(Election $election)
    {
        $this->election = $election;
        $this->loadPositions();
    }

    public function loadPositions()
    {
        $this->positions = $this->election->positions()->orderBy('display_order')->get();
    }

    public function addNewPosition()
    {
        $this->newPositions[] = [
            'name' => '',
            'description' => '',
            'max_votes_allowed' => 1,
            'display_order' => count($this->newPositions),
        ];
    }

    public function removeNewPosition($index)
    {
        unset($this->newPositions[$index]);
        $this->newPositions = array_values($this->newPositions);
    }

    public function saveNewPositions()
    {
        \Log::info('saveNewPositions method called');
        \Log::debug('New positions data:', $this->newPositions);
        
        try {
            $this->validate([
                'newPositions.*.name' => 'required|string|max:255',
                'newPositions.*.description' => 'nullable|string',
                'newPositions.*.max_votes_allowed' => 'required|integer|min:1|max:10',
                'newPositions.*.display_order' => 'required|integer|min:0',
            ]);
            
            \Log::info('Validation passed successfully');

            DB::beginTransaction();
            
            $positionCount = 0;
            foreach ($this->newPositions as $position) {
                \Log::info('Creating position:', ['name' => $position['name']]);
                
                $newPosition = ElectionPosition::create([
                    'election_id' => $this->election->id,
                    'name' => $position['name'],
                    'description' => $position['description'],
                    'max_selections' => $position['max_votes_allowed'],  // Map max_votes_allowed to max_selections
                    'display_order' => $position['display_order'],
                    'is_active' => true,
                ]);
                
                \Log::info('Position created successfully', ['id' => $newPosition->id]);
                $positionCount++;

                \App\Models\ElectionAuditLog::log(
                    $this->election,
                    'admin',
                    auth()->id(),
                    'position_created',
                    'Created position: ' . $newPosition->name,
                    ['position_id' => $newPosition->id]
                );
            }

            DB::commit();
            \Log::info('Transaction committed successfully', ['positions_created' => $positionCount]);
            
            $this->newPositions = [];
            $this->loadPositions();
            $this->dispatch('alert', [
                'type' => 'success',
                'message' => 'Positions created successfully!'
            ]);
            
            \Log::info('Alert dispatched and component state reset');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', [
                'errors' => $e->errors(),
                'message' => $e->getMessage()
            ]);
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error while creating positions', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
        
        \Log::info('saveNewPositions method completed');
    }

    public function edit(ElectionPosition $position)
    {
        $this->resetErrorBag();
        $this->isEditing = true;
        $this->positionId = $position->id;
        $this->name = $position->name;
        $this->description = $position->description;
        $this->max_votes_allowed = $position->max_votes_allowed;
        $this->display_order = $position->display_order;
    }

    public function cancelEdit()
    {
        $this->reset(['name', 'description', 'max_votes_allowed', 'display_order', 'isEditing', 'positionId']);
    }

    public function save()
    {
        $validated = $this->validate();
        
        \Log::info('Updating position ID: ' . $this->positionId);
        \Log::debug('Position update data:', $validated);
        
        DB::beginTransaction();
        try {
            $position = ElectionPosition::findOrFail($this->positionId);
            
            // Map the validated fields to the correct database columns
            $position->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'max_selections' => $validated['max_votes_allowed'],
                'display_order' => $validated['display_order'],
            ]);
            
            \App\Models\ElectionAuditLog::log(
                $this->election,
                'admin',
                auth()->id(),
                'position_updated',
                'Updated position: ' . $position->name,
                ['position_id' => $position->id]
            );
            
            DB::commit();
            $this->reset(['name', 'description', 'max_votes_allowed', 'display_order', 'isEditing', 'positionId']);
            $this->loadPositions();
            
            $this->dispatch('alert', [
                'type' => 'success',
                'message' => 'Position updated successfully!'
            ]);
            
            \Log::info('Position updated successfully', ['id' => $position->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating position', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'An error occurred: ' . $e->getMessage()
            ]);
        }
    }

    public function confirmDelete(ElectionPosition $position)
    {
        $this->confirmingDeletion = true;
        $this->positionIdToDelete = $position->id;
    }

    public function delete()
    {
        $position = ElectionPosition::findOrFail($this->positionIdToDelete);
        $positionName = $position->name;
        
        DB::beginTransaction();
        try {
            // Check if there are candidates or votes for this position
            $candidatesCount = $position->candidates()->count();
            $votesCount = $position->votes()->count();
            
            if ($candidatesCount > 0 || $votesCount > 0) {
                $this->dispatch('alert', [
                    'type' => 'error',
                    'message' => 'Cannot delete this position. It has candidates or votes associated with it.'
                ]);
                $this->confirmingDeletion = false;
                $this->positionIdToDelete = null;
                return;
            }
            
            \App\Models\ElectionAuditLog::log(
                $this->election,
                'admin',
                auth()->id(),
                'position_deleted',
                'Deleted position: ' . $positionName,
                ['position_id' => $position->id]
            );
            
            $position->delete();
            DB::commit();
            
            $this->confirmingDeletion = false;
            $this->positionIdToDelete = null;
            $this->loadPositions();
            
            $this->dispatch('alert', [
                'type' => 'success',
                'message' => 'Position deleted successfully!'
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
        $this->positionIdToDelete = null;
    }

    public function render()
    {
        return view('livewire.election-position-manager')->layout('components.dashboard.default', ['title' => 'Manage Election Positions']);
    }
}