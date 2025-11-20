<?php

namespace App\Events\Communication;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DocumentUploadedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The session ID
     */
    public string $sessionId = '';

    /**
     * Document details
     */
    public array $document = [];

    /**
     * Create a new event instance.
     */
    public function __construct(string $sessionId, array $document)
    {
        $this->sessionId = $sessionId;
        $this->document = $document;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('chat.'.$this->sessionId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'document.uploaded';
    }

    /**
     * Data to broadcast with the event.
     */
    public function broadcastWith(): array
    {
        return [
            'sessionId' => $this->sessionId,
            'document' => $this->document ?? [],
        ];
    }
}
