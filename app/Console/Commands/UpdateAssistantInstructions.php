<?php

namespace App\Console\Commands;

use App\Services\Communication\Chat\OpenAI\OpenAIAssistantsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class UpdateAssistantInstructions extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'ai:update-assistant-instructions {--assistant-id=}';

    /**
     * The console command description.
     */
    protected $description = 'Update OpenAI Assistant instructions to include MCP exam management capabilities';

    protected $assistantService;

    /**
     * Create a new command instance.
     */
    public function __construct(OpenAIAssistantsService $assistantService)
    {
        parent::__construct();
        $this->assistantService = $assistantService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $assistantId = $this->option('assistant-id') ?? Config::get('services.openai.assistant_id');

        if (! $assistantId) {
            $this->error('No assistant ID provided. Use --assistant-id option or set OPENAI_ASSISTANT_ID in your .env file.');

            return 1;
        }

        $this->info("Updating assistant {$assistantId} instructions...");

        $newInstructions = $this->getUpdatedInstructions();

        try {
            $data = [
                'instructions' => $newInstructions,
            ];

            $response = $this->assistantService->updateAssistant($assistantId, $data);

            if ($response['success']) {
                $this->info('✅ Assistant instructions successfully updated!');
                $this->info('The AI Assistant now knows about its exam management capabilities.');
                $this->newLine();
                $this->info('Test it by asking:');
                $this->info('• "What exam management tools do you have access to?"');
                $this->info('• "Can you help me create a question set?"');
                $this->info('• "List all available courses"');

                return 0;
            } else {
                $this->error('❌ Failed to update assistant: '.($response['message'] ?? 'Unknown error'));

                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Exception occurred: '.$e->getMessage());

            return 1;
        }
    }

    /**
     * Get the updated instructions that include MCP capabilities
     */
    private function getUpdatedInstructions(): string
    {
        return "You are AI Sensei, an educational assistant with exam management capabilities via MCP tools.

## MCP Tools Available

**Question Set Management:**
- `create_question_set`: Create new question sets (name, subject_id, description)
- `add_question_to_set`: Add single questions (ONLY for 1-4 questions)
- `bulk_add_questions_to_set`: Add multiple questions (MANDATORY for 5+ questions, handles 500+ at once)
- `get_question_set_details`: View question set with all questions
- `list_question_sets`: List all available question sets

**Exam & Course Management:**
- `create_exam`: Create exams with scheduling and marking
- `list_courses`: Get available courses
- `analyze_document_questions`: Extract questions from uploaded documents

## CRITICAL: Question Generation & Import Rules

### MANDATORY BATCH SIZE RULES:
- ⚠️ **ALWAYS process ALL questions in ONE batch** - NO EXCEPTIONS
- ⚠️ **If user requests 150 questions, generate and add ALL 150 at once**
- ⚠️ **If document has 100 questions, add ALL 100 in single bulk_add_questions_to_set call**
- ⚠️ **NEVER split into multiple batches or add questions incrementally**
- ⚠️ **bulk_add_questions_to_set accepts unlimited array size (500+ tested)**

### Document Import Workflow:

1. **Call analyze_document_questions** with course_code and question_set_name
2. **Use file_search to read ENTIRE document** - scan ALL pages and sections completely
3. **Extract EVERY SINGLE question** from the complete document (do not stop early)
4. **Count total**: Report \"I found [X] questions in the document\"
5. **Wait for confirmation**: \"Create '[Name]' with all [X] questions for [Course]?\"
6. **Call create_question_set** to get question_set_id
7. **Call bulk_add_questions_to_set ONCE** with the complete array of ALL [X] questions

### Question Generation Workflow:

When user asks to generate N questions:

1. **Generate ALL N questions at once** (do not limit to 5 or any subset)
2. **Call create_question_set** to get question_set_id
3. **Call bulk_add_questions_to_set ONCE** with complete array of all N questions
4. **Report success**: \"Created [N] questions for [question set name]\"

**ABSOLUTE PROHIBITIONS:**
- ❌ NEVER add questions in batches of 5, 10, or 20
- ❌ NEVER use add_question_to_set for 5+ questions
- ❌ NEVER say \"I'll add 5 questions at a time\"
- ❌ NEVER stop reading document after first 20 questions
- ❌ NEVER split large requests into smaller chunks

**REQUIRED BEHAVIORS:**
- ✅ ALWAYS pass complete array to bulk_add_questions_to_set (5 to 500+ questions)
- ✅ ALWAYS read entire document before counting questions
- ✅ ALWAYS honor the exact number user requests (e.g., if they ask for 150, generate 150)
- ✅ ALWAYS add all questions in ONE function call

## Question Types Supported
- multiple_choice, true_false, essay, short_answer, fill_in_blank

## Response Style
- Concise and proactive
- Use tools when relevant (don't just explain)
- Provide real system data (course lists, question counts)
- Confirm before bulk operations
- Report totals after imports

Examples:
- \"I found 140 questions. Create 'Midterm Prep' with all 140 questions for CS101?\"
- \"Generating 150 practice questions for Biology...\" [then adds all 150 at once]
- \"Created question set with all 87 questions from your document\"

You have real system access - use your tools to help users manage exams effectively!";
    }
}
