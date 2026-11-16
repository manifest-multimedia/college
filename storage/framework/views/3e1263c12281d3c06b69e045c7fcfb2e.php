<div>
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">
                    <i class="fas fa-ticket-alt mr-2"></i> Exam Entry Tickets
                </h3>
                <div class="d-flex">
                    <div class="input-group mr-2" style="width: 250px;">
                        <input wire:model.live.debounce.300ms="search" type="text" class="form-control" placeholder="Search tickets...">
                        <div class="input-group-append">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                    </div>
                    <div class="mr-2" style="width: 180px;">
                        <select wire:model.live="examFilter" class="form-control">
                            <option value="">All Exams</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $exams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exam): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($exam->id); ?>"><?php echo e($exam->title ?: 'Untitled examination'); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                    </div>
                    <div class="mr-2" style="width: 140px;">
                        <select wire:model.live="statusFilter" class="form-control">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="used">Used</option>
                            <option value="expired">Expired</option>
                            <option value="revoked">Revoked</option>
                        </select>
                    </div>
                    <div style="width: 70px;">
                        <select wire:model.live="perPage" class="form-control">
                            <option>10</option>
                            <option>25</option>
                            <option>50</option>
                            <option>100</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Ticket #</th>
                            <th>Student</th>
                            <th>Exam</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Used At</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td>
                                    <span class="text-primary font-weight-bold"><?php echo e($ticket->ticket_number); ?></span><br>
                                    <small class="text-muted"><?php echo e($ticket->verification_code); ?></small>
                                </td>
                                <td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ticket->examClearance && $ticket->examClearance->student): ?>
                                        <?php echo e($ticket->examClearance->student->name); ?><br>
                                        <small class="text-muted"><?php echo e($ticket->examClearance->student->student_id); ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">No student data</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($ticket->examClearance && $ticket->examClearance->exam): ?>
                                        <?php echo e($ticket->examClearance->exam->title); ?>

                                    <?php else: ?>
                                        <span class="text-muted">No exam data</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php switch($ticket->status):
                                        case ('active'): ?>
                                            <span class="badge badge-success">Active</span>
                                            <?php break; ?>
                                        <?php case ('used'): ?>
                                            <span class="badge badge-info">Used</span>
                                            <?php break; ?>
                                        <?php case ('expired'): ?>
                                            <span class="badge badge-warning">Expired</span>
                                            <?php break; ?>
                                        <?php case ('revoked'): ?>
                                            <span class="badge badge-danger">Revoked</span>
                                            <?php break; ?>
                                        <?php default: ?>
                                            <span class="badge badge-secondary"><?php echo e($ticket->status); ?></span>
                                    <?php endswitch; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td><?php echo e($ticket->created_at->format('M d, Y H:i')); ?></td>
                                <td><?php echo e($ticket->used_at ? $ticket->used_at->format('M d, Y H:i') : 'Not used'); ?></td>
                                <td class="text-right">
                                    <button wire:click="viewTicketDetails(<?php echo e($ticket->id); ?>)" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button wire:click="printTicket(<?php echo e($ticket->id); ?>)" class="btn btn-sm btn-primary">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="empty-state">
                                        <i class="fas fa-ticket-alt fa-3x text-muted mb-3"></i>
                                        <p>No exam entry tickets found</p>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($search || $examFilter || $statusFilter): ?>
                                            <button wire:click="$set('search', ''); $set('examFilter', ''); $set('statusFilter', '')" class="btn btn-sm btn-outline-primary">
                                                Clear filters
                                            </button>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    Showing <?php echo e($tickets->firstItem() ?? 0); ?> to <?php echo e($tickets->lastItem() ?? 0); ?> of <?php echo e($tickets->total()); ?> tickets
                </div>
                <div>
                    <?php echo e($tickets->links()); ?>

                </div>
            </div>
        </div>
    </div>

    <!-- Ticket Details Modal -->
    <div class="modal fade" id="ticketDetailsModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ticket Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedTicket): ?>
                        <div class="text-center mb-4">
                            <div style="width: 180px; height: 180px; margin: 0 auto;">
                                <?php echo QrCode::size(180)->generate($selectedTicket->verification_code); ?>

                            </div>
                            <p class="mt-2 font-weight-bold"><?php echo e($selectedTicket->verification_code); ?></p>
                        </div>

                        <div class="card bg-light mb-3">
                            <div class="card-body p-3">
                                <div class="row">
                                    <div class="col-5 text-muted">Ticket Number:</div>
                                    <div class="col-7 font-weight-bold"><?php echo e($selectedTicket->ticket_number); ?></div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-5 text-muted">Status:</div>
                                    <div class="col-7">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php switch($selectedTicket->status):
                                            case ('active'): ?>
                                                <span class="badge badge-success">Active</span>
                                                <?php break; ?>
                                            <?php case ('used'): ?>
                                                <span class="badge badge-info">Used</span>
                                                <?php break; ?>
                                            <?php case ('expired'): ?>
                                                <span class="badge badge-warning">Expired</span>
                                                <?php break; ?>
                                            <?php case ('revoked'): ?>
                                                <span class="badge badge-danger">Revoked</span>
                                                <?php break; ?>
                                            <?php default: ?>
                                                <span class="badge badge-secondary"><?php echo e($selectedTicket->status); ?></span>
                                        <?php endswitch; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-5 text-muted">Student:</div>
                                    <div class="col-7">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedTicket->examClearance && $selectedTicket->examClearance->student): ?>
                                            <?php echo e($selectedTicket->examClearance->student->name); ?><br>
                                            <small class="text-muted">ID: <?php echo e($selectedTicket->examClearance->student->student_id); ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">No student data</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-5 text-muted">Exam:</div>
                                    <div class="col-7">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedTicket->examClearance && $selectedTicket->examClearance->exam): ?>
                                            <?php echo e($selectedTicket->examClearance->exam->title); ?>

                                        <?php else: ?>
                                            <span class="text-muted">No exam data</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-5 text-muted">Created At:</div>
                                    <div class="col-7"><?php echo e($selectedTicket->created_at->format('M d, Y H:i')); ?></div>
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedTicket->used_at): ?>
                                <div class="row mt-2">
                                    <div class="col-5 text-muted">Used At:</div>
                                    <div class="col-7"><?php echo e($selectedTicket->used_at->format('M d, Y H:i')); ?></div>
                                </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedTicket->expires_at): ?>
                                <div class="row mt-2">
                                    <div class="col-5 text-muted">Expires At:</div>
                                    <div class="col-7"><?php echo e($selectedTicket->expires_at->format('M d, Y H:i')); ?></div>
                                </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedTicket->notes): ?>
                                <div class="row mt-2">
                                    <div class="col-5 text-muted">Notes:</div>
                                    <div class="col-7"><?php echo e($selectedTicket->notes); ?></div>
                                </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedTicket): ?>
                        <button wire:click="printTicket(<?php echo e($selectedTicket->id); ?>)" class="btn btn-primary">
                            <i class="fas fa-print mr-1"></i> Print Ticket
                        </button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
        document.addEventListener('livewire:initialized', () => {
            window.Livewire.find('<?php echo e($_instance->getId()); ?>').on('show-ticket-details-modal', () => {
                $('#ticketDetailsModal').modal('show');
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            $('#ticketDetailsModal').on('hidden.bs.modal', function () {
                window.Livewire.find('<?php echo e($_instance->getId()); ?>').call('closeTicketDetails');
            });
        });
    </script>
    <?php $__env->stopPush(); ?>
</div>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/livewire/admin/exam-tickets.blade.php ENDPATH**/ ?>