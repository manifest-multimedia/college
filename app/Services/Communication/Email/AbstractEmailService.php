<?php

namespace App\Services\Communication\Email;

use App\Models\EmailLog;
use App\Models\RecipientList;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

abstract class AbstractEmailService implements EmailServiceInterface
{
    /**
     * Send an email to a single recipient.
     *
     * @param  string|array  $message
     * @return array
     */
    public function sendSingle(string $recipient, string $subject, $message, array $options = [])
    {
        // Validate email
        if (! $this->validateEmail($recipient)) {
            Log::error('Invalid email address', ['recipient' => $recipient]);

            return [
                'success' => false,
                'message' => 'Invalid email address format',
            ];
        }

        try {
            // Send email using provider-specific implementation
            $result = $this->send($recipient, $subject, $message, $options);

            // Log email details
            $this->logEmail($recipient, $subject, $message, $result, 'single', $options);

            return [
                'success' => true,
                'message' => 'Email sent successfully',
                'data' => $result,
            ];
        } catch (\Exception $e) {
            Log::error('Email sending failed', [
                'recipient' => $recipient,
                'subject' => $subject,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send email: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Send the same email to multiple recipients.
     *
     * @param  string|array  $message
     * @return array
     */
    public function sendBulk(array $recipients, string $subject, $message, array $options = [])
    {
        $results = [
            'success' => true,
            'total' => count($recipients),
            'sent' => 0,
            'failed' => 0,
            'details' => [],
        ];

        foreach ($recipients as $recipient) {
            $result = $this->sendSingle($recipient, $subject, $message, $options);

            if ($result['success']) {
                $results['sent']++;
            } else {
                $results['failed']++;
            }

            $results['details'][] = [
                'recipient' => $recipient,
                'status' => $result['success'] ? 'sent' : 'failed',
                'message' => $result['message'],
            ];
        }

        return $results;
    }

    /**
     * Send an email to a predefined group.
     *
     * @param  string|array  $message
     * @return array
     */
    public function sendToGroup(int $groupId, string $subject, $message, array $options = [])
    {
        try {
            $recipientList = RecipientList::with('items')
                ->where('id', $groupId)
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->where('type', 'email')
                        ->orWhere('type', 'both');
                })
                ->firstOrFail();

            $recipients = $recipientList->items()
                ->where('is_active', true)
                ->whereNotNull('email')
                ->pluck('email')
                ->toArray();

            // Add group ID to options for tracking
            $options['group_id'] = $groupId;

            return $this->sendBulk($recipients, $subject, $message, $options);
        } catch (\Exception $e) {
            Log::error('Group email sending failed', [
                'group_id' => $groupId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send group email: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Validate an email address format.
     */
    public function validateEmail(string $email): bool
    {
        $validator = Validator::make(['email' => $email], [
            'email' => 'required|email',
        ]);

        return ! $validator->fails();
    }

    /**
     * Log email details to the database.
     *
     * @param  string|array  $message
     */
    protected function logEmail(
        string $recipient,
        string $subject,
        $message,
        array $result,
        string $type = 'single',
        array $options = []
    ): void {
        try {
            // Convert message to string if it's an array (e.g., a view with data)
            $messageText = is_array($message)
                ? json_encode($message)
                : (string) $message;

            EmailLog::create([
                'user_id' => $options['user_id'] ?? null,
                'recipient' => $recipient,
                'subject' => $subject,
                'message' => $messageText,
                'cc' => $options['cc'] ?? null,
                'bcc' => $options['bcc'] ?? null,
                'template' => $options['template'] ?? null,
                'attachment' => $options['attachment'] ?? null,
                'provider' => $this->getProviderName(),
                'type' => $type,
                'group_id' => $options['group_id'] ?? null,
                'status' => $result['success'] ? 'sent' : 'failed',
                'response_data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log email', [
                'error' => $e->getMessage(),
                'recipient' => $recipient,
            ]);
        }
    }

    /**
     * Send an email using the specific provider implementation.
     *
     * @param  string|array  $message
     */
    abstract protected function send(string $recipient, string $subject, $message, array $options = []): array;

    /**
     * Get the name of the email provider.
     */
    abstract protected function getProviderName(): string;
}
