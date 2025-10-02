<?php

namespace App\Livewire\Academics;

use Livewire\Component;
use App\Models\Year;
use Livewire\WithPagination;
use Illuminate\Support\Str;

class YearManager extends Component
{
    use WithPagination;

    public $name = '';
    public $yearId = null;
    public $isEdit = false;

    protected $rules = [
        'name' => 'required|string|max:255|unique:years,name',
    ];

    public function resetForm()
    {
        $this->name = '';
        $this->yearId = null;
        $this->isEdit = false;
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate();
        Year::create([
            'name' => $this->name,
            'slug' => Str::slug($this->name),
        ]);
        session()->flash('success', 'Year created successfully.');
        $this->resetForm();
    }

    public function edit($id)
    {
        $year = Year::findOrFail($id);
        $this->yearId = $year->id;
        $this->name = $year->name;
        $this->isEdit = true;
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:years,name,' . $this->yearId,
        ]);
        $year = Year::findOrFail($this->yearId);
        $year->update([
            'name' => $this->name,
            'slug' => Str::slug($this->name),
        ]);
        session()->flash('success', 'Year updated successfully.');
        $this->resetForm();
    }

    public function delete($id)
    {
        $year = Year::findOrFail($id);
        $year->delete();
        session()->flash('success', 'Year deleted successfully.');
        $this->resetForm();
    }

    public function render()
    {
        $years = Year::orderBy('id')->paginate(10);
        return view('livewire.academics.year-manager', compact('years'))->layout('components.dashboard.default');
    }
}
