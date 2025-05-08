<?php

namespace App\Events\Communication;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserTypingEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The session ID for the chat.
     */
    public string $sessionId;

    /**
     * The typing status.
     */
    public bool $typing;
    
    /**
     * The user ID who is typing.
     */
    public ?int $userId;

    /**
     * Create a new event instance.
     */
    public function __construct(string $sessionId, bool $typing, ?int $userId = null)
    {
        $this->sessionId = $sessionId;
        $this->typing = $typing;
        $this->userId = $userId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->sessionId),
        ];
    }
    
    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'user.typing';
    }
}
