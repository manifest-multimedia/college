<?php

namespace App\Livewire\Admin;

use App\Models\AcademicYear;
use App\Models\AssessmentScore;
use App\Models\CollegeClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class AssessmentScores extends Component
{
    use WithFileUploads;

    // Filter properties
    public $selectedCourseId = null;

    public $selectedClassId = null;

    public $selectedAcademicYearId = null;

    public $selectedSemesterId = null;

    // Weight configuration
    public $assignmentWeight = 20;

    public $midSemesterWeight = 20;

    public $endSemesterWeight = 60;

    public $assignmentCount = 3;

    // Scoresheet data
    public $studentScores = [];

    public $isLoaded = false;

    // UI state
    public $showWeightConfig = false;

    // Import/Export
    public $importFile = null;

    public function mount()
    {
        // Auto-select current academic year and semester if available
        $currentAcademicYear = AcademicYear::where('is_current', true)->first();
        $currentSemester = Semester::where('is_current', true)->first();

        if ($currentAcademicYear) {
            $this->selectedAcademicYearId = $currentAcademicYear->id;
        }

        if ($currentSemester) {
            $this->selectedSemesterId = $currentSemester->id;
        }
    }

    public function loadScoresheet()
    {
        $this->validate([
            'selectedCourseId' => 'required',
            'selectedClassId' => 'required',
            'selectedAcademicYearId' => 'required',
            'selectedSemesterId' => 'required',
        ], [
            'selectedCourseId.required' => 'Please select a course',
            'selectedClassId.required' => 'Please select a class',
            'selectedAcademicYearId.required' => 'Please select an academic year',
            'selectedSemesterId.required' => 'Please select a semester',
        ]);

        // Load students for the selected class
        $students = Student::where('college_class_id', $this->selectedClassId)
            ->orderBy('student_id')
            ->get();

        $this->studentScores = [];

        foreach ($students as $student) {
            // Check for existing score
            $existingScore = AssessmentScore::where([
                'course_id' => $this->selectedCourseId,
                'student_id' => $student->id,
                'academic_year_id' => $this->selectedAcademicYearId,
                'semester_id' => $this->selectedSemesterId,
            ])->first();

            $this->studentScores[] = [
                'student_id' => $student->id,
                'student_number' => $student->student_id,
                'student_name' => $student->name,
                'assignment_1' => $existingScore ? $existingScore->assignment_1_score : null,
                'assignment_2' => $existingScore ? $existingScore->assignment_2_score : null,
                'assignment_3' => $existingScore ? $existingScore->assignment_3_score : null,
                'mid_semester' => $existingScore ? $existingScore->mid_semester_score : null,
                'end_semester' => $existingScore ? $existingScore->end_semester_score : null,
                'total' => $existingScore ? $existingScore->total_score : 0,
                'grade' => $existingScore ? $existingScore->grade_letter : '',
                'existing_id' => $existingScore ? $existingScore->id : null,
            ];
        }

        $this->isLoaded = true;
        $this->dispatch('scoresheet-loaded');
        session()->flash('success', count($this->studentScores).' students loaded successfully');
    }

    public function saveScores()
    {
        $this->validate([
            'studentScores.*.assignment_1' => 'nullable|numeric|min:0|max:100',
            'studentScores.*.assignment_2' => 'nullable|numeric|min:0|max:100',
            'studentScores.*.assignment_3' => 'nullable|numeric|min:0|max:100',
            'studentScores.*.mid_semester' => 'nullable|numeric|min:0|max:100',
            'studentScores.*.end_semester' => 'nullable|numeric|min:0|max:100',
        ]);

        $savedCount = 0;
        $updatedCount = 0;

        foreach ($this->studentScores as $studentScore) {
            // Skip if no scores entered
            if (
                $studentScore['assignment_1'] === null &&
                $studentScore['assignment_2'] === null &&
                $studentScore['assignment_3'] === null &&
                $studentScore['mid_semester'] === null &&
                $studentScore['end_semester'] === null
            ) {
                continue;
            }

            $data = [
                'course_id' => $this->selectedCourseId,
                'student_id' => $studentScore['student_id'],
                'academic_year_id' => $this->selectedAcademicYearId,
                'semester_id' => $this->selectedSemesterId,
                'assignment_1_score' => $studentScore['assignment_1'],
                'assignment_2_score' => $studentScore['assignment_2'],
                'assignment_3_score' => $studentScore['assignment_3'],
                'mid_semester_score' => $studentScore['mid_semester'],
                'end_semester_score' => $studentScore['end_semester'],
                'assignment_weight' => $this->assignmentWeight,
                'mid_semester_weight' => $this->midSemesterWeight,
                'end_semester_weight' => $this->endSemesterWeight,
                'assignment_count' => $this->assignmentCount,
                'recorded_by' => Auth::id(),
            ];

            if ($studentScore['existing_id']) {
                AssessmentScore::find($studentScore['existing_id'])->update($data);
                $updatedCount++;
            } else {
                AssessmentScore::create($data);
                $savedCount++;
            }
        }

        // Reload scoresheet to show updated calculations
        $this->loadScoresheet();

        $message = 'Scores saved successfully! ';
        if ($savedCount > 0) {
            $message .= "$savedCount new records created. ";
        }
        if ($updatedCount > 0) {
            $message .= "$updatedCount records updated.";
        }

        session()->flash('success', $message);
    }

    public function calculateStudentTotal($index)
    {
        $student = $this->studentScores[$index];

        // Calculate assignment average
        $assignments = array_filter([
            $student['assignment_1'],
            $student['assignment_2'],
            $student['assignment_3'],
        ], fn ($score) => $score !== null);

        $assignmentAvg = count($assignments) > 0 ? array_sum($assignments) / count($assignments) : 0;
        $assignmentWeighted = $assignmentAvg * ($this->assignmentWeight / 100);

        // Calculate weighted scores
        $midSemWeighted = ($student['mid_semester'] ?? 0) * ($this->midSemesterWeight / 100);
        $endSemWeighted = ($student['end_semester'] ?? 0) * ($this->endSemesterWeight / 100);

        // Total
        $total = round($assignmentWeighted + $midSemWeighted + $endSemWeighted, 2);

        // Grade
        $grade = $this->determineGrade($total);

        $this->studentScores[$index]['total'] = $total;
        $this->studentScores[$index]['grade'] = $grade;
    }

    public function determineGrade($totalScore): string
    {
        if ($totalScore >= 80) {
            return 'A';
        }
        if ($totalScore >= 75) {
            return 'B+';
        }
        if ($totalScore >= 70) {
            return 'B';
        }
        if ($totalScore >= 65) {
            return 'C+';
        }
        if ($totalScore >= 60) {
            return 'C';
        }
        if ($totalScore >= 55) {
            return 'D+';
        }
        if ($totalScore >= 50) {
            return 'D';
        }

        return 'E';
    }

    public function recalculateAllScores()
    {
        foreach ($this->studentScores as $index => $student) {
            $this->calculateStudentTotal($index);
        }

        session()->flash('info', 'All scores recalculated with current weights');
    }

    public function toggleWeightConfig()
    {
        $this->showWeightConfig = ! $this->showWeightConfig;
    }

    public function saveWeightConfiguration()
    {
        $this->validate([
            'assignmentWeight' => 'required|numeric|min:0|max:100',
            'midSemesterWeight' => 'required|numeric|min:0|max:100',
            'endSemesterWeight' => 'required|numeric|min:0|max:100',
        ]);

        $totalWeight = $this->assignmentWeight + $this->midSemesterWeight + $this->endSemesterWeight;

        if ($totalWeight != 100) {
            session()->flash('error', "Weights must sum to 100%. Current total: {$totalWeight}%");

            return;
        }

        $this->showWeightConfig = false;
        $this->recalculateAllScores();
        session()->flash('success', 'Weight configuration saved and scores recalculated');
    }

    public function getPassingStudentsProperty()
    {
        return collect($this->studentScores)->filter(fn ($s) => ($s['total'] ?? 0) >= 50)->count();
    }

    public function getFailingStudentsProperty()
    {
        return collect($this->studentScores)->filter(fn ($s) => ($s['total'] ?? 0) < 50 && ($s['total'] ?? 0) > 0)->count();
    }

    public function getClassAverageProperty()
    {
        $totals = collect($this->studentScores)->pluck('total')->filter(fn ($t) => $t > 0);

        return $totals->count() > 0 ? round($totals->average(), 2) : 0;
    }

    public function render()
    {
        $courses = Subject::orderBy('name')->get();
        $collegeClasses = CollegeClass::orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        $semesters = Semester::orderBy('name')->get();

        return view('livewire.admin.assessment-scores', [
            'courses' => $courses,
            'collegeClasses' => $collegeClasses,
            'academicYears' => $academicYears,
            'semesters' => $semesters,
        ]);
    }
}
