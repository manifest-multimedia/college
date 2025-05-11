<?php

namespace App\Services\Communication\SMS;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NaloSmsService extends AbstractSmsService
{
    /**
     * Nalo API credentials and settings
     */
    protected $apiKey;
    protected $senderId;
    protected $apiUrl;
    protected $isConfigured = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->apiKey = Config::get('services.nalo.api_key');
        $this->senderId = Config::get('services.nalo.sender_id');
        
        // Check if credentials are properly set
        if (!empty($this->apiKey)) {
            $this->isConfigured = true;
            $this->apiUrl = "https://api.nalo.io/v1/sms/send";
        } else {
            Log::error('Nalo SMS service not properly configured. Missing API key.');
        }
    }

    /**
     * Send an SMS message using Nalo API.
     *
     * @param string $recipient
     * @param string $message
     * @param array $options
     * @return array
     */
    protected function send(string $recipient, string $message, array $options = []): array
    {
        // Check if service is properly configured
        if (!$this->isConfigured) {
            Log::error('Nalo SMS service not configured properly. Message not sent.', [
                'recipient' => $recipient
            ]);
            
            return [
                'success' => false,
                'error_message' => 'SMS service not properly configured',
            ];
        }
        
        try {
            // Format phone number - remove + if present
            $recipient = ltrim($recipient, '+');
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post($this->apiUrl, [
                'sender' => $options['sender'] ?? $this->senderId,
                'recipient' => $recipient,
                'message' => $message,
                'callbackUrl' => $options['callback_url'] ?? null,
                'type' => $options['type'] ?? 'text'
            ]);
            
            $responseData = $response->json();
            
            if ($response->successful() && isset($responseData['status']) && $responseData['status'] === 'success') {
                return [
                    'success' => true,
                    'message_id' => $responseData['data']['messageId'] ?? null,
                    'response' => $responseData
                ];
            }
            
            Log::error('Nalo SMS sending failed', [
                'recipient' => $recipient,
                'status' => $responseData['status'] ?? 'unknown',
                'error' => $responseData['message'] ?? 'Unknown error',
                'response' => $responseData
            ]);
            
            return [
                'success' => false,
                'error_message' => $responseData['message'] ?? 'Failed to send SMS',
                'response' => $responseData
            ];
            
        } catch (\Exception $e) {
            Log::error('Nalo SMS exception', [
                'recipient' => $recipient,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error_message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get the name of the SMS provider.
     *
     * @return string
     */
    protected function getProviderName(): string
    {
        return 'nalo';
    }
}
