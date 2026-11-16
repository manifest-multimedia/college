
<div>
    <div class="alert alert-warning mb-4">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>Deprecated:</strong> This page is deprecated. For student exam session tracking, please use the <strong>Exam Audit Tool</strong>.
    </div>
    
    <div class="card shadow-sm mb-4">
        <div class="card-header py-3">
            <div class="card-title">
                <h3 class="card-title">
                    <i class="bi bi-calendar-check me-2"></i>
                    Exam Sessions (Deprecated)
                </h3>
            </div>
        </div>
        
        <div class="card-body">
            <!-- Search and filters row -->
            <div class="row mb-4">
                <div class="col-md-6 mb-3 mb-md-0">
                    <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="bi bi-search"></i>
                        </span>
                        <input 
                            wire:model.live.debounce.300ms="search" 
                            type="text" 
                            class="form-control" 
                            placeholder="Search exam by course name..."
                        >
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="d-flex justify-content-md-end">
                        <select wire:model.live="perPage" class="form-select w-auto">
                            <option value="10">10 per page</option>
                            <option value="25">25 per page</option>
                            <option value="50">50 per page</option>
                            <option value="100">100 per page</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Exams table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th wire:click="sortBy('title')" class="cursor-pointer">
                                Title 
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sortField === 'title'): ?>
                                    <i class="bi bi-arrow-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?> ms-1"></i>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </th>
                            <th>Course</th>
                            <th wire:click="sortBy('date')" class="cursor-pointer">
                                Date 
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sortField === 'date'): ?>
                                    <i class="bi bi-arrow-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?> ms-1"></i>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </th>
                            <th>Duration</th>
                            <th wire:click="sortBy('status')" class="cursor-pointer">
                                Status 
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sortField === 'status'): ?>
                                    <i class="bi bi-arrow-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?> ms-1"></i>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </th>
                            <th>Active Sessions</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $examSessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exam): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="fw-medium"><?php echo e($exam->title); ?></td>
                                <td><?php echo e($exam->course->name); ?></td>
                                <td><?php echo e($exam->date ? date('M d, Y', strtotime($exam->date)) : 'Not scheduled'); ?></td>
                                <td><?php echo e($exam->duration); ?> minutes</td>
                                <td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($exam->status === 'active'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php elseif($exam->status === 'pending'): ?>
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    <?php elseif($exam->status === 'completed'): ?>
                                        <span class="badge bg-secondary">Completed</span>
                                    <?php elseif($exam->status === 'canceled'): ?>
                                        <span class="badge bg-danger">Canceled</span>
                                    <?php else: ?>
                                        <span class="badge bg-info"><?php echo e($exam->status); ?></span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-primary"><?php echo e($exam->sessions()->where('completed_at', null)->count()); ?></span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="<?php echo e(route('exams.show', $exam->slug ?? $exam->id)); ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> View Sessions
                                        </a>
                                        <a href="<?php echo e(route('exams.edit', $exam->slug ?? $exam->id)); ?>" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <div class="mb-3">
                                            <i class="bi bi-calendar-x fs-1 text-muted"></i>
                                        </div>
                                        <h5 class="text-muted mb-3">No exam sessions found</h5>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($search): ?>
                                            <p class="text-muted">Try adjusting your search criteria</p>
                                            <button wire:click="$set('search', '')" class="btn btn-outline-secondary btn-sm mt-2">
                                                <i class="bi bi-x me-1"></i> Clear search
                                            </button>
                                        <?php else: ?>
                                            <p class="text-muted">No exam sessions have been created yet</p>
                                            <a href="<?php echo e(route('admin.exams.create')); ?>" class="btn btn-primary btn-sm mt-2">
                                                <i class="bi bi-plus me-1"></i> Create Exam
                                            </a>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-4">
                <?php echo e($examSessions->links()); ?>

            </div>
        </div>
    </div>

    <style>
        .cursor-pointer {
            cursor: pointer;
        }
        
        .table th {
            font-weight: 600;
        }
    </style>
</div><?php /**PATH /home/johnsonsebire/www/college/resources/views/livewire/exam-sessions.blade.php ENDPATH**/ ?>