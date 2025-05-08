<?php

namespace App\Livewire\Communication;

use App\Models\ChatSession;
use App\Services\Communication\Chat\ChatServiceInterface;
use App\Services\Communication\Chat\Document\DocumentUploadService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;

class Chat extends Component
{
    use WithFileUploads;
    
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
    public $document = null; // For file uploads
    
    // Initialize with null to prevent "must not be accessed before initialization" error
    protected ?ChatServiceInterface $chatService = null;
    protected ?DocumentUploadService $documentService = null;
    
    protected $listeners = [
        'createNewSession',
        'selectSession',
        'refreshMessages',
        'loadMoreMessages',
        'archiveSession',
        'deleteSession',
        'echo:private-chat.*,new.message' => 'handleNewMessage',
        'echo:private-chat.*,document.uploaded' => 'handleDocumentUploaded',
        'echo:private-chat.*,user.typing' => 'handleUserTyping',
        'echo:private-chat.*,ai.typing' => 'handleAiTyping',
        'addMessage',
    ];
    
    protected $rules = [
        'document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,gif,txt,csv,xls,xlsx|max:10240',
    ];
    
    public function boot(ChatServiceInterface $chatService, DocumentUploadService $documentService)
    {
        $this->chatService = $chatService;
        $this->documentService = $documentService;
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
        $this->messageOffset = 0;
        $this->loadMessages();
        $this->selectedSession = collect($this->sessions)
            ->firstWhere('session_id', $sessionId);
        
        // Dispatch event for frontend to listen for real-time updates
        $this->dispatch('sessionSelected', $sessionId);
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
        // If there's a document to upload, handle it first
        if ($this->document) {
            $this->handleDocumentUpload();
            return;
        }
        
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
            
            // Store the message content before it's cleared
            $messageContent = $this->message;
            
            // Immediately add the user message to the messages array for instant feedback
            $userMessage = [
                'id' => 'temp_' . time(),
                'type' => 'user',
                'message' => $messageContent,
                'timestamp' => now(),
            ];
            $this->messages[] = $userMessage;
            
            // Clear the input field right away
            $this->message = '';
            
            $result = $this->chatService->sendMessage(
                $this->selectedSessionId,
                $messageContent,
                auth()->id()
            );
            
            if ($result['success']) {
                // After getting success response, refresh messages to get both user message and AI response
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
    
    public function handleDocumentUpload()
    {
        // Validate the document first
        $this->validate();
        
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
            
            // Get file information for temporary display
            $fileName = $this->document->getClientOriginalName();
            
            // Add a placeholder for the document upload
            $documentMessage = [
                'id' => 'temp_doc_' . time(),
                'type' => 'user',
                'is_document' => true,
                'message' => 'Document uploading: ' . $fileName,
                'file_name' => $fileName,
                'timestamp' => now(),
            ];
            
            // Add to messages array for immediate feedback
            $this->messages[] = $documentMessage;
            
            // Upload the document
            $result = $this->documentService->uploadDocument(
                $this->selectedSessionId,
                $this->document
            );
            
            if ($result['success']) {
                $this->document = null; // Reset the upload
                $this->refreshMessages(); // Get the actual uploaded document with proper links
                $this->loadSessions(); // Refresh the list to update last_activity_at
            } else {
                session()->flash('error', $result['message'] ?? 'Failed to upload document');
            }
        } catch (\Exception $e) {
            Log::error('Failed to upload document', [
                'error' => $e->getMessage(),
                'session_id' => $this->selectedSessionId
            ]);
            session()->flash('error', 'An error occurred while uploading document');
        }
    }
    
    public function removeDocument()
    {
        $this->document = null;
    }
    
    public function handleTyping()
    {
        // Typing event handled by JavaScript in the view
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
    
    /**
     * Handle real-time events
     */
    public function addMessage($message)
    {
        // Add the message to the end of the messages array
        $this->messages[] = $message;
    }
    
    public function handleNewMessage($event)
    {
        $this->refreshMessages();
    }
    
    public function handleDocumentUploaded($event)
    {
        $this->refreshMessages();
    }
    
    public function handleUserTyping($event)
    {
        // Handled by JavaScript in the view
    }
    
    public function handleAiTyping($event)
    {
        // Handled by JavaScript in the view
    }
    
    public function render()
    {
        return view('livewire.communication.chat')
            ->layout('components.dashboard.default');
    }
}
