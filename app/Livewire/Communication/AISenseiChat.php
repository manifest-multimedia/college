<?php

namespace App\Livewire\Communication;

use App\Models\ChatSession;
use App\Models\User;
use App\Services\Communication\Chat\ChatSessionService;
use App\Services\Communication\Chat\MarkdownRenderingService;
use App\Services\Communication\Chat\MCPIntegrationService;
use App\Services\Communication\Chat\OpenAI\OpenAIAssistantsService;
use App\Services\Communication\Chat\OpenAI\OpenAIFilesService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;

class AISenseiChat extends Component
{
    use WithFileUploads;

    // Chat properties
    public $threadId;

    public $currentThreadId;

    public $assistantId;

    public $messages = [];

    public $newMessage = '';

    public $error = null;

    public $isAITyping = false;

    public $isLoading = false;

    public $isUserTyping = false;

    public $componentLoaded = false;

    // Chat session management
    public $currentChatSession = null;

    public $chatSessions = [];

    public $showSessionHistory = false;

    public $sessionSearchQuery = '';

    public $editingSessionTitle = null;

    public $newSessionTitle = '';

    // File upload properties
    public $temporaryUploads = [];

    public $uploadingFile = false;

    public $filesAttachedToThread = [];

    public $uploadedFiles = [];

    public $pendingFiles = []; // Files waiting to be sent with message

    public $fileUploadMessage = ''; // Custom message to send with files

    // Service injections
    protected $openAIAssistantsService;

    protected $openAIFilesService;

    protected $mcpIntegrationService;

    protected $markdownRenderingService;

    protected $chatSessionService;

    // Constructor with dependency injection
    public function boot(
        OpenAIAssistantsService $openAIAssistantsService,
        OpenAIFilesService $openAIFilesService,
        MCPIntegrationService $mcpIntegrationService,
        MarkdownRenderingService $markdownRenderingService,
        ChatSessionService $chatSessionService
    ) {
        $this->openAIAssistantsService = $openAIAssistantsService;
        $this->openAIFilesService = $openAIFilesService;
        $this->mcpIntegrationService = $mcpIntegrationService;
        $this->markdownRenderingService = $markdownRenderingService;
        $this->chatSessionService = $chatSessionService;
    }

