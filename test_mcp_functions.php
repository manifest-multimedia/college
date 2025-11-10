#!/usr/bin/env php
<?php

/**
 * Simple MCP Server Function Tests
 * Tests each MCP function individually
 */

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🧪 MCP Server Function Tests\n";
echo "===========================\n\n";

// Test the MCP service directly without HTTP
$mcpService = new App\Services\Communication\Chat\MCP\ExamManagementMCPService();

echo "🔧 Available Tools:\n";
$tools = $mcpService->getTools();
foreach ($tools as $tool) {
    echo "  - {$tool['name']}: {$tool['description']}\n";
}
echo "\n";

// Test 1: List courses
echo "📚 Test 1: List Courses\n";
echo "----------------------\n";
$result = $mcpService->handleToolCall('list_courses', []);
echo "Result: " . json_encode($result, JSON_PRETTY_PRINT) . "\n\n";

// Test 2: Create Question Set
echo "📝 Test 2: Create Question Set\n";
echo "-----------------------------\n";
$createArgs = [
    'name' => 'Test Question Set - ' . date('Y-m-d H:i:s'),
    'description' => 'A test question set created via MCP',
    'course_code' => 'MOCK101', // Using mock course code
    'difficulty_level' => 'medium'
];
$result = $mcpService->handleToolCall('create_question_set', $createArgs);
echo "Arguments: " . json_encode($createArgs, JSON_PRETTY_PRINT) . "\n";
echo "Result: " . json_encode($result, JSON_PRETTY_PRINT) . "\n\n";

$questionSetId = null;
if ($result['success'] ?? false) {
    $questionSetId = $result['data']['question_set_id'] ?? null;
    echo "✅ Question Set Created with ID: {$questionSetId}\n\n";
    
    // Test 3: Add Question to Set
    echo "❓ Test 3: Add Question to Set\n";
    echo "-----------------------------\n";
    $questionArgs = [
        'question_set_id' => $questionSetId,
        'question_text' => 'What is the primary purpose of MCP (Model Context Protocol)?',
        'options' => [
            ['text' => 'To enable AI assistants to use tools and services', 'is_correct' => true],
            ['text' => 'To manage database connections', 'is_correct' => false],
            ['text' => 'To handle user authentication', 'is_correct' => false],
            ['text' => 'To process file uploads', 'is_correct' => false]
        ],
        'explanation' => 'MCP allows AI assistants to interact with external tools and services.',
        'marks' => 2,
        'difficulty_level' => 'medium'
    ];
    $result = $mcpService->handleToolCall('add_question_to_set', $questionArgs);
    echo "Arguments: " . json_encode($questionArgs, JSON_PRETTY_PRINT) . "\n";
    echo "Result: " . json_encode($result, JSON_PRETTY_PRINT) . "\n\n";
    
    if ($result['success'] ?? false) {
        echo "✅ Question Added with ID: {$result['data']['question_id']}\n\n";
        
        // Test 4: Get Question Set Details
        echo "🔍 Test 4: Get Question Set Details\n";
        echo "----------------------------------\n";
        $result = $mcpService->handleToolCall('get_question_set_details', [
            'question_set_id' => $questionSetId
        ]);
        echo "Result: " . json_encode($result, JSON_PRETTY_PRINT) . "\n\n";
        
        // Test 5: Create Exam
        echo "📋 Test 5: Create Exam\n";
        echo "---------------------\n";
        $examArgs = [
            'course_code' => 'MOCK101',
            'type' => 'quiz',
            'duration' => 60,
            'passing_percentage' => 70,
            'start_date' => date('c', strtotime('+1 day')),
            'end_date' => date('c', strtotime('+2 days')),
            'question_sets' => [
                [
                    'question_set_id' => $questionSetId,
                    'questions_to_pick' => 0,
                    'shuffle_questions' => true
                ]
            ]
        ];
        $result = $mcpService->handleToolCall('create_exam', $examArgs);
        echo "Arguments: " . json_encode($examArgs, JSON_PRETTY_PRINT) . "\n";
        echo "Result: " . json_encode($result, JSON_PRETTY_PRINT) . "\n\n";
    }
} else {
    echo "❌ Failed to create question set. Error: " . ($result['error'] ?? 'Unknown error') . "\n\n";
}

// Test 6: List Question Sets
echo "📋 Test 6: List Question Sets\n";
echo "----------------------------\n";
$result = $mcpService->handleToolCall('list_question_sets', []);
echo "Result: " . json_encode($result, JSON_PRETTY_PRINT) . "\n\n";

echo "🎉 MCP Function Testing Complete!\n";
echo "\nTo test with AI Assistant:\n";
echo "1. Configure your AI assistant with the MCP server\n";
echo "2. Use the configuration file: mcp-exam-server.json\n";
echo "3. Start MCP server: php artisan mcp:serve\n";
echo "4. Ask AI to create question sets and exams!\n";