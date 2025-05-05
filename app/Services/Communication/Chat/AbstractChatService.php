<?php

namespace App\Services\Communication\Chat;

use App\Models\ChatSession;
use App\Models\ChatMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

abstract class AbstractChatService implements ChatServiceInterface
{
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
            
            $session = ChatSession::create([
                'user_id' => $userId,
                'session_id' => $sessionId,
                'title' => $title ?? 'New Chat Session',
                'status' => 'active',
                'metadata' => $options['metadata'] ?? null,
                'last_activity_at' => now(),
            ]);
            
            return [
                'success' => true,
                'session_id' => $session->session_id,
                'title' => $session->title,
                'created_at' => $session->created_at,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to create chat session', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
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
            $session = ChatSession::where('session_id', $sessionId)
                ->where('status', '!=', 'deleted')
                ->firstOrFail();
            
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
                'message' => 'Chat session not found.',
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
            
            // Log the user message
            $userMessage = ChatMessage::create([
                'chat_session_id' => $session->id,
                'user_id' => $userId,
                'type' => 'user',
                'message' => $message,
            ]);
            
            // Update session activity
            $session->update([
                'last_activity_at' => now(),
            ]);
            
            // Send to AI model and get response
            $aiResponse = $this->getAiResponse($session->id, $message, $options);
            
            if (!$aiResponse['success']) {
                return $aiResponse;
            }
            
            // Log the AI response
            $aiMessage = ChatMessage::create([
                'chat_session_id' => $session->id,
                'user_id' => null,
                'type' => 'ai',
                'message' => $aiResponse['message'],
                'metadata' => $aiResponse['metadata'] ?? null,
            ]);
            
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
            $session = ChatSession::where('session_id', $sessionId)
                ->where('status', '!=', 'deleted')
                ->firstOrFail();
                
            $messages = ChatMessage::where('chat_session_id', $session->id)
                ->orderBy('created_at', 'desc')
                ->skip($offset)
                ->take($limit)
                ->get()
                ->map(function ($message) {
                    return [
                        'id' => $message->id,
                        'type' => $message->type,
                        'message' => $message->message,
                        'timestamp' => $message->created_at,
                    ];
                })
                ->toArray();
                
            return [
                'success' => true,
                'session_id' => $sessionId,
                'messages' => $messages,
                'total' => ChatMessage::where('chat_session_id', $session->id)->count(),
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
     * Update a session's status (active, archived, deleted).
     *
     * @param string $sessionId
     * @param string $status
     * @return bool
     */
    public function updateSessionStatus(string $sessionId, string $status)
    {
        try {
            $session = ChatSession::where('session_id', $sessionId)->firstOrFail();
            
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

    /**
     * Get a response from the AI model.
     *
     * @param int $sessionId
     * @param string $message
     * @param array $options
     * @return array
     */
    abstract protected function getAiResponse(int $sessionId, string $message, array $options = []): array;
}