    public function mount()
    {
        try {
            // Check if user has an existing thread
            $user = Auth::user();

            // Set assistant ID from config
            $this->assistantId = Config::get('services.openai.assistant_id');

            if (! $this->assistantId) {
                Log::error('OpenAI Assistant ID is not configured', [
                    'user_id' => Auth::id(),
                ]);
                $this->error = 'OpenAI Assistant ID is not configured. Please check your configuration.';
            }

            // Initialize thread and messages
            $this->initializeChat($user);
        } catch (\Exception $e) {
            Log::error('Error initializing AI Sensei Chat', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            $this->error = 'Failed to initialize chat: '.$e->getMessage();
        }
    }

    private function initializeChat(User $user)
    {
        // Load recent sessions for the sidebar
        $this->loadChatSessions();

        // Get or restore the current thread
        $this->threadId = session('ai_sensei_thread_id');

        if (! $this->threadId) {
            // Create a new thread if one doesn't exist
            $threadResponse = $this->openAIAssistantsService->createThread();

            if ($threadResponse['success']) {
                $this->threadId = $threadResponse['data']['id'];
                session(['ai_sensei_thread_id' => $this->threadId]);

                // Create a new chat session in the database
                $this->currentChatSession = $this->chatSessionService->createNewSession($user, $this->threadId);
            } else {
                throw new \Exception('Failed to create a new thread: '.($threadResponse['message'] ?? 'Unknown error'));
            }
        } else {
            // Get or create the chat session for this thread
            $this->currentChatSession = $this->chatSessionService->getOrCreateActiveSession($user, $this->threadId);

            // Load existing messages
            $this->loadMessages();
        }

        // Load any files attached to this thread
        $this->loadAttachedFiles();
    }

    public function loadMessages()
    {
        if (! $this->threadId) {
            return;
        }

        try {
            $response = $this->openAIAssistantsService->listMessages($this->threadId);

            if ($response['success']) {
                // OpenAI returns messages in reverse chronological order
                $messages = collect($response['data']['data'])->reverse()->values();

                $this->messages = $messages->map(function ($message) {
                    $content = collect($message['content'])->map(function ($item) {
                        if ($item['type'] === 'text') {
                            return [
                                'type' => 'text',
                                'text' => $item['text']['value'],
                            ];
                        } elseif ($item['type'] === 'image_file') {
                            return [
                                'type' => 'image',
                                'file_id' => $item['image_file']['file_id'],
                            ];
                        }

                        return null;
                    })->filter()->toArray();

                    // Add attachment information if this is a user message with attachments
                    if ($message['role'] === 'user' && ! empty($message['attachments'])) {
                        foreach ($message['attachments'] as $attachment) {
                            if ($attachment['file_id']) {
                                // Get file info from our uploaded files tracking or OpenAI
                                $fileInfo = $this->getFileInfo($attachment['file_id']);
                                $content[] = [
                                    'type' => 'file_attachment',
                                    'file_id' => $attachment['file_id'],
                                    'filename' => $fileInfo['filename'] ?? 'Attached File',
                                    'size' => $fileInfo['size'] ?? 0,
                                ];
                            }
                        }
                    }

                    return [
                        'id' => $message['id'],
                        'role' => $message['role'],
                        'content' => $content,
                        'created_at' => $message['created_at'],
                    ];
                })->toArray();

                // Dispatch event for message updates
                $this->dispatch('messages-updated');
            } else {
                Log::error('Failed to load messages', [
                    'error' => $response['message'] ?? 'Unknown error',
                    'thread_id' => $this->threadId,
                ]);
                $this->error = 'Failed to load messages: '.($response['message'] ?? 'Unknown error');
            }
        } catch (\Exception $e) {
            Log::error('Error loading messages', [
                'error' => $e->getMessage(),
                'thread_id' => $this->threadId,
            ]);
            $this->error = 'Error loading messages: '.$e->getMessage();
        }
    }

    public function loadAttachedFiles()
    {
        if (! $this->threadId) {
            return;
        }

        try {
            $response = $this->openAIAssistantsService->listThreadFiles($this->threadId);

            if ($response['success']) {
                $this->filesAttachedToThread = $response['data']['data'] ?? [];
            } else {
                Log::error('Failed to load attached files', [
                    'error' => $response['message'] ?? 'Unknown error',
                    'thread_id' => $this->threadId,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error loading attached files', [
                'error' => $e->getMessage(),
                'thread_id' => $this->threadId,
            ]);
        }
    }

    /**
     * Send a message to AI Sensei
     * This method accepts a parameter to allow direct message sending from JavaScript
     */
    public function sendMessage($message = null)
    {
        // Get message from parameter if provided, otherwise use the property
        $messageText = $message ?: $this->newMessage;

        if (empty($messageText)) {
            return;
        }

        // Check if assistant ID is set
        if (! $this->assistantId) {
            $this->error = 'OpenAI Assistant ID is not configured. Please check your configuration.';
            Log::error('Assistant ID not set when sending message', [
                'user_id' => Auth::id(),
            ]);

            return;
        }

        // Check for active runs first with improved handling
        try {
            $runsResponse = $this->openAIAssistantsService->listRuns($this->threadId);

            if ($runsResponse['success']) {
                $activeRuns = collect($runsResponse['data']['data'] ?? [])->filter(function ($run) {
                    return in_array($run['status'], ['queued', 'in_progress', 'requires_action']);
                });

                if ($activeRuns->isNotEmpty()) {
                    $activeRun = $activeRuns->first();

                    // Check if the run is too old (stuck)
                    $runAge = time() - $activeRun['created_at'];
                    if ($runAge > 60) { // 1 minute
                        Log::warning('Found old active run, attempting to cancel', [
                            'run_id' => $activeRun['id'],
                            'status' => $activeRun['status'],
                            'age_seconds' => $runAge,
                        ]);

                        // Try to cancel the old run
                        $this->cancelStuckRun($activeRun['id']);

                        // Continue with sending the new message
                    } else {
                        $this->error = 'Please wait for the current response to complete before sending another message.';
                        // Log the issue for debugging
                        Log::warning('Attempt to send message while a run is active', [
                            'user_id' => Auth::id(),
                            'thread_id' => $this->threadId,
                            'run_id' => $activeRun['id'],
                            'run_status' => $activeRun['status'],
                            'run_age' => $runAge,
                        ]);

                        return;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error checking for active runs', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'thread_id' => $this->threadId,
            ]);
        }

        // Broadcast AI typing status
        $this->broadcastTypingStatus(true);
        $this->error = null;

        try {
            // Add user message to thread
            $userMessageResponse = $this->openAIAssistantsService->addMessage($this->threadId, $messageText, 'user');

            if (! $userMessageResponse['success']) {
                $this->error = 'Failed to send message: '.($userMessageResponse['message'] ?? 'Unknown error');
                $this->broadcastTypingStatus(false);

                return;
            }

            // Get user context for permission-aware responses
            $userContext = $this->mcpIntegrationService->getUserContextForAssistant();

            // Run the assistant with user context for permission-aware responses
            $runResponse = $this->openAIAssistantsService->createRun(
                $this->threadId,
                $this->assistantId,
                $userContext
            );

            if (! $runResponse['success']) {
                $this->error = 'Failed to process message: '.($runResponse['message'] ?? 'Unknown error');
                $this->broadcastTypingStatus(false);

                return;
            }

            $runId = $runResponse['data']['id'];

            // Store the current run ID in the session for reference
            session(['ai_sensei_current_run_id' => $runId]);

            // Process in background or poll based on your setup
            $this->processAiResponse($runId);

        } catch (\Exception $e) {
            Log::error('Error sending message to AI Sensei', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'thread_id' => $this->threadId,
            ]);
            $this->error = 'Error sending message: '.$e->getMessage();
            $this->broadcastTypingStatus(false);
        }

        // Clear the input field if using the property (not from JavaScript)
        if (! $message) {
            $this->newMessage = '';
        }
    }

    /**
     * Process the AI response - this could be moved to a job if needed
     */
    protected function processAiResponse($runId)
    {
        try {
            // Enhanced polling with better timeout handling
            $status = 'queued';
            $maxRetries = 60; // Increased to 30 seconds (60 * 500ms)
            $retries = 0;
            $startTime = time();
            $maxExecutionTime = 30; // 30 seconds maximum

            Log::info('Starting AI response processing', [
                'run_id' => $runId,
                'thread_id' => $this->threadId,
                'max_retries' => $maxRetries,
            ]);

            while (in_array($status, ['queued', 'in_progress', 'requires_action']) && $retries < $maxRetries) {
                // Check for overall timeout
                if (time() - $startTime > $maxExecutionTime) {
                    Log::error('AI response processing timed out', [
                        'run_id' => $runId,
                        'execution_time' => time() - $startTime,
                        'final_status' => $status,
                    ]);
                    $this->error = 'Response processing timed out. Please try again.';
                    $this->broadcastTypingStatus(false);

                    return;
                }

                // Wait before checking status (progressive backoff)
                $waitTime = min(500000 + ($retries * 100000), 2000000); // 0.5s to 2s max
                usleep($waitTime);

                $runStatusResponse = $this->openAIAssistantsService->retrieveRun($this->threadId, $runId);

                if (! $runStatusResponse['success']) {
                    Log::error('Failed to retrieve run status', [
                        'run_id' => $runId,
                        'error' => $runStatusResponse['message'] ?? 'Unknown error',
                    ]);
                    $this->error = 'Failed to check message status: '.($runStatusResponse['message'] ?? 'Unknown error');
                    $this->broadcastTypingStatus(false);
                    break;
                }

                $status = $runStatusResponse['data']['status'];

                Log::info('Run status check', [
                    'run_id' => $runId,
                    'status' => $status,
                    'retry' => $retries + 1,
                    'elapsed_time' => time() - $startTime,
                ]);

                // Handle function calls (MCP tool execution)
                if ($status === 'requires_action') {
                    Log::info('Handling function calls for run', ['run_id' => $runId]);

                    $actionHandled = $this->handleFunctionCalls($runStatusResponse['data'], $runId);

                    if ($actionHandled) {
                        $status = 'in_progress'; // Continue polling after submitting function outputs
                        // Reset some counters after successful action handling
                        $retries = max(0, $retries - 5); // Give more time after function calls
                    } else {
                        Log::error('Failed to handle required actions', ['run_id' => $runId]);
                        $this->error = 'Failed to process required actions.';
                        $this->broadcastTypingStatus(false);
                        break;
                    }
                }

                $retries++;
            }

            if ($status === 'completed') {
                Log::info('AI response processing completed', [
                    'run_id' => $runId,
                    'total_time' => time() - $startTime,
                ]);

                // Refresh messages from the API
                $this->loadMessages();

                // Sync messages to database
                $this->syncMessagesFromOpenAI();

                // Auto-generate title for new sessions with meaningful content
                if ($this->currentChatSession &&
                    $this->currentChatSession->title &&
                    str_contains($this->currentChatSession->title, 'New Chat -')) {
                    $this->autoGenerateTitle();
                }

                // Signal that AI is done typing
                $this->broadcastTypingStatus(false);

                // Clear any session run ID
                session()->forget('ai_sensei_current_run_id');

            } elseif ($status === 'failed') {
                $lastError = $runStatusResponse['data']['last_error'] ?? null;

                Log::error('AI run failed', [
                    'run_id' => $runId,
                    'thread_id' => $this->threadId,
                    'last_error' => $lastError,
                ]);

                // Check if this is a rate limit error
                if ($lastError && isset($lastError['code']) && $lastError['code'] === 'rate_limit_exceeded') {
                    // Extract wait time from error message if available
                    $errorMessage = $lastError['message'] ?? '';
                    $waitTime = null;

                    if (preg_match('/Please try again in ([\d.]+)s/', $errorMessage, $matches)) {
                        $waitTime = ceil(floatval($matches[1]));
                    }

                    $userMessage = "⏳ Your request completed successfully, but AI Sensei's response was delayed due to API rate limits. ";
                    if ($waitTime) {
                        $userMessage .= "Please wait {$waitTime} seconds and try sending your message again.";
                    } else {
                        $userMessage .= 'Please wait a moment and try again.';
                    }

                    $this->error = $userMessage;

                    Log::info('Rate limit encountered - operation completed but response delayed', [
                        'run_id' => $runId,
                        'wait_time' => $waitTime,
                        'rate_limit_message' => $errorMessage,
                    ]);
                } else {
                    // Generic failure for non-rate-limit errors
                    $this->error = 'AI processing failed. Please try again.';
                }

                $this->broadcastTypingStatus(false);
                session()->forget('ai_sensei_current_run_id');

            } elseif ($status === 'cancelled') {
                Log::info('AI run was cancelled', [
                    'run_id' => $runId,
                    'thread_id' => $this->threadId,
                ]);

                $this->error = 'Response was cancelled. Please try again.';
                $this->broadcastTypingStatus(false);
                session()->forget('ai_sensei_current_run_id');

            } else {
                // Handle timeout or stuck runs
                Log::error('Message processing failed or timed out', [
                    'status' => $status,
                    'run_id' => $runId,
                    'thread_id' => $this->threadId,
                    'retries' => $retries,
                    'execution_time' => time() - $startTime,
                ]);

                // Try to cancel the stuck run
                $this->cancelStuckRun($runId);

                $this->error = "Message processing failed or timed out with status: {$status}. The run has been cancelled.";
                $this->broadcastTypingStatus(false);
                session()->forget('ai_sensei_current_run_id');
            }
        } catch (\Exception $e) {
            Log::error('Error processing AI response', [
                'error' => $e->getMessage(),
                'run_id' => $runId,
                'thread_id' => $this->threadId,
            ]);
            $this->error = 'Error processing response: '.$e->getMessage();
            $this->broadcastTypingStatus(false);
        }
    }

    /**
     * Handle function calls from OpenAI Assistant (MCP tools)
     */
    protected function handleFunctionCalls($runData, $runId): bool
    {
        try {
            if (! isset($runData['required_action']['submit_tool_outputs']['tool_calls'])) {
                Log::warning('Function call action detected but no tool calls found', [
                    'run_id' => $runId,
                    'run_data_keys' => array_keys($runData),
                ]);

                return false;
            }

            $toolCalls = $runData['required_action']['submit_tool_outputs']['tool_calls'];
            $toolOutputs = [];

            Log::info('Processing function calls', [
                'run_id' => $runId,
                'tool_calls_count' => count($toolCalls),
            ]);

            foreach ($toolCalls as $toolCall) {
                Log::info('Processing MCP tool call', [
                    'tool_call_id' => $toolCall['id'],
                    'function_name' => $toolCall['function']['name'],
                    'arguments' => $toolCall['function']['arguments'],
                ]);

                try {
                    // Parse arguments (they come as JSON string)
                    $arguments = json_decode($toolCall['function']['arguments'], true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        Log::error('Failed to parse tool call arguments', [
                            'tool_call_id' => $toolCall['id'],
                            'json_error' => json_last_error_msg(),
                            'raw_arguments' => $toolCall['function']['arguments'],
                        ]);

                        // Provide error response for this tool call
                        $toolOutputs[] = [
                            'tool_call_id' => $toolCall['id'],
                            'output' => json_encode([
                                'success' => false,
                                'error' => 'Invalid arguments provided to function',
                            ]),
                        ];

                        continue;
                    }

                    // Execute the MCP function with timeout protection
                    $startTime = microtime(true);
                    $result = $this->mcpIntegrationService->processFunctionCall(
                        $toolCall['function']['name'],
                        $arguments ?: []
                    );
                    $executionTime = microtime(true) - $startTime;

                    Log::info('MCP function executed', [
                        'function_name' => $toolCall['function']['name'],
                        'execution_time' => round($executionTime, 3),
                        'success' => $result['success'] ?? false,
                    ]);

                    // Prepare the output for OpenAI
                    $toolOutputs[] = [
                        'tool_call_id' => $toolCall['id'],
                        'output' => json_encode($result),
                    ];

                } catch (\Exception $e) {
                    Log::error('Error executing MCP function', [
                        'tool_call_id' => $toolCall['id'],
                        'function_name' => $toolCall['function']['name'],
                        'error' => $e->getMessage(),
                    ]);

                    // Provide error response for this specific tool call
                    $toolOutputs[] = [
                        'tool_call_id' => $toolCall['id'],
                        'output' => json_encode([
                            'success' => false,
                            'error' => 'Function execution failed: '.$e->getMessage(),
                        ]),
                    ];
                }
            }

            // Submit the tool outputs back to OpenAI
            if (! empty($toolOutputs)) {
                Log::info('Submitting tool outputs', [
                    'run_id' => $runId,
                    'outputs_count' => count($toolOutputs),
                ]);

                $submitResponse = $this->openAIAssistantsService->submitToolOutputs(
                    $this->threadId,
                    $runId,
                    $toolOutputs
                );

                if (! $submitResponse['success']) {
                    Log::error('Failed to submit tool outputs', [
                        'run_id' => $runId,
                        'error' => $submitResponse['message'] ?? 'Unknown error',
                        'tool_outputs_count' => count($toolOutputs),
                    ]);

                    return false;
                }

                Log::info('Tool outputs submitted successfully', [
                    'run_id' => $runId,
                    'outputs_count' => count($toolOutputs),
                ]);

                return true;
            }

            Log::warning('No tool outputs to submit', ['run_id' => $runId]);

            return false;

        } catch (\Exception $e) {
            Log::error('Error handling function calls', [
                'error' => $e->getMessage(),
                'run_id' => $runId,
                'thread_id' => $this->threadId,
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Cancel a stuck run
     */
    protected function cancelStuckRun($runId)
    {
        try {
            Log::info('Attempting to cancel stuck run', [
                'run_id' => $runId,
                'thread_id' => $this->threadId,
            ]);

            $cancelResponse = $this->openAIAssistantsService->cancelRun($this->threadId, $runId);

            if ($cancelResponse['success']) {
                Log::info('Successfully cancelled stuck run', ['run_id' => $runId]);

                return true;
            } else {
                Log::error('Failed to cancel stuck run', [
                    'run_id' => $runId,
                    'error' => $cancelResponse['message'] ?? 'Unknown error',
                ]);

                return false;
            }
        } catch (\Exception $e) {
            Log::error('Exception while cancelling stuck run', [
                'run_id' => $runId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Clean up all stuck runs for the current thread
     */
    public function cleanupStuckRuns()
    {
        try {
            if (! $this->threadId) {
                return;
            }

            $runsResponse = $this->openAIAssistantsService->listRuns($this->threadId);

            if (! $runsResponse['success']) {
                return;
            }

            $stuckRuns = collect($runsResponse['data']['data'] ?? [])->filter(function ($run) {
                $isActive = in_array($run['status'], ['queued', 'in_progress', 'requires_action']);
                $isOld = (time() - $run['created_at']) > 30; // 30 seconds

                return $isActive && $isOld;
            });

            foreach ($stuckRuns as $run) {
                Log::info('Cleaning up stuck run', [
                    'run_id' => $run['id'],
                    'status' => $run['status'],
                    'age' => time() - $run['created_at'],
                ]);

                $this->cancelStuckRun($run['id']);
            }

            if ($stuckRuns->isNotEmpty()) {
                $this->dispatch('stuck-runs-cleaned', [
                    'count' => $stuckRuns->count(),
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error cleaning up stuck runs', [
                'error' => $e->getMessage(),
                'thread_id' => $this->threadId,
            ]);
        }
    }

    /**
     * Broadcast AI typing status changes through events
     */
    protected function broadcastTypingStatus($isTyping)
    {
        $this->isAITyping = $isTyping;

        // Dispatch to frontend
        $this->dispatch('ai-typing-status', ['isTyping' => $isTyping]);
    }

    /**
     * Handle user typing status changes
     */
    public function userStartedTyping()
    {
        $this->isUserTyping = true;

        // Dispatch event for UI updates in other components if needed
        // $this->dispatch('user-typing-status', ['status' => 'typing']);
    }

    public function userStoppedTyping()
    {
        $this->isUserTyping = false;

        // Dispatch event for UI updates in other components if needed
        // $this->dispatch('user-typing-status', ['status' => 'stopped']);
    }

    public function updatedTemporaryUploads($value)
    {
        try {
            // Stage files for sending with message instead of immediate upload
            foreach ($this->temporaryUploads as $file) {
                $this->pendingFiles[] = [
                    'file' => $file,
                    'filename' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'type' => $this->getFileTypeName($file->getClientOriginalName()),
                    'icon' => $this->getFileIcon($file->getClientOriginalName()),
                    'id' => uniqid('pending_'),
                ];
            }

            // Clear temporary uploads
            $this->temporaryUploads = [];

            // Emit event to update UI
            $this->dispatch('files-staged', [
                'count' => count($this->pendingFiles),
            ]);

        } catch (\Exception $e) {
            Log::error('Error staging file uploads', [
                'error' => $e->getMessage(),
            ]);
            $this->error = 'Error preparing files: '.$e->getMessage();
        }
    }

    public function removeFile($fileId)
    {
        try {
            if ($this->threadId) {
                // Remove the file from the thread
                $response = $this->openAIAssistantsService->removeFileFromThread($this->threadId, $fileId);

                if ($response['success']) {
                    // Remove from the uploaded files list
                    $this->uploadedFiles = array_filter($this->uploadedFiles ?? [], function ($file) use ($fileId) {
                        return $file['id'] !== $fileId;
                    });

                    // Refresh the list of attached files
                    $this->loadAttachedFiles();
                } else {
                    $this->error = 'Failed to remove file: '.($response['message'] ?? 'Unknown error');
                }
            }
        } catch (\Exception $e) {
            Log::error('Error removing file', [
                'error' => $e->getMessage(),
                'file_id' => $fileId,
                'thread_id' => $this->threadId,
            ]);
            $this->error = 'Error removing file: '.$e->getMessage();
        }
    }

    /**
     * Mark component as loaded - this is called from the wire:init directive
     */
    public function markLoaded()
    {
        $this->componentLoaded = true;
        $this->dispatch('component-loaded');

        // Log successful initialization for debugging purposes
        Log::info('AI Sensei Chat component fully loaded', [
            'user_id' => Auth::id(),
            'thread_id' => $this->threadId,
        ]);
    }

    /**
     * Get file icon based on file extension
     */
    private function getFileIcon($filename)
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        $iconMap = [
            'pdf' => 'ki-duotone ki-file-sheet fs-3x text-danger',
            'doc' => 'ki-duotone ki-file-text fs-3x text-primary',
            'docx' => 'ki-duotone ki-file-text fs-3x text-primary',
            'txt' => 'ki-duotone ki-file-added fs-3x text-info',
            'csv' => 'ki-duotone ki-file-sheet fs-3x text-success',
            'xlsx' => 'ki-duotone ki-file-sheet fs-3x text-success',
            'xls' => 'ki-duotone ki-file-sheet fs-3x text-success',
            'ppt' => 'ki-duotone ki-file-up fs-3x text-warning',
            'pptx' => 'ki-duotone ki-file-up fs-3x text-warning',
            'jpg' => 'ki-duotone ki-picture fs-3x text-info',
            'jpeg' => 'ki-duotone ki-picture fs-3x text-info',
            'png' => 'ki-duotone ki-picture fs-3x text-info',
            'gif' => 'ki-duotone ki-picture fs-3x text-info',
            'zip' => 'ki-duotone ki-file-zip fs-3x text-dark',
            'rar' => 'ki-duotone ki-file-zip fs-3x text-dark',
            'json' => 'ki-duotone ki-code fs-3x text-warning',
            'xml' => 'ki-duotone ki-code fs-3x text-warning',
            'html' => 'ki-duotone ki-code fs-3x text-warning',
            'css' => 'ki-duotone ki-code fs-3x text-warning',
            'js' => 'ki-duotone ki-code fs-3x text-warning',
            'php' => 'ki-duotone ki-code fs-3x text-warning',
            'py' => 'ki-duotone ki-code fs-3x text-warning',
        ];

        return $iconMap[$extension] ?? 'ki-duotone ki-file fs-3x text-gray-400';
    }

    /**
     * Get file type display name
     */
    private function getFileTypeName($filename)
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        $typeMap = [
            'pdf' => 'PDF Document',
            'doc' => 'Word Document',
            'docx' => 'Word Document',
            'txt' => 'Text File',
            'csv' => 'CSV Spreadsheet',
            'xlsx' => 'Excel Spreadsheet',
            'xls' => 'Excel Spreadsheet',
            'ppt' => 'PowerPoint Presentation',
            'pptx' => 'PowerPoint Presentation',
            'jpg' => 'JPEG Image',
            'jpeg' => 'JPEG Image',
            'png' => 'PNG Image',
            'gif' => 'GIF Image',
            'zip' => 'ZIP Archive',
            'rar' => 'RAR Archive',
            'json' => 'JSON File',
            'xml' => 'XML File',
            'html' => 'HTML File',
            'css' => 'CSS File',
            'js' => 'JavaScript File',
            'php' => 'PHP File',
            'py' => 'Python File',
        ];

        return $typeMap[$extension] ?? strtoupper($extension).' File';
    }

    /**
     * Upload file and get immediate AI analysis
     * This method provides instant feedback on uploaded files
     */
    public function uploadAndAnalyzeFile($file, $query = null)
    {
        try {
            $this->uploadingFile = true;
            $this->error = null;
            $this->isAITyping = true;

            // Use default analysis query if none provided
            $analysisQuery = $query ?? 'Please analyze this uploaded file and provide a detailed summary of its contents, structure, and key insights.';

            // Use the new comprehensive processing method
            $result = $this->openAIAssistantsService->processFileWithAI(
                $this->threadId,
                $this->assistantId,
                $file,
                $analysisQuery
            );

            if ($result['success']) {
                // Add the uploaded file to our tracking
                $this->uploadedFiles[] = [
                    'id' => $result['data']['file_info']['file_id'],
                    'filename' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'attached' => true,
                    'analyzed' => true,
                    'run_id' => $result['data']['run_id'],
                ];

                // Refresh messages to show both the upload and AI response
                $this->loadMessages();

                // Emit success event
                $this->dispatch('file-analyzed', [
                    'file_id' => $result['data']['file_info']['file_id'],
                    'filename' => $file->getClientOriginalName(),
                    'analysis_completed' => true,
                ]);

                // Add a success message
                $this->dispatch('show-notification', [
                    'type' => 'success',
                    'message' => "File '{$file->getClientOriginalName()}' uploaded and analyzed successfully!",
                ]);

            } else {
                $this->error = 'File analysis failed: '.$result['message'];

                Log::error('File analysis failed', [
                    'error' => $result['message'],
                    'step' => $result['step'] ?? 'unknown',
                    'file_name' => $file->getClientOriginalName(),
                    'thread_id' => $this->threadId,
                ]);

                // If file was uploaded but analysis failed, we still have the file
                if (isset($result['file_info'])) {
                    $this->uploadedFiles[] = [
                        'id' => $result['file_info']['file_id'],
                        'filename' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'attached' => true,
                        'analyzed' => false,
                        'error' => $result['message'],
                    ];
                }
            }

        } catch (\Exception $e) {
            Log::error('Error in uploadAndAnalyzeFile', [
                'error' => $e->getMessage(),
                'file_name' => $file->getClientOriginalName(),
                'thread_id' => $this->threadId,
            ]);

            $this->error = 'Failed to process file: '.$e->getMessage();
        } finally {
            $this->uploadingFile = false;
            $this->isAITyping = false;
        }
    }

    /**
     * Remove a staged file before sending
     */
    public function removeStagedFile($fileId)
    {
        $this->pendingFiles = array_filter($this->pendingFiles, function ($file) use ($fileId) {
            return $file['id'] !== $fileId;
        });

        $this->dispatch('file-removed', [
            'file_id' => $fileId,
        ]);
    }

    /**
     * Send message with attached files
     */
    public function sendMessageWithFiles($customMessage = null)
    {
        try {
            Log::info('sendMessageWithFiles called', [
                'customMessage' => $customMessage,
                'newMessage' => $this->newMessage,
                'pendingFiles_count' => count($this->pendingFiles),
                'pendingFiles' => array_map(function ($file) {
                    return ['filename' => $file['filename'], 'size' => $file['size']];
                }, $this->pendingFiles),
            ]);

            if (empty($this->pendingFiles) && empty(trim($customMessage ?: $this->newMessage))) {
                Log::warning('sendMessageWithFiles: Nothing to send - no files and no message');

                return; // Nothing to send
            }

            $this->uploadingFile = true;
            $this->error = null;

            // Prepare the message content
            $messageText = trim($customMessage ?: $this->newMessage) ?: "I've shared some files with you.";

            if (! empty($this->pendingFiles)) {
                // Upload files and create message with attachments
                $attachments = [];
                $fileInfos = [];

                foreach ($this->pendingFiles as $pendingFile) {
                    $file = $pendingFile['file'];

                    // Upload file to OpenAI
                    $uploadResponse = $this->openAIFilesService->uploadFile($file, 'assistants');

                    if ($uploadResponse['success']) {
                        $fileId = $uploadResponse['data']['file_id'];

                        $attachments[] = [
                            'file_id' => $fileId,
                            'tools' => [
                                ['type' => 'code_interpreter'],
                                ['type' => 'file_search'],
                            ],
                        ];

                        $fileInfos[] = [
                            'id' => $fileId,
                            'filename' => $pendingFile['filename'],
                            'size' => $pendingFile['size'],
                            'type' => $pendingFile['type'],
                            'icon' => $pendingFile['icon'],
                        ];
                    } else {
                        $this->error = 'Failed to upload file: '.$pendingFile['filename'];

                        return;
                    }
                }

                // Create message with attachments
                $result = $this->openAIAssistantsService->addMessage(
                    $this->threadId,
                    $messageText,
                    'user',
                    null, // deprecated fileIds
                    null, // metadata
                    $attachments
                );

                if ($result['success']) {
                    // Add to uploaded files tracking
                    foreach ($fileInfos as $fileInfo) {
                        $this->uploadedFiles[] = [
                            'id' => $fileInfo['id'],
                            'filename' => $fileInfo['filename'],
                            'size' => $fileInfo['size'],
                            'attached' => true,
                            'message_text' => $messageText,
                        ];
                    }

                    // Clear pending files and message
                    $this->pendingFiles = [];
                    $this->newMessage = '';

                    // Refresh messages to show the sent message with files
                    $this->loadMessages();

                    // Automatically trigger AI response to process the files
                    $this->triggerAIResponse();

                    $this->dispatch('message-sent', [
                        'with_files' => true,
                        'file_count' => count($fileInfos),
                    ]);
                } else {
                    $this->error = 'Failed to send message with files: '.$result['message'];
                }
            } else {
                // Send regular message without files
                $this->sendMessage();
            }

        } catch (\Exception $e) {
            Log::error('Error sending message with files', [
                'error' => $e->getMessage(),
                'thread_id' => $this->threadId,
            ]);
            $this->error = 'Error sending message: '.$e->getMessage();
        } finally {
            $this->uploadingFile = false;
        }
    }

    /**
     * Trigger AI response after files are uploaded
     */
    private function triggerAIResponse()
    {
        try {
            // Check if assistant ID is set
            if (! $this->assistantId) {
                Log::error('Assistant ID not set when triggering AI response for files');

                return;
            }

            // Broadcast AI typing status
            $this->broadcastTypingStatus(true);

            // Get user context for permission-aware responses
            $userContext = $this->mcpIntegrationService->getUserContextForAssistant();

            // Run the assistant to process the uploaded files with user context
            $runResponse = $this->openAIAssistantsService->createRun(
                $this->threadId,
                $this->assistantId,
                $userContext
            );

            if (! $runResponse['success']) {
                $this->error = 'Failed to process uploaded files: '.($runResponse['message'] ?? 'Unknown error');
                $this->broadcastTypingStatus(false);
                Log::error('Failed to create run for file processing', ['error' => $runResponse['message'] ?? 'Unknown error']);

                return;
            }

            $runId = $runResponse['data']['id'];

            // Store the current run ID in the session for reference
            session(['ai_sensei_current_run_id' => $runId]);

            // Process the AI response
            $this->processAiResponse($runId);

        } catch (\Exception $e) {
            Log::error('Error triggering AI response for files', [
                'error' => $e->getMessage(),
                'thread_id' => $this->threadId,
                'user_id' => Auth::id(),
            ]);
            $this->broadcastTypingStatus(false);
            $this->error = 'Error processing files: '.$e->getMessage();
        }
    }

    /**
     * Get file information by file ID
     */
    private function getFileInfo($fileId)
    {
        // First check our uploaded files tracking
        foreach ($this->uploadedFiles as $uploadedFile) {
            if ($uploadedFile['id'] === $fileId) {
                return [
                    'filename' => $uploadedFile['filename'],
                    'size' => $uploadedFile['size'],
                ];
            }
        }

        // If not found, try to get from OpenAI API
        try {
            $response = $this->openAIFilesService->getFile($fileId);
            if ($response['success']) {
                return [
                    'filename' => $response['data']['filename'] ?? 'Unknown File',
                    'size' => $response['data']['bytes'] ?? 0,
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Could not retrieve file info', ['file_id' => $fileId, 'error' => $e->getMessage()]);
        }

        return [
            'filename' => 'Attached File',
            'size' => 0,
        ];
    }

    /**
     * Clear all staged files
     */
    public function clearStagedFiles()
    {
        $this->pendingFiles = [];
        $this->dispatch('files-cleared');
    }

    /**
     * Clear all pending files (alias for clearStagedFiles)
     */
    public function clearPendingFiles()
    {
        $this->clearStagedFiles();
    }

    /**
     * Remove a specific pending file by index
     */
    public function removePendingFile($index)
    {
        if (isset($this->pendingFiles[$index])) {
            unset($this->pendingFiles[$index]);
            // Re-index the array to avoid gaps
            $this->pendingFiles = array_values($this->pendingFiles);

            $this->dispatch('file-removed', [
                'index' => $index,
                'remaining_count' => count($this->pendingFiles),
            ]);
        }
    }

    /**
     * Load chat sessions for the sidebar
     */
    public function loadChatSessions()
    {
        $user = Auth::user();
        $this->chatSessions = $this->chatSessionService->getRecentSessions($user, 15)->toArray();
    }

    /**
     * Switch to a different chat session
     */
    public function loadChatSession($sessionId)
    {
        try {
            $user = Auth::user();

            // Find the session
            $session = ChatSession::where('session_id', $sessionId)
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->first();

            if (! $session) {
                $this->error = 'Chat session not found or inaccessible.';

                return;
            }

            // Switch to this session
            $this->threadId = $sessionId;
            $this->currentChatSession = $session;

            // Update session storage
            session(['ai_sensei_thread_id' => $sessionId]);

            // Load messages and files
            $this->loadMessages();
            $this->loadAttachedFiles();

            // Sync messages from OpenAI (in case there are new ones)
            $this->syncMessagesFromOpenAI();

            // Update last activity
            $session->update(['last_activity_at' => now()]);

            // Clear any errors
            $this->error = null;

            // Refresh sessions list to update last activity times
            $this->loadChatSessions();

            $this->dispatch('session-loaded', [
                'session_id' => $sessionId,
                'title' => $session->title,
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading chat session', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            $this->error = 'Failed to load chat session: '.$e->getMessage();
        }
    }

    /**
     * Start a new chat session
     */
    public function startNewChat()
    {
        try {
            // Create a new thread
            $threadResponse = $this->openAIAssistantsService->createThread();

            if ($threadResponse['success']) {
                $user = Auth::user();
                $this->threadId = $threadResponse['data']['id'];

                // Create new chat session in database
                $this->currentChatSession = $this->chatSessionService->createNewSession($user, $this->threadId);

                // Update session storage
                session(['ai_sensei_thread_id' => $this->threadId]);

                // Reset state
                $this->messages = [];
                $this->newMessage = '';
                $this->filesAttachedToThread = [];
                $this->uploadedFiles = [];
                $this->pendingFiles = [];
                $this->error = null;

                // Refresh sessions list
                $this->loadChatSessions();

                // Dispatch event for message updates
                $this->dispatch('messages-updated');
                $this->dispatch('new-chat-started', [
                    'session_id' => $this->threadId,
                    'title' => $this->currentChatSession->title,
                ]);
            } else {
                $this->error = 'Failed to start new chat: '.($threadResponse['message'] ?? 'Unknown error');
            }
        } catch (\Exception $e) {
            Log::error('Error starting new chat', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            $this->error = 'Error starting new chat: '.$e->getMessage();
        }
    }

    /**
     * Toggle session history sidebar
     */
    public function toggleSessionHistory()
    {
        $this->showSessionHistory = ! $this->showSessionHistory;

        if ($this->showSessionHistory) {
            $this->loadChatSessions();
        }
    }

    /**
     * Search chat sessions
     */
    public function searchSessions()
    {
        if (empty($this->sessionSearchQuery)) {
            $this->loadChatSessions();

            return;
        }

        $user = Auth::user();
        $searchResults = $this->chatSessionService->searchUserSessions($user, $this->sessionSearchQuery, 15);

        $this->chatSessions = $searchResults->map(function ($session) {
            return [
                'id' => $session->id,
                'session_id' => $session->session_id,
                'title' => $session->title,
                'last_activity_at' => $session->last_activity_at,
                'last_activity_human' => $session->last_activity_at->diffForHumans(),
                'message_count' => $session->messages()->count(),
            ];
        })->toArray();
    }

    /**
     * Start editing session title
     */
    public function startEditingTitle($sessionId)
    {
        $session = collect($this->chatSessions)->firstWhere('session_id', $sessionId);
        if ($session) {
            $this->editingSessionTitle = $sessionId;
            $this->newSessionTitle = $session['title'];
        }
    }

    /**
     * Cancel editing session title
     */
    public function cancelEditingTitle()
    {
        $this->editingSessionTitle = null;
        $this->newSessionTitle = '';
    }

    /**
     * Save session title
     */
    public function saveSessionTitle($sessionId)
    {
        try {
            if (empty(trim($this->newSessionTitle))) {
                $this->error = 'Session title cannot be empty.';

                return;
            }

            $user = Auth::user();
            $success = $this->chatSessionService->updateSessionTitle($sessionId, trim($this->newSessionTitle), $user);

            if ($success) {
                // Update current session if it's the one being edited
                if ($this->currentChatSession && $this->currentChatSession->session_id === $sessionId) {
                    $this->currentChatSession->title = trim($this->newSessionTitle);
                }

                // Refresh sessions list
                $this->loadChatSessions();

                // Reset editing state
                $this->editingSessionTitle = null;
                $this->newSessionTitle = '';

                $this->dispatch('session-title-updated', [
                    'session_id' => $sessionId,
                    'title' => trim($this->newSessionTitle),
                ]);
            } else {
                $this->error = 'Failed to update session title.';
            }
        } catch (\Exception $e) {
            Log::error('Error updating session title', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            $this->error = 'Error updating title: '.$e->getMessage();
        }
    }

    /**
     * Archive a chat session
     */
    public function archiveSession($sessionId)
    {
        try {
            $user = Auth::user();
            $success = $this->chatSessionService->archiveSession($sessionId, $user);

            if ($success) {
                // If we're archiving the current session, start a new one
                if ($this->threadId === $sessionId) {
                    $this->startNewChat();
                } else {
                    // Just refresh the sessions list
                    $this->loadChatSessions();
                }

                $this->dispatch('session-archived', ['session_id' => $sessionId]);
            } else {
                $this->error = 'Failed to archive session.';
            }
        } catch (\Exception $e) {
            Log::error('Error archiving session', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            $this->error = 'Error archiving session: '.$e->getMessage();
        }
    }

    /**
     * Delete a chat session
     */
    public function deleteSession($sessionId)
    {
        try {
            $user = Auth::user();
            $success = $this->chatSessionService->deleteSession($sessionId, $user);

            if ($success) {
                // If we're deleting the current session, start a new one
                if ($this->threadId === $sessionId) {
                    $this->startNewChat();
                } else {
                    // Just refresh the sessions list
                    $this->loadChatSessions();
                }

                $this->dispatch('session-deleted', ['session_id' => $sessionId]);
            } else {
                $this->error = 'Failed to delete session.';
            }
        } catch (\Exception $e) {
            Log::error('Error deleting session', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            $this->error = 'Error deleting session: '.$e->getMessage();
        }
    }

    /**
     * Sync messages from OpenAI to local database
     */
    private function syncMessagesFromOpenAI()
    {
        if (! $this->currentChatSession) {
            return;
        }

        try {
            $response = $this->openAIAssistantsService->listMessages($this->threadId);

            if ($response['success']) {
                $openaiMessages = collect($response['data']['data'])->reverse()->values()->toArray();
                $this->chatSessionService->syncMessagesFromOpenAI($this->currentChatSession, $openaiMessages);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to sync messages from OpenAI', [
                'error' => $e->getMessage(),
                'session_id' => $this->currentChatSession->session_id,
            ]);
        }
    }

    /**
     * Auto-generate and update session title based on content
     */
    public function autoGenerateTitle($sessionId = null)
    {
        try {
            $targetSessionId = $sessionId ?: $this->threadId;
            $session = $this->currentChatSession;

            if ($sessionId) {
                $session = ChatSession::where('session_id', $sessionId)
                    ->where('user_id', Auth::id())
                    ->first();
            }

            if (! $session) {
                return;
            }

            $newTitle = $this->chatSessionService->generateSessionTitle($session);

            if ($newTitle !== $session->title) {
                $user = Auth::user();
                $this->chatSessionService->updateSessionTitle($session->session_id, $newTitle, $user);

                // Update current session if it's the one being updated
                if ($this->currentChatSession && $this->currentChatSession->id === $session->id) {
                    $this->currentChatSession->title = $newTitle;
                }

                // Refresh sessions list
                $this->loadChatSessions();

                $this->dispatch('session-title-generated', [
                    'session_id' => $session->session_id,
                    'title' => $newTitle,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error auto-generating session title', [
                'session_id' => $sessionId ?: $this->threadId,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
        }
    }

    /**
     * Render markdown content to HTML for display
     */
    public function renderMarkdown($content)
    {
        try {
            return $this->markdownRenderingService->safeRender($content);
        } catch (\Exception $e) {
            // Fallback to regular text rendering if markdown parsing fails
            Log::warning('Markdown rendering failed', [
                'error' => $e->getMessage(),
                'content_preview' => substr($content, 0, 100),
            ]);

            return nl2br(e($content));
        }
    }

    public function render()
    {
        return view('livewire.communication.ai-sensei-chat')
            ->layout('components.dashboard.default', ['title' => 'AI Sensei Assistant']);
    }
}
