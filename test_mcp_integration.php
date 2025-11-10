<?php

require_once 'vendor/autoload.php';

use App\Services\Communication\Chat\MCPIntegrationService;
use App\Services\Communication\Chat\OpenAI\OpenAIAssistantsService;
use App\Services\Communication\Chat\MCP\ExamManagementMCPService;
use Illuminate\Support\Facades\Config;

/**
 * Test script to verify MCP integration with OpenAI Assistant
 * 
 * This script tests:
 * 1. MCP tool registration with OpenAI Assistant
 * 2. Function call execution
 * 3. End-to-end exam management workflow
 */

echo "🧪 MCP Integration Testing Suite\n";
echo "================================\n\n";

// Initialize services
try {
    $mcpService = new ExamManagementMCPService();
    $assistantService = new OpenAIAssistantsService();
    $integrationService = new MCPIntegrationService($mcpService, $assistantService);
    
    echo "✅ Services initialized successfully\n\n";
} catch (Exception $e) {
    echo "❌ Failed to initialize services: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 1: Get MCP tools configuration
echo "📋 Test 1: MCP Tools Configuration\n";
echo "-----------------------------------\n";
try {
    $tools = $integrationService->getMCPToolsConfig();
    echo "✅ Found " . count($tools) . " MCP tools:\n";
    foreach ($tools as $tool) {
        echo "  • " . $tool['function']['name'] . ": " . $tool['function']['description'] . "\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "❌ Failed to get tools config: " . $e->getMessage() . "\n\n";
}

// Test 2: Function call processing
echo "🔧 Test 2: Function Call Processing\n";
echo "-----------------------------------\n";

// Test list_courses function
try {
    echo "Testing list_courses...\n";
    $result = $integrationService->processFunctionCall('list_courses', []);
    if ($result['success']) {
        echo "✅ list_courses: Found " . count($result['data']) . " courses\n";
    } else {
        echo "❌ list_courses failed: " . $result['error'] . "\n";
    }
} catch (Exception $e) {
    echo "❌ list_courses exception: " . $e->getMessage() . "\n";
}

// Test list_question_sets function
try {
    echo "Testing list_question_sets...\n";
    $result = $integrationService->processFunctionCall('list_question_sets', []);
    if ($result['success']) {
        echo "✅ list_question_sets: Found " . count($result['data']) . " question sets\n";
    } else {
        echo "❌ list_question_sets failed: " . $result['error'] . "\n";
    }
} catch (Exception $e) {
    echo "❌ list_question_sets exception: " . $e->getMessage() . "\n";
}

// Test create_question_set function (only if we have subjects)
try {
    echo "Testing create_question_set...\n";
    $subjects = \App\Models\Subject::take(1)->get();
    if ($subjects->count() > 0) {
        $result = $integrationService->processFunctionCall('create_question_set', [
            'subject_id' => $subjects->first()->id,
            'title' => 'MCP Integration Test Set - ' . date('Y-m-d H:i:s'),
            'description' => 'Test question set created by MCP integration testing'
        ]);
        
        if ($result['success']) {
            echo "✅ create_question_set: Created question set ID " . $result['data']['id'] . "\n";
            $testQuestionSetId = $result['data']['id'];
            
            // Test adding a question to the set
            echo "Testing add_question_to_set...\n";
            $questionResult = $integrationService->processFunctionCall('add_question_to_set', [
                'question_set_id' => $testQuestionSetId,
                'question_text' => 'What is 2 + 2?',
                'question_type' => 'multiple_choice',
                'options' => ['A) 3', 'B) 4', 'C) 5', 'D) 6'],
                'correct_answer' => 'B) 4',
                'marks' => 5
            ]);
            
            if ($questionResult['success']) {
                echo "✅ add_question_to_set: Added question ID " . $questionResult['data']['id'] . "\n";
            } else {
                echo "❌ add_question_to_set failed: " . $questionResult['error'] . "\n";
            }
            
        } else {
            echo "❌ create_question_set failed: " . $result['error'] . "\n";
        }
    } else {
        echo "⏭️ Skipping create_question_set: No subjects found\n";
    }
} catch (Exception $e) {
    echo "❌ create_question_set exception: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Assistant Integration
echo "🤖 Test 3: Assistant Integration\n";
echo "--------------------------------\n";
$assistantId = Config::get('services.openai.assistant_id');

if ($assistantId) {
    try {
        echo "Updating assistant {$assistantId} with MCP tools...\n";
        $result = $integrationService->updateAssistantWithMCPTools($assistantId);
        
        if ($result['success']) {
            echo "✅ Assistant successfully updated with MCP tools!\n";
        } else {
            echo "❌ Failed to update assistant: " . $result['message'] . "\n";
        }
    } catch (Exception $e) {
        echo "❌ Assistant update exception: " . $e->getMessage() . "\n";
    }
} else {
    echo "⏭️ Skipping assistant update: No OPENAI_ASSISTANT_ID configured\n";
}

echo "\n";

// Test 4: Error handling
echo "🚨 Test 4: Error Handling\n";
echo "-------------------------\n";

// Test invalid function call
try {
    $result = $integrationService->processFunctionCall('invalid_function', []);
    if (!$result['success']) {
        echo "✅ Error handling: Invalid function properly rejected\n";
    } else {
        echo "❌ Error handling: Invalid function was accepted\n";
    }
} catch (Exception $e) {
    echo "✅ Error handling: Exception properly thrown for invalid function\n";
}

// Test invalid parameters
try {
    $result = $integrationService->processFunctionCall('create_question_set', [
        'invalid_param' => 'value'
    ]);
    if (!$result['success']) {
        echo "✅ Error handling: Invalid parameters properly rejected\n";
    } else {
        echo "❌ Error handling: Invalid parameters were accepted\n";
    }
} catch (Exception $e) {
    echo "✅ Error handling: Exception properly thrown for invalid parameters\n";
}

echo "\n";

echo "🎉 MCP Integration Testing Complete!\n";
echo "===================================\n\n";
echo "Next Steps:\n";
echo "1. Run: php artisan ai:update-assistant-mcp\n";
echo "2. Test the AI Assistant in your chat interface\n";
echo "3. Try asking: 'Create a question set for Mathematics'\n";
echo "4. Try asking: 'List all available courses'\n";
echo "5. Try asking: 'Create an exam for [course name]'\n\n";
echo "Your AI Assistant now has powerful exam management capabilities! 🚀\n";