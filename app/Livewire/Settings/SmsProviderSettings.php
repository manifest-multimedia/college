<?php

namespace App\Livewire\Settings;

use App\Services\Communication\SMS\CallblySmsService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SmsProviderSettings extends Component
{
    public string $apiToken = '';
    public string $senderName = '';
    public bool $enabled = true;
    public bool $hasApiToken = false;
    public ?array $balance = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['System', 'Super Admin']), 403);

        $this->senderName = $this->setting('sms.callbly.sender_name') ?? config('branding.institution.acronym', 'College360');
        $this->enabled = $this->setting('sms.callbly.enabled', 'true') === 'true';
        $this->hasApiToken = filled($this->setting('sms.callbly.api_token'));
    }

    public function save(): void
    {
        $this->validate([
            'apiToken' => 'nullable|string|min:16',
            'senderName' => 'required|string|max:11',
        ]);

        if (! $this->hasApiToken && blank($this->apiToken)) {
            $this->addError('apiToken', 'An API token is required the first time Callbly is configured.');
            return;
        }

        DB::transaction(function (): void {
            if (filled($this->apiToken)) {
                $this->set('sms.callbly.api_token', Crypt::encryptString($this->apiToken), 'Callbly API bearer token', 'secret');
            }
            $this->set('sms.callbly.sender_name', $this->senderName, 'Approved Callbly sender ID', 'string');
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

    public function render()
    {
        return view('livewire.settings.sms-provider-settings')->layout('components.dashboard.default', ['title' => 'SMS Provider']);
    }
}
