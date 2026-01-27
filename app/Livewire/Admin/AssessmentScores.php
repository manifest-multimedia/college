<?php

namespace App\Livewire\Admin;

use App\Exports\AssessmentScoresExport;
use App\Exports\AssessmentScoresTemplateExport;
use App\Imports\AssessmentScoresImport;
use App\Models\AcademicYear;
use App\Models\AssessmentScore;
use App\Models\Cohort;
use App\Models\CollegeClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class AssessmentScores extends Component
{
    use WithFileUploads;

    // Filter properties
    public $selectedCourseId = null;

    public $selectedClassId = null;

    public $selectedCohortId = null;

    public $selectedSemesterId = null;

    public $selectedAcademicYear = null;

    public $academicYears = [];

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

    public $importPreviewData = [];

    public $importSummary = [];

    public $importErrors = [];

    public $showImportPreview = false;

    public function mount()
    {
        // Fetch distinct academic years from cohorts
        $this->academicYears = AcademicYear::query()
            ->select('name')
            ->distinct()
            ->pluck('name');

        // Auto-select current cohort and semester if available
        $currentCohort = Cohort::where('is_active', true)->first();
        $currentSemester = Semester::where('is_current', true)->first();

        if ($currentCohort) {
            $this->selectedCohortId = $currentCohort->id;
            $this->selectedAcademicYear = $currentCohort->academic_year;
        }

        if ($currentSemester) {
            $this->selectedSemesterId = $currentSemester->id;
        }

        // Load default weights from system settings
        $this->loadDefaultWeights();
    }

    public function updatedSelectedClassId(): void
    {
        $this->selectedCourseId = null;
        $this->studentScores = [];
        $this->isLoaded = false;
        $this->importPreviewData = [];
        $this->importSummary = [];
        $this->importErrors = [];
        $this->showImportPreview = false;
        $this->importFile = null;
    }

    protected function loadDefaultWeights()
    {
        $settings = DB::table('system_settings')
            ->whereIn('key', ['default_assignment_weight', 'default_mid_semester_weight', 'default_end_semester_weight'])
            ->get()
            ->keyBy('key');

        $this->assignmentWeight = $settings->get('default_assignment_weight')->value ?? 20;
        $this->midSemesterWeight = $settings->get('default_mid_semester_weight')->value ?? 20;
        $this->endSemesterWeight = $settings->get('default_end_semester_weight')->value ?? 60;
    }

    public function loadScoresheet()
    {
        $this->validate([
            'selectedClassId' => 'required|exists:college_classes,id',
            'selectedCourseId' => [
                'required',
                Rule::exists('subjects', 'id')->where(fn ($query) => $query->where('college_class_id', $this->selectedClassId)),
            ],
            'selectedCohortId' => 'required',
            'selectedSemesterId' => 'required',
        ], [
            'selectedCourseId.required' => 'Please select a course',
            'selectedClassId.required' => 'Please select a program',
            'selectedCourseId.exists' => 'Selected course does not belong to the chosen program.',
            'selectedCohortId.required' => 'Please select a cohort',
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
                'cohort_id' => $this->selectedCohortId,
                'semester_id' => $this->selectedSemesterId,
            ])->first();

            // If existing score has different assignment count, update our component's assignment count
            if ($existingScore && $existingScore->assignment_count) {
                $this->assignmentCount = max($this->assignmentCount, $existingScore->assignment_count);
            }

            $studentData = [
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

            // Add assignment 4 and 5 if they exist or if assignment count requires them
            if ($this->assignmentCount >= 4) {
                $studentData['assignment_4'] = $existingScore ? $existingScore->assignment_4_score : null;
            }
            if ($this->assignmentCount >= 5) {
                $studentData['assignment_5'] = $existingScore ? $existingScore->assignment_5_score : null;
            }

            $this->studentScores[] = $studentData;
        }

        $this->isLoaded = true;
        $this->dispatch('scoresheet-loaded');
        session()->flash('success', count($this->studentScores).' students loaded successfully');
    }

    public function saveScores()
    {
        $validationRules = [
            'studentScores.*.assignment_1' => 'nullable|numeric|min:0|max:100',
            'studentScores.*.assignment_2' => 'nullable|numeric|min:0|max:100',
            'studentScores.*.assignment_3' => 'nullable|numeric|min:0|max:100',
            'studentScores.*.mid_semester' => 'nullable|numeric|min:0|max:100',
            'studentScores.*.end_semester' => 'nullable|numeric|min:0|max:100',
        ];

        // Add validation for assignments 4 and 5 if they're active
        if ($this->assignmentCount >= 4) {
            $validationRules['studentScores.*.assignment_4'] = 'nullable|numeric|min:0|max:100';
        }
        if ($this->assignmentCount >= 5) {
            $validationRules['studentScores.*.assignment_5'] = 'nullable|numeric|min:0|max:100';
        }

        $this->validate($validationRules);

        $savedCount = 0;
        $updatedCount = 0;

        foreach ($this->studentScores as $index => $studentScore) {
            // Skip if no scores entered
            if (
                $studentScore['assignment_1'] === null &&
                $studentScore['assignment_2'] === null &&
                $studentScore['assignment_3'] === null &&
                ($this->assignmentCount < 4 || $studentScore['assignment_4'] === null) &&
                ($this->assignmentCount < 5 || $studentScore['assignment_5'] === null) &&
                $studentScore['mid_semester'] === null &&
                $studentScore['end_semester'] === null
            ) {
                continue;
            }

            // Convert empty strings to NULL for decimal fields
            $data = [
                'course_id' => $this->selectedCourseId,
                'student_id' => $studentScore['student_id'],
                'cohort_id' => $this->selectedCohortId,
                'semester_id' => $this->selectedSemesterId,
                'assignment_1_score' => $studentScore['assignment_1'] === '' ? null : $studentScore['assignment_1'],
                'assignment_2_score' => $studentScore['assignment_2'] === '' ? null : $studentScore['assignment_2'],
                'assignment_3_score' => $studentScore['assignment_3'] === '' ? null : $studentScore['assignment_3'],
                'assignment_4_score' => ($this->assignmentCount >= 4 && isset($studentScore['assignment_4'])) ? ($studentScore['assignment_4'] === '' ? null : $studentScore['assignment_4']) : null,
                'assignment_5_score' => ($this->assignmentCount >= 5 && isset($studentScore['assignment_5'])) ? ($studentScore['assignment_5'] === '' ? null : $studentScore['assignment_5']) : null,
                'mid_semester_score' => $studentScore['mid_semester'] === '' ? null : $studentScore['mid_semester'],
                'end_semester_score' => $studentScore['end_semester'] === '' ? null : $studentScore['end_semester'],
                'assignment_weight' => $this->assignmentWeight,
                'mid_semester_weight' => $this->midSemesterWeight,
                'end_semester_weight' => $this->endSemesterWeight,
                'assignment_count' => $this->assignmentCount,
                'recorded_by' => Auth::id(),
            ];

            if ($studentScore['existing_id']) {
                $saved = AssessmentScore::find($studentScore['existing_id']);
                $saved->update($data);
                // Update the display with calculated values from model
                $this->studentScores[$index]['total'] = $saved->fresh()->total_score;
                $this->studentScores[$index]['grade'] = $saved->fresh()->grade_letter;
                $updatedCount++;
            } else {
                $saved = AssessmentScore::create($data);
                // Update the display with calculated values from model
                $this->studentScores[$index]['existing_id'] = $saved->id;
                $this->studentScores[$index]['total'] = $saved->total_score;
                $this->studentScores[$index]['grade'] = $saved->grade_letter;
                $savedCount++;
            }
        }

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

        // Calculate assignment average - dynamically include all active assignments
        $assignments = [];
        for ($i = 1; $i <= $this->assignmentCount; $i++) {
            $key = "assignment_{$i}";
            if (isset($student[$key]) && $student[$key] !== null) {
                $assignments[] = $student[$key];
            }
        }

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

    public function addAssignmentColumn()
    {
        if ($this->assignmentCount >= 5) {
            session()->flash('error', 'Maximum 5 assignments allowed');

            return;
        }

        $this->assignmentCount++;

        // Initialize new assignment column for all students
        foreach ($this->studentScores as $index => $student) {
            $this->studentScores[$index]["assignment_{$this->assignmentCount}"] = null;
        }

        session()->flash('info', "Assignment {$this->assignmentCount} column added");
    }

    public function removeAssignmentColumn()
    {
        if ($this->assignmentCount <= 3) {
            session()->flash('error', 'Minimum 3 assignments required');

            return;
        }

        // Remove the last assignment column data
        foreach ($this->studentScores as $index => $student) {
            unset($this->studentScores[$index]["assignment_{$this->assignmentCount}"]);
        }

        $this->assignmentCount--;

        // Recalculate all scores after column removal
        $this->recalculateAllScores();

        session()->flash('info', 'Assignment column removed and scores recalculated');
    }

    public function downloadExcelTemplate()
    {
        $this->validate([
            'selectedClassId' => 'required|exists:college_classes,id',
            'selectedCourseId' => [
                'required',
                Rule::exists('subjects', 'id')->where(fn ($query) => $query->where('college_class_id', $this->selectedClassId)),
            ],
            'selectedCohortId' => 'required',
            'selectedSemesterId' => 'required',
        ]);

        $students = Student::where('college_class_id', $this->selectedClassId)
            ->orderBy('student_id')
            ->get();

        $course = Subject::find($this->selectedCourseId);
        $class = CollegeClass::find($this->selectedClassId);
        $cohort = Cohort::find($this->selectedCohortId);
        $semester = Semester::find($this->selectedSemesterId);

        $courseInfo = [
            'course' => $course->name,
            'programme' => $class->name,
            'cohort' => $cohort->name,
            'semester' => $semester->name,
            'academic_year' => $academicYear->name,
        ];

        $weights = [
            'assignment' => $this->assignmentWeight,
            'mid_semester' => $this->midSemesterWeight,
            'end_semester' => $this->endSemesterWeight,
        ];

        $filename = 'assessment_scores_template_'.str_replace(' ', '_', $course->name).'_'.now()->format('Y-m-d').'.xlsx';

        return Excel::download(new AssessmentScoresTemplateExport($students, $courseInfo, $weights), $filename);
    }

    public function importFromExcel()
    {
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls|max:10240',
            'selectedClassId' => 'required|exists:college_classes,id',
            'selectedCourseId' => [
                'required',
                Rule::exists('subjects', 'id')->where(fn ($query) => $query->where('college_class_id', $this->selectedClassId)),
            ],
            'selectedCohortId' => 'required',
            'selectedSemesterId' => 'required',
        ]);

        try {
            $import = new AssessmentScoresImport(
                $this->selectedCourseId,
                $this->selectedCohortId,
                $this->selectedSemesterId,
                Auth::id()
            );

            Excel::import($import, $this->importFile->getRealPath());

            $this->importPreviewData = $import->getValidatedData();
            $this->importSummary = $import->getSummary();
            $this->importErrors = $import->getErrors();

            if ($import->hasErrors()) {
                session()->flash('error', 'Import validation failed. Please review errors below.');
                $this->showImportPreview = false;
            } else {
                $this->showImportPreview = true;
                session()->flash('info', 'Import preview ready. Please review and confirm.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Import failed: '.$e->getMessage());
        }
    }

    public function confirmImport()
    {
        if (empty($this->importPreviewData)) {
            session()->flash('error', 'No data to import');

            return;
        }

        $savedCount = 0;
        $updatedCount = 0;

        foreach ($this->importPreviewData as $data) {
            $scoreData = [
                'course_id' => $this->selectedCourseId,
                'student_id' => $data['student_id'],
                'academic_year_id' => $this->selectedAcademicYearId,
                'semester_id' => $this->selectedSemesterId,
                'assignment_1_score' => $data['assignment_1'] === '' ? null : $data['assignment_1'],
                'assignment_2_score' => $data['assignment_2'] === '' ? null : $data['assignment_2'],
                'assignment_3_score' => $data['assignment_3'] === '' ? null : $data['assignment_3'],
                'mid_semester_score' => $data['mid_semester'] === '' ? null : $data['mid_semester'],
                'end_semester_score' => $data['end_semester'] === '' ? null : $data['end_semester'],
                'assignment_weight' => $this->assignmentWeight,
                'mid_semester_weight' => $this->midSemesterWeight,
                'end_semester_weight' => $this->endSemesterWeight,
                'recorded_by' => Auth::id(),
            ];

            if ($data['existing_id']) {
                AssessmentScore::find($data['existing_id'])->update($scoreData);
                $updatedCount++;
            } else {
                AssessmentScore::create($scoreData);
                $savedCount++;
            }
        }

        // Clear import data
        $this->importPreviewData = [];
        $this->importSummary = [];
        $this->importErrors = [];
        $this->showImportPreview = false;
        $this->importFile = null;

        // Reload scoresheet
        $this->loadScoresheet();

        session()->flash('success', "Import completed: {$savedCount} new records, {$updatedCount} updated records");
    }

    public function cancelImport()
    {
        $this->importPreviewData = [];
        $this->importSummary = [];
        $this->importErrors = [];
        $this->showImportPreview = false;
        $this->importFile = null;
    }

    public function exportToExcel()
    {
        if (empty($this->studentScores)) {
            session()->flash('error', 'No scores to export. Please load a scoresheet first.');

            return;
        }

        $course = Subject::find($this->selectedCourseId);
        $class = CollegeClass::find($this->selectedClassId);
        $cohort = Cohort::find($this->selectedCohortId);
        $semester = Semester::find($this->selectedSemesterId);

        $courseInfo = [
            'course' => $course->name,
            'programme' => $class->name,
            'cohort' => $cohort->name,
            'semester' => $semester->name,
        ];

        $weights = [
            'assignment' => $this->assignmentWeight,
            'mid_semester' => $this->midSemesterWeight,
            'end_semester' => $this->endSemesterWeight,
        ];

        // Calculate weighted scores for export
        $exportScores = collect($this->studentScores)->map(function ($score) {
            // Calculate assignment average
            $assignments = array_filter([
                $score['assignment_1'] ?? null,
                $score['assignment_2'] ?? null,
                $score['assignment_3'] ?? null,
            ], fn ($val) => $val !== null);

            $assignmentAverage = count($assignments) > 0 ? round(array_sum($assignments) / count($assignments), 2) : null;

            // Calculate weighted scores
            $assignmentWeighted = $assignmentAverage ? round($assignmentAverage * ($this->assignmentWeight / 100), 2) : null;
            $midWeighted = isset($score['mid_semester']) ? round($score['mid_semester'] * ($this->midSemesterWeight / 100), 2) : null;
            $endWeighted = isset($score['end_semester']) ? round($score['end_semester'] * ($this->endSemesterWeight / 100), 2) : null;

            return array_merge($score, [
                'assignment_average' => $assignmentAverage,
                'assignment_weighted' => $assignmentWeighted,
                'mid_weighted' => $midWeighted,
                'end_weighted' => $endWeighted,
            ]);
        });

        $filename = 'assessment_scores_'.str_replace(' ', '_', $course->name).'_'.now()->format('Y-m-d').'.xlsx';

        return Excel::download(new AssessmentScoresExport($exportScores, $courseInfo, $weights), $filename);
    }

    public function render()
    {
        $courses = Subject::query()
            ->when($this->selectedClassId, fn ($query) => $query->where('college_class_id', $this->selectedClassId))
            ->orderBy('name')
            ->get();
        $collegeClasses = CollegeClass::orderBy('name')->get();
        $cohorts = Cohort::where('is_active', true)->orderBy('name', 'desc')->get();
        $semesters = Semester::orderBy('name')->get();

        return view('livewire.admin.assessment-scores', [
            'courses' => $courses,
            'collegeClasses' => $collegeClasses,
            'cohorts' => $cohorts,
            'semesters' => $semesters,
        ]);
    }
}
