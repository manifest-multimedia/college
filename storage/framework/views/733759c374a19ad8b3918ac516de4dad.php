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
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('success')): ?>
                        <div class="alert alert-success">
                            <?php echo e(session('success')); ?>

                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('error')): ?>
                        <div class="alert alert-danger">
                            <?php echo e(session('error')); ?>

                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    
                    <!-- Tabs for Active and Archived Sessions -->
                    <ul class="nav nav-tabs mb-4" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?php echo e($activeTab === 'active' ? 'active' : ''); ?>" 
                                    wire:click="changeTab('active')" type="button">
                                <i class="bi bi-chat-left-text"></i> Active
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?php echo e($activeTab === 'archived' ? 'active' : ''); ?>" 
                                    wire:click="changeTab('archived')" type="button">
                                <i class="bi bi-archive"></i> Archived
                            </button>
                        </li>
                    </ul>
                    
                    <div class="d-flex justify-content-between mb-4">
                        <h5><?php echo e($activeTab === 'active' ? 'Recent Chats' : 'Archived Chats'); ?></h5>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'active'): ?>
                        <button class="btn btn-sm btn-primary" wire:click="toggleNewSessionForm">
                            <i class="bi bi-plus-circle"></i> New Chat
                        </button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isCreatingSession && $activeTab === 'active'): ?>
                        <div class="mb-4">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Chat title" wire:model="newSessionTitle">
                                <button class="btn btn-success" type="button" wire:click="createNewSession">Create</button>
                                <button class="btn btn-secondary" type="button" wire:click="toggleNewSessionForm">Cancel</button>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div class="list-group">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'active'): ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?php echo e($selectedSessionId === $session['session_id'] ? 'active' : ''); ?>"
                                    wire:click="selectSession('<?php echo e($session['session_id']); ?>')">
                                    <div>
                                        <h6 class="mb-1"><?php echo e($session['title']); ?></h6>
                                        <small><?php echo e(\Carbon\Carbon::parse($session['last_activity_at'])->diffForHumans()); ?></small>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-sm <?php echo e($selectedSessionId === $session['session_id'] ? 'btn-light' : 'btn-outline-secondary'); ?>" 
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                                onclick="event.stopPropagation();">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" wire:click.prevent="archiveSession('<?php echo e($session['session_id']); ?>')">
                                                <i class="bi bi-archive"></i> Archive
                                            </a></li>
                                            <li><a class="dropdown-item text-danger" href="#" wire:click.prevent="deleteSession('<?php echo e($session['session_id']); ?>')">
                                                <i class="bi bi-trash"></i> Delete
                                            </a></li>
                                        </ul>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <div class="text-center p-4">
                                    <p class="text-muted">No active chat sessions</p>
                                    <button class="btn btn-primary" wire:click="toggleNewSessionForm">Start a new conversation</button>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php else: ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $archivedSessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center <?php echo e($selectedSessionId === $session['session_id'] ? 'active' : ''); ?>"
                                    wire:click="selectSession('<?php echo e($session['session_id']); ?>')">
                                    <div>
                                        <h6 class="mb-1"><?php echo e($session['title']); ?></h6>
                                        <small><?php echo e(\Carbon\Carbon::parse($session['last_activity_at'])->diffForHumans()); ?></small>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-sm <?php echo e($selectedSessionId === $session['session_id'] ? 'btn-light' : 'btn-outline-secondary'); ?>" 
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                                onclick="event.stopPropagation();">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" wire:click.prevent="restoreSession('<?php echo e($session['session_id']); ?>')">
                                                <i class="bi bi-arrow-counterclockwise"></i> Restore
                                            </a></li>
                                            <li><a class="dropdown-item text-danger" href="#" wire:click.prevent="deleteSession('<?php echo e($session['session_id']); ?>')">
                                                <i class="bi bi-trash"></i> Delete
                                            </a></li>
                                        </ul>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <div class="text-center p-4">
                                    <p class="text-muted">No archived chat sessions</p>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
                            <?php echo e($selectedSession ? $selectedSession['title'] : 'Select a conversation'); ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedSession && $selectedSession['status'] === 'archived'): ?>
                                <span class="badge bg-secondary ms-2">Archived</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </h3>
                    </div>
                </div>
                <div class="card-body d-flex flex-column" style="height: 600px;">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedSessionId): ?>
                        <!-- Messages Area -->
                        <div class="flex-grow-1 overflow-auto mb-4" id="chat-messages" style="height: 500px;">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($messages) > 0): ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chatMessage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="mb-4 d-flex <?php echo e($chatMessage['type'] === 'user' ? 'justify-content-end' : 'justify-content-start'); ?>">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($chatMessage['type'] === 'ai'): ?>
                                        <div class="ai-avatar me-2">
                                            <img src="<?php echo e(asset('images/ai-sensei.png')); ?>" alt="AI Sensei" class="rounded-circle" width="40" height="40">
                                        </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        
                                        <div class="card <?php echo e($chatMessage['type'] === 'user' ? 'bg-light border-0 shadow-sm' : 'bg-primary text-white border-0 shadow'); ?>" 
                                             style="max-width: 80%; border-radius: 18px;">
                                            <div class="card-body py-2 px-3">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($chatMessage['is_document']) && $chatMessage['is_document']): ?>
                                                    <div class="document-container">
                                                        <div class="mb-2 d-flex align-items-center">
                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($chatMessage['mime_type']) && str_contains($chatMessage['mime_type'] ?? '', 'image')): ?>
                                                                <img src="<?php echo e(route('chat.document.preview', ['path' => $chatMessage['file_path']])); ?>" 
                                                                    alt="<?php echo e($chatMessage['file_name']); ?>" class="img-fluid mb-2 rounded" style="max-height: 200px;">
                                                            <?php else: ?>
                                                                <i class="bi bi-file-earmark-fill me-2" style="font-size: 2rem;"></i>
                                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                            <div>
                                                                <p class="mb-0"><?php echo e($chatMessage['file_name']); ?></p>
                                                                <button class="btn btn-sm <?php echo e($chatMessage['type'] === 'user' ? 'btn-outline-primary' : 'btn-light'); ?>" 
                                                                        onclick="downloadDocument('<?php echo e($chatMessage['file_path']); ?>')">
                                                                    <i class="bi bi-download"></i> Download
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <p class="mb-0"><?php echo nl2br(e($chatMessage['message'])); ?></p>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                
                                                <div class="text-end">
                                                    <small class="<?php echo e($chatMessage['type'] === 'user' ? 'text-muted' : 'text-white-50'); ?>">
                                                        <?php echo e(\Carbon\Carbon::parse($chatMessage['timestamp'])->format('h:i A')); ?>

                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($chatMessage['type'] === 'user'): ?>
                                        <div class="user-avatar ms-2">
                                            <div class="rounded-circle bg-info d-flex justify-content-center align-items-center text-white" style="width: 40px; height: 40px;">
                                                <?php echo e(strtoupper(substr(auth()->user()->name ?? 'U', 0, 1))); ?>

                                            </div>
                                        </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($messageOffset > 0): ?>
                                    <div class="text-center mb-3">
                                        <button class="btn btn-sm btn-secondary" wire:click="loadMoreMessages">
                                            Load previous messages
                                        </button>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php else: ?>
                                <div class="text-center p-4">
                                    <p class="text-muted">No messages yet</p>
                                    <p>Start the conversation with AI Sensei by sending a message below!</p>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            
                            <!-- Typing indicators -->
                            <div id="user-typing-indicator" class="mb-4 d-flex justify-content-start" style="display: none !important;">
                                <div class="ai-avatar me-2">
                                    <img src="<?php echo e(asset('images/ai-sensei.png')); ?>" alt="AI Sensei" class="rounded-circle" width="40" height="40">
                                </div>
                                <div class="card bg-light" style="max-width: 80%; border-radius: 15px;">
                                    <div class="card-body py-2 px-3">
                                        <p class="mb-0">
                                            <span class="typing-text">User is typing</span>
                                            <span class="typing-dots">...</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="ai-typing-indicator" class="mb-4 d-flex justify-content-start" style="display: none !important;">
                                <div class="ai-avatar me-2">
                                    <img src="<?php echo e(asset('images/ai-sensei.png')); ?>" alt="AI Sensei" class="rounded-circle" width="40" height="40">
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
                        </div>
                        
                        <!-- Message Input - Only show if session is active -->
                        <div class="mt-auto">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedSession && $selectedSession['status'] === 'archived'): ?>
                                <div class="alert alert-info mb-3">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <i class="bi bi-info-circle-fill me-2"></i>
                                            This chat is archived. Restore it to continue the conversation.
                                        </div>
                                        <button class="btn btn-sm btn-outline-primary" wire:click="restoreSession('<?php echo e($selectedSessionId); ?>')">
                                            <i class="bi bi-arrow-counterclockwise"></i> Restore Chat
                                        </button>
                                    </div>
                                </div>
                            <?php else: ?>
                                <form wire:submit.prevent="sendMessage">
                                    <div class="input-group mb-3">
                                        <label class="btn btn-outline-secondary" for="file-upload">
                                            <i class="bi bi-paperclip"></i>
                                        </label>
                                        <input id="file-upload" type="file" wire:model.live="document" class="d-none" 
                                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.txt,.csv,.xls,.xlsx">
                                        <textarea class="form-control" rows="2" wire:model="message" id="message-input"
                                                  placeholder="Message AI Sensei..." 
                                                  wire:keydown="handleTyping"
                                                  wire:keydown.enter="sendMessage"></textarea>
                                        <button class="btn btn-primary" type="submit" id="send-button">
                                            <i class="bi bi-send-fill"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- File preview -->
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($document): ?>
                                    <div class="document-preview alert alert-light d-flex align-items-center justify-content-between">
                                        <div>
                                            <i class="bi bi-file-earmark"></i> <?php echo e($document->getClientOriginalName()); ?>

                                        </div>
                                        <button type="button" class="btn-close" wire:click="removeDocument"></button>
                                    </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['document'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                                    <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </form>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            
                            <div class="text-center text-muted small mt-3">
                                <p>AI Sensei is powered by <a href="https://manifestghana.com" target="_blank">Manifest Digital</a></p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="d-flex align-items-center justify-content-center flex-column h-100">
                            <img src="<?php echo e(asset('images/ai-sensei.png')); ?>" alt="AI Sensei" class="mb-4" width="120" height="120">
                            <div class="text-center">
                                <h4>Welcome to AI Sensei</h4>
                                <p class="text-muted">Select a conversation from the left panel or create a new one to begin chatting.</p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'active'): ?>
                                    <button class="btn btn-primary" wire:click="toggleNewSessionForm">Start a new conversation</button>
                                <?php else: ?>
                                    <button class="btn btn-outline-secondary" wire:click="changeTab('active')">
                                        <i class="bi bi-chat-left-text"></i> View active conversations
                                    </button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
        let typingTimeout;
        let sessionId;
        let isUserTyping = false;
        let shouldScroll = true;
        
        document.addEventListener('livewire:initialized', function () {
            // Scrolling functionality
            function scrollToBottom() {
                const chatMessages = document.getElementById('chat-messages');
                if (chatMessages && shouldScroll) {
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
            }
            
            // Scroll to bottom after messages are loaded or updated
            scrollToBottom();
            
            // Listen for Livewire updates and react to message updates
            Livewire.hook('message.processed', (message, component) => {
                if (component.name === 'communication.chat') {
                    // Force scroll to bottom when a new message is sent or received
                    if (message.updateQueue['messages']) {
                        // Set shouldScroll to true when we send a new message
                        shouldScroll = true;
                        setTimeout(scrollToBottom, 100); // Small delay to ensure DOM is updated
                    }
                    
                    if (message.updateQueue['selectedSessionId']) {
                        shouldScroll = true;
                        setTimeout(scrollToBottom, 100);
                    }
                }
            });
            
            // Get message input for typing events
            const messageInput = document.getElementById('message-input');
            
            if (messageInput) {
                messageInput.addEventListener('input', function() {
                    const chatComponent = Livewire.get('communication.chat');
                    sessionId = chatComponent.selectedSessionId;
                    
                    if (!sessionId) return;
                    
                    // Handle typing status
                    clearTimeout(typingTimeout);
                    
                    if (!isUserTyping) {
                        isUserTyping = true;
                        sendTypingStatus(true);
                    }
                    
                    typingTimeout = setTimeout(() => {
                        isUserTyping = false;
                        sendTypingStatus(false);
                    }, 3000);
                });
            }
            
            // Check if user is at bottom of the chat
            const chatMessagesContainer = document.getElementById('chat-messages');
            if (chatMessagesContainer) {
                // Detect scrolling to determine whether auto-scroll should be enabled
                chatMessagesContainer.addEventListener('scroll', function() {
                    const isAtBottom = chatMessagesContainer.scrollHeight - chatMessagesContainer.scrollTop - chatMessagesContainer.clientHeight < 50;
                    shouldScroll = isAtBottom;
                });
                
                // Detect when the user initiates a scroll
                chatMessagesContainer.addEventListener('wheel', function() {
                    const isAtBottom = chatMessagesContainer.scrollHeight - chatMessagesContainer.scrollTop - chatMessagesContainer.clientHeight < 50;
                    if (!isAtBottom) {
                        shouldScroll = false;
                    }
                });
                
                // Add a scroll-to-bottom button
                const scrollButton = document.createElement('button');
                scrollButton.id = 'scroll-to-bottom';
                scrollButton.innerHTML = '<i class="bi bi-arrow-down"></i>';
                scrollButton.style.display = 'none';
                chatMessagesContainer.appendChild(scrollButton);
                
                // Show/hide scroll button based on scroll position
                chatMessagesContainer.addEventListener('scroll', function() {
                    const isAtBottom = chatMessagesContainer.scrollHeight - chatMessagesContainer.scrollTop - chatMessagesContainer.clientHeight < 50;
                    scrollButton.style.display = isAtBottom ? 'none' : 'flex';
                });
                
                // Scroll to bottom when button is clicked
                scrollButton.addEventListener('click', function() {
                    shouldScroll = true;
                    scrollToBottom();
                });
            }
            
            // Function to send typing status to the server
            function sendTypingStatus(isTyping) {
                fetch('/api/communication/chat/typing', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        session_id: sessionId,
                        typing: isTyping
                    })
                });
            }
        });
        
        // Document download functionality
        function downloadDocument(filePath) {
            fetch('/api/communication/chat/document/download-url', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    path: filePath
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Create temporary link and trigger download
                    const a = document.createElement('a');
                    a.href = data.url;
                    a.download = filePath.split('/').pop();
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                } else {
                    alert('Failed to download file: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error downloading document:', error);
                alert('Error downloading document');
            });
        }
        
        // Initialize Echo for real-time communication
        document.addEventListener('livewire:initialized', function () {
            if (window.Echo) {
                const chatComponent = Livewire.get('communication.chat');
                
                // Update when session ID changes
                Livewire.on('sessionSelected', (sessionId) => {
                    // Remove any previous listeners
                    if (window.Echo.connector.channels[`private-chat.${sessionId}`]) {
                        window.Echo.leave(`private-chat.${sessionId}`);
                    }
                    
                    // Listen for real-time events on this channel
                    window.Echo.private(`chat.${sessionId}`)
                        .listen('.new.message', (e) => {
                            chatComponent.addMessage(e.message);
                            shouldScroll = true; // Force scroll on new message
                            setTimeout(scrollToBottom, 100);
                        })
                        .listen('.document.uploaded', (e) => {
                            chatComponent.addMessage(e.message);
                            shouldScroll = true; // Force scroll on new document
                            setTimeout(scrollToBottom, 100);
                        })
                        .listen('.user.typing', (e) => {
                            const typingIndicator = document.getElementById('user-typing-indicator');
                            if (e.typing) {
                                typingIndicator.style.display = 'flex';
                            } else {
                                typingIndicator.style.display = 'none';
                            }
                        })
                        .listen('.ai.typing', (e) => {
                            const typingIndicator = document.getElementById('ai-typing-indicator');
                            if (e.typing) {
                                typingIndicator.style.display = 'flex';
                            } else {
                                typingIndicator.style.display = 'none';
                            }
                        });
                });
                
                // Initialize for current session if exists
                if (chatComponent.selectedSessionId) {
                    Livewire.dispatch('sessionSelected', chatComponent.selectedSessionId);
                }
            }
        });
        
        // Animation for typing dots
        document.addEventListener('DOMContentLoaded', function() {
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
        });
        
        // Manual scroll function for the chat window
        function scrollToBottom() {
            const chatMessages = document.getElementById('chat-messages');
            if (chatMessages && shouldScroll) {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        }
        
        // Reset auto-scroll when sending a new message
        document.getElementById('send-button')?.addEventListener('click', function() {
            shouldScroll = true;
        });
        
        // Reset auto-scroll when pressing Enter to send a message
        document.getElementById('message-input')?.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                shouldScroll = true;
                setTimeout(scrollToBottom, 100); // Scroll after message is sent
            }
        });
    </script>
    <?php $__env->stopPush(); ?>
    <?php $__env->startPush('styles'); ?>
        

    <style>
        /* Typing animation */
        .typing-dots {
            display: inline-block;
            width: 20px;
        }
        
        /* Smoothly scroll chat to bottom */
        #chat-messages {
            scroll-behavior: smooth;
            overflow-y: auto;
            position: relative;
        }
        
        /* Document preview styles */
        .document-container {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 10px;
        }
        
        /* Message bubble improvements */
        .card {
            transition: all 0.2s ease;
        }
        
        .card:hover {
            transform: translateY(-2px);
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
            background: rgba(0,0,0,0.5);
            color: white;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 10;
            transition: opacity 0.3s;
            border: none;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        #scroll-to-bottom:hover {
            background: rgba(0,0,0,0.7);
        }
    </style>
    <?php $__env->stopPush(); ?>
</div>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/livewire/communication/chat.blade.php ENDPATH**/ ?>