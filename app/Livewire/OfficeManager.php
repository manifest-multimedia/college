<?php

namespace App\Livewire;

use App\Models\Office;
use App\Models\Department;
use Livewire\Component;
use Livewire\WithPagination;

class OfficeManager extends Component
{
    use WithPagination;

    public $departments;
    public $office_id;
    public $department_id;
    public $name;
    public $code;
    public $location;
    public $phone;
    public $email;
    public $description;
    public $is_active = true;

    public $search = '';
    public $filterDepartment = '';
    public $filterStatus = '';

    public $showModal = false;
    public $editMode = false;

    protected function rules()
    {
        return [
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:offices,code,' . $this->office_id,
            'location' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }

    public function mount()
    {
        $this->departments = Department::where('is_active', true)->orderBy('name')->get();
    }

    public function render()
    {
        $offices = Office::query()
            ->with('department')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('code', 'like', '%' . $this->search . '%')
                    ->orWhere('location', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterDepartment, function ($query) {
                $query->where('department_id', $this->filterDepartment);
            })
            ->when($this->filterStatus !== '', function ($query) {
                $query->where('is_active', $this->filterStatus);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.office-manager', [
            'offices' => $offices
        ]);
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function openEditModal($officeId)
    {
        $office = Office::findOrFail($officeId);
        
        $this->office_id = $office->id;
        $this->department_id = $office->department_id;
        $this->name = $office->name;
        $this->code = $office->code;
        $this->location = $office->location;
        $this->phone = $office->phone;
        $this->email = $office->email;
        $this->description = $office->description;
        $this->is_active = $office->is_active;

        $this->editMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->editMode) {
            $office = Office::findOrFail($this->office_id);
            $office->update([
                'department_id' => $this->department_id,
                'name' => $this->name,
                'code' => $this->code,
                'location' => $this->location,
                'phone' => $this->phone,
                'email' => $this->email,
                'description' => $this->description,
                'is_active' => $this->is_active,
            ]);

            session()->flash('message', 'Office updated successfully.');
        } else {
            Office::create([
                'department_id' => $this->department_id,
                'name' => $this->name,
                'code' => $this->code,
                'location' => $this->location,
                'phone' => $this->phone,
                'email' => $this->email,
                'description' => $this->description,
                'is_active' => $this->is_active,
            ]);

            session()->flash('message', 'Office created successfully.');
        }

        $this->closeModal();
    }

    public function toggleStatus($officeId)
    {
        $office = Office::findOrFail($officeId);
        $office->update(['is_active' => !$office->is_active]);

        session()->flash('message', 'Office status updated successfully.');
    }

    public function delete($officeId)
    {
        $office = Office::findOrFail($officeId);
        
        // Check if office has assets
        if ($office->assets()->count() > 0) {
            session()->flash('error', 'Cannot delete office with assigned assets.');
            return;
        }

        $office->delete();
        session()->flash('message', 'Office deleted successfully.');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->office_id = null;
        $this->department_id = '';
        $this->name = '';
        $this->code = '';
        $this->location = '';
        $this->phone = '';
        $this->email = '';
        $this->description = '';
        $this->is_active = true;
        $this->resetValidation();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterDepartment()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }
}
