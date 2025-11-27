<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExamSession;
use App\Models\ExamSessionQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SessionQuestionsSync extends Controller
{
    /**
     * Sync session questions from offline app to CIS
     * This creates ExamSessionQuestion entries to match CIS's dynamic question assignment
     */
    public function syncSessionQuestions(Request $request)
    {
        $request->validate([
            'session_questions' => 'required|array',
            'session_questions.*.cis_session_id' => 'required|integer',
            'session_questions.*.session_questions' => 'required|array',
            'session_questions.*.session_questions.*.question_id' => 'required|integer',
            'session_questions.*.session_questions.*.display_order' => 'required|integer',
        ]);

        try {
            $synced = [];
            $failed = [];

            DB::transaction(function () use ($request, &$synced, &$failed) {
                foreach ($request->session_questions as $sessionQuestionsData) {
                    try {
                        $cisSessionId = $sessionQuestionsData['cis_session_id'];
                        $questionAssignments = $sessionQuestionsData['session_questions'];

                        // Verify the exam session exists
                        $examSession = ExamSession::find($cisSessionId);
                        if (!$examSession) {
                            $failed[] = [
                                'cis_session_id' => $cisSessionId,
                                'error' => 'Exam session not found',
                            ];
                            continue;
                        }

                        // Clear existing session questions to avoid duplicates
                        ExamSessionQuestion::where('exam_session_id', $cisSessionId)->delete();

                        // Create new session question assignments
                        $createdCount = 0;
                        foreach ($questionAssignments as $assignment) {
                            ExamSessionQuestion::create([
                                'exam_session_id' => $cisSessionId,
                                'question_id' => $assignment['question_id'],
                                'display_order' => $assignment['display_order'],
                            ]);
                            $createdCount++;
                        }

                        $synced[] = [
                            'cis_session_id' => $cisSessionId,
                            'questions_assigned' => $createdCount,
                            'action' => 'created',
                        ];

                        Log::info('Session questions synced successfully', [
                            'cis_session_id' => $cisSessionId,
                            'questions_count' => $createdCount,
                        ]);

                    } catch (\Exception $e) {
                        $failed[] = [
                            'cis_session_id' => $sessionQuestionsData['cis_session_id'] ?? 'unknown',
                            'error' => $e->getMessage(),
                        ];

                        Log::error('Failed to sync session questions', [
                            'session_questions_data' => $sessionQuestionsData,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });

            Log::info('Session questions sync completed', [
                'synced_count' => count($synced),
                'failed_count' => count($failed),
            ]);

            return response()->json([
                'success' => true,
                'synced' => $synced,
                'failed' => $failed,
                'summary' => [
                    'total' => count($request->session_questions),
                    'synced' => count($synced),
                    'failed' => count($failed),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error syncing session questions', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error syncing session questions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}