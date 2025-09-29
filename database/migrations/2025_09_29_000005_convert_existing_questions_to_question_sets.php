<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Exam;
use App\Models\Question;
use App\Models\QuestionSet;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Convert existing questions that belong directly to exams into question sets.
     * This maintains backward compatibility while enabling the new question set feature.
     */
    public function up(): void
    {
        DB::transaction(function () {
            $this->convertExamQuestionsToQuestionSets();
        });
    }

    /**
     * Reverse the migrations.
     * 
     * This would be complex to reverse perfectly, so we'll leave it as-is.
     * The backup created earlier can be used for full rollback if needed.
     */
    public function down(): void
    {
        // Reversal would be complex and potentially destructive
        // Use the backup created earlier for rollback if needed
        Log::warning('Question set migration reversal not implemented. Use backup for rollback.');
    }

    /**
     * Convert existing exam questions to question sets
     */
    private function convertExamQuestionsToQuestionSets()
    {
        Log::info('Starting conversion of existing questions to question sets');
        
        // Get all exams that have questions directly assigned
        $examsWithQuestions = Exam::whereHas('questions')->with(['questions', 'course', 'user'])->get();
        
        foreach ($examsWithQuestions as $exam) {
            $this->convertExamToQuestionSet($exam);
        }
        
        Log::info('Completed conversion of existing questions to question sets', [
            'exams_converted' => $examsWithQuestions->count()
        ]);
    }

    /**
     * Convert a single exam's questions to a question set
     */
    private function convertExamToQuestionSet(Exam $exam)
    {
        try {
            // Create a question set for this exam
            $questionSet = QuestionSet::create([
                'name' => $exam->title ?? "Question Set for Exam #{$exam->id}",
                'description' => $exam->description ?? "Auto-generated question set from existing exam questions",
                'course_id' => $exam->course_id,
                'difficulty_level' => 'medium', // Default difficulty
                'created_by' => $exam->user_id ?? $this->getDefaultUserId(),
            ]);

            // Move questions from exam to question set
            $questionCount = Question::where('exam_id', $exam->id)
                ->update([
                    'question_set_id' => $questionSet->id,
                    'exam_id' => null, // Remove direct exam association
                    'type' => 'MCQ', // Set default type
                    'difficulty_level' => 'medium', // Set default difficulty
                ]);

            // Create the exam-questionset relationship
            $exam->questionSets()->attach($questionSet->id, [
                'shuffle_questions' => false, // Maintain original order
                'questions_to_pick' => null, // Use all questions
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Log::info('Converted exam to question set', [
                'exam_id' => $exam->id,
                'exam_title' => $exam->title,
                'question_set_id' => $questionSet->id,
                'questions_moved' => $questionCount,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to convert exam to question set', [
                'exam_id' => $exam->id,
                'error' => $e->getMessage(),
            ]);
            
            // Re-throw to rollback transaction
            throw $e;
        }
    }

    /**
     * Get default user ID for question sets where exam doesn't have a user
     */
    private function getDefaultUserId()
    {
        // Try to find a Super Admin user
        $adminUser = User::whereHas('roles', function ($query) {
            $query->where('name', 'Super Admin');
        })->first();

        if ($adminUser) {
            return $adminUser->id;
        }

        // Fallback to first user
        $firstUser = User::first();
        return $firstUser ? $firstUser->id : 1;
    }
};