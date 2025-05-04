<?php

namespace App\Http\Controllers;

use App\Models\CollegeClass;
use App\Models\Semester;
use App\Models\Course;
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
     * Display a listing of college classes.
     */
    public function index()
    {
        // Default to current semester if set
        $currentSemester = $this->academicsService->getCurrentSemester();
        
        $classes = CollegeClass::when($currentSemester, function ($query) use ($currentSemester) {
                $query->where('semester_id', $currentSemester->id);
            })
            ->with(['semester.academicYear', 'course', 'instructor'])
            ->paginate(10);
        
        return view('academics.classes.index', compact('classes', 'currentSemester'));
    }

    /**
     * Show the form for creating a new college class.
     */
    public function create()
    {
        $semesters = Semester::with('academicYear')->get();
        $courses = Course::all();
        $instructors = User::whereHas('roles', function ($query) {
            $query->where('name', 'instructor');
        })->get();
        
        return view('academics.classes.create', compact('semesters', 'courses', 'instructors'));
    }

    /**
     * Store a newly created college class in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'semester_id' => 'required|exists:semesters,id',
            'course_id' => 'required|exists:courses,id',
            'instructor_id' => 'nullable|exists:users,id',
        ]);
        
        $collegeClass = CollegeClass::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'slug' => Str::slug($validated['name']),
            'semester_id' => $validated['semester_id'],
            'course_id' => $validated['course_id'],
            'instructor_id' => $validated['instructor_id'],
            'is_active' => true,
            'is_deleted' => false,
            'created_by' => auth()->id(),
        ]);
        
        return redirect()->route('academics.classes.index')
            ->with('success', 'College class created successfully.');
    }

    /**
     * Display the specified college class.
     */
    public function show(CollegeClass $class)
    {
        $class->load(['semester.academicYear', 'course', 'instructor', 'students']);
        
        // Get student grades for this class
        $studentGrades = $class->studentGrades()->with(['student', 'grade'])->get();
        
        return view('academics.classes.show', compact('class', 'studentGrades'));
    }

    /**
     * Show the form for editing the specified college class.
     */
    public function edit(CollegeClass $class)
    {
        $semesters = Semester::with('academicYear')->get();
        $courses = Course::all();
        $instructors = User::whereHas('roles', function ($query) {
            $query->where('name', 'instructor');
        })->get();
        
        return view('academics.classes.edit', compact('class', 'semesters', 'courses', 'instructors'));
    }

    /**
     * Update the specified college class in storage.
     */
    public function update(Request $request, CollegeClass $class)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'semester_id' => 'required|exists:semesters,id',
            'course_id' => 'required|exists:courses,id',
            'instructor_id' => 'nullable|exists:users,id',
        ]);
        
        $class->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'slug' => Str::slug($validated['name']),
            'semester_id' => $validated['semester_id'],
            'course_id' => $validated['course_id'],
            'instructor_id' => $validated['instructor_id'],
        ]);
        
        return redirect()->route('academics.classes.index')
            ->with('success', 'College class updated successfully.');
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
            ->with(['semester.academicYear', 'course', 'instructor'])
            ->paginate(10);
        
        $currentSemester = Semester::find($semesterId);
        
        return view('academics.classes.index', compact('classes', 'currentSemester'));
    }
}