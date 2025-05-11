<?php

namespace App\Livewire\Communication;

use App\Models\RecipientList;
use App\Models\RecipientListItem;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ContactGroups extends Component
{
    use WithPagination;
    
    public $name = '';
    public $description = '';
    public $type = 'sms';
    public $is_active = true;
    
    public $editMode = false;
    public $groupIdToEdit = null;
    public $searchTerm = '';
    
    protected $rules = [
        'name' => 'required|string|max:100',
        'description' => 'nullable|string|max:255',
        'type' => 'required|in:sms,email,both',
        'is_active' => 'boolean',
    ];
    
    public function createGroup()
    {
        $this->validate();
        
        try {
            RecipientList::create([
                'user_id' => Auth::id(),
                'name' => $this->name,
                'description' => $this->description,
                'type' => $this->type,
                'is_active' => $this->is_active,
            ]);
            
            session()->flash('success', 'Contact group created successfully.');
            $this->resetForm();
        } catch (\Exception $e) {
            Log::error('Failed to create contact group', [
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Failed to create contact group: ' . $e->getMessage());
        }
    }
    
    public function editGroup($id)
    {
        try {
            $group = RecipientList::findOrFail($id);
            $this->groupIdToEdit = $id;
            $this->name = $group->name;
            $this->description = $group->description;
            $this->type = $group->type;
            $this->is_active = $group->is_active;
            $this->editMode = true;
        } catch (\Exception $e) {
            Log::error('Failed to load contact group for editing', [
                'error' => $e->getMessage(),
                'id' => $id
            ]);
            session()->flash('error', 'Failed to load contact group for editing.');
        }
    }
    
    public function updateGroup()
    {
        $this->validate();
        
        try {
            $group = RecipientList::findOrFail($this->groupIdToEdit);
            $group->update([
                'name' => $this->name,
                'description' => $this->description,
                'type' => $this->type,
                'is_active' => $this->is_active,
            ]);
            
            session()->flash('success', 'Contact group updated successfully.');
            $this->resetForm();
        } catch (\Exception $e) {
            Log::error('Failed to update contact group', [
                'error' => $e->getMessage(),
                'id' => $this->groupIdToEdit
            ]);
            session()->flash('error', 'Failed to update contact group: ' . $e->getMessage());
        }
    }
    
    public function confirmDelete($id)
    {
        $this->dispatch('showDeleteConfirmation', id: $id);
    }
    
    public function deleteGroup($id)
    {
        try {
            $group = RecipientList::findOrFail($id);
            
            // Check if there are any contacts in this group
            $contactCount = $group->items()->count();
            
            if ($contactCount > 0) {
                // Delete all contacts in this group first
                $group->items()->delete();
            }
            
            $group->delete();
            
            session()->flash('success', 'Contact group deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete contact group', [
                'error' => $e->getMessage(),
                'id' => $id
            ]);
            session()->flash('error', 'Failed to delete contact group: ' . $e->getMessage());
        }
    }
    
    public function resetForm()
    {
        $this->reset(['name', 'description', 'type', 'is_active', 'editMode', 'groupIdToEdit']);
    }
    
    public function render()
    {
        $query = RecipientList::query()
            ->where(function ($q) {
                $q->where('type', 'sms')
                    ->orWhere('type', 'both');
            });
        
        if (!empty($this->searchTerm)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $this->searchTerm . '%');
            });
        }
        
        $contactGroups = $query->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('livewire.communication.contact-groups', [
            'contactGroups' => $contactGroups
        ])->layout('components.dashboard.default', ['title' => 'SMS Contact Groups']);
    }
}
