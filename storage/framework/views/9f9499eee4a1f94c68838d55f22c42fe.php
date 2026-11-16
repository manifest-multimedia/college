<!--begin::Card-->
<div class="card">
            <!--begin::Card header-->
            <div class="card-header">
                <div class="card-title">
                    <h3 class="card-title">
                        <i class="ki-duotone ki-ticket fs-1 me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        <?php echo e($ticket->ticket_number); ?>: <?php echo e($ticket->subject); ?>

                    </h3>
                </div>
                <div class="card-toolbar">
                    <a href="<?php echo e(route('support.tickets')); ?>" class="btn btn-sm btn-light-primary me-2">
                        <i class="ki-duotone ki-arrow-left fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Back to Tickets
                    </a>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$ticket->isClosed()): ?>
                        <button wire:click="closeTicket" class="btn btn-sm btn-danger" wire:confirm="Are you sure you want to close this ticket?">
                            <i class="ki-duotone ki-cross-circle fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Close Ticket
                        </button>
                    <?php else: ?>
                        <button wire:click="reopenTicket" class="btn btn-sm btn-success" wire:confirm="Are you sure you want to reopen this ticket?">
                            <i class="ki-duotone ki-check-circle fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Reopen Ticket
                        </button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
            <!--end::Card header-->

            <!--begin::Card body-->
            <div class="card-body py-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo e(session('success')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo e(session('error')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <!--begin::Ticket Info-->
                <div class="d-flex flex-wrap mb-10 gap-5">
                    <div class="border border-dashed border-gray-300 rounded py-3 px-4">
                        <div class="fw-semibold text-gray-500">Category:</div>
                        <div class="fs-5 fw-bold text-gray-800"><?php echo e($ticket->category->name); ?></div>
                    </div>
                    <div class="border border-dashed border-gray-300 rounded py-3 px-4">
                        <div class="fw-semibold text-gray-500">Status:</div>
                        <div class="fs-5 fw-bold text-gray-800">
                            <?php
                                $statusBadge = match($ticket->status) {
                                    'Open' => 'badge-light-success',
                                    'In Progress' => 'badge-light-warning',
                                    'Resolved' => 'badge-light-info',
                                    'Closed' => 'badge-light-danger',
                                    default => 'badge-light-secondary',
                                };
                            ?>
                            <span class="badge <?php echo e($statusBadge); ?> fs-6"><?php echo e($ticket->status); ?></span>
                        </div>
                    </div>
                    <div class="border border-dashed border-gray-300 rounded py-3 px-4">
                        <div class="fw-semibold text-gray-500">Priority:</div>
                        <div class="fs-5 fw-bold text-gray-800">
                            <?php
                                $badgeClass = match($ticket->priority) {
                                    'Low' => 'badge-light-info',
                                    'Medium' => 'badge-light-primary',
                                    'High' => 'badge-light-warning',
                                    'Urgent' => 'badge-light-danger',
                                    default => 'badge-light-secondary',
                                };
                            ?>
                            <span class="badge <?php echo e($badgeClass); ?> fs-6"><?php echo e($ticket->priority); ?></span>
                        </div>
                    </div>
                    <div class="border border-dashed border-gray-300 rounded py-3 px-4">
                        <div class="fw-semibold text-gray-500">Created On:</div>
                        <div class="fs-5 fw-bold text-gray-800"><?php echo e($ticket->created_at->format('M d, Y')); ?></div>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ticket->assignedTo): ?>
                    <div class="border border-dashed border-gray-300 rounded py-3 px-4">
                        <div class="fw-semibold text-gray-500">Assigned To:</div>
                        <div class="fs-5 fw-bold text-gray-800"><?php echo e($ticket->assignedTo->name); ?></div>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <!--end::Ticket Info-->

                <!--begin::Conversation Thread-->
                <div class="mb-10">
                    <h3 class="fw-bold mb-5">Conversation</h3>
                    
                    <!--begin::Original Message-->
                    <div class="border border-dashed border-gray-300 rounded p-7 mb-7">
                        <div class="d-flex flex-stack mb-5">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-circle symbol-40px me-3">
                                    <img src="<?php echo e($ticket->user->profile_photo_url); ?>" alt="Profile" />
                                </div>
                                <div>
                                    <a href="#" class="fs-5 fw-bold text-gray-900 text-hover-primary me-1"><?php echo e($ticket->user->name); ?></a>
                                    <span class="text-muted fs-7 d-block"><?php echo e($ticket->created_at->diffForHumans()); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="fs-6 text-gray-800 mb-5">
                            <?php echo nl2br(e($ticket->message)); ?>

                        </div>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ticket->attachments->count() > 0): ?>
                            <div class="separator separator-dashed my-5"></div>
                            <div class="fs-7 text-muted mb-2">Attachments:</div>
                            <div class="d-flex flex-wrap gap-2">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $ticket->attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attachment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <a href="<?php echo e(Storage::url($attachment->file_path)); ?>" target="_blank" class="btn btn-sm btn-light-primary">
                                        <i class="ki-duotone ki-file fs-3">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        <?php echo e($attachment->file_name); ?> (<?php echo e($attachment->formatted_size); ?>)
                                    </a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <!--end::Original Message-->

                    <!--begin::Replies-->
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $ticket->replies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reply): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="border border-dashed border-gray-300 rounded p-7 mb-7 <?php echo e($reply->user_id !== $ticket->user_id ? 'bg-light-primary' : ''); ?>">
                            <div class="d-flex flex-stack mb-5">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-circle symbol-40px me-3">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($reply->user->profile_photo_url): ?>
                                            <img src="<?php echo e($reply->user->profile_photo_url); ?>" alt="Profile" />
                                        <?php else: ?>
                                            <div class="symbol-label bg-light-primary">
                                                <i class="ki-duotone ki-abstract-26 fs-2 text-primary">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <div>
                                        <a href="#" class="fs-5 fw-bold text-gray-900 text-hover-primary me-1"><?php echo e($reply->user->name); ?></a>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($reply->user_id !== $ticket->user_id): ?>
                                            <span class="badge badge-sm badge-light-primary">Support Team</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <span class="text-muted fs-7 d-block"><?php echo e($reply->created_at->diffForHumans()); ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="fs-6 text-gray-800 mb-5">
                                <?php echo nl2br(e($reply->message)); ?>

                            </div>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($reply->attachments->count() > 0): ?>
                                <div class="separator separator-dashed my-5"></div>
                                <div class="fs-7 text-muted mb-2">Attachments:</div>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $reply->attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attachment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <a href="<?php echo e(Storage::url($attachment->file_path)); ?>" target="_blank" class="btn btn-sm btn-light-primary">
                                            <i class="ki-duotone ki-file fs-3">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                            <?php echo e($attachment->file_name); ?> (<?php echo e($attachment->formatted_size); ?>)
                                        </a>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <!--end::Replies-->
                </div>
                <!--end::Conversation Thread-->

                <!--begin::Reply Form-->
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$ticket->isClosed()): ?>
                    <div class="card bg-light">
                        <div class="card-header">
                            <h4 class="card-title">Add Reply</h4>
                        </div>
                        <div class="card-body">
                            <form wire:submit.prevent="submitReply">
                                <div class="mb-5">
                                    <label class="required fs-6 fw-semibold mb-2">Your Reply</label>
                                    <textarea class="form-control form-control-solid border border-gray-400 <?php $__errorArgs = ['replyMessage'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" rows="6" wire:model="replyMessage" placeholder="Type your reply here" style="border-width: 1px !important;"></textarea>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['replyMessage'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                
                                <div class="mb-5">
                                    <label class="fs-6 fw-semibold mb-2">Attachments (Optional)</label>
                                    <input type="file" class="form-control form-control-solid <?php $__errorArgs = ['attachments.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" wire:model="attachments" multiple />
                                    <div class="form-text">You can upload up to 5 files (Max 2MB each).</div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['attachments.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attachments): ?>
                                        <div class="mt-3">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <div class="badge badge-light-primary me-2"><?php echo e($file->getClientOriginalName()); ?></div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>

                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                        <span wire:loading.remove>
                                            <i class="ki-duotone ki-send fs-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                            Submit Reply
                                        </span>
                                        <span wire:loading>
                                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            Please wait...
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="ki-duotone ki-information-5 fs-2x text-info me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        This ticket is closed. Click "Reopen Ticket" above to add more replies.
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <!--end::Reply Form-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->
</div>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/livewire/ticket-detail.blade.php ENDPATH**/ ?>