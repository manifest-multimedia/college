<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExamEntryTicket;
use App\Services\ExamClearanceManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ExamTicketController extends Controller
{
    /**
     * Validate an exam entry ticket using its QR code
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateTicket(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'qr_code' => 'required|string',
            'verified_by' => 'required|integer|exists:users,id',
            'location' => 'nullable|string|max:255',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Extract request data
        $qrCode = $request->input('qr_code');
        $verifiedBy = $request->input('verified_by');
        $location = $request->input('location');
        $ip = $request->ip();
        
        try {
            // Use the ExamClearanceManager service to validate the ticket
            $clearanceManager = new ExamClearanceManager();
            $result = $clearanceManager->verifyExamEntryTicket($qrCode, $verifiedBy, $location, $ip);
            
            return response()->json($result, $result['success'] ? 200 : 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error validating exam ticket: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get detailed information about a ticket for debugging purposes
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ticketInfo(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'qr_code' => 'required|string',
            'api_key' => 'required|string', // For security, require an API key
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Verify API key (compare with a constant or config value)
        $validApiKey = config('services.exam_validation.api_key', 'default_key');
        if ($request->input('api_key') !== $validApiKey) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key'
            ], 403);
        }
        
        // Find ticket
        $ticket = ExamEntryTicket::where('qr_code', $request->input('qr_code'))->first();
        
        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found'
            ], 404);
        }
        
        // Return ticket information
        return response()->json([
            'success' => true,
            'data' => [
                'ticket' => [
                    'id' => $ticket->id,
                    'qr_code' => $ticket->qr_code,
                    'ticket_number' => $ticket->ticket_number,
                    'is_active' => $ticket->is_active,
                    'is_verified' => $ticket->is_verified,
                    'verified_at' => $ticket->verified_at,
                    'expires_at' => $ticket->expires_at,
                    'created_at' => $ticket->created_at,
                ],
                'student' => [
                    'id' => $ticket->student->id,
                    'name' => $ticket->student->full_name,
                    'student_id' => $ticket->student->student_id,
                ],
                'exam' => [
                    'id' => $ticket->exam->id,
                    'title' => $ticket->exam->title,
                    'exam_date' => $ticket->exam->exam_date,
                ],
                'clearance' => [
                    'id' => $ticket->examClearance->id,
                    'is_cleared' => $ticket->examClearance->is_cleared,
                    'is_manual_override' => $ticket->examClearance->is_manual_override,
                ],
            ]
        ]);
    }
}