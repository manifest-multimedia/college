<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceAccessLog;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Response;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OfflineExamSyncController extends Controller
{
    /**
     * Sync exam sessions from offline app to CIS
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncExamSessions(Request $request)
    {
        $request->validate([
            'sessions' => 'required|array',
            'sessions.*.offline_exam_id' => 'required|integer',
            'sessions.*.cis_exam_id' => 'required|integer',
            'sessions.*.student_id' => 'required|string',
            'sessions.*.started_at' => 'required|date',
            'sessions.*.completed_at' => 'nullable|date',
            'sessions.*.score' => 'nullable|integer',
            'sessions.*.auto_submitted' => 'required|boolean',
            'sessions.*.extra_time_minutes' => 'nullable|integer',
            'sessions.*.device_info' => 'nullable|string',
        ]);

        try {
            $synced = [];
            $failed = [];

            DB::transaction(function () use ($request, &$synced, &$failed) {
                foreach ($request->sessions as $sessionData) {
                    try {
                        // Find the student by student_id
                        $student = Student::where('student_id', $sessionData['student_id'])->first();

                        if (! $student) {
                            $failed[] = [
                                'offline_exam_id' => $sessionData['offline_exam_id'],
                                'student_id' => $sessionData['student_id'],
                                'error' => 'Student not found',
                            ];

                            continue;
                        }

                        // Find the exam
                        $exam = Exam::find($sessionData['cis_exam_id']);

                        if (! $exam) {
                            $failed[] = [
                                'offline_exam_id' => $sessionData['offline_exam_id'],
                                'cis_exam_id' => $sessionData['cis_exam_id'],
                                'error' => 'Exam not found',
                            ];

                            continue;
                        }

                        // Check if session already exists (prevent duplicates)
                        $existingSession = ExamSession::where('exam_id', $exam->id)
                            ->where('student_id', $student->user_id)
                            ->where('started_at', $sessionData['started_at'])
                            ->first();

                        if ($existingSession) {
                            // Update existing session
                            $existingSession->update([
                                'completed_at' => $sessionData['completed_at'],
                                'score' => $sessionData['score'],
                                'auto_submitted' => $sessionData['auto_submitted'],
                                'extra_time_minutes' => $sessionData['extra_time_minutes'] ?? 0,
                                'device_info' => $sessionData['device_info'] ?? null,
                            ]);

                            $synced[] = [
                                'offline_exam_id' => $sessionData['offline_exam_id'],
                                'cis_session_id' => $existingSession->id,
                                'action' => 'updated',
                            ];
                        } else {
                            // Create new session
                            $examSession = ExamSession::create([
                                'exam_id' => $exam->id,
                                'student_id' => $student->user_id,
                                'started_at' => $sessionData['started_at'],
                                'completed_at' => $sessionData['completed_at'],
                                'score' => $sessionData['score'],
                                'auto_submitted' => $sessionData['auto_submitted'],
                                'extra_time_minutes' => $sessionData['extra_time_minutes'] ?? 0,
                                'device_info' => $sessionData['device_info'] ?? null,
                            ]);

                            $synced[] = [
                                'offline_exam_id' => $sessionData['offline_exam_id'],
                                'cis_session_id' => $examSession->id,
                                'action' => 'created',
                            ];
                        }
                    } catch (\Exception $e) {
                        $failed[] = [
                            'offline_exam_id' => $sessionData['offline_exam_id'],
                            'error' => $e->getMessage(),
                        ];

                        Log::error('Failed to sync exam session', [
                            'session_data' => $sessionData,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });

            Log::info('Exam sessions sync completed', [
                'synced_count' => count($synced),
                'failed_count' => count($failed),
            ]);

            return response()->json([
                'success' => true,
                'synced' => $synced,
                'failed' => $failed,
                'summary' => [
                    'total' => count($request->sessions),
                    'synced' => count($synced),
                    'failed' => count($failed),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error syncing exam sessions', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error syncing exam sessions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sync responses from offline app to CIS
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncResponses(Request $request)
    {
        $request->validate([
            'responses' => 'required|array',
            'responses.*.offline_session_id' => 'required|integer',
            'responses.*.cis_session_id' => 'required|integer',
            'responses.*.question_id' => 'required|integer',
            'responses.*.student_id' => 'required|string',
            'responses.*.selected_option' => 'nullable|integer',
            'responses.*.option_id' => 'nullable|integer',
            'responses.*.is_correct' => 'required|boolean',
        ]);

        try {
            $synced = [];
            $failed = [];

            DB::transaction(function () use ($request, &$synced, &$failed) {
                foreach ($request->responses as $responseData) {
                    try {
                        // Find the student
                        $student = Student::where('student_id', $responseData['student_id'])->first();

                        if (! $student) {
                            $failed[] = [
                                'offline_session_id' => $responseData['offline_session_id'],
                                'question_id' => $responseData['question_id'],
                                'error' => 'Student not found',
                            ];

                            continue;
                        }

                        // Check if response already exists
                        $existingResponse = Response::where('exam_session_id', $responseData['cis_session_id'])
                            ->where('question_id', $responseData['question_id'])
                            ->where('student_id', $student->user_id)
                            ->first();

                        if ($existingResponse) {
                            // Update existing response
                            $existingResponse->update([
                                'selected_option' => $responseData['selected_option'],
                                'option_id' => $responseData['option_id'],
                                'is_correct' => $responseData['is_correct'],
                            ]);

                            $synced[] = [
                                'offline_session_id' => $responseData['offline_session_id'],
                                'question_id' => $responseData['question_id'],
                                'cis_response_id' => $existingResponse->id,
                                'action' => 'updated',
                            ];
                        } else {
                            // Create new response
                            $response = Response::create([
                                'exam_session_id' => $responseData['cis_session_id'],
                                'question_id' => $responseData['question_id'],
                                'student_id' => $student->user_id,
                                'selected_option' => $responseData['selected_option'],
                                'option_id' => $responseData['option_id'],
                                'is_correct' => $responseData['is_correct'],
                            ]);

                            $synced[] = [
                                'offline_session_id' => $responseData['offline_session_id'],
                                'question_id' => $responseData['question_id'],
                                'cis_response_id' => $response->id,
                                'action' => 'created',
                            ];
                        }
                    } catch (\Exception $e) {
                        $failed[] = [
                            'offline_session_id' => $responseData['offline_session_id'],
                            'question_id' => $responseData['question_id'],
                            'error' => $e->getMessage(),
                        ];

                        Log::error('Failed to sync response', [
                            'response_data' => $responseData,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });

            Log::info('Responses sync completed', [
                'synced_count' => count($synced),
                'failed_count' => count($failed),
            ]);

            return response()->json([
                'success' => true,
                'synced' => $synced,
                'failed' => $failed,
                'summary' => [
                    'total' => count($request->responses),
                    'synced' => count($synced),
                    'failed' => count($failed),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error syncing responses', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error syncing responses',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sync device access logs from offline app to CIS
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncDeviceLogs(Request $request)
    {
        $request->validate([
            'logs' => 'required|array',
            'logs.*.cis_session_id' => 'required|integer',
            'logs.*.student_id' => 'required|string',
            'logs.*.device_info' => 'required|string',
            'logs.*.session_token' => 'required|string',
            'logs.*.is_conflict' => 'required|boolean',
            'logs.*.ip_address' => 'nullable|string',
            'logs.*.access_time' => 'required|date',
        ]);

        try {
            $synced = [];
            $failed = [];

            DB::transaction(function () use ($request, &$synced, &$failed) {
                foreach ($request->logs as $logData) {
                    try {
                        // Find the student
                        $student = Student::where('student_id', $logData['student_id'])->first();

                        if (! $student) {
                            $failed[] = [
                                'cis_session_id' => $logData['cis_session_id'],
                                'student_id' => $logData['student_id'],
                                'error' => 'Student not found',
                            ];

                            continue;
                        }

                        // Find the exam session
                        $examSession = ExamSession::find($logData['cis_session_id']);

                        if (! $examSession) {
                            $failed[] = [
                                'cis_session_id' => $logData['cis_session_id'],
                                'error' => 'Exam session not found',
                            ];

                            continue;
                        }

                        // Create device access log
                        $deviceLog = DeviceAccessLog::create([
                            'exam_session_id' => $logData['cis_session_id'],
                            'student_id' => $student->id,
                            'student_user_id' => $student->user_id,
                            'exam_id' => $examSession->exam_id,
                            'device_info' => $logData['device_info'],
                            'session_token' => $logData['session_token'],
                            'is_conflict' => $logData['is_conflict'],
                            'ip_address' => $logData['ip_address'] ?? null,
                            'access_time' => $logData['access_time'],
                        ]);

                        $synced[] = [
                            'cis_session_id' => $logData['cis_session_id'],
                            'cis_log_id' => $deviceLog->id,
                            'action' => 'created',
                        ];
                    } catch (\Exception $e) {
                        $failed[] = [
                            'cis_session_id' => $logData['cis_session_id'],
                            'error' => $e->getMessage(),
                        ];

                        Log::error('Failed to sync device log', [
                            'log_data' => $logData,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });

            Log::info('Device logs sync completed', [
                'synced_count' => count($synced),
                'failed_count' => count($failed),
            ]);

            return response()->json([
                'success' => true,
                'synced' => $synced,
                'failed' => $failed,
                'summary' => [
                    'total' => count($request->logs),
                    'synced' => count($synced),
                    'failed' => count($failed),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error syncing device logs', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error syncing device logs',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get session data for diagnostic purposes
     *
     * @param  int  $sessionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSession($sessionId)
    {
        try {
            $examSession = ExamSession::with(['exam', 'student.user', 'responses.question'])
                ->find($sessionId);

            if (! $examSession) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'session' => [
                    'id' => $examSession->id,
                    'exam_id' => $examSession->exam_id,
                    'exam_title' => $examSession->exam->title ?? 'Unknown',
                    'student_id' => $examSession->student->student_id ?? 'Unknown',
                    'student_name' => $examSession->student->user->name ?? 'Unknown',
                    'started_at' => $examSession->started_at,
                    'completed_at' => $examSession->completed_at,
                    'score' => $examSession->score,
                    'auto_submitted' => $examSession->auto_submitted,
                    'extra_time_minutes' => $examSession->extra_time_minutes,
                ],
                'responses' => $examSession->responses->map(function ($response) {
                    return [
                        'id' => $response->id,
                        'question_id' => $response->question_id,
                        'question_text' => $response->question->question ?? 'Question not found',
                        'selected_option' => $response->selected_option,
                        'option_id' => $response->option_id,
                        'is_correct' => $response->is_correct,
                    ];
                }),
                'student' => [
                    'id' => $examSession->student->user_id,
                    'student_id' => $examSession->student->student_id,
                    'name' => $examSession->student->user->name,
                    'email' => $examSession->student->user->email,
                ],
                'exam' => [
                    'id' => $examSession->exam->id,
                    'title' => $examSession->exam->title,
                    'duration_minutes' => $examSession->exam->duration_minutes,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving session data', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving session data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get session data with specific student validation
     *
     * @param  int  $sessionId
     * @param  string  $studentId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSessionWithStudent($sessionId, $studentId)
    {
        try {
            // First, find the Student record to get the user_id
            $student = Student::where('student_id', $studentId)->first();
            
            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student not found',
                ], 404);
            }
            
            // Now find the ExamSession using the user_id from the student record
            $examSession = ExamSession::with(['exam', 'student', 'responses.question'])
                ->where('id', $sessionId)
                ->where('student_id', $student->user_id) // ExamSession.student_id is actually user_id
                ->first();

            if (! $examSession) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session not found for this student',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'session' => [
                    'id' => $examSession->id,
                    'exam_id' => $examSession->exam_id,
                    'exam_title' => $examSession->exam->title ?? 'Unknown',
                    'student_id' => $student->student_id,
                    'student_name' => $student->full_name,
                    'started_at' => $examSession->started_at,
                    'completed_at' => $examSession->completed_at,
                    'score' => $examSession->score,
                    'auto_submitted' => $examSession->auto_submitted,
                    'extra_time_minutes' => $examSession->extra_time_minutes,
                ],
                'responses' => $examSession->responses->map(function ($response) {
                    return [
                        'id' => $response->id,
                        'question_id' => $response->question_id,
                        'question_text' => $response->question->question ?? 'Question not found',
                        'selected_option' => $response->selected_option,
                        'option_id' => $response->option_id,
                        'is_correct' => $response->is_correct,
                    ];
                }),
                'student' => [
                    'id' => $student->user_id,
                    'student_id' => $student->student_id,
                    'name' => $student->full_name,
                    'email' => $student->email,
                ],
                'exam' => [
                    'id' => $examSession->exam->id,
                    'title' => $examSession->exam->title,
                    'duration_minutes' => $examSession->exam->duration_minutes,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving session data with student validation', [
                'session_id' => $sessionId,
                'student_id' => $studentId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving session data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
