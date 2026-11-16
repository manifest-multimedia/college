<div>
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <h3 class="card-title">
                    <i class="ki-duotone ki-document fs-1 me-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    Memo: <?php echo e($memo->reference_number); ?>

                </h3>
            </div>
            <div class="card-toolbar">
                <a href="<?php echo e(route('memos')); ?>" class="btn btn-sm btn-light me-2">
                    <i class="ki-duotone ki-arrow-left fs-3"></i>
                    Back to List
                </a>
            </div>
        </div>
        
        <div class="card-body">
            <!-- Status Messages -->
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('success')): ?>
                <div class="alert alert-success mb-5">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('error')): ?>
                <div class="alert alert-danger mb-5">
                    <?php echo e(session('error')); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            
            <!-- Memo Header Section -->
            <div class="d-flex flex-column flex-lg-row mb-7">
                <!-- Left Column - Memo Details -->
                <div class="flex-column flex-lg-row-auto w-100 w-lg-300px w-xl-350px mb-10 mb-lg-0">
                    <div class="card card-flush">
                        <div class="card-header">
                            <h3 class="card-title text-gray-800 fw-bold">Memo Details</h3>
                        </div>
                        <div class="card-body pt-0">
                            <div class="fw-bold fs-5 text-gray-800 mb-2"><?php echo e($memo->title); ?></div>
                            
                            <div class="d-flex flex-column text-gray-600 gap-1 mt-6">
                                <div class="d-flex">
                                    <span class="text-gray-700 fw-bold me-1">Status:</span>
                                    <?php
                                        $statusClass = [
                                            'draft' => 'badge-light-dark',
                                            'pending' => 'badge-light-warning',
                                            'forwarded' => 'badge-light-info',
                                            'approved' => 'badge-light-success',
                                            'rejected' => 'badge-light-danger',
                                            'completed' => 'badge-light-primary',
                                        ][$memo->status] ?? 'badge-light';
                                    ?>
                                    <span class="badge <?php echo e($statusClass); ?>"><?php echo e(ucfirst($memo->status)); ?></span>
                                </div>
                                
                                <div class="d-flex">
                                    <span class="text-gray-700 fw-bold me-1">Priority:</span>
                                    <?php
                                        $priorityClass = [
                                            'low' => 'badge-light-success',
                                            'medium' => 'badge-light-warning',
                                            'high' => 'badge-light-danger',
                                        ][$memo->priority] ?? 'badge-light';
                                    ?>
                                    <span class="badge <?php echo e($priorityClass); ?>"><?php echo e(ucfirst($memo->priority)); ?></span>
                                </div>
                                
                                <div class="d-flex flex-column mt-3">
                                    <span class="text-gray-700 fw-bold mb-1">From:</span>
                                    <span class="text-gray-600"><?php echo e($memo->user->name ?? 'N/A'); ?></span>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($memo->department): ?>
                                        <span class="text-gray-500"><?php echo e($memo->department->name); ?></span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                
                                <div class="d-flex flex-column mt-3">
                                    <span class="text-gray-700 fw-bold mb-1">To:</span>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($memo->recipient): ?>
                                        <span class="text-gray-600"><?php echo e($memo->recipient->name); ?></span>
                                    <?php elseif($memo->recipientDepartment): ?>
                                        <span class="text-gray-600"><?php echo e($memo->recipientDepartment->name); ?> Department</span>
                                    <?php else: ?>
                                        <span class="text-gray-500">Not specified</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($memo->requested_action): ?>
                                    <div class="d-flex flex-column mt-3">
                                        <span class="text-gray-700 fw-bold mb-1">Requested Action:</span>
                                        <span class="text-gray-600"><?php echo e($memo->requested_action); ?></span>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                
                                <div class="d-flex flex-column mt-3">
                                    <span class="text-gray-700 fw-bold mb-1">Created:</span>
                                    <span class="text-gray-600"><?php echo e($memo->created_at->format('M d, Y \a\t h:i A')); ?></span>
                                </div>
                                
                                <div class="d-flex flex-column mt-3">
                                    <span class="text-gray-700 fw-bold mb-1">Last Updated:</span>
                                    <span class="text-gray-600"><?php echo e($memo->updated_at->format('M d, Y \a\t h:i A')); ?></span>
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
                            <div class="text-gray-700"><?php echo e($memo->description); ?></div>
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
                                        <a class="nav-link text-hover-primary <?php echo e($currentTab === 'attachments' ? 'active' : ''); ?>" 
                                           wire:click="setActiveTab('attachments')"
                                           role="tab">Attachments</a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link text-hover-primary <?php echo e($currentTab === 'history' ? 'active' : ''); ?>" 
                                           wire:click="setActiveTab('history')"
                                           role="tab">History</a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link text-hover-primary <?php echo e($currentTab === 'actions' ? 'active' : ''); ?>" 
                                           wire:click="setActiveTab('actions')"
                                           role="tab">Actions</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <div class="tab-content">
                                <!-- Attachments Tab -->
                                <div class="tab-pane fade <?php echo e($currentTab === 'attachments' ? 'show active' : ''); ?>" id="attachments" role="tabpanel">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($memo->attachments->count() > 0): ?>
                                        <div class="d-flex flex-column gap-5">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $memo->attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attachment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="d-flex justify-content-between align-items-center border-bottom pb-5">
                                                    <div class="d-flex align-items-center">
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attachment->is_image): ?>
                                                            <div class="symbol symbol-50px me-3">
                                                                <img src="<?php echo e($attachment->url); ?>" alt="<?php echo e($attachment->original_filename); ?>">
                                                            </div>
                                                        <?php elseif($attachment->is_pdf): ?>
                                                            <div class="symbol symbol-50px me-3">
                                                                <i class="ki-duotone ki-file-pdf fs-2x text-danger">
                                                                    <span class="path1"></span>
                                                                    <span class="path2"></span>
                                                                </i>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="symbol symbol-50px me-3">
                                                                <i class="ki-duotone ki-file fs-2x text-primary">
                                                                    <span class="path1"></span>
                                                                    <span class="path2"></span>
                                                                </i>
                                                            </div>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                        
                                                        <div class="ms-2">
                                                            <a href="<?php echo e($attachment->url); ?>" target="_blank" class="fs-5 fw-bold text-gray-900 text-hover-primary"><?php echo e($attachment->original_filename); ?></a>
                                                            <div class="fs-7 text-muted">
                                                                Size: <?php echo e($attachment->formatted_size); ?> | Uploaded by: <?php echo e($attachment->user->name ?? 'Unknown'); ?>

                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div>
                                                        <a href="<?php echo e($attachment->url); ?>" target="_blank" class="btn btn-icon btn-sm btn-light-primary me-1" title="View">
                                                            <i class="ki-duotone ki-eye fs-2">
                                                                <span class="path1"></span>
                                                                <span class="path2"></span>
                                                                <span class="path3"></span>
                                                            </i>
                                                        </a>
                                                        
                                                        <a href="<?php echo e($attachment->url); ?>" download class="btn btn-icon btn-sm btn-light-success me-1" title="Download">
                                                            <i class="ki-duotone ki-cloud-download fs-2">
                                                                <span class="path1"></span>
                                                                <span class="path2"></span>
                                                            </i>
                                                        </a>
                                                        
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->id() === $attachment->user_id || auth()->id() === $memo->user_id): ?>
                                                            <button wire:click="deleteAttachment(<?php echo e($attachment->id); ?>)" 
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
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-10">
                                            <i class="ki-duotone ki-document-missing fs-3x text-muted mb-5">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                            <div class="text-muted fw-semibold fs-5">No attachments found for this memo.</div>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                
                                <!-- History Tab -->
                                <div class="tab-pane fade <?php echo e($currentTab === 'history' ? 'show active' : ''); ?>" id="history" role="tabpanel">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($memo->actions->count() > 0): ?>
                                        <div class="timeline">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $memo->actions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="timeline-item">
                                                    <div class="timeline-line w-40px"></div>
                                                    
                                                    <div class="timeline-icon symbol symbol-circle symbol-40px">
                                                        <?php
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
                                                        ?>
                                                        
                                                        <i class="<?php echo e($iconClass); ?> fs-1">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(strpos($iconClass, 'check-circle') !== false || strpos($iconClass, 'clipboard-check') !== false): ?>
                                                                <span class="path3"></span>
                                                                <span class="path4"></span>
                                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                        </i>
                                                    </div>
                                                    
                                                    <div class="timeline-content mb-10 mt-n1">
                                                        <div class="pe-3 mb-5">
                                                            <div class="fs-5 fw-semibold mb-2">
                                                                <?php
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
                                                                ?>
                                                                
                                                                <span class="text-primary fw-bold"><?php echo e($action->user->name); ?></span> <?php echo e($actionText); ?>

                                                            </div>
                                                            
                                                            <div class="d-flex flex-wrap align-items-center text-muted fs-7">
                                                                <span class="me-4">
                                                                    <i class="ki-duotone ki-calendar text-primary fs-7 me-1">
                                                                        <span class="path1"></span>
                                                                        <span class="path2"></span>
                                                                    </i>
                                                                    <?php echo e($action->created_at->format('M d, Y \a\t h:i A')); ?>

                                                                </span>
                                                            </div>
                                                        </div>
                                                        
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($action->comment): ?>
                                                            <div class="p-5 rounded bg-light-primary text-dark fw-semibold mw-lg-600px text-start"><?php echo e($action->comment); ?></div>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-10">
                                            <i class="ki-duotone ki-abstract-26 fs-3x text-muted mb-5">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                            <div class="text-muted fw-semibold fs-5">No activity history found for this memo.</div>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                
                                <!-- Actions Tab -->
                                <div class="tab-pane fade <?php echo e($currentTab === 'actions' ? 'show active' : ''); ?>" id="actions" role="tabpanel">
                                    <div class="d-flex flex-column gap-3">
                                        <div class="mb-5">
                                            <label class="form-label">Comment</label>
                                            <textarea wire:model="comment" class="form-control" rows="3" placeholder="Add a comment (required for rejections)"></textarea>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['comment'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                        
                                        <!-- Action Buttons -->
                                        <div class="d-flex flex-wrap gap-2">
                                            <!-- Forward Action -->
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($memo->status, ['pending', 'approved'])): ?>
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
                                                        
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($forwardType === 'user'): ?>
                                                            <div class="mb-5">
                                                                <label class="form-label required">Select User</label>
                                                                <select wire:model="forwardToUserId" class="form-select">
                                                                    <option value="">Select a user</option>
                                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                        <option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?></option>
                                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                                </select>
                                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['forwardToUserId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="mb-5">
                                                                <label class="form-label required">Select Department</label>
                                                                <select wire:model="forwardToDepartmentId" class="form-select">
                                                                    <option value="">Select a department</option>
                                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                        <option value="<?php echo e($department->id); ?>"><?php echo e($department->name); ?></option>
                                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                                </select>
                                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['forwardToDepartmentId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                            </div>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                        
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
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            
                                            <!-- Approval/Rejection Buttons -->
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($memo->status === 'pending' && ($memo->recipient_id === auth()->id() || ($memo->recipient_department_id && auth()->user()->department_id === $memo->recipient_department_id))): ?>
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
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            
                                            <!-- Procurement Workflow -->
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($memo->status === 'approved'): ?>
                                                <!-- Mark as Procured button - for procurement officers -->
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->hasRole('Procurement Officer')): ?>
                                                    <button type="button" wire:click="markAsProcured" class="btn btn-info">
                                                        <i class="ki-duotone ki-basket fs-2 me-1">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                            <span class="path3"></span>
                                                        </i>
                                                        Mark as Procured
                                                    </button>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                
                                                <!-- Mark as Delivered button - for procurement officers -->
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->hasRole('Procurement Officer')): ?>
                                                    <button type="button" wire:click="markAsDelivered" class="btn btn-info">
                                                        <i class="ki-duotone ki-delivery fs-2 me-1">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                            <span class="path3"></span>
                                                        </i>
                                                        Mark as Delivered to Stores
                                                    </button>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                
                                                <!-- Mark as Audited button - for stores officers -->
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->hasRole('Stores Manager')): ?>
                                                    <button type="button" wire:click="markAsAudited" class="btn btn-warning">
                                                        <i class="ki-duotone ki-clipboard-check fs-2 me-1">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                            <span class="path3"></span>
                                                        </i>
                                                        Mark as Audited
                                                    </button>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                
                                                <!-- Mark as Completed button - for memo owner or approver -->
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->id() === $memo->user_id || 
                                                   $memo->actions->where('action_type', 'approved')->where('user_id', auth()->id())->isNotEmpty()): ?>
                                                    <button type="button" wire:click="completeMemo" class="btn btn-success">
                                                        <i class="ki-duotone ki-check-circle fs-2 me-1">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                            <span class="path3"></span>
                                                        </i>
                                                        Mark as Completed
                                                    </button>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
<?php /**PATH /home/johnsonsebire/www/college/resources/views/livewire/memos/view-memo.blade.php ENDPATH**/ ?>