<?php

namespace App\Http\Controllers;

use App\Models\StudentGrade;
use App\Models\Student;
use App\Models\CollegeClass;
use App\Models\Grade;
use App\Models\Semester;
use App\Services\AcademicsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StudentGradeController extends Controller
{
    protected $academicsService;
    
    /**
     * Constructor
     */
    public function __construct(AcademicsService $academicsService)
    {
        $this->middleware(['auth', 'permission:manage-academics']);
        $this->academicsService = $academicsService;
    }
    
    /**
     * Display a listing of student grades.
     */
    public function index()
    {
        // Default to current semester if set
        $currentSemester = $this->academicsService->getCurrentSemester();
        $semesters = Semester::with('academicYear')->get();
        
        $grades = StudentGrade::whereHas('collegeClass', function ($query) use ($currentSemester) {
                if ($currentSemester) {
                    $query->where('semester_id', $currentSemester->id);
                }
            })
            ->with(['student', 'collegeClass.course', 'grade', 'gradedBy'])
            ->paginate(15);
        
        return view('academics.student-grades.index', compact('grades', 'semesters', 'currentSemester'));
    }

    /**
     * Show the form for creating a new student grade.
     */
    public function create()
    {
        // Default to current semester if set
        $currentSemester = $this->academicsService->getCurrentSemester();
        
        $students = Student::where('status', 'active')->get();
        $collegeClasses = CollegeClass::when($currentSemester, function ($query) use ($currentSemester) {
                $query->where('semester_id', $currentSemester->id);
            })
            ->with(['course', 'semester'])
            ->get();
        $gradeTypes = Grade::all();
        
        return view('academics.student-grades.create', compact('students', 'collegeClasses', 'gradeTypes', 'currentSemester'));
    }

    /**
     * Store a newly created student grade in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'college_class_id' => 'required|exists:college_classes,id',
            'grade_id' => 'required|exists:grades,id',
            'comments' => 'nullable|string',
        ]);
        
        try {
            // Use the service to assign the grade
            $studentGrade = $this->academicsService->assignStudentGrade(
                $validated['student_id'],
                $validated['college_class_id'],
                $validated['grade_id'],
                $validated['comments'],
                auth()->id()
            );
            
            // Log the grade assignment
            Log::channel('academics')->info('Student grade assigned', [
                'student_id' => $validated['student_id'],
                'class_id' => $validated['college_class_id'],
                'grade_id' => $validated['grade_id'],
                'assigned_by' => auth()->id(),
            ]);
            
            return redirect()->route('academics.student-grades.index')
                ->with('success', 'Student grade assigned successfully.');
        } catch (\Exception $e) {
            Log::error('Error assigning student grade: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Error assigning student grade: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified student grade.
     */
    public function show(StudentGrade $studentGrade)
    {
        $studentGrade->load(['student', 'collegeClass.course', 'collegeClass.semester', 'grade', 'gradedBy']);
        
        return view('academics.student-grades.show', compact('studentGrade'));
    }

    /**
     * Show the form for editing the specified student grade.
     */
    public function edit(StudentGrade $studentGrade)
    {
        $students = Student::where('status', 'active')->get();
        $collegeClasses = CollegeClass::with(['course', 'semester'])->get();
        $gradeTypes = Grade::all();
        
        return view('academics.student-grades.edit', compact('studentGrade', 'students', 'collegeClasses', 'gradeTypes'));
    }

    /**
     * Update the specified student grade in storage.
     */
    public function update(Request $request, StudentGrade $studentGrade)
    {
        $validated = $request->validate([
            'grade_id' => 'required|exists:grades,id',
            'comments' => 'nullable|string',
        ]);
        
        try {
            $studentGrade->update([
                'grade_id' => $validated['grade_id'],
                'comments' => $validated['comments'],
                'graded_by' => auth()->id(),
            ]);
            
            // Log the grade update
            Log::channel('academics')->info('Student grade updated', [
                'student_id' => $studentGrade->student_id,
                'class_id' => $studentGrade->college_class_id,
                'grade_id' => $validated['grade_id'],
                'updated_by' => auth()->id(),
            ]);
            
            return redirect()->route('academics.student-grades.index')
                ->with('success', 'Student grade updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating student grade: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Error updating student grade: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified student grade from storage.
     */
    public function destroy(StudentGrade $studentGrade)
    {
        try {
            // Log the grade deletion
            Log::channel('academics')->info('Student grade deleted', [
                'student_id' => $studentGrade->student_id,
                'class_id' => $studentGrade->college_class_id,
                'grade_id' => $studentGrade->grade_id,
                'deleted_by' => auth()->id(),
            ]);
            
            $studentGrade->delete();
            
            return redirect()->route('academics.student-grades.index')
                ->with('success', 'Student grade deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting student grade: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Error deleting student grade: ' . $e->getMessage());
        }
    }
    
    /**
     * Filter grades by semester
     */
    public function filter(Request $request)
    {
        $semesterId = $request->semester_id;
        $semesters = Semester::with('academicYear')->get();
        $currentSemester = Semester::find($semesterId);
        
        $grades = StudentGrade::whereHas('collegeClass', function ($query) use ($semesterId) {
                $query->where('semester_id', $semesterId);
            })
            ->with(['student', 'collegeClass.course', 'grade', 'gradedBy'])
            ->paginate(15);
        
        return view('academics.student-grades.index', compact('grades', 'semesters', 'currentSemester'));
    }
    
    /**
     * Show the batch grading form for a specific class
     */
    public function batchCreate(CollegeClass $collegeClass)
    {
        $students = $collegeClass->students;
        $gradeTypes = Grade::all();
        
        return view('academics.student-grades.batch-create', compact('collegeClass', 'students', 'gradeTypes'));
    }
    
    /**
     * Process batch grading for a specific class
     */
    public function batchStore(Request $request, CollegeClass $collegeClass)
    {
        $validated = $request->validate([
            'grades' => 'required|array',
            'grades.*' => 'required|exists:grades,id',
            'comments' => 'nullable|array',
        ]);
        
        try {
            $count = 0;
            
            foreach ($validated['grades'] as $studentId => $gradeId) {
                // Skip if no grade is selected
                if (!$gradeId) {
                    continue;
                }
                
                // Get comment if available
                $comment = $validated['comments'][$studentId] ?? null;
                
                // Assign grade
                $this->academicsService->assignStudentGrade(
                    $studentId,
                    $collegeClass->id,
                    $gradeId,
                    $comment,
                    auth()->id()
                );
                
                $count++;
            }
            
            // Log the batch grade assignment
            Log::channel('academics')->info('Batch student grades assigned', [
                'class_id' => $collegeClass->id,
                'count' => $count,
                'assigned_by' => auth()->id(),
            ]);
            
            return redirect()->route('academics.classes.show', $collegeClass)
                ->with('success', $count . ' student grades assigned successfully.');
        } catch (\Exception $e) {
            Log::error('Error assigning batch student grades: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Error assigning batch student grades: ' . $e->getMessage())
                ->withInput();
        }
    }
}