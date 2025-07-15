<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\CourseRegistration;
use App\Models\StudentFeeBill;
use App\Models\ExamSession;
use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StudentDashboardController extends Controller
{
    /**
     * Display the student dashboard.
     */
    public function index()
    {
        try {
            // Get the current authenticated user
            $user = Auth::user();
            
            // Find the student record associated with this user
            $student = Student::where('email', $user->email)->first();
            
            if (!$student) {
                // If no student record found, show a message
                return view('students.dashboard', [
                    'student' => null,
                    'enrolledCourses' => 0,
                    'paymentPercentage' => 0,
                    'examsTaken' => 0,
                    'outstandingBalance' => 0,
                    'registeredCourses' => collect(),
                ]);
            }
            
            // Get current academic year and semester
            $currentAcademicYear = AcademicYear::where('is_current', true)->first();
            $currentSemester = Semester::where('is_current', true)->first();
            
            if (!$currentAcademicYear || !$currentSemester) {
                Log::warning('No current academic year or semester set');
                // Use defaults if no current ones are set
                $currentAcademicYear = AcademicYear::latest()->first();
                $currentSemester = Semester::latest()->first();
            }
            
            // Get enrolled courses count for current semester
            $enrolledCourses = 0;
            $registeredCourses = collect();
            
            if ($currentAcademicYear && $currentSemester) {
                $registeredCourses = CourseRegistration::where('student_id', $student->id)
                    ->where('academic_year_id', $currentAcademicYear->id)
                    ->where('semester_id', $currentSemester->id)
                    ->with(['subject'])
                    ->get();
                
                $enrolledCourses = $registeredCourses->count();
            }
            
            // Get payment information
            $paymentPercentage = 0;
            $outstandingBalance = 0;
            
            if ($currentAcademicYear && $currentSemester) {
                $feeBill = StudentFeeBill::where('student_id', $student->id)
                    ->where('academic_year_id', $currentAcademicYear->id)
                    ->where('semester_id', $currentSemester->id)
                    ->first();
                
                if ($feeBill) {
                    $paymentPercentage = $feeBill->payment_percentage ?? 0;
                    $outstandingBalance = $feeBill->balance ?? 0;
                }
            }
            
            // Get exams taken (using the relationship through User)
            $examsTaken = 0;
            try {
                // Find the User record associated with this student's email
                $userRecord = \App\Models\User::where('email', $student->email)->first();
                if ($userRecord) {
                    $examsTaken = ExamSession::where('student_id', $userRecord->id)
                        ->whereNotNull('completed_at')
                        ->count();
                }
            } catch (\Exception $e) {
                Log::warning('Error fetching exam sessions: ' . $e->getMessage());
                $examsTaken = 0;
            }
            
            return view('students.dashboard', [
                'student' => $student,
                'enrolledCourses' => $enrolledCourses,
                'paymentPercentage' => $paymentPercentage,
                'examsTaken' => $examsTaken,
                'outstandingBalance' => $outstandingBalance,
                'registeredCourses' => $registeredCourses,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error loading student dashboard: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'user_email' => Auth::user()->email ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return a safe fallback view
            return view('students.dashboard', [
                'student' => null,
                'enrolledCourses' => 0,
                'paymentPercentage' => 0,
                'examsTaken' => 0,
                'outstandingBalance' => 0,
                'registeredCourses' => collect(),
            ]);
        }
    }
}
