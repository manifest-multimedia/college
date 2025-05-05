<?php

namespace App\Services\Communication\SMS;

use App\Models\RecipientList;
use App\Models\SmsLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

abstract class AbstractSmsService implements SmsServiceInterface
{
    /**
     * Send an SMS message to a single recipient.
     *
     * @param string $recipient
     * @param string $message
     * @param array $options
     * @return array
     */
    public function sendSingle(string $recipient, string $message, array $options = [])
    {
        // Validate phone number
        if (!$this->validatePhoneNumber($recipient)) {
            Log::error('Invalid phone number', ['recipient' => $recipient]);
            return [
                'success' => false,
                'message' => 'Invalid phone number format',
            ];
        }

        try {
            // Send SMS using provider-specific implementation
            $result = $this->send($recipient, $message, $options);

            // Log SMS details
            $this->logSms($recipient, $message, $result, 'single', $options['user_id'] ?? null);

            return [
                'success' => true,
                'message' => 'SMS sent successfully',
                'data' => $result,
            ];
        } catch (\Exception $e) {
            Log::error('SMS sending failed', [
                'recipient' => $recipient,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send SMS: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Send the same SMS message to multiple recipients.
     *
     * @param array $recipients
     * @param string $message
     * @param array $options
     * @return array
     */
    public function sendBulk(array $recipients, string $message, array $options = [])
    {
        $results = [
            'success' => true,
            'total' => count($recipients),
            'sent' => 0,
            'failed' => 0,
            'details' => [],
        ];

        foreach ($recipients as $recipient) {
            $result = $this->sendSingle($recipient, $message, $options);
            
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
     * Send an SMS message to a predefined group.
     *
     * @param int $groupId
     * @param string $message
     * @param array $options
     * @return array
     */
    public function sendToGroup(int $groupId, string $message, array $options = [])
    {
        try {
            $recipientList = RecipientList::with('items')
                ->where('id', $groupId)
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->where('type', 'sms')
                        ->orWhere('type', 'both');
                })
                ->firstOrFail();

            $recipients = $recipientList->items()
                ->where('is_active', true)
                ->whereNotNull('phone')
                ->pluck('phone')
                ->toArray();

            // Add group ID to options for tracking
            $options['group_id'] = $groupId;
            
            return $this->sendBulk($recipients, $message, $options);
        } catch (\Exception $e) {
            Log::error('Group SMS sending failed', [
                'group_id' => $groupId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send group SMS: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Validate a phone number format.
     * 
     * @param string $phoneNumber
     * @return bool
     */
    public function validatePhoneNumber(string $phoneNumber): bool
    {
        $validator = Validator::make(['phone' => $phoneNumber], [
            'phone' => 'required|regex:/^\+?[0-9]{10,15}$/',
        ]);

        return !$validator->fails();
    }

    /**
     * Log SMS details to the database.
     *
     * @param string $recipient
     * @param string $message
     * @param array $result
     * @param string $type
     * @param int|null $userId
     * @param string|null $groupId
     * @return void
     */
    protected function logSms(
        string $recipient, 
        string $message, 
        array $result, 
        string $type = 'single', 
        ?int $userId = null, 
        ?string $groupId = null
    ): void {
        try {
            SmsLog::create([
                'user_id' => $userId,
                'recipient' => $recipient,
                'message' => $message,
                'provider' => $this->getProviderName(),
                'type' => $type,
                'group_id' => $groupId,
                'status' => $result['success'] ? 'sent' : 'failed',
                'response_data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log SMS', [
                'error' => $e->getMessage(),
                'recipient' => $recipient,
            ]);
        }
    }

    /**
     * Send an SMS message using the specific provider implementation.
     *
     * @param string $recipient
     * @param string $message
     * @param array $options
     * @return array
     */
    abstract protected function send(string $recipient, string $message, array $options = []): array;

    /**
     * Get the name of the SMS provider.
     *
     * @return string
     */
    abstract protected function getProviderName(): string;
}