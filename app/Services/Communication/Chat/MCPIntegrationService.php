<?php

namespace App\Services\Communication\Chat;

use App\Services\Communication\Chat\OpenAI\OpenAIAssistantsService;
use App\Services\Communication\Chat\MCP\ExamManagementMCPService;
use App\Services\Communication\Chat\MCPPermissionService;
use Illuminate\Support\Facades\Log;

class MCPIntegrationService
{
    protected $mcpService;
    protected $assistantService;
    protected $permissionService;

    public function __construct(
        ExamManagementMCPService $mcpService, 
        OpenAIAssistantsService $assistantService,
        MCPPermissionService $permissionService
    ) {
        $this->mcpService = $mcpService;
        $this->assistantService = $assistantService;
        $this->permissionService = $permissionService;
    }

    /**
     * Get MCP tools configuration for OpenAI Assistant
     */
    public function getMCPToolsConfig(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'create_question_set',
                    'description' => 'Create a new question set with specified parameters',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'course_code' => [
                                'type' => 'string',
                                'description' => 'Course code for the question set (e.g., CS101, MATH201)'
                            ],
                            'name' => [
                                'type' => 'string',
                                'description' => 'Name of the question set'
                            ],
                            'description' => [
                                'type' => 'string',
                                'description' => 'Optional description of the question set'
                            ],
                            'difficulty_level' => [
                                'type' => 'string',
                                'enum' => ['easy', 'medium', 'hard'],
                                'description' => 'Difficulty level of the question set (default: medium)'
                            ]
                        ],
                        'required' => ['course_code', 'name']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'add_question_to_set',
                    'description' => 'Add a question to an existing question set',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'question_set_id' => [
                                'type' => 'integer',
                                'description' => 'The ID of the question set'
                            ],
                            'question_text' => [
                                'type' => 'string',
                                'description' => 'The text of the question'
                            ],
                            'question_type' => [
                                'type' => 'string',
                                'enum' => ['multiple_choice', 'true_false', 'short_answer', 'essay'],
                                'description' => 'Type of question'
                            ],
                            'options' => [
                                'type' => 'array',
                                'items' => ['type' => 'string'],
                                'description' => 'Array of answer options (for multiple choice)'
                            ],
                            'correct_answer' => [
                                'type' => 'string',
                                'description' => 'The correct answer or answer key'
                            ],
                            'marks' => [
                                'type' => 'integer',
                                'description' => 'Points awarded for correct answer'
                            ]
                        ],
                        'required' => ['question_set_id', 'question_text', 'question_type', 'correct_answer', 'marks']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'create_exam',
                    'description' => 'Create a new exam with specified parameters',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'course_code' => [
                                'type' => 'string',
                                'description' => 'Course code for the exam (e.g., CS101, MATH201)'
                            ],
                            'type' => [
                                'type' => 'string',
                                'enum' => ['quiz', 'midterm', 'final', 'assignment', 'test'],
                                'description' => 'Type of exam'
                            ],
                            'duration' => [
                                'type' => 'integer',
                                'description' => 'Exam duration in minutes'
                            ],
                            'passing_percentage' => [
                                'type' => 'integer',
                                'description' => 'Minimum percentage to pass (default: 50)'
                            ],
                            'start_date' => [
                                'type' => 'string',
                                'format' => 'date-time',
                                'description' => 'Exam start date and time (ISO format)'
                            ],
                            'end_date' => [
                                'type' => 'string',
                                'format' => 'date-time',
                                'description' => 'Exam end date and time (ISO format)'
                            ],
                            'question_sets' => [
                                'type' => 'array',
                                'description' => 'Question sets to include in exam (optional)',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'question_set_id' => [
                                            'type' => 'integer',
                                            'description' => 'Question set ID'
                                        ],
                                        'questions_to_pick' => [
                                            'type' => 'integer',
                                            'description' => 'Number of questions to pick (0 = all)'
                                        ],
                                        'shuffle_questions' => [
                                            'type' => 'boolean',
                                            'description' => 'Whether to shuffle questions'
                                        ]
                                    ],
                                    'required' => ['question_set_id']
                                ]
                            ]
                        ],
                        'required' => ['course_code', 'type', 'duration', 'start_date', 'end_date']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'list_courses',
                    'description' => 'Get a list of all available courses',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => new \stdClass(),
                        'required' => []
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'list_question_sets',
                    'description' => 'Get a list of question sets, optionally filtered by subject',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'subject_id' => [
                                'type' => 'integer',
                                'description' => 'Optional: Filter by subject ID'
                            ]
                        ],
                        'required' => []
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_question_set_details',
                    'description' => 'Get detailed information about a specific question set including all questions',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'question_set_id' => [
                                'type' => 'integer',
                                'description' => 'The ID of the question set to retrieve'
                            ]
                        ],
                        'required' => ['question_set_id']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'list_exams',
                    'description' => 'Get a list of exams, optionally filtered by course',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'course_code' => [
                                'type' => 'string',
                                'description' => 'Optional: Filter by course code'
                            ],
                            'status' => [
                                'type' => 'string',
                                'enum' => ['upcoming', 'active', 'completed'],
                                'description' => 'Optional: Filter by exam status'
                            ]
                        ],
                        'required' => []
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_exam_details',
                    'description' => 'Get detailed information about a specific exam',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'exam_id' => [
                                'type' => 'integer',
                                'description' => 'The ID of the exam to retrieve'
                            ]
                        ],
                        'required' => ['exam_id']
                    ]
                ]
            ]
        ];
    }

    /**
     * Process MCP function calls from OpenAI Assistant with permission checks
     */
    public function processFunctionCall(string $functionName, array $arguments): array
    {
        try {
            Log::info("Processing MCP function call", [
                'function' => $functionName,
                'arguments' => $arguments
            ]);

            // Check if user has general MCP access
            if (!$this->permissionService->canAccessMCP()) {
                $this->permissionService->logPermissionCheck($functionName, false);
                return [
                    'success' => false,
                    'error' => "Access denied. You don't have permission to use AI Sensei exam management features. Please contact your administrator if you believe this is an error."
                ];
            }

            switch ($functionName) {
                case 'create_question_set':
                    if (!$this->permissionService->canCreateQuestionSets()) {
                        $this->permissionService->logPermissionCheck($functionName, false);
                        return [
                            'success' => false,
                            'error' => $this->permissionService->getPermissionDenialMessage($functionName)
                        ];
                    }
                    $this->permissionService->logPermissionCheck($functionName, true);
                    return $this->mcpService->createQuestionSet($arguments);
                
                case 'add_question_to_set':
                    if (!$this->permissionService->canAddQuestions()) {
                        $this->permissionService->logPermissionCheck($functionName, false);
                        return [
                            'success' => false,
                            'error' => $this->permissionService->getPermissionDenialMessage($functionName)
                        ];
                    }
                    $this->permissionService->logPermissionCheck($functionName, true);
                    return $this->mcpService->addQuestionToSet($arguments);
                
                case 'create_exam':
                    if (!$this->permissionService->canCreateExams()) {
                        $this->permissionService->logPermissionCheck($functionName, false);
                        return [
                            'success' => false,
                            'error' => $this->permissionService->getPermissionDenialMessage($functionName)
                        ];
                    }
                    $this->permissionService->logPermissionCheck($functionName, true);
                    return $this->mcpService->createExam($arguments);
                
                case 'list_courses':
                    if (!$this->permissionService->canListCourses()) {
                        $this->permissionService->logPermissionCheck($functionName, false);
                        return [
                            'success' => false,
                            'error' => $this->permissionService->getPermissionDenialMessage($functionName)
                        ];
                    }
                    $this->permissionService->logPermissionCheck($functionName, true);
                    return $this->mcpService->listCourses($arguments);
                
                case 'list_question_sets':
                    if (!$this->permissionService->canListQuestionSets()) {
                        $this->permissionService->logPermissionCheck($functionName, false);
                        return [
                            'success' => false,
                            'error' => $this->permissionService->getPermissionDenialMessage($functionName)
                        ];
                    }
                    $this->permissionService->logPermissionCheck($functionName, true);
                    return $this->mcpService->listQuestionSets($arguments);
                
                case 'get_question_set_details':
                    if (!$this->permissionService->canViewQuestionSetDetails()) {
                        $this->permissionService->logPermissionCheck($functionName, false);
                        return [
                            'success' => false,
                            'error' => $this->permissionService->getPermissionDenialMessage($functionName)
                        ];
                    }
                    $this->permissionService->logPermissionCheck($functionName, true);
                    return $this->mcpService->getQuestionSetDetails($arguments);
                
                case 'list_exams':
                    if (!$this->permissionService->canViewExams()) {
                        $this->permissionService->logPermissionCheck($functionName, false);
                        return [
                            'success' => false,
                            'error' => $this->permissionService->getPermissionDenialMessage($functionName)
                        ];
                    }
                    $this->permissionService->logPermissionCheck($functionName, true);
                    return $this->mcpService->listExams($arguments);
                
                case 'get_exam_details':
                    if (!$this->permissionService->canViewExams()) {
                        $this->permissionService->logPermissionCheck($functionName, false);
                        return [
                            'success' => false,
                            'error' => $this->permissionService->getPermissionDenialMessage($functionName)
                        ];
                    }
                    $this->permissionService->logPermissionCheck($functionName, true);
                    return $this->mcpService->getExamDetails($arguments);
                
                default:
                    Log::warning("Unknown MCP function called", ['function' => $functionName]);
                    return [
                        'success' => false,
                        'error' => "Unknown function: {$functionName}"
                    ];
            }
        } catch (\Exception $e) {
            Log::error("Error processing MCP function call", [
                'function' => $functionName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => "Function execution failed: {$e->getMessage()}"
            ];
        }
    }

    /**
     * Get user context for AI assistant instructions
     */
    public function getUserContextForAssistant(): string
    {
        $context = $this->permissionService->getUserContext();
        
        if (!$context['authenticated']) {
            return "User is not authenticated. Inform them that they need to log in to use exam management features.";
        }

        $contextString = "## Current User Context\n";
        $contextString .= "- **User**: {$context['name']} ({$context['email']})\n";
        $contextString .= "- **Primary Role**: {$context['primary_role']}\n";
        
        if (!empty($context['capabilities'])) {
            $contextString .= "- **Available Exam Management Capabilities**: " . implode(', ', $context['capabilities']) . "\n";
        } else {
            $contextString .= "- **Exam Management Access**: None - inform user they don't have exam management permissions\n";
        }
        
        if (!empty($context['exam_permissions'])) {
            $contextString .= "- **Specific Exam Permissions**: " . implode(', ', $context['exam_permissions']) . "\n";
        }

        $contextString .= "\n**Important**: Only perform actions the user has permission for. If they request something they can't do, explain their role limitations politely and suggest who they should contact.";

        return $contextString;
    }

    /**
     * Update existing assistant with MCP tools
     */
    public function updateAssistantWithMCPTools(string $assistantId): array
    {
        $tools = array_merge(
            [
                ['type' => 'code_interpreter'],
                ['type' => 'file_search']
            ],
            $this->getMCPToolsConfig()
        );

        $data = [
            'tools' => $tools
        ];

        return $this->assistantService->updateAssistant($assistantId, $data);
    }
}