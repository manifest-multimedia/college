<?php

namespace App\Notifications;

use App\Models\ExamClearance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExamClearanceNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The exam clearance instance.
     *
     * @var ExamClearance
     */
    protected $clearance;

    /**
     * Create a new notification instance.
     */
    public function __construct(ExamClearance $clearance)
    {
        $this->clearance = $clearance;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        // Add mail channel if the user has an email
        if ($notifiable->email) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $exam = $this->clearance->clearable;
        $examType = $this->clearance->isOnlineExam() ? 'Online Exam' : 'Offline Exam';
        $examTitle = $exam->title ?? ($exam->course->title ?? 'Exam');
        $status = $this->clearance->is_cleared ? 'Cleared' : 'Not Cleared';
        $action = $this->clearance->is_cleared ? 'View Entry Ticket' : 'Check Fee Status';

        return (new MailMessage)
            ->subject("Exam Clearance Status: {$status}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your clearance status for {$examType}: {$examTitle} is: {$status}.")
            ->when($this->clearance->is_cleared, function ($message) use ($exam) {
                $venue = $this->clearance->isOfflineExam() ? "Venue: {$exam->venue}" : '';
                $date = $this->clearance->isOfflineExam()
                    ? 'Date: '.$exam->date->format('l, F j, Y \a\t g:i A')
                    : '';

                return $message->line('Your exam entry ticket has been issued.')
                    ->line($venue)
                    ->line($date);
            })
            ->when(! $this->clearance->is_cleared, function ($message) use ($exam) {
                $threshold = $exam->clearance_threshold ?? 60;

                return $message->line("You need to pay at least {$threshold}% of your fees to be cleared for this exam.");
            })
            ->action($action, $this->getActionUrl())
            ->line('If you have any questions, please contact the finance office.')
            ->salutation('Best regards, Your College Administration');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $exam = $this->clearance->clearable;
        $examType = $this->clearance->isOnlineExam() ? 'Online Exam' : 'Offline Exam';
        $examTitle = $exam->title ?? ($exam->course->title ?? 'Exam');

        return [
            'clearance_id' => $this->clearance->id,
            'exam_id' => $exam->id,
            'exam_type' => $examType,
            'exam_title' => $examTitle,
            'is_cleared' => $this->clearance->is_cleared,
            'status' => $this->clearance->status,
            'created_at' => $this->clearance->created_at,
            'message' => $this->clearance->is_cleared
                ? "You have been cleared for {$examType}: {$examTitle}"
                : "You have not been cleared for {$examType}: {$examTitle}. Please check your fee status.",
        ];
    }

    /**
     * Get the action URL for the notification.
     */
    protected function getActionUrl(): string
    {
        if ($this->clearance->is_cleared) {
            // URL to view entry ticket
            return route('student.exam.entry-ticket', ['clearance_id' => $this->clearance->id]);
        } else {
            // URL to view fee status
            return route('student.fees.status');
        }
    }
}
