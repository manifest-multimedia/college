<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOfflineExamRequest;
use App\Http\Requests\UpdateOfflineExamRequest;
use App\Jobs\ProcessExamClearanceJob;
use App\Models\OfflineExam;
use App\Services\Exams\ExamClearanceService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OfflineExamController extends Controller
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
        $this->middleware('permission:view exams')->only(['index', 'show']);
        $this->middleware('permission:create exams')->only(['store']);
        $this->middleware('permission:update exams')->only(['update']);
        $this->middleware('permission:delete exams')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = OfflineExam::query();
            
            // Filter by course
            if ($request->has('course_id')) {
                $query->where('course_id', $request->course_id);
            }
            
            // Filter by exam type
            if ($request->has('type_id')) {
                $query->where('type_id', $request->type_id);
            }
            
            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
            
            // Filter by venue
            if ($request->has('venue')) {
                $query->where('venue', 'like', '%' . $request->venue . '%');
            }
            
            // Eager load relationships
            $query->with(['course', 'type', 'proctor']);
            
            // Paginate or get all
            if ($request->has('per_page')) {
                $exams = $query->paginate($request->per_page);
            } else {
                $exams = $query->get();
            }
            
            return response()->json([
                'success' => true,
                'data' => $exams,
                'message' => 'Offline exams retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving offline exams: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving offline exams',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOfflineExamRequest $request)
    {
        try {
            $data = $request->validated();
            
            // Set the current user as creator if not specified
            if (!isset($data['user_id'])) {
                $data['user_id'] = Auth::id();
            }
            
            // Create the offline exam
            $offlineExam = OfflineExam::create($data);
            
            // Eager load relationships for response
            $offlineExam->load(['course', 'type', 'proctor']);
            
            // If the exam is published, trigger clearance processing
            if ($offlineExam->status === 'published') {
                ProcessExamClearanceJob::dispatch($offlineExam)
                    ->onQueue('exam_clearances');
            }
            
            return response()->json([
                'success' => true,
                'data' => $offlineExam,
                'message' => 'Offline exam created successfully'
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            Log::error('Error creating offline exam: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error creating offline exam',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $offlineExam = OfflineExam::with(['course', 'type', 'proctor'])
                ->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $offlineExam,
                'message' => 'Offline exam retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving offline exam: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving offline exam',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOfflineExamRequest $request, string $id)
    {
        try {
            $offlineExam = OfflineExam::findOrFail($id);
            $data = $request->validated();
            
            // Check if status is changing to published
            $wasPublished = $offlineExam->status === 'published';
            
            // Update the offline exam
            $offlineExam->update($data);
            
            // Reload the model with relationships
            $offlineExam->load(['course', 'type', 'proctor']);
            
            // If the exam is newly published or clearance_threshold changed, trigger clearance processing
            if (($offlineExam->status === 'published' && !$wasPublished) || 
                (isset($data['clearance_threshold']) && $offlineExam->status === 'published')) {
                ProcessExamClearanceJob::dispatch($offlineExam)
                    ->onQueue('exam_clearances');
            }
            
            return response()->json([
                'success' => true,
                'data' => $offlineExam,
                'message' => 'Offline exam updated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating offline exam: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error updating offline exam',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $offlineExam = OfflineExam::findOrFail($id);
            
            // Check if there are any clearances or entry tickets
            if ($offlineExam->clearances()->count() > 0 || $offlineExam->examEntryTickets()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete offline exam with associated clearances or entry tickets'
                ], Response::HTTP_BAD_REQUEST);
            }
            
            $offlineExam->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Offline exam deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting offline exam: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error deleting offline exam',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * Process clearance for a specific exam.
     */
    public function processClearance(Request $request, string $id)
    {
        try {
            $offlineExam = OfflineExam::findOrFail($id);
            
            // Dispatch job to process clearances
            $job = ProcessExamClearanceJob::dispatch($offlineExam)
                ->onQueue('exam_clearances');
            
            return response()->json([
                'success' => true,
                'message' => 'Clearance processing started for offline exam',
                'data' => [
                    'exam_id' => $offlineExam->id,
                    'job_id' => $job->getJobId() ?? null
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error processing clearance for offline exam: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error processing clearance for offline exam',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
