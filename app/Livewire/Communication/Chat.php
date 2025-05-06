<?php

namespace App\Livewire\Communication;

use App\Models\ChatSession;
use App\Services\Communication\Chat\ChatServiceInterface;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class Chat extends Component
{
    public string $message = '';
    public ?string $sessionId = null;
    public array $messages = [];
    public array $sessions = [];
    public ?string $selectedSessionId = null;
    public ?array $selectedSession = null;
    public int $messageLimit = 50;
    public int $messageOffset = 0;
    public bool $isCreatingSession = false;
    public string $newSessionTitle = '';
    
    // Initialize with null to prevent "must not be accessed before initialization" error
    protected ?ChatServiceInterface $chatService = null;
    
    protected $listeners = [
        'createNewSession',
        'selectSession',
        'refreshMessages',
        'loadMoreMessages',
        'archiveSession',
        'deleteSession',
    ];
    
    public function boot(ChatServiceInterface $chatService)
    {
        $this->chatService = $chatService;
    }
    
    public function mount()
    {
        $this->loadSessions();
    }
    
    public function loadSessions()
    {
        try {
            $chatSessions = ChatSession::where('user_id', auth()->id())
                ->where('status', '!=', 'deleted')
                ->orderBy('last_activity_at', 'desc')
                ->limit(30)
                ->get()
                ->toArray();
            
            $this->sessions = $chatSessions;
            
            // Select the first session by default if none is selected
            if (empty($this->selectedSessionId) && !empty($this->sessions)) {
                $this->selectSession($this->sessions[0]['session_id']);
            }
        } catch (\Exception $e) {
            Log::error('Failed to load chat sessions', [
                'error' => $e->getMessage()
            ]);
            $this->sessions = [];
        }
    }
    
    public function selectSession($sessionId)
    {
        $this->selectedSessionId = $sessionId;
        $this->loadMessages();
        $this->selectedSession = collect($this->sessions)
            ->firstWhere('session_id', $sessionId);
    }
    
    public function loadMessages()
    {
        if (!$this->selectedSessionId) {
            return;
        }
        
        try {
            $result = $this->chatService->getMessageHistory(
                $this->selectedSessionId,
                $this->messageLimit,
                $this->messageOffset
            );
            
            if ($result['success']) {
                $this->messages = $result['messages'];
            } else {
                $this->messages = [];
                session()->flash('error', $result['message'] ?? 'Failed to load messages');
            }
        } catch (\Exception $e) {
            Log::error('Failed to load messages', [
                'error' => $e->getMessage(),
                'session_id' => $this->selectedSessionId
            ]);
            $this->messages = [];
            session()->flash('error', 'An error occurred while loading messages');
        }
    }
    
    public function loadMoreMessages()
    {
        $this->messageOffset += $this->messageLimit;
        $this->loadMessages();
    }
    
    public function refreshMessages()
    {
        $this->messageOffset = 0;
        $this->loadMessages();
    }
    
    public function sendMessage()
    {
        if (empty($this->message)) {
            return;
        }
        
        try {
            // If no session selected, create one
            if (!$this->selectedSessionId) {
                $result = $this->createNewSession();
                
                if (!$result['success']) {
                    session()->flash('error', $result['message'] ?? 'Failed to create a chat session');
                    return;
                }
                
                $this->selectedSessionId = $result['session_id'];
            }
            
            $result = $this->chatService->sendMessage(
                $this->selectedSessionId,
                $this->message,
                auth()->id()
            );
            
            if ($result['success']) {
                $this->message = '';
                $this->refreshMessages();
                $this->loadSessions(); // Refresh the list to update last_activity_at
            } else {
                session()->flash('error', $result['message'] ?? 'Failed to send message');
            }
        } catch (\Exception $e) {
            Log::error('Failed to send message', [
                'error' => $e->getMessage(),
                'session_id' => $this->selectedSessionId
            ]);
            session()->flash('error', 'An error occurred while sending message');
        }
    }
    
    public function createNewSession()
    {
        try {
            $result = $this->chatService->createSession(
                auth()->id(),
                $this->newSessionTitle ?: 'New Conversation',
                []
            );
            
            if ($result['success']) {
                $this->isCreatingSession = false;
                $this->newSessionTitle = '';
                $this->loadSessions();
                $this->selectSession($result['session_id']);
            } else {
                session()->flash('error', $result['message'] ?? 'Failed to create a chat session');
            }
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to create chat session', [
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'An error occurred while creating chat session');
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    public function toggleNewSessionForm()
    {
        $this->isCreatingSession = !$this->isCreatingSession;
    }
    
    public function archiveSession($sessionId)
    {
        try {
            $result = $this->chatService->updateSessionStatus($sessionId, 'archived');
            
            if ($result) {
                $this->loadSessions();
                if ($this->selectedSessionId === $sessionId) {
                    $this->selectedSessionId = null;
                    $this->selectedSession = null;
                    $this->messages = [];
                }
                session()->flash('success', 'Chat session archived successfully');
            } else {
                session()->flash('error', 'Failed to archive chat session');
            }
        } catch (\Exception $e) {
            Log::error('Failed to archive chat session', [
                'error' => $e->getMessage(),
                'session_id' => $sessionId
            ]);
            session()->flash('error', 'An error occurred while archiving chat session');
        }
    }
    
    public function deleteSession($sessionId)
    {
        try {
            $result = $this->chatService->updateSessionStatus($sessionId, 'deleted');
            
            if ($result) {
                $this->loadSessions();
                if ($this->selectedSessionId === $sessionId) {
                    $this->selectedSessionId = null;
                    $this->selectedSession = null;
                    $this->messages = [];
                }
                session()->flash('success', 'Chat session deleted successfully');
            } else {
                session()->flash('error', 'Failed to delete chat session');
            }
        } catch (\Exception $e) {
            Log::error('Failed to delete chat session', [
                'error' => $e->getMessage(),
                'session_id' => $sessionId
            ]);
            session()->flash('error', 'An error occurred while deleting chat session');
        }
    }
    
    public function render()
    {
        return view('livewire.communication.chat')
            ->layout('components.dashboard.default');
    }
}
