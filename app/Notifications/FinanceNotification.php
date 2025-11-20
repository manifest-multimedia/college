<?php

namespace App\Notifications;

class FinanceNotification extends BaseNotification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(string $title, string $message, ?string $transactionId = null, string $type = 'info')
    {
        $this->data = [
            'title' => $title,
            'message' => $message,
            'action_url' => $transactionId ? route('student.finance.transaction', $transactionId) : null,
            'transaction_id' => $transactionId,
        ];

        $this->type = $type;
    }
}
