<div>
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <h3 class="card-title">
                    <i class="ki-duotone ki-document fs-1 me-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    Memo: {{ $memo->reference_number }}
                </h3>
            </div>
            <div class="card-toolbar">
                <a href="{{ route('memos') }}" class="btn btn-sm btn-light me-2">
                    <i class="ki-duotone ki-arrow-left fs-3"></i>
                    Back to List
                </a>
            </div>
        </div>
        
        <div class="card-body">
            <!-- Status Messages -->
            @if (session()->has('success'))
                <div class="alert alert-success mb-5">
                    {{ session('success') }}
                </div>
            @endif
            
            @if (session()->has('error'))
                <div class="alert alert-danger mb-5">
                    {{ session('error') }}
                </div>
            @endif
            
            <!-- Memo Header Section -->
            <div class="d-flex flex-column flex-lg-row mb-7">
                <!-- Left Column - Memo Details -->
                <div class="flex-column flex-lg-row-auto w-100 w-lg-300px w-xl-350px mb-10 mb-lg-0">
                    <div class="card card-flush">
                        <div class="card-header">
                            <h3 class="card-title text-gray-800 fw-bold">Memo Details</h3>
                        </div>
                        <div class="card-body pt-0">
                            <div class="fw-bold fs-5 text-gray-800 mb-2">{{ $memo->title }}</div>
                            
                            <div class="d-flex flex-column text-gray-600 gap-1 mt-6">
                                <div class="d-flex">
                                    <span class="text-gray-700 fw-bold me-1">Status:</span>
                                    @php
                                        $statusClass = [
                                            'draft' => 'badge-light-dark',
                                            'pending' => 'badge-light-warning',
                                            'forwarded' => 'badge-light-info',
                                            'approved' => 'badge-light-success',
                                            'rejected' => 'badge-light-danger',
                                            'completed' => 'badge-light-primary',
                                        ][$memo->status] ?? 'badge-light';
                                    @endphp
                                    <span class="badge {{ $statusClass }}">{{ ucfirst($memo->status) }}</span>
                                </div>
                                
                                <div class="d-flex">
                                    <span class="text-gray-700 fw-bold me-1">Priority:</span>
                                    @php
                                        $priorityClass = [
                                            'low' => 'badge-light-success',
                                            'medium' => 'badge-light-warning',
                                            'high' => 'badge-light-danger',
                                        ][$memo->priority] ?? 'badge-light';
                                    @endphp
                                    <span class="badge {{ $priorityClass }}">{{ ucfirst($memo->priority) }}</span>
                                </div>
                                
                                <div class="d-flex flex-column mt-3">
                                    <span class="text-gray-700 fw-bold mb-1">From:</span>
                                    <span class="text-gray-600">{{ $memo->user->name ?? 'N/A' }}</span>
                                    @if($memo->department)
                                        <span class="text-gray-500">{{ $memo->department->name }}</span>
                                    @endif
                                </div>
                                
                                <div class="d-flex flex-column mt-3">
                                    <span class="text-gray-700 fw-bold mb-1">To:</span>
                                    @if($memo->recipient)
                                        <span class="text-gray-600">{{ $memo->recipient->name }}</span>
                                    @elseif($memo->recipientDepartment)
                                        <span class="text-gray-600">{{ $memo->recipientDepartment->name }} Department</span>
                                    @else
                                        <span class="text-gray-500">Not specified</span>
                                    @endif
                                </div>
                                
                                @if($memo->requested_action)
                                    <div class="d-flex flex-column mt-3">
                                        <span class="text-gray-700 fw-bold mb-1">Requested Action:</span>
                                        <span class="text-gray-600">{{ $memo->requested_action }}</span>
                                    </div>
                                @endif
                                
                                <div class="d-flex flex-column mt-3">
                                    <span class="text-gray-700 fw-bold mb-1">Created:</span>
                                    <span class="text-gray-600">{{ $memo->created_at->format('M d, Y \a\t h:i A') }}</span>
                                </div>
                                
                                <div class="d-flex flex-column mt-3">
                                    <span class="text-gray-700 fw-bold mb-1">Last Updated:</span>
                                    <span class="text-gray-600">{{ $memo->updated_at->format('M d, Y \a\t h:i A') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column - Content and Tabs -->
                <div class="flex-lg-row-fluid ms-lg-7 ms-xl-10">
                    <!-- Memo Content -->
                    <div class="card card-flush mb-6">
                        <div class="card-header">
                            <h3 class="card-title text-gray-800 fw-bold">Memo Content</h3>
                        </div>
                        <div class="card-body">
                            <div class="text-gray-700">{{ $memo->description }}</div>
                        </div>
                    </div>
                    
                    <!-- Attachments and History Tabs -->
                    <div class="card card-flush">
                        <div class="card-header card-header-stretch">
                            <div class="card-title">
                                <h3 class="m-0 text-gray-800">Memo Information</h3>
                            </div>
                            <div class="card-toolbar">
                                <ul class="nav nav-stretch nav-line-tabs border-transparent fs-5 fw-bold" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link text-hover-primary {{ $currentTab === 'attachments' ? 'active' : '' }}" 
                                           wire:click="setActiveTab('attachments')"
                                           role="tab">Attachments</a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link text-hover-primary {{ $currentTab === 'history' ? 'active' : '' }}" 
                                           wire:click="setActiveTab('history')"
                                           role="tab">History</a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link text-hover-primary {{ $currentTab === 'actions' ? 'active' : '' }}" 
                                           wire:click="setActiveTab('actions')"
                                           role="tab">Actions</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <div class="tab-content">
                                <!-- Attachments Tab -->
                                <div class="tab-pane fade {{ $currentTab === 'attachments' ? 'show active' : '' }}" id="attachments" role="tabpanel">
                                    @if($memo->attachments->count() > 0)
                                        <div class="d-flex flex-column gap-5">
                                            @foreach($memo->attachments as $attachment)
                                                <div class="d-flex justify-content-between align-items-center border-bottom pb-5">
                                                    <div class="d-flex align-items-center">
                                                        @if($attachment->is_image)
                                                            <div class="symbol symbol-50px me-3">
                                                                <img src="{{ $attachment->url }}" alt="{{ $attachment->original_filename }}">
                                                            </div>
                                                        @elseif($attachment->is_pdf)
                                                            <div class="symbol symbol-50px me-3">
                                                                <i class="ki-duotone ki-file-pdf fs-2x text-danger">
                                                                    <span class="path1"></span>
                                                                    <span class="path2"></span>
                                                                </i>
                                                            </div>
                                                        @else
                                                            <div class="symbol symbol-50px me-3">
                                                                <i class="ki-duotone ki-file fs-2x text-primary">
                                                                    <span class="path1"></span>
                                                                    <span class="path2"></span>
                                                                </i>
                                                            </div>
                                                        @endif
                                                        
                                                        <div class="ms-2">
                                                            <a href="{{ $attachment->url }}" target="_blank" class="fs-5 fw-bold text-gray-900 text-hover-primary">{{ $attachment->original_filename }}</a>
                                                            <div class="fs-7 text-muted">
                                                                Size: {{ $attachment->formatted_size }} | Uploaded by: {{ $attachment->user->name ?? 'Unknown' }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div>
                                                        <a href="{{ $attachment->url }}" target="_blank" class="btn btn-icon btn-sm btn-light-primary me-1" title="View">
                                                            <i class="ki-duotone ki-eye fs-2">
                                                                <span class="path1"></span>
                                                                <span class="path2"></span>
                                                                <span class="path3"></span>
                                                            </i>
                                                        </a>
                                                        
                                                        <a href="{{ $attachment->url }}" download class="btn btn-icon btn-sm btn-light-success me-1" title="Download">
                                                            <i class="ki-duotone ki-cloud-download fs-2">
                                                                <span class="path1"></span>
                                                                <span class="path2"></span>
                                                            </i>
                                                        </a>
                                                        
                                                        @if(auth()->id() === $attachment->user_id || auth()->id() === $memo->user_id)
                                                            <button wire:click="deleteAttachment({{ $attachment->id }})" 
                                                                    class="btn btn-icon btn-sm btn-light-danger" 
                                                                    title="Delete"
                                                                    onclick="return confirm('Are you sure you want to delete this attachment?')">
                                                                <i class="ki-duotone ki-trash fs-2">
                                                                    <span class="path1"></span>
                                                                    <span class="path2"></span>
                                                                    <span class="path3"></span>
                                                                    <span class="path4"></span>
                                                                    <span class="path5"></span>
                                                                </i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-center py-10">
                                            <i class="ki-duotone ki-document-missing fs-3x text-muted mb-5">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                            <div class="text-muted fw-semibold fs-5">No attachments found for this memo.</div>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- History Tab -->
                                <div class="tab-pane fade {{ $currentTab === 'history' ? 'show active' : '' }}" id="history" role="tabpanel">
                                    @if($memo->actions->count() > 0)
                                        <div class="timeline">
                                            @foreach($memo->actions as $action)
                                                <div class="timeline-item">
                                                    <div class="timeline-line w-40px"></div>
                                                    
                                                    <div class="timeline-icon symbol symbol-circle symbol-40px">
                                                        @php
                                                            $iconClass = match($action->action_type) {
                                                                'created' => 'ki-duotone ki-document text-success',
                                                                'viewed' => 'ki-duotone ki-eye text-info',
                                                                'forwarded' => 'ki-duotone ki-arrow-right text-warning',
                                                                'approved' => 'ki-duotone ki-check text-success',
                                                                'rejected' => 'ki-duotone ki-cross text-danger',
                                                                'commented' => 'ki-duotone ki-message-text text-primary',
                                                                'completed' => 'ki-duotone ki-check-circle text-success',
                                                                'procured' => 'ki-duotone ki-basket text-primary',
                                                                'delivered' => 'ki-duotone ki-delivery text-info',
                                                                'audited' => 'ki-duotone ki-clipboard-check text-warning',
                                                                default => 'ki-duotone ki-question text-muted'
                                                            };
                                                        @endphp
                                                        
                                                        <i class="{{ $iconClass }} fs-1">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                            @if(strpos($iconClass, 'check-circle') !== false || strpos($iconClass, 'clipboard-check') !== false)
                                                                <span class="path3"></span>
                                                                <span class="path4"></span>
                                                            @endif
                                                        </i>
                                                    </div>
                                                    
                                                    <div class="timeline-content mb-10 mt-n1">
                                                        <div class="pe-3 mb-5">
                                                            <div class="fs-5 fw-semibold mb-2">
                                                                @php
                                                                    $actionText = match($action->action_type) {
                                                                        'created' => 'created the memo',
                                                                        'viewed' => 'viewed the memo',
                                                                        'forwarded' => $action->forwardedToUser 
                                                                            ? "forwarded the memo to {$action->forwardedToUser->name}" 
                                                                            : ($action->forwardedToDepartment 
                                                                                ? "forwarded the memo to {$action->forwardedToDepartment->name} Department" 
                                                                                : "forwarded the memo"),
                                                                        'approved' => 'approved the memo',
                                                                        'rejected' => 'rejected the memo',
                                                                        'commented' => 'commented on the memo',
                                                                        'completed' => 'marked the memo as completed',
                                                                        'procured' => 'marked items as procured',
                                                                        'delivered' => 'marked items as delivered to stores',
                                                                        'audited' => 'marked items as audited by stores',
                                                                        default => "performed action: {$action->action_type}"
                                                                    };
                                                                @endphp
                                                                
                                                                <span class="text-primary fw-bold">{{ $action->user->name }}</span> {{ $actionText }}
                                                            </div>
                                                            
                                                            <div class="d-flex flex-wrap align-items-center text-muted fs-7">
                                                                <span class="me-4">
                                                                    <i class="ki-duotone ki-calendar text-primary fs-7 me-1">
                                                                        <span class="path1"></span>
                                                                        <span class="path2"></span>
                                                                    </i>
                                                                    {{ $action->created_at->format('M d, Y \a\t h:i A') }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                        
                                                        @if($action->comment)
                                                            <div class="p-5 rounded bg-light-primary text-dark fw-semibold mw-lg-600px text-start">{{ $action->comment }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-center py-10">
                                            <i class="ki-duotone ki-abstract-26 fs-3x text-muted mb-5">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                            <div class="text-muted fw-semibold fs-5">No activity history found for this memo.</div>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Actions Tab -->
                                <div class="tab-pane fade {{ $currentTab === 'actions' ? 'show active' : '' }}" id="actions" role="tabpanel">
                                    <div class="d-flex flex-column gap-3">
                                        <div class="mb-5">
                                            <label class="form-label">Comment</label>
                                            <textarea wire:model="comment" class="form-control" rows="3" placeholder="Add a comment (required for rejections)"></textarea>
                                            @error('comment') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                        
                                        <!-- Action Buttons -->
                                        <div class="d-flex flex-wrap gap-2">
                                            <!-- Forward Action -->
                                            @if(in_array($memo->status, ['pending', 'approved']))
                                                <div class="card card-flush p-5 mb-5 w-100">
                                                    <div class="card-header">
                                                        <h3 class="card-title">Forward Memo</h3>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="d-flex mb-5">
                                                            <div class="form-check form-check-custom form-check-solid me-5">
                                                                <input class="form-check-input" type="radio" value="user" id="forward_to_user" wire:model="forwardType">
                                                                <label class="form-check-label" for="forward_to_user">
                                                                    Forward to User
                                                                </label>
                                                            </div>
                                                            <div class="form-check form-check-custom form-check-solid">
                                                                <input class="form-check-input" type="radio" value="department" id="forward_to_department" wire:model="forwardType">
                                                                <label class="form-check-label" for="forward_to_department">
                                                                    Forward to Department
                                                                </label>
                                                            </div>
                                                        </div>
                                                        
                                                        @if($forwardType === 'user')
                                                            <div class="mb-5">
                                                                <label class="form-label required">Select User</label>
                                                                <select wire:model="forwardToUserId" class="form-select">
                                                                    <option value="">Select a user</option>
                                                                    @foreach($users as $user)
                                                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                @error('forwardToUserId') <span class="text-danger">{{ $message }}</span> @enderror
                                                            </div>
                                                        @else
                                                            <div class="mb-5">
                                                                <label class="form-label required">Select Department</label>
                                                                <select wire:model="forwardToDepartmentId" class="form-select">
                                                                    <option value="">Select a department</option>
                                                                    @foreach($departments as $department)
                                                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                @error('forwardToDepartmentId') <span class="text-danger">{{ $message }}</span> @enderror
                                                            </div>
                                                        @endif
                                                        
                                                        <div class="d-flex justify-content-end">
                                                            <button type="button" wire:click="forwardMemo" class="btn btn-primary">
                                                                <i class="ki-duotone ki-arrow-right fs-2 me-1">
                                                                    <span class="path1"></span>
                                                                    <span class="path2"></span>
                                                                </i>
                                                                Forward Memo
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                            
                                            <!-- Approval/Rejection Buttons -->
                                            @if($memo->status === 'pending' && ($memo->recipient_id === auth()->id() || ($memo->recipient_department_id && auth()->user()->department_id === $memo->recipient_department_id)))
                                                <div class="d-flex gap-2 mb-5">
                                                    <button type="button" wire:click="approveMemo" class="btn btn-success">
                                                        <i class="ki-duotone ki-check fs-2 me-1">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                        </i>
                                                        Approve
                                                    </button>
                                                    <button type="button" wire:click="rejectMemo" class="btn btn-danger">
                                                        <i class="ki-duotone ki-cross fs-2 me-1">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                        </i>
                                                        Reject
                                                    </button>
                                                </div>
                                            @endif
                                            
                                            <!-- Procurement Workflow -->
                                            @if($memo->status === 'approved')
                                                <!-- Mark as Procured button - for procurement officers -->
                                                @if(auth()->user()->hasRole('Procurement Officer'))
                                                    <button type="button" wire:click="markAsProcured" class="btn btn-info">
                                                        <i class="ki-duotone ki-basket fs-2 me-1">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                            <span class="path3"></span>
                                                        </i>
                                                        Mark as Procured
                                                    </button>
                                                @endif
                                                
                                                <!-- Mark as Delivered button - for procurement officers -->
                                                @if(auth()->user()->hasRole('Procurement Officer'))
                                                    <button type="button" wire:click="markAsDelivered" class="btn btn-info">
                                                        <i class="ki-duotone ki-delivery fs-2 me-1">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                            <span class="path3"></span>
                                                        </i>
                                                        Mark as Delivered to Stores
                                                    </button>
                                                @endif
                                                
                                                <!-- Mark as Audited button - for stores officers -->
                                                @if(auth()->user()->hasRole('Stores Manager'))
                                                    <button type="button" wire:click="markAsAudited" class="btn btn-warning">
                                                        <i class="ki-duotone ki-clipboard-check fs-2 me-1">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                            <span class="path3"></span>
                                                        </i>
                                                        Mark as Audited
                                                    </button>
                                                @endif
                                                
                                                <!-- Mark as Completed button - for memo owner or approver -->
                                                @if(auth()->id() === $memo->user_id || 
                                                   $memo->actions->where('action_type', 'approved')->where('user_id', auth()->id())->isNotEmpty())
                                                    <button type="button" wire:click="completeMemo" class="btn btn-success">
                                                        <i class="ki-duotone ki-check-circle fs-2 me-1">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                            <span class="path3"></span>
                                                        </i>
                                                        Mark as Completed
                                                    </button>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
