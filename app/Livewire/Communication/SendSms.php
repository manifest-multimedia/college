<?php

namespace App\Livewire\Communication;

use App\Models\RecipientList;
use App\Services\Communication\SMS\SmsServiceInterface;
use App\Services\Communication\SMS\CallblySmsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class SendSms extends Component
{
    public string $recipient = '';

    public string $message = '';

    public string $sendType = 'single';

    public array $recipients = [];

    public ?int $recipientListId = null;

    public array $recipientLists = [];

    public ?array $balance = null;

    public array $senderIds = [];

    public string $selectedSenderId = '';

    // Initialize with null to prevent "must not be accessed before initialization" error
    protected ?SmsServiceInterface $smsService = null;

    public function boot(SmsServiceInterface $smsService)
    {
        $this->smsService = $smsService;
    }

    public function mount()
    {
        $this->loadRecipientLists();
        $this->loadSenderIds();
    }

    protected function loadSenderIds(): void
    {
        $senderIds = json_decode((string) DB::table('system_settings')->where('key', 'sms.callbly.sender_ids')->value('value'), true);
        $senderIds = is_array($senderIds) ? $senderIds : [];
        $defaultSenderId = DB::table('system_settings')->where('key', 'sms.callbly.sender_name')->value('value');

        $this->senderIds = collect([...$senderIds, $defaultSenderId])
            ->filter(fn ($senderId) => is_string($senderId) && filled($senderId))
            ->map(fn ($senderId) => trim($senderId))
            ->unique()
            ->values()
            ->all();
        $this->selectedSenderId = $defaultSenderId && in_array($defaultSenderId, $this->senderIds, true)
            ? $defaultSenderId
            : ($this->senderIds[0] ?? '');
    }

    protected function loadRecipientLists()
    {
        try {
            $this->recipientLists = RecipientList::where('is_active', true)
                ->where(function ($query) {
                    $query->where('type', 'sms')
                        ->orWhere('type', 'both');
                })
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Failed to load recipient lists', [
                'error' => $e->getMessage(),
            ]);
            $this->recipientLists = [];
        }
    }

    public function addRecipient()
    {
        if (! empty($this->recipient)) {
            // Validate phone number
            if ($this->smsService->validatePhoneNumber($this->recipient)) {
                $this->recipients[] = $this->recipient;
                $this->recipient = '';
            } else {
                session()->flash('error', 'Invalid phone number format.');
            }
        }
    }

    public function removeRecipient($index)
    {
        if (isset($this->recipients[$index])) {
            unset($this->recipients[$index]);
            $this->recipients = array_values($this->recipients);
        }
    }

    public function sendSms()
    {
        $this->validate([
            'message' => 'required|min:3|max:160',
            'selectedSenderId' => ['required', Rule::in($this->senderIds)],
        ]);

        try {
            $result = match ($this->sendType) {
                'single' => $this->sendSingleSms(),
                'bulk' => $this->sendBulkSms(),
                'group' => $this->sendGroupSms(),
                default => ['success' => false, 'message' => 'Invalid send type.'],
            };

            if ($result['success']) {
                $this->reset(['recipient', 'recipients', 'message', 'recipientListId']);
                session()->flash('success', $result['message'] ?? 'SMS sent successfully.');
            } else {
                session()->flash('error', $result['message'] ?? 'Failed to send SMS.');
            }
        } catch (\Exception $e) {
            Log::error('SMS sending error', [
                'error' => $e->getMessage(),
                'type' => $this->sendType,
            ]);
            session()->flash('error', 'An error occurred while sending SMS: '.$e->getMessage());
        }
    }

    protected function sendSingleSms()
    {
        $this->validate([
            'recipient' => 'required',
        ]);

        return $this->smsService->sendSingle(
            $this->recipient,
            $this->message,
            $this->sendOptions()
        );
    }

    protected function sendBulkSms()
    {
        if (empty($this->recipients)) {
            return ['success' => false, 'message' => 'No recipients added.'];
        }

        return $this->smsService->sendBulk(
            $this->recipients,
            $this->message,
            $this->sendOptions()
        );
    }

    protected function sendGroupSms()
    {
        $this->validate([
            'recipientListId' => 'required|exists:recipient_lists,id',
        ]);

        return $this->smsService->sendToGroup(
            $this->recipientListId,
            $this->message,
            $this->sendOptions()
        );
    }

    public function refreshBalances(CallblySmsService $callbly): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['System', 'Super Admin']), 403);

        $this->balance = $callbly->getBalances();

        if (! $this->balance['success']) {
            session()->flash('error', $this->balance['message'] ?? 'Unable to retrieve Callbly balances.');
        }
    }

    private function sendOptions(): array
    {
        return ['user_id' => auth()->id(), 'sender_name' => $this->selectedSenderId];
    }

    public function render()
    {
        return view('livewire.communication.send-sms')
            ->layout('components.dashboard.default');
    }
}
