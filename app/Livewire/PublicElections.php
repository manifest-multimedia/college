<?php

namespace App\Livewire;

use App\Models\Election;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class PublicElections extends Component
{
    public $activeElections;

    public $upcomingElections;

    public $completedElections;

    public function mount()
    {
        try {
            // Get active elections (current date falls within start_time and end_time)
            $this->activeElections = Election::where('is_active', true)
                ->where('start_time', '<=', now())
                ->where('end_time', '>=', now())
                ->orderBy('end_time', 'asc') // Show elections ending soon first
                ->get();

            // Get upcoming elections
            $this->upcomingElections = Election::where('is_active', true)
                ->where('start_time', '>', now())
                ->orderBy('start_time', 'asc') // Show closest upcoming election first
                ->get();

            // Get recently completed elections
            $this->completedElections = Election::where('is_active', true)
                ->where('end_time', '<', now())
                ->orderBy('end_time', 'desc') // Show most recently ended first
                ->limit(3) // Only show 3 most recent completed elections
                ->get();

            Log::info('Public Elections page loaded', [
                'active_count' => $this->activeElections->count(),
                'upcoming_count' => $this->upcomingElections->count(),
                'completed_count' => $this->completedElections->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading public elections', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->activeElections = collect();
            $this->upcomingElections = collect();
            $this->completedElections = collect();
        }
    }

    public function render()
    {
        return view('livewire.public-elections')
            ->layout('components.public.layout', ['title' => 'Public Elections']);
    }
}
