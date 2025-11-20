<?php

namespace App\Console\Commands;

use App\Models\Subject;
use App\Services\Communication\Chat\MCPIntegrationService;
use Illuminate\Console\Command;

class TestMCPIntegration extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'ai:test-mcp-integration';

    /**
     * The console command description.
     */
    protected $description = 'Test MCP integration functionality';

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
        $this->info('🧪 MCP Integration Testing Suite');
        $this->info('================================');
        $this->newLine();

        // Test 1: Get MCP tools configuration
        $this->info('📋 Test 1: MCP Tools Configuration');
        $this->info('-----------------------------------');
        try {
            $tools = $this->mcpIntegrationService->getMCPToolsConfig();
            $this->info('✅ Found '.count($tools).' MCP tools:');
            foreach ($tools as $tool) {
                $this->info('  • '.$tool['function']['name'].': '.$tool['function']['description']);
            }
            $this->newLine();
        } catch (\Exception $e) {
            $this->error('❌ Failed to get tools config: '.$e->getMessage());
            $this->newLine();
        }

        // Test 2: Function call processing
        $this->info('🔧 Test 2: Function Call Processing');
        $this->info('-----------------------------------');

        // Test list_courses function
        try {
            $this->info('Testing list_courses...');
            $result = $this->mcpIntegrationService->processFunctionCall('list_courses', []);
            if ($result['success']) {
                $this->info('✅ list_courses: Found '.count($result['data']).' courses');
            } else {
                $this->error('❌ list_courses failed: '.$result['error']);
            }
        } catch (\Exception $e) {
            $this->error('❌ list_courses exception: '.$e->getMessage());
        }

        // Test list_question_sets function
        try {
            $this->info('Testing list_question_sets...');
            $result = $this->mcpIntegrationService->processFunctionCall('list_question_sets', []);
            if ($result['success']) {
                $this->info('✅ list_question_sets: Found '.count($result['data']).' question sets');
            } else {
                $this->error('❌ list_question_sets failed: '.$result['error']);
            }
        } catch (\Exception $e) {
            $this->error('❌ list_question_sets exception: '.$e->getMessage());
        }

        // Test create_question_set function (only if we have subjects)
        try {
            $this->info('Testing create_question_set...');
            $subjects = Subject::take(1)->get();
            if ($subjects->count() > 0) {
                $result = $this->mcpIntegrationService->processFunctionCall('create_question_set', [
                    'subject_id' => $subjects->first()->id,
                    'title' => 'MCP Integration Test Set - '.date('Y-m-d H:i:s'),
                    'description' => 'Test question set created by MCP integration testing',
                ]);

                if ($result['success']) {
                    $this->info('✅ create_question_set: Created question set ID '.$result['data']['id']);
                    $testQuestionSetId = $result['data']['id'];

                    // Test adding a question to the set
                    $this->info('Testing add_question_to_set...');
                    $questionResult = $this->mcpIntegrationService->processFunctionCall('add_question_to_set', [
                        'question_set_id' => $testQuestionSetId,
                        'question_text' => 'What is 2 + 2?',
                        'question_type' => 'multiple_choice',
                        'options' => ['A) 3', 'B) 4', 'C) 5', 'D) 6'],
                        'correct_answer' => 'B) 4',
                        'marks' => 5,
                    ]);

                    if ($questionResult['success']) {
                        $this->info('✅ add_question_to_set: Added question ID '.$questionResult['data']['id']);
                    } else {
                        $this->error('❌ add_question_to_set failed: '.$questionResult['error']);
                    }

                } else {
                    $this->error('❌ create_question_set failed: '.$result['error']);
                }
            } else {
                $this->warn('⏭️ Skipping create_question_set: No subjects found');
            }
        } catch (\Exception $e) {
            $this->error('❌ create_question_set exception: '.$e->getMessage());
        }

        $this->newLine();

        // Test 3: Error handling
        $this->info('🚨 Test 3: Error Handling');
        $this->info('-------------------------');

        // Test invalid function call
        try {
            $result = $this->mcpIntegrationService->processFunctionCall('invalid_function', []);
            if (! $result['success']) {
                $this->info('✅ Error handling: Invalid function properly rejected');
            } else {
                $this->error('❌ Error handling: Invalid function was accepted');
            }
        } catch (\Exception $e) {
            $this->info('✅ Error handling: Exception properly thrown for invalid function');
        }

        // Test invalid parameters
        try {
            $result = $this->mcpIntegrationService->processFunctionCall('create_question_set', [
                'invalid_param' => 'value',
            ]);
            if (! $result['success']) {
                $this->info('✅ Error handling: Invalid parameters properly rejected');
            } else {
                $this->error('❌ Error handling: Invalid parameters were accepted');
            }
        } catch (\Exception $e) {
            $this->info('✅ Error handling: Exception properly thrown for invalid parameters');
        }

        $this->newLine();
        $this->info('🎉 MCP Integration Testing Complete!');
        $this->info('===================================');
        $this->newLine();
        $this->info('Your AI Assistant now has powerful exam management capabilities! 🚀');
        $this->info('Try asking it:');
        $this->info('• "Create a question set for Mathematics"');
        $this->info('• "List all available courses"');
        $this->info('• "Create an exam for [course name]"');

        return 0;
    }
}
