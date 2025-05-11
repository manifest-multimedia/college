<?php

namespace App\Services\Communication\Chat\OpenAI;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIAssistantsService
{
    /**
     * OpenAI API credentials and URLs
     */
    protected $apiKey;
    protected $baseUrl;
    protected $model;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->apiKey = Config::get('services.openai.key');
        $this->baseUrl = Config::get('services.openai.base_url', 'https://api.openai.com/v1');
        $this->model = Config::get('services.openai.model', 'gpt-4-turbo');
    }
    
    /**
     * Create a new assistant
     * 
     * @param string $name
     * @param string $instructions
     * @param array $tools
     * @param array $fileIds
     * @param string|null $model
     * @return array
     */
    public function createAssistant(
        string $name,
        string $instructions,
        array $tools = [],
        array $fileIds = [],
        ?string $model = null
    ) {
        try {
            $payload = [
                'name' => $name,
                'instructions' => $instructions,
                'model' => $model ?? $this->model,
            ];
            
            if (!empty($tools)) {
                $payload['tools'] = $tools;
            }
            
            if (!empty($fileIds)) {
                $payload['file_ids'] = $fileIds;
            }
            
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
                'OpenAI-Beta' => 'assistants=v2',
            ])->post("{$this->baseUrl}/assistants", $payload);
            
            $responseData = $response->json();
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $responseData,
                ];
            } else {
                Log::error('OpenAI assistant creation error', [
                    'error' => $responseData['error'] ?? 'Unknown error',
                    'name' => $name,
                ]);
                
                return [
                    'success' => false,
                    'message' => $responseData['error']['message'] ?? 'Unknown assistant creation error',
                ];
            }
        } catch (\Exception $e) {
            Log::error('OpenAI assistant creation failed', [
                'error' => $e->getMessage(),
                'name' => $name,
            ]);
            
            return [
                'success' => false,
                'message' => 'Assistant creation failed: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * List assistants
     * 
     * @param int $limit
     * @param string|null $order
     * @param string|null $after
     * @param string|null $before
     * @return array
     */
    public function listAssistants(int $limit = 20, ?string $order = null, ?string $after = null, ?string $before = null)
    {
        try {
            $queryParams = ['limit' => $limit];
            
            if ($order) {
                $queryParams['order'] = $order;
            }
            
            if ($after) {
                $queryParams['after'] = $after;
            }
            
            if ($before) {
                $queryParams['before'] = $before;
            }
            
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
                'OpenAI-Beta' => 'assistants=v2',
            ])->get("{$this->baseUrl}/assistants?" . http_build_query($queryParams));
            
            $responseData = $response->json();
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $responseData,
                ];
            } else {
                Log::error('OpenAI assistants listing error', [
                    'error' => $responseData['error'] ?? 'Unknown error',
                ]);
                
                return [
                    'success' => false,
                    'message' => $responseData['error']['message'] ?? 'Unknown assistants listing error',
                ];
            }
        } catch (\Exception $e) {
            Log::error('OpenAI assistants listing failed', [
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Assistants listing failed: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Retrieve an assistant
     * 
     * @param string $assistantId
     * @return array
     */
    public function getAssistant(string $assistantId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
                'OpenAI-Beta' => 'assistants=v2',
            ])->get("{$this->baseUrl}/assistants/{$assistantId}");
            
            $responseData = $response->json();
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $responseData,
                ];
            } else {
                Log::error('OpenAI assistant retrieval error', [
                    'error' => $responseData['error'] ?? 'Unknown error',
                    'assistant_id' => $assistantId,
                ]);
                
                return [
                    'success' => false,
                    'message' => $responseData['error']['message'] ?? 'Unknown assistant retrieval error',
                ];
            }
        } catch (\Exception $e) {
            Log::error('OpenAI assistant retrieval failed', [
                'error' => $e->getMessage(),
                'assistant_id' => $assistantId,
            ]);
            
            return [
                'success' => false,
                'message' => 'Assistant retrieval failed: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Modify an assistant
     * 
     * @param string $assistantId
     * @param array $data
     * @return array
     */
    public function updateAssistant(string $assistantId, array $data)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
                'OpenAI-Beta' => 'assistants=v2',
            ])->post("{$this->baseUrl}/assistants/{$assistantId}", $data);
            
            $responseData = $response->json();
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $responseData,
                ];
            } else {
                Log::error('OpenAI assistant update error', [
                    'error' => $responseData['error'] ?? 'Unknown error',
                    'assistant_id' => $assistantId,
                ]);
                
                return [
                    'success' => false,
                    'message' => $responseData['error']['message'] ?? 'Unknown assistant update error',
                ];
            }
        } catch (\Exception $e) {
            Log::error('OpenAI assistant update failed', [
                'error' => $e->getMessage(),
                'assistant_id' => $assistantId,
            ]);
            
            return [
                'success' => false,
                'message' => 'Assistant update failed: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Delete an assistant
     * 
     * @param string $assistantId
     * @return array
     */
    public function deleteAssistant(string $assistantId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
                'OpenAI-Beta' => 'assistants=v2',
            ])->delete("{$this->baseUrl}/assistants/{$assistantId}");
            
            $responseData = $response->json();
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $responseData,
                ];
            } else {
                Log::error('OpenAI assistant deletion error', [
                    'error' => $responseData['error'] ?? 'Unknown error',
                    'assistant_id' => $assistantId,
                ]);
                
                return [
                    'success' => false,
                    'message' => $responseData['error']['message'] ?? 'Unknown assistant deletion error',
                ];
            }
        } catch (\Exception $e) {
            Log::error('OpenAI assistant deletion failed', [
                'error' => $e->getMessage(),
                'assistant_id' => $assistantId,
            ]);
            
            return [
                'success' => false,
                'message' => 'Assistant deletion failed: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Create a new thread
     * 
     * @param array|null $messages
     * @param array|null $metadata
     * @return array
     */
    public function createThread(?array $messages = null, ?array $metadata = null)
    {
        try {
            $payload = [];
            
            if ($messages) {
                $payload['messages'] = $messages;
            }
            
            if ($metadata) {
                $payload['metadata'] = $metadata;
            }
            
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
                'OpenAI-Beta' => 'assistants=v2',
            ])->post("{$this->baseUrl}/threads", $payload);
            
            $responseData = $response->json();
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $responseData,
                ];
            } else {
                Log::error('OpenAI thread creation error', [
                    'error' => $responseData['error'] ?? 'Unknown error',
                ]);
                
                return [
                    'success' => false,
                    'message' => $responseData['error']['message'] ?? 'Unknown thread creation error',
                ];
            }
        } catch (\Exception $e) {
            Log::error('OpenAI thread creation failed', [
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Thread creation failed: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Retrieve a thread
     * 
     * @param string $threadId
     * @return array
     */
    public function getThread(string $threadId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
                'OpenAI-Beta' => 'assistants=v2',
            ])->get("{$this->baseUrl}/threads/{$threadId}");
            
            $responseData = $response->json();
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $responseData,
                ];
            } else {
                Log::error('OpenAI thread retrieval error', [
                    'error' => $responseData['error'] ?? 'Unknown error',
                    'thread_id' => $threadId,
                ]);
                
                return [
                    'success' => false,
                    'message' => $responseData['error']['message'] ?? 'Unknown thread retrieval error',
                ];
            }
        } catch (\Exception $e) {
            Log::error('OpenAI thread retrieval failed', [
                'error' => $e->getMessage(),
                'thread_id' => $threadId,
            ]);
            
            return [
                'success' => false,
                'message' => 'Thread retrieval failed: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Modify a thread
     * 
     * @param string $threadId
     * @param array $metadata
     * @return array
     */
    public function updateThread(string $threadId, array $metadata)
    {
        try {
            $payload = [
                'metadata' => $metadata,
            ];
            
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
                'OpenAI-Beta' => 'assistants=v2',
            ])->post("{$this->baseUrl}/threads/{$threadId}", $payload);
            
            $responseData = $response->json();
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $responseData,
                ];
            } else {
                Log::error('OpenAI thread update error', [
                    'error' => $responseData['error'] ?? 'Unknown error',
                    'thread_id' => $threadId,
                ]);
                
                return [
                    'success' => false,
                    'message' => $responseData['error']['message'] ?? 'Unknown thread update error',
                ];
            }
        } catch (\Exception $e) {
            Log::error('OpenAI thread update failed', [
                'error' => $e->getMessage(),
                'thread_id' => $threadId,
            ]);
            
            return [
                'success' => false,
                'message' => 'Thread update failed: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Delete a thread
     * 
     * @param string $threadId
     * @return array
     */
    public function deleteThread(string $threadId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
                'OpenAI-Beta' => 'assistants=v2',
            ])->delete("{$this->baseUrl}/threads/{$threadId}");
            
            $responseData = $response->json();
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $responseData,
                ];
            } else {
                Log::error('OpenAI thread deletion error', [
                    'error' => $responseData['error'] ?? 'Unknown error',
                    'thread_id' => $threadId,
                ]);
                
                return [
                    'success' => false,
                    'message' => $responseData['error']['message'] ?? 'Unknown thread deletion error',
                ];
            }
        } catch (\Exception $e) {
            Log::error('OpenAI thread deletion failed', [
                'error' => $e->getMessage(),
                'thread_id' => $threadId,
            ]);
            
            return [
                'success' => false,
                'message' => 'Thread deletion failed: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Add a message to a thread
     * 
     * @param string $threadId
     * @param string $content
     * @param string $role
     * @param array|null $fileIds
     * @param array|null $metadata
     * @return array
     */
    public function addMessage(
        string $threadId,
        string $content,
        string $role = 'user',
        ?array $fileIds = null,
        ?array $metadata = null
    ) {
        try {
            $payload = [
                'role' => $role,
                'content' => $content,
            ];
            
            if ($fileIds) {
                $payload['file_ids'] = $fileIds;
            }
            
            if ($metadata) {
                $payload['metadata'] = $metadata;
            }
            
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
                'OpenAI-Beta' => 'assistants=v2',
            ])->post("{$this->baseUrl}/threads/{$threadId}/messages", $payload);
            
            $responseData = $response->json();
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $responseData,
                ];
            } else {
                Log::error('OpenAI message creation error', [
                    'error' => $responseData['error'] ?? 'Unknown error',
                    'thread_id' => $threadId,
                ]);
                
                return [
                    'success' => false,
                    'message' => $responseData['error']['message'] ?? 'Unknown message creation error',
                ];
            }
        } catch (\Exception $e) {
            Log::error('OpenAI message creation failed', [
                'error' => $e->getMessage(),
                'thread_id' => $threadId,
            ]);
            
            return [
                'success' => false,
                'message' => 'Message creation failed: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * List messages in a thread
     * 
     * @param string $threadId
     * @param int $limit
     * @param string|null $order
     * @param string|null $after
     * @param string|null $before
     * @return array
     */
    public function listMessages(
        string $threadId,
        int $limit = 20,
        ?string $order = null,
        ?string $after = null,
        ?string $before = null
    ) {
        try {
            $queryParams = ['limit' => $limit];
            
            if ($order) {
                $queryParams['order'] = $order;
            }
            
            if ($after) {
                $queryParams['after'] = $after;
            }
            
            if ($before) {
                $queryParams['before'] = $before;
            }
            
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
                'OpenAI-Beta' => 'assistants=v2',
            ])->get("{$this->baseUrl}/threads/{$threadId}/messages?" . http_build_query($queryParams));
            
            $responseData = $response->json();
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $responseData,
                ];
            } else {
                Log::error('OpenAI messages listing error', [
                    'error' => $responseData['error'] ?? 'Unknown error',
                    'thread_id' => $threadId,
                ]);
                
                return [
                    'success' => false,
                    'message' => $responseData['error']['message'] ?? 'Unknown messages listing error',
                ];
            }
        } catch (\Exception $e) {
            Log::error('OpenAI messages listing failed', [
                'error' => $e->getMessage(),
                'thread_id' => $threadId,
            ]);
            
            return [
                'success' => false,
                'message' => 'Messages listing failed: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Retrieve a message
     * 
     * @param string $threadId
     * @param string $messageId
     * @return array
     */
    public function getMessage(string $threadId, string $messageId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
                'OpenAI-Beta' => 'assistants=v2',
            ])->get("{$this->baseUrl}/threads/{$threadId}/messages/{$messageId}");
            
            $responseData = $response->json();
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $responseData,
                ];
            } else {
                Log::error('OpenAI message retrieval error', [
                    'error' => $responseData['error'] ?? 'Unknown error',
                    'thread_id' => $threadId,
                    'message_id' => $messageId,
                ]);
                
                return [
                    'success' => false,
                    'message' => $responseData['error']['message'] ?? 'Unknown message retrieval error',
                ];
            }
        } catch (\Exception $e) {
            Log::error('OpenAI message retrieval failed', [
                'error' => $e->getMessage(),
                'thread_id' => $threadId,
                'message_id' => $messageId,
            ]);
            
            return [
                'success' => false,
                'message' => 'Message retrieval failed: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Run an assistant on a thread
     * 
     * @param string $threadId
     * @param string $assistantId
     * @param string|null $instructions
     * @param array|null $tools
     * @param array|null $metadata
     * @return array
     */
    public function createRun(
        string $threadId,
        string $assistantId,
        ?string $instructions = null,
        ?array $tools = null,
        ?array $metadata = null
    ) {
        try {
            $payload = [
                'assistant_id' => $assistantId,
            ];
            
            if ($instructions) {
                $payload['instructions'] = $instructions;
            }
            
            if ($tools) {
                $payload['tools'] = $tools;
            }
            
            if ($metadata) {
                $payload['metadata'] = $metadata;
            }
            
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
                'OpenAI-Beta' => 'assistants=v2',
            ])->post("{$this->baseUrl}/threads/{$threadId}/runs", $payload);
            
            $responseData = $response->json();
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $responseData,
                ];
            } else {
                Log::error('OpenAI run creation error', [
                    'error' => $responseData['error'] ?? 'Unknown error',
                    'thread_id' => $threadId,
                    'assistant_id' => $assistantId,
                ]);
                
                return [
                    'success' => false,
                    'message' => $responseData['error']['message'] ?? 'Unknown run creation error',
                ];
            }
        } catch (\Exception $e) {
            Log::error('OpenAI run creation failed', [
                'error' => $e->getMessage(),
                'thread_id' => $threadId,
                'assistant_id' => $assistantId,
            ]);
            
            return [
                'success' => false,
                'message' => 'Run creation failed: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Retrieve the status of a run
     * 
     * @param string $threadId
     * @param string $runId
     * @return array
     */
    public function retrieveRun(string $threadId, string $runId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
                'OpenAI-Beta' => 'assistants=v2',
            ])->get("{$this->baseUrl}/threads/{$threadId}/runs/{$runId}");
            
            $responseData = $response->json();
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $responseData,
                ];
            } else {
                Log::error('OpenAI run retrieval error', [
                    'error' => $responseData['error'] ?? 'Unknown error',
                    'thread_id' => $threadId,
                    'run_id' => $runId,
                ]);
                
                return [
                    'success' => false,
                    'message' => $responseData['error']['message'] ?? 'Unknown run retrieval error',
                ];
            }
        } catch (\Exception $e) {
            Log::error('OpenAI run retrieval failed', [
                'error' => $e->getMessage(),
                'thread_id' => $threadId,
                'run_id' => $runId,
            ]);
            
            return [
                'success' => false,
                'message' => 'Run retrieval failed: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Attach a file to a thread
     * 
     * @param string $threadId
     * @param string $fileId
     * @return array
     */
    public function attachFileToThread(string $threadId, string $fileId)
    {
        try {
            // In v2 API, we attach files by creating a message with file_ids array
            $payload = [
                'role' => 'user',
                'content' => '', // Empty content for file-only message
                'file_ids' => [$fileId], // Use file_ids as an array parameter
            ];
            
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
                'OpenAI-Beta' => 'assistants=v2',
            ])->post("{$this->baseUrl}/threads/{$threadId}/messages", $payload);
            
            $responseData = $response->json();
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $responseData,
                ];
            } else {
                Log::error('OpenAI file attachment error', [
                    'error' => $responseData['error'] ?? 'Unknown error',
                    'thread_id' => $threadId,
                    'file_id' => $fileId,
                    'response' => $responseData,
                ]);
                
                return [
                    'success' => false,
                    'message' => $responseData['error']['message'] ?? 'Unknown file attachment error',
                ];
            }
        } catch (\Exception $e) {
            Log::error('OpenAI file attachment failed', [
                'error' => $e->getMessage(),
                'thread_id' => $threadId,
                'file_id' => $fileId,
            ]);
            
            return [
                'success' => false,
                'message' => 'File attachment failed: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * List files attached to a thread
     * 
     * @param string $threadId
     * @param int $limit
     * @param string|null $order
     * @param string|null $after
     * @param string|null $before
     * @return array
     */
    public function listThreadFiles(
        string $threadId,
        int $limit = 20,
        ?string $order = null,
        ?string $after = null,
        ?string $before = null
    ) {
        try {
            $queryParams = ['limit' => $limit];
            
            if ($order) {
                $queryParams['order'] = $order;
            }
            
            if ($after) {
                $queryParams['after'] = $after;
            }
            
            if ($before) {
                $queryParams['before'] = $before;
            }
            
            // Use v2 API as v1 is deprecated
            // In v2, we need to list all messages and collect their file IDs
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
                'OpenAI-Beta' => 'assistants=v2',
            ])->get("{$this->baseUrl}/threads/{$threadId}/messages?" . http_build_query($queryParams));
            
            $responseData = $response->json();
            
            if ($response->successful()) {
                // Process messages to extract file information
                $files = [];
                if (isset($responseData['data']) && is_array($responseData['data'])) {
                    foreach ($responseData['data'] as $message) {
                        if (isset($message['file_ids']) && is_array($message['file_ids'])) {
                            foreach ($message['file_ids'] as $fileId) {
                                // Get file details - in v2, we need to do this separately
                                $fileResponse = $this->getFileDetails($fileId);
                                if ($fileResponse['success'] && isset($fileResponse['data'])) {
                                    $files[] = $fileResponse['data'];
                                }
                            }
                        }
                        
                        // Also check content for any files
                        if (isset($message['content']) && is_array($message['content'])) {
                            foreach ($message['content'] as $content) {
                                if (isset($content['type']) && $content['type'] === 'file' && isset($content['file_id'])) {
                                    $fileResponse = $this->getFileDetails($content['file_id']);
                                    if ($fileResponse['success'] && isset($fileResponse['data'])) {
                                        $files[] = $fileResponse['data'];
                                    }
                                }
                            }
                        }
                    }
                }
                
                return [
                    'success' => true,
                    'data' => [
                        'data' => $files
                    ],
                ];
            } else {
                Log::error('OpenAI thread files listing error', [
                    'error' => $responseData['error'] ?? 'Unknown error',
                    'thread_id' => $threadId,
                ]);
                
                return [
                    'success' => false,
                    'message' => $responseData['error']['message'] ?? 'Unknown thread files listing error',
                ];
            }
        } catch (\Exception $e) {
            Log::error('OpenAI thread files listing failed', [
                'error' => $e->getMessage(),
                'thread_id' => $threadId,
            ]);
            
            return [
                'success' => false,
                'message' => 'Thread files listing failed: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Get file details from OpenAI
     * 
     * @param string $fileId
     * @return array
     */
    protected function getFileDetails(string $fileId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
                'OpenAI-Beta' => 'assistants=v2',
            ])->get("{$this->baseUrl}/files/{$fileId}");
            
            $responseData = $response->json();
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $responseData,
                ];
            } else {
                Log::error('OpenAI file details retrieval error', [
                    'error' => $responseData['error'] ?? 'Unknown error',
                    'file_id' => $fileId,
                ]);
                
                return [
                    'success' => false,
                    'message' => $responseData['error']['message'] ?? 'Unknown file details retrieval error',
                ];
            }
        } catch (\Exception $e) {
            Log::error('OpenAI file details retrieval failed', [
                'error' => $e->getMessage(),
                'file_id' => $fileId,
            ]);
            
            return [
                'success' => false,
                'message' => 'File details retrieval failed: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Retrieve a file attached to a thread
     * 
     * @param string $threadId
     * @param string $fileId
     * @return array
     */
    public function getThreadFile(string $threadId, string $fileId)
    {
        // In v2, we directly get the file details
        return $this->getFileDetails($fileId);
    }
    
    /**
     * Remove a file from a thread
     * 
     * @param string $threadId
     * @param string $fileId
     * @return array
     */
    public function removeFileFromThread(string $threadId, string $fileId)
    {
        try {
            // In v2 API, we can't directly remove a file from a thread
            // Instead, we can delete the file from OpenAI entirely
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
                'OpenAI-Beta' => 'assistants=v2',
            ])->delete("{$this->baseUrl}/files/{$fileId}");
            
            $responseData = $response->json();
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $responseData,
                ];
            } else {
                Log::error('OpenAI file removal error', [
                    'error' => $responseData['error'] ?? 'Unknown error',
                    'thread_id' => $threadId,
                    'file_id' => $fileId,
                ]);
                
                return [
                    'success' => false,
                    'message' => $responseData['error']['message'] ?? 'Unknown file removal error',
                ];
            }
        } catch (\Exception $e) {
            Log::error('OpenAI file removal failed', [
                'error' => $e->getMessage(),
                'thread_id' => $threadId,
                'file_id' => $fileId,
            ]);
            
            return [
                'success' => false,
                'message' => 'File removal failed: ' . $e->getMessage(),
            ];
        }
    }
}