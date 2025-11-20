<?php

namespace App\Livewire;

use App\Models\Exam;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Component;

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

    public function render()
    {
        try {
            if (Auth::user()->hasRole(['Super Admin', 'Administrator', 'admin'])) {
                $exams = Exam::with([
                    'course',
                    'course.collegeClass',
                    'course.semester',
                    'course.year',
                    'questionSets.questions', // Load question sets with their questions for calculation
                ])
                    ->when(
                        $this->search,
                        function ($query) {
                            return $query->whereHas('course', function ($query) {
                                return $query->where('name', 'like', '%'.$this->search.'%');
                            });
                        }
                    )->when(
                        $this->filter,
                        function ($query) {
                            return $query->where('status', $this->filter);
                        }
                    )->withCount('questions')
                    ->get();
            } else {
                $exams = Exam::where('user_id', Auth::user()->id)
                    ->with([
                        'course',
                        'course.collegeClass',
                        'course.semester',
                        'course.year',
                        'questionSets.questions', // Load question sets with their questions for calculation
                    ])
                    ->when(
                        $this->search,
                        function ($query) {
                            return $query->whereHas('course', function ($query) {
                                return $query->where('name', 'like', '%'.$this->search.'%');
                            });
                        }
                    )
                    ->when(
                        $this->filter,
                        function ($query) {
                            return $query->where('status', $this->filter);
                        }
                    )
                    ->withCount('questions')
                    ->get();
            }

            // Check for any exams with missing relationships
            foreach ($exams as $exam) {
                if (! $exam->course) {
                    Log::warning('Exam found without course relationship', [
                        'exam_id' => $exam->id,
                        'slug' => $exam->slug,
                        'user_id' => $exam->user_id,
                    ]);
                }
            }

            return view(
                'livewire.exam-center-widget',
                [
                    'exams' => $exams,
                    'users' => User::whereHas('roles', function ($query) {
                        $query->whereNotIn('name', ['Student', 'Parent']);
                    })
                        ->orWhere(function ($query) {
                            // Also include users with 'role' column (for backward compatibility)
                            $query->whereNotIn('role', ['Student', 'Parent'])
                                ->whereDoesntHave('roles'); // Only if they don't have Spatie roles assigned
                        })
                        ->orderBy('name', 'asc')
                        ->get(),
                ]
            );
        } catch (\Exception $e) {
            Log::error('Error in ExamCenterWidget render method: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            // Return view with empty data to prevent further errors
            return view(
                'livewire.exam-center-widget',
                [
                    'exams' => collect([]),
                    'users' => collect([]),
                ]
            );
        }
    }

    public function generateSlug($exams)
    {
        foreach ($exams as $exam) {
            if (! $exam->course) {
                Log::warning('Unable to generate slug: Exam has no course', ['exam_id' => $exam->id]);

                continue;
            }

            $slug = Str::slug($exam->course->name.'-'.now()->format('Y-m-d H:i:s'));

            while (Exam::where('slug', $slug)->exists()) {
                $slug = Str::slug($exam->course->name.'-'.now()->format('Y-m-d H:i:s'));
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

    /**
     * Delete an exam by its ID.
     *
     * @param  int  $id  The ID of the exam to be deleted.
     * @return void
     */
    public function deleteExam($id)
    {
        try {
            $exam = Exam::find($id);
            if ($exam) {
                // Get the exam name for the success message
                $examName = $exam->course ? $exam->course->name : 'Exam';

                // Delete the exam
                $exam->delete();

                // Flash success message
                session()->flash('success', "Exam '{$examName}' deleted successfully.");

                // Dispatch event to close modal
                $this->dispatch('examDeleted');
            } else {
                session()->flash('error', 'Exam not found.');
            }
        } catch (\Exception $e) {
            Log::error('Error deleting exam: '.$e->getMessage(), [
                'exam_id' => $id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);
            session()->flash('error', 'Failed to delete exam. Please try again later.');
        }
    }
}
