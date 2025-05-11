<?php

namespace App\Livewire\Communication;

use App\Models\User;
use App\Services\Communication\Chat\OpenAI\OpenAIAssistantsService;
use App\Services\Communication\Chat\OpenAI\OpenAIFilesService;
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

    // Service injections
    protected $openAIAssistantsService;
    protected $openAIFilesService;

    // Constructor with dependency injection
    public function boot(OpenAIAssistantsService $openAIAssistantsService, OpenAIFilesService $openAIFilesService)
    {
        $this->openAIAssistantsService = $openAIAssistantsService;
        $this->openAIFilesService = $openAIFilesService;
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
            
            while (in_array($status, ['queued', 'in_progress']) && $retries < $maxRetries) {
                // Wait a moment before checking status
                usleep(500000); // 500ms
                
                $runStatusResponse = $this->openAIAssistantsService->retrieveRun($this->threadId, $runId);
                
                if (!$runStatusResponse['success']) {
                    $this->error = "Failed to check message status: " . ($runStatusResponse['message'] ?? 'Unknown error');
                    $this->broadcastTypingStatus(false);
                    break;
                }
                
                $status = $runStatusResponse['data']['status'];
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
            foreach ($this->temporaryUploads as $file) {
                $this->uploadingFile = true;

                // Upload the file to OpenAI
                $response = $this->openAIFilesService->uploadFile($file, 'assistants');

                if ($response['success']) {
                    // Debug the response to see what's being returned
                    Log::info('OpenAI file upload response', [
                        'response' => $response
                    ]);

                    // The file ID is stored in the data array in our service response
                    $fileId = $response['data']['file_id'] ?? $response['data']['id'] ?? null;

                    if (!$fileId) {
                        Log::error('Missing file ID in OpenAI response', [
                            'response' => $response,
                            'filename' => $file->getClientOriginalName()
                        ]);
                        $this->error = "Failed to get file ID from upload response";
                        continue;
                    }

                    // Attach the file to the thread
                    if ($this->threadId) {
                        $attachResponse = $this->openAIAssistantsService->attachFileToThread(
                            $this->threadId, 
                            $fileId
                        );

                        if ($attachResponse['success']) {
                            $this->uploadedFiles[] = [
                                'id' => $fileId,
                                'filename' => $file->getClientOriginalName(),
                                'size' => $file->getSize(),
                                'attached' => true
                            ];

                            // Emit an event about the successful file upload
                            $this->dispatch('file-uploaded', [
                                'file_id' => $fileId,
                                'filename' => $file->getClientOriginalName()
                            ]);
                        } else {
                            Log::error('Failed to attach file to thread', [
                                'error' => $attachResponse['message'] ?? 'Unknown error',
                                'file_id' => $fileId,
                                'thread_id' => $this->threadId
                            ]);
                            $this->error = "Failed to attach file: " . ($attachResponse['message'] ?? 'Unknown error');
                        }
                    }
                } else {
                    Log::error('Failed to upload file', [
                        'error' => $response['message'] ?? 'Unknown error',
                        'filename' => $file->getClientOriginalName()
                    ]);
                    $this->error = "Failed to upload file: " . ($response['message'] ?? 'Unknown error');
                }
            }

            // Clear the temporary uploads
            $this->temporaryUploads = [];

            // Refresh the list of attached files
            $this->loadAttachedFiles();
        } catch (\Exception $e) {
            Log::error('Error processing file upload', [
                'error' => $e->getMessage(),
            ]);
            $this->error = "Error uploading file: " . $e->getMessage();
        }

        $this->uploadingFile = false;
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

    public function render()
    {
        return view('livewire.communication.ai-sensei-chat')
            ->layout('components.dashboard.default', ['title' => 'AI Sensei Assistant']);
    }
}