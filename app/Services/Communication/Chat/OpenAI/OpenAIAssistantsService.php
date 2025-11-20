<?php

namespace App\Services\Communication\Chat\OpenAI;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIAssistantsService
{
    /**
     * OpenAI API URLs and model (non-sensitive configuration)
     */
    protected $baseUrl;

    protected $model;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->baseUrl = Config::get('services.openai.base_url', 'https://api.openai.com/v1');
        $this->model = Config::get('services.openai.model', 'gpt-4-turbo');
    }

    /**
     * Get the API key dynamically from config
     * This prevents caching of sensitive credentials in singleton instances
     */
    protected function getApiKey(): ?string
    {
        return Config::get('services.openai.key');
    }

    /**
     * Create a new assistant
     *
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

            if (! empty($tools)) {
                $payload['tools'] = $tools;
            }

            // Note: For assistants, file attachments are now handled through tool_resources
            // file_ids parameter is deprecated in v2 API for assistants
            if (! empty($fileIds)) {
                // Convert to tool_resources format for v2 API
                $payload['tool_resources'] = [
                    'file_search' => ['file_ids' => $fileIds],
                    'code_interpreter' => ['file_ids' => $fileIds],
                ];
            }

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->getApiKey()}",
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
                'message' => 'Assistant creation failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * List assistants
     *
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
                'Authorization' => "Bearer {$this->getApiKey()}",
                'Content-Type' => 'application/json',
                'OpenAI-Beta' => 'assistants=v2',
            ])->get("{$this->baseUrl}/assistants?".http_build_query($queryParams));

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
                'message' => 'Assistants listing failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Retrieve an assistant
     *
     * @return array
     */
    public function getAssistant(string $assistantId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->getApiKey()}",
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
                'message' => 'Assistant retrieval failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Modify an assistant
     *
     * @return array
     */
    public function updateAssistant(string $assistantId, array $data)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->getApiKey()}",
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
                'message' => 'Assistant update failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Delete an assistant
     *
     * @return array
     */
    public function deleteAssistant(string $assistantId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->getApiKey()}",
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
                'message' => 'Assistant deletion failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Create a new thread
     *
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
                'Authorization' => "Bearer {$this->getApiKey()}",
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
                'message' => 'Thread creation failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Retrieve a thread
     *
     * @return array
     */
    public function getThread(string $threadId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->getApiKey()}",
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
                'message' => 'Thread retrieval failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Modify a thread
     *
     * @return array
     */
    public function updateThread(string $threadId, array $metadata)
    {
        try {
            $payload = [
                'metadata' => $metadata,
            ];

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->getApiKey()}",
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
                'message' => 'Thread update failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Delete a thread
     *
     * @return array
     */
    public function deleteThread(string $threadId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->getApiKey()}",
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
                'message' => 'Thread deletion failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Add a message to a thread
     *
     * @param  array|null  $fileIds  (deprecated - use attachments instead)
     * @param  array|null  $attachments  - New format: [['file_id' => 'file-xxx', 'tools' => [...]]]
     * @return array
     */
    public function addMessage(
        string $threadId,
        string $content,
        string $role = 'user',
        ?array $fileIds = null,
        ?array $metadata = null,
        ?array $attachments = null
    ) {
        try {
            $payload = [
                'role' => $role,
                'content' => $content,
            ];

            // Handle new attachments format (v2 API)
            if ($attachments) {
                $payload['attachments'] = $attachments;
            }
            // Handle legacy file_ids format for backward compatibility
            elseif ($fileIds) {
                $payload['attachments'] = array_map(function ($fileId) {
                    return [
                        'file_id' => $fileId,
                        'tools' => [
                            ['type' => 'code_interpreter'],
                            ['type' => 'file_search'],
                        ],
                    ];
                }, $fileIds);
            }

            if ($metadata) {
                $payload['metadata'] = $metadata;
            }

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->getApiKey()}",
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
                'message' => 'Message creation failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * List messages in a thread
     *
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
                'Authorization' => "Bearer {$this->getApiKey()}",
                'Content-Type' => 'application/json',
                'OpenAI-Beta' => 'assistants=v2',
            ])->get("{$this->baseUrl}/threads/{$threadId}/messages?".http_build_query($queryParams));

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
                'message' => 'Messages listing failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Retrieve a message
     *
     * @return array
     */
    public function getMessage(string $threadId, string $messageId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->getApiKey()}",
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
                'message' => 'Message retrieval failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Run an assistant on a thread
     *
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
                'Authorization' => "Bearer {$this->getApiKey()}",
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
                'message' => 'Run creation failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Retrieve the status of a run
     *
     * @return array
     */
    public function retrieveRun(string $threadId, string $runId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->getApiKey()}",
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
                'message' => 'Run retrieval failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * List runs for a thread
     *
     * @return array
     */
    public function listRuns(
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
                'Authorization' => "Bearer {$this->getApiKey()}",
                'Content-Type' => 'application/json',
                'OpenAI-Beta' => 'assistants=v2',
            ])->get("{$this->baseUrl}/threads/{$threadId}/runs?".http_build_query($queryParams));

            $responseData = $response->json();

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $responseData,
                ];
            } else {
                Log::error('OpenAI thread runs listing error', [
                    'error' => $responseData['error'] ?? 'Unknown error',
                    'thread_id' => $threadId,
                ]);

                return [
                    'success' => false,
                    'message' => $responseData['error']['message'] ?? 'Unknown thread runs listing error',
                ];
            }
        } catch (\Exception $e) {
            Log::error('OpenAI thread runs listing failed', [
                'error' => $e->getMessage(),
                'thread_id' => $threadId,
            ]);

            return [
                'success' => false,
                'message' => 'Thread runs listing failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Cancel a run
     *
     * @return array
     */
    public function cancelRun(string $threadId, string $runId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->getApiKey()}",
                'Content-Type' => 'application/json',
                'OpenAI-Beta' => 'assistants=v2',
            ])->post("{$this->baseUrl}/threads/{$threadId}/runs/{$runId}/cancel");

            $responseData = $response->json();

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $responseData,
                ];
            } else {
                Log::error('OpenAI run cancellation error', [
                    'error' => $responseData['error'] ?? 'Unknown error',
                    'thread_id' => $threadId,
                    'run_id' => $runId,
                ]);

                return [
                    'success' => false,
                    'message' => $responseData['error']['message'] ?? 'Unknown run cancellation error',
                ];
            }
        } catch (\Exception $e) {
            Log::error('OpenAI run cancellation failed', [
                'error' => $e->getMessage(),
                'thread_id' => $threadId,
                'run_id' => $runId,
            ]);

            return [
                'success' => false,
                'message' => 'Run cancellation failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Attach a file to a thread
     *
     * @return array
     */
    public function attachFileToThread(string $threadId, string $fileId, string $content = "I've uploaded a file for you to analyze.")
    {
        try {
            // In v2 API, we attach files using attachments instead of file_ids
            $payload = [
                'role' => 'user',
                'content' => $content,
                'attachments' => [
                    [
                        'file_id' => $fileId,
                        'tools' => [
                            ['type' => 'code_interpreter'],
                            ['type' => 'file_search'],
                        ],
                    ],
                ],
            ];

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->getApiKey()}",
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
                'message' => 'File attachment failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * List files attached to a thread
     *
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
                'Authorization' => "Bearer {$this->getApiKey()}",
                'Content-Type' => 'application/json',
                'OpenAI-Beta' => 'assistants=v2',
            ])->get("{$this->baseUrl}/threads/{$threadId}/messages?".http_build_query($queryParams));

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
                        'data' => $files,
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
                'message' => 'Thread files listing failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Get file details from OpenAI
     *
     * @return array
     */
    protected function getFileDetails(string $fileId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->getApiKey()}",
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
                'message' => 'File details retrieval failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Retrieve a file attached to a thread
     *
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
     * @return array
     */
    public function removeFileFromThread(string $threadId, string $fileId)
    {
        try {
            // In v2 API, we can't directly remove a file from a thread
            // Instead, we can delete the file from OpenAI entirely
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->getApiKey()}",
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
                'message' => 'File removal failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Upload file and create message with attachment in one step
     * This is a convenience method that handles the complete workflow
     *
     * @param  array  $tools  - Tools to enable for the file ['code_interpreter', 'file_search']
     * @return array
     */
    public function uploadFileAndCreateMessage(
        string $threadId,
        \Illuminate\Http\UploadedFile $file,
        string $content = "I've uploaded a file for you to analyze.",
        array $tools = ['code_interpreter', 'file_search']
    ) {
        try {
            // Step 1: Upload file to OpenAI
            $filesService = app(\App\Services\Communication\Chat\OpenAI\OpenAIFilesService::class);
            $uploadResult = $filesService->uploadFile($file, 'assistants');

            if (! $uploadResult['success']) {
                return [
                    'success' => false,
                    'message' => 'File upload failed: '.$uploadResult['message'],
                    'step' => 'file_upload',
                ];
            }

            $fileId = $uploadResult['data']['file_id'];

            // Step 2: Create message with file attachment
            $attachments = [
                [
                    'file_id' => $fileId,
                    'tools' => array_map(fn ($tool) => ['type' => $tool], $tools),
                ],
            ];

            $messageResult = $this->addMessage(
                $threadId,
                $content,
                'user',
                null, // fileIds (deprecated)
                null, // metadata
                $attachments // new attachments format
            );

            if (! $messageResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Message creation failed: '.$messageResult['message'],
                    'step' => 'message_creation',
                    'file_id' => $fileId,
                ];
            }

            return [
                'success' => true,
                'data' => [
                    'file_id' => $fileId,
                    'message' => $messageResult['data'],
                    'file_info' => $uploadResult['data'],
                ],
            ];

        } catch (\Exception $e) {
            Log::error('Complete file upload and message creation failed', [
                'error' => $e->getMessage(),
                'thread_id' => $threadId,
                'file_name' => $file->getClientOriginalName(),
            ]);

            return [
                'success' => false,
                'message' => 'Complete file processing failed: '.$e->getMessage(),
                'step' => 'exception',
            ];
        }
    }

    /**
     * Process uploaded file and get AI response
     * This method handles the complete workflow including running the assistant
     *
     * @param  string  $query  - What to ask about the file
     * @param  array  $tools  - Tools to enable for the file
     * @return array
     */
    public function processFileWithAI(
        string $threadId,
        string $assistantId,
        \Illuminate\Http\UploadedFile $file,
        string $query = 'Please analyze this file and provide a summary.',
        array $tools = ['code_interpreter', 'file_search']
    ) {
        try {
            // Step 1: Upload file and create message
            $uploadResult = $this->uploadFileAndCreateMessage($threadId, $file, $query, $tools);

            if (! $uploadResult['success']) {
                return $uploadResult;
            }

            // Step 2: Run the assistant
            $runResult = $this->createRun($threadId, $assistantId);

            if (! $runResult['success']) {
                return [
                    'success' => false,
                    'message' => 'Failed to run assistant: '.$runResult['message'],
                    'step' => 'assistant_run',
                    'file_info' => $uploadResult['data'],
                ];
            }

            $runId = $runResult['data']['id'];

            // Step 3: Wait for completion (with timeout)
            $maxAttempts = 60; // 60 attempts = ~2 minutes
            $attempt = 0;

            do {
                sleep(2); // Wait 2 seconds between checks
                $attempt++;

                $statusResult = $this->retrieveRun($threadId, $runId);

                if (! $statusResult['success']) {
                    return [
                        'success' => false,
                        'message' => 'Failed to check run status: '.$statusResult['message'],
                        'step' => 'status_check',
                        'file_info' => $uploadResult['data'],
                    ];
                }

                $status = $statusResult['data']['status'];

                if ($status === 'completed') {
                    // Step 4: Get the response
                    $messagesResult = $this->listMessages($threadId, 1, 'desc');

                    if (! $messagesResult['success']) {
                        return [
                            'success' => false,
                            'message' => 'Failed to retrieve AI response: '.$messagesResult['message'],
                            'step' => 'response_retrieval',
                            'file_info' => $uploadResult['data'],
                        ];
                    }

                    $messages = $messagesResult['data']['data'] ?? [];
                    $latestMessage = $messages[0] ?? null;

                    return [
                        'success' => true,
                        'data' => [
                            'file_info' => $uploadResult['data'],
                            'run_id' => $runId,
                            'response' => $latestMessage,
                            'status' => 'completed',
                        ],
                    ];
                }

                if (in_array($status, ['failed', 'cancelled', 'expired'])) {
                    return [
                        'success' => false,
                        'message' => "Assistant run failed with status: {$status}",
                        'step' => 'assistant_execution',
                        'file_info' => $uploadResult['data'],
                        'run_status' => $status,
                    ];
                }

            } while ($attempt < $maxAttempts && in_array($status, ['queued', 'in_progress', 'requires_action']));

            // Timeout reached
            return [
                'success' => false,
                'message' => 'Assistant processing timeout. The file is uploaded but processing is taking longer than expected.',
                'step' => 'timeout',
                'file_info' => $uploadResult['data'],
                'run_id' => $runId,
                'last_status' => $status ?? 'unknown',
            ];

        } catch (\Exception $e) {
            Log::error('Complete file processing with AI failed', [
                'error' => $e->getMessage(),
                'thread_id' => $threadId,
                'assistant_id' => $assistantId,
                'file_name' => $file->getClientOriginalName(),
            ]);

            return [
                'success' => false,
                'message' => 'File processing with AI failed: '.$e->getMessage(),
                'step' => 'exception',
            ];
        }
    }

    /**
     * Retrieve assistant details
     */
    public function retrieveAssistant(string $assistantId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->getApiKey()}",
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
                'message' => 'Assistant retrieval failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Submit tool outputs for a run that requires action
     *
     * @param  array  $toolOutputs  - Format: [['tool_call_id' => 'call_xxx', 'output' => 'result']]
     */
    public function submitToolOutputs(string $threadId, string $runId, array $toolOutputs): array
    {
        try {
            $payload = [
                'tool_outputs' => $toolOutputs,
            ];

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->getApiKey()}",
                'Content-Type' => 'application/json',
                'OpenAI-Beta' => 'assistants=v2',
            ])->post("{$this->baseUrl}/threads/{$threadId}/runs/{$runId}/submit_tool_outputs", $payload);

            $responseData = $response->json();

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $responseData,
                ];
            } else {
                Log::error('OpenAI tool outputs submission error', [
                    'error' => $responseData['error'] ?? 'Unknown error',
                    'thread_id' => $threadId,
                    'run_id' => $runId,
                    'tool_outputs' => $toolOutputs,
                ]);

                return [
                    'success' => false,
                    'message' => $responseData['error']['message'] ?? 'Unknown tool outputs submission error',
                ];
            }
        } catch (\Exception $e) {
            Log::error('OpenAI tool outputs submission failed', [
                'error' => $e->getMessage(),
                'thread_id' => $threadId,
                'run_id' => $runId,
            ]);

            return [
                'success' => false,
                'message' => 'Tool outputs submission failed: '.$e->getMessage(),
            ];
        }
    }
}
