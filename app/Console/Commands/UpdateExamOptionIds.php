<?php

namespace App\Console\Commands;

use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Response;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateExamOptionIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exam:fix-option-ids {exam_id : The ID of the exam to fix} {--dry-run : Run without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix option ID mismatches for exams where options were updated during the test';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $examId = $this->argument('exam_id');
        $isDryRun = $this->option('dry-run');
        
        $exam = Exam::find($examId);
        
        if (!$exam) {
            $this->error("Exam with ID {$examId} not found.");
            return 1;
        }
        
        $this->info("Starting to process exam: {$exam->course->name} (ID: {$examId})");
        
        if ($isDryRun) {
            $this->warn("DRY RUN MODE: No actual changes will be made to the database");
        }
        
        // Get all exam sessions for this exam
        $examSessions = ExamSession::where('exam_id', $examId)->get();
        
        if ($examSessions->isEmpty()) {
            $this->warn("No exam sessions found for this exam.");
            return 0;
        }
        
        $this->info("Found {$examSessions->count()} exam sessions to process");
        
        $fixedCount = 0;
        $errorCount = 0;
        $skippedCount = 0;
        $alreadyMatchedCount = 0;
        
        // Create a progress bar for better visibility
        $progressBar = $this->output->createProgressBar($examSessions->count());
        $progressBar->start();
        
        foreach ($examSessions as $session) {
            try {
                // Process each response for the session
                $responses = $session->responses()->with(['question.options'])->get();
                
                foreach ($responses as $response) {
                    // Skip if no selected option or no question
                    if (!$response->selected_option || !$response->question) {
                        $skippedCount++;
                        continue;
                    }
                    
                    $question = $response->question;
                    $currentOptions = $question->options;
                    $selectedOptionId = $response->selected_option;
                    
                    // Check if the selected option matches any current option
                    $existingMatch = $currentOptions->firstWhere('id', $selectedOptionId);
                    if ($existingMatch) {
                        $alreadyMatchedCount++;
                        continue; // Already matched, no need to fix
                    }
                    
                    // Get the last 2 digits of the selected option
                    $selectedOptionSuffix = substr((string)$selectedOptionId, -2);
                    
                    // Find option with matching suffix
                    $matchingOption = $currentOptions->first(function ($option) use ($selectedOptionSuffix) {
                        $optionSuffix = substr((string)$option->id, -2);
                        return $optionSuffix === $selectedOptionSuffix;
                    });
                    
                    if ($matchingOption) {
                        // We found a matching option with the same last 2 digits but different ID
                        $this->line("\nFound mismatch for Response #{$response->id}: Selected {$selectedOptionId}, should be {$matchingOption->id}");
                        
                        if (!$isDryRun) {
                            $response->update([
                                'selected_option' => $matchingOption->id
                            ]);
                        }
                        
                        $fixedCount++; // Count as fixed regardless of dry run status
                    } else {
                        $this->warn("\nNo matching option found for Response #{$response->id} with selected option {$selectedOptionId}");
                        $skippedCount++;
                    }
                }
            } catch (\Exception $e) {
                $errorCount++;
                Log::error("Error processing exam session {$session->id}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                
                $this->error("\nError processing exam session {$session->id}: {$e->getMessage()}");
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine(2);
        
        if ($isDryRun) {
            $this->info("Dry run completed. Would have fixed {$fixedCount} responses.");
        } else {
            $this->info("Processing complete. Fixed {$fixedCount} responses.");
        }
        
        if ($alreadyMatchedCount > 0) {
            $this->line("{$alreadyMatchedCount} responses already had correct option IDs.");
        }
        
        if ($errorCount > 0) {
            $this->warn("{$errorCount} errors occurred during processing. Check logs for details.");
        }
        
        if ($skippedCount > 0) {
            $this->line("{$skippedCount} responses skipped (no selected option, no matching option found, or no question).");
        }
        
        return 0;
    }
}
