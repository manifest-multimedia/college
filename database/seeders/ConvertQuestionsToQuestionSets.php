<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Exam;
use App\Models\Question;
use Carbon\Carbon;

class ConvertQuestionsToQuestionSets extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Converting existing questions to question sets...');
        
        // Get all exams that have questions
        $exams = Exam::whereHas('questions')->with(['questions', 'course'])->get();
        
        $this->command->info("📊 Found {$exams->count()} exams with questions");
        
        $createdSets = 0;
        $convertedQuestions = 0;
        
        foreach ($exams as $exam) {
            $this->command->info("📝 Processing exam: {$exam->title} (ID: {$exam->id})");
            
            // Create a default question set for this exam
            $questionSetData = [
                'name' => "Question Set: {$exam->title}",
                'description' => "Auto-generated question set from exam: {$exam->title}. Contains all original questions from this exam.",
                'course_id' => $exam->course_id,
                'difficulty_level' => 'medium', // Default difficulty
                'created_by' => $exam->user_id ?? 1, // Use exam creator or default to admin
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
            
            // Insert question set
            $questionSetId = DB::table('question_sets')->insertGetId($questionSetData);
            $createdSets++;
            
            $this->command->info("  ✅ Created question set ID: {$questionSetId}");
            
            // Update all questions for this exam to belong to the new question set
            $questionIds = $exam->questions->pluck('id')->toArray();
            
            if (!empty($questionIds)) {
                DB::table('questions')
                    ->whereIn('id', $questionIds)
                    ->update([
                        'question_set_id' => $questionSetId,
                        'type' => 'MCQ', // Default to MCQ for existing questions
                        'difficulty_level' => 'medium', // Default difficulty
                        'updated_at' => Carbon::now(),
                    ]);
                
                $convertedQuestions += count($questionIds);
                $this->command->info("  📋 Converted {" . count($questionIds) . "} questions");
                
                // Create exam_question_set relationship
                DB::table('exam_question_set')->insert([
                    'exam_id' => $exam->id,
                    'question_set_id' => $questionSetId,
                    'shuffle_questions' => true, // Default to shuffled
                    'questions_to_pick' => null, // Pick all questions from the set
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                
                $this->command->info("  🔗 Created exam-questionset relationship");
            }
        }
        
        $this->command->info("✅ Conversion completed!");
        $this->command->info("📊 Summary:");
        $this->command->info("   - Question sets created: {$createdSets}");
        $this->command->info("   - Questions converted: {$convertedQuestions}");
        $this->command->info("   - Exam relationships created: {$createdSets}");
        
        // Verify the conversion
        $orphanedQuestions = Question::whereNull('question_set_id')->whereNotNull('exam_id')->count();
        if ($orphanedQuestions > 0) {
            $this->command->warn("⚠️  Warning: {$orphanedQuestions} questions still have no question set assigned");
        } else {
            $this->command->info("✅ All questions successfully assigned to question sets");
        }
    }
}
