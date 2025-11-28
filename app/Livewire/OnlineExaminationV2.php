<?php

namespace App\Livewire;

use App\Helpers\DeviceDetector;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Question;
use App\Models\Response;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

class OnlineExaminationV2 extends Component
{
    public $exam;
    public $examSession;
    public $questions = [];
    public $flaggedQuestions = []; // Array of flagged question IDs
    public $user;
    public $student;
    public $isLoading = true;
    public $readOnlyMode = false;
    public $showModal = false;
    public $modalMessage = '';
    public $device;
    public $browser;
    public $platform;
    public $isRobot;
    public $deviceValidation = null;
    public $validationMessage = '';
    
    // V2 Optimization Properties
    public $lastSyncedAt;
    public $pendingSyncCount = 0;
    public $isOffline = false;
    public $syncStatus = 'synced'; // 'syncing', 'synced', 'offline', 'error'

    protected $listeners = ['submitExam'];

    public function mount($examPassword, $student_id)
    {
        $this->exam = Exam::where('password', $examPassword)->with('questions.options')->firstOrFail();
        
        // Get student using database ID (matches V1 behavior)
        $this->student = Student::find($student_id);
        if (!$this->student) {
            abort(404, 'Student not found');
        }
        
        $studentName = $this->student->first_name.' '.$this->student->last_name.' '.$this->student->other_name;
        
        $this->user = User::firstOrCreate(
            ['email' => $this->student->email],
            ['name' => $studentName]
        );

        // Device detection
        $deviceDetector = new DeviceDetector();
        $this->device = $deviceDetector->device();
        $this->browser = $deviceDetector->browser();
        $this->platform = $deviceDetector->platform();
        $this->isRobot = $deviceDetector->isBot();

        // Get or create exam session
        $this->examSession = ExamSession::where('exam_id', $this->exam->id)
            ->where('student_id', $this->user->id)
            ->first();

        if (!$this->examSession) {
            $this->createExamSession();
        } else {
            $this->validateDeviceAndSession();
        }

        $this->loadQuestions();
        $this->isLoading = false;
        $this->lastSyncedAt = now();
    }

    protected function createExamSession()
    {
        $currentTime = now();
        $sessionToken = session('exam_session_token') ?? \Illuminate\Support\Str::random(40);
        session(['exam_session_token' => $sessionToken]);
        $deviceInfo = json_encode((new DeviceDetector())->getDeviceInfo());

        $this->examSession = ExamSession::create([
            'exam_id' => $this->exam->id,
            'student_id' => $this->user->id,
            'started_at' => $currentTime,
            'completed_at' => null,
            'extra_time_minutes' => 0,
            'extra_time_added_at' => null,
            'auto_submitted' => false,
            'session_token' => $sessionToken,
            'device_info' => $deviceInfo,
            'last_activity' => $currentTime,
        ]);

        $this->deviceValidation = 'approved';
        $this->validationMessage = 'Session created successfully';
    }

    protected function validateDeviceAndSession()
    {
        $currentDeviceInfo = json_encode((new DeviceDetector())->getDeviceInfo());
        $sessionToken = session('exam_session_token');

        if (!$sessionToken) {
            $sessionToken = \Illuminate\Support\Str::random(40);
            session(['exam_session_token' => $sessionToken]);
        }

        // Check if exam is completed (read-only mode)
        if ($this->examSession->completed_at && !$this->examSession->completed_at->isFuture()) {
            $this->readOnlyMode = true;
            $this->deviceValidation = 'approved';
            $this->validationMessage = 'Exam completed';
            return;
        }

        // Validate device consistency
        if ($this->examSession->isBeingAccessedFromDifferentDevice($sessionToken, $currentDeviceInfo)) {
            $this->deviceValidation = 'flagged';
            $this->validationMessage = 'Device mismatch detected. Contact administrator.';
            $this->readOnlyMode = true;
            return;
        }

        $this->deviceValidation = 'approved';
        $this->validationMessage = 'Device validated successfully';
        $this->examSession->updateDeviceAccess($sessionToken, $currentDeviceInfo);
    }

