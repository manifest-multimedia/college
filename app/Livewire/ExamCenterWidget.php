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

    public function mount()
    {
        // Check for exams without slug and update with slug
        $exams = Exam::where('user_id', Auth::user()->id)->whereNull('slug')->with('course')->get();

        $this->generateSlug($exams);
    }

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



        return view(
            'livewire.exam-center-widget',
            [
                'exams' => $exams,
            ]
        );
    }

    public function generateSlug($exams)
    {
        foreach ($exams as $exam) {

            $slug  = Str::slug($exam->course->name . '-' . now()->format('Y-m-d H:i:s'));

            while (Exam::where('slug', $slug)->exists()) {
                $slug = Str::slug($exam->course->name . '-' . now()->format('Y-m-d H:i:s'));
            }

            $exam->update(['slug' => $slug]);
        }
    }
}
