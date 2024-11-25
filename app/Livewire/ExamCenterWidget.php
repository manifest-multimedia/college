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

        // Check for exams without passwords
        $nopass = Exam::where('user_id', Auth::user()->id)->whereNull('password')->get();

        $this->generatePassword($nopass);
    }

    // Handle form submission to create the exam


    public function render()
    {
        if (Auth::user()->role == 'admin' || Auth::user()->role == 'Super Admin') {
            $exams = Exam::with(['course.collegeClass', 'course.semester', 'course.year'])
                ->when(
                    $this->search,
                    function ($query) {
                        return $query->whereHas('course', function ($query) {
                            return $query->where('name', 'like', '%' . $this->search . '%');
                        });
                    }
                )->when(
                    $this->filter,
                    function ($query) {
                        return $query->where('status', $this->filter);
                    }
                )->get();
        } else {

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
        }



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
    public function generatePassword($exams)
    {
        foreach ($exams as $exam) {
            $password = Str::random(8);
            while (Exam::where('password', $password)->exists()) {
                $password = Str::random(8);
            }
            $exam->update(['password' => $password]);
        }
    }

    /*************  ✨ Codeium Command ⭐  *************/
    /**
     * Delete an exam by its ID.
     *
     * @param int $id The ID of the exam to be deleted.
     * @return void
     */
    /******  d1373e01-cbab-4675-b4ac-3ccf648c79b9  *******/
    public function deleteExam($id)
    {
        $exam = Exam::find($id);
        $exam->delete();
    }
}