    protected function loadQuestions()
    {
        // Check if this session already has defined questions from question sets
        $existingSessionQuestions = \App\Models\ExamSessionQuestion::where('exam_session_id', $this->examSession->id)
            ->orderBy('display_order')
            ->with('question.options')
            ->get();

        if ($existingSessionQuestions->isNotEmpty()) {
            $examQuestions = $existingSessionQuestions->pluck('question');
            
            Log::info('V2: Loading existing session questions', [
                'session_id' => $this->examSession->id,
                'question_count' => $examQuestions->count()
            ]);
        } else {
            // Generate new session questions using the exam model's method
            $examQuestions = $this->exam->generateSessionQuestions(true);
            
            // Store these questions in exam_session_questions for consistency across page loads
            $displayOrder = 1;
            foreach ($examQuestions as $question) {
                \App\Models\ExamSessionQuestion::create([
                    'exam_session_id' => $this->examSession->id,
                    'question_id' => $question->id,
                    'display_order' => $displayOrder++,
                ]);
            }
            
            Log::info('V2: Generated and stored new session questions', [
                'session_id' => $this->examSession->id,
                'question_count' => $examQuestions->count()
            ]);
        }

        // Load flagged questions for this session
        $this->flaggedQuestions = \App\Models\ExamSessionFlag::where('exam_session_id', $this->examSession->id)
            ->pluck('question_id')
            ->toArray();

        // Build questions array with responses
        foreach ($examQuestions as $question) {
            $response = Response::where('exam_session_id', $this->examSession->id)
                ->where('question_id', $question->id)
                ->where('student_id', $this->user->id)
                ->first();

            $selectedAnswer = $response ? (int) $response->selected_option : null;
            $isFlagged = in_array($question->id, $this->flaggedQuestions);
            
            $this->questions[] = [
                'id' => $question->id,
                'question_text' => $question->question_text,
                'question_type' => $question->question_type,
                'options' => $question->options->toArray(),
                'selected_answer' => $selectedAnswer,
                'is_flagged' => $isFlagged,
            ];
            
            // Debug log
            if ($selectedAnswer !== null) {
                Log::info('V2: Question with answer loaded', [
                    'question_id' => $question->id,
                    'selected_answer' => $selectedAnswer,
                    'response_id' => $response->id ?? null
                ]);
            }
        }
        
        // Log summary
        $answeredCount = collect($this->questions)->whereNotNull('selected_answer')->count();
        Log::info('V2: Questions loaded summary', [
            'session_id' => $this->examSession->id,
            'total_questions' => count($this->questions),
            'answered_questions' => $answeredCount,
            'question_ids' => collect($this->questions)->pluck('id')->toArray()
        ]);
    }

    /**
     * V2 Batch Sync Method - Optimized for bulk updates
     * This reduces database writes by 80-90% compared to V1
     */
    public function syncResponsesBatch($responses)
    {
        try {
            if ($this->readOnlyMode) {
                return ['success' => false, 'message' => 'Exam is in read-only mode'];
            }

            $syncedCount = 0;

            DB::transaction(function () use ($responses, &$syncedCount) {
                // Get valid question IDs for this exam session
                $validQuestionIds = \App\Models\ExamSessionQuestion::where('exam_session_id', $this->examSession->id)
                    ->pluck('question_id')
                    ->toArray();
                
                $dataToUpsert = [];
                
                foreach ($responses as $questionId => $answer) {
                    // Validate question belongs to this exam session
                    if (!in_array($questionId, $validQuestionIds)) {
                        Log::warning('V2: Attempted to save response for invalid question', [
                            'session_id' => $this->examSession->id,
                            'question_id' => $questionId,
                            'valid_questions' => $validQuestionIds
                        ]);
                        continue;
                    }

                    $dataToUpsert[] = [
                        'exam_session_id' => $this->examSession->id,
                        'question_id' => $questionId,
                        'student_id' => $this->user->id,
                        'selected_option' => $answer,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ];
                }

                if (!empty($dataToUpsert)) {
                    Response::upsert(
                        $dataToUpsert,
                        ['exam_session_id', 'question_id', 'student_id'],
                        ['selected_option', 'updated_at']
                    );
                    $syncedCount = count($dataToUpsert);
                }
            });

            $this->lastSyncedAt = now();
            $this->pendingSyncCount = 0;
            $this->syncStatus = 'synced';

            return [
                'success' => true,
                'synced_count' => $syncedCount,
                'last_synced_at' => $this->lastSyncedAt->toIso8601String()
            ];

        } catch (\Throwable $th) {
            Log::error('V2 Batch Sync Error', [
                'exam_session_id' => $this->examSession->id,
                'error' => $th->getMessage()
            ]);

            $this->syncStatus = 'error';
            
            return [
                'success' => false,
                'error' => $th->getMessage()
            ];
        }
    }

    /**
     * V2 Batch Sync Method for Flags - Optimized for bulk flag operations
     * Syncs multiple flag operations in a single transaction
     */
    public function syncFlagsBatch($flags)
    {
        try {
            if ($this->readOnlyMode) {
                return ['success' => false, 'message' => 'Exam is in read-only mode'];
            }

            $syncedCount = 0;

            DB::transaction(function () use ($flags, &$syncedCount) {
                // Get valid question IDs for this exam session
                $validQuestionIds = \App\Models\ExamSessionQuestion::where('exam_session_id', $this->examSession->id)
                    ->pluck('question_id')
                    ->toArray();

                foreach ($flags as $questionId => $action) {
                    // Validate question belongs to this exam session
                    if (!in_array($questionId, $validQuestionIds)) {
                        Log::warning('V2: Attempted to flag invalid question', [
                            'session_id' => $this->examSession->id,
                            'question_id' => $questionId,
                        ]);
                        continue;
                    }

                    if ($action === 'flag') {
                        // Add flag
                        \App\Models\ExamSessionFlag::firstOrCreate([
                            'exam_session_id' => $this->examSession->id,
                            'question_id' => $questionId,
                        ]);
                        $syncedCount++;
                    } elseif ($action === 'unflag') {
                        // Remove flag
                        \App\Models\ExamSessionFlag::where('exam_session_id', $this->examSession->id)
                            ->where('question_id', $questionId)
                            ->delete();
                        $syncedCount++;
                    }
                }

                // Reload flagged questions
                $this->flaggedQuestions = \App\Models\ExamSessionFlag::where('exam_session_id', $this->examSession->id)
                    ->pluck('question_id')
                    ->toArray();
            });

            return [
                'success' => true,
                'synced_count' => $syncedCount,
                'flagged_questions' => $this->flaggedQuestions,
            ];

        } catch (\Throwable $th) {
            Log::error('V2 Batch Flag Sync Error', [
                'exam_session_id' => $this->examSession->id,
                'error' => $th->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $th->getMessage()
            ];
        }
    }

    /**
     * Toggle flag for a specific question (for immediate/fallback sync)
     */
    public function toggleFlag($questionId)
    {
        return $this->syncFlagsBatch([$questionId => in_array($questionId, $this->flaggedQuestions) ? 'unflag' : 'flag']);
    }

    /**
     * Clear response for a specific question
     * Handles both synced responses in database and pending unsynced responses
     */
    public function clearResponse($questionId)
    {
        try {
            if ($this->readOnlyMode) {
                return ['success' => false, 'message' => 'Exam is in read-only mode'];
            }

            // Validate question belongs to this exam session
            $validQuestionIds = \App\Models\ExamSessionQuestion::where('exam_session_id', $this->examSession->id)
                ->pluck('question_id')
                ->toArray();

            if (!in_array($questionId, $validQuestionIds)) {
                return ['success' => false, 'message' => 'Invalid question'];
            }

            // Try to delete the response from database
            $deleted = Response::where('exam_session_id', $this->examSession->id)
                ->where('question_id', $questionId)
                ->where('student_id', $this->user->id)
                ->delete();

            // Update local questions array regardless of database deletion
            // This handles pending unsynced responses
            foreach ($this->questions as $key => $question) {
                if ($question['id'] == $questionId) {
                    $this->questions[$key]['selected_answer'] = null;
                    break;
                }
            }

            Log::info('V2: Response cleared', [
                'session_id' => $this->examSession->id,
                'question_id' => $questionId,
                'was_in_database' => $deleted > 0,
            ]);

            return [
                'success' => true, 
                'message' => 'Response cleared successfully',
                'was_synced' => $deleted > 0,
            ];

        } catch (\Throwable $th) {
            Log::error('V2 Clear Response Error', [
                'exam_session_id' => $this->examSession->id,
                'question_id' => $questionId,
                'error' => $th->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $th->getMessage()
            ];
        }
    }

    /**
     * Update sync status from frontend
     */
    public function updateSyncStatus($status, $pendingCount = 0)
    {
        $this->syncStatus = $status;
        $this->pendingSyncCount = $pendingCount;
    }

    /**
     * Legacy method for backward compatibility - now just updates local state
     * Actual sync happens via batch in frontend
     */
    public function storeResponse($questionId, $answer)
    {
        // In V2, this is only called as fallback or for manual sync
        // The frontend handles batching automatically
        return $this->syncResponsesBatch([$questionId => $answer]);
    }

    public function getRemainingTimeProperty()
    {
        if (!$this->examSession) {
            return 0;
        }

        $examDuration = (int) $this->exam->duration;
        $extraTime = (int) ($this->examSession->extra_time_minutes ?? 0);
        $totalDuration = $examDuration + $extraTime;

        $startTime = Carbon::parse($this->examSession->started_at);
        $endTime = $startTime->copy()->addMinutes($totalDuration);
        $now = Carbon::now();

        if ($now->greaterThanOrEqualTo($endTime)) {
            return 0;
        }

        return $now->diffInSeconds($endTime);
    }

    #[On('auto-submit')]
    public function autoSubmit()
    {
        if ($this->examSession && !$this->examSession->completed_at) {
            $this->examSession->update([
                'completed_at' => now(),
                'auto_submitted' => true,
            ]);

            $this->readOnlyMode = true;
            $this->showModal = true;
            $this->modalMessage = 'Your exam has been automatically submitted due to time expiration.';
        }
    }

    /**
     * Called by timer component when exam time expires
     * This method is required for compatibility with the exam timer component
     */
    public function examTimeExpired()
    {
        try {
            if ($this->examSession && !$this->examSession->completed_at) {
                $timeExpiredAt = now();

                // Log the expiration
                Log::info('V2: Exam time expired - automatic submission triggered by timer component', [
                    'session_id' => $this->examSession->id,
                    'student_id' => $this->user->id,
                    'exam_id' => $this->exam->id,
                    'time' => $timeExpiredAt,
                ]);

                // Update the session with completion info
                $this->examSession->update([
                    'completed_at' => $timeExpiredAt,
                    'auto_submitted' => true,
                ]);

                $this->readOnlyMode = true;
                $this->showModal = true;
                $this->modalMessage = 'Time expired! Your exam has been automatically submitted.';

                // Return status for the JS callback
                return [
                    'success' => true,
                    'message' => 'Time expired! Your exam has been automatically submitted.',
                    'submittedAt' => $timeExpiredAt->toIso8601String(),
                ];
            }

            return [
                'success' => false,
                'message' => 'Exam already submitted',
            ];
        } catch (\Exception $e) {
            Log::error('V2: Error during exam time expiration', [
                'error' => $e->getMessage(),
                'session_id' => $this->examSession->id ?? null,
                'student_id' => $this->user->id ?? null,
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred during submission',
            ];
        }
    }

    public function submitExam()
    {
        if ($this->readOnlyMode) {
            session()->flash('error', 'Cannot submit exam in read-only mode.');
            return;
        }

        try {
            $answeredCount = Response::where('exam_session_id', $this->examSession->id)
                ->where('student_id', $this->user->id)
                ->whereNotNull('selected_option')
                ->count();

            DB::transaction(function () use ($answeredCount) {
                $this->examSession->update([
                    'completed_at' => now(),
                    'auto_submitted' => false,
                ]);

                Log::info('V2 Exam Submitted', [
                    'exam_session_id' => $this->examSession->id,
                    'student_id' => $this->user->id,
                    'answered_count' => $answeredCount,
                    'total_questions' => count($this->questions),
                    'last_sync' => $this->lastSyncedAt
                ]);
            });

            session()->flash('message', 'Exam submitted successfully.');
            return redirect()->route('take-exam');

        } catch (\Throwable $th) {
            Log::error('V2 Exam Submission Error', [
                'exam_session_id' => $this->examSession->id,
                'error' => $th->getMessage()
            ]);

            session()->flash('error', 'Failed to submit exam. Please try again.');
        }
    }

    public function getAnsweredQuestionsCountProperty()
    {
        return collect($this->questions)->whereNotNull('selected_answer')->count();
    }

    public function getTotalQuestionsProperty()
    {
        return count($this->questions);
    }

    #[Title('Online Examination - V2 (Optimized)')]
    public function render()
    {
        // Calculate adjusted completion time for timer
        $examDuration = (int) $this->exam->duration;
        $extraTime = (int) ($this->examSession->extra_time_minutes ?? 0);
        $totalDuration = $examDuration + $extraTime;
        $adjustedCompletionTime = Carbon::parse($this->examSession->started_at)->addMinutes($totalDuration);

        return view('livewire.online-examination-v2', [
            'remainingTime' => $this->remainingTime,
            'answeredCount' => $this->answeredQuestionsCount,
            'totalQuestions' => $this->totalQuestions,
            'adjustedCompletionTime' => $adjustedCompletionTime,
            'flaggedCount' => count($this->flaggedQuestions),
        ]);
    }
}
