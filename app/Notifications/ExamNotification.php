<?php

namespace App\Notifications;

class ExamNotification extends BaseNotification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(string $title, string $message, string $examId = null, string $type = 'info')
    {
        $this->data = [
            'title' => $title,
            'message' => $message,
            'action_url' => $examId ? route('student.exams.show', $examId) : null,
            'exam_id' => $examId,
        ];
        
        $this->type = $type;
    }
}