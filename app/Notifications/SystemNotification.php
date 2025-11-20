<?php

namespace App\Notifications;

class SystemNotification extends BaseNotification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(string $title, string $message, ?string $actionUrl = null, string $type = 'info')
    {
        $this->data = [
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
        ];

        $this->type = $type;
    }
}
