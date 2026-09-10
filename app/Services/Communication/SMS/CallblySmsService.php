<?php

namespace App\Services\Communication\SMS;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CallblySmsService extends AbstractSmsService
{
    private const BASE_URL = 'https://callbly.com/api/v1';

    private ?string $tokenFailure = null;

    public function sendBulk(array $recipients, string $message, array $options = []): array
    {
        $recipients = array_values(array_unique(array_filter(array_map(
            fn ($recipient) => $this->normalizePhoneNumber((string) $recipient),
            $recipients
        ), fn ($recipient) => $recipient !== null && $this->validatePhoneNumber($recipient))));

        if ($recipients === []) {
            return ['success' => false, 'message' => 'No valid recipient phone numbers were supplied.'];
        }

        $senderName = $this->senderName($options);
        $result = $this->request('post', '/sms/send-bulk', [
            'recipients' => $recipients,
            'message' => $message,
            'sender_name' => $senderName,
        ]);
        $result['sender_name'] = $senderName;
        $result['audience_type'] = $options['audience_type'] ?? null;
        $result['audience_label'] = $options['audience_label'] ?? null;

        foreach ($recipients as $recipient) {
            $this->logSms($recipient, $message, $result, 'bulk', $options['user_id'] ?? null, $options['group_id'] ?? null);
        }

        return [
            'success' => $result['success'],
            'message' => $result['success'] ? 'SMS batch submitted successfully.' : ($result['error_message'] ?? 'Failed to send the SMS batch.'),
            'total' => count($recipients),
            'sent' => $result['success'] ? count($recipients) : 0,
            'failed' => $result['success'] ? 0 : count($recipients),
            'data' => $result,
        ];
    }

    public function getBalances(): array
    {
        $sms = $this->request('get', '/sms/balance', [], true);
        $wallet = $this->request('get', '/wallet/balance', [], true);

        return [
            'success' => $sms['success'] && $wallet['success'],
            'sms_balance' => $sms['data']['balance'] ?? null,
            'wallet_balance' => $wallet['data']['balance'] ?? null,
            'currency' => $wallet['data']['currency'] ?? 'GHS',
            'formatted_wallet_balance' => $wallet['data']['formatted_balance'] ?? null,
            'sms_response' => $sms,
            'wallet_response' => $wallet,
            'message' => $sms['error_message'] ?? $wallet['error_message'] ?? null,
        ];
    }

    protected function send(string $recipient, string $message, array $options = []): array
    {
        $senderName = $this->senderName($options);
        $result = $this->request('post', '/sms/send', [
            'recipient' => $recipient,
            'message' => $message,
            'sender_name' => $senderName,
        ]);

        $result['sender_name'] = $senderName;
        $result['audience_type'] = $options['audience_type'] ?? null;
        $result['audience_label'] = $options['audience_label'] ?? null;

        return $result;
    }

    protected function getProviderName(): string
    {
        return 'callbly';
    }

    private function request(string $method, string $path, array $payload = [], bool $allowWhenDisabled = false): array
    {
        if (! $allowWhenDisabled && $this->setting('sms.callbly.enabled', 'true') !== 'true') {
            return ['success' => false, 'error_message' => 'Callbly SMS sending is disabled in System Settings.'];
        }

        $token = $this->apiToken();

        if (blank($token)) {
            return ['success' => false, 'error_message' => $this->tokenFailure ?? 'Callbly is not configured. Add the API token in System Settings or configure CALLBLY_API_TOKEN.'];
        }

        try {
            $request = Http::acceptJson()->withToken($token)->timeout(15);
            $response = $method === 'get'
                ? $request->get(self::BASE_URL.$path)
                : $request->post(self::BASE_URL.$path, $payload);
            $data = $response->json() ?? [];

            if ($response->successful() && ($data['success'] ?? true)) {
                return ['success' => true, 'data' => $data['data'] ?? $data, 'response' => $data];
            }

            $maskedToken = $this->maskToken($token);
            Log::warning('Callbly SMS API rejected request', [
                'method' => strtoupper($method),
                'path' => $path,
                'status' => $response->status(),
                'response' => $data,
                'masked_token' => $maskedToken,
            ]);

            return [
                'success' => false,
                'error_message' => $response->status() === 401
                    ? 'Callbly rejected the stored API token. It may have expired, been regenerated, belong to another Callbly account, or have been saved incorrectly. Re-enter a current token in System Settings or update CALLBLY_API_TOKEN.'
                    : data_get($data, 'message', 'Callbly returned HTTP '.$response->status()),
                'response' => $data,
                'status_code' => $response->status(),
            ];
        } catch (\Throwable $exception) {
            Log::error('Callbly SMS connection exception', [
                'method' => strtoupper($method),
                'path' => $path,
                'error' => $exception->getMessage(),
            ]);

            return ['success' => false, 'error_message' => 'Unable to connect to Callbly: '.$exception->getMessage()];
        }
    }

    private function senderName(array $options): string
    {
        return $options['sender_name']
            ?? $this->setting('sms.callbly.sender_name')
            ?? config('services.callbly.sender_name')
            ?? config('communication.callbly.sender_name')
            ?? config('branding.institution.acronym', 'College360');
    }

    private function apiToken(): ?string
    {
        $this->tokenFailure = null;
        $dbToken = $this->rawSetting('sms.callbly.api_token');
        $resolvedToken = null;

        if (filled($dbToken)) {
            try {
                $decrypted = Crypt::decryptString($dbToken);
                $resolvedToken = $this->sanitizeToken($decrypted);
            } catch (\Throwable $exception) {
                if ($this->isEncryptedPayload($dbToken)) {
                    Log::warning('Callbly API token in database could not be decrypted with the current application key.', [
                        'exception' => $exception->getMessage(),
                    ]);
                    $this->tokenFailure = 'The saved Callbly API token cannot be decrypted with the current application key (it may have been encrypted under a previous APP_KEY). Re-enter the current token in System Settings or set CALLBLY_API_TOKEN in .env.';
                } else {
                    // Pre-existing plaintext token support during upgrades
                    $resolvedToken = $this->sanitizeToken($dbToken);
                }
            }
        }

        // Fallback to environment/config if DB token is absent or could not be decrypted
        if (blank($resolvedToken)) {
            $envToken = config('services.callbly.api_token') ?? config('communication.callbly.api_token');
            if (filled($envToken)) {
                $resolvedToken = $this->sanitizeToken((string) $envToken);
                if (filled($resolvedToken)) {
                    // Clear failure since env fallback succeeded
                    $this->tokenFailure = null;
                }
            }
        }

        return $resolvedToken;
    }

    private function sanitizeToken(?string $token): ?string
    {
        if (blank($token)) {
            return null;
        }

        $token = trim($token);
        // Strip surrounding quotes if pasted with them
        $token = preg_replace('/^(["\'])(.*)\1$/s', '$2', $token);
        // Laravel's withToken() adds the Bearer scheme itself. This also
        // accepts a value pasted from documentation as "Bearer <token>".
        $token = preg_replace('/^Bearer\s+/i', '', trim($token));

        return trim($token) ?: null;
    }

    private function maskToken(?string $token): string
    {
        if (blank($token)) {
            return 'empty';
        }

        $len = strlen($token);
        if ($len <= 8) {
            return str_repeat('*', $len);
        }

        return substr($token, 0, 4) . str_repeat('*', max(0, $len - 8)) . substr($token, -4);
    }

    private function isEncryptedPayload(string $value): bool
    {
        $decoded = base64_decode(trim($value), true);
        $payload = $decoded === false ? null : json_decode($decoded, true);

        return is_array($payload)
            && isset($payload['iv'], $payload['value'], $payload['mac']);
    }

    private function rawSetting(string $key): ?string
    {
        if (! Schema::hasTable('system_settings')) {
            return null;
        }

        return DB::table('system_settings')
            ->where('key', $key)
            ->where('is_active', true)
            ->value('value');
    }

    private function setting(string $key, ?string $default = null): ?string
    {
        $value = $this->rawSetting($key);

        if ($value !== null) {
            return $value;
        }

        // Fallback to configuration for known keys
        if ($key === 'sms.callbly.enabled') {
            $configEnabled = config('services.callbly.enabled') ?? config('communication.callbly.enabled');
            if ($configEnabled !== null) {
                return $configEnabled ? 'true' : 'false';
            }
        }

        if ($key === 'sms.callbly.sender_name') {
            $configSender = config('services.callbly.sender_name') ?? config('communication.callbly.sender_name');
            if (filled($configSender)) {
                return (string) $configSender;
            }
        }

        return $default;
    }
}
