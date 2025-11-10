<?php

/**
 * MCP Server Test Script
 * 
 * This script tests the MCP (Model Context Protocol) server functionality
 * for question set and exam management.
 */

require_once __DIR__ . '/vendor/autoload.php';

class MCPServerTester
{
    private $serverUrl;
    private $testResults = [];

    public function __construct($serverUrl = 'http://localhost:3002')
    {
        $this->serverUrl = $serverUrl;
    }

    /**
     * Run all tests
     */
    public function runAllTests()
    {
        echo "🧪 MCP Server Test Suite\n";
        echo "======================\n\n";

        // Test server health
        $this->testServerHealth();
        
        // Test course listing
        $this->testListCourses();
        
        // Test question set creation
        $questionSetId = $this->testCreateQuestionSet();
        
        if ($questionSetId) {
            // Test adding questions to the set
            $this->testAddQuestions($questionSetId);
            
            // Test getting question set details
            $this->testGetQuestionSetDetails($questionSetId);
            
            // Test creating an exam with the question set
            $this->testCreateExam($questionSetId);
        }
        
        // Test listing question sets
        $this->testListQuestionSets();
        
        // Print summary
        $this->printSummary();
    }

    /**
     * Test server health
     */
    private function testServerHealth()
    {
        echo "📡 Testing server health...\n";
        
        try {
            $response = $this->makeHttpRequest('GET', '/health');
            
            if ($response && isset($response['status']) && $response['status'] === 'healthy') {
                $this->logSuccess("Server is healthy");
            } else {
                $this->logError("Server health check failed");
            }
        } catch (Exception $e) {
            $this->logError("Could not connect to server: " . $e->getMessage());
        }
    }

    /**
     * Test listing courses
     */
    private function testListCourses()
    {
        echo "\n📚 Testing course listing...\n";
        
        $response = $this->makeMCPRequest('tools/call', [
            'name' => 'list_courses',
            'arguments' => []
        ]);
        
        if ($response && $response['success']) {
            $courses = $response['data'];
            $this->logSuccess("Listed " . count($courses) . " courses");
            
            // Display first few courses
            foreach (array_slice($courses, 0, 3) as $course) {
                echo "  - {$course['course_code']}: {$course['name']}\n";
            }
        } else {
            $this->logError("Failed to list courses: " . ($response['error'] ?? 'Unknown error'));
        }
    }

    /**
     * Test creating a question set
     */
    private function testCreateQuestionSet()
    {
        echo "\n📝 Testing question set creation...\n";
        
        $response = $this->makeMCPRequest('tools/call', [
            'name' => 'create_question_set',
            'arguments' => [
                'name' => 'MCP Test Question Set - ' . date('Y-m-d H:i:s'),
                'description' => 'This is a test question set created via MCP server',
                'course_code' => 'TEST101',
                'difficulty_level' => 'medium'
            ]
        ]);
        
        if ($response && $response['success']) {
            $questionSetId = $response['data']['question_set_id'];
            $this->logSuccess("Created question set with ID: " . $questionSetId);
            return $questionSetId;
        } else {
            $this->logError("Failed to create question set: " . ($response['error'] ?? 'Unknown error'));
            return null;
        }
    }

    /**
     * Test adding questions to a question set
     */
    private function testAddQuestions($questionSetId)
    {
        echo "\n❓ Testing question addition...\n";
        
        $questions = [
            [
                'question_text' => 'What is the primary purpose of MCP (Model Context Protocol)?',
                'options' => [
                    ['text' => 'To enable AI assistants to use tools and services', 'is_correct' => true],
                    ['text' => 'To manage database connections', 'is_correct' => false],
                    ['text' => 'To handle user authentication', 'is_correct' => false],
                    ['text' => 'To process file uploads', 'is_correct' => false]
                ],
                'explanation' => 'MCP allows AI assistants to interact with external tools and services in a standardized way.',
                'marks' => 2,
                'difficulty_level' => 'medium'
            ],
            [
                'question_text' => 'Which of the following is a valid MCP tool type?',
                'options' => [
                    ['text' => 'create_question_set', 'is_correct' => true],
                    ['text' => 'invalid_tool', 'is_correct' => false],
                    ['text' => 'random_function', 'is_correct' => false],
                    ['text' => 'undefined_method', 'is_correct' => false]
                ],
                'explanation' => 'create_question_set is one of the tools available in our MCP server.',
                'marks' => 1,
                'difficulty_level' => 'easy'
            ]
        ];

        foreach ($questions as $index => $question) {
            $question['question_set_id'] = $questionSetId;
            
            $response = $this->makeMCPRequest('tools/call', [
                'name' => 'add_question_to_set',
                'arguments' => $question
            ]);
            
            if ($response && $response['success']) {
                $questionId = $response['data']['question_id'];
                $this->logSuccess("Added question " . ($index + 1) . " with ID: " . $questionId);
            } else {
                $this->logError("Failed to add question " . ($index + 1) . ": " . ($response['error'] ?? 'Unknown error'));
            }
        }
    }

