<?php

namespace App\Services\Communication\Chat\MCP;

use App\Jobs\GenerateCohortStudentIds;
use App\Models\Cohort;
use App\Models\Exam;
use App\Models\Option;
use App\Models\Question;
use App\Models\QuestionSet;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ExamManagementMCPService
{
    /**
     * Get available tools/functions for the MCP server
     */
    public function getTools(): array
    {
        return [
            [
                'name' => 'create_question_set',
                'description' => 'Create a new question set for a specific course with name, description, and difficulty level',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'name' => [
                            'type' => 'string',
                            'description' => 'Name of the question set',
                        ],
                        'description' => [
                            'type' => 'string',
                            'description' => 'Description of the question set',
                        ],
                        'course_code' => [
                            'type' => 'string',
                            'description' => 'Course code (e.g., CS101, MATH201)',
                        ],
                        'difficulty_level' => [
                            'type' => 'string',
                            'enum' => ['easy', 'medium', 'hard'],
                            'description' => 'Difficulty level of the question set',
                        ],
                    ],
                    'required' => ['name', 'course_code', 'difficulty_level'],
                ],
            ],
            [
                'name' => 'add_question_to_set',
                'description' => 'Add a multiple choice question to a question set',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'question_set_id' => [
                            'type' => 'integer',
                            'description' => 'ID of the question set to add question to',
                        ],
                        'question_text' => [
                            'type' => 'string',
                            'description' => 'The question text/prompt',
                        ],
                        'options' => [
                            'type' => 'array',
                            'description' => 'Array of answer options',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'text' => ['type' => 'string', 'description' => 'Option text'],
                                    'is_correct' => ['type' => 'boolean', 'description' => 'Whether this option is correct'],
                                ],
                                'required' => ['text', 'is_correct'],
                            ],
                        ],
                        'explanation' => [
                            'type' => 'string',
                            'description' => 'Explanation for the correct answer (optional)',
                        ],
                        'marks' => [
                            'type' => 'integer',
                            'description' => 'Marks/points for this question (default: 1)',
                            'default' => 1,
                        ],
                        'difficulty_level' => [
                            'type' => 'string',
                            'enum' => ['easy', 'medium', 'hard'],
                            'description' => 'Difficulty level of this specific question',
                        ],
                    ],
                    'required' => ['question_set_id', 'question_text', 'options'],
                ],
            ],
            [
                'name' => 'create_exam',
                'description' => 'Create a new exam with question sets',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'course_code' => [
                            'type' => 'string',
                            'description' => 'Course code for the exam',
                        ],
                        'type' => [
                            'type' => 'string',
                            'enum' => ['quiz', 'midterm', 'final', 'assignment', 'test'],
                            'description' => 'Type of exam',
                        ],
                        'duration' => [
                            'type' => 'integer',
                            'description' => 'Exam duration in minutes',
                        ],
                        'passing_percentage' => [
                            'type' => 'integer',
                            'description' => 'Minimum percentage to pass (default: 50)',
                            'default' => 50,
                        ],
                        'start_date' => [
                            'type' => 'string',
                            'format' => 'date-time',
                            'description' => 'Exam start date and time (ISO format)',
                        ],
                        'end_date' => [
                            'type' => 'string',
                            'format' => 'date-time',
                            'description' => 'Exam end date and time (ISO format)',
                        ],
                        'question_sets' => [
                            'type' => 'array',
                            'description' => 'Question sets to include in exam',
                            'items' => [
                                'type' => 'object',
                                'properties' => [
                                    'question_set_id' => ['type' => 'integer', 'description' => 'Question set ID'],
                                    'questions_to_pick' => ['type' => 'integer', 'description' => 'Number of questions to pick (0 = all)'],
                                    'shuffle_questions' => ['type' => 'boolean', 'description' => 'Whether to shuffle questions'],
                                ],
                                'required' => ['question_set_id'],
                            ],
                        ],
                    ],
                    'required' => ['course_code', 'type', 'duration', 'start_date', 'end_date'],
                ],
            ],
            [
                'name' => 'list_question_sets',
                'description' => 'List all question sets with optional filtering',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'course_code' => [
                            'type' => 'string',
                            'description' => 'Filter by course code (optional)',
                        ],
                        'difficulty_level' => [
                            'type' => 'string',
                            'enum' => ['easy', 'medium', 'hard'],
                            'description' => 'Filter by difficulty level (optional)',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'list_courses',
                'description' => 'List all available courses/subjects',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [],
                ],
            ],
            [
                'name' => 'get_question_set_details',
                'description' => 'Get detailed information about a specific question set including its questions',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'question_set_id' => [
                            'type' => 'integer',
                            'description' => 'ID of the question set',
                        ],
                    ],
                    'required' => ['question_set_id'],
                ],
            ],
            [
                'name' => 'list_exams',
                'description' => 'List all exams with optional filtering by course or status',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'course_code' => [
                            'type' => 'string',
                            'description' => 'Filter by course code (optional)',
                        ],
                        'status' => [
                            'type' => 'string',
                            'enum' => ['upcoming', 'active', 'completed'],
                            'description' => 'Filter by exam status (optional)',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'get_exam_details',
                'description' => 'Get detailed information about a specific exam',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'exam_id' => [
                            'type' => 'integer',
                            'description' => 'ID of the exam',
                        ],
                    ],
                    'required' => ['exam_id'],
                ],
            ],
            [
                'name' => 'list_cohorts',
                'description' => 'List all cohorts in the system',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'active_only' => [
                            'type' => 'boolean',
                            'description' => 'Filter to show only active cohorts (default: false)',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'generate_student_ids_for_cohort',
                'description' => 'Generate student IDs for all students in a specific cohort who do not have student IDs',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'cohort_name' => [
                            'type' => 'string',
                            'description' => 'Name of the cohort to generate IDs for',
                        ],
                    ],
                    'required' => ['cohort_name'],
                ],
            ],
            [
                'name' => 'delete_cohort_students',
                'description' => 'Delete all students for a specific cohort. This is a destructive action that requires confirmation.',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'cohort_name' => [
                            'type' => 'string',
                            'description' => 'Name of the cohort to delete students from',
                        ],
                        'confirm_deletion' => [
                            'type' => 'boolean',
                            'description' => 'Must be true to confirm the deletion. This action is permanent.',
                        ],
                    ],
                    'required' => ['cohort_name', 'confirm_deletion'],
                ],
            ],
            [
                'name' => 'get_cohort_student_count',
                'description' => 'Get the number of students in a specific cohort',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'cohort_name' => [
                            'type' => 'string',
                            'description' => 'Name of the cohort',
                        ],
                    ],
                    'required' => ['cohort_name'],
                ],
            ],
        ];
    }

    /**
     * Handle MCP tool calls
     */
    public function handleToolCall(string $toolName, array $arguments): array
    {
        try {
            switch ($toolName) {
                case 'create_question_set':
                    return $this->createQuestionSet($arguments);
                case 'add_question_to_set':
                    return $this->addQuestionToSet($arguments);
                case 'create_exam':
                    return $this->createExam($arguments);
                case 'list_question_sets':
                    return $this->listQuestionSets($arguments);
                case 'list_courses':
                    return $this->listCourses();
                case 'get_question_set_details':
                    return $this->getQuestionSetDetails($arguments);
                case 'list_exams':
                    return $this->listExams($arguments);
                case 'get_exam_details':
                    return $this->getExamDetails($arguments);
                case 'list_cohorts':
                    return $this->listCohorts($arguments);
                case 'generate_student_ids_for_cohort':
                    return $this->generateStudentIdsForCohort($arguments);
                case 'delete_cohort_students':
                    return $this->deleteCohortStudents($arguments);
                case 'get_cohort_student_count':
                    return $this->getCohortStudentCount($arguments);
                default:
                    return [
                        'success' => false,
                        'error' => "Unknown tool: {$toolName}",
                    ];
            }
        } catch (\Exception $e) {
            Log::error("MCP Tool Error: {$toolName}", [
                'error' => $e->getMessage(),
                'arguments' => $arguments,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create a new question set
     */
    public function createQuestionSet(array $args): array
    {
        // Find subject by course code (compatible with both old subject_id and new course_code)
        $subject = null;

        if (isset($args['course_code'])) {
            $subject = Subject::where('course_code', $args['course_code'])->first();
            if (! $subject) {
                return [
                    'success' => false,
                    'error' => "Course with code '{$args['course_code']}' not found",
                ];
            }
        } elseif (isset($args['subject_id'])) {
            // Backward compatibility for old API calls
            $subject = Subject::find($args['subject_id']);
            if (! $subject) {
                return [
                    'success' => false,
                    'error' => "Subject with ID '{$args['subject_id']}' not found",
                ];
            }
        } else {
            return [
                'success' => false,
                'error' => "Either 'course_code' or 'subject_id' must be provided",
            ];
        }

        $questionSet = QuestionSet::create([
            'name' => $args['name'] ?? $args['title'], // Support both new 'name' and old 'title'
            'description' => $args['description'] ?? null,
            'course_id' => $subject->id,
            'difficulty_level' => $args['difficulty_level'] ?? 'medium',
            'created_by' => Auth::id() ?? 1,
        ]);

        return [
            'success' => true,
            'data' => [
                'id' => $questionSet->id,
                'name' => $questionSet->name,
                'course' => $subject->course_code.' - '.$subject->name,
                'difficulty_level' => $questionSet->difficulty_level,
                'created_at' => $questionSet->created_at->toISOString(),
            ],
        ];
    }

    /**
     * Add a question to a question set
     */
    public function addQuestionToSet(array $args): array
    {
        $questionSet = QuestionSet::find($args['question_set_id']);
        if (! $questionSet) {
            return [
                'success' => false,
                'error' => "Question set with ID {$args['question_set_id']} not found",
            ];
        }

        // Check permissions - only creator or Super Admin can add questions
        $user = Auth::user();
        if (! $user->hasRole(['Super Admin', 'Administrator', 'admin']) && $questionSet->created_by !== $user->id) {
            return [
                'success' => false,
                'error' => 'You do not have permission to add questions to this question set',
            ];
        }

        // Map question types to database enum values
        $typeMapping = [
            'multiple_choice' => 'MCQ',
            'true_false' => 'TF',
            'short_answer' => 'ESSAY', // Treating as essay for now
            'essay' => 'ESSAY',
        ];

        $dbType = $typeMapping[$args['question_type']] ?? 'MCQ';

        // Create the question
        $question = Question::create([
            'question_set_id' => $questionSet->id,
            'question_text' => $args['question_text'],
            'explanation' => $args['explanation'] ?? null,
            'mark' => $args['marks'] ?? 1,
            'type' => $dbType,
            'difficulty_level' => $args['difficulty_level'] ?? $questionSet->difficulty_level ?? 'medium',
        ]);

        $createdOptions = [];

        // Handle different question types
        if ($args['question_type'] === 'multiple_choice' && ! empty($args['options'])) {
            // Create options for multiple choice questions
            foreach ($args['options'] as $index => $optionText) {
                $isCorrect = ($optionText === $args['correct_answer']);

                $option = Option::create([
                    'question_id' => $question->id,
                    'option_text' => $optionText,
                    'is_correct' => $isCorrect,
                    'option_letter' => chr(65 + $index), // A, B, C, D...
                ]);

                $createdOptions[] = [
                    'id' => $option->id,
                    'text' => $option->option_text,
                    'letter' => $option->option_letter,
                    'is_correct' => $option->is_correct,
                ];
            }
        }

        return [
            'success' => true,
            'data' => [
                'id' => $question->id,
                'question_text' => $question->question_text,
                'question_type' => $question->type,
                'question_set' => $questionSet->name,
                'marks' => $question->mark,
                'difficulty_level' => $question->difficulty_level,
                'options' => $createdOptions,
                'correct_answer' => $args['correct_answer'],
                'created_at' => $question->created_at->toISOString(),
            ],
        ];
    }

    /**
     * Create a new exam
     */
    public function createExam(array $args): array
    {
        // Find course by code
        $course = Subject::where('course_code', $args['course_code'])->first();
        if (! $course) {
            return [
                'success' => false,
                'error' => "Course with code '{$args['course_code']}' not found",
            ];
        }

        // Generate unique slug
        $baseSlug = Str::slug($course->course_code.'-'.$args['type'].'-'.now()->format('Y-m-d'));
        $slug = $baseSlug;
        $counter = 1;

        while (Exam::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        // Create exam
        $exam = Exam::create([
            'course_id' => $course->id,
            'user_id' => Auth::id() ?? 1,
            'type' => $args['type'],
            'duration' => $args['duration'],
            'passing_percentage' => $args['passing_percentage'] ?? 50,
            'status' => 'upcoming', // Use valid enum value
            'slug' => $slug,
            'start_date' => $args['start_date'],
            'end_date' => $args['end_date'],
        ]);

        // Attach question sets if provided
        $attachedSets = [];
        if (! empty($args['question_sets'])) {
            foreach ($args['question_sets'] as $setConfig) {
                $questionSet = QuestionSet::find($setConfig['question_set_id']);
                if ($questionSet) {
                    $exam->questionSets()->attach($questionSet->id, [
                        'questions_to_pick' => $setConfig['questions_to_pick'] ?? 0,
                        'shuffle_questions' => $setConfig['shuffle_questions'] ?? false,
                    ]);

                    $attachedSets[] = [
                        'id' => $questionSet->id,
                        'name' => $questionSet->name,
                        'questions_to_pick' => $setConfig['questions_to_pick'] ?? 0,
                        'shuffle_questions' => $setConfig['shuffle_questions'] ?? false,
                    ];
                }
            }
        }

        return [
            'success' => true,
            'data' => [
                'exam_id' => $exam->id,
                'slug' => $exam->slug,
                'course' => $course->course_code.' - '.$course->name,
                'type' => $exam->type,
                'duration' => $exam->duration,
                'passing_percentage' => $exam->passing_percentage,
                'start_date' => $exam->start_date->toISOString(),
                'end_date' => $exam->end_date->toISOString(),
                'status' => $exam->status,
                'question_sets' => $attachedSets,
                'total_questions_estimate' => $exam->total_questions_count,
                'created_at' => $exam->created_at->toISOString(),
            ],
        ];
    }

    /**
     * List question sets with filtering
     */
    public function listQuestionSets(array $args = []): array
    {
        $query = QuestionSet::with(['course', 'creator'])
            ->withCount('questions');

        // Apply role-based access control - same as exam filtering
        $user = Auth::user();
        if (! $user->hasRole(['Super Admin', 'Administrator', 'admin'])) {
            // Lecturers can only see their own question sets
            $query->where('created_by', $user->id);
        }

        // Apply filters
        if (! empty($args['course_code'])) {
            $query->whereHas('course', function ($q) use ($args) {
                $q->where('course_code', $args['course_code']);
            });
        }

        if (! empty($args['difficulty_level'])) {
            $query->where('difficulty_level', $args['difficulty_level']);
        }

        $questionSets = $query->orderBy('created_at', 'desc')->get();

        $data = $questionSets->map(function ($set) {
            return [
                'id' => $set->id,
                'name' => $set->name,
                'description' => $set->description,
                'course' => $set->course->course_code.' - '.$set->course->name,
                'difficulty_level' => $set->difficulty_level,
                'questions_count' => $set->questions_count,
                'created_by' => $set->creator->name ?? 'Unknown',
                'created_at' => $set->created_at->toISOString(),
            ];
        });

        return [
            'success' => true,
            'data' => $data->toArray(),
        ];
    }

    /**
     * List all courses
     */
    public function listCourses(array $args = []): array
    {
        $courses = Subject::select('id', 'course_code', 'name', 'description')
            ->orderBy('course_code')
            ->get();

        $data = $courses->map(function ($course) {
            return [
                'id' => $course->id,
                'course_code' => $course->course_code,
                'name' => $course->name,
                'description' => $course->description,
            ];
        });

        return [
            'success' => true,
            'data' => $data->toArray(),
        ];
    }

    /**
     * Get detailed information about a question set
     */
    public function getQuestionSetDetails(array $args): array
    {
        $questionSet = QuestionSet::with(['course', 'creator', 'questions.options'])
            ->find($args['question_set_id']);

        if (! $questionSet) {
            return [
                'success' => false,
                'error' => "Question set with ID {$args['question_set_id']} not found",
            ];
        }

        // Check permissions - only creator or Super Admin can view details
        $user = Auth::user();
        if (! $user->hasRole(['Super Admin', 'Administrator', 'admin']) && $questionSet->created_by !== $user->id) {
            return [
                'success' => false,
                'error' => 'You do not have permission to view this question set',
            ];
        }

        $questions = $questionSet->questions->map(function ($question) {
            return [
                'id' => $question->id,
                'question_text' => $question->question_text,
                'explanation' => $question->explanation,
                'marks' => $question->mark,
                'difficulty_level' => $question->difficulty_level,
                'type' => $question->type,
                'options' => $question->options->map(function ($option) {
                    return [
                        'id' => $option->id,
                        'text' => $option->option_text,
                        'letter' => $option->option_letter,
                        'is_correct' => $option->is_correct,
                    ];
                })->toArray(),
            ];
        });

        return [
            'success' => true,
            'data' => [
                'id' => $questionSet->id,
                'name' => $questionSet->name,
                'description' => $questionSet->description,
                'course' => [
                    'id' => $questionSet->course->id,
                    'code' => $questionSet->course->course_code,
                    'name' => $questionSet->course->name,
                ],
                'difficulty_level' => $questionSet->difficulty_level,
                'created_by' => $questionSet->creator->name ?? 'Unknown',
                'created_at' => $questionSet->created_at->toISOString(),
                'questions_count' => $questions->count(),
                'questions' => $questions->toArray(),
            ],
        ];
    }

    /**
     * List exams with filtering
     */
    public function listExams(array $args = []): array
    {
        $query = Exam::with(['course', 'user'])
            ->withCount(['questionSets']);

        // Apply filters
        if (! empty($args['course_code'])) {
            $query->whereHas('course', function ($q) use ($args) {
                $q->where('course_code', $args['course_code']);
            });
        }

        if (! empty($args['status'])) {
            $query->where('status', $args['status']);
        }

        $exams = $query->orderBy('created_at', 'desc')->get();

        $data = $exams->map(function ($exam) {
            return [
                'id' => $exam->id,
                'slug' => $exam->slug,
                'course' => $exam->course->course_code.' - '.$exam->course->name,
                'type' => $exam->type,
                'duration' => $exam->duration,
                'passing_percentage' => $exam->passing_percentage,
                'start_date' => $exam->start_date?->toISOString(),
                'end_date' => $exam->end_date?->toISOString(),
                'status' => $exam->status,
                'question_sets_count' => $exam->question_sets_count,
                'created_by' => $exam->user->name ?? 'Unknown',
                'created_at' => $exam->created_at->toISOString(),
            ];
        });

        return [
            'success' => true,
            'data' => $data->toArray(),
        ];
    }

    /**
     * Get detailed information about a specific exam
     */
    public function getExamDetails(array $args): array
    {
        $exam = Exam::with(['course', 'user', 'questionSets.questions'])
            ->find($args['exam_id']);

        if (! $exam) {
            return [
                'success' => false,
                'error' => "Exam with ID {$args['exam_id']} not found",
            ];
        }

        $questionSets = $exam->questionSets->map(function ($questionSet) {
            return [
                'id' => $questionSet->id,
                'name' => $questionSet->name,
                'description' => $questionSet->description,
                'difficulty_level' => $questionSet->difficulty_level,
                'questions_count' => $questionSet->questions->count(),
                'questions_to_pick' => $questionSet->pivot->questions_to_pick ?? 0,
                'shuffle_questions' => $questionSet->pivot->shuffle_questions ?? false,
            ];
        });

        return [
            'success' => true,
            'data' => [
                'id' => $exam->id,
                'slug' => $exam->slug,
                'course' => [
                    'id' => $exam->course->id,
                    'code' => $exam->course->course_code,
                    'name' => $exam->course->name,
                ],
                'type' => $exam->type,
                'duration' => $exam->duration,
                'passing_percentage' => $exam->passing_percentage,
                'start_date' => $exam->start_date?->toISOString(),
                'end_date' => $exam->end_date?->toISOString(),
                'status' => $exam->status,
                'created_by' => $exam->user->name ?? 'Unknown',
                'created_at' => $exam->created_at->toISOString(),
                'question_sets' => $questionSets->toArray(),
                'total_questions_estimate' => $questionSets->sum('questions_count'),
            ],
        ];
    }

    /**
     * List all cohorts in the system
     */
    private function listCohorts(array $arguments): array
    {
        try {
            $activeOnly = $arguments['active_only'] ?? false;

            $query = Cohort::query();

            if ($activeOnly) {
                $query->where('is_active', true);
            }

            $cohorts = $query->orderBy('name')->get()->map(function ($cohort) {
                return [
                    'id' => $cohort->id,
                    'name' => $cohort->name,
                    'description' => $cohort->description,
                    'academic_year' => $cohort->academic_year,
                    'start_date' => $cohort->start_date?->toDateString(),
                    'end_date' => $cohort->end_date?->toDateString(),
                    'is_active' => $cohort->is_active,
                    'student_count' => $cohort->students()->count(),
                ];
            });

            return [
                'success' => true,
                'data' => $cohorts->toArray(),
            ];

        } catch (\Exception $e) {
            Log::error('Error listing cohorts', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'error' => 'Error listing cohorts: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Generate student IDs for all students in a cohort who don't have IDs
     */
    private function generateStudentIdsForCohort(array $arguments): array
    {
        try {
            $cohortName = $arguments['cohort_name'];

            $cohort = Cohort::where('name', $cohortName)->first();
            if (! $cohort) {
                return [
                    'success' => false,
                    'error' => "Cohort '{$cohortName}' not found",
                ];
            }

            // Get students without student IDs
            $studentsWithoutIdsCount = $cohort->students()
                ->where(function ($query) {
                    $query->whereNull('student_id')
                        ->orWhere('student_id', '')
                        ->orWhere('student_id', 'LIKE', 'TEMP_%');
                })
                ->count();

            if ($studentsWithoutIdsCount === 0) {
                return [
                    'success' => true,
                    'message' => "All students in cohort '{$cohortName}' already have student IDs",
                    'data' => [
                        'queued' => false,
                        'processed' => 0,
                        'generated' => 0,
                        'errors' => 0,
                    ],
                ];
            }

            // Dispatch a background job to avoid request timeouts
            GenerateCohortStudentIds::dispatch($cohort->id, Auth::id());

            return [
                'success' => true,
                'message' => "Queued ID generation for {$studentsWithoutIdsCount} students in cohort '{$cohortName}'. You'll be notified when it's complete.",
                'data' => [
                    'queued' => true,
                    'to_process' => $studentsWithoutIdsCount,
                    'cohort_id' => $cohort->id,
                ],
            ];

        } catch (\Exception $e) {
            Log::error('Error generating student IDs for cohort', [
                'error' => $e->getMessage(),
                'cohort_name' => $cohortName ?? 'Unknown',
            ]);

            return [
                'success' => false,
                'error' => 'Error generating student IDs: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Get student count for a specific cohort
     */
    private function getCohortStudentCount(array $arguments): array
    {
        try {
            $cohortName = $arguments['cohort_name'];

            $cohort = Cohort::where('name', $cohortName)->first();
            if (! $cohort) {
                return [
                    'success' => false,
                    'error' => "Cohort '{$cohortName}' not found",
                ];
            }

            $studentCount = $cohort->students()->count();
            $studentsWithIds = $cohort->students()
                ->whereNotNull('student_id')
                ->where('student_id', '!=', '')
                ->where('student_id', 'NOT LIKE', 'TEMP_%')
                ->count();

            $studentsWithoutIds = $studentCount - $studentsWithIds;

            return [
                'success' => true,
                'data' => [
                    'cohort_name' => $cohortName,
                    'total_students' => $studentCount,
                    'students_with_ids' => $studentsWithIds,
                    'students_without_ids' => $studentsWithoutIds,
                    'cohort_info' => [
                        'id' => $cohort->id,
                        'description' => $cohort->description,
                        'academic_year' => $cohort->academic_year,
                        'is_active' => $cohort->is_active,
                    ],
                ],
            ];

        } catch (\Exception $e) {
            Log::error('Error getting cohort student count', [
                'error' => $e->getMessage(),
                'cohort_name' => $cohortName ?? 'Unknown',
            ]);

            return [
                'success' => false,
                'error' => 'Error getting student count: '.$e->getMessage(),
            ];
        }
    }
}
