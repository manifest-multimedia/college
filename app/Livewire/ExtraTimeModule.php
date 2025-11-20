<?php

namespace App\Livewire;

use App\Models\Student;
use Livewire\Component;

class ExtraTimeModule extends Component
{
    public $mode = 'index';

    public $exam_filter;

    public function render()
    {

        $students = Student::with('user')
            ->when(
                $this->exam_filter,
                fn ($q) => $q->whereHas(
                    'examSession',
                    fn ($q) => $q->where('completed_at', '>', now()->format('H:i'))
                )
            )
            ->get();

        return view('livewire.extra-time-module', [
            'students' => $students,

        ]);
    }
}
