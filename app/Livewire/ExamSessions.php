<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Exam;
use Livewire\WithPagination;

class ExamSessions extends Component
{
    use WithPagination;
    
    public $search = '';
    public $perPage = 10;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    
    public function updatingSearch()
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

    public function render()
    {
        return view('livewire.exam-sessions', [
            'examSessions' => Exam::where('title', 'like', '%'.$this->search.'%')
                ->orderBy($this->sortField, $this->sortDirection)
                ->paginate($this->perPage)
        ]);
    }
}