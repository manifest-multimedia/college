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
    public array $archivedSessions = [];
    public ?string $selectedSessionId = null;
    public ?array $selectedSession = null;
    public int $messageLimit = 50;
    public int $messageOffset = 0;
    public bool $isCreatingSession = false;
    public string $newSessionTitle = '';
    public $document = null; // For file uploads
    public string $activeTab = 'active'; // 'active' or 'archived'
    
    // Initialize with null to prevent "must not be accessed before initialization" error
    protected ?ChatServiceInterface $chatService = null;
    protected ?DocumentUploadService $documentService = null;
    
    protected $listeners = [
        'createNewSession',
        'selectSession',
        'refreshMessages',
        'loadMoreMessages',
        'archiveSession',
        'restoreSession',
        'deleteSession',
        'echo:private-chat.*,new.message' => 'handleNewMessage',
        'echo:private-chat.*,document.uploaded' => 'handleDocumentUploaded',
        'echo:private-chat.*,user.typing' => 'handleUserTyping',
        'echo:private-chat.*,ai.typing' => 'handleAiTyping',
        'addMessage',
        'changeTab',
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
    
    public function changeTab($tab)
    {
        $this->activeTab = $tab;
        
        if ($tab === 'archived') {
            $this->loadArchivedSessions();
            // Clear selected session if it's not in archived sessions
            if ($this->selectedSessionId) {
                $found = false;
                foreach ($this->archivedSessions as $session) {
                    if ($session['session_id'] === $this->selectedSessionId) {
                        $found = true;
                        break;
                    }
                }
                
                if (!$found) {
                    $this->selectedSessionId = null;
                    $this->selectedSession = null;
                    $this->messages = [];
                }
            }
        } else {
            $this->loadSessions();
            // Clear selected session if it's not in active sessions
            if ($this->selectedSessionId) {
                $found = false;
                foreach ($this->sessions as $session) {
                    if ($session['session_id'] === $this->selectedSessionId) {
                        $found = true;
                        break;
                    }
                }
                
                if (!$found) {
                    $this->selectedSessionId = null;
                    $this->selectedSession = null;
                    $this->messages = [];
                }
            }
        }
    }
    
    public function loadSessions()
    {
        try {
            $chatSessions = ChatSession::where('user_id', auth()->id())
                ->where('status', 'active')
                ->orderBy('last_activity_at', 'desc')
                ->limit(30)
                ->get()
                ->toArray();
            
            $this->sessions = $chatSessions;
            
            // Select the first session by default if none is selected and we're on the active tab
            if (empty($this->selectedSessionId) && !empty($this->sessions) && $this->activeTab === 'active') {
                $this->selectSession($this->sessions[0]['session_id']);
            }
        } catch (\Exception $e) {
            Log::error('Failed to load chat sessions', [
                'error' => $e->getMessage()
            ]);
            $this->sessions = [];
        }
    }
    
    public function loadArchivedSessions()
    {
        try {
            $archivedSessions = ChatSession::where('user_id', auth()->id())
                ->where('status', 'archived')
                ->orderBy('last_activity_at', 'desc')
                ->limit(30)
                ->get()
                ->toArray();
            
            $this->archivedSessions = $archivedSessions;
            
            // Select the first archived session by default if none is selected and we're on the archived tab
            if (empty($this->selectedSessionId) && !empty($this->archivedSessions) && $this->activeTab === 'archived') {
                $this->selectSession($this->archivedSessions[0]['session_id']);
            }
        } catch (\Exception $e) {
            Log::error('Failed to load archived chat sessions', [
                'error' => $e->getMessage()
            ]);
            $this->archivedSessions = [];
        }
    }
    
    public function selectSession($sessionId)
    {
        $this->selectedSessionId = $sessionId;
        $this->messageOffset = 0;
        $this->loadMessages();
        
        // Find the selected session from either active or archived sessions
        $allSessions = array_merge($this->sessions, $this->archivedSessions);
        $this->selectedSession = collect($allSessions)
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
    
    /**
     * Handle document upload
     */
    public function handleDocumentUpload()
    {
        if (!$this->document) {
            return;
        }
        
        try {
            // Get original file name
            $fileName = $this->document->getClientOriginalName();
            
            // Process and upload document
            $result = $this->documentService->uploadDocument(
                $this->selectedSessionId,
                $this->document
            );
            
            if ($result['success']) {
                // Check if there's a message to send along with the document
                if (!empty($this->message)) {
                    $userMessage = $this->message;
                    
                    // Generate message for AI that includes document information
                    $aiMessage = sprintf(
                        "User uploaded a document: '%s' with the message: \"%s\".",
                        $fileName,
                        $userMessage
                    );
                    
                    // Send notification to AI about the document and including user's message
                    $this->chatService->sendMessage(
                        $this->selectedSessionId,
                        $aiMessage,
                        auth()->id(),
                        [
                            'is_document_notification' => true, 
                            'document_path' => $result['file_path'] ?? null,
                            'openai_file_id' => $result['openai_file_id'] ?? null
                        ]
                    );
                    
                    // Clear the message input
                    $this->message = '';
                } else {
                    // If no message, still notify AI about the document
                    $aiMessage = sprintf("User uploaded a document: '%s'.", $fileName);
                    
                    $this->chatService->sendMessage(
                        $this->selectedSessionId,
                        $aiMessage,
                        auth()->id(),
                        [
                            'is_document_notification' => true, 
                            'document_path' => $result['file_path'] ?? null,
                            'openai_file_id' => $result['openai_file_id'] ?? null
                        ]
                    );
                }
                
                // Reset the document upload
                $this->document = null;
                
                // Refresh the messages
                $this->loadSessionMessages();
                
                // Display success message
                $this->dispatch('notify', [
                    'type' => 'success',
                    'message' => 'Document uploaded successfully'
                ]);
            } else {
                // Display error message
                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => $result['message']
                ]);
            }
        } catch (\Exception $e) {
            // Log the error
            Log::error('Failed to upload document', [
                'error' => $e->getMessage(),
                'session_id' => $this->selectedSessionId,
            ]);
            
            // Display error message
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to upload document: ' . $e->getMessage()
            ]);
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
                if ($this->activeTab === 'active') {
                    $this->loadSessions();
                } else {
                    $this->loadArchivedSessions();
                }
                
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
    
    public function restoreSession($sessionId)
    {
        try {
            $result = $this->chatService->updateSessionStatus($sessionId, 'active');
            
            if ($result) {
                if ($this->activeTab === 'archived') {
                    $this->loadArchivedSessions();
                } else {
                    $this->loadSessions();
                }
                
                if ($this->selectedSessionId === $sessionId) {
                    $this->selectedSessionId = null;
                    $this->selectedSession = null;
                    $this->messages = [];
                }
                session()->flash('success', 'Chat session restored successfully');
            } else {
                session()->flash('error', 'Failed to restore chat session');
            }
        } catch (\Exception $e) {
            Log::error('Failed to restore chat session', [
                'error' => $e->getMessage(),
                'session_id' => $sessionId
            ]);
            session()->flash('error', 'An error occurred while restoring chat session');
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
