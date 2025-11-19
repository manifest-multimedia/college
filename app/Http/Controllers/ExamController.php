<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\CollegeClass;
use App\Models\Exam;
use App\Models\QuestionSet;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\User;
use App\Models\Year;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ExamController extends Controller
{
    /**
     * Show the exam creation form
     */
    public function create()
    {
        $classes = CollegeClass::all();
        $years = Year::all();
        $semesters = Semester::all();
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();

        // Get staff users using Spatie's role system
        $staffUsers = User::whereHas('roles', function ($query) {
            $query->whereNotIn('name', ['Student', 'Parent']);
        })
            ->orWhere(function ($query) {
                $query->whereNotIn('role', ['Student', 'Parent'])
                    ->whereDoesntHave('roles');
            })
            ->orderBy('name', 'asc')
            ->get();

        return view('exams.create-ajax', compact('classes', 'years', 'semesters', 'academicYears', 'staffUsers'));
    }

    /**
     * Get courses based on filters via AJAX
     */
    public function getCourses(Request $request)
    {
        try {
            $query = Subject::query();

            if ($request->class_id) {
                $query->where('college_class_id', $request->class_id);
            }
            if ($request->year_id) {
                $query->where('year_id', $request->year_id);
            }
            if ($request->semester_id) {
                $query->where('semester_id', $request->semester_id);
            }

            $courses = $query->orderBy('name', 'asc')->get(['id', 'name', 'course_code']);

            return response()->json([
                'success' => true,
                'courses' => $courses,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getCourses: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to load courses',
                'courses' => [],
            ], 500);
        }
    }

    /**
     * Get question sets for a course via AJAX
     */
    public function getQuestionSets(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:subjects,id',
        ]);

        $questionSets = QuestionSet::where('course_id', $request->course_id)
            ->withCount('questions')
            ->get(['id', 'name', 'description']);

        return response()->json([
            'success' => true,
            'question_sets' => $questionSets,
        ]);
    }

    /**
     * Store a new exam
     */
    public function store(Request $request)
    {
        $request->validate([
            'class' => 'required|exists:college_classes,id',
            'year' => 'required|exists:years,id',
            'semester' => 'required|exists:semesters,id',
            'course_code' => 'required|exists:subjects,id',
            'exam_title' => 'required|string|max:255',
            'exam_description' => 'nullable|string',
            'exam_type' => 'required|in:mcq,short_answer,essay,mixed',
            'exam_duration' => 'required|integer|min:1',
            'questions_per_session' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'exam_password' => 'required|string',
            'passing_mark' => 'nullable|integer|min:0|max:100',
            'enable_proctoring' => 'boolean',
            'user_id' => 'nullable|exists:users,id',
            'selected_question_sets' => 'required|array|min:1',
            'selected_question_sets.*' => 'exists:question_sets,id',
            'question_set_configs' => 'required|array',
            'question_set_configs.*.questions_to_pick' => 'nullable|integer|min:1',
            'question_set_configs.*.shuffle_questions' => 'boolean',
        ], [
            'selected_question_sets.required' => 'Please select at least one question set.',
            'selected_question_sets.min' => 'Please select at least one question set.',
        ]);

        try {
            DB::beginTransaction();

            // Generate unique slug
            $slug = Str::slug('exam-'.now()->format('Y-m-d-H-i-s'));

            while (Exam::where('slug', $slug)->exists()) {
                $slug = Str::slug('exam-'.now()->format('Y-m-d-H-i-s').'-'.Str::random(4));
            }

            // Create the exam with correct database columns
            $exam = Exam::create([
                'course_id' => $request->course_code,
                'user_id' => $request->user_id ?: Auth::id(),
                'type' => $request->exam_type,
                'type_id' => null,
                'duration' => $request->exam_duration,
                'password' => $request->exam_password,
                'questions_per_session' => $request->questions_per_session,
                'slug' => $slug,
                'status' => 'upcoming', // Use valid enum value instead of 'draft'
                'passing_percentage' => $request->passing_mark ?: 50.00,
                'clearance_threshold' => (int) ($request->passing_mark ?: 50),
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ]);

            // Attach question sets with their configurations
            foreach ($request->selected_question_sets as $setId) {
                $config = $request->question_set_configs[$setId] ?? [];
                $exam->questionSets()->attach($setId, [
                    'shuffle_questions' => $config['shuffle_questions'] ?? false,
                    'questions_to_pick' => $config['questions_to_pick'] ?? null,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Exam created successfully!',
                'redirect' => route('examcenter'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Exam creation error: '.$e->getMessage(), [
                'request_data' => $request->all(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the exam. Please try again.',
                'errors' => ['general' => [$e->getMessage()]],
            ], 422);
        }
    }

    /**
     * Generate a new exam password via AJAX
     */
    public function generatePassword()
    {
        $password = Str::random(8);

        while (Exam::where('password', $password)->exists()) {
            $password = Str::random(8);
        }

        return response()->json([
            'success' => true,
            'password' => $password,
        ]);
    }

    /**
     * Validate exam form data via AJAX
     */
    public function validateForm(Request $request)
    {
        try {
            $rules = [
                'class' => 'required|exists:college_classes,id',
                'year' => 'required|exists:years,id',
                'semester' => 'required|exists:semesters,id',
                'course_code' => 'required|exists:subjects,id',
                'exam_title' => 'required|string|max:255',
                'exam_type' => 'required|in:mcq,short_answer,essay,mixed',
                'exam_duration' => 'required|integer|min:1',
                'questions_per_session' => 'required|integer|min:1',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'exam_password' => 'required|string',
                'selected_question_sets' => 'required|array|min:1',
            ];

            $request->validate($rules);

            return response()->json([
                'success' => true,
                'message' => 'Form validation passed',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        }
    }
}
