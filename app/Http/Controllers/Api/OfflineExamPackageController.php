<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OfflineExamPackageController extends Controller
{
    /**
     * Get available exams for offline download
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function available(Request $request)
    {
        try {
            // Get active exams that are ready for offline delivery
            $exams = Exam::where('status', 'active')
                ->with(['course'])
                ->select([
                    'id',
                    'course_id',
                    'duration',
                    'questions_per_session',
                    'passing_percentage',
                    'password',
                    'status',
                    'start_date',
                    'end_date',
                    'created_at',
                ])
                ->get()
                ->map(function ($exam) {
                    return [
                        'id' => $exam->id,
                        'title' => $exam->course->name ?? 'Unknown Course',
                        'course_code' => $exam->course->code ?? 'N/A',
                        'course_name' => $exam->course->name ?? 'Unknown',
                        'duration' => $exam->duration,
                        'questions_per_session' => $exam->questions_per_session,
                        'passing_percentage' => $exam->passing_percentage,
                        'password' => $exam->password,
                        'start_date' => $exam->start_date?->toIso8601String(),
                        'end_date' => $exam->end_date?->toIso8601String(),
                        'questions_count' => $exam->totalQuestionsCount,
                        'created_at' => $exam->created_at->toIso8601String(),
                    ];
                });

            return response()->json([
                'success' => true,
                'exams' => $exams,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching available exams', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching exams',
            ], 500);
        }
    }

    /**
     * Get complete exam package for offline delivery
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function package($id)
    {
        try {
            $exam = Exam::with([
                'course',
                'questions.options',
            ])->findOrFail($id);

            // Get all students who should have access to this exam
            // You may want to filter by specific criteria (e.g., class, department)
            $students = Student::select([
                'id',
                'student_id',
                'first_name',
                'last_name',
                'other_name',
                'email',
            ])->get();

            // Generate session questions for the exam
            $questions = $exam->generateSessionQuestions(false); // Don't shuffle for package

            $package = [
                'exam' => [
                    'id' => $exam->id,
                    'title' => $exam->course->name ?? 'Unknown Course',
                    'course_code' => $exam->course->code ?? 'N/A',
                    'course_name' => $exam->course->name ?? 'Unknown',
                    'duration' => $exam->duration,
                    'questions_per_session' => $exam->questions_per_session,
                    'passing_percentage' => $exam->passing_percentage,
                    'password' => $exam->password,
                    'start_date' => $exam->start_date?->toIso8601String(),
                    'end_date' => $exam->end_date?->toIso8601String(),
                ],
                'questions' => $questions->map(function ($question) {
                    return [
                        'id' => $question->id,
                        'question_text' => $question->question_text,
                        'correct_option' => $question->correct_option,
                        'mark' => $question->mark,
                        'explanation' => $question->explanation,
                        'options' => $question->options->map(function ($option) {
                            return [
                                'id' => $option->id,
                                'option_text' => $option->option_text,
                                'is_correct' => $option->is_correct,
                            ];
                        }),
                    ];
                }),
                'students' => $students->map(function ($student) {
                    return [
                        'id' => $student->id,
                        'student_id' => $student->student_id,
                        'first_name' => $student->first_name,
                        'last_name' => $student->last_name,
                        'other_name' => $student->other_name,
                        'email' => $student->email,
                    ];
                }),
                'metadata' => [
                    'downloaded_at' => now()->toIso8601String(),
                    'questions_count' => $questions->count(),
                    'students_count' => $students->count(),
                ],
            ];

            Log::info('Exam package generated', [
                'exam_id' => $exam->id,
                'questions_count' => $questions->count(),
                'students_count' => $students->count(),
            ]);

            return response()->json([
                'success' => true,
                'package' => $package,
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating exam package', [
                'exam_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error generating exam package',
            ], 500);
        }
    }
}
