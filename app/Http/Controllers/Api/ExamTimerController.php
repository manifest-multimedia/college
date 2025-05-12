<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ExamSession;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ExamTimerController extends Controller
{
    /**
     * Check the current status of an exam timer
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkStatus(Request $request)
    {
        $request->validate([
            'exam_session_id' => 'required|exists:exam_sessions,id'
        ]);

        try {
            $examSession = ExamSession::findOrFail($request->exam_session_id);
            
            // Check if the exam is still active (not expired)
            $isActive = $examSession->adjustedCompletionTime->gt(now());
            
            return response()->json([
                'isActive' => $isActive,
                'endTimeIso' => $examSession->adjustedCompletionTime->toIso8601String(),
                'currentServerTime' => now()->toIso8601String(),
                'hasExtraTime' => $examSession->extra_time_minutes > 0,
                'remainingSeconds' => $isActive ? now()->diffInSeconds($examSession->adjustedCompletionTime, false) : 0
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking exam timer status', [
                'error' => $e->getMessage(),
                'exam_session_id' => $request->exam_session_id
            ]);
            
            return response()->json([
                'error' => 'Failed to check exam timer status',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check for and return extra time information
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkExtraTime(Request $request)
    {
        $request->validate([
            'exam_session_id' => 'required|exists:exam_sessions,id'
        ]);

        try {
            $examSession = ExamSession::findOrFail($request->exam_session_id);
            
            // Calculate base end time (without extra time)
            $startTime = Carbon::parse($examSession->started_at);
            $baseEndTime = $startTime->copy()->addMinutes($examSession->exam->duration);
            
            // Get adjusted end time (with extra time)
            $adjustedEndTime = $examSession->adjustedCompletionTime;
            
            // Check if extra time was recently added (within last 5 minutes)
            $recentlyAdded = false;
            $addedAgo = null;
            
            if ($examSession->extra_time_added_at) {
                $recentlyAdded = $examSession->extra_time_added_at->diffInMinutes(now()) < 5;
                $addedAgo = $examSession->extra_time_added_at->diffForHumans();
            }
            
            return response()->json([
                'hasExtraTime' => $examSession->extra_time_minutes > 0,
                'extraMinutes' => $examSession->extra_time_minutes,
                'baseEndTime' => $baseEndTime->toIso8601String(),
                'newEndTime' => $adjustedEndTime->toIso8601String(),
                'recentlyAdded' => $recentlyAdded,
                'addedAgo' => $addedAgo,
                'currentServerTime' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking exam extra time', [
                'error' => $e->getMessage(),
                'exam_session_id' => $request->exam_session_id
            ]);
            
            return response()->json([
                'error' => 'Failed to check extra time',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit exam due to time expiration
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitExam(Request $request)
    {
        $request->validate([
            'exam_session_id' => 'required|exists:exam_sessions,id'
        ]);

        try {
            $examSession = ExamSession::findOrFail($request->exam_session_id);
            
            // Check if the exam is actually expired
            if ($examSession->adjustedCompletionTime->gt(now())) {
                return response()->json([
                    'error' => 'Cannot submit exam that is not expired',
                    'isActive' => true,
                    'endTimeIso' => $examSession->adjustedCompletionTime->toIso8601String(),
                ], 400);
            }
            
            // Calculate score based on responses
            $score = 0;
            $responses = $examSession->responses;
            
            foreach ($responses as $response) {
                $question = $response->question;
                if ($question && $response->selected_option == $question->correct_option) {
                    $score += $question->mark;
                }
            }
            
            // Update the session with completion info
            $examSession->update([
                'completed_at' => now(),
                'score' => $score,
                'auto_submitted' => true,
            ]);
            
            Log::info('Exam auto-submitted via API due to time expiration', [
                'session_id' => $examSession->id,
                'score' => $score,
                'responses_count' => $responses->count(),
                'completion_time' => now()->toDateTimeString()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Exam submitted successfully',
                'score' => $score,
                'submittedAt' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            Log::error('Error auto-submitting exam', [
                'error' => $e->getMessage(),
                'exam_session_id' => $request->exam_session_id
            ]);
            
            return response()->json([
                'error' => 'Failed to submit exam',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
