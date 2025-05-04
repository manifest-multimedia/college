<?php

namespace App\Livewire;

use App\Models\Election;
use Livewire\Component;

class ElectionExpired extends Component
{
    public $election;
    
    public function mount(Election $election)
    {
        $this->election = $election;
    }
    
    public function render()
    {
        return view('livewire.election-expired')
            ->layout('components.dashboard.default', ['title' => 'Voting Session Expired']);
    }
}