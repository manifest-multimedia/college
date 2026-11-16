<div>
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="card-title">
                    <i class="fas fa-clipboard-check"></i> Exam Clearance Management
                </h1>
            </div>
        </div>
        <div class="card-body">
            <!-- Filters Section -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="academicYearId">Academic Year</label>
                        <select wire:model.live="academicYearId" class="form-control">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $academicYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($year->id); ?>"><?php echo e($year->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="semesterId">Semester</label>
                        <select wire:model.live="semesterId" class="form-control">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $semesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $semester): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($semester->id); ?>">
                                    <?php echo e($semester->name); ?><?php echo e(empty($academicYearId) && $semester->academicYear ? ' (' . $semester->academicYear->name . ')' : ''); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="examTypeId">Exam Type</label>
                        <select wire:model.live="examTypeId" class="form-control">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $examTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $examType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($examType->id); ?>"><?php echo e($examType->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="searchTerm">Search Student</label>
                        <input type="text" wire:model.live.debounce.300ms="searchTerm" class="form-control" placeholder="Name or ID...">
                    </div>
                </div>
            </div>
            
            <!-- Students List -->
            <div class="table-responsive">
                <table class="table table-striped table-hover table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Program</th>
                            <th>Payment Status</th>
                            <th>Payment %</th>
                            <th>Clearance Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $feeBill = $student->feeBills->first();
                                $clearance = $student->examClearances->first();
                                $paymentPercentage = $feeBill ? $feeBill->payment_percentage : 0;
                                $examType = $examTypes->where('id', $examTypeId)->first();
                                $requiredPercentage = $examType ? $examType->payment_threshold : 0;
                                $isEligible = $paymentPercentage >= $requiredPercentage;
                            ?>
                            <tr>
                                <td><?php echo e($student->student_id); ?></td>
                                <td><?php echo e($student->full_name); ?></td>
                                <td><?php echo e($student->collegeClass->name ?? 'Not Assigned'); ?></td>
                                <td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($feeBill): ?>
                                        <?php
                                            $status = $feeBill->getPaymentStatus();
                                        ?>
                                        <span class="badge bg-<?php echo e($status === 'paid' ? 'success' : ($status === 'partial' ? 'warning' : 'danger')); ?>">
                                            <?php echo e($status === 'paid' ? 'Paid' : ($status === 'partial' ? 'Partial' : 'Unpaid')); ?>

                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Not Billed</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td>
                                    <div class="progress">
                                        <div class="progress-bar <?php echo e($isEligible ? 'bg-success' : 'bg-danger'); ?>" role="progressbar"
                                            style="width: <?php echo e($paymentPercentage); ?>%" aria-valuenow="<?php echo e($paymentPercentage); ?>" aria-valuemin="0"
                                            aria-valuemax="100"><?php echo e(number_format($paymentPercentage, 1)); ?>%</div>
                                    </div>
                                    <small class="text-muted">Required: <?php echo e($requiredPercentage); ?>%</small>
                                </td>
                                <td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($clearance): ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($clearance->is_cleared): ?>
                                            <span class="badge bg-success">Cleared</span>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($clearance->is_manual_override): ?>
                                                <span class="badge bg-warning" title="<?php echo e($clearance->override_reason); ?>">Manual Override</span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Not Cleared</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Pending</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($clearance && $clearance->is_cleared): ?>
                                        <button wire:click="revokeClearance(<?php echo e($clearance->id); ?>)" class="btn btn-sm btn-danger">
                                            <i class="fas fa-ban"></i> Revoke
                                        </button>
                                        <a href="<?php echo e(route('finance.exam.tickets', ['clearanceId' => $clearance->id])); ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-qrcode"></i> Tickets
                                        </a>
                                    <?php else: ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isEligible): ?>
                                            <button wire:click="openClearanceModal(<?php echo e($student->id); ?>)" class="btn btn-sm btn-primary">
                                                <i class="fas fa-check-circle"></i> Clear
                                            </button>
                                        <?php else: ?>
                                            <button wire:click="openOverrideModal(<?php echo e($student->id); ?>)" class="btn btn-sm btn-warning">
                                                <i class="fas fa-exclamation-triangle"></i> Override
                                            </button>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="text-center">No students found</td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
                
                <div class="mt-4">
                    <?php echo e($students->links()); ?>

                </div>
            </div>
            
            <div class="mt-4">
                <h4>Cleared Students for <?php echo e($examTypes->where('id', $examTypeId)->first()->name ?? 'Selected Exam'); ?></h4>
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Program</th>
                                <th>Clearance Date</th>
                                <th>Method</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $clearedStudents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $clearance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($clearance->student->student_id); ?></td>
                                    <td><?php echo e($clearance->student->full_name); ?></td>
                                    <td><?php echo e($clearance->student->collegeClass->name ?? 'Not Assigned'); ?></td>
                                    <td><?php echo e($clearance->cleared_at->format('d M Y, h:i A')); ?></td>
                                    <td>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($clearance->is_manual_override): ?>
                                            <span class="badge bg-warning">Manual Override</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Normal</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="5" class="text-center">No cleared students found</td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <a href="#" onclick="window.print()" class="btn btn-secondary">
                        <i class="fas fa-print"></i> Print Clearance List
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Clearance Modal -->
    <div wire:ignore.self class="modal fade" id="clearanceModal" tabindex="-1" aria-labelledby="clearanceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="clearanceModalLabel">Confirm Exam Clearance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="clearStudent">
                    <div class="modal-body">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedStudentId && $examTypeId): ?>
                            <p>Are you sure you want to clear this student for the exam?</p>
                            <p>Selected exam: <strong><?php echo e($examTypes->where('id', $examTypeId)->first()->name ?? 'Unknown'); ?></strong></p>
                            <p>Student has met the payment requirement of <?php echo e($examTypes->where('id', $examTypeId)->first()->payment_threshold ?? '0'); ?>%.</p>
                            
                            <input type="hidden" wire:model="examTypeId">
                        <?php else: ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i> Please select an exam type before continuing.
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" <?php if(!$examTypeId): ?> disabled <?php endif; ?>>Clear Student</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Manual Override Modal -->
    <div wire:ignore.self class="modal fade" id="overrideModal" tabindex="-1" aria-labelledby="overrideModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="overrideModalLabel">Manual Clearance Override</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="clearStudent">
                    <div class="modal-body">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($examTypeId): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> Warning: This student has not met the required payment threshold.
                            </div>
                            
                            <p>Selected exam: <strong><?php echo e($examTypes->where('id', $examTypeId)->first()->name ?? 'Unknown'); ?></strong></p>
                            
                            <div class="form-group">
                                <label for="overrideReason">Reason for Override (Required)</label>
                                <textarea wire:model="overrideReason" class="form-control <?php $__errorArgs = ['overrideReason'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" rows="3"></textarea>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['overrideReason'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            
                            <input type="hidden" wire:model="examTypeId">
                        <?php else: ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i> Please select an exam type before continuing.
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning" <?php if(!$examTypeId): ?> disabled <?php endif; ?>>Override & Clear</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
        document.addEventListener('livewire:init', function () {
            // Modal handling for Laravel 12 and Livewire v3
            Livewire.on('show-clearance-modal', () => {
                let modal = new bootstrap.Modal(document.getElementById('clearanceModal'));
                modal.show();
                console.log('Showing clearance modal');
            });
            
            Livewire.on('show-override-modal', () => {
                let modal = new bootstrap.Modal(document.getElementById('overrideModal'));
                modal.show();
                console.log('Showing override modal');
            });
            
            Livewire.on('close-modal', () => {
                // Close any open modals
                let clearanceModal = bootstrap.Modal.getInstance(document.getElementById('clearanceModal'));
                let overrideModal = bootstrap.Modal.getInstance(document.getElementById('overrideModal'));
                
                if (clearanceModal) {
                    clearanceModal.hide();
                    console.log('Hiding clearance modal');
                }
                
                if (overrideModal) {
                    overrideModal.hide();
                    console.log('Hiding override modal');
                }
            });
        });
    </script>
    <?php $__env->stopPush(); ?>
</div>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/livewire/finance/exam-clearance-manager.blade.php ENDPATH**/ ?>