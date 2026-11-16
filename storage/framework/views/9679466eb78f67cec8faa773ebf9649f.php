<div>
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
                        Support Tickets
                    </h3>
                </div>
                <div class="card-toolbar">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#create_ticket_modal">
                        <i class="ki-duotone ki-plus-square fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        Create New Ticket
                    </button>
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

                <!-- Tabs for filtering tickets -->
                <ul class="nav nav-tabs nav-line-tabs mb-5 fs-6">
                    <li class="nav-item">
                        <a class="nav-link <?php echo e($statusFilter === 'all' ? 'active' : ''); ?>" href="#" wire:click.prevent="setStatusFilter('all')">All Tickets (<?php echo e($tickets->count()); ?>)</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e($statusFilter === 'open' ? 'active' : ''); ?>" href="#" wire:click.prevent="setStatusFilter('open')">Open</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e($statusFilter === 'in_progress' ? 'active' : ''); ?>" href="#" wire:click.prevent="setStatusFilter('in_progress')">In Progress</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e($statusFilter === 'closed' ? 'active' : ''); ?>" href="#" wire:click.prevent="setStatusFilter('closed')">Closed</a>
                    </li>
                </ul>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tickets->isEmpty()): ?>
                    <div class="py-10 text-center">
                        <i class="ki-duotone ki-information-5 fs-5x text-gray-400 mb-5">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        <h3 class="text-gray-600 mb-2">No tickets found</h3>
                        <p class="text-gray-400">Click "Create New Ticket" to submit a support request.</p>
                    </div>
                <?php else: ?>
                    <!--begin::Table -->
                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-6 gy-5">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th class="min-w-125px">Ticket ID</th>
                                    <th class="min-w-175px">Subject</th>
                                    <th class="min-w-125px">Category</th>
                                    <th class="min-w-100px">Priority</th>
                                    <th class="min-w-125px">Status</th>
                                    <th class="min-w-125px">Last Updated</th>
                                    <th class="text-end min-w-100px">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-semibold">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo e(route('support.ticket.detail', $ticket->public_id ?? $ticket->id)); ?>" class="text-gray-800 text-hover-primary mb-1"><?php echo e($ticket->ticket_number); ?></a>
                                        </td>
                                        <td><?php echo e(Str::limit($ticket->subject, 50)); ?></td>
                                        <td><?php echo e($ticket->category->name); ?></td>
                                        <td>
                                            <?php
                                                $badgeClass = match($ticket->priority) {
                                                    'Low' => 'badge-light-info',
                                                    'Medium' => 'badge-light-primary',
                                                    'High' => 'badge-light-warning',
                                                    'Urgent' => 'badge-light-danger',
                                                    default => 'badge-light-secondary',
                                                };
                                            ?>
                                            <span class="badge <?php echo e($badgeClass); ?>"><?php echo e($ticket->priority); ?></span>
                                        </td>
                                        <td>
                                            <?php
                                                $statusBadge = match($ticket->status) {
                                                    'Open' => 'badge-light-success',
                                                    'In Progress' => 'badge-light-warning',
                                                    'Resolved' => 'badge-light-info',
                                                    'Closed' => 'badge-light-danger',
                                                    default => 'badge-light-secondary',
                                                };
                                            ?>
                                            <span class="badge <?php echo e($statusBadge); ?>"><?php echo e($ticket->status); ?></span>
                                        </td>
                                        <td><?php echo e($ticket->updated_at->diffForHumans()); ?></td>
                                        <td class="text-end">
                                            <a href="<?php echo e(route('support.ticket.detail', $ticket->id)); ?>" class="btn btn-sm btn-light-primary">
                                                View
                                            </a>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$ticket->isClosed()): ?>
                                                <button wire:click="closeTicket(<?php echo e($ticket->id); ?>)" class="btn btn-sm btn-light-danger" wire:confirm="Are you sure you want to close this ticket?">
                                                    Close
                                                </button>
                                            <?php else: ?>
                                                <button wire:click="reopenTicket(<?php echo e($ticket->id); ?>)" class="btn btn-sm btn-light-success" wire:confirm="Are you sure you want to reopen this ticket?">
                                                    Reopen
                                                </button>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <!--end::Table-->
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->

        <!-- Create Ticket Modal -->
        <div class="modal fade" id="create_ticket_modal" tabindex="-1" aria-hidden="true" wire:ignore.self>
            <div class="modal-dialog modal-dialog-centered mw-650px">
                <div class="modal-content">
                    <form wire:submit.prevent="createTicket">
                        <div class="modal-header">
                            <h2 class="fw-bold">Create New Support Ticket</h2>
                            <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                                <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                            </div>
                        </div>
                        <div class="modal-body py-10 px-lg-17">
                            <!-- Subject Field -->
                            <div class="fv-row mb-7">
                                <label class="required fs-6 fw-semibold mb-2">Subject</label>
                                <input type="text" class="form-control form-control-solid <?php $__errorArgs = ['subject'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="Enter ticket subject" wire:model="subject" />
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['subject'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            
                            <!-- Category Field -->
                            <div class="fv-row mb-7">
                                <label class="required fs-6 fw-semibold mb-2">Category</label>
                                <select class="form-select form-select-solid <?php $__errorArgs = ['category_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" wire:model="category_id">
                                    <option value="">Select a category...</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($category->id); ?>"><?php echo e($category->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </select>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['category_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            
                            <!-- Priority Field -->
                            <div class="fv-row mb-7">
                                <label class="required fs-6 fw-semibold mb-2">Priority</label>
                                <select class="form-select form-select-solid <?php $__errorArgs = ['priority'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" wire:model="priority">
                                    <option value="Low">Low</option>
                                    <option value="Medium">Medium</option>
                                    <option value="High">High</option>
                                    <option value="Urgent">Urgent</option>
                                </select>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['priority'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            
                            <!-- Message Field -->
                            <div class="fv-row mb-7">
                                <label class="required fs-6 fw-semibold mb-2">Message</label>
                                <textarea class="form-control form-control-solid <?php $__errorArgs = ['message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" rows="6" wire:model="message" placeholder="Describe your issue in detail"></textarea>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            
                            <!-- Attachments Field -->
                            <div class="fv-row mb-7">
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
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="badge badge-light-primary me-2"><?php echo e($file->getClientOriginalName()); ?></div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                        <div class="modal-footer flex-center">
                            <button type="button" data-bs-dismiss="modal" class="btn btn-light me-3">Cancel</button>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove>Submit</span>
                                <span wire:loading>
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                    Please wait...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
   
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    // Close modal on successful ticket creation
    Livewire.on('close-modal', (modalId) => {
        const modal = document.getElementById(modalId);
        if (modal) {
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) {
                bsModal.hide();
            }
        }
    });
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/livewire/support-tickets.blade.php ENDPATH**/ ?>