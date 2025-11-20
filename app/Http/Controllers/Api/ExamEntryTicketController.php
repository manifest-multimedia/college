<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamEntryTicket;
use App\Models\OfflineExam;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ExamEntryTicketController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['validateTicket', 'ticketInfo']);
        $this->middleware('permission:view entry tickets')->only(['index', 'show', 'getStudentTickets']);
        $this->middleware('permission:issue entry tickets')->only(['store', 'bulkIssue']);
        $this->middleware('permission:verify entry tickets')->only(['verifyTicket']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = ExamEntryTicket::query();

            // Filter by exam type (online/offline)
            if ($request->has('ticketable_type')) {
                $modelClass = $request->ticketable_type === 'offline'
                    ? 'App\\Models\\OfflineExam'
                    : 'App\\Models\\Exam';
                $query->where('ticketable_type', $modelClass);
            }

            // Filter by exam id
            if ($request->has('ticketable_id')) {
                $query->where('ticketable_id', $request->ticketable_id);
            }

            // Filter by student
            if ($request->has('student_id')) {
                $query->where('student_id', $request->student_id);
            }

            // Filter by verification status
            if ($request->has('is_verified')) {
                $query->where('is_verified', filter_var($request->is_verified, FILTER_VALIDATE_BOOLEAN));
            }

            // Filter by active status
            if ($request->has('is_active')) {
                $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
            }

            // Eager load relationships
            $query->with(['student', 'ticketable', 'examClearance']);

            // Paginate results
            $tickets = $query->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $tickets,
                'message' => 'Exam entry tickets retrieved successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving exam entry tickets: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving exam entry tickets',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get tickets for a specific student.
     */
    public function getStudentTickets(Request $request, $studentId)
    {
        try {
            $query = ExamEntryTicket::where('student_id', $studentId)
                ->where('is_active', true);

            // Filter by exam type (online/offline)
            if ($request->has('exam_type')) {
                $modelClass = $request->exam_type === 'offline'
                    ? 'App\\Models\\OfflineExam'
                    : 'App\\Models\\Exam';
                $query->where('ticketable_type', $modelClass);
            }

            // Eager load relationships
            $query->with(['ticketable', 'examClearance']);

            $tickets = $query->get();

            return response()->json([
                'success' => true,
                'data' => $tickets,
                'message' => 'Student tickets retrieved successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving student tickets: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving student tickets',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created entry ticket.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'student_id' => 'required|exists:students,id',
                'exam_clearance_id' => 'required|exists:exam_clearances,id',
                'exam_type' => 'required|in:online,offline',
                'exam_id' => 'required|integer',
                'expires_at' => 'nullable|date|after:now',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                ], Response::HTTP_BAD_REQUEST);
            }

            // Get exam based on type
            $exam = null;
            if ($request->exam_type === 'online') {
                $exam = Exam::findOrFail($request->exam_id);
            } else {
                $exam = OfflineExam::findOrFail($request->exam_id);
            }

            // Check if a ticket already exists
            $existingTicket = ExamEntryTicket::where('student_id', $request->student_id)
                ->where('exam_clearance_id', $request->exam_clearance_id)
                ->where('ticketable_type', get_class($exam))
                ->where('ticketable_id', $exam->id)
                ->where('is_active', true)
                ->first();

            if ($existingTicket) {
                return response()->json([
                    'success' => false,
                    'message' => 'Active entry ticket already exists for this student and exam',
                    'data' => $existingTicket,
                ], Response::HTTP_CONFLICT);
            }

            // Create new entry ticket
            $ticket = new ExamEntryTicket([
                'student_id' => $request->student_id,
                'exam_clearance_id' => $request->exam_clearance_id,
                'is_verified' => false,
                'is_active' => true,
                'expires_at' => $request->expires_at ?? now()->addDays(7),
            ]);

            $exam->examEntryTickets()->save($ticket);

            // Load relationships for the response
            $ticket->load(['student', 'ticketable', 'examClearance']);

            return response()->json([
                'success' => true,
                'data' => $ticket,
                'message' => 'Exam entry ticket created successfully',
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            Log::error('Error creating exam entry ticket: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error creating exam entry ticket',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified entry ticket.
     */
    public function show(string $id)
    {
        try {
            $ticket = ExamEntryTicket::with(['student', 'ticketable', 'examClearance'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $ticket,
                'message' => 'Exam entry ticket retrieved successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving exam entry ticket: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving exam entry ticket',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Validate a ticket (public endpoint for verification stations).
     */
    public function validateTicket(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ticket_number' => 'required|string',
                'qr_code' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                ], Response::HTTP_BAD_REQUEST);
            }

            $ticket = null;

            // Try to find by ticket number
            if ($request->has('ticket_number')) {
                $ticket = ExamEntryTicket::where('ticket_number', $request->ticket_number)
                    ->first();
            }

            // If not found and QR code provided, try by QR code
            if (! $ticket && $request->has('qr_code')) {
                $ticket = ExamEntryTicket::where('qr_code', $request->qr_code)
                    ->first();
            }

            if (! $ticket) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid ticket',
                    'is_valid' => false,
                ], Response::HTTP_NOT_FOUND);
            }

            // Check if ticket is valid
            $isValid = $ticket->isValid();

            // Load relationships for detailed response
            if ($isValid) {
                $ticket->load(['student', 'ticketable', 'examClearance']);
            }

            return response()->json([
                'success' => true,
                'is_valid' => $isValid,
                'data' => $isValid ? $ticket : null,
                'message' => $isValid
                    ? 'Ticket is valid'
                    : ($ticket->is_verified
                        ? 'Ticket has already been used'
                        : ($ticket->is_active ? 'Ticket has expired' : 'Ticket is inactive')),
            ]);
        } catch (\Exception $e) {
            Log::error('Error validating ticket: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error validating ticket',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get ticket information (for student view).
     */
    public function ticketInfo(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ticket_number' => 'required|string',
                'student_id' => 'required|exists:students,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                ], Response::HTTP_BAD_REQUEST);
            }

            $ticket = ExamEntryTicket::where('ticket_number', $request->ticket_number)
                ->where('student_id', $request->student_id)
                ->first();

            if (! $ticket) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ticket not found',
                ], Response::HTTP_NOT_FOUND);
            }

            // Load relationships
            $ticket->load(['student', 'ticketable', 'examClearance']);

            return response()->json([
                'success' => true,
                'data' => $ticket,
                'is_valid' => $ticket->isValid(),
                'message' => 'Ticket information retrieved successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving ticket information: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving ticket information',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Verify a ticket (mark as used).
     */
    public function verifyTicket(Request $request, string $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'verification_location' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                ], Response::HTTP_BAD_REQUEST);
            }

            $ticket = ExamEntryTicket::findOrFail($id);

            if (! $ticket->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => $ticket->is_verified
                        ? 'Ticket has already been verified'
                        : ($ticket->is_active ? 'Ticket has expired' : 'Ticket is inactive'),
                ], Response::HTTP_BAD_REQUEST);
            }

            // Update ticket verification status
            $ticket->update([
                'is_verified' => true,
                'verified_at' => now(),
                'verified_by' => Auth::id(),
                'verification_location' => $request->verification_location,
                'verification_ip' => $request->ip(),
            ]);

            // Load relationships for response
            $ticket->load(['student', 'ticketable', 'examClearance', 'verifiedBy']);

            return response()->json([
                'success' => true,
                'data' => $ticket,
                'message' => 'Ticket verified successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error verifying ticket: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error verifying ticket',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Bulk issue tickets for an exam.
     */
    public function bulkIssue(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'student_ids' => 'required|array',
                'student_ids.*' => 'exists:students,id',
                'exam_type' => 'required|in:online,offline',
                'exam_id' => 'required|integer',
                'expires_at' => 'nullable|date|after:now',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                ], Response::HTTP_BAD_REQUEST);
            }

            // Get exam based on type
            $exam = null;
            if ($request->exam_type === 'online') {
                $exam = Exam::findOrFail($request->exam_id);
            } else {
                $exam = OfflineExam::findOrFail($request->exam_id);
            }

            $created = 0;
            $skipped = 0;
            $tickets = [];

            foreach ($request->student_ids as $studentId) {
                // Find clearance for this student and exam
                $clearance = \App\Models\ExamClearance::where('student_id', $studentId)
                    ->where('clearable_type', get_class($exam))
                    ->where('clearable_id', $exam->id)
                    ->where('is_cleared', true)
                    ->first();

                if (! $clearance) {
                    $skipped++;

                    continue;
                }

                // Check if a ticket already exists
                $existingTicket = ExamEntryTicket::where('student_id', $studentId)
                    ->where('ticketable_type', get_class($exam))
                    ->where('ticketable_id', $exam->id)
                    ->where('is_active', true)
                    ->first();

                if ($existingTicket) {
                    $skipped++;

                    continue;
                }

                // Create new entry ticket
                $ticket = new ExamEntryTicket([
                    'student_id' => $studentId,
                    'exam_clearance_id' => $clearance->id,
                    'is_verified' => false,
                    'is_active' => true,
                    'expires_at' => $request->expires_at ?? now()->addDays(7),
                ]);

                $exam->examEntryTickets()->save($ticket);
                $tickets[] = $ticket;
                $created++;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'tickets_created' => $created,
                    'tickets_skipped' => $skipped,
                    'total_processed' => count($request->student_ids),
                ],
                'message' => 'Bulk ticket issuance completed',
            ]);
        } catch (\Exception $e) {
            Log::error('Error issuing bulk tickets: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error issuing bulk tickets',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
