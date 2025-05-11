<?php

namespace App\Services\Communication\Chat;

use App\Events\Communication\AiTypingEvent;
use App\Events\Communication\NewMessageEvent;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Services\Communication\Chat\OpenAI\OpenAIAssistantsService;
use App\Services\Communication\Chat\OpenAI\OpenAIFilesService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AssistantsChatService implements ChatServiceInterface
{
    /**
     * OpenAI Assistants API Service
     */
    protected OpenAIAssistantsService $assistantsService;
    
    /**
     * OpenAI Files Service
     */
    protected OpenAIFilesService $filesService;
    
    /**
     * Constructor
     */
    public function __construct(OpenAIAssistantsService $assistantsService, OpenAIFilesService $filesService)
    {
        $this->assistantsService = $assistantsService;
        $this->filesService = $filesService;
    }
    
    /**
     * Create a new chat session.
     *
     * @param int|null $userId
     * @param string|null $title
     * @param array $options
     * @return array
     */
    public function createSession(?int $userId, ?string $title = null, array $options = [])
    {
        try {
            $sessionId = $options['session_id'] ?? (string) Str::uuid();
            
            // Create a thread in OpenAI Assistants API
            $threadResult = $this->assistantsService->createThread([
                'metadata' => [
                    'user_id' => (string) $userId,
                    'session_id' => $sessionId,
                ],
            ]);
            
            if (!$threadResult['success']) {
                Log::error('Failed to create OpenAI thread', [
                    'error' => $threadResult['message'] ?? 'Unknown error',
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Failed to create chat session: ' . ($threadResult['message'] ?? 'Unknown error'),
                ];
            }
            
            // Save the thread ID in the session metadata
            $threadId = $threadResult['data']['id'];
            
            // Create local session record
            $session = ChatSession::create([
                'user_id' => $userId,
                'session_id' => $sessionId,
                'title' => $title ?? 'New Chat with AI Sensei',
                'status' => 'active',
                'metadata' => [
                    'thread_id' => $threadId,
                    'assistant_capabilities' => [
                        'file_upload' => true,
                        'code_interpreter' => true,
                    ],
                ],
            ]);
            
            return [
                'success' => true,
                'session_id' => $sessionId,
                'thread_id' => $threadId,
                'session' => $session,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to create chat session', [
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to create chat session: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Get a chat session by its ID.
     *
     * @param string $sessionId
     * @return array
     */
    public function getSession(string $sessionId)
    {
        try {
            $session = ChatSession::where('session_id', $sessionId)->first();
            
            if (!$session) {
                return [
                    'success' => false,
                    'message' => 'Chat session not found.',
                ];
            }
            
            // Check if the thread still exists in OpenAI
            $threadId = $session->metadata['thread_id'] ?? null;
            
            if ($threadId) {
                $threadResult = $this->assistantsService->getThread($threadId);
                
                if (!$threadResult['success']) {
                    // Thread doesn't exist, we might need to create a new one
                    Log::warning('OpenAI thread not found, creating a new one', [
                        'session_id' => $sessionId,
                        'old_thread_id' => $threadId,
                    ]);
                    
                    $newThreadResult = $this->assistantsService->createThread([
                        'metadata' => [
                            'user_id' => (string) $session->user_id,
                            'session_id' => $sessionId,
                        ],
                    ]);
                    
                    if ($newThreadResult['success']) {
                        $newThreadId = $newThreadResult['data']['id'];
                        
                        // Update session metadata
                        $metadata = $session->metadata ?? [];
                        $metadata['thread_id'] = $newThreadId;
                        $session->metadata = $metadata;
                        $session->save();
                    } else {
                        Log::error('Failed to create new OpenAI thread', [
                            'session_id' => $sessionId,
                            'error' => $newThreadResult['message'] ?? 'Unknown error',
                        ]);
                    }
                }
            }
            
            return [
                'success' => true,
                'session' => $session,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get chat session', [
                'error' => $e->getMessage(),
                'session_id' => $sessionId,
            ]);
            
            return [
                'success' => false,
                'message' => 'Chat session error: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Send a message to the AI model and get a response.
     *
     * @param string $sessionId
     * @param string $message
     * @param int|null $userId
     * @param array $options
     * @return array
     */
    public function sendMessage(string $sessionId, string $message, ?int $userId = null, array $options = [])
    {
        try {
            // Get or create session
            $sessionResult = $this->getSession($sessionId);
            
            if (!$sessionResult['success']) {
                // Create a new session if it doesn't exist
                $sessionResult = $this->createSession($userId, null, ['session_id' => $sessionId]);
                
                if (!$sessionResult['success']) {
                    return $sessionResult;
                }
                
                $session = ChatSession::where('session_id', $sessionId)->firstOrFail();
            } else {
                $session = $sessionResult['session'];
            }
            
            // Get the thread ID from the session metadata
            $threadId = $session->metadata['thread_id'] ?? null;
            
            if (!$threadId) {
                // Create a new thread if none exists
                $threadResult = $this->assistantsService->createThread([
                    'metadata' => [
                        'user_id' => (string) $userId,
                        'session_id' => $sessionId,
                    ],
                ]);
                
                if (!$threadResult['success']) {
                    return [
                        'success' => false,
                        'message' => 'Failed to create thread: ' . ($threadResult['message'] ?? 'Unknown error'),
                    ];
                }
                
                $threadId = $threadResult['data']['id'];
                
                // Update session metadata
                $metadata = $session->metadata ?? [];
                $metadata['thread_id'] = $threadId;
                $session->metadata = $metadata;
                $session->save();
            }
            
            // Log the user message in our database
            $userMessage = ChatMessage::create([
                'chat_session_id' => $session->id,
                'user_id' => $userId,
                'type' => 'user',
                'message' => $message,
                'metadata' => [
                    'is_document' => $options['is_document_notification'] ?? false,
                    'document_path' => $options['document_path'] ?? null,
                ],
            ]);
            
            // Update session activity timestamp
            $session->update([
                'last_activity_at' => now(),
            ]);
            
            // Format user message for broadcasting
            $userMessageData = [
                'id' => $userMessage->id,
                'type' => $userMessage->type,
                'message' => $userMessage->message,
                'timestamp' => $userMessage->created_at,
                'is_document' => $options['is_document_notification'] ?? false,
                'document_path' => $options['document_path'] ?? null,
            ];
            
            // Broadcast user message
            broadcast(new NewMessageEvent($sessionId, $userMessageData))->toOthers();
            
            // Handle file uploads if any
            $fileIds = [];
            $openAiFileId = $options['openai_file_id'] ?? null;
            
            if ($openAiFileId) {
                $fileIds[] = $openAiFileId;
            }
            
            // Send message to OpenAI Assistant
            broadcast(new AiTypingEvent($sessionId, true))->toOthers();
            
            // Add the message to the thread
            $addMessageResult = $this->assistantsService->addMessage(
                $threadId, 
                $message, 
                $fileIds,
                [
                    'user_id' => (string) $userId,
                    'message_id' => (string) $userMessage->id,
                ]
            );
            
            if (!$addMessageResult['success']) {
                // Stop AI typing indicator
                broadcast(new AiTypingEvent($sessionId, false))->toOthers();
                
                return [
                    'success' => false,
                    'message' => 'Failed to send message to AI: ' . ($addMessageResult['message'] ?? 'Unknown error'),
                ];
            }
            
            // Run the assistant on the thread
            $runResult = $this->assistantsService->runAssistant($threadId);
            
            if (!$runResult['success']) {
                // Stop AI typing indicator
                broadcast(new AiTypingEvent($sessionId, false))->toOthers();
                
                return [
                    'success' => false,
                    'message' => 'Failed to run AI assistant: ' . ($runResult['message'] ?? 'Unknown error'),
                ];
            }
            
            $runId = $runResult['data']['id'];
            
            // Poll for completion
            $completionResult = $this->assistantsService->pollRunCompletion($threadId, $runId, 60, 1);
            
            // Stop AI typing indicator
            broadcast(new AiTypingEvent($sessionId, false))->toOthers();
            
            if (!$completionResult['success'] || ($completionResult['data']['status'] ?? '') !== 'completed') {
                $errorStatus = $completionResult['data']['status'] ?? 'unknown';
                $errorMessage = $completionResult['message'] ?? "Run failed with status: $errorStatus";
                
                return [
                    'success' => false,
                    'message' => 'AI processing failed: ' . $errorMessage,
                ];
            }
            
            // Get the latest messages
            $messagesResult = $this->assistantsService->listMessages($threadId, ['limit' => 1]);
            
            if (!$messagesResult['success'] || empty($messagesResult['data']['data'])) {
                return [
                    'success' => false,
                    'message' => 'Failed to retrieve AI response: ' . ($messagesResult['message'] ?? 'No message returned'),
                ];
            }
            
            // The latest message should be from the assistant
            $assistantMessage = $messagesResult['data']['data'][0];
            
            if ($assistantMessage['role'] !== 'assistant') {
                // Get another message
                if (count($messagesResult['data']['data']) > 1) {
                    foreach ($messagesResult['data']['data'] as $msg) {
                        if ($msg['role'] === 'assistant') {
                            $assistantMessage = $msg;
                            break;
                        }
                    }
                }
            }
            
            if ($assistantMessage['role'] !== 'assistant') {
                return [
                    'success' => false,
                    'message' => 'Did not receive an assistant response',
                ];
            }
            
            // Process the assistant's message content
            $aiResponseText = '';
            
            foreach ($assistantMessage['content'] as $content) {
                if ($content['type'] === 'text') {
                    $aiResponseText .= $content['text']['value'] . "\n\n";
                } else if ($content['type'] === 'image_file') {
                    // Handle image files if needed
                    $aiResponseText .= "[Image attachment]\n\n";
                }
            }
            
            $aiResponseText = trim($aiResponseText);
            
            // Log the AI response in our database
            $aiMessage = ChatMessage::create([
                'chat_session_id' => $session->id,
                'user_id' => null,
                'type' => 'ai',
                'message' => $aiResponseText,
                'metadata' => [
                    'assistant_message_id' => $assistantMessage['id'],
                    'run_id' => $runId,
                ],
            ]);
            
            // Format AI message for broadcasting
            $aiMessageData = [
                'id' => $aiMessage->id,
                'type' => $aiMessage->type,
                'message' => $aiMessage->message,
                'timestamp' => $aiMessage->created_at,
            ];
            
            // Broadcast AI message
            broadcast(new NewMessageEvent($sessionId, $aiMessageData))->toOthers();
            
            return [
                'success' => true,
                'session_id' => $session->session_id,
                'user_message' => $userMessage->message,
                'ai_response' => $aiMessage->message,
                'timestamp' => $aiMessage->created_at,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to send message to AI', [
                'error' => $e->getMessage(),
                'session_id' => $sessionId,
            ]);
            
            // Stop AI typing indicator
            broadcast(new AiTypingEvent($sessionId, false))->toOthers();
            
            return [
                'success' => false,
                'message' => 'Failed to process message: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Get the message history for a session.
     *
     * @param string $sessionId
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getMessageHistory(string $sessionId, int $limit = 50, int $offset = 0)
    {
        try {
            $sessionResult = $this->getSession($sessionId);
            
            if (!$sessionResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Session not found',
                ];
            }
            
            $session = $sessionResult['session'];
            
            // Get messages from database
            $messages = ChatMessage::where('chat_session_id', $session->id)
                ->orderBy('created_at', 'desc')
                ->skip($offset)
                ->take($limit)
                ->get()
                ->map(function ($message) {
                    $isDocument = $message->metadata['is_document'] ?? false;
                    $documentPath = $message->metadata['document_path'] ?? null;
                    
                    return [
                        'id' => $message->id,
                        'type' => $message->type,
                        'message' => $message->message,
                        'timestamp' => $message->created_at,
                        'is_document' => $isDocument,
                        'document_path' => $documentPath,
                    ];
                })
                ->reverse()
                ->values()
                ->toArray();
            
            return [
                'success' => true,
                'messages' => $messages,
                'count' => count($messages),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get message history', [
                'error' => $e->getMessage(),
                'session_id' => $sessionId,
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to get message history: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Update a session's status.
     * 
     * @param string $sessionId
     * @param string $status
     * @return bool
     */
    public function updateSessionStatus(string $sessionId, string $status)
    {
        try {
            $session = ChatSession::where('session_id', $sessionId)->first();
            
            if (!$session) {
                return false;
            }
            
            $session->update([
                'status' => $status,
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to update session status', [
                'error' => $e->getMessage(),
                'session_id' => $sessionId,
            ]);
            
            return false;
        }
    }
}