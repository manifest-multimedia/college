<div class="row g-5">
    <!-- Sessions List Column -->
    <div class="col-12 col-md-4 col-lg-3">
        <!-- Sessions Header -->
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0">AI Sensei</h3>
                    <button class="btn btn-sm btn-primary" wire:click="startNewChat">
                        <i class="bi bi-plus-lg"></i> New Chat
                    </button>
                </div>
            </div>
        </div>

        <!-- About AI Sensei -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h3 class="card-title mb-0">About AI Sensei</h3>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <img src="{{ asset('images/ai-sensei.png') }}" alt="AI Sensei" class="rounded-circle me-3" width="60" height="60">
                    <div>
                        <h5 class="mb-1">AI Sensei Assistant</h5>
                        <p class="text-muted mb-0">Intelligent educational AI</p>
                    </div>
                </div>
                <p class="mb-0">
                    AI Sensei can help with your educational queries, analyze documents, provide insights, 
                    and assist with research. Upload documents for analysis and get comprehensive assistance.
                </p>
                
                <!-- Files Attached to Current Thread -->
                @if(count($filesAttachedToThread) > 0)
                <div class="mt-3">
                    <h6>Attached Files</h6>
                    <ul class="list-group list-group-flush">
                        @foreach($filesAttachedToThread as $file)
                        <li class="list-group-item d-flex justify-content-between align-items-center p-2 border-0">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-file-earmark me-2"></i>
                                <small class="text-truncate">{{ $file['filename'] ?? 'File' }}</small>
                            </div>
                            <button class="btn btn-sm btn-outline-danger" wire:click="removeFile('{{ $file['id'] }}')">
                                <i class="bi bi-x"></i>
                            </button>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
                
                <!-- AI Tools -->
                <div class="mt-3">
                    <h6>Available Tools</h6>
                    <div class="d-flex flex-wrap gap-1">
                        <span class="badge bg-primary rounded-pill">File Search</span>
                        <span class="badge bg-primary rounded-pill">Code Interpreter</span>
                        <span class="badge bg-primary rounded-pill">Function Calling</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chat Area Column -->
    <div class="col-12 col-md-8 col-lg-9">
        <div class="card shadow-sm vh-75" style="min-height: 75vh;">
            @if($error)
                <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0 rounded-top" role="alert">
                    {{ $error }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Chat Header -->
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <img src="{{ asset('images/ai-sensei.png') }}" alt="AI Sensei" class="rounded-circle" width="40" height="40">
                        </div>
                        <div>
                            <h3 class="card-title mb-0">AI Sensei Assistant</h3>
                            <small class="text-muted">
                                <span class="badge bg-success">Online</span>
                                <!-- Show available tools -->
                                <span class="ms-2">Tools: File Analysis, Code, Math</span>
                            </small>
                        </div>
                    </div>
                    <div>
                        <button class="btn btn-outline-primary me-2" wire:click="startNewChat">
                            <i class="bi bi-plus-circle"></i> New Chat
                        </button>
                    </div>
                </div>
            </div>

            <!-- Chat Messages Area -->
            <div class="card-body d-flex flex-column" style="height: 70vh; overflow: hidden;">
                <!-- Messages -->
                <div id="chat-messages" class="flex-grow-1 overflow-auto px-1 py-3">
                    @if(count($messages) > 0)
                        @foreach($messages as $message)
                        <div class="mb-4 d-flex {{ $message['role'] === 'user' ? 'justify-content-end' : 'justify-content-start' }}">
                            @if($message['role'] !== 'user')
                            <div class="ai-avatar me-2">
                                <img src="{{ asset('images/ai-sensei.png') }}" alt="AI Sensei" class="rounded-circle" width="40" height="40">
                            </div>
                            @endif
                            
                            <div class="card {{ $message['role'] === 'user' ? 'bg-light border-0 shadow-sm' : 'bg-primary text-white border-0 shadow' }}" 
                                 style="max-width: 80%; border-radius: 18px;">
                                <div class="card-body py-2 px-3">
                                    @foreach($message['content'] as $contentItem)
                                        @if($contentItem['type'] === 'text')
                                            <p class="mb-0">{!! nl2br(e($contentItem['text'])) !!}</p>
                                        @elseif($contentItem['type'] === 'image')
                                            <div class="image-attachment mb-2">
                                                <img src="{{ $contentItem['file_url'] ?? '#' }}" class="img-fluid rounded" alt="Image Attachment">
                                            </div>
                                        @endif
                                    @endforeach
                                    
                                    <div class="text-end">
                                        <small class="{{ $message['role'] === 'user' ? 'text-muted' : 'text-white-50' }}">
                                            {{ \Carbon\Carbon::parse($message['created_at'])->format('h:i A') }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            @if($message['role'] === 'user')
                            <div class="user-avatar ms-2">
                                <div class="rounded-circle bg-info d-flex justify-content-center align-items-center text-white" style="width: 40px; height: 40px;">
                                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                                </div>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    @else
                        <div class="d-flex align-items-center justify-content-center flex-column h-100">
                            <img src="{{ asset('images/ai-sensei.png') }}" alt="AI Sensei" class="mb-4" width="120" height="120">
                            <div class="text-center">
                                <h4>Welcome to AI Sensei</h4>
                                <p class="text-muted">Ask any question or upload a document for analysis.</p>
                                <p>AI Sensei can:</p>
                                <ul class="list-group list-group-flush mb-3">
                                    <li class="list-group-item border-0">📄 Analyze and process your documents</li>
                                    <li class="list-group-item border-0">🧮 Perform complex calculations</li>
                                    <li class="list-group-item border-0">💻 Help with programming tasks</li>
                                    <li class="list-group-item border-0">🔍 Search through file contents</li>
                                </ul>
                            </div>
                        </div>
                    @endif
                    
                    @if($isLoading)
                    <div class="mb-4 d-flex justify-content-start">
                        <div class="ai-avatar me-2">
                            <img src="{{ asset('images/ai-sensei.png') }}" alt="AI Sensei" class="rounded-circle" width="40" height="40">
                        </div>
                        <div class="card bg-primary text-white" style="max-width: 80%; border-radius: 15px;">
                            <div class="card-body py-2 px-3">
                                <p class="mb-0">
                                    <span class="typing-text">AI Sensei is thinking</span>
                                    <span class="typing-dots">...</span>
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                
                <!-- Message Input -->
                <div class="mt-auto">
                    <form wire:submit.prevent="sendMessage" class="mt-3">
                        <div class="input-group mb-2">
                            <!-- File Upload Button -->
                            <button class="btn btn-outline-secondary" type="button" onclick="document.getElementById('file-upload').click()">
                                <i class="bi bi-paperclip"></i>
                            </button>
                            
                            <textarea class="form-control" id="message-input" 
                                      wire:model="newMessage" rows="2" 
                                      placeholder="Message AI Sensei..." 
                                      wire:keydown.enter.prevent="sendMessage"></textarea>
                                      
                            <button class="btn btn-primary" type="submit" id="send-button" @if($isLoading) disabled @endif>
                                <i class="bi bi-send-fill"></i>
                            </button>
                        </div>
                        
                        <!-- Hidden file input -->
                        <input type="file" id="file-upload" class="d-none" wire:model="temporaryUploads" multiple />
                        
                        <!-- File upload status -->
                        @if($uploadingFile)
                        <div class="progress mt-2">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" style="width: 100%">
                                Uploading file...
                            </div>
                        </div>
                        @endif
                        
                        <!-- Show temporary upload files -->
                        @if(!empty($temporaryUploads))
                        <div class="mt-2">
                            @foreach($temporaryUploads as $file)
                            <div class="alert alert-info d-flex align-items-center mb-2">
                                <i class="bi bi-file-earmark me-2"></i>
                                <div class="flex-grow-1 text-truncate">{{ $file->getClientOriginalName() }}</div>
                                <button type="button" class="btn-close btn-sm" wire:click="$set('temporaryUploads', [])"></button>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </form>
                    
                    <div class="text-center text-muted small mt-3">
                        <p>AI Sensei uses OpenAI Assistants API with file search, code interpreter, and function calling capabilities</p>
                    </div>
                </div>
            </div>
            
            <!-- Scroll to bottom button -->
            <button id="scroll-to-bottom" class="btn btn-primary rounded-circle shadow d-none">
                <i class="bi bi-arrow-down"></i>
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        const chatMessagesContainer = document.getElementById('chat-messages');
        const scrollToBottomButton = document.getElementById('scroll-to-bottom');
        let shouldScroll = true;
        
        // Function to scroll to bottom of chat
        function scrollToBottom() {
            if (!chatMessagesContainer || !shouldScroll) return;
            chatMessagesContainer.scrollTop = chatMessagesContainer.scrollHeight;
            if (scrollToBottomButton) {
                scrollToBottomButton.classList.add('d-none');
            }
        }
        
        // Initial scroll to bottom
        setTimeout(scrollToBottom, 200);
        
        // Detect scrolling to determine whether auto-scroll should be enabled
        if (chatMessagesContainer && scrollToBottomButton) {
            chatMessagesContainer.addEventListener('scroll', function() {
                const isAtBottom = chatMessagesContainer.scrollHeight - chatMessagesContainer.scrollTop - chatMessagesContainer.clientHeight < 50;
                shouldScroll = isAtBottom;
                
                // Show/hide scroll to bottom button
                if (isAtBottom) {
                    scrollToBottomButton.classList.add('d-none');
                } else {
                    scrollToBottomButton.classList.remove('d-none');
                }
            });
            
            // Scroll to bottom button click handler
            scrollToBottomButton.addEventListener('click', function() {
                shouldScroll = true;
                scrollToBottom();
            });
        }
        
        // Listen for updates to messages
        Livewire.on('messages-updated', () => {
            setTimeout(scrollToBottom, 200);
        });
        
        // Animation for typing dots
        setInterval(() => {
            const typingDots = document.querySelectorAll('.typing-dots');
            typingDots.forEach(el => {
                switch (el.innerText) {
                    case '.': el.innerText = '..'; break;
                    case '..': el.innerText = '...'; break;
                    default: el.innerText = '.'; break;
                }
            });
        }, 500);
        
        // Reset auto-scroll when sending a new message
        document.getElementById('send-button')?.addEventListener('click', function() {
            shouldScroll = true;
        });
    });
</script>

<style>
    /* Chat container styles */
    #chat-messages {
        scrollbar-width: thin;
        scrollbar-color: rgba(0, 0, 0, 0.2) transparent;
    }
    
    #chat-messages::-webkit-scrollbar {
        width: 6px;
    }
    
    #chat-messages::-webkit-scrollbar-thumb {
        background-color: rgba(0, 0, 0, 0.2);
        border-radius: 3px;
    }
    
    /* Message styles */
    .card {
        transition: all 0.2s ease;
    }
    
    /* User and AI indicators */
    .user-avatar, .ai-avatar {
        align-self: flex-end;
        margin-bottom: 5px;
    }
    
    /* Typing indicator animation */
    @keyframes pulse {
        0% { opacity: 0.5; }
        50% { opacity: 1; }
        100% { opacity: 0.5; }
    }
    
    .typing-dots {
        animation: pulse 1.5s infinite;
    }
    
    /* Scroll to bottom button */
    #scroll-to-bottom {
        position: absolute;
        bottom: 20px;
        right: 20px;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        transition: opacity 0.3s;
        border: none;
    }
</style>
@endpush