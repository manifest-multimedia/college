<?php

namespace App\Livewire;

use Carbon\Carbon;
use Livewire\Component;

class ExamTimer extends Component
{
    public $started_at;
    public $completed_at;

    public function mount($startedAt = null, $completedAt = null)
    {
        // Ensure dates are converted to ISO-8601 format
        $this->started_at = Carbon::parse($startedAt)->toIso8601String();
        $this->completed_at = Carbon::parse($completedAt)->toIso8601String();
    }

    public function render()
    {
        return view('livewire.exam-timer');
    }
}
