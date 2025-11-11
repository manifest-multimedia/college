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
                            'subject_id' => [
                                'type' => 'integer',
                                'description' => 'The ID of the subject for the question set'
                            ],
                            'title' => [
                                'type' => 'string',
                                'description' => 'The title of the question set'
                            ],
                            'description' => [
                                'type' => 'string',
                                'description' => 'Optional description of the question set'
                            ]
                        ],
                        'required' => ['subject_id', 'title']
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
                            'course_id' => [
                                'type' => 'integer',
                                'description' => 'The ID of the course for the exam'
                            ],
                            'title' => [
                                'type' => 'string',
                                'description' => 'The title of the exam'
                            ],
                            'exam_date' => [
                                'type' => 'string',
                                'format' => 'date-time',
                                'description' => 'The date and time of the exam (Y-m-d H:i:s)'
                            ],
                            'duration' => [
                                'type' => 'integer',
                                'description' => 'Duration of the exam in minutes'
                            ],
                            'total_marks' => [
                                'type' => 'integer',
                                'description' => 'Total marks for the exam'
                            ],
                            'instructions' => [
                                'type' => 'string',
                                'description' => 'Instructions for the exam'
                            ]
                        ],
                        'required' => ['course_id', 'title', 'exam_date', 'duration', 'total_marks']
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