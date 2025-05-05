<div>
    <div class="row">
        <!-- Chat Sessions List -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <h3 class="card-title">
                            <i class="ki-duotone ki-message-circle-2 fs-1 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                            Conversations
                        </h3>
                    </div>
                </div>
                <div class="card-body">
                    @if (session()->has('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session()->has('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="d-flex justify-content-between mb-4">
                        <h5>Recent Chats</h5>
                        <button class="btn btn-sm btn-primary" wire:click="toggleNewSessionForm">
                            <i class="bi bi-plus-circle"></i> New Chat
                        </button>
                    </div>

                    @if ($isCreatingSession)
                        <div class="mb-4">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Chat title" wire:model="newSessionTitle">
                                <button class="btn btn-success" type="button" wire:click="createNewSession">Create</button>
                                <button class="btn btn-secondary" type="button" wire:click="toggleNewSessionForm">Cancel</button>
                            </div>
                        </div>
                    @endif

                    <div class="list-group">
                        @forelse ($sessions as $session)
                            <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ $selectedSessionId === $session['session_id'] ? 'active' : '' }}"
                                wire:click="selectSession('{{ $session['session_id'] }}')">
                                <div>
                                    <h6 class="mb-1">{{ $session['title'] }}</h6>
                                    <small>{{ \Carbon\Carbon::parse($session['last_activity_at'])->diffForHumans() }}</small>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm {{ $selectedSessionId === $session['session_id'] ? 'btn-light' : 'btn-outline-secondary' }}" 
                                            type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                            onclick="event.stopPropagation();">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" wire:click.prevent="archiveSession('{{ $session['session_id'] }}')">Archive</a></li>
                                        <li><a class="dropdown-item text-danger" href="#" wire:click.prevent="deleteSession('{{ $session['session_id'] }}')">Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                        @empty
                            <div class="text-center p-4">
                                <p class="text-muted">No chat sessions yet</p>
                                <button class="btn btn-primary" wire:click="toggleNewSessionForm">Start a new conversation</button>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Messages Area -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <h3 class="card-title">
                            <i class="ki-duotone ki-message-text-2 fs-1 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            {{ $selectedSession ? $selectedSession['title'] : 'Select a conversation' }}
                        </h3>
                    </div>
                </div>
                <div class="card-body d-flex flex-column" style="height: 600px;">
                    @if ($selectedSessionId)
                        <!-- Messages Area -->
                        <div class="flex-grow-1 overflow-auto mb-4" id="chat-messages" style="height: 500px;">
                            @if (count($messages) > 0)
                                @if ($messageOffset > 0)
                                    <div class="text-center mb-3">
                                        <button class="btn btn-sm btn-secondary" wire:click="loadMoreMessages">
                                            Load previous messages
                                        </button>
                                    </div>
                                @endif
                                
                                @foreach ($messages as $chatMessage)
                                    <div class="mb-4 d-flex {{ $chatMessage['type'] === 'user' ? 'justify-content-end' : 'justify-content-start' }}">
                                        <div class="card {{ $chatMessage['type'] === 'user' ? 'bg-light' : 'bg-primary text-white' }}" 
                                             style="max-width: 80%; border-radius: 15px;">
                                            <div class="card-body py-2 px-3">
                                                <p class="mb-0">{!! nl2br(e($chatMessage['message'])) !!}</p>
                                                <div class="text-end">
                                                    <small class="{{ $chatMessage['type'] === 'user' ? 'text-muted' : 'text-white-50' }}">
                                                        {{ \Carbon\Carbon::parse($chatMessage['timestamp'])->format('h:i A') }}
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center p-4">
                                    <p class="text-muted">No messages yet</p>
                                    <p>Start the conversation by sending a message below!</p>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Message Input -->
                        <div class="mt-auto">
                            <form wire:submit.prevent="sendMessage">
                                <div class="input-group">
                                    <textarea class="form-control" rows="2" wire:model="message" placeholder="Type your message here..." 
                                              wire:keydown.enter="sendMessage"></textarea>
                                    <button class="btn btn-primary" type="submit">
                                        <i class="bi bi-send"></i> Send
                                    </button>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class="d-flex align-items-center justify-content-center h-100">
                            <div class="text-center">
                                <h4>Select a conversation or start a new one</h4>
                                <p class="text-muted">Click on a conversation from the left panel or create a new one to begin chatting.</p>
                                <button class="btn btn-primary" wire:click="toggleNewSessionForm">Start a new conversation</button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('livewire:initialized', function () {
            function scrollToBottom() {
                const chatMessages = document.getElementById('chat-messages');
                if (chatMessages) {
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
            }
            
            // Scroll to bottom after messages are loaded or updated
            scrollToBottom();
            
            Livewire.hook('message.processed', (message, component) => {
                if (component.name === 'communication.chat' && (message.updateQueue['messages'] || message.updateQueue['selectedSessionId'])) {
                    scrollToBottom();
                }
            });
        });
    </script>
    @endpush
</div>
