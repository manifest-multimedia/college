<div>
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-warning text-dark">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="card-title mb-0">
                    <i class="fas fa-edit"></i> Edit Student Bill
                </h1>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bill): ?>
                    <a href="<?php echo e(route('finance.bill.view', ['id' => $bill->id])); ?>" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Bill
                    </a>
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

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo e(session('success')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($loading): ?>
                <div class="d-flex justify-content-center align-items-center p-5">
                    <div class="spinner-border text-warning" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <span class="ms-2">Loading bill for editing...</span>
                </div>
            <?php elseif(! $bill): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> Bill not found or has been deleted.
                </div>
            <?php else: ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bill->payments->isNotEmpty()): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        This bill has payments recorded. Updating the bill will recalculate balance: if total paid exceeds the new amount, balance will show in the student's favor; otherwise arrears will show.
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

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
                                <td><?php echo e($bill->billing_date?->format('d M, Y') ?? $bill->created_at?->format('d M, Y')); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($availableFees)): ?>
                    <?php
                        $feesCol = collect($availableFees)->map(fn ($f) => [
                            'id' => (int) ($f['id'] ?? 0),
                            'amount' => (float) ($f['amount'] ?? 0),
                            'is_mandatory' => $f['is_mandatory'] ?? false,
                        ]);
                        $mandatoryIds = $feesCol->where('is_mandatory', true)->pluck('id')->values()->toArray();
                        $userIds = array_values(array_unique(array_map('intval', $selectedFeeIds ?? [])));
                        $effectiveIds = array_values(array_unique(array_merge($mandatoryIds, $userIds)));
                        $computedTotal = $feesCol->whereIn('id', $effectiveIds)->sum('amount');
                    ?>

                    <div class="mb-3">
                        <label class="form-label">Select Fees to Include on this Bill</label>
                        <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $availableFees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="form-check mb-2">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        value="<?php echo e($fee['id']); ?>"
                                        wire:model.live="selectedFeeIds"
                                        id="editFee<?php echo e($fee['id']); ?>"
                                        <?php if($fee['is_mandatory']): ?> disabled checked <?php endif; ?>
                                    >
                                    <label class="form-check-label d-flex justify-content-between w-100" for="editFee<?php echo e($fee['id']); ?>">
                                        <span>
                                            <?php echo e($fee['fee_type']['name'] ?? 'Unknown Fee'); ?>

                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($fee['is_mandatory']): ?>
                                                <span class="badge bg-primary ms-2">Mandatory</span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($fee['applicable_gender']) && $fee['applicable_gender'] !== 'all'): ?>
                                                <span class="badge bg-info ms-1">
                                                    <?php echo e($fee['applicable_gender'] === 'female' ? 'Female only' : 'Male only'); ?>

                                                </span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </span>
                                        <strong>GH₵ <?php echo e(number_format($fee['amount'], 2)); ?></strong>
                                    </label>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Mandatory fees are automatically selected and cannot be removed. Only fees applicable to this student's gender are shown.
                        </small>
                    </div>

                    <div class="alert alert-info">
                        <strong>Total Selected:</strong>
                        GH₵ <?php echo e(number_format($computedTotal, 2)); ?>

                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i>
                        No configurable fee items are available for this bill's program, academic year and semester.
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <a href="<?php echo e(route('finance.billing')); ?>" class="btn btn-secondary">
                <i class="fas fa-list"></i> Back to Billing
            </a>
            <button
                type="button"
                class="btn btn-primary"
                wire:click="save"
                <?php if(! $canEdit || empty($availableFees)): ?> disabled <?php endif; ?>
            >
                <i class="fas fa-save"></i> Save Changes
            </button>
        </div>
    </div>
</div>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/livewire/finance/bill-editor.blade.php ENDPATH**/ ?>