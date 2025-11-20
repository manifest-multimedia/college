<?php

namespace App\Services\Communication\Chat;

interface ChatServiceInterface
{
    /**
     * Create a new chat session.
     *
     * @return array
     */
    public function createSession(?int $userId, ?string $title = null, array $options = []);

    /**
     * Get a chat session by its ID.
     *
     * @return array
     */
    public function getSession(string $sessionId);

    /**
     * Send a message to the AI model and get a response.
     *
     * @return array
     */
    public function sendMessage(string $sessionId, string $message, ?int $userId = null, array $options = []);

    /**
     * Get the message history for a session.
     *
     * @return array
     */
    public function getMessageHistory(string $sessionId, int $limit = 50, int $offset = 0);

    /**
     * Update a session's status (active, archived, deleted).
     *
     * @return bool
     */
    public function updateSessionStatus(string $sessionId, string $status);
}
