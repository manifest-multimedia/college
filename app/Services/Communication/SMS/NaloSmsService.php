<?php

namespace App\Services\Communication\SMS;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class NaloSmsService extends AbstractSmsService
{
    /**
     * Nalo API credentials and settings
     */
    protected $apiKey;
    protected $username;
    protected $password;
    protected $senderId;
    protected $apiUrl;
    protected $baseUrl;
    protected $resellerPrefix;
    protected $isConfigured = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->apiKey = Config::get('services.nalo.api_key');
        $this->username = Config::get('services.nalo.username');
        $this->password = Config::get('services.nalo.password');
        $this->senderId = Config::get('services.nalo.sender_id', 'PNMTC');
        $this->resellerPrefix = Config::get('services.nalo.reseller_prefix', 'Resl_Nalo');
        
        // Check if credentials are properly set
        if (!empty($this->apiKey) || (!empty($this->username) && !empty($this->password))) {
            $this->isConfigured = true;
            // Set base URL from documentation
            $this->baseUrl = "https://sms.nalosolutions.com/smsbackend";
        } else {
            Log::error('Manifest Digital SMS service not properly configured. Missing API key or username/password.');
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
            Log::error('Manifest Digital SMS service not configured properly. Message not sent.', [
                'recipient' => $recipient
            ]);
            
            return [
                'success' => false,
                'error_message' => 'SMS service not properly configured',
            ];
        }
        
        try {
            // Format phone number - ensure it starts with country code (e.g., 233 for Ghana)
            // Remove any + prefix
            $recipient = ltrim($recipient, '+');
            
            // Determine method to use based on options
            $method = $options['method'] ?? 'json';
            
            if ($method === 'get') {
                return $this->sendWithGetMethod($recipient, $message, $options);
            } else {
                return $this->sendWithPostMethod($recipient, $message, $options);
            }
            
        } catch (GuzzleException $e) {
            Log::error('Manifest Digital SMS Guzzle exception', [
                'recipient' => $recipient,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error_message' => 'GuzzleException: ' . $e->getMessage()
            ];
        } catch (\Exception $e) {
            Log::error('Manifest Digital SMS general exception', [
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
     * Send SMS using GET method
     * 
     * @param string $recipient
     * @param string $message
     * @param array $options
     * @return array
     */
    protected function sendWithGetMethod(string $recipient, string $message, array $options = []): array
    {
        $client = new Client([
            'verify' => false, // Disable SSL verification
            'timeout' => 15,
            'connect_timeout' => 15
        ]);
        
        // Build URL for GET request
        $endpoint = "{$this->baseUrl}/clientapi/{$this->resellerPrefix}/send-message/";
        
        // Prepare query parameters - prefer API key if available, otherwise use username/password
        $queryParams = [];
        
        if (!empty($this->apiKey)) {
            $queryParams['key'] = $this->apiKey;
        } else {
            $queryParams['username'] = $this->username;
            $queryParams['password'] = $this->password;
        }
        
        // Add required parameters as per documentation
        $queryParams['type'] = $options['type'] ?? '0';
        $queryParams['destination'] = $recipient;
        $queryParams['dir'] = $options['dir'] ?? '1'; // Default to 1 for outbound messages
        $queryParams['source'] = $options['sender'] ?? $this->senderId;
        $queryParams['message'] = urlencode($message);
        
        $response = $client->get($endpoint, [
            'query' => $queryParams
        ]);
        
        $responseBody = $response->getBody()->getContents();
        
        // Parse response according to the documentation
        // Expected format: 1701|233XXXXXXXXX|ATXid_123
        if (strpos($responseBody, '1701|') === 0) {
            $parts = explode('|', $responseBody);
            return [
                'success' => true,
                'message_id' => $parts[2] ?? null,
                'response' => $responseBody
            ];
        }
        
        // Handle error codes
        $errorCode = intval(trim($responseBody));
        $errorMessage = $this->getErrorMessage($errorCode);
        
        Log::error('Manifest Digital SMS sending failed', [
            'recipient' => $recipient,
            'error_code' => $errorCode,
            'error' => $errorMessage,
            'response' => $responseBody
        ]);
        
        return [
            'success' => false,
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
            'response' => $responseBody
        ];
    }

    /**
     * Send SMS using POST method with JSON
     * 
     * @param string $recipient
     * @param string $message
     * @param array $options
     * @return array
     */
    protected function sendWithPostMethod(string $recipient, string $message, array $options = []): array
    {
        $client = new Client([
            'verify' => false, // Disable SSL verification
            'timeout' => 15,
            'connect_timeout' => 15
        ]);
        
        // Build endpoint for POST request
        $endpoint = "{$this->baseUrl}/{$this->resellerPrefix}/send-message/";
        
        // Prepare payload according to the documentation
        $payload = [];
        
        // Use API key or username/password
        if (!empty($this->apiKey)) {
            $payload['key'] = $this->apiKey;
        } else {
            $payload['username'] = $this->username;
            $payload['password'] = $this->password;
        }
        
        // Multiple recipients separated by comma
        $recipients = is_array($recipient) ? implode(',', $recipient) : $recipient;
        $payload['msisdn'] = $recipients;
        $payload['message'] = $message;
        $payload['sender_id'] = $options['sender'] ?? $this->senderId;
        
        $response = $client->post($endpoint, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'json' => $payload
        ]);
        
        $responseBody = $response->getBody()->getContents();
        $responseData = json_decode($responseBody, true);
        
        // Check if the response contains a success code
        if (is_array($responseData) && isset($responseData['code']) && $responseData['code'] == 1701) {
            return [
                'success' => true,
                'message_id' => $responseData['message_id'] ?? null,
                'response' => $responseData
            ];
        }
        
        // Handle error
        $errorCode = $responseData['code'] ?? 1710; // Default to internal error
        $errorMessage = $this->getErrorMessage($errorCode);
        
        Log::error('Manifest Digital SMS sending failed', [
            'recipient' => $recipient,
            'error_code' => $errorCode,
            'error' => $errorMessage,
            'response' => $responseData
        ]);
        
        return [
            'success' => false,
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
            'response' => $responseData
        ];
    }

    /**
     * Translate error codes to meaningful messages
     * 
     * @param int $code
     * @return string
     */
    protected function getErrorMessage($code): string
    {
        return match ($code) {
            1702 => 'Invalid URL Error (missing or blank parameter)',
            1703 => 'Invalid value in username or password field',
            1704 => 'Invalid value in type field',
            1705 => 'Invalid message content',
            1706 => 'Invalid destination (recipient phone number)',
            1707 => 'Invalid source (sender ID)',
            1708 => 'Invalid value for dlr field',
            1709 => 'User validation failed',
            1025 => 'Insufficient credit (user)',
            1026 => 'Insufficient credit (reseller)',
            default => 'Internal error',
        };
    }

    /**
     * Get the provider name.
     *
     * @return string
     */
    protected function getProviderName(): string
    {
        return 'Manifest Digital';
    }
}
