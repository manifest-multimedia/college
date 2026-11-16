<div>
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h1 class="card-title">
                                <i class="fa fa-money-bill-wave me-2"></i> Fee Payment Manager
                            </h1>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Use this interface to record and manage student fee payments. Search for a student, select their bill, and record payments. You can update the bill amount even after payments are recorded (balance in favor or arrears will update), and reverse any payment if recorded by mistake.</p>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('message')): ?>
                            <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
                                <?php echo e(session('message')); ?>

                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('error')): ?>
                            <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
                                <?php echo e(session('error')); ?>

                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Search & Filters</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa fa-search"></i></span>
                                    <input type="text" class="form-control" placeholder="Search by name or student ID..." wire:model.live.debounce.300ms="search">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" wire:model.live="academicYearId">
                                    <option value="">-- Academic Year --</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $academicYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($year->id); ?>"><?php echo e($year->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" wire:model.live="semesterId">
                                    <option value="">-- Semester --</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $semesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $semester): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($semester->id); ?>">
                                            <?php echo e($semester->name); ?><?php echo e(empty($academicYearId) && $semester->academicYear ? ' (' . $semester->academicYear->name . ')' : ''); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" wire:model.live="cohortId">
                                    <option value="">-- Cohort --</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $cohorts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cohort): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($cohort->id); ?>"><?php echo e($cohort->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Students List -->
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Students</h5>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th class="px-3">Student ID</th>
                                        <th>Name</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr class="<?php echo e($loadedStudent && $loadedStudent->id === $student->id ? 'table-primary' : ''); ?>">
                                            <td class="px-3"><?php echo e($student->student_id); ?></td>
                                            <td><?php echo e($student->first_name); ?> <?php echo e($student->last_name); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary" wire:click="loadStudent(<?php echo e($student->id); ?>)">
                                                    <i class="fa fa-user"></i> Select
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="3" class="text-center">No students found</td>
                                        </tr>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="p-3">
                            <?php echo e($students->links()); ?>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Student Bill Details -->
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($loadedStudent): ?>
                                    <?php echo e($loadedStudent->first_name); ?> <?php echo e($loadedStudent->last_name); ?>'s Bill
                                <?php else: ?>
                                    Student Bill Details
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </h5>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($loadedBill): ?>
                                <div class="d-flex gap-2">
                                    <a href="<?php echo e(route('finance.bill.edit', ['billId' => $loadedBill->id])); ?>" class="btn btn-warning btn-sm" title="Update bill (fee items and amount)">
                                        <i class="fa fa-edit"></i> Edit Bill
                                    </a>
                                    <button class="btn btn-success" wire:click="openPaymentForm">
                                        <i class="fa fa-plus-circle"></i> Record Payment
                                    </button>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($loadedStudent && $loadedStudent->feeBills->isEmpty()): ?>
                            <div class="alert alert-warning">
                                <i class="fa fa-exclamation-circle"></i> No bills found for this student in the selected academic year and semester.
                            </div>
                        <?php elseif($loadedBill): ?>
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6>Bill #<?php echo e($loadedBill->id); ?></h6>
                                    <p class="mb-1"><strong>Academic Year:</strong> <?php echo e($loadedBill->academicYear->name); ?></p>
                                    <p class="mb-1"><strong>Semester:</strong> <?php echo e($loadedBill->semester->name); ?></p>
                                    <p class="mb-1"><strong>Bill Date:</strong> <?php echo e($loadedBill->created_at->format('M d, Y')); ?></p>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <div class="mb-1">
                                        <span class="fs-6">Total Bill Amount: </span>
                                        <span class="fs-5 fw-bold"><?php echo e(number_format($loadedBill->total_amount, 2)); ?></span>
                                    </div>
                                    <div class="mb-1">
                                        <span class="fs-6">Amount Paid: </span>
                                        <span class="fs-5 fw-bold text-success"><?php echo e(number_format($loadedBill->total_paid, 2)); ?></span>
                                    </div>
                                    <div>
                                        <span class="fs-6">Balance: </span>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($loadedBill->balance_display_type === 'credit'): ?>
                                            <span class="fs-5 fw-bold text-success">(GH₵<?php echo e(number_format($loadedBill->balance_display_amount, 2)); ?>)</span>
                                            <small class="text-muted">in favor of student</small>
                                        <?php elseif($loadedBill->balance_display_type === 'debit'): ?>
                                            <span class="fs-5 fw-bold text-danger">GH₵<?php echo e(number_format($loadedBill->balance_display_amount, 2)); ?></span>
                                            <small class="text-muted">arrears</small>
                                        <?php else: ?>
                                            <span class="fs-5 fw-bold text-body">GH₵<?php echo e(number_format($loadedBill->balance_display_amount, 2)); ?></span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <div class="mt-2">
                                        <span class="fs-6">Payment Status: </span>
                                        <?php
                                            $status = $loadedBill->getPaymentStatus();
                                        ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($status === 'paid'): ?>
                                            <span class="badge bg-success fs-6">PAID (100%)</span>
                                        <?php elseif($status === 'partial'): ?>
                                            <span class="badge bg-primary fs-6">PARTIAL (<?php echo e(number_format($loadedBill->display_payment_percentage, 1)); ?>%)</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger fs-6">UNPAID (<?php echo e(number_format($loadedBill->display_payment_percentage, 1)); ?>%)</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <!-- Payment History -->
                            <h6 class="mt-4">Payment History</h6>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($loadedBill->payments->isEmpty()): ?>
                                <div class="alert alert-info">
                                    No payments have been recorded for this bill.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Receipt #</th>
                                                <th>Method</th>
                                                <th>Amount</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $loadedBill->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <tr class="<?php echo e($payment->reversed_at ? 'table-secondary' : ''); ?>">
                                                    <td><?php echo e($payment->payment_date->format('M d, Y')); ?></td>
                                                    <td>
                                                        <?php echo e($payment->receipt_number); ?>

                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($payment->reversed_at): ?>
                                                            <span class="badge bg-secondary ms-1">Reversed</span>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </td>
                                                    <td><?php echo e($payment->payment_method); ?></td>
                                                    <td class="text-end"><?php echo e(number_format($payment->amount, 2)); ?></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-info" wire:click="viewPayment(<?php echo e($payment->id); ?>)">
                                                            <i class="fa fa-eye"></i> View
                                                        </button>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$payment->reversed_at): ?>
                                                            <button class="btn btn-sm btn-danger" wire:click="openReverseModal(<?php echo e($payment->id); ?>)">
                                                                <i class="fa fa-undo"></i> Reverse
                                                            </button>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php else: ?>
                            <div class="alert alert-info text-center py-5">
                                <i class="fa fa-info-circle fa-2x mb-3"></i>
                                <p>Select a student to view their bill details and record payments.</p>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                <!-- Recent Payments -->
                <div class="card mt-4">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Recent Payments</h5>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th class="px-3">Date</th>
                                        <th>Student</th>
                                        <th>Receipt #</th>
                                        <th>Amount</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $recentPayments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <tr class="align-middle">
                                            <td class="px-3"><?php echo e($payment->payment_date->format('M d, Y')); ?></td>
                                            <td><?php echo e($payment->student->first_name); ?> <?php echo e($payment->student->last_name); ?></td>
                                            <td><?php echo e($payment->receipt_number); ?></td>
                                            <td class="text-end"><?php echo e(number_format($payment->amount, 2)); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-info" wire:click="viewPayment(<?php echo e($payment->id); ?>)">
                                                    <i class="fa fa-eye"></i> View
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="5" class="text-center">No recent payments found</td>
                                        </tr>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Form Modal -->
    <div class="modal fade" id="paymentFormModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Record Fee Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($loadedStudent && $loadedBill): ?>
                        <div class="mb-3">
                            <p><strong>Student:</strong> <?php echo e($loadedStudent->first_name); ?> <?php echo e($loadedStudent->last_name); ?></p>
                            <p><strong>Bill Amount:</strong> <?php echo e(number_format($loadedBill->total_amount, 2)); ?></p>
                            <p><strong>Amount Paid:</strong> <?php echo e(number_format($loadedBill->total_paid, 2)); ?></p>
                            <p><strong>Balance:</strong>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($loadedBill->balance_display_type === 'credit'): ?>
                                    <span class="text-success fw-bold">(GH₵<?php echo e(number_format($loadedBill->balance_display_amount, 2)); ?>)</span> credit
                                <?php elseif($loadedBill->balance_display_type === 'debit'): ?>
                                    GH₵<?php echo e(number_format($loadedBill->balance_display_amount, 2)); ?>

                                <?php else: ?>
                                    GH₵<?php echo e(number_format($loadedBill->balance_display_amount, 2)); ?>

                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </p>
                        </div>
                        <hr>
                        <form>
                            <div class="mb-3">
                                <label for="paymentAmount" class="form-label">Payment Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">GH₵</span>
                                    <input type="number" step="0.01" class="form-control <?php $__errorArgs = ['paymentAmount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        id="paymentAmount" wire:model="paymentAmount">
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['paymentAmount'];
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

                            <div class="mb-3">
                                <label for="paymentMethod" class="form-label">Payment Method</label>
                                <select class="form-select <?php $__errorArgs = ['paymentMethod'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    id="paymentMethod" wire:model="paymentMethod">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $paymentMethods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </select>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['paymentMethod'];
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

                            <div class="mb-3">
                                <label for="referenceNumber" class="form-label">Reference Number</label>
                                <input type="text" class="form-control <?php $__errorArgs = ['referenceNumber'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    id="referenceNumber" wire:model="referenceNumber"
                                    placeholder="Bank transfer reference, cheque number, etc.">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['referenceNumber'];
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

                            <div class="mb-3">
                                <label for="paymentDate" class="form-label">Payment Date</label>
                                <input type="date" class="form-control <?php $__errorArgs = ['paymentDate'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    id="paymentDate" wire:model="paymentDate">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['paymentDate'];
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

                            <div class="mb-3">
                                <label for="note" class="form-label">Note</label>
                                <textarea class="form-control <?php $__errorArgs = ['note'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    id="note" wire:model="note" rows="2"></textarea>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['note'];
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
                        </form>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="recordPayment">
                        <i class="fa fa-save"></i> Record Payment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reverse Payment Modal -->
    <div class="modal fade" id="reversePaymentModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="fa fa-undo me-2"></i>Reverse Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">The bill balance will be updated and you can then record the correct payment. Please provide a reason for this reversal (it will be recorded on the receipt).</p>
                    <div class="mb-3">
                        <label for="reversalReason" class="form-label">Reason for reversal <span class="text-danger">*</span></label>
                        <textarea class="form-control <?php $__errorArgs = ['reversalReason'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            id="reversalReason" wire:model="reversalReason" rows="3"
                            placeholder="e.g. Wrong amount entered, duplicate entry, payment made by mistake..."></textarea>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['reversalReason'];
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="reversePayment">
                        <i class="fa fa-undo"></i> Confirm Reverse
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Details Modal -->
    <div class="modal fade" id="paymentDetailsModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Payment Receipt</h5>
                    <button type="button" class="btn-close" aria-label="Close" wire:click="closePaymentDetails"></button>
                </div>
                <div class="modal-body">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->selectedPayment): ?>
                        <div class="text-center mb-4 border-bottom pb-3">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->selectedPayment->reversed_at): ?>
                                <div class="alert alert-danger mb-2">
                                    <strong>This payment was reversed on <?php echo e($this->selectedPayment->reversed_at->format('F d, Y')); ?>.</strong>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->selectedPayment->reversal_reason): ?>
                                        <div class="mt-2"><strong>Reason:</strong> <?php echo e($this->selectedPayment->reversal_reason); ?></div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->selectedPayment->reversedBy): ?>
                                        <div class="mt-1 small">Reversed by: <?php echo e($this->selectedPayment->reversedBy->name); ?></div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <h4>PAYMENT RECEIPT</h4>
                            <h6>Receipt #: <?php echo e($this->selectedPayment->receipt_number); ?></h6>
                            <p class="mb-0">Date: <?php echo e($this->selectedPayment->payment_date->format('F d, Y')); ?></p>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Student:</strong></div>
                            <div class="col-sm-8"><?php echo e($this->selectedPayment->student->first_name); ?> <?php echo e($this->selectedPayment->student->last_name); ?></div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Student ID:</strong></div>
                            <div class="col-sm-8"><?php echo e($this->selectedPayment->student->student_id); ?></div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Academic Year:</strong></div>
                            <div class="col-sm-8"><?php echo e($this->selectedPayment->studentFeeBill->academicYear->name); ?></div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Semester:</strong></div>
                            <div class="col-sm-8"><?php echo e($this->selectedPayment->studentFeeBill->semester->name); ?></div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Amount:</strong></div>
                            <div class="col-sm-8 fw-bold">GH₵ <?php echo e(number_format($this->selectedPayment->amount, 2)); ?></div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Payment Method:</strong></div>
                            <div class="col-sm-8"><?php echo e($this->selectedPayment->payment_method); ?></div>
                        </div>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->selectedPayment->reference_number): ?>
                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Reference:</strong></div>
                                <div class="col-sm-8"><?php echo e($this->selectedPayment->reference_number); ?></div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->selectedPayment->note): ?>
                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Note:</strong></div>
                                <div class="col-sm-8"><?php echo e($this->selectedPayment->note); ?></div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Recorded By:</strong></div>
                            <div class="col-sm-8"><?php echo e($this->selectedPayment->recordedBy->name); ?></div>
                        </div>

                        <?php
                            $bill = $this->selectedPayment->studentFeeBill;
                            $overpayment = max(0, (float) $bill->amount_paid - (float) $bill->total_amount);
                        ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($overpayment > 0): ?>
                            <div class="alert alert-info mt-3 mb-0">
                                <strong>Overpayment on this bill:</strong> GH₵ <?php echo e(number_format($overpayment, 2)); ?>. This amount is recorded on the current bill. Applying credit to future bills is not yet supported.
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <div class="text-center mt-4">
                            <p><em>Thank you for your payment!</em></p>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            Loading payment details...
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closePaymentDetails">Close</button>
                    <button type="button" class="btn btn-primary" onclick="window.print()">
                        <i class="fa fa-print"></i> Print Receipt
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            const paymentFormModalEl = document.getElementById('paymentFormModal');
            const paymentDetailsModalEl = document.getElementById('paymentDetailsModal');
            const reversePaymentModalEl = document.getElementById('reversePaymentModal');
            const paymentFormModal = new bootstrap.Modal(paymentFormModalEl);
            const paymentDetailsModal = new bootstrap.Modal(paymentDetailsModalEl);
            const reversePaymentModal = new bootstrap.Modal(reversePaymentModalEl);

            window.Livewire.find('<?php echo e($_instance->getId()); ?>').on('show-payment-form', () => {
                paymentFormModal.show();
            });

            window.Livewire.find('<?php echo e($_instance->getId()); ?>').on('hide-payment-form', () => {
                paymentFormModal.hide();
            });

            window.Livewire.find('<?php echo e($_instance->getId()); ?>').on('show-reverse-modal', () => {
                reversePaymentModal.show();
            });

            window.Livewire.find('<?php echo e($_instance->getId()); ?>').on('hide-reverse-modal', () => {
                reversePaymentModal.hide();
            });

            window.Livewire.find('<?php echo e($_instance->getId()); ?>').on('show-payment-details', () => {
                paymentDetailsModal.show();
            });

            window.Livewire.find('<?php echo e($_instance->getId()); ?>').on('hide-payment-details', () => {
                paymentDetailsModal.hide();
            });

            paymentDetailsModalEl.addEventListener('hidden.bs.modal', () => {
                window.Livewire.find('<?php echo e($_instance->getId()); ?>').call('closePaymentDetails');
            });

            paymentFormModalEl.addEventListener('hidden.bs.modal', () => {
                window.Livewire.find('<?php echo e($_instance->getId()); ?>').call('closePaymentForm');
            });

            reversePaymentModalEl.addEventListener('hidden.bs.modal', () => {
                window.Livewire.find('<?php echo e($_instance->getId()); ?>').call('closeReverseModal');
            });
        });
    </script>

    <style>
        @media print {
            /* Hide everything except the receipt modal content */
            body * {
                visibility: hidden;
            }

            #paymentDetailsModal,
            #paymentDetailsModal * {
                visibility: visible;
            }

            #paymentDetailsModal {
                position: fixed;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                margin: 0;
                padding: 20px;
                background: white;
            }

            #paymentDetailsModal .modal-dialog {
                max-width: 100%;
                margin: 0;
            }

            #paymentDetailsModal .modal-content {
                border: none;
                box-shadow: none;
            }

            /* Hide modal header buttons and footer */
            #paymentDetailsModal .modal-header .btn-close,
            #paymentDetailsModal .modal-footer {
                display: none !important;
            }

            /* Optimize receipt content for printing */
            #paymentDetailsModal .modal-body {
                padding: 20px;
            }
        }
    </style>
</div>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/livewire/finance/fee-payment-manager.blade.php ENDPATH**/ ?>