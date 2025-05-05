<?php

namespace App\Services\Communication\Email;

use App\Mail\GenericEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LaravelMailService extends AbstractEmailService
{
    /**
     * Send an email using Laravel's built-in Mail system.
     *
     * @param string $recipient
     * @param string $subject
     * @param string|array $message
     * @param array $options
     * @return array
     */
    protected function send(string $recipient, string $subject, $message, array $options = []): array
    {
        try {
            // Prepare the email data
            $emailData = [
                'subject' => $subject,
                'content' => is_array($message) ? $message['content'] ?? '' : $message,
                'template' => $options['template'] ?? 'emails.generic',
                'attachments' => $options['attachments'] ?? [],
            ];

            // Send the email
            Mail::to($recipient)
                ->when(!empty($options['cc']), function ($mail) use ($options) {
                    return $mail->cc($options['cc']);
                })
                ->when(!empty($options['bcc']), function ($mail) use ($options) {
                    return $mail->bcc($options['bcc']);
                })
                ->queue(new GenericEmail($emailData));

            return [
                'success' => true,
                'provider_message_id' => null,
                'provider_response' => ['message' => 'Email queued successfully'],
            ];
        } catch (\Exception $e) {
            Log::error('Laravel Mail sending error', [
                'recipient' => $recipient,
                'subject' => $subject,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error_message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get the provider name.
     *
     * @return string
     */
    protected function getProviderName(): string
    {
        return 'laravel_mail';
    }
}