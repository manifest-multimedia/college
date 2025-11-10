<?php

namespace App\Livewire\Communication;

use App\Models\User;
use App\Services\Communication\Chat\OpenAI\OpenAIAssistantsService;
use App\Services\Communication\Chat\OpenAI\OpenAIFilesService;
use App\Services\Communication\Chat\MCPIntegrationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
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

    // Constructor with dependency injection
    public function boot(
        OpenAIAssistantsService $openAIAssistantsService, 
        OpenAIFilesService $openAIFilesService,
        MCPIntegrationService $mcpIntegrationService
    ) {
        $this->openAIAssistantsService = $openAIAssistantsService;
        $this->openAIFilesService = $openAIFilesService;
        $this->mcpIntegrationService = $mcpIntegrationService;
    }

    public function mount()
    {
        try {
            // Check if user has an existing thread
            $user = Auth::user();

            // Set assistant ID from config
            $this->assistantId = Config::get('services.openai.assistant_id');

            if (!$this->assistantId) {
                Log::error('OpenAI Assistant ID is not configured', [
                    'user_id' => Auth::id(),
                ]);
                $this->error = "OpenAI Assistant ID is not configured. Please check your configuration.";
            }

            // Initialize thread and messages
            $this->initializeChat($user);
        } catch (\Exception $e) {
            Log::error('Error initializing AI Sensei Chat', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            $this->error = "Failed to initialize chat: " . $e->getMessage();
        }
    }

    private function initializeChat(User $user)
    {
        // This would typically come from a database table that stores thread IDs per user
        $this->threadId = session('ai_sensei_thread_id');

        if (!$this->threadId) {
            // Create a new thread if one doesn't exist
            $threadResponse = $this->openAIAssistantsService->createThread();

            if ($threadResponse['success']) {
                $this->threadId = $threadResponse['data']['id'];
                session(['ai_sensei_thread_id' => $this->threadId]);
            } else {
                throw new \Exception('Failed to create a new thread: ' . ($threadResponse['message'] ?? 'Unknown error'));
            }
        } else {
            // Load existing messages
            $this->loadMessages();
        }

        // Load any files attached to this thread
        $this->loadAttachedFiles();
    }

    public function loadMessages()
    {
        if (!$this->threadId) {
            return;
        }

        try {
            $response = $this->openAIAssistantsService->listMessages($this->threadId);

            if ($response['success']) {
                // OpenAI returns messages in reverse chronological order
                $messages = collect($response['data']['data'])->reverse()->values();

                $this->messages = $messages->map(function($message) {
                    $content = collect($message['content'])->map(function($item) {
                        if ($item['type'] === 'text') {
                            return [
                                'type' => 'text',
                                'text' => $item['text']['value']
                            ];
                        } elseif ($item['type'] === 'image_file') {
                            return [
                                'type' => 'image',
                                'file_id' => $item['image_file']['file_id']
                            ];
                        }
                        return null;
                    })->filter()->toArray();

                    // Add attachment information if this is a user message with attachments
                    if ($message['role'] === 'user' && !empty($message['attachments'])) {
                        foreach ($message['attachments'] as $attachment) {
                            if ($attachment['file_id']) {
                                // Get file info from our uploaded files tracking or OpenAI
                                $fileInfo = $this->getFileInfo($attachment['file_id']);
                                $content[] = [
                                    'type' => 'file_attachment',
                                    'file_id' => $attachment['file_id'],
                                    'filename' => $fileInfo['filename'] ?? 'Attached File',
                                    'size' => $fileInfo['size'] ?? 0
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
                    'thread_id' => $this->threadId
                ]);
                $this->error = "Failed to load messages: " . ($response['message'] ?? 'Unknown error');
            }
        } catch (\Exception $e) {
            Log::error('Error loading messages', [
                'error' => $e->getMessage(),
                'thread_id' => $this->threadId
            ]);
            $this->error = "Error loading messages: " . $e->getMessage();
        }
    }

    public function loadAttachedFiles()
    {
        if (!$this->threadId) {
            return;
        }

        try {
            $response = $this->openAIAssistantsService->listThreadFiles($this->threadId);

            if ($response['success']) {
                $this->filesAttachedToThread = $response['data']['data'] ?? [];
            } else {
                Log::error('Failed to load attached files', [
                    'error' => $response['message'] ?? 'Unknown error',
                    'thread_id' => $this->threadId
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error loading attached files', [
                'error' => $e->getMessage(),
                'thread_id' => $this->threadId
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
        if (!$this->assistantId) {
            $this->error = "OpenAI Assistant ID is not configured. Please check your configuration.";
            Log::error('Assistant ID not set when sending message', [
                'user_id' => Auth::id(),
            ]);
            return;
        }

        // Check for active runs first
        try {
            $runsResponse = $this->openAIAssistantsService->listRuns($this->threadId);
            
            if ($runsResponse['success']) {
                $activeRun = collect($runsResponse['data']['data'] ?? [])->first(function ($run) {
                    return in_array($run['status'], ['queued', 'in_progress', 'requires_action']);
                });
                
                if ($activeRun) {
                    $this->error = "Please wait for the current response to complete before sending another message.";
                    // Log the issue for debugging
                    Log::warning('Attempt to send message while a run is active', [
                        'user_id' => Auth::id(),
                        'thread_id' => $this->threadId,
                        'run_id' => $activeRun['id'],
                        'run_status' => $activeRun['status']
                    ]);
                    return;
                }
            }
        } catch (\Exception $e) {
            Log::error('Error checking for active runs', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'thread_id' => $this->threadId
            ]);
        }

        // Broadcast AI typing status
        $this->broadcastTypingStatus(true);
        $this->error = null;

        try {
            // Add user message to thread
            $userMessageResponse = $this->openAIAssistantsService->addMessage($this->threadId, $messageText, 'user');

            if (!$userMessageResponse['success']) {
                $this->error = "Failed to send message: " . ($userMessageResponse['message'] ?? 'Unknown error');
                $this->broadcastTypingStatus(false);
                return;
            }

            // Run the assistant - explicitly pass the assistant ID
            $runResponse = $this->openAIAssistantsService->createRun($this->threadId, $this->assistantId);

            if (!$runResponse['success']) {
                $this->error = "Failed to process message: " . ($runResponse['message'] ?? 'Unknown error');
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
                'thread_id' => $this->threadId
            ]);
            $this->error = "Error sending message: " . $e->getMessage();
            $this->broadcastTypingStatus(false);
        }

        // Clear the input field if using the property (not from JavaScript)
        if (!$message) {
            $this->newMessage = '';
        }
    }

    /**
     * Process the AI response - this could be moved to a job if needed
     */
    protected function processAiResponse($runId)
    {
        try {
            // This would be better handled through a queued job and websocket for real-time updates
            // For now, we'll simulate the polling behavior
            $status = 'queued';
            $maxRetries = 30;
            $retries = 0;
            
            while (in_array($status, ['queued', 'in_progress', 'requires_action']) && $retries < $maxRetries) {
                // Wait a moment before checking status
                usleep(500000); // 500ms
                
                $runStatusResponse = $this->openAIAssistantsService->retrieveRun($this->threadId, $runId);
                
                if (!$runStatusResponse['success']) {
                    $this->error = "Failed to check message status: " . ($runStatusResponse['message'] ?? 'Unknown error');
                    $this->broadcastTypingStatus(false);
                    break;
                }
                
                $status = $runStatusResponse['data']['status'];
                
                // Handle function calls (MCP tool execution)
                if ($status === 'requires_action') {
                    $this->handleFunctionCalls($runStatusResponse['data'], $runId);
                    $status = 'in_progress'; // Continue polling after submitting function outputs
                }
                
                $retries++;
            }
            
            if ($status === 'completed') {
                // Refresh messages from the API
                $this->loadMessages();
                
                // Signal that AI is done typing
                $this->broadcastTypingStatus(false);
            } else {
                $this->error = "Message processing failed or timed out with status: {$status}";
                $this->broadcastTypingStatus(false);
                
                Log::error('Message processing failed', [
                    'status' => $status,
                    'run_id' => $runId,
                    'thread_id' => $this->threadId
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error processing AI response', [
                'error' => $e->getMessage(),
                'run_id' => $runId,
                'thread_id' => $this->threadId
            ]);
            $this->error = "Error processing response: " . $e->getMessage();
            $this->broadcastTypingStatus(false);
        }
    }

    /**
     * Handle function calls from OpenAI Assistant (MCP tools)
     */
    protected function handleFunctionCalls($runData, $runId)
    {
        try {
            if (!isset($runData['required_action']['submit_tool_outputs']['tool_calls'])) {
                Log::warning('Function call action detected but no tool calls found', ['run_data' => $runData]);
                return;
            }

            $toolCalls = $runData['required_action']['submit_tool_outputs']['tool_calls'];
            $toolOutputs = [];

            foreach ($toolCalls as $toolCall) {
                Log::info('Processing MCP tool call', [
                    'tool_call_id' => $toolCall['id'],
                    'function_name' => $toolCall['function']['name'],
                    'arguments' => $toolCall['function']['arguments']
                ]);

                // Parse arguments (they come as JSON string)
                $arguments = json_decode($toolCall['function']['arguments'], true);
                
                // Execute the MCP function
                $result = $this->mcpIntegrationService->processFunctionCall(
                    $toolCall['function']['name'],
                    $arguments
                );

                // Prepare the output for OpenAI
                $toolOutputs[] = [
                    'tool_call_id' => $toolCall['id'],
                    'output' => json_encode($result)
                ];
            }

            // Submit the tool outputs back to OpenAI
            if (!empty($toolOutputs)) {
                $submitResponse = $this->openAIAssistantsService->submitToolOutputs(
                    $this->threadId,
                    $runId,
                    $toolOutputs
                );

                if (!$submitResponse['success']) {
                    Log::error('Failed to submit tool outputs', [
                        'error' => $submitResponse['message'] ?? 'Unknown error',
                        'tool_outputs' => $toolOutputs
                    ]);
                }
            }

        } catch (\Exception $e) {
            Log::error('Error handling function calls', [
                'error' => $e->getMessage(),
                'run_id' => $runId,
                'thread_id' => $this->threadId
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

    public function startNewChat()
    {
        try {
            // Create a new thread
            $threadResponse = $this->openAIAssistantsService->createThread();

            if ($threadResponse['success']) {
                $this->threadId = $threadResponse['data']['id'];
                session(['ai_sensei_thread_id' => $this->threadId]);
                $this->messages = [];
                $this->newMessage = '';
                $this->filesAttachedToThread = [];
                $this->error = null;

                // Dispatch event for message updates
                $this->dispatch('messages-updated');
            } else {
                $this->error = "Failed to start new chat: " . ($threadResponse['message'] ?? 'Unknown error');
            }
        } catch (\Exception $e) {
            Log::error('Error starting new chat', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            $this->error = "Error starting new chat: " . $e->getMessage();
        }
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
                    'id' => uniqid('pending_')
                ];
            }

            // Clear temporary uploads
            $this->temporaryUploads = [];

            // Emit event to update UI
            $this->dispatch('files-staged', [
                'count' => count($this->pendingFiles)
            ]);

        } catch (\Exception $e) {
            Log::error('Error staging file uploads', [
                'error' => $e->getMessage(),
            ]);
            $this->error = "Error preparing files: " . $e->getMessage();
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
                    $this->uploadedFiles = array_filter($this->uploadedFiles ?? [], function($file) use ($fileId) {
                        return $file['id'] !== $fileId;
                    });

                    // Refresh the list of attached files
                    $this->loadAttachedFiles();
                } else {
                    $this->error = "Failed to remove file: " . ($response['message'] ?? 'Unknown error');
                }
            }
        } catch (\Exception $e) {
            Log::error('Error removing file', [
                'error' => $e->getMessage(),
                'file_id' => $fileId,
                'thread_id' => $this->threadId
            ]);
            $this->error = "Error removing file: " . $e->getMessage();
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
            'thread_id' => $this->threadId
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
        
        return $typeMap[$extension] ?? strtoupper($extension) . ' File';
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
            $analysisQuery = $query ?? "Please analyze this uploaded file and provide a detailed summary of its contents, structure, and key insights.";

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
                    'run_id' => $result['data']['run_id']
                ];

                // Refresh messages to show both the upload and AI response
                $this->loadMessages();

                // Emit success event
                $this->dispatch('file-analyzed', [
                    'file_id' => $result['data']['file_info']['file_id'],
                    'filename' => $file->getClientOriginalName(),
                    'analysis_completed' => true
                ]);

                // Add a success message
                $this->dispatch('show-notification', [
                    'type' => 'success',
                    'message' => "File '{$file->getClientOriginalName()}' uploaded and analyzed successfully!"
                ]);

            } else {
                $this->error = "File analysis failed: " . $result['message'];
                
                Log::error('File analysis failed', [
                    'error' => $result['message'],
                    'step' => $result['step'] ?? 'unknown',
                    'file_name' => $file->getClientOriginalName(),
                    'thread_id' => $this->threadId
                ]);

                // If file was uploaded but analysis failed, we still have the file
                if (isset($result['file_info'])) {
                    $this->uploadedFiles[] = [
                        'id' => $result['file_info']['file_id'],
                        'filename' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'attached' => true,
                        'analyzed' => false,
                        'error' => $result['message']
                    ];
                }
            }

        } catch (\Exception $e) {
            Log::error('Error in uploadAndAnalyzeFile', [
                'error' => $e->getMessage(),
                'file_name' => $file->getClientOriginalName(),
                'thread_id' => $this->threadId
            ]);
            
            $this->error = "Failed to process file: " . $e->getMessage();
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
        $this->pendingFiles = array_filter($this->pendingFiles, function($file) use ($fileId) {
            return $file['id'] !== $fileId;
        });

        $this->dispatch('file-removed', [
            'file_id' => $fileId
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
                'pendingFiles' => array_map(function($file) {
                    return ['filename' => $file['filename'], 'size' => $file['size']];
                }, $this->pendingFiles)
            ]);

            if (empty($this->pendingFiles) && empty(trim($customMessage ?: $this->newMessage))) {
                Log::warning('sendMessageWithFiles: Nothing to send - no files and no message');
                return; // Nothing to send
            }

            $this->uploadingFile = true;
            $this->error = null;

            // Prepare the message content
            $messageText = trim($customMessage ?: $this->newMessage) ?: "I've shared some files with you.";
            
            if (!empty($this->pendingFiles)) {
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
                                ['type' => 'file_search']
                            ]
                        ];

                        $fileInfos[] = [
                            'id' => $fileId,
                            'filename' => $pendingFile['filename'],
                            'size' => $pendingFile['size'],
                            'type' => $pendingFile['type'],
                            'icon' => $pendingFile['icon']
                        ];
                    } else {
                        $this->error = "Failed to upload file: " . $pendingFile['filename'];
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
                            'message_text' => $messageText
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
                        'file_count' => count($fileInfos)
                    ]);
                } else {
                    $this->error = "Failed to send message with files: " . $result['message'];
                }
            } else {
                // Send regular message without files
                $this->sendMessage();
            }

        } catch (\Exception $e) {
            Log::error('Error sending message with files', [
                'error' => $e->getMessage(),
                'thread_id' => $this->threadId
            ]);
            $this->error = "Error sending message: " . $e->getMessage();
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
            if (!$this->assistantId) {
                Log::error('Assistant ID not set when triggering AI response for files');
                return;
            }

            // Broadcast AI typing status
            $this->broadcastTypingStatus(true);

            // Run the assistant to process the uploaded files
            $runResponse = $this->openAIAssistantsService->createRun($this->threadId, $this->assistantId);

            if (!$runResponse['success']) {
                $this->error = "Failed to process uploaded files: " . ($runResponse['message'] ?? 'Unknown error');
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
                'user_id' => Auth::id()
            ]);
            $this->broadcastTypingStatus(false);
            $this->error = "Error processing files: " . $e->getMessage();
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
                    'size' => $uploadedFile['size']
                ];
            }
        }

        // If not found, try to get from OpenAI API
        try {
            $response = $this->openAIFilesService->getFile($fileId);
            if ($response['success']) {
                return [
                    'filename' => $response['data']['filename'] ?? 'Unknown File',
                    'size' => $response['data']['bytes'] ?? 0
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Could not retrieve file info', ['file_id' => $fileId, 'error' => $e->getMessage()]);
        }

        return [
            'filename' => 'Attached File',
            'size' => 0
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
                'remaining_count' => count($this->pendingFiles)
            ]);
        }
    }

    public function render()
    {
        return view('livewire.communication.ai-sensei-chat')
            ->layout('components.dashboard.default', ['title' => 'AI Sensei Assistant']);
    }
}