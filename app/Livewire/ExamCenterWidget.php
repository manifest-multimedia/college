<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Exam;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\Subject;

class ExamCenterWidget extends Component
{

    public $search;
    public $fitlerOptions = [
        '' => 'All',
        'upcoming' => 'Upcoming',
        'active' => 'Active',
        'completed' => 'Completed',
    ];

    public $filter = '';

    public function mount() {}

    // Handle form submission to create the exam


    public function render()
    {
        $exams = Exam::where('user_id', Auth::user()->id)
            ->with(['course.collegeClass', 'course.semester', 'course.year'])
            ->when(
                $this->search,
                function ($query) {
                    return $query->whereHas('course', function ($query) {
                        return $query->where('name', 'like', '%' . $this->search . '%');
                    });
                }
            )
            ->when(
                $this->filter,
                function ($query) {
                    return $query->where('status', $this->filter);
                }
            )
            ->get();

        // dd($exams);

        return view(
            'livewire.exam-center-widget',
            [
                'exams' => $exams,
            ]
        );
    }
}
