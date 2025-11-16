<?php

namespace App\Services\Communication\Chat;

use App\Models\ChatSession;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ChatSessionService
{
    /**
     * Get or create active chat session for user
     */
    public function getOrCreateActiveSession(User $user, string $threadId): ChatSession
    {
        // First, try to find an active session with this thread ID
        $session = ChatSession::where('user_id', $user->id)
            ->where('session_id', $threadId)
            ->where('status', 'active')
            ->first();

        if ($session) {
            // Update last activity
            $session->update(['last_activity_at' => now()]);
            return $session;
        }

        // Create new session if none exists
        return $this->createNewSession($user, $threadId);
    }

    /**
     * Create a new chat session
     */
    public function createNewSession(User $user, string $threadId, ?string $title = null): ChatSession
    {
        return ChatSession::create([
            'user_id' => $user->id,
            'session_id' => $threadId,
            'title' => $title ?? 'New Chat - ' . now()->format('M j, Y g:i A'),
            'status' => 'active',
            'metadata' => [
                'assistant_id' => config('services.openai.assistant_id'),
                'created_via' => 'ai_sensei_chat'
            ],
            'last_activity_at' => now(),
        ]);
    }

    /**
     * Get user's chat sessions with pagination
     */
    public function getUserSessions(User $user, int $limit = 20, string $status = 'active')
    {
        return ChatSession::where('user_id', $user->id)
            ->where('status', $status)
            ->orderBy('last_activity_at', 'desc')
            ->with(['messages' => function ($query) {
                $query->orderBy('created_at', 'desc')->limit(1);
            }])
            ->paginate($limit);
    }

    /**
     * Get recent sessions for sidebar
     */
    public function getRecentSessions(User $user, int $limit = 10)
    {
        return ChatSession::where('user_id', $user->id)
            ->where('status', 'active')
            ->orderBy('last_activity_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'session_id' => $session->session_id,
                    'title' => $session->title,
                    'last_activity_at' => $session->last_activity_at,
                    'last_activity_human' => $session->last_activity_at->diffForHumans(),
                    'message_count' => $session->messages()->count(),
                ];
            });
    }

    /**
     * Update session title
     */
    public function updateSessionTitle(string $sessionId, string $title, User $user): bool
    {
        $session = ChatSession::where('session_id', $sessionId)
            ->where('user_id', $user->id)
            ->first();

        if ($session) {
            $session->update([
                'title' => $title,
                'last_activity_at' => now()
            ]);
            return true;
        }

        return false;
    }

    /**
     * Archive a session
     */
    public function archiveSession(string $sessionId, User $user): bool
    {
        $session = ChatSession::where('session_id', $sessionId)
            ->where('user_id', $user->id)
            ->first();

        if ($session) {
            $session->update(['status' => 'archived']);
            return true;
        }

        return false;
    }

    /**
     * Delete a session and its messages
     */
    public function deleteSession(string $sessionId, User $user): bool
    {
        $session = ChatSession::where('session_id', $sessionId)
            ->where('user_id', $user->id)
            ->first();

        if ($session) {
            // Delete all messages first
            $session->messages()->delete();
            // Delete the session
            $session->delete();
            return true;
        }

        return false;
    }

    /**
     * Store a chat message
     */
    public function storeMessage(
        ChatSession $session,
        string $type,
        string $message,
        ?array $metadata = null,
        ?string $openaiFileId = null,
        ?string $fileName = null,
        ?string $filePath = null,
        ?string $mimeType = null,
        ?int $fileSize = null
    ): ChatMessage {
        return ChatMessage::create([
            'chat_session_id' => $session->id,
            'user_id' => $type === 'user' ? $session->user_id : null,
            'type' => $type,
            'is_document' => !empty($fileName),
            'message' => $message,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'mime_type' => $mimeType,
            'file_size' => $fileSize,
            'openai_file_id' => $openaiFileId,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Sync OpenAI messages with local database
     */
    public function syncMessagesFromOpenAI(ChatSession $session, array $openaiMessages): void
    {
        try {
            // Get existing message IDs from database
            $existingMessageIds = $session->messages()
                ->whereNotNull('metadata')
                ->get()
                ->filter(function ($message) {
                    return isset($message->metadata['openai_message_id']);
                })
                ->pluck('metadata.openai_message_id')
                ->toArray();

            foreach ($openaiMessages as $openaiMessage) {
                $messageId = $openaiMessage['id'];

                // Skip if message already exists
                if (in_array($messageId, $existingMessageIds)) {
                    continue;
                }

                $content = '';
                $attachments = [];
                $metadata = [
                    'openai_message_id' => $messageId,
                    'openai_created_at' => $openaiMessage['created_at'],
                ];

                // Process message content
                foreach ($openaiMessage['content'] as $contentItem) {
                    if ($contentItem['type'] === 'text') {
                        $content .= $contentItem['text']['value'] . "\n";
                    }
                }

                // Process attachments if any
                if (!empty($openaiMessage['attachments'])) {
                    $metadata['attachments'] = $openaiMessage['attachments'];
                }

                // Store the message
                $this->storeMessage(
                    $session,
                    $openaiMessage['role'] === 'user' ? 'user' : 'ai',
                    trim($content),
                    $metadata
                );
            }

            // Update session last activity
            $session->update(['last_activity_at' => now()]);

        } catch (\Exception $e) {
            Log::error('Error syncing messages from OpenAI', [
                'session_id' => $session->session_id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Generate an intelligent title for a session based on the first few messages
     */
    public function generateSessionTitle(ChatSession $session): string
    {
        $messages = $session->messages()
            ->where('type', 'user')
            ->orderBy('created_at', 'asc')
            ->limit(2)
            ->pluck('message')
            ->toArray();

        if (empty($messages)) {
            return 'New Chat - ' . $session->created_at->format('M j, Y');
        }

        $firstMessage = $messages[0];
        
        // Extract key topics or create a summary
        $words = explode(' ', $firstMessage);
        
        if (count($words) <= 6) {
            return ucfirst(trim($firstMessage, '.,!?'));
        }

        // Take first 6 words and add ellipsis
        $title = implode(' ', array_slice($words, 0, 6));
        
        // Clean up and capitalize
        $title = ucfirst(strtolower(trim($title, '.,!?'))) . '...';
        
        // Some smart detection for common patterns
        if (stripos($firstMessage, 'create') === 0) {
            return 'Create Request - ' . $session->created_at->format('M j');
        }
        
        if (stripos($firstMessage, 'help') === 0 || stripos($firstMessage, 'how') === 0) {
            return 'Help Request - ' . $session->created_at->format('M j');
        }
        
        if (stripos($firstMessage, 'explain') === 0) {
            return 'Explanation - ' . $session->created_at->format('M j');
        }

        return $title;
    }

    /**
     * Get session statistics
     */
    public function getSessionStats(User $user): array
    {
        $sessions = ChatSession::where('user_id', $user->id);

        return [
            'total_sessions' => $sessions->count(),
            'active_sessions' => $sessions->where('status', 'active')->count(),
            'archived_sessions' => $sessions->where('status', 'archived')->count(),
            'total_messages' => ChatMessage::whereIn('chat_session_id', 
                $sessions->pluck('id')
            )->count(),
            'this_month_sessions' => $sessions->where('created_at', '>=', now()->startOfMonth())->count(),
        ];
    }

    /**
     * Search user's chat sessions
     */
    public function searchUserSessions(User $user, string $query, int $limit = 10)
    {
        return ChatSession::where('user_id', $user->id)
            ->where('status', 'active')
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                  ->orWhereHas('messages', function ($messageQuery) use ($query) {
                      $messageQuery->where('message', 'LIKE', "%{$query}%");
                  });
            })
            ->orderBy('last_activity_at', 'desc')
            ->limit($limit)
            ->get();
    }
}