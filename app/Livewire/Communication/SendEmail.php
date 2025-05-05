<?php

namespace App\Livewire\Communication;

use App\Models\RecipientList;
use App\Services\Communication\Email\EmailServiceInterface;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;

class SendEmail extends Component
{
    use WithFileUploads;
    
    public string $recipient = '';
    public string $subject = '';
    public string $message = '';
    public string $sendType = 'single';
    public array $recipients = [];
    public ?int $recipientListId = null;
    public $attachment = null;
    public ?string $cc = null;
    public ?string $bcc = null;
    public ?string $template = null;
    
    public array $recipientLists = [];
    public array $templates = ['emails.generic' => 'Default Template'];
    
    protected EmailServiceInterface $emailService;
    
    public function mount(EmailServiceInterface $emailService)
    {
        $this->emailService = $emailService;
        $this->loadRecipientLists();
    }
    
    protected function loadRecipientLists()
    {
        try {
            $this->recipientLists = RecipientList::where('is_active', true)
                ->where(function ($query) {
                    $query->where('type', 'email')
                        ->orWhere('type', 'both');
                })
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Failed to load recipient lists', [
                'error' => $e->getMessage()
            ]);
            $this->recipientLists = [];
        }
    }
    
    public function addRecipient()
    {
        if (!empty($this->recipient)) {
            // Validate email
            if ($this->emailService->validateEmail($this->recipient)) {
                $this->recipients[] = $this->recipient;
                $this->recipient = '';
            } else {
                session()->flash('error', 'Invalid email address format.');
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
    
    public function sendEmail()
    {
        $this->validate([
            'subject' => 'required|min:3|max:255',
            'message' => 'required|min:10',
        ]);
        
        try {
            $result = match ($this->sendType) {
                'single' => $this->sendSingleEmail(),
                'bulk' => $this->sendBulkEmail(),
                'group' => $this->sendGroupEmail(),
                default => ['success' => false, 'message' => 'Invalid send type.'],
            };
            
            if ($result['success']) {
                $this->reset(['recipient', 'recipients', 'subject', 'message', 'recipientListId', 'attachment', 'cc', 'bcc']);
                session()->flash('success', $result['message'] ?? 'Email sent successfully.');
            } else {
                session()->flash('error', $result['message'] ?? 'Failed to send email.');
            }
        } catch (\Exception $e) {
            Log::error('Email sending error', [
                'error' => $e->getMessage(),
                'type' => $this->sendType
            ]);
            session()->flash('error', 'An error occurred while sending email: ' . $e->getMessage());
        }
    }
    
    protected function prepareEmailOptions()
    {
        $options = [
            'user_id' => auth()->id(),
            'template' => $this->template,
        ];
        
        if (!empty($this->cc)) {
            $options['cc'] = $this->cc;
        }
        
        if (!empty($this->bcc)) {
            $options['bcc'] = $this->bcc;
        }
        
        if ($this->attachment) {
            $path = $this->attachment->store('attachments', 'public');
            $options['attachments'] = [
                [
                    'path' => storage_path('app/public/' . $path),
                    'name' => $this->attachment->getClientOriginalName(),
                    'mime' => $this->attachment->getMimeType(),
                ]
            ];
        }
        
        return $options;
    }
    
    protected function sendSingleEmail()
    {
        $this->validate([
            'recipient' => 'required|email',
        ]);
        
        $options = $this->prepareEmailOptions();
        
        return $this->emailService->sendSingle(
            $this->recipient,
            $this->subject,
            $this->message,
            $options
        );
    }
    
    protected function sendBulkEmail()
    {
        if (empty($this->recipients)) {
            return ['success' => false, 'message' => 'No recipients added.'];
        }
        
        $options = $this->prepareEmailOptions();
        
        return $this->emailService->sendBulk(
            $this->recipients,
            $this->subject,
            $this->message,
            $options
        );
    }
    
    protected function sendGroupEmail()
    {
        $this->validate([
            'recipientListId' => 'required|exists:recipient_lists,id',
        ]);
        
        $options = $this->prepareEmailOptions();
        
        return $this->emailService->sendToGroup(
            $this->recipientListId,
            $this->subject,
            $this->message,
            $options
        );
    }
    
    public function render()
    {
        return view('livewire.communication.send-email')
            ->layout('components.dashboard.default');
    }
}
