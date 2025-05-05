<?php

namespace App\Services;

use App\Models\Student;
use App\Models\ExamType;
use App\Models\ExamClearance;
use App\Models\ExamEntryTicket;
use App\Models\Exam;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ExamClearanceManager
{
    /**
     * Check if a student is eligible for exam clearance based on fee payment status
     * 
     * @param Student $student
     * @param int $academicYearId
     * @param int $semesterId
     * @param int $examTypeId
     * @return bool
     */
    public function isEligibleForClearance(Student $student, int $academicYearId, int $semesterId, int $examTypeId): bool
    {
        return $student->isEligibleForExamClearance($academicYearId, $semesterId, $examTypeId);
    }
    
    /**
     * Process exam clearance for a student
     * 
     * @param Student $student
     * @param int $academicYearId
     * @param int $semesterId
     * @param int $examTypeId
     * @param bool $manualOverride
     * @param string|null $overrideReason
     * @param int|null $clearedBy
     * @return ExamClearance
     */
    public function processClearance(
        Student $student,
        int $academicYearId,
        int $semesterId,
        int $examTypeId,
        bool $manualOverride = false,
        ?string $overrideReason = null,
        ?int $clearedBy = null
    ): ExamClearance {
        Log::info('ExamClearanceService processClearance called', [
            'studentId' => $student->id,
            'academicYearId' => $academicYearId,
            'semesterId' => $semesterId,
            'examTypeId' => $examTypeId,
            'manualOverride' => $manualOverride
        ]);

        return DB::transaction(function () use (
            $student,
            $academicYearId,
            $semesterId,
            $examTypeId,
            $manualOverride,
            $overrideReason,
            $clearedBy
        ) {
            try {
                // Check if a clearance record already exists for this student
                $existingClearance = $student->examClearances()
                    ->where('academic_year_id', $academicYearId)
                    ->where('semester_id', $semesterId)
                    ->where('exam_type_id', $examTypeId)
                    ->first();
                
                Log::info('Checking existing clearance', [
                    'exists' => !is_null($existingClearance),
                    'clearanceId' => $existingClearance->id ?? 'none'
                ]);
                    
                if ($existingClearance) {
                    // If a record exists, update its status
                    Log::info('Updating existing clearance', [
                        'clearanceId' => $existingClearance->id,
                        'previousStatus' => $existingClearance->is_cleared
                    ]);
                    
                    $existingClearance->update([
                        'is_cleared' => true,
                        'is_manual_override' => $manualOverride,
                        'override_reason' => $overrideReason,
                        'cleared_by' => $clearedBy,
                        'cleared_at' => Carbon::now(),
                    ]);
                    
                    Log::info('Existing clearance updated', [
                        'clearanceId' => $existingClearance->id,
                        'newStatus' => true
                    ]);
                    
                    return $existingClearance->fresh();
                }
                
                // Generate a unique clearance code
                $clearanceCode = 'CLR-' . $student->student_id . '-' . strtoupper(Str::random(6));
                
                Log::info('Creating new clearance record', [
                    'studentId' => $student->id,
                    'clearanceCode' => $clearanceCode
                ]);
                
                // Create a new clearance record
                $clearance = ExamClearance::create([
                    'student_id' => $student->id,
                    'academic_year_id' => $academicYearId,
                    'semester_id' => $semesterId,
                    'exam_type_id' => $examTypeId,
                    'is_cleared' => true,
                    'is_manual_override' => $manualOverride,
                    'override_reason' => $overrideReason,
                    'cleared_by' => $clearedBy,
                    'cleared_at' => Carbon::now(),
                    'clearance_code' => $clearanceCode,
                ]);
                
                Log::info('New clearance created', [
                    'clearanceId' => $clearance->id,
                    'clearanceCode' => $clearance->clearance_code,
                    'isCleared' => $clearance->is_cleared
                ]);
                
                return $clearance;
            } catch (\Exception $e) {
                Log::error('Error in processClearance transaction', [
                    'error' => $e->getMessage(),
                    'studentId' => $student->id,
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        });
    }
    
    /**
     * Revoke a student's exam clearance
     * 
     * @param ExamClearance $clearance
     * @return bool
     */
    public function revokeClearance(ExamClearance $clearance): bool
    {
        return DB::transaction(function () use ($clearance) {
            // Invalidate any existing exam entry tickets
            $clearance->examEntryTickets()->update([
                'is_active' => false
            ]);
            
            // Update clearance status
            return $clearance->update([
                'is_cleared' => false,
                'cleared_at' => null,
            ]);
        });
    }
    
    /**
     * Generate exam entry ticket for a cleared student
     * 
     * @param ExamClearance $clearance
     * @param Exam $exam
     * @param Carbon|null $expiresAt
     * @return ExamEntryTicket
     */
    public function generateExamEntryTicket(ExamClearance $clearance, Exam $exam, ?Carbon $expiresAt = null): ExamEntryTicket
    {
        if (!$clearance->is_cleared) {
            throw new \Exception('Cannot generate exam entry ticket for a student who is not cleared for exams.');
        }
        
        // Check for any existing active tickets for this exam
        $existingTicket = ExamEntryTicket::where('exam_clearance_id', $clearance->id)
            ->where('exam_id', $exam->id)
            ->where('is_active', true)
            ->first();
            
        if ($existingTicket) {
            return $existingTicket;
        }
        
        // Generate QR code and ticket number
        $qrCode = 'QR-' . $clearance->student->student_id . '-' . $exam->id . '-' . Str::random(8);
        $ticketNumber = 'TKT-' . strtoupper(Str::random(8));
        
        // Create the entry ticket
        $ticket = ExamEntryTicket::create([
            'exam_clearance_id' => $clearance->id,
            'student_id' => $clearance->student_id,
            'exam_id' => $exam->id,
            'qr_code' => $qrCode,
            'ticket_number' => $ticketNumber,
            'is_verified' => false,
            'is_active' => true,
            'expires_at' => $expiresAt,
        ]);
        
        return $ticket;
    }
    
    /**
     * Verify an exam entry ticket
     * 
     * @param string $qrCode
     * @param int $verifiedBy
     * @param string|null $location
     * @param string|null $ip
     * @return array
     */
    public function verifyExamEntryTicket(string $qrCode, int $verifiedBy, ?string $location = null, ?string $ip = null): array
    {
        $ticket = ExamEntryTicket::where('qr_code', $qrCode)->first();
        
        if (!$ticket) {
            return [
                'success' => false,
                'message' => 'Invalid QR code. Ticket not found.',
                'data' => null
            ];
        }
        
        if (!$ticket->is_active) {
            return [
                'success' => false,
                'message' => 'This ticket is no longer active.',
                'data' => [
                    'student' => $ticket->student,
                    'exam' => $ticket->exam,
                    'status' => 'inactive'
                ]
            ];
        }
        
        if ($ticket->is_verified) {
            return [
                'success' => false,
                'message' => 'This ticket has already been used for entry.',
                'data' => [
                    'student' => $ticket->student,
                    'exam' => $ticket->exam,
                    'status' => 'already_used',
                    'verified_at' => $ticket->verified_at
                ]
            ];
        }
        
        if ($ticket->expires_at && now()->isAfter($ticket->expires_at)) {
            return [
                'success' => false,
                'message' => 'This ticket has expired.',
                'data' => [
                    'student' => $ticket->student,
                    'exam' => $ticket->exam,
                    'status' => 'expired',
                    'expired_at' => $ticket->expires_at
                ]
            ];
        }
        
        // Update the ticket status
        $ticket->update([
            'is_verified' => true,
            'verified_at' => Carbon::now(),
            'verified_by' => $verifiedBy,
            'verification_location' => $location,
            'verification_ip' => $ip,
        ]);
        
        return [
            'success' => true,
            'message' => 'Ticket verified successfully. Student is cleared for the exam.',
            'data' => [
                'student' => $ticket->student,
                'exam' => $ticket->exam,
                'status' => 'cleared',
                'verified_at' => $ticket->verified_at
            ]
        ];
    }
    
    /**
     * Get all cleared students for an exam type in a specific academic year and semester
     * 
     * @param int $academicYearId
     * @param int $semesterId
     * @param int $examTypeId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getClearedStudents(int $academicYearId, int $semesterId, int $examTypeId)
    {
        return ExamClearance::where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->where('exam_type_id', $examTypeId)
            ->where('is_cleared', true)
            ->with(['student', 'examType'])
            ->get();
    }
}