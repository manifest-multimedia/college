<?php

namespace App\Livewire;

use App\Models\Student;
use App\Models\Exam;
use App\Models\ExamSession;
use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Helpers\DeviceDetector;
use Carbon\Carbon;

class ExamLogin extends Component
{
    public $studentId;
    public $examPassword;
    public $deviceConflict = false;

    public function mount()
    {
        // check environment set values for local
        if (env('APP_ENV') == 'local') {
            // $this->studentId = "PNMTC/DA/RGN/24/25/001";
            $this->examPassword = "vaQTusuK";
        }
    }

    public function render()
    {
        return view('livewire.exam-login');
    }

    /**
     * Generate a unique device identifier
     */
    private function getDeviceInfo()
    {
        $detector = new DeviceDetector();
        
        return json_encode($detector->getDeviceInfo());
    }

    public function startExam()
    {
        $this->validate([
            'studentId' => 'required',
            'examPassword' => 'required',
        ]);

        $student = Student::where('student_id', $this->studentId)->first();

        if (!$student) {
            session()->flash('error', 'Invalid Student ID');
            return;
        }

        $exam = Exam::where('password', $this->examPassword)->first();

        if (!$exam) {
            session()->flash('error', 'Invalid Exam Password');
            return;
        }

        // Additional validation: Check if the student is eligible to take the exam
        // if (!$student->isEligibleForExam()) {
        //     session()->flash('error', "
        //     Dear " . $student->first_name . ",
        //     You are not eligible to take this exam. You have pending fees to clear. Please see the accounts Office for clearance.");
        //     return;
        // }

        // Create or find User for Student
        try {
            // Check if Student has user account, else create one
            $user = User::where('email', $student->email)->first();
            
            if (!$user) {
                $student->createUser();
                $user = User::where('email', $student->email)->first();
            }
            
            if (!$user) {
                Log::error('Failed to create or find user for student', [
                    'student_id' => $student->student_id,
                    'email' => $student->email
                ]);
                session()->flash('error', 'System error: Unable to initialize exam session. Please contact support.');
                return;
            }

            // Generate a unique session token for this device access
            $sessionToken = Str::random(40);
            $deviceInfo = $this->getDeviceInfo();
            
            // Store device token in session for validation during exam
            session(['exam_session_token' => $sessionToken]);

            // Check if there's an existing active exam session
            $existingSession = ExamSession::where('exam_id', $exam->id)
                ->where('student_id', $user->id)
                ->first();
                
            if ($existingSession) {
                // Check if this session is being accessed from another device
                if ($existingSession->isBeingAccessedFromDifferentDevice($sessionToken, $deviceInfo)) {
                    Log::warning('Attempt to access exam from multiple devices detected', [
                        'session_id' => $existingSession->id,
                        'student_id' => $student->student_id,
                        'current_device' => $deviceInfo,
                        'saved_device' => $existingSession->device_info
                    ]);
                    
                    $this->deviceConflict = true;
                    session()->flash('error', 'This exam is already in progress on another device. You can only access the exam from one device at a time.');
                    return;
                }
                
                // Check if the session has a completed_at date in the future (restored session)
                if ($existingSession->completed_at && $existingSession->completed_at->isFuture()) {
                    Log::info('Restored exam session detected', [
                        'session_id' => $existingSession->id,
                        'student_id' => $student->student_id,
                        'completed_at' => $existingSession->completed_at->toDateTimeString(),
                        'extra_time_minutes' => $existingSession->extra_time_minutes
                    ]);
                    
                    // Update device access info for this restored session
                    $existingSession->updateDeviceAccess($sessionToken, $deviceInfo);
                }
                // If the adjusted completion time is in the past, the session has expired
                elseif ($existingSession->adjustedCompletionTime->isPast()) {
                    Log::info('Existing session found but expired', [
                        'session_id' => $existingSession->id,
                        'student_id' => $student->student_id,
                        'expired_at' => $existingSession->adjustedCompletionTime
                    ]);
                    
                    // Auto-complete the expired session if it wasn't completed
                    if (!$existingSession->completed_at) {
                        $existingSession->update([
                            'completed_at' => now(),
                            'auto_submitted' => true
                        ]);
                    }
                } else {
                    // Update device access info for this session
                    $existingSession->updateDeviceAccess($sessionToken, $deviceInfo);
                }
            }
            
            return redirect()->route('exams', [
                'slug' => $exam->slug,
                'student_id' => $student->id
            ]);
        } catch (\Throwable $th) {
            Log::error('Error in startExam', [
                'error' => $th->getMessage(),
                'student_id' => $student->student_id ?? null,
                'exam_id' => $exam->id ?? null
            ]);
            
            session()->flash('error', 'An error occurred. Please try again or contact support.');
        }
    }
}
