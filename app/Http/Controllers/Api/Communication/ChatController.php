<?php

namespace App\Http\Controllers\Api\Communication;

use App\Http\Controllers\Controller;
use App\Models\ChatSession;
use App\Services\Communication\Chat\ChatServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Events\Communication\UserTypingEvent;

class ChatController extends Controller
{
    /**
     * The Chat Service implementation.
     */
    protected ChatServiceInterface $chatService;

    /**
     * Create a new controller instance.
     */
    public function __construct(ChatServiceInterface $chatService)
    {
        $this->chatService = $chatService;
    }

    /**
     * Create a new chat session.
     */
    public function createSession(Request $request): JsonResponse
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->chatService->createSession(
                auth()->id(),
                $request->input('title'),
                $request->input('metadata', [])
            );

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Failed to create chat session', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create chat session: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a chat session by its ID.
     */
    public function getSession(string $sessionId): JsonResponse
    {
        try {
            $result = $this->chatService->getSession($sessionId);

            if (!$result['success']) {
                return response()->json($result, 404);
            }

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Failed to get chat session', [
                'error' => $e->getMessage(),
                'session_id' => $sessionId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get chat session: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send a message to the AI and get a response.
     */
    public function sendMessage(Request $request): JsonResponse
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string',
            'message' => 'required|string',
            'options' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->chatService->sendMessage(
                $request->input('session_id'),
                $request->input('message'),
                auth()->id(),
                $request->input('options', [])
            );

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Failed to send message', [
                'error' => $e->getMessage(),
                'session_id' => $request->input('session_id'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send message: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get the message history for a session.
     */
    public function getMessageHistory(Request $request, string $sessionId): JsonResponse
    {
        try {
            $limit = $request->input('limit', 50);
            $offset = $request->input('offset', 0);

            $result = $this->chatService->getMessageHistory($sessionId, $limit, $offset);

            if (!$result['success']) {
                return response()->json($result, 404);
            }

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Failed to get message history', [
                'error' => $e->getMessage(),
                'session_id' => $sessionId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get message history: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a session's status (active, archived, deleted).
     */
    public function updateSessionStatus(Request $request, string $sessionId): JsonResponse
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:active,archived,deleted',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->chatService->updateSessionStatus(
                $sessionId,
                $request->input('status')
            );

            return response()->json([
                'success' => $result,
                'message' => $result ? 'Session status updated successfully' : 'Failed to update session status',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update session status', [
                'error' => $e->getMessage(),
                'session_id' => $sessionId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update session status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all chat sessions for the authenticated user.
     */
    public function getUserSessions(Request $request): JsonResponse
    {
        try {
            $query = ChatSession::where('user_id', auth()->id())
                ->where('status', '!=', 'deleted');

            if ($request->has('status')) {
                $query->where('status', $request->input('status'));
            }

            $perPage = $request->input('per_page', 15);
            $sessions = $query->orderBy('last_activity_at', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $sessions,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get user sessions', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get user sessions: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update typing status for a chat session.
     */
    public function updateTypingStatus(Request $request): JsonResponse
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string',
            'typing' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Broadcast typing status
            broadcast(new UserTypingEvent(
                $request->input('session_id'),
                $request->input('typing'),
                auth()->id()
            ))->toOthers();
            
            return response()->json([
                'success' => true,
                'message' => 'Typing status updated'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update typing status', [
                'error' => $e->getMessage(),
                'session_id' => $request->input('session_id'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update typing status: ' . $e->getMessage(),
            ], 500);
        }
    }
}
