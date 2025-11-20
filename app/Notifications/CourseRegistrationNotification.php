<?php

namespace App\Notifications;

class CourseRegistrationNotification extends BaseNotification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(string $title, string $message, ?string $courseId = null, string $type = 'info')
    {
        $this->data = [
            'title' => $title,
            'message' => $message,
            'action_url' => $courseId ? route('student.courses.show', $courseId) : null,
            'course_id' => $courseId,
        ];

        $this->type = $type;
    }
}
