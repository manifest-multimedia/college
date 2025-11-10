<?php

namespace App\Console\Commands;

use App\Services\Communication\Chat\MCPIntegrationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class UpdateAssistantWithMCPTools extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'ai:update-assistant-mcp {--assistant-id=}';

    /**
     * The console command description.
     */
    protected $description = 'Update OpenAI Assistant with MCP (Model Context Protocol) tools for exam management';

    protected $mcpIntegrationService;

    /**
     * Create a new command instance.
     */
    public function __construct(MCPIntegrationService $mcpIntegrationService)
    {
        parent::__construct();
        $this->mcpIntegrationService = $mcpIntegrationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $assistantId = $this->option('assistant-id') ?? Config::get('services.openai.assistant_id');

        if (!$assistantId) {
            $this->error('No assistant ID provided. Use --assistant-id option or set OPENAI_ASSISTANT_ID in your .env file.');
            return 1;
        }

        $this->info("Updating assistant {$assistantId} with MCP tools...");

        try {
            $response = $this->mcpIntegrationService->updateAssistantWithMCPTools($assistantId);

            if ($response['success']) {
                $this->info('✅ Assistant successfully updated with MCP tools!');
                $this->info('Available MCP tools:');
                $this->info('• create_question_set - Create new question sets');
                $this->info('• add_question_to_set - Add questions to existing sets');
                $this->info('• create_exam - Create new exams');
                $this->info('• list_courses - List available courses');
                $this->info('• list_question_sets - List question sets');
                $this->info('• get_question_set_details - Get detailed question set information');
                
                $this->newLine();
                $this->info('Your AI Assistant can now help with exam management tasks!');
                $this->info('Try asking it to: "Create a new question set for Mathematics" or "List all available courses"');
                
                return 0;
            } else {
                $this->error('❌ Failed to update assistant: ' . ($response['message'] ?? 'Unknown error'));
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Exception occurred: ' . $e->getMessage());
            return 1;
        }
    }
}