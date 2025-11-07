<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Election;
use Livewire\WithPagination;

class ElectionResultsArchive extends Component
{
    use WithPagination;
    
    protected $paginationTheme = 'bootstrap';
    
    public $search = '';
    public $statusFilter = '';
    
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatingStatusFilter()
    {
        $this->resetPage();
    }
    
    public function render()
    {
        $query = Election::query()
            ->when($this->search, function($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
            });
            
        // Apply status filter based on election state
        if ($this->statusFilter) {
            $now = now();
            
            switch ($this->statusFilter) {
                case 'inactive':
                    $query->where('is_active', false);
                    break;
                case 'upcoming':
                    $query->where('is_active', true)
                          ->where('start_time', '>', $now);
                    break;
                case 'active':
                    $query->where('is_active', true)
                          ->where('start_time', '<=', $now)
                          ->where('end_time', '>=', $now);
                    break;
                case 'completed':
                    $query->where('end_time', '<', $now);
                    break;
            }
        }
        
        $elections = $query->orderBy('start_time', 'desc')->paginate(10);
        
        // Add computed status to each election
        $elections->getCollection()->transform(function ($election) {
            $now = now();
            
            if (!$election->is_active) {
                $election->computed_status = 'inactive';
            } elseif ($now->lessThan($election->start_time)) {
                $election->computed_status = 'upcoming';
            } elseif ($now->between($election->start_time, $election->end_time)) {
                $election->computed_status = 'active';
            } else {
                $election->computed_status = 'completed';
            }
            
            return $election;
        });
            
        return view('livewire.election-results-archive', [
            'elections' => $elections
        ]);
    }
}
