<?php

namespace App\Livewire\Communication;

use App\Models\RecipientList;
use App\Models\RecipientListItem;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;

class Contacts extends Component
{
    use WithPagination;
    
    public $name = '';
    public $phone = '';
    public $email = '';
    public $is_active = true;
    public $recipient_list_id = null;
    
    public $editMode = false;
    public $contactIdToEdit = null;
    public $searchTerm = '';
    public $selectedGroup = null;
    
    protected $rules = [
        'name' => 'required|string|max:100',
        'phone' => 'required|string|max:15',
        'email' => 'nullable|email|max:100',
        'recipient_list_id' => 'required|exists:recipient_lists,id',
        'is_active' => 'boolean',
    ];
    
    public function mount($groupId = null)
    {
        if ($groupId) {
            $this->selectedGroup = $groupId;
            $this->recipient_list_id = $groupId;
        }
    }
    
    public function createContact()
    {
        $this->validate();
        
        try {
            RecipientListItem::create([
                'recipient_list_id' => $this->recipient_list_id,
                'name' => $this->name,
                'phone' => $this->phone,
                'email' => $this->email,
                'is_active' => $this->is_active,
            ]);
            
            session()->flash('success', 'Contact created successfully.');
            $this->resetForm();
        } catch (\Exception $e) {
            Log::error('Failed to create contact', [
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Failed to create contact: ' . $e->getMessage());
        }
    }
    
    public function editContact($id)
    {
        try {
            $contact = RecipientListItem::findOrFail($id);
            $this->contactIdToEdit = $id;
            $this->name = $contact->name;
            $this->phone = $contact->phone;
            $this->email = $contact->email;
            $this->recipient_list_id = $contact->recipient_list_id;
            $this->is_active = $contact->is_active;
            $this->editMode = true;
        } catch (\Exception $e) {
            Log::error('Failed to load contact for editing', [
                'error' => $e->getMessage(),
                'id' => $id
            ]);
            session()->flash('error', 'Failed to load contact for editing.');
        }
    }
    
    public function updateContact()
    {
        $this->validate();
        
        try {
            $contact = RecipientListItem::findOrFail($this->contactIdToEdit);
            $contact->update([
                'recipient_list_id' => $this->recipient_list_id,
                'name' => $this->name,
                'phone' => $this->phone,
                'email' => $this->email,
                'is_active' => $this->is_active,
            ]);
            
            session()->flash('success', 'Contact updated successfully.');
            $this->resetForm();
        } catch (\Exception $e) {
            Log::error('Failed to update contact', [
                'error' => $e->getMessage(),
                'id' => $this->contactIdToEdit
            ]);
            session()->flash('error', 'Failed to update contact: ' . $e->getMessage());
        }
    }
    
    public function confirmDelete($id)
    {
        $this->dispatch('showDeleteConfirmation', id: $id);
    }
    
    public function deleteContact($id)
    {
        try {
            RecipientListItem::findOrFail($id)->delete();
            session()->flash('success', 'Contact deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete contact', [
                'error' => $e->getMessage(),
                'id' => $id
            ]);
            session()->flash('error', 'Failed to delete contact: ' . $e->getMessage());
        }
    }
    
    public function resetForm()
    {
        $this->reset(['name', 'phone', 'email', 'is_active', 'editMode', 'contactIdToEdit']);
        // Keep the recipient_list_id if a group is selected
        if (!$this->selectedGroup) {
            $this->reset(['recipient_list_id']);
        }
    }
    
    public function updatedSelectedGroup($value)
    {
        $this->recipient_list_id = $value;
        $this->resetPage();
    }
    
    public function render()
    {
        $groups = RecipientList::where('is_active', true)
            ->where(function ($query) {
                $query->where('type', 'sms')
                    ->orWhere('type', 'both');
            })
            ->orderBy('name')
            ->get();
        
        $query = RecipientListItem::query()
            ->whereNotNull('phone');
        
        if ($this->selectedGroup) {
            $query->where('recipient_list_id', $this->selectedGroup);
        }
        
        if (!empty($this->searchTerm)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('phone', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('email', 'like', '%' . $this->searchTerm . '%');
            });
        }
        
        $contacts = $query->with('recipientList')
            ->orderBy('name')
            ->paginate(10);
        
        $pageTitle = $this->selectedGroup 
            ? 'Contacts - ' . optional(RecipientList::find($this->selectedGroup))->name 
            : 'All SMS Contacts';
        
        return view('livewire.communication.contacts', [
            'contacts' => $contacts,
            'groups' => $groups
        ])->layout('components.dashboard.default', ['title' => $pageTitle]);
    }
}
