    <div class="row g-5" wire:init="markLoaded" x-data="chatManager()" x-init="initChat" x-cloak>
        <!-- Sessions List Column -->
        <div class="col-12 col-md-4 col-lg-3">
            <!-- Sessions Header -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <h3 class="card-title mb-0">AI Sensei</h3>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-secondary" wire:click="toggleSessionHistory" 
                                    title="Toggle History">
                                <i class="bi bi-clock-history"></i>
                            </button>
                            <button class="btn btn-sm btn-primary" wire:click="startNewChat">
                                <i class="bi bi-plus-lg"></i> New Chat
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Chat History Section -->
                @if ($showSessionHistory || count($chatSessions) > 0)
                    <div class="card-body">
                        <!-- Current Session Info -->
                        @if ($currentChatSession)
                            <div class="mb-3 p-2 bg-light rounded">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        @if ($editingSessionTitle === $currentChatSession->session_id)
                                            <div class="input-group input-group-sm">
                                                <input type="text" class="form-control" wire:model="newSessionTitle" 
                                                       wire:keydown.enter="saveSessionTitle('{{ $currentChatSession->session_id }}')"
                                                       wire:keydown.escape="cancelEditingTitle" 
                                                       autofocus>
                                                <button class="btn btn-outline-success" type="button" 
                                                        wire:click="saveSessionTitle('{{ $currentChatSession->session_id }}')">
                                                    <i class="bi bi-check"></i>
                                                </button>
                                                <button class="btn btn-outline-secondary" type="button" 
                                                        wire:click="cancelEditingTitle">
                                                    <i class="bi bi-x"></i>
                                                </button>
                                            </div>
                                        @else
                                            <div class="fw-bold text-primary cursor-pointer" 
                                                 wire:click="startEditingTitle('{{ $currentChatSession->session_id }}')"
                                                 title="Click to edit title">
                                                {{ $currentChatSession->title }}
                                            </div>
                                        @endif
                                        <small class="text-muted">
                                            Current • {{ count($messages) }} messages
                                            @if ($currentChatSession->last_activity_at)
                                                • {{ $currentChatSession->last_activity_at->diffForHumans() }}
                                            @endif
                                        </small>
                                    </div>
                                    <span class="badge bg-success">Active</span>
                                </div>
                            </div>
                        @endif

                        <!-- Session Search -->
                        <div class="mb-3">
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" placeholder="Search conversations..." 
                                       wire:model.live.debounce.300ms="sessionSearchQuery">
                                <span class="input-group-text">
                                    <i class="bi bi-search"></i>
                                </span>
                            </div>
                        </div>

                        <!-- Session History List -->
                        <div class="session-list" style="max-height: 300px; overflow-y: auto;">
                            @forelse ($chatSessions as $session)
                                <div class="session-item p-2 mb-2 border rounded cursor-pointer position-relative
                                            {{ $currentChatSession && $currentChatSession->session_id === $session['session_id'] ? 'border-primary bg-light' : 'border-light' }}"
                                     wire:click="loadChatSession('{{ $session['session_id'] }}')">
                                    
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1 me-2">
                                            <div class="fw-semibold small">{{ Str::limit($session['title'], 30) }}</div>
                                            <small class="text-muted">
                                                {{ $session['message_count'] }} messages • {{ $session['last_activity_human'] }}
                                            </small>
                                        </div>
                                        
                                        <!-- Session Actions Dropdown -->
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary border-0" type="button" 
                                                    data-bs-toggle="dropdown" aria-expanded="false"
                                                    onclick="event.stopPropagation();">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="#" 
                                                       wire:click.prevent="startEditingTitle('{{ $session['session_id'] }}')"
                                                       onclick="event.stopPropagation();">
                                                        <i class="bi bi-pencil me-2"></i>
                                                        Rename
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="#" 
                                                       wire:click.prevent="autoGenerateTitle('{{ $session['session_id'] }}')"
                                                       onclick="event.stopPropagation();">
                                                        <i class="bi bi-lightning me-2"></i>
                                                        Auto-title
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item" href="#" 
                                                       wire:click.prevent="archiveSession('{{ $session['session_id'] }}')"
                                                       onclick="event.stopPropagation();">
                                                        <i class="bi bi-archive me-2"></i>
                                                        Archive
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="#" 
                                                       wire:click.prevent="deleteSession('{{ $session['session_id'] }}')"
                                                       onclick="event.stopPropagation();"
                                                       wire:confirm="Are you sure you want to delete this conversation? This action cannot be undone.">
                                                        <i class="bi bi-trash me-2"></i>
                                                        Delete
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted py-3">
                                    @if ($sessionSearchQuery)
                                        <div>
                                            <i class="bi bi-search fs-3 text-muted mb-2"></i>
                                            <div>No conversations found</div>
                                            <small>Try different search terms</small>
                                        </div>
                                    @else
                                        <div>
                                            <i class="bi bi-chat-dots fs-3 text-muted mb-2"></i>
                                            <div>No conversation history</div>
                                            <small>Previous chats will appear here</small>
                                        </div>
                                    @endif
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endif
            </div>

            <!-- About AI Sensei -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h3 class="card-title mb-0">About AI Sensei</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <img src="{{ asset('images/ai-sensei.png') }}" alt="AI Sensei" class="rounded-circle me-3"
                            width="60" height="60">
                        <div>
                            <h5 class="mb-1">AI Sensei Assistant</h5>
                            <p class="text-muted mb-0">Intelligent Educational AI</p>
                        </div>
                    </div>
                    <p class="mb-0">
                        AI Sensei can help with your educational queries, analyze documents, provide insights,
                        assist with research, and manage your exam content including creating question sets,
                        adding questions, and organizing course materials.
                    </p>

                    <!-- Files Attached to Current Thread -->
                    @if (count($filesAttachedToThread) > 0)
                        <div class="mt-3">
                            <h6>Attached Files</h6>
                            <ul class="list-group list-group-flush">
                                @foreach ($filesAttachedToThread as $file)
                                    <li
                                        class="list-group-item d-flex justify-content-between align-items-center p-2 border-0">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-file-earmark me-2"></i>
                                            <small class="text-truncate">{{ $file['filename'] ?? 'File' }}</small>
                                        </div>
                                        <button class="btn btn-sm btn-outline-danger"
                                            wire:click="removeFile('{{ $file['id'] }}')">
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
                            <span class="badge bg-success rounded-pill">Chat</span>
                            <span class="badge bg-success rounded-pill">File Analysis</span>
                            <span class="badge bg-success rounded-pill">Code Analysis</span>
                            <span class="badge bg-primary rounded-pill">Exam Management</span>
                        </div>
                        <small class="text-muted d-block mt-2">
                            <strong>Exam Management:</strong> Create question sets, add questions, manage exams, and organize course content
                        </small>
                    </div>
                    {{-- Tools in Development --}}
                    <div class="mt-3">
                        <h6>Tools in Development</h6>
                        <div class="d-flex flex-wrap gap-1">
                            <span class="badge bg-secondary rounded-pill">Advanced Analytics</span>
                            <span class="badge bg-secondary rounded-pill">Automated Grading</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Area Column -->
        <div class="col-12 col-md-8 col-lg-9">
            <div class="card shadow-sm vh-75 position-relative" style="min-height: 75vh;">
                @if ($error)
                    <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0 rounded-top"
                        role="alert">
                        {{ $error }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Chat Header -->
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <div class="d-flex align-items-center">
                            <div class="me-3 position-relative">
                                <img src="{{ asset('images/ai-sensei.png') }}" alt="AI Sensei" class="rounded-circle"
                                    width="40" height="40">
                                <!-- AI Status Indicator -->
                                <span class="position-absolute bottom-0 end-0 bg-success rounded-circle p-1"
                                    style="width: 10px; height: 10px;"></span>
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
                        <button class="btn btn-sm btn-primary ms-auto" wire:click="startNewChat">
                            <i class="bi bi-plus-lg"></i> New Chat
                        </button>
                    </div>
                </div>

                <!-- Chat Messages Area -->
                <div class="card-body d-flex flex-column position-relative" style="height: 70vh; overflow: hidden;">
                    <!-- Messages -->
                    <div id="chat-messages" class="flex-grow-1 overflow-auto px-1 py-3">
                        <div id="message-container">
                            @if (count($messages) > 0)
                                @foreach ($messages as $message)
                                    <div
                                        class="mb-4 d-flex {{ $message['role'] === 'user' ? 'justify-content-end' : 'justify-content-start' }} message-animation">
                                        @if ($message['role'] !== 'user')
                                            <div class="ai-avatar me-2">
                                                <img src="{{ asset('images/ai-sensei.png') }}" alt="AI Sensei"
                                                    class="rounded-circle" width="40" height="40">
                                            </div>
                                        @endif

                                        <div class="card {{ $message['role'] === 'user' ? 'bg-light border-0 shadow-sm' : 'bg-primary text-white border-0 shadow' }}"
                                            style="max-width: 80%; border-radius: 18px;">
                                            <div class="card-body py-2 px-3">
                                                @foreach ($message['content'] as $contentItem)
                                                    @if ($contentItem['type'] === 'text')
                                                        <div class="mb-0 rendered-markdown">{!! $this->renderMarkdown($contentItem['text']) !!}</div>
                                                    @elseif($contentItem['type'] === 'image')
                                                        <div class="image-attachment mb-2">
                                                            <img src="{{ $contentItem['file_url'] ?? '#' }}"
                                                                class="img-fluid rounded" alt="Image Attachment">
                                                        </div>
                                                    @elseif($contentItem['type'] === 'file_attachment')
                                                        <div class="file-attachment mb-2">
                                                            <div class="card bg-light border">
                                                                <div class="card-body p-2 d-flex align-items-center">
                                                                    <div class="file-icon me-2" style="font-size: 1.5rem;">
                                                                        {!! $this->getFileIcon($contentItem['filename']) !!}
                                                                    </div>
                                                                    <div class="flex-grow-1">
                                                                        <div class="fw-bold text-truncate" style="font-size: 0.85rem;">
                                                                            {{ $contentItem['filename'] }}
                                                                        </div>
                                                                        <div class="text-muted small">
                                                                            {{ number_format($contentItem['size'] / 1024, 1) }} KB
                                                                        </div>
                                                                    </div>
                                                                    <div class="text-success">
                                                                        <i class="bi bi-check-circle"></i>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach

                                                <div class="text-end">
                                                    <small
                                                        class="{{ $message['role'] === 'user' ? 'text-muted' : 'text-white-50' }}">
                                                        {{ \Carbon\Carbon::parse($message['created_at'])->format('h:i A') }}
                                                    </small>
                                                </div>
                                            </div>
                                        </div>

                                        @if ($message['role'] === 'user')
                                            <div class="user-avatar ms-2">
                                                <div class="rounded-circle bg-info d-flex justify-content-center align-items-center text-white"
                                                    style="width: 40px; height: 40px;">
                                                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <div class="d-flex align-items-center justify-content-center flex-column h-100">
                                    <img src="{{ asset('images/ai-sensei.png') }}" alt="AI Sensei" class="mb-4"
                                        width="120" height="120">
                                    <div class="text-center">
                                        <h4>Welcome to AI Sensei</h4>
                                        <p class="text-muted">Ask any question or upload a document for analysis.</p>
                                        <p>AI Sensei can:</p>
                                        <ul class="list-group list-group-flush mb-3">
                                            <li class="list-group-item border-0">📄 Analyze and process your documents
                                            </li>
                                            <li class="list-group-item border-0">🧮 Perform complex calculations</li>
                                            <li class="list-group-item border-0">💻 Help with programming tasks</li>
                                            <li class="list-group-item border-0">🔍 Search through file contents</li>
                                        </ul>
                                    </div>
                                </div>
                            @endif

                            <!-- Client-side temporary message container -->
                            <template x-for="(message, index) in localMessages" :key="index">
                                <div class="mb-4 d-flex justify-content-end message-animation">
                                    <div class="card bg-light border-0 shadow-sm"
                                        style="max-width: 80%; border-radius: 18px;">
                                        <div class="card-body py-2 px-3">
                                            <p class="mb-0" x-text="message.content"></p>
                                            <div class="text-end">
                                                <small class="text-muted">
                                                    <span x-text="message.time"></span>
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="user-avatar ms-2">
                                        <div class="rounded-circle bg-info d-flex justify-content-center align-items-center text-white"
                                            style="width: 40px; height: 40px;">
                                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- User Typing Indicator - Fixed with proper hide/show logic -->
                            <div class="mb-4 d-none user-typing-indicator-container"
                                :class="{ 'd-flex': isTyping && !isAiResponseInProgress && isPageLoaded, 'd-none': !isTyping ||
                                        isAiResponseInProgress || !isPageLoaded }"
                                style="justify-content: flex-end">
                                <div class="card bg-light border-0 shadow-sm"
                                    style="max-width: 80%; border-radius: 18px;">
                                    <div class="card-body py-2 px-3">
                                        <div class="user-typing-indicator">
                                            <span></span>
                                            <span></span>
                                            <span></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="user-avatar ms-2">
                                    <div class="rounded-circle bg-info d-flex justify-content-center align-items-center text-white"
                                        style="width: 40px; height: 40px;">
                                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                                    </div>
                                </div>
                            </div>

                            <!-- AI Typing Indicator - Fixed with proper hide/show logic -->
                            <div class="mb-4 d-none ai-typing-indicator-container"
                                :class="{ 'd-flex': isAiResponseInProgress && isPageLoaded, 'd-none': !isAiResponseInProgress ||
                                        !isPageLoaded }"
                                style="justify-content: flex-start">
                                <div class="ai-avatar me-2">
                                    <img src="{{ asset('images/ai-sensei.png') }}" alt="AI Sensei"
                                        class="rounded-circle" width="40" height="40">
                                </div>
                                <div class="card bg-primary text-white" style="max-width: 80%; border-radius: 18px;">
                                    <div class="card-body py-2 px-3">
                                        <div class="d-flex align-items-center">
                                            <div class="typing-indicator me-2">
                                                <span></span>
                                                <span></span>
                                                <span></span>
                                            </div>
                                            <span>AI Sensei is thinking</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- File Staging Area -->
                    @if (!empty($pendingFiles))
                        <div class="border-top pt-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <small class="text-muted fw-bold">Files to Send ({{ count($pendingFiles) }})</small>
                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                        wire:click="clearPendingFiles">
                                    Clear All
                                </button>
                            </div>
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                @foreach ($pendingFiles as $index => $file)
                                    <div class="file-preview-item">
                                        <div class="card border-0 shadow-sm" style="min-width: 120px; max-width: 200px;">
                                            <div class="card-body p-2 text-center">
                                                <div class="file-icon mb-1" style="font-size: 2rem;">
                                                    {!! $this->getFileIcon($file['filename'] ?? 'unknown') !!}
                                                </div>
                                                <div class="file-name text-truncate" style="font-size: 0.75rem;" 
                                                     title="{{ $file['filename'] ?? 'Unknown File' }}">
                                                    {{ $file['filename'] ?? 'Unknown File' }}
                                                </div>
                                                <div class="file-size text-muted" style="font-size: 0.65rem;">
                                                    {{ number_format(($file['size'] ?? 0) / 1024, 1) }} KB
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-danger mt-1" 
                                                        wire:click="removePendingFile({{ $index }})">
                                                    <i class="bi bi-x" style="font-size: 0.75rem;"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Message Input -->
                    <div class="mt-auto">
                        <form @submit.prevent="sendChatMessage" class="mt-3">
                            <div class="input-group mb-2">
                                <!-- File Upload Button -->
                                <button class="btn btn-outline-secondary" type="button"
                                    onclick="document.getElementById('file-upload').click()" 
                                    wire:loading.attr="disabled" 
                                    wire:target="temporaryUploads"
                                    style="border: 1px solid #ced4da;">
                                    <i class="bi bi-paperclip" style="font-size:25px"></i>
                                </button>

                                <textarea class="form-control" id="message-input" x-model="newMessage" rows="2"
                                    placeholder="Message AI Sensei..." @input="handleTyping" @keydown.enter.prevent="sendChatMessage"
                                    :disabled="isSubmitting"
                                    wire:loading.attr="disabled" 
                                    wire:target="temporaryUploads"></textarea>

                                <button class="btn btn-primary" type="submit" id="send-button"
                                    :disabled="isSubmitting || !newMessage.trim()">
                                    <span x-show="!isSubmitting"><i class="bi bi-send-fill"></i></span>
                                    <span x-show="isSubmitting" class="spinner-border spinner-border-sm"
                                        role="status"></span>
                                </button>
                            </div>

                            <!-- Hidden file input -->
                            <input type="file" id="file-upload" class="d-none" wire:model="temporaryUploads"
                                multiple />
                            
                            <!-- File processing indicator (shows while Livewire uploads file) -->
                            <div wire:loading wire:target="temporaryUploads" class="mt-2 alert alert-info d-flex align-items-center">
                                <div class="spinner-border spinner-border-sm me-2" role="status">
                                    <span class="visually-hidden">Processing...</span>
                                </div>
                                <span>Processing file upload... Please wait.</span>
                            </div>

                            <!-- File upload status -->
                            @if ($uploadingFile)
                                <div class="file-upload-progress mt-2">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                                            <span class="visually-hidden">Processing...</span>
                                        </div>
                                        <span class="text-primary fw-bold">Processing files and message...</span>
                                    </div>
                                    <div class="progress">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                            role="progressbar" style="width: 100%">
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Show upload progress or validation errors -->
                            @if (!empty($temporaryUploads))
                                <div class="mt-2">
                                    @foreach ($temporaryUploads as $file)
                                        <div class="alert alert-success d-flex align-items-center mb-2">
                                            <i class="bi bi-check-circle me-2"></i>
                                            <div class="flex-grow-1 text-truncate">
                                                File uploaded: {{ $file->getClientOriginalName() }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if (!empty($fileValidationErrors))
                                <div class="mt-2">
                                    @foreach ($fileValidationErrors as $error)
                                        <div class="alert alert-danger alert-dismissible fade show mb-2" role="alert">
                                            <i class="bi bi-exclamation-triangle me-2"></i>
                                            {{ $error }}
                                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </form>

                        <div class="text-center text-muted small mt-3">
                            <p>AI Sensei is a Useful Assistant Still in Development</p>
                        </div>
                    </div>
                </div>

                <!-- Scroll to bottom button -->
                <button id="scroll-to-bottom" class="btn btn-primary rounded-circle shadow d-none"
                    @click="scrollToBottom">
                    <i class="bi bi-arrow-down"></i>
                </button>
            </div>
        </div>
    </div>


    @push('scripts')
        <script>
            document.addEventListener('livewire:initialized', () => {
                // Listen for updates to messages
                Livewire.on('messages-updated', () => {
                    Alpine.store('chat').updateAiResponseStatus(false);
                });

                // Listen for AI typing status changes
                Livewire.on('ai-typing-status', (status) => {
                    Alpine.store('chat').updateAiResponseStatus(status.isTyping);
                });
            });

            // Alpine store for global chat state
            document.addEventListener('alpine:init', () => {
                Alpine.store('chat', {
                    isAiResponseInProgress: false,
                    pageLoaded: false,

                    updateAiResponseStatus(status) {
                        this.isAiResponseInProgress = status;
                    },

                    setPageLoaded(status) {
                        this.pageLoaded = status;
                    }
                });
            });

            // Chat component logic
            function chatManager() {
                return {
                    isTyping: false,
                    typingTimer: null,
                    newMessage: '',
                    isSubmitting: false,
                    localMessages: [],
                    isAiResponseInProgress: false,
                    isPageLoaded: false,

                    // Specific initialization function
                    initChat() {
                        // Start with all states explicitly set to false
                        this.isTyping = false;
                        this.isAiResponseInProgress = false;
                        this.isSubmitting = false;

                        // Set page load status after a slight delay to ensure DOM is fully ready
                        setTimeout(() => {
                            this.isPageLoaded = true;
                            Alpine.store('chat').setPageLoaded(true);
                        }, 1000);

                        this.$watch('$store.chat.isAiResponseInProgress', (value) => {
                            this.isAiResponseInProgress = value;

                            if (!value) {
                                // When AI response finishes, set submitting status to false
                                this.isSubmitting = false;

                                // Clear local temporary messages after getting real response
                                this.localMessages = [];

                                // Scroll to bottom after small delay to ensure content is rendered
                                setTimeout(() => this.scrollToBottom(), 100);
                            }
                        });

                        // Force scroll down on page load
                        this.scrollToBottom();

                        // Set up scroll listener
                        const chatMessagesContainer = document.getElementById('chat-messages');
                        if (chatMessagesContainer) {
                            chatMessagesContainer.addEventListener('scroll', () => {
                                const scrollContainer = chatMessagesContainer;
                                const scrollButton = document.getElementById('scroll-to-bottom');

                                if (scrollButton) {
                                    // Show/hide scroll button based on scroll position
                                    if (scrollContainer.scrollHeight - scrollContainer.scrollTop - scrollContainer
                                        .clientHeight > 100) {
                                        scrollButton.classList.remove('d-none');
                                    } else {
                                        scrollButton.classList.add('d-none');
                                    }
                                }
                            });
                        }
                    },

                    // Handle user typing status and animation
                    handleTyping() {
                        if (!this.isPageLoaded) return;

                        this.isTyping = true;
                        this.$wire.userStartedTyping();

                        clearTimeout(this.typingTimer);
                        this.typingTimer = setTimeout(() => {
                            this.isTyping = false;
                            this.$wire.userStoppedTyping();
                        }, 1000);
                    },

                    // Send message with instant local feedback
                    sendChatMessage() {
                        // Check if there are pending files by looking at the DOM element
                        const pendingFilesContainer = document.querySelector('.file-preview-item');
                        const hasPendingFiles = pendingFilesContainer !== null;
                        const hasMessage = this.newMessage.trim();
                        
                        if ((!hasMessage && !hasPendingFiles) || this.isSubmitting || !this.isPageLoaded) return;

                        // Set submitting state
                        this.isSubmitting = true;

                        // Store message to send before clearing
                        const messageToSend = this.newMessage;

                        // Clear input field immediately
                        this.newMessage = '';

                        // Stop typing indicator
                        this.isTyping = false;
                        this.$wire.userStoppedTyping();

                        if (hasPendingFiles) {
                            // Show processing message for files
                            console.log('Processing files and message...');
                            
                            // Don't show local message yet - wait for server processing
                            // This ensures message appears WITH attachments
                            
                            // Don't start AI response yet - let file processing complete first
                            this.$wire.sendMessageWithFiles(messageToSend);
                        } else {
                            // Regular message - show immediately
                            const tempMessage = {
                                content: messageToSend,
                                time: new Date().toLocaleTimeString([], {
                                    hour: '2-digit',
                                    minute: '2-digit'
                                })
                            };

                            // Add to local messages array for instant display
                            this.localMessages.push(tempMessage);

                            // Scroll to show the new message
                            setTimeout(() => this.scrollToBottom(), 100);

                            // Set AI response in progress state
                            Alpine.store('chat').updateAiResponseStatus(true);

                            console.log('Sending regular message:', messageToSend);
                            this.$wire.sendMessage(messageToSend);
                        }
                    },

                    // Scroll to bottom of chat
                    scrollToBottom() {
                        const chatMessagesContainer = document.getElementById('chat-messages');
                        if (chatMessagesContainer) {
                            chatMessagesContainer.scrollTop = chatMessagesContainer.scrollHeight;
                        }
                    }
                }
            }
        </script>
    @endpush

    @push('styles')
        <style>
            /* Chat container styles */
            #chat-messages {
                scrollbar-width: thin;
                scrollbar-color: rgba(0, 0, 0, 0.2) transparent;
                scroll-behavior: smooth;
            }

            #chat-messages::-webkit-scrollbar {
                width: 8px;
            }

            #chat-messages::-webkit-scrollbar-track {
                background: #f1f1f1;
                border-radius: 4px;
            }

            #chat-messages::-webkit-scrollbar-thumb {
                background: #c1c1c1;
                border-radius: 4px;
                transition: background 0.2s ease;
            }

            #chat-messages::-webkit-scrollbar-thumb:hover {
                background: #a8a8a8;
            }

            /* Message styles with improved animation */
            .message-animation {
                animation: messageSlideIn 0.3s ease forwards;
                opacity: 0;
                transform: translateY(20px);
            }

            @keyframes messageSlideIn {
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            /* Typing indicator animation */
            .typing-indicator {
                display: flex;
                gap: 6px;
                padding: 6px 0;
            }

            .typing-indicator span {
                width: 8px;
                height: 8px;
                background: white;
                border-radius: 50%;
                display: inline-block;
                animation: pulse 1.4s infinite;
            }

            .typing-indicator span:nth-child(2) {
                animation-delay: 0.2s;
            }

            .typing-indicator span:nth-child(3) {
                animation-delay: 0.4s;
            }

            /* User typing indicator */
            .user-typing-indicator {
                display: flex;
                gap: 6px;
            }

            .user-typing-indicator span {
                width: 6px;
                height: 6px;
                background: #6c757d;
                border-radius: 50%;
                display: inline-block;
                animation: userTypingPulse 1.4s infinite;
            }

            .user-typing-indicator span:nth-child(2) {
                animation-delay: 0.2s;
            }

            .user-typing-indicator span:nth-child(3) {
                animation-delay: 0.4s;
            }

            @keyframes pulse {

                0%,
                100% {
                    transform: scale(1);
                    opacity: 0.7;
                }

                50% {
                    transform: scale(1.3);
                    opacity: 1;
                }
            }

            @keyframes userTypingPulse {

                0%,
                100% {
                    transform: scale(1);
                    opacity: 0.7;
                }

                50% {
                    transform: scale(1.3);
                    opacity: 1;
                }
            }

            /* Smooth transition for all interactive elements */
            .card,
            button,
            .form-control {
                transition: all 0.2s ease;
            }

            /* Scroll to bottom button */
            #scroll-to-bottom {
                position: absolute;
                bottom: 80px;
                right: 20px;
                width: 40px;
                height: 40px;
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10;
                transition: all 0.3s ease;
                opacity: 0.8;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
                animation: bounceIn 0.5s ease;
            }

            #scroll-to-bottom:hover {
                opacity: 1;
                transform: translateY(-2px);
            }

            @keyframes bounceIn {
                0% {
                    opacity: 0;
                    transform: scale(0.8);
                }

                50% {
                    opacity: 0.8;
                    transform: scale(1.1);
                }

                100% {
                    opacity: 1;
                    transform: scale(1);
                }
            }

            /* File staging area styles */
            .file-preview-item {
                animation: fadeInUp 0.3s ease forwards;
                opacity: 0;
                transform: translateY(10px);
            }

            @keyframes fadeInUp {
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .file-preview-item .card {
                transition: all 0.2s ease;
                cursor: pointer;
            }

            .file-preview-item .card:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
            }

            .file-icon {
                color: #6c757d;
                transition: color 0.2s ease;
            }

            .file-preview-item:hover .file-icon {
                color: #495057;
            }

            /* File type specific colors */
            .file-icon.text-primary { color: #0d6efd !important; }
            .file-icon.text-success { color: #198754 !important; }
            .file-icon.text-danger { color: #dc3545 !important; }
            .file-icon.text-warning { color: #fd7e14 !important; }
            .file-icon.text-info { color: #0dcaf0 !important; }
            .file-icon.text-purple { color: #6f42c1 !important; }

            /* File attachment in messages */
            .file-attachment .card {
                transition: all 0.2s ease;
                border: 1px solid #e9ecef;
                max-width: 300px;
            }

            .file-attachment .card:hover {
                border-color: #6c757d;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            .file-attachment .file-icon {
                min-width: 24px;
            }

            /* File processing indicator styles */
            .file-upload-progress {
                background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                border: 1px solid #dee2e6;
                border-radius: 0.375rem;
                padding: 1rem;
                animation: processingPulse 2s infinite;
            }

            .file-upload-progress .spinner-border-sm {
                width: 1rem;
                height: 1rem;
            }

            @keyframes processingPulse {
                0% { 
                    opacity: 1; 
                    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                }
                50% { 
                    opacity: 0.85; 
                    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
                }
                100% { 
                    opacity: 1; 
                    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                }
            }

            /* Markdown rendering styles */
            .rendered-markdown {
                line-height: 1.6;
            }

            .rendered-markdown h1, 
            .rendered-markdown h2, 
            .rendered-markdown h3, 
            .rendered-markdown h4, 
            .rendered-markdown h5, 
            .rendered-markdown h6 {
                margin-top: 1rem;
                margin-bottom: 0.5rem;
                font-weight: 600;
                line-height: 1.25;
            }

            .rendered-markdown h1 { font-size: 1.5rem; }
            .rendered-markdown h2 { font-size: 1.35rem; }
            .rendered-markdown h3 { font-size: 1.2rem; }
            .rendered-markdown h4 { font-size: 1.1rem; }
            .rendered-markdown h5 { font-size: 1rem; }
            .rendered-markdown h6 { font-size: 0.9rem; }

            .rendered-markdown p {
                margin-bottom: 0.75rem;
            }

            .rendered-markdown ul, 
            .rendered-markdown ol {
                margin-bottom: 0.75rem;
                padding-left: 1.5rem;
            }

            .rendered-markdown ul {
                list-style-type: disc;
            }

            .rendered-markdown ol {
                list-style-type: decimal;
            }

            .rendered-markdown li {
                margin-bottom: 0.25rem;
                display: list-item;
            }

            .rendered-markdown ul ul {
                list-style-type: circle;
                margin-top: 0.25rem;
            }

            .rendered-markdown ul ul ul {
                list-style-type: square;
            }

            .rendered-markdown ol ol {
                list-style-type: lower-alpha;
                margin-top: 0.25rem;
            }

            .rendered-markdown ol ol ol {
                list-style-type: lower-roman;
            }

            .rendered-markdown code {
                background-color: rgba(255, 255, 255, 0.15);
                padding: 0.125rem 0.25rem;
                border-radius: 0.25rem;
                font-size: 0.875em;
                font-family: 'Courier New', Courier, monospace;
            }

            .rendered-markdown pre {
                background-color: rgba(255, 255, 255, 0.1);
                padding: 0.75rem;
                border-radius: 0.375rem;
                margin: 0.75rem 0;
                overflow-x: auto;
            }

            .rendered-markdown pre code {
                background-color: transparent;
                padding: 0;
                border-radius: 0;
                font-size: 0.8rem;
            }

            .rendered-markdown blockquote {
                border-left: 4px solid rgba(255, 255, 255, 0.3);
                padding-left: 1rem;
                margin: 0.75rem 0;
                font-style: italic;
            }

            .rendered-markdown table {
                width: 100%;
                margin: 0.75rem 0;
                border-collapse: collapse;
            }

            .rendered-markdown table th,
            .rendered-markdown table td {
                border: 1px solid rgba(255, 255, 255, 0.2);
                padding: 0.375rem 0.75rem;
                text-align: left;
            }

            .rendered-markdown table th {
                background-color: rgba(255, 255, 255, 0.1);
                font-weight: 600;
            }

            .rendered-markdown strong {
                font-weight: 700;
            }

            .rendered-markdown em {
                font-style: italic;
            }

            .rendered-markdown a {
                color: rgba(255, 255, 255, 0.9);
                text-decoration: underline;
            }

            .rendered-markdown a:hover {
                color: rgba(255, 255, 255, 1);
            }

            /* User message markdown (darker background) */
            .card.bg-light .rendered-markdown code {
                background-color: rgba(0, 0, 0, 0.1);
            }

            .card.bg-light .rendered-markdown pre {
                background-color: rgba(0, 0, 0, 0.05);
            }

            .card.bg-light .rendered-markdown blockquote {
                border-left-color: #6c757d;
            }

            .card.bg-light .rendered-markdown table th,
            .card.bg-light .rendered-markdown table td {
                border-color: rgba(0, 0, 0, 0.125);
            }

            .card.bg-light .rendered-markdown table th {
                background-color: rgba(0, 0, 0, 0.05);
            }

            .card.bg-light .rendered-markdown a {
                color: #0d6efd;
            }

            .card.bg-light .rendered-markdown a:hover {
                color: #0a58ca;
            }

            /* Chat Session History Styles */
            .session-item {
                transition: all 0.2s ease;
            }

            .session-item:hover {
                background-color: #f8f9fa !important;
                border-color: #6c757d !important;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }

            .session-item.border-primary:hover {
                background-color: #e7f3ff !important;
            }

            .cursor-pointer {
                cursor: pointer;
            }

            .session-list {
                scrollbar-width: thin;
                scrollbar-color: #dee2e6 transparent;
            }

            .session-list::-webkit-scrollbar {
                width: 4px;
            }

            .session-list::-webkit-scrollbar-track {
                background: transparent;
            }

            .session-list::-webkit-scrollbar-thumb {
                background: #dee2e6;
                border-radius: 2px;
            }

            .session-list::-webkit-scrollbar-thumb:hover {
                background: #adb5bd;
            }

            /* Enhanced dropdown styling */
            .dropdown-toggle::after {
                display: none;
            }

            .session-item .dropdown-menu {
                box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
                border: 1px solid rgba(0, 0, 0, 0.1);
            }

            .session-item .dropdown-item {
                font-size: 0.875rem;
                padding: 0.5rem 1rem;
            }

            .session-item .dropdown-item:hover {
                background-color: #f8f9fa;
            }
        </style>
    @endpush
