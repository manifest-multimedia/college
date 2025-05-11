    <div class="row g-5" wire:init="markLoaded" x-data="chatManager()" x-init="initChat" x-cloak>
        <!-- Sessions List Column -->
        <div class="col-12 col-md-4 col-lg-3">
            <!-- Sessions Header -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between w-100">
                        <h3 class="card-title mb-0">AI Sensei</h3>
                        <button class="btn btn-sm btn-primary" wire:click="startNewChat">
                            <i class="bi bi-plus-lg"></i> New Chat
                        </button>
                    </div>
                </div>    </div>

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
                        and assist with research. 
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
                        </div>

                    </div>
                    {{-- Tools in Development --}}
                    <div class="mt-3">
                        <h6>Tools in Development</h6>
                        <div class="d-flex flex-wrap gap-1">
                            <span class="badge bg-secondary rounded-pill">Document Processing</span>
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
                                                        <p class="mb-0">{!! nl2br(e($contentItem['text'])) !!}</p>
                                                    @elseif($contentItem['type'] === 'image')
                                                        <div class="image-attachment mb-2">
                                                            <img src="{{ $contentItem['file_url'] ?? '#' }}"
                                                                class="img-fluid rounded" alt="Image Attachment">
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

                    <!-- Message Input -->
                    <div class="mt-auto">
                        <form @submit.prevent="sendChatMessage" class="mt-3">
                            <div class="input-group mb-2">
                                <!-- File Upload Button -->
                                <button class="btn btn-outline-secondary " type="button"
                                    onclick="document.getElementById('file-upload').click()" 

                                    style="border: 1px solid #ced4da;">
                                    
                                    <i class="bi bi-paperclip" style="font-size:25px"></i>
                                </button>

                                <textarea class="form-control" id="message-input" x-model="newMessage" rows="2"
                                    placeholder="Message AI Sensei..." @input="handleTyping" @keydown.enter.prevent="sendChatMessage"
                                    :disabled="isSubmitting"></textarea>

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

                            <!-- File upload status -->
                            @if ($uploadingFile)
                                <div class="progress mt-2">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated"
                                        role="progressbar" style="width: 100%">
                                        Uploading file...
                                    </div>
                                </div>
                            @endif

                            <!-- Show temporary upload files -->
                            @if (!empty($temporaryUploads))
                                <div class="mt-2">
                                    @foreach ($temporaryUploads as $file)
                                        <div class="alert alert-info d-flex align-items-center mb-2">
                                            <i class="bi bi-file-earmark me-2"></i>
                                            <div class="flex-grow-1 text-truncate">
                                                {{ $file->getClientOriginalName() }}</div>
                                            <button type="button" class="btn-close btn-sm"
                                                wire:click="$set('temporaryUploads', [])"></button>
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
                        if (!this.newMessage.trim() || this.isSubmitting || !this.isPageLoaded) return;

                        // Set submitting state
                        this.isSubmitting = true;

                        // Create temporary local message
                        const tempMessage = {
                            content: this.newMessage,
                            time: new Date().toLocaleTimeString([], {
                                hour: '2-digit',
                                minute: '2-digit'
                            })
                        };

                        // Add to local messages array for instant display
                        this.localMessages.push(tempMessage);

                        // Store message to send
                        const messageToSend = this.newMessage;

                        // Clear input field
                        this.newMessage = '';

                        // Stop typing indicator
                        this.isTyping = false;
                        this.$wire.userStoppedTyping();

                        // Scroll to show the new message
                        setTimeout(() => this.scrollToBottom(), 100);

                        // Set AI response in progress state
                        Alpine.store('chat').updateAiResponseStatus(true);

                        // Send to server
                        this.$wire.sendMessage(messageToSend);
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
        </style>
    @endpush
