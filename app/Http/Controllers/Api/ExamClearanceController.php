<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessExamClearanceJob;
use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\ExamClearance;
use App\Models\OfflineExam;
use App\Models\Semester;
use App\Models\Student;
use App\Services\Exams\ExamClearanceService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ExamClearanceController extends Controller
{
    /**
     * @var ExamClearanceService
     */
    protected $clearanceService;

    /**
     * Create a new controller instance.
     *
     * @param ExamClearanceService $clearanceService
     */
    public function __construct(ExamClearanceService $clearanceService)
    {
        $this->clearanceService = $clearanceService;
        $this->middleware('auth:sanctum');
        $this->middleware('permission:view clearances')->only(['index', 'show', 'getStudentClearances']);
        $this->middleware('permission:manage clearances')->only([
            'store', 'update', 'destroy', 'manualOverride', 'bulkProcess'
        ]);
    }

    /**
     * Display a listing of the exam clearances.
     */
    public function index(Request $request)
    {
        try {
            $query = ExamClearance::query();
            
            // Filter by student
            if ($request->has('student_id')) {
                $query->where('student_id', $request->student_id);
            }
            
            // Filter by exam type (online/offline)
            if ($request->has('clearable_type')) {
                $modelClass = $request->clearable_type === 'offline' 
                    ? 'App\\Models\\OfflineExam' 
                    : 'App\\Models\\Exam';
                $query->where('clearable_type', $modelClass);
            }
            
            // Filter by exam id
            if ($request->has('clearable_id')) {
                $query->where('clearable_id', $request->clearable_id);
            }
            
            // Filter by academic year
            if ($request->has('academic_year_id')) {
                $query->where('academic_year_id', $request->academic_year_id);
            }
            
            // Filter by semester
            if ($request->has('semester_id')) {
                $query->where('semester_id', $request->semester_id);
            }
            
            // Filter by status (cleared/pending/denied)
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
            
            // Filter by clearance status
            if ($request->has('is_cleared')) {
                $query->where('is_cleared', filter_var($request->is_cleared, FILTER_VALIDATE_BOOLEAN));
            }
            
            // Eager load relationships
            $query->with(['student', 'academicYear', 'semester', 'clearable', 'clearedBy']);
            
            // Paginate
            $clearances = $query->paginate($request->get('per_page', 15));
            
            return response()->json([
                'success' => true,
                'data' => $clearances,
                'message' => 'Exam clearances retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving exam clearances: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving exam clearances',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get clearances for a specific student.
     */
    public function getStudentClearances(Request $request, $studentId)
    {
        try {
            $student = Student::findOrFail($studentId);
            
            $query = ExamClearance::where('student_id', $studentId);
            
            // Filter by exam type (online/offline)
            if ($request->has('exam_type')) {
                $modelClass = $request->exam_type === 'offline' 
                    ? 'App\\Models\\OfflineExam' 
                    : 'App\\Models\\Exam';
                $query->where('clearable_type', $modelClass);
            }
            
            // Filter by academic year & semester
            if ($request->has('academic_year_id')) {
                $query->where('academic_year_id', $request->academic_year_id);
            } else {
                // Default to current academic year
                $currentAcademicYear = AcademicYear::where('is_current', true)->first();
                if ($currentAcademicYear) {
                    $query->where('academic_year_id', $currentAcademicYear->id);
                }
            }
            
            if ($request->has('semester_id')) {
                $query->where('semester_id', $request->semester_id);
            } else {
                // Default to current semester
                $currentSemester = Semester::where('is_current', true)->first();
                if ($currentSemester) {
                    $query->where('semester_id', $currentSemester->id);
                }
            }
            
            // Eager load relationships
            $query->with(['clearable', 'academicYear', 'semester']);
            
            $clearances = $query->get();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'student' => $student,
                    'clearances' => $clearances
                ],
                'message' => 'Student clearances retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving student clearances: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving student clearances',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Check clearance eligibility for a student (without creating a clearance record).
     */
    public function checkClearance(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'student_id' => 'required|exists:students,id',
                'exam_type' => 'required|in:online,offline',
                'exam_id' => 'required|integer',
                'academic_year_id' => 'nullable|exists:academic_years,id',
                'semester_id' => 'nullable|exists:semesters,id',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], Response::HTTP_BAD_REQUEST);
            }
            
            // Get student
            $student = Student::findOrFail($request->student_id);
            
            // Get exam based on type
            $exam = null;
            if ($request->exam_type === 'online') {
                $exam = Exam::findOrFail($request->exam_id);
            } else {
                $exam = OfflineExam::findOrFail($request->exam_id);
            }
            
            // Get academic year and semester (use current if not provided)
            $academicYear = $request->has('academic_year_id')
                ? AcademicYear::findOrFail($request->academic_year_id)
                : AcademicYear::where('is_current', true)->first();
                
            $semester = $request->has('semester_id')
                ? Semester::findOrFail($request->semester_id)
                : Semester::where('is_current', true)->first();
            
            // Check if student meets clearance threshold
            $isEligible = $this->clearanceService->checkClearance($student, $exam, $academicYear, $semester);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'student_id' => $student->id,
                    'exam_id' => $exam->id,
                    'exam_type' => $request->exam_type,
                    'academic_year' => $academicYear->name,
                    'semester' => $semester->name,
                    'clearance_threshold' => $exam->clearance_threshold,
                    'is_eligible' => $isEligible,
                ],
                'message' => $isEligible 
                    ? 'Student meets clearance requirements'
                    : 'Student does not meet clearance requirements'
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking clearance: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error checking clearance',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * Process manual clearance override for a student.
     */
    public function manualOverride(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'student_id' => 'required|exists:students,id',
                'exam_type' => 'required|in:online,offline',
                'exam_id' => 'required|integer',
                'academic_year_id' => 'nullable|exists:academic_years,id',
                'semester_id' => 'nullable|exists:semesters,id',
                'is_cleared' => 'required|boolean',
                'override_reason' => 'required|string',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], Response::HTTP_BAD_REQUEST);
            }
            
            // Get student
            $student = Student::findOrFail($request->student_id);
            
            // Get exam based on type
            $exam = null;
            if ($request->exam_type === 'online') {
                $exam = Exam::findOrFail($request->exam_id);
            } else {
                $exam = OfflineExam::findOrFail($request->exam_id);
            }
            
            // Get academic year and semester (use current if not provided)
            $academicYear = $request->has('academic_year_id')
                ? AcademicYear::findOrFail($request->academic_year_id)
                : AcademicYear::where('is_current', true)->first();
                
            $semester = $request->has('semester_id')
                ? Semester::findOrFail($request->semester_id)
                : Semester::where('is_current', true)->first();
            
            // Process manual override
            $clearance = $this->clearanceService->processClearance(
                $student,
                $exam,
                $academicYear,
                $semester,
                true, // is manual override
                $request->override_reason,
                Auth::id() // current user as the one who processed the override
            );
            
            return response()->json([
                'success' => true,
                'data' => $clearance,
                'message' => 'Manual clearance override processed successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error processing manual clearance override: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error processing manual clearance override',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Bulk process clearances for multiple students.
     */
    public function bulkProcess(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'student_ids' => 'required|array',
                'student_ids.*' => 'exists:students,id',
                'exam_type' => 'required|in:online,offline',
                'exam_id' => 'required|integer',
                'academic_year_id' => 'nullable|exists:academic_years,id',
                'semester_id' => 'nullable|exists:semesters,id',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], Response::HTTP_BAD_REQUEST);
            }
            
            // Get students
            $students = Student::whereIn('id', $request->student_ids)->get();
            
            // Get exam based on type
            $exam = null;
            if ($request->exam_type === 'online') {
                $exam = Exam::findOrFail($request->exam_id);
            } else {
                $exam = OfflineExam::findOrFail($request->exam_id);
            }
            
            // Get academic year and semester (use current if not provided)
            $academicYear = $request->has('academic_year_id')
                ? AcademicYear::findOrFail($request->academic_year_id)
                : AcademicYear::where('is_current', true)->first();
                
            $semester = $request->has('semester_id')
                ? Semester::findOrFail($request->semester_id)
                : Semester::where('is_current', true)->first();
            
            // Dispatch job to process clearances for these students
            ProcessExamClearanceJob::dispatch(
                $exam, 
                $students, 
                $academicYear, 
                $semester
            )->onQueue('exam_clearances');
            
            return response()->json([
                'success' => true,
                'message' => 'Bulk clearance processing started',
                'data' => [
                    'exam_id' => $exam->id,
                    'exam_type' => $request->exam_type,
                    'student_count' => $students->count(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error processing bulk clearances: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error processing bulk clearances',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display a specific clearance record.
     */
    public function show(string $id)
    {
        try {
            $clearance = ExamClearance::with(['student', 'academicYear', 'semester', 'clearable', 'clearedBy'])
                ->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $clearance,
                'message' => 'Exam clearance retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving exam clearance: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving exam clearance',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update a clearance record.
     */
    public function update(Request $request, string $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'is_cleared' => 'required|boolean',
                'status' => 'required|in:cleared,pending,denied',
                'is_manual_override' => 'required|boolean',
                'override_reason' => 'nullable|string|required_if:is_manual_override,true',
                'comments' => 'nullable|string',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], Response::HTTP_BAD_REQUEST);
            }
            
            $clearance = ExamClearance::findOrFail($id);
            
            $clearance->update([
                'is_cleared' => $request->is_cleared,
                'status' => $request->status,
                'is_manual_override' => $request->is_manual_override,
                'override_reason' => $request->override_reason,
                'comments' => $request->comments,
                'cleared_by' => $request->is_manual_override ? Auth::id() : $clearance->cleared_by,
                'cleared_at' => $request->is_cleared ? now() : $clearance->cleared_at,
            ]);
            
            // Reload the clearance with relationships
            $clearance->load(['student', 'academicYear', 'semester', 'clearable', 'clearedBy']);
            
            return response()->json([
                'success' => true,
                'data' => $clearance,
                'message' => 'Exam clearance updated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating exam clearance: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error updating exam clearance',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
