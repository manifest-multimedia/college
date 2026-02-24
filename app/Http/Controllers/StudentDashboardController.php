<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\CourseRegistration;
use App\Models\ExamSession;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentFeeBill;
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

            if (! $student) {
                // If no student record found, show a message
                return view('students.dashboard', [
                    'student' => null,
                    'enrolledCourses' => 0,
                    'paymentPercentage' => 0,
                    'examsTaken' => 0,
                    'outstandingBalance' => 0,
                    'balanceDisplayType' => 'zero',
                    'balanceDisplayAmount' => 0,
                    'registeredCourses' => collect(),
                ]);
            }

            // Get current academic year and semester
            $currentAcademicYear = AcademicYear::where('is_current', true)->first();
            $currentSemester = Semester::where('is_current', true)->first();

            if (! $currentAcademicYear || ! $currentSemester) {
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

            // Get payment information (display: cap % at 100; balance: credit in brackets green, debit red, zero dark)
            $paymentPercentage = 0;
            $outstandingBalance = 0;
            $balanceDisplayType = 'zero'; // 'credit' | 'debit' | 'zero'
            $balanceDisplayAmount = 0.0;

            if ($currentAcademicYear && $currentSemester) {
                $feeBill = StudentFeeBill::where('student_id', $student->id)
                    ->where('academic_year_id', $currentAcademicYear->id)
                    ->where('semester_id', $currentSemester->id)
                    ->first();

                if ($feeBill) {
                    $paymentPercentage = $feeBill->display_payment_percentage;
                    $balanceDisplayType = $feeBill->balance_display_type;
                    $balanceDisplayAmount = $feeBill->balance_display_amount;
                    $outstandingBalance = $feeBill->balance_display_type === 'debit' ? $feeBill->balance : 0;
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
                Log::warning('Error fetching exam sessions: '.$e->getMessage());
                $examsTaken = 0;
            }

            return view('students.dashboard', [
                'student' => $student,
                'enrolledCourses' => $enrolledCourses,
                'paymentPercentage' => $paymentPercentage,
                'examsTaken' => $examsTaken,
                'outstandingBalance' => $outstandingBalance,
                'balanceDisplayType' => $balanceDisplayType,
                'balanceDisplayAmount' => $balanceDisplayAmount,
                'registeredCourses' => $registeredCourses,
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading student dashboard: '.$e->getMessage(), [
                'user_id' => Auth::id(),
                'user_email' => Auth::user()->email ?? 'unknown',
                'trace' => $e->getTraceAsString(),
            ]);

            // Return a safe fallback view
            return view('students.dashboard', [
                'student' => null,
                'enrolledCourses' => 0,
                'paymentPercentage' => 0,
                'examsTaken' => 0,
                'outstandingBalance' => 0,
                'balanceDisplayType' => 'zero',
                'balanceDisplayAmount' => 0,
                'registeredCourses' => collect(),
            ]);
        }
    }
}
