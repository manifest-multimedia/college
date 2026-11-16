<div>
    <!-- Bill Details Card -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="card-title mb-0">
                    <i class="fas fa-file-invoice"></i> Student Bill Details
                </h1>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$loading && $bill): ?>
                <div>
                    <button wire:click="printBill" class="btn btn-light btn-sm">
                        <i class="fas fa-print"></i> Print Bill
                    </button>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $bill->isReversed()): ?>
                    <button wire:click="recordPayment" class="btn btn-success btn-sm ms-2">
                        <i class="fas fa-money-bill-wave"></i> Record Payment
                    </button>
                    <a href="<?php echo e(route('finance.bill.edit', ['billId' => $billId])); ?>" class="btn btn-warning btn-sm ms-2">
                        <i class="fas fa-edit"></i> Edit Bill
                    </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo e(session('error')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($loading): ?>
                <div class="d-flex justify-content-center align-items-center p-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <span class="ms-2">Loading bill details...</span>
                </div>
            <?php elseif(!$bill): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> Bill not found or has been deleted.
                </div>
            <?php else: ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bill->isReversed()): ?>
                    <div class="alert alert-warning">
                        <strong>This bill was reversed on <?php echo e($bill->reversed_at->format('d M, Y H:i')); ?>.</strong>
                        It is retained for audit only and does not affect the student's current fees.
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bill->reversal_reason): ?>
                            <div class="mt-1"><strong>Reason:</strong> <?php echo e($bill->reversal_reason); ?></div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bill->reversedBy): ?>
                            <div class="small mt-1">Reversed by <?php echo e($bill->reversedBy->name); ?>.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <!-- Student & Bill Information -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2 mb-3">Student Information</h5>
                        <table class="table table-sm">
                            <tr>
                                <th style="width: 150px;">Student ID:</th>
                                <td><?php echo e($bill->student->student_id); ?></td>
                            </tr>
                            <tr>
                                <th>Name:</th>
                                <td><?php echo e($bill->student->first_name); ?> <?php echo e($bill->student->last_name); ?></td>
                            </tr>
                            <tr>
                                <th>Program:</th>
                                <td><?php echo e($bill->student->collegeClass->name ?? 'Not Assigned'); ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2 mb-3">Bill Information</h5>
                        <table class="table table-sm">
                            <tr>
                                <th style="width: 150px;">Bill Number:</th>
                                <td><?php echo e($bill->bill_reference); ?></td>
                            </tr>
                            <tr>
                                <th>Academic Year:</th>
                                <td><?php echo e($bill->academicYear->name); ?></td>
                            </tr>
                            <tr>
                                <th>Semester:</th>
                                <td><?php echo e($bill->semester->name); ?></td>
                            </tr>
                            <tr>
                                <th>Date Generated:</th>
                                <td><?php echo e($bill->created_at->format('d M, Y')); ?></td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bill->isReversed()): ?>
                                        <span class="badge bg-secondary">Reversed</span>
                                    <?php else: ?>
                                    <?php
                                        $status = $bill->getPaymentStatus();
                                    ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($status === 'paid'): ?>
                                        <span class="badge bg-success">Paid (100%)</span>
                                    <?php elseif($status === 'partial'): ?>
                                        <span class="badge bg-warning">Partially Paid (<?php echo e(number_format($bill->display_payment_percentage, 1)); ?>%)</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Unpaid (<?php echo e(number_format($bill->display_payment_percentage, 1)); ?>%)</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Bill Items -->
                <h5 class="border-bottom pb-2 mb-3">Fee Items</h5>
                <div class="table-responsive mb-4">
                    <table class="table table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Fee Type</th>
                                <th>Description</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $bill->billItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($index + 1); ?></td>
                                    <td><?php echo e($item->feeType->name); ?></td>
                                    <td><?php echo e($item->description ?? $item->feeType->description); ?></td>
                                    <td class="text-end">GH₵ <?php echo e(number_format($item->amount, 2)); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="4" class="text-center">No fee items found</td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                        <tfoot class="fw-bold">
                            <tr>
                                <td colspan="3" class="text-end">Total:</td>
                                <td class="text-end">GH₵ <?php echo e(number_format($bill->total_amount, 2)); ?></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end">Amount Paid:</td>
                                <td class="text-end text-success">GH₵ <?php echo e(number_format($totalPaid, 2)); ?></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end">Balance:</td>
                                <td class="text-end
                                    <?php if($bill->balance_display_type === 'credit'): ?> text-success
                                    <?php elseif($bill->balance_display_type === 'debit'): ?> text-danger
                                    <?php else: ?> text-body
                                    <?php endif; ?>">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bill->balance_display_type === 'credit'): ?>
                                        (GH₵ <?php echo e(number_format($bill->balance_display_amount, 2)); ?>)
                                    <?php else: ?>
                                        GH₵ <?php echo e(number_format($bill->balance_display_amount, 2)); ?>

                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Payment History -->
                <h5 class="border-bottom pb-2 mb-3">Payment History</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Receipt No.</th>
                                <th>Date</th>
                                <th>Payment Method</th>
                                <th>Reference</th>
                                <th>Recorded By</th>
                                <th class="text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($index + 1); ?></td>
                                    <td><?php echo e($payment->receipt_number); ?></td>
                                    <td><?php echo e($payment->payment_date->format('d M, Y')); ?></td>
                                    <td><?php echo e($payment->payment_method); ?></td>
                                    <td><?php echo e($payment->reference_number); ?></td>
                                    <td><?php echo e($payment->recordedBy->name ?? 'System'); ?></td>
                                    <td class="text-end">GH₵ <?php echo e(number_format($payment->amount, 2)); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="7" class="text-center">No payment records found</td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/livewire/finance/bill-detail-viewer.blade.php ENDPATH**/ ?>