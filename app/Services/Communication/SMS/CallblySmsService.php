<?php

namespace App\Services\Communication\SMS;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
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
            return ['success' => false, 'error_message' => $this->tokenFailure ?? 'Callbly is not configured. Add the API token in System Settings.'];
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

            return [
                'success' => false,
                'error_message' => $response->status() === 401
                    ? 'Callbly rejected the stored API token. It may have expired, been regenerated, belong to another Callbly account, or have been saved incorrectly. Re-enter a current token in System Settings and save it again.'
                    : data_get($data, 'message', 'Callbly returned HTTP '.$response->status()),
                'response' => $data,
                'status_code' => $response->status(),
            ];
        } catch (\Throwable $exception) {
            return ['success' => false, 'error_message' => 'Unable to connect to Callbly: '.$exception->getMessage()];
        }
    }

    private function senderName(array $options): string
    {
        return $options['sender_name']
            ?? $this->setting('sms.callbly.sender_name')
            ?? config('branding.institution.acronym', 'College360');
    }

    private function apiToken(): ?string
    {
        $token = $this->setting('sms.callbly.api_token');

        if (blank($token)) {
            return null;
        }

        try {
            $token = Crypt::decryptString($token);
        } catch (\Throwable) {
            if ($this->isEncryptedPayload($token)) {
                $this->tokenFailure = 'The saved Callbly API token cannot be decrypted by this application. Re-enter the current token in System Settings and save it again.';

                return null;
            }

            // Supports a pre-existing plaintext token during a controlled
            // upgrade; saving the settings encrypts it thereafter.
        }

        // Laravel's withToken() adds the Bearer scheme itself. This also
        // accepts a value pasted from documentation as "Bearer <token>".
        return preg_replace('/^Bearer\s+/i', '', trim($token)) ?: null;
    }

    private function isEncryptedPayload(string $value): bool
    {
        $decoded = base64_decode($value, true);
        $payload = $decoded === false ? null : json_decode($decoded, true);

        return is_array($payload)
            && isset($payload['iv'], $payload['value'], $payload['mac']);
    }

    private function setting(string $key, ?string $default = null): ?string
    {
        if (! Schema::hasTable('system_settings')) {
            return $default;
        }

        return DB::table('system_settings')->where('key', $key)->where('is_active', true)->value('value') ?? $default;
    }
}
