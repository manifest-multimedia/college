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
    public ?array $balance = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['System', 'Super Admin']), 403);

        $this->senderIds = $this->configuredSenderIds();
        $this->defaultSenderId = $this->setting('sms.callbly.sender_name') ?? $this->senderIds[0];
        $this->enabled = $this->setting('sms.callbly.enabled', 'true') === 'true';
        $this->hasApiToken = filled($this->setting('sms.callbly.api_token'));
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
            $this->addError('apiToken', 'An API token is required the first time Callbly is configured.');
            return;
        }

        DB::transaction(function (): void {
            if (filled($this->apiToken)) {
                $this->set('sms.callbly.api_token', Crypt::encryptString($this->apiToken), 'Callbly API bearer token', 'secret');
            }
            // sender_name remains the provider-compatible default while the
            // JSON setting preserves the approved choices for message senders.
            $this->set('sms.callbly.sender_name', $this->defaultSenderId, 'Default approved Callbly sender ID', 'string');
            $this->set('sms.callbly.sender_ids', json_encode($senderIds, JSON_THROW_ON_ERROR), 'Approved Callbly sender IDs', 'json');
            $this->set('sms.callbly.enabled', $this->enabled ? 'true' : 'false', 'Enable Callbly SMS sending', 'boolean');
        });

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
        return DB::table('system_settings')->where('key', $key)->value('value') ?? $default;
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

        $legacySenderId = $this->setting('sms.callbly.sender_name') ?? config('branding.institution.acronym', 'College360');

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
