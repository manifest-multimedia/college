<?php

namespace App\Services\Communication\Chat;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIChatService extends AbstractChatService
{
    /**
     * OpenAI API credentials
     */
    protected $apiKey;
    protected $apiUrl;
    protected $model;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->apiKey = Config::get('services.openai.key');
        $this->apiUrl = Config::get('services.openai.url', 'https://api.openai.com/v1/chat/completions');
        $this->model = Config::get('services.openai.model', 'gpt-4-turbo');
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
            $messages[] = [
                'role' => 'user',
                'content' => $message,
            ];

            // Make API request to OpenAI
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, [
                'model' => $options['model'] ?? $this->model,
                'messages' => $messages,
                'max_tokens' => $options['max_tokens'] ?? 1000,
                'temperature' => $options['temperature'] ?? 0.7,
            ]);

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