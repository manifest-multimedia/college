<?php

namespace App\Http\Controllers;

use App\Models\CollegeClass;
use App\Models\Semester;
use App\Models\User;
use App\Services\AcademicsService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CollegeClassController extends Controller
{
    protected $academicsService;

    /**
     * Constructor
     */
    public function __construct(AcademicsService $academicsService)
    {
        // $this->middleware(['auth', 'permission:manage-academics']);
        $this->academicsService = $academicsService;
    }

    /**
     * Display a listing of college programs.
     */
    public function index()
    {
        // Programs are semester-independent, show all active programs
        $classes = CollegeClass::where('is_deleted', false)
            ->orderBy('name')
            ->paginate(10);

        return view('academics.classes.index', compact('classes'));
    }

    /**
     * Show the form for creating a new college program.
     */
    public function create()
    {
        // Programs are semester-independent and don't require instructor assignment
        return view('academics.classes.create');
    }

    /**
     * Store a newly created college program in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:10|alpha_num',
            'description' => 'nullable|string',
        ]);

        $collegeClass = CollegeClass::create([
            'name' => $validated['name'],
            'short_name' => $validated['short_name'],
            'description' => $validated['description'],
            'slug' => Str::slug($validated['name']),
            'is_active' => true,
            'is_deleted' => false,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('academics.classes.index')
            ->with('success', 'Academic program created successfully.');
    }

    /**
     * Display the specified college program.
     */
    public function show(CollegeClass $class)
    {
        $class->load(['students']);

        // Get student grades for this program
        $studentGrades = $class->studentGrades()->with(['student', 'grade'])->get();

        return view('academics.classes.show', compact('class', 'studentGrades'));
    }

    /**
     * Show the form for editing the specified college program.
     */
    public function edit(CollegeClass $class)
    {
        // Programs are semester-independent and don't require instructor assignment
        return view('academics.classes.edit', compact('class'));
    }

    /**
     * Update the specified college program in storage.
     */
    public function update(Request $request, CollegeClass $class)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:10|alpha_num',
            'description' => 'nullable|string',
        ]);

        $class->update([
            'name' => $validated['name'],
            'short_name' => $validated['short_name'],
            'description' => $validated['description'],
            'slug' => Str::slug($validated['name']),
        ]);

        return redirect()->route('academics.classes.index')
            ->with('success', 'Academic program updated successfully.');
    }

    /**
     * Remove the specified college class from storage.
     */
    public function destroy(CollegeClass $class)
    {
        // Check if the class has any student grades
        if ($class->studentGrades()->count() > 0) {
            return redirect()->route('academics.classes.index')
                ->with('error', 'Cannot delete college class with associated student grades.');
        }

        // Check if the class has any students assigned to it
        if ($class->students()->count() > 0) {
            return redirect()->route('academics.classes.index')
                ->with('error', 'Cannot delete college class with assigned students.');
        }

        $class->delete();

        return redirect()->route('academics.classes.index')
            ->with('success', 'College class deleted successfully.');
    }

    /**
     * Filter classes by semester
     */
    public function filter(Request $request)
    {
        $semesterId = $request->semester_id;

        $classes = CollegeClass::where('semester_id', $semesterId)
            ->with(['semester.academicYear', 'instructor'])
            ->paginate(10);

        $currentSemester = Semester::find($semesterId);

        return view('academics.classes.index', compact('classes', 'currentSemester'));
    }

    /**
     * Add students to a program
     */
    public function addStudents(Request $request, CollegeClass $class)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id'
        ]);

        // Use direct foreign key assignment since students belong to programs directly
        foreach ($request->student_ids as $studentId) {
            $student = \App\Models\Student::find($studentId);
            if ($student && $student->college_class_id != $class->id) {
                $student->update(['college_class_id' => $class->id]);
            }
        }

        return redirect()->route('academics.classes.show', $class)
            ->with('success', 'Students added to program successfully.');
    }

    /**
     * Remove a student from a program
     */
    public function removeStudent(CollegeClass $class, $studentId)
    {
        $student = \App\Models\Student::find($studentId);
        if ($student && $student->college_class_id == $class->id) {
            $student->update(['college_class_id' => null]);
        }

        return redirect()->route('academics.classes.show', $class)
            ->with('success', 'Student removed from program successfully.');
    }
}
