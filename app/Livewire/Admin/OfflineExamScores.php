<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\OfflineExam;
use App\Models\OfflineExamScore;
use App\Models\Student;
use App\Models\CollegeClass;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Exports\OfflineExamScoresExport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class OfflineExamScores extends Component
{
    use WithPagination;

    // Form properties
    public $selectedExamId = null;
    public $selectedClassId = null;
    public $selectedAcademicYearId = null;
    public $selectedSemesterId = null;
    public $showForm = false;
    public $editingScore = null;
    
    // Score form properties
    public $scoreForm = [
        'student_id' => null,
        'score' => null,
        'total_marks' => 100,
        'remarks' => '',
        'exam_date' => null,
    ];

    // Bulk entry properties
    public $bulkEntry = false;
    public $studentScores = [];
    public $bulkProgress = 0;
    public $isBulkSaving = false;

    // Search and filter
    public $search = '';
    public $perPage = 15;

    protected $rules = [
        'scoreForm.student_id' => 'required|exists:students,id',
        'scoreForm.score' => 'required|numeric|min:0',
        'scoreForm.total_marks' => 'required|numeric|min:1',
        'scoreForm.remarks' => 'nullable|string|max:1000',
        'scoreForm.exam_date' => 'nullable|date',
    ];

    protected $messages = [
        'scoreForm.student_id.required' => 'Please select a student.',
        'scoreForm.score.required' => 'Score is required.',
        'scoreForm.score.numeric' => 'Score must be a number.',
        'scoreForm.total_marks.required' => 'Total marks is required.',
        'scoreForm.total_marks.numeric' => 'Total marks must be a number.',
        'scoreForm.total_marks.min' => 'Total marks must be at least 1.',
    ];

    public function mount()
    {
        $this->selectedAcademicYearId = AcademicYear::where('is_current', true)->first()?->id;
        $this->selectedSemesterId = Semester::where('is_current', true)->first()?->id;
        $this->scoreForm['exam_date'] = now()->format('Y-m-d');
    }

    public function updatedSelectedExamId()
    {
        $this->resetPage();
        $this->resetBulkEntry();
    }

    public function updatedSelectedClassId()
    {
        $this->resetPage();
        $this->resetBulkEntry();
    }

    public function toggleBulkEntry()
    {
        $this->bulkEntry = !$this->bulkEntry;
        if ($this->bulkEntry) {
            $this->loadStudentsForBulkEntry();
        } else {
            $this->resetBulkEntry();
        }
    }

    public function loadStudentsForBulkEntry()
    {
        if (!$this->selectedExamId || !$this->selectedClassId) {
            return;
        }

        $exam = OfflineExam::find($this->selectedExamId);
        $students = Student::where('college_class_id', $this->selectedClassId)
            ->orderBy('student_id')
            ->get();

        $this->studentScores = [];
        
        foreach ($students as $student) {
            $existingScore = OfflineExamScore::where('offline_exam_id', $this->selectedExamId)
                ->where('student_id', $student->id)
                ->first();

            $this->studentScores[] = [
                'student_id' => $student->id,
                'student_name' => $student->full_name,
                'student_number' => $student->student_id,
                'score' => $existingScore ? $existingScore->score : null,
                'total_marks' => $existingScore ? $existingScore->total_marks : ($exam->total_marks ?? 100),
                'remarks' => $existingScore ? $existingScore->remarks : '',
                'existing_score_id' => $existingScore ? $existingScore->id : null,
            ];
        }
    }

    public function createScore()
    {
        $this->resetForm();
        $this->showForm = true;
        $this->editingScore = null;
    }

    public function editScore($scoreId)
    {
        $score = OfflineExamScore::with('student')->findOrFail($scoreId);
        
        $this->scoreForm = [
            'student_id' => $score->student_id,
            'score' => $score->score,
            'total_marks' => $score->total_marks,
            'remarks' => $score->remarks,
            'exam_date' => $score->exam_date ? $score->exam_date->format('Y-m-d') : null,
        ];

        $this->editingScore = $scoreId;
        $this->showForm = true;
    }

    public function saveScore()
    {
        $this->validate();

        if (!$this->selectedExamId) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Please select an offline exam first.',
                'timer' => 3000
            ]);
            return;
        }

        try {
            $data = $this->scoreForm;
            $data['offline_exam_id'] = $this->selectedExamId;
            $data['recorded_by'] = Auth::id();
            
            if ($data['exam_date']) {
                $data['exam_date'] = $data['exam_date'] . ' 00:00:00';
            }

            if ($this->editingScore) {
                $score = OfflineExamScore::findOrFail($this->editingScore);
                
                // Check if student is changing and if new combination already exists
                if ($score->student_id != $data['student_id']) {
                    $existing = OfflineExamScore::where('offline_exam_id', $data['offline_exam_id'])
                        ->where('student_id', $data['student_id'])
                        ->where('id', '!=', $this->editingScore)
                        ->first();
                        
                    if ($existing) {
                        $this->dispatch('notify', [
                            'type' => 'error',
                            'message' => 'A score already exists for this student in the selected exam.',
                            'timer' => 3000
                        ]);
                        return;
                    }
                }
                
                $score->update($data);
                $message = 'Score updated successfully.';

                // Log the update action
                Log::info('Offline exam score updated', [
                    'score_id' => $score->id,
                    'exam_id' => $data['offline_exam_id'],
                    'student_id' => $data['student_id'],
                    'updated_by' => Auth::id(),
                    'old_score' => $score->getOriginal('score'),
                    'new_score' => $data['score']
                ]);
            } else {
                // Check if score already exists for this student and exam
                $existing = OfflineExamScore::where('offline_exam_id', $data['offline_exam_id'])
                    ->where('student_id', $data['student_id'])
                    ->first();
                    
                if ($existing) {
                    $this->dispatch('notify', [
                        'type' => 'error',
                        'message' => 'A score already exists for this student in the selected exam.',
                        'timer' => 3000
                    ]);
                    return;
                }

                $score = OfflineExamScore::create($data);
                $message = 'Score recorded successfully.';

                // Log the creation action
                Log::info('Offline exam score created', [
                    'score_id' => $score->id,
                    'exam_id' => $data['offline_exam_id'],
                    'student_id' => $data['student_id'],
                    'score' => $data['score'],
                    'recorded_by' => Auth::id()
                ]);
            }

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => $message,
                'timer' => 3000
            ]);

            $this->resetForm();
            $this->showForm = false;
            $this->editingScore = null;

        } catch (\Exception $e) {
            Log::error('Error saving offline exam score', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data ?? null,
                'user_id' => Auth::id()
            ]);

            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error saving score: ' . $e->getMessage(),
                'timer' => 5000
            ]);
        }
    }

    public function saveBulkScores()
    {
        if (!$this->selectedExamId) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Please select an offline exam first.',
                'timer' => 3000
            ]);
            return;
        }

        try {
            $this->isBulkSaving = true;
            $this->bulkProgress = 0;
            
            $savedCount = 0;
            $errorCount = 0;
            $totalScores = collect($this->studentScores)->filter(function($score) {
                return !empty($score['score']);
            })->count();

            foreach ($this->studentScores as $index => $studentScore) {
                if (empty($studentScore['score'])) {
                    continue; // Skip students without scores
                }

                // Validate score
                if (!is_numeric($studentScore['score']) || $studentScore['score'] < 0) {
                    $errorCount++;
                    continue;
                }

                if (!is_numeric($studentScore['total_marks']) || $studentScore['total_marks'] < 1) {
                    $errorCount++;
                    continue;
                }

                try {
                    $data = [
                        'offline_exam_id' => $this->selectedExamId,
                        'student_id' => $studentScore['student_id'],
                        'score' => $studentScore['score'],
                        'total_marks' => $studentScore['total_marks'],
                        'remarks' => $studentScore['remarks'] ?? '',
                        'recorded_by' => Auth::id(),
                        'exam_date' => $this->scoreForm['exam_date'] ?? now(),
                    ];

                    if ($studentScore['existing_score_id']) {
                        // Update existing score
                        $score = OfflineExamScore::find($studentScore['existing_score_id']);
                        if ($score) {
                            $score->update($data);
                            $savedCount++;
                        }
                    } else {
                        // Create new score
                        OfflineExamScore::create($data);
                        $savedCount++;
                    }

                    // Update progress
                    $this->bulkProgress = round(($savedCount / $totalScores) * 100);
                    
                } catch (\Exception $e) {
                    Log::error('Error saving individual bulk score', [
                        'error' => $e->getMessage(),
                        'student_id' => $studentScore['student_id'],
                        'exam_id' => $this->selectedExamId
                    ]);
                    $errorCount++;
                }
            }

            $message = "Bulk save completed. {$savedCount} scores saved";
            if ($errorCount > 0) {
                $message .= ", {$errorCount} errors occurred";
            }

            Log::info('Bulk offline exam scores saved', [
                'exam_id' => $this->selectedExamId,
                'saved_count' => $savedCount,
                'error_count' => $errorCount,
                'saved_by' => Auth::id()
            ]);

            $this->dispatch('notify', [
                'type' => $errorCount > 0 ? 'warning' : 'success',
                'message' => $message,
                'timer' => 5000
            ]);

            $this->resetBulkEntry();
            $this->bulkEntry = false;

        } catch (\Exception $e) {
            Log::error('Error saving bulk offline exam scores', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'exam_id' => $this->selectedExamId
            ]);

            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error saving bulk scores: ' . $e->getMessage(),
                'timer' => 5000
            ]);
        } finally {
            $this->isBulkSaving = false;
            $this->bulkProgress = 0;
        }
    }

    public function deleteScore($scoreId)
    {
        try {
            $score = OfflineExamScore::findOrFail($scoreId);
            $score->delete();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Score deleted successfully.',
                'timer' => 3000
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting offline exam score', [
                'error' => $e->getMessage(),
                'score_id' => $scoreId
            ]);

            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error deleting score.',
                'timer' => 3000
            ]);
        }
    }

    public function resetForm()
    {
        $this->scoreForm = [
            'student_id' => null,
            'score' => null,
            'total_marks' => 100,
            'remarks' => '',
            'exam_date' => now()->format('Y-m-d'),
        ];
        $this->resetValidation();
    }

    public function resetBulkEntry()
    {
        $this->studentScores = [];
    }

    public function exportScores()
    {
        if (!$this->selectedExamId) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Please select an exam to export scores.',
                'timer' => 3000
            ]);
            return;
        }

        try {
            $exam = OfflineExam::with('course')->find($this->selectedExamId);
            $examName = $exam->title ?? 'Offline Exam';
            $courseName = $exam->course->name ?? 'Unknown Course';
            
            $filename = str_replace([' ', '/'], ['_', '-'], $examName . '_' . $courseName) . '_scores_' . now()->format('Y-m-d') . '.xlsx';
            
            Log::info('Exporting offline exam scores', [
                'exam_id' => $this->selectedExamId,
                'class_id' => $this->selectedClassId,
                'exported_by' => Auth::id(),
                'filename' => $filename
            ]);

            return Excel::download(
                new OfflineExamScoresExport($this->selectedExamId, $this->selectedClassId),
                $filename
            );

        } catch (\Exception $e) {
            Log::error('Error exporting offline exam scores', [
                'error' => $e->getMessage(),
                'exam_id' => $this->selectedExamId,
                'class_id' => $this->selectedClassId
            ]);

            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Error exporting scores: ' . $e->getMessage(),
                'timer' => 5000
            ]);
        }
    }

    public function render()
    {
        $offlineExams = OfflineExam::with('course')
            ->when($this->selectedAcademicYearId, function($query) {
                $query->whereHas('course', function($q) {
                    $q->where('year_id', $this->selectedAcademicYearId);
                });
            })
            ->when($this->selectedSemesterId, function($query) {
                $query->whereHas('course', function($q) {
                    $q->where('semester_id', $this->selectedSemesterId);
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $collegeClasses = CollegeClass::orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('year', 'desc')->get();
        $semesters = Semester::orderBy('name')->get();

        $students = [];
        if ($this->selectedClassId && $this->showForm) {
            $students = Student::where('college_class_id', $this->selectedClassId)
                ->orderBy('student_id')
                ->get()
                ->mapWithKeys(function ($student) {
                    return [$student->id => $student->student_id . ' - ' . $student->full_name];
                });
        }

        $scores = collect();
        if ($this->selectedExamId) {
            $query = OfflineExamScore::with(['student.collegeClass', 'offlineExam.course', 'recordedBy'])
                ->where('offline_exam_id', $this->selectedExamId);

            if ($this->selectedClassId) {
                $query->whereHas('student', function($q) {
                    $q->where('college_class_id', $this->selectedClassId);
                });
            }

            if ($this->search) {
                $query->whereHas('student', function($q) {
                    $q->where('student_id', 'like', '%' . $this->search . '%')
                      ->orWhere('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%');
                });
            }

            $scores = $query->orderBy('created_at', 'desc')
                ->paginate($this->perPage);
        }

        return view('livewire.admin.offline-exam-scores', [
            'offlineExams' => $offlineExams,
            'collegeClasses' => $collegeClasses,
            'academicYears' => $academicYears,
            'semesters' => $semesters,
            'students' => $students,
            'scores' => $scores,
        ]);
    }
}
