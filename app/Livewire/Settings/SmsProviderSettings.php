<?php

namespace App\Livewire\Settings;

use App\Services\Communication\SMS\CallblySmsService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SmsProviderSettings extends Component
{
    public string $apiToken = '';
    public array $senderIds = [];
    public string $defaultSenderId = '';
    public bool $enabled = true;
    public bool $hasApiToken = false;
    public bool $isConfiguredInEnv = false;
    public bool $tokenUndecryptable = false;
    public ?array $balance = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['System', 'Super Admin']), 403);

        $this->senderIds = $this->configuredSenderIds();
        $this->defaultSenderId = $this->setting('sms.callbly.sender_name')
            ?? config('services.callbly.sender_name')
            ?? config('communication.callbly.sender_name')
            ?? $this->senderIds[0];
        $this->enabled = $this->setting('sms.callbly.enabled', config('services.callbly.enabled', true) ? 'true' : 'false') === 'true';

        $envToken = config('services.callbly.api_token') ?? config('communication.callbly.api_token');
        $this->isConfiguredInEnv = filled($envToken);

        $dbToken = $this->setting('sms.callbly.api_token');
        if (filled($dbToken)) {
            try {
                $decrypted = Crypt::decryptString($dbToken);
                $this->hasApiToken = filled(trim($decrypted));
            } catch (\Throwable) {
                $this->tokenUndecryptable = true;
                // If env token is present, we still consider an API token available
                $this->hasApiToken = $this->isConfiguredInEnv;
            }
        } else {
            $this->hasApiToken = $this->isConfiguredInEnv;
        }
    }

    public function save(): void
    {
        $this->validate([
            'apiToken' => 'nullable|string|min:16',
            'senderIds' => 'required|array|min:1',
            'senderIds.*' => 'required|string|max:11|distinct',
            'defaultSenderId' => 'required|string|max:11',
        ]);

        $senderIds = collect($this->senderIds)
            ->map(fn ($senderId) => trim($senderId))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $this->defaultSenderId = trim($this->defaultSenderId);

        if (! in_array($this->defaultSenderId, $senderIds, true)) {
            $this->addError('defaultSenderId', 'Select one of the approved sender IDs as the default.');

            return;
        }

        if (! $this->hasApiToken && blank($this->apiToken)) {
            $this->addError('apiToken', 'An API token is required the first time Callbly is configured (or set CALLBLY_API_TOKEN in .env).');
            return;
        }

        $cleanToken = null;
        if (filled($this->apiToken)) {
            $cleanToken = trim($this->apiToken);
            $cleanToken = preg_replace('/^(["\'])(.*)\1$/s', '$2', $cleanToken);
            $cleanToken = preg_replace('/^Bearer\s+/i', '', trim($cleanToken));
        }

        DB::transaction(function () use ($senderIds, $cleanToken): void {
            if (filled($cleanToken)) {
                $this->set('sms.callbly.api_token', Crypt::encryptString($cleanToken), 'Callbly API bearer token', 'secret');
            }
            // sender_name remains the provider-compatible default while the
            // JSON setting preserves the approved choices for message senders.
            $this->set('sms.callbly.sender_name', $this->defaultSenderId, 'Default approved Callbly sender ID', 'string');
            $this->set('sms.callbly.sender_ids', json_encode($senderIds, JSON_THROW_ON_ERROR), 'Approved Callbly sender IDs', 'json');
            $this->set('sms.callbly.enabled', $this->enabled ? 'true' : 'false', 'Enable Callbly SMS sending', 'boolean');
        });

        if (filled($cleanToken)) {
            $this->tokenUndecryptable = false;
        }
        $this->hasApiToken = true;
        $this->apiToken = '';
        session()->flash('success', 'Callbly SMS settings saved.');
    }

    public function refreshBalances(CallblySmsService $callbly): void
    {
        $this->balance = $callbly->getBalances();

        if (! $this->balance['success']) {
            session()->flash('error', $this->balance['message'] ?? 'Unable to retrieve Callbly balances.');
        }
    }

    public function addSenderId(): void
    {
        $this->senderIds[] = '';
    }

    public function removeSenderId(int $index): void
    {
        if (count($this->senderIds) === 1) {
            return;
        }

        $removedSenderId = $this->senderIds[$index] ?? null;
        unset($this->senderIds[$index]);
        $this->senderIds = array_values($this->senderIds);

        if ($this->defaultSenderId === $removedSenderId) {
            $this->defaultSenderId = $this->senderIds[0] ?? '';
        }
    }

    private function setting(string $key, mixed $default = null): mixed
    {
        return DB::table('system_settings')
            ->where('key', $key)
            ->where('is_active', true)
            ->value('value') ?? $default;
    }

    private function set(string $key, string $value, string $description, string $type): void
    {
        DB::table('system_settings')->updateOrInsert(['key' => $key], [
            'value' => $value,
            'type' => $type,
            'description' => $description,
            'group' => 'sms_callbly',
            'is_active' => true,
            'updated_at' => now(),
            'created_at' => now(),
        ]);
    }

    private function configuredSenderIds(): array
    {
        $senderIds = json_decode((string) $this->setting('sms.callbly.sender_ids', '[]'), true);
        $senderIds = is_array($senderIds) ? $senderIds : [];

        $envSenderIds = config('services.callbly.sender_ids') ?? config('communication.callbly.sender_ids');
        if (is_string($envSenderIds)) {
            $parsedEnvIds = json_decode($envSenderIds, true);
            if (is_array($parsedEnvIds)) {
                $senderIds = array_merge($senderIds, $parsedEnvIds);
            } else {
                $senderIds = array_merge($senderIds, array_map('trim', explode(',', $envSenderIds)));
            }
        } elseif (is_array($envSenderIds)) {
            $senderIds = array_merge($senderIds, $envSenderIds);
        }

        $legacySenderId = $this->setting('sms.callbly.sender_name')
            ?? config('services.callbly.sender_name')
            ?? config('communication.callbly.sender_name')
            ?? config('branding.institution.acronym', 'College360');

        return collect([...$senderIds, $legacySenderId])
            ->filter(fn ($senderId) => is_string($senderId) && filled($senderId))
            ->map(fn ($senderId) => trim($senderId))
            ->unique()
            ->values()
            ->all();
    }

    public function render()
    {
        return view('livewire.settings.sms-provider-settings')->layout('components.dashboard.default', ['title' => 'SMS Provider']);
    }
}
