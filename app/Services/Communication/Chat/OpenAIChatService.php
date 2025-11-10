<?php

namespace App\Services\Communication\Chat;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Services\Communication\Chat\OpenAI\OpenAIFilesService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIChatService extends AbstractChatService
{
    /**
     * OpenAI API URLs and model (non-sensitive configuration)
     */
    protected $apiUrl;
    protected $model;
    
    /**
     * OpenAI Files Service instance
     */
    protected OpenAIFilesService $openAIFilesService;

    /**
     * Constructor
     */
    public function __construct(OpenAIFilesService $openAIFilesService)
    {
        $this->apiUrl = Config::get('services.openai.url', 'https://api.openai.com/v1/chat/completions');
        $this->model = Config::get('services.openai.model', 'gpt-4-turbo');
        $this->openAIFilesService = $openAIFilesService;
    }
    
    /**
     * Get the API key dynamically from config
     * This prevents caching of sensitive credentials in singleton instances
     * 
     * @return string|null
     */
    protected function getApiKey(): ?string
    {
        return Config::get('services.openai.key');
    }

    /**
     * Get a response from the AI model.
     *
     * @param int $chatSessionId
     * @param string $message
     * @param array $options
     * @return array
     */
    protected function getAiResponse(int $chatSessionId, string $message, array $options = []): array
    {
        try {
            // Get previous messages for context, limited to the last 10
            $previousMessages = ChatMessage::where('chat_session_id', $chatSessionId)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->reverse();

            $messages = [];

            // System message to define the assistant's behavior
            $messages[] = [
                'role' => 'system',
                'content' => $options['system_message'] ?? 'You are a helpful AI assistant for our college system. Provide concise and accurate information to students and staff.',
            ];

            // Add previous messages to maintain conversation context
            foreach ($previousMessages as $prevMessage) {
                $messages[] = [
                    'role' => $prevMessage->type === 'user' ? 'user' : 'assistant',
                    'content' => $prevMessage->message,
                ];
            }

            // Add the current user message
            // Check if this is a document notification
            if (isset($options['is_document_notification']) && $options['is_document_notification']) {
                // Get document file ID from OpenAI if available
                $openAiFileId = $options['openai_file_id'] ?? null;
                $documentPath = $options['document_path'] ?? null;
                $documentInfo = "This message contains a document reference. ";
                
                if ($openAiFileId) {
                    // If we have an OpenAI file ID, include it in the message
                    $documentInfo .= "Document has been processed and is available as OpenAI file ID: $openAiFileId. ";
                    
                    // Get file information from OpenAI to include additional context
                    $fileInfo = $this->openAIFilesService->retrieveFile($openAiFileId);
                    if ($fileInfo['success']) {
                        $documentInfo .= "File details: " . json_encode($fileInfo['data']) . ". ";
                    }
                } else if ($documentPath) {
                    // Fallback if file wasn't uploaded to OpenAI
                    $documentInfo .= "Document can be accessed at path: $documentPath. ";
                }
                
                // Combine the document info with the user message
                $fullMessage = $documentInfo . $message;
                
                $messages[] = [
                    'role' => 'user',
                    'content' => $fullMessage,
                ];
            } else {
                // Regular message without document
                $messages[] = [
                    'role' => 'user',
                    'content' => $message,
                ];
            }

            // Prepare API request parameters
            $requestParams = [
                'model' => $options['model'] ?? $this->model,
                'messages' => $messages,
                'max_tokens' => $options['max_tokens'] ?? 1000,
                'temperature' => $options['temperature'] ?? 0.7,
            ];
            
            // If we have file references, add them to the request for models that support it
            if (!empty($options['openai_file_id'])) {
                $model = $options['model'] ?? $this->model;
                // Only add file references for models that support it
                if (in_array($model, ['gpt-4-turbo', 'gpt-4', 'gpt-4o'])) {
                    $requestParams['file_ids'] = [$options['openai_file_id']];
                }
            }
            
            // Make API request to OpenAI
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->getApiKey()}",
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, $requestParams);

            $responseData = $response->json();

            if ($response->successful() && isset($responseData['choices'][0]['message']['content'])) {
                $aiResponseText = $responseData['choices'][0]['message']['content'];
                
                return [
                    'success' => true,
                    'message' => $aiResponseText,
                    'metadata' => [
                        'model' => $responseData['model'] ?? $this->model,
                        'usage' => $responseData['usage'] ?? null,
                        'finish_reason' => $responseData['choices'][0]['finish_reason'] ?? null,
                    ],
                ];
            }

            Log::error('OpenAI API error', [
                'error' => $responseData['error'] ?? 'Unknown error',
                'session_id' => $chatSessionId,
            ]);

            return [
                'success' => false,
                'message' => 'Failed to get AI response: ' . ($responseData['error']['message'] ?? 'Unknown error'),
            ];
        } catch (\Exception $e) {
            Log::error('OpenAI service error', [
                'error' => $e->getMessage(),
                'session_id' => $chatSessionId,
            ]);

            return [
                'success' => false,
                'message' => 'Error processing AI response: ' . $e->getMessage(),
            ];
        }
    }
}