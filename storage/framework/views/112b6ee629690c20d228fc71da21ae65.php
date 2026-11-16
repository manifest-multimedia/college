<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h1 class="card-title">
                            <i class="fa fa-chart-bar me-2"></i> Financial Reports
                        </h1>
                    </div>
                </div>
                <div class="card-body">
                    <p class="card-text">Generate financial reports for fee collections, outstanding fees, and payment summaries.</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Report Selection & Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Report Parameters</h5>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="reportType" class="form-label">Report Type</label>
                            <select id="reportType" class="form-select" wire:model.live="reportType">
                                <option value="fee_collection">Fee Collection Report</option>
                                <option value="outstanding_fees">Outstanding Fees Report</option>
                                <option value="payment_summary">Payment Summary Report</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="academicYearId" class="form-label">Academic Year</label>
                            <select id="academicYearId" class="form-select" wire:model.live="academicYearId">
                                <option value="">All Academic Years</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $academicYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($year->id); ?>"><?php echo e($year->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="semesterId" class="form-label">Semester</label>
                            <select id="semesterId" class="form-select" wire:model.live="semesterId">
                                <option value="">All Semesters</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $semesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $semester): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($semester->id); ?>">
                                        <?php echo e($semester->name); ?><?php echo e(empty($academicYearId) && $semester->academicYear ? ' (' . $semester->academicYear->name . ')' : ''); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="collegeClassId" class="form-label">Program</label>
                            <select id="collegeClassId" class="form-select" wire:model.live="collegeClassId">
                                <option value="">All Programs</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $collegeClasses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($class->id); ?>"><?php echo e($class->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="cohortId" class="form-label">Cohort</label>
                            <select id="cohortId" class="form-select" wire:model.live="cohortId">
                                <option value="">All Cohorts</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $cohorts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cohort): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($cohort->id); ?>"><?php echo e($cohort->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                        </div>
                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($reportType === 'fee_collection'): ?>
                        <div class="col-md-4">
                            <label for="feeTypeId" class="form-label">Fee Type</label>
                            <select id="feeTypeId" class="form-select" wire:model.live="feeTypeId">
                                <option value="">All Fee Types</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $feeTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($type->id); ?>"><?php echo e($type->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                        </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($reportType !== 'outstanding_fees'): ?>
                        <div class="col-md-<?php echo e($reportType === 'fee_collection' ? '2' : '4'); ?>">
                            <label for="startDate" class="form-label">Start Date</label>
                            <input type="date" id="startDate" class="form-control" wire:model.live="startDate">
                        </div>
                        
                        <div class="col-md-<?php echo e($reportType === 'fee_collection' ? '2' : '4'); ?>">
                            <label for="endDate" class="form-label">End Date</label>
                            <input type="date" id="endDate" class="form-control" wire:model.live="endDate">
                        </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <label for="exportFormat" class="form-label">Export Format</label>
                            <div class="d-flex">
                                <div class="form-check me-3">
                                    <input class="form-check-input" type="radio" name="exportFormat" id="formatExcel" value="excel" wire:model="exportFormat" checked>
                                    <label class="form-check-label" for="formatExcel">
                                        Excel
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="exportFormat" id="formatPdf" value="pdf" wire:model="exportFormat">
                                    <label class="form-check-label" for="formatPdf">
                                        PDF
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-8 d-flex align-items-end justify-content-end">
                            <button type="button" class="btn btn-primary" wire:click="generateReport" wire:loading.attr="disabled">
                                <i class="fa fa-file-export me-1"></i>
                                <span wire:loading.remove wire:target="generateReport">Export Report</span>
                                <span wire:loading wire:target="generateReport">Processing...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Report Preview -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Report Preview</h5>
                    </div>
                </div>
                <div class="card-body">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('message')): ?>
                        <div class="alert alert-success">
                            <?php echo e(session('message')); ?>

                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('error')): ?>
                        <div class="alert alert-danger">
                            <?php echo e(session('error')); ?>

                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($reportType === 'fee_collection' && $reportData && count($reportData)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Receipt #</th>
                                        <th>Student</th>
                                        <th>Academic Year</th>
                                        <th>Semester</th>
                                        <th>Payment Method</th>
                                        <th>Reference</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $reportData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($payment->payment_date->format('Y-m-d')); ?></td>
                                            <td><?php echo e($payment->receipt_number); ?></td>
                                            <td><?php echo e($payment->student->first_name); ?> <?php echo e($payment->student->last_name); ?></td>
                                            <td><?php echo e($payment->studentFeeBill->academicYear->name ?? ''); ?></td>
                                            <td><?php echo e($payment->studentFeeBill->semester->name ?? ''); ?></td>
                                            <td><?php echo e($payment->payment_method); ?></td>
                                            <td><?php echo e($payment->reference_number); ?></td>
                                            <td class="text-end"><?php echo e(number_format($payment->amount, 2)); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="7" class="text-end">Total:</th>
                                        <th class="text-end"><?php echo e(number_format($reportData->sum('amount'), 2)); ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <div class="d-flex justify-content-center mt-3">
                            <?php echo e($reportData->links()); ?>

                        </div>
                    <?php elseif($reportType === 'outstanding_fees' && $reportData && count($reportData)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>Student ID</th>
                                        <th>Student Name</th>
                                        <th>Program</th>
                                        <th>Academic Year</th>
                                        <th>Semester</th>
                                        <th>Total Bill</th>
                                        <th>Paid</th>
                                        <th>Balance</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $reportData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bill): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($bill->student->student_id ?? 'N/A'); ?></td>
                                            <td><?php echo e($bill->student->first_name); ?> <?php echo e($bill->student->last_name); ?></td>
                                            <td><?php echo e($bill->student->collegeClass->name ?? 'N/A'); ?></td>
                                            <td><?php echo e($bill->academicYear->name); ?></td>
                                            <td><?php echo e($bill->semester->name); ?></td>
                                            <td class="text-end"><?php echo e(number_format($bill->total_amount, 2)); ?></td>
                                            <td class="text-end"><?php echo e(number_format($bill->total_paid, 2)); ?></td>
                                            <td class="text-end">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bill->balance_display_type === 'credit'): ?>
                                                    <span class="text-success fw-bold">(GH₵<?php echo e(number_format($bill->balance_display_amount, 2)); ?>)</span>
                                                <?php elseif($bill->balance_display_type === 'debit'): ?>
                                                    <span class="text-danger fw-bold">GH₵<?php echo e(number_format($bill->balance_display_amount, 2)); ?></span>
                                                <?php else: ?>
                                                    GH₵<?php echo e(number_format($bill->balance_display_amount, 2)); ?>

                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                    $status = $bill->getPaymentStatus();
                                                ?>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($status === 'paid'): ?>
                                                    <span class="badge bg-success">PAID (100%)</span>
                                                <?php elseif($status === 'partial'): ?>
                                                    <span class="badge bg-primary">PARTIAL (<?php echo e(number_format($bill->display_payment_percentage, 1)); ?>%)</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">UNPAID (<?php echo e(number_format($bill->display_payment_percentage, 1)); ?>%)</span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="5" class="text-end">Totals:</th>
                                        <th class="text-end"><?php echo e(number_format($reportData->sum('total_amount'), 2)); ?></th>
                                        <th class="text-end"><?php echo e(number_format($reportData->sum('total_paid'), 2)); ?></th>
                                        <th class="text-end"><?php echo e(number_format($reportData->sum('balance'), 2)); ?></th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <div class="d-flex justify-content-center mt-3">
                            <?php echo e($reportData->links()); ?>

                        </div>
                    <?php elseif($reportType === 'payment_summary' && $reportData && count($reportData)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Number of Payments</th>
                                        <th>Total Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $reportData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $summary): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e(\Carbon\Carbon::parse($summary->payment_date)->format('Y-m-d')); ?></td>
                                            <td class="text-center"><?php echo e($summary->payment_count); ?></td>
                                            <td class="text-end"><?php echo e(number_format($summary->total_amount, 2)); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th class="text-end">Totals:</th>
                                        <th class="text-center"><?php echo e($reportData->sum('payment_count')); ?></th>
                                        <th class="text-end"><?php echo e(number_format($reportData->sum('total_amount'), 2)); ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <div class="d-flex justify-content-center mt-3">
                            <?php echo e($reportData->links()); ?>

                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center">
                            <i class="fa fa-info-circle fa-2x mb-3"></i>
                            <p>Select your report parameters and click 'Export Report' to generate a report.</p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/livewire/finance/financial-reports-manager.blade.php ENDPATH**/ ?>