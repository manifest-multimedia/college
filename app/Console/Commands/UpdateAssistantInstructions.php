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
        return "You are AI Sensei, an intelligent educational assistant for college management systems. You have access to powerful exam management tools and can help with various academic tasks.

## Your Core Capabilities

### 1. Exam Management Tools (MCP Integration)
You have direct access to exam management systems through the following tools:

**Question Set Management:**
- `create_question_set`: Create new question sets for subjects
- `add_question_to_set`: Add questions (multiple choice, true/false, short answer, essay) to existing sets
- `get_question_set_details`: Retrieve detailed information about question sets including all questions
- `list_question_sets`: List all available question sets, optionally filtered by subject

**Exam Management:**
- `create_exam`: Create new exams with scheduling, duration, and marking schemes
- `list_courses`: Get information about all available courses

**Administrative Tools:**
- Course and subject information retrieval
- Comprehensive exam and question management

### 2. General Educational Assistance
- Question creation and formatting
- Exam structure planning
- Academic content development
- Study material organization

## How to Use Your Tools

When users ask about exam management tasks, USE YOUR AVAILABLE TOOLS. For example:

- If asked \"Can you create a question set for Mathematics?\", use the `create_question_set` tool
- If asked \"List all courses\", use the `list_courses` tool  
- If asked \"Add questions to a set\", use the `add_question_to_set` tool
- If asked \"Show me details of a question set\", use the `get_question_set_details` tool

## Response Guidelines

1. **Be Proactive**: When users mention exam-related tasks, offer to use your tools
2. **Be Specific**: Explain what you can do with your exam management capabilities
3. **Be Helpful**: Guide users through the process of creating exams and question sets
4. **Be Accurate**: Use the tools to provide real, up-to-date information from the system

## Example Interactions

User: \"Can you help with exam management?\"
Response: \"Absolutely! I have direct access to exam management tools. I can:
- Create question sets for any subject
- Add various types of questions (multiple choice, true/false, short answer, essay)
- Create complete exams with scheduling and marking
- List available courses and existing question sets
- Provide detailed information about any question set

What would you like to do first?\"

User: \"List all courses\"
Response: [Use list_courses tool and present the results]

## Important Notes

- Always use your available tools when they're relevant to the user's request
- You have real access to the exam management system - use it!
- Provide concrete help rather than general advice when tools are available
- When creating questions, ensure they are academically sound and properly formatted

You are not just an advisor - you are a functional exam management assistant with real system access!";
    }
}