    /**
     * Test getting question set details
     */
    private function testGetQuestionSetDetails($questionSetId)
    {
        echo "\n🔍 Testing question set details...\n";
        
        $response = $this->makeMCPRequest('tools/call', [
            'name' => 'get_question_set_details',
            'arguments' => [
                'question_set_id' => $questionSetId
            ]
        ]);
        
        if ($response && $response['success']) {
            $details = $response['data'];
            $this->logSuccess("Retrieved details for question set: " . $details['name']);
            echo "  - Questions: " . $details['questions_count'] . "\n";
            echo "  - Difficulty: " . $details['difficulty_level'] . "\n";
        } else {
            $this->logError("Failed to get question set details: " . ($response['error'] ?? 'Unknown error'));
        }
    }

    /**
     * Test creating an exam
     */
    private function testCreateExam($questionSetId)
    {
        echo "\n📋 Testing exam creation...\n";
        
        $response = $this->makeMCPRequest('tools/call', [
            'name' => 'create_exam',
            'arguments' => [
                'course_code' => 'TEST101',
                'type' => 'quiz',
                'duration' => 60,
                'passing_percentage' => 70,
                'start_date' => date('c', strtotime('+1 day')),
                'end_date' => date('c', strtotime('+2 days')),
                'question_sets' => [
                    [
                        'question_set_id' => $questionSetId,
                        'questions_to_pick' => 0, // All questions
                        'shuffle_questions' => true
                    ]
                ]
            ]
        ]);
        
        if ($response && $response['success']) {
            $examId = $response['data']['exam_id'];
            $this->logSuccess("Created exam with ID: " . $examId);
            echo "  - Slug: " . $response['data']['slug'] . "\n";
        } else {
            $this->logError("Failed to create exam: " . ($response['error'] ?? 'Unknown error'));
        }
    }

    /**
     * Test listing question sets
     */
    private function testListQuestionSets()
    {
        echo "\n📋 Testing question set listing...\n";
        
        $response = $this->makeMCPRequest('tools/call', [
            'name' => 'list_question_sets',
            'arguments' => []
        ]);
        
        if ($response && $response['success']) {
            $questionSets = $response['data'];
            $this->logSuccess("Listed " . count($questionSets) . " question sets");
            
            foreach (array_slice($questionSets, 0, 3) as $set) {
                echo "  - {$set['name']} ({$set['questions_count']} questions)\n";
            }
        } else {
            $this->logError("Failed to list question sets: " . ($response['error'] ?? 'Unknown error'));
        }
    }

    /**
     * Make an MCP request
     */
    private function makeMCPRequest($method, $params)
    {
        $data = [
            'jsonrpc' => '2.0',
            'id' => uniqid(),
            'method' => $method,
            'params' => $params
        ];
        
        $response = $this->makeHttpRequest('POST', '/', $data);
        
        if ($response && isset($response['result'])) {
            // Extract the actual result from MCP response
            $content = $response['result']['content'][0]['text'] ?? null;
            if ($content) {
                return json_decode($content, true);
            }
        }
        
        return null;
    }

    /**
     * Make HTTP request
     */
    private function makeHttpRequest($method, $path, $data = null)
    {
        $url = $this->serverUrl . $path;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        if ($method === 'POST' && $data) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            return json_decode($response, true);
        }
        
        return null;
    }

    /**
     * Log success message
     */
    private function logSuccess($message)
    {
        echo "✅ " . $message . "\n";
        $this->testResults[] = ['status' => 'success', 'message' => $message];
    }

    /**
     * Log error message
     */
    private function logError($message)
    {
        echo "❌ " . $message . "\n";
        $this->testResults[] = ['status' => 'error', 'message' => $message];
    }

    /**
     * Print test summary
     */
    private function printSummary()
    {
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "📊 TEST SUMMARY\n";
        echo str_repeat("=", 50) . "\n";
        
        $success = array_filter($this->testResults, function($result) {
            return $result['status'] === 'success';
        });
        
        $errors = array_filter($this->testResults, function($result) {
            return $result['status'] === 'error';
        });
        
        echo "✅ Successful tests: " . count($success) . "\n";
        echo "❌ Failed tests: " . count($errors) . "\n";
        echo "📈 Success rate: " . round((count($success) / count($this->testResults)) * 100, 1) . "%\n";
        
        if (count($errors) > 0) {
            echo "\n🔍 Failed Tests:\n";
            foreach ($errors as $error) {
                echo "  - " . $error['message'] . "\n";
            }
        }
        
        echo "\n🎉 Testing complete!\n";
    }
}

// Run the tests
if (isset($argv[1]) && $argv[1] === '--run') {
    $port = $argv[2] ?? '3003';
    $tester = new MCPServerTester("http://localhost:{$port}");
    $tester->runAllTests();
} else {
    echo "MCP Server Test Script\n";
    echo "====================\n\n";
    echo "Usage: php mcp_test.php --run [port]\n\n";
    echo "Make sure the MCP server is running first:\n";
    echo "php artisan mcp:serve --port=3003\n\n";
    echo "Then run: php mcp_test.php --run 3003\n\n";
}