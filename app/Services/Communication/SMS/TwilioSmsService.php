<?php

namespace App\Services\Communication\SMS;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TwilioSmsService extends AbstractSmsService
{
    /**
     * Twilio API credentials
     */
    protected $accountSid;

    protected $authToken;

    protected $fromNumber;

    protected $apiUrl;

    protected $isConfigured = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->accountSid = Config::get('services.twilio.sid');
        $this->authToken = Config::get('services.twilio.token');
        $this->fromNumber = Config::get('services.twilio.from');

        // Check if credentials are properly set
        if (! empty($this->accountSid) && ! empty($this->authToken)) {
            $this->isConfigured = true;
            $this->apiUrl = "https://api.twilio.com/2010-04-01/Accounts/{$this->accountSid}/Messages.json";
        } else {
            Log::error('Twilio SMS service not properly configured. Missing credentials.');
        }
    }

    /**
     * Send an SMS message using Twilio API.
     */
    protected function send(string $recipient, string $message, array $options = []): array
    {
        // Check if service is properly configured
        if (! $this->isConfigured) {
            Log::error('Twilio SMS service not configured properly. Message not sent.', [
                'recipient' => $recipient,
            ]);

            return [
                'success' => false,
                'error_message' => 'SMS service not properly configured',
            ];
        }

        try {
            $response = Http::asForm()
                ->withBasicAuth($this->accountSid, $this->authToken)
                ->post($this->apiUrl, [
                    'From' => $options['from'] ?? $this->fromNumber,
                    'To' => $recipient,
                    'Body' => $message,
                ]);

            $responseData = $response->json();

            if ($response->successful()) {
                return [
                    'success' => true,
                    'provider_message_id' => $responseData['sid'] ?? null,
                    'provider_response' => $responseData,
                ];
            }

            return [
                'success' => false,
                'error_code' => $responseData['code'] ?? $response->status(),
                'error_message' => $responseData['message'] ?? 'Unknown error',
                'provider_response' => $responseData,
            ];
        } catch (\Exception $e) {
            Log::error('Twilio SMS sending error', [
                'recipient' => $recipient,
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
     */
    protected function getProviderName(): string
    {
        return 'twilio';
    }
}
