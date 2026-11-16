<div>
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title text-white"><?php echo e($stats['total']); ?></h4>
                            <p class="card-text">Total Registrations</p>
                        </div>
                        <div>
                            <i class="text-white fas fa-list fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title text-white"><?php echo e($stats['pending']); ?></h4>
                            <p class="card-text">Pending Approval</p>
                        </div>
                        <div>
                            <i class="text-white fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title text-white"><?php echo e($stats['approved']); ?></h4>
                            <p class="card-text">Approved</p>
                        </div>
                        <div>
                            <i class="text-white fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title text-white"><?php echo e($stats['rejected']); ?></h4>
                            <p class="card-text">Rejected</p>
                        </div>
                        <div>
                            <i class="text-white fas fa-times-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="text-white fas fa-filter"></i> Filters & Search
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="academicYear">Academic Year</label>
                        <select wire:model.live="selectedAcademicYear" class="form-control">
                            <option value="">-- All Academic Years --</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $academicYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($year->id); ?>"><?php echo e($year->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="semester">Semester</label>
                        <select wire:model.live="selectedSemester" class="form-control">
                            <option value="">-- All Semesters --</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $semesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $semester): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($semester->id); ?>">
                                    <?php echo e($semester->name); ?><?php echo e(empty($selectedAcademicYear) && $semester->academicYear ? ' (' . $semester->academicYear->name . ')' : ''); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select wire:model.live="filterStatus" class="form-control">
                            <option value="pending">Pending Approval</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="all">All</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="search">Search</label>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control" 
                               placeholder="Student name, ID, or course...">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Course Registrations Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="text-white fas fa-book-open"></i> Course Registration Approvals
            </h5>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($stats['pending'] > 0): ?>
                <button type="button" class="btn btn-success btn-sm" onclick="bulkApproveSelected()">
                    <i class="text-white fas fa-check"></i> Bulk Approve Selected
                </button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <div class="card-body">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($registrations->count() > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-light">
                            <tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($filterStatus === 'pending'): ?>
                                    <th width="3%">
                                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                    </th>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <th width="12%">Student ID</th>
                                <th width="20%">Student Name</th>
                                <th width="15%">Course Code</th>
                                <th width="25%">Course Name</th>
                                <th width="10%">Credits</th>
                                <th width="10%">Payment %</th>
                                <th width="12%">Registered On</th>
                                <th width="10%">Status</th>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($filterStatus === 'pending'): ?>
                                    <th width="15%">Actions</th>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $registrations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $registration): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($filterStatus === 'pending'): ?>
                                        <td>
                                            <input type="checkbox" class="registration-checkbox" 
                                                   value="<?php echo e($registration->id); ?>">
                                        </td>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <td><?php echo e($registration->student->student_id ?? 'N/A'); ?></td>
                                    <td><?php echo e($registration->student->full_name ?? 'N/A'); ?></td>
                                    <td><?php echo e($registration->subject->course_code ?? 'N/A'); ?></td>
                                    <td><?php echo e($registration->subject->name ?? 'N/A'); ?></td>
                                    <td><?php echo e($registration->subject->credit_hours ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="badge badge-info">
                                            <?php echo e(number_format($registration->payment_percentage_at_registration, 1)); ?>%
                                        </span>
                                    </td>
                                    <td><?php echo e($registration->registered_at->format('M d, Y')); ?></td>
                                    <td>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($registration->is_approved): ?>
                                            <span class="badge badge-success">
                                                <i class="text-white fas fa-check"></i> Approved
                                            </span>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($registration->approved_at): ?>
                                                <small class="d-block text-muted">
                                                    <?php echo e($registration->approved_at->format('M d, Y')); ?>

                                                </small>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php elseif($registration->rejected_at): ?>
                                            <span class="badge badge-danger">
                                                <i class="text-white fas fa-times"></i> Rejected
                                            </span>
                                            <small class="d-block text-muted">
                                                <?php echo e($registration->rejected_at->format('M d, Y')); ?>

                                            </small>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($registration->rejection_reason): ?>
                                                <small class="d-block text-muted" title="<?php echo e($registration->rejection_reason); ?>">
                                                    <?php echo e(Str::limit($registration->rejection_reason, 30)); ?>

                                                </small>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge badge-warning">
                                                <i class="text-white fas fa-clock"></i> Pending
                                            </span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($filterStatus === 'pending' && !$registration->is_approved && !$registration->rejected_at): ?>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" 
                                                        wire:click="approveRegistration(<?php echo e($registration->id); ?>)"
                                                        class="btn btn-success btn-sm"
                                                        wire:confirm="Are you sure you want to approve this registration?">
                                                    <i class="text-white fas fa-check"></i>
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-danger btn-sm"
                                                        onclick="showRejectModal(<?php echo e($registration->id); ?>, '<?php echo e($registration->student->full_name); ?>', '<?php echo e($registration->subject->name); ?>')">
                                                    <i class="text-white fas fa-times"></i>
                                                </button>
                                            </div>
                                        </td>
                                    <?php elseif($filterStatus !== 'pending'): ?>
                                        <!-- Show who approved/rejected if not in pending view -->
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        Showing <?php echo e($registrations->firstItem()); ?> to <?php echo e($registrations->lastItem()); ?> 
                        of <?php echo e($registrations->total()); ?> results
                    </div>
                    <div>
                        <?php echo e($registrations->links()); ?>

                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="text-white fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No course registrations found</h5>
                    <p class="text-muted">Try adjusting your filters or search criteria.</p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <!-- Rejection Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalLabel">Reject Course Registration</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to reject the course registration for:</p>
                    <div class="alert alert-info">
                        <strong>Student:</strong> <span id="rejectStudentName"></span><br>
                        <strong>Course:</strong> <span id="rejectCourseName"></span>
                    </div>
                    <div class="form-group">
                        <label for="rejectionReason">Reason for Rejection (Optional)</label>
                        <textarea class="form-control" id="rejectionReason" rows="3" 
                                  placeholder="Enter reason for rejection..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="confirmReject()">
                        <i class="text-white fas fa-times"></i> Reject Registration
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    let currentRejectId = null;

    function showRejectModal(id, studentName, courseName) {
        currentRejectId = id;
        document.getElementById('rejectStudentName').textContent = studentName;
        document.getElementById('rejectCourseName').textContent = courseName;
        document.getElementById('rejectionReason').value = '';
        $('#rejectModal').modal('show');
    }

    function confirmReject() {
        if (currentRejectId) {
            const reason = document.getElementById('rejectionReason').value;
            window.Livewire.find('<?php echo e($_instance->getId()); ?>').rejectRegistration(currentRejectId, reason);
            $('#rejectModal').modal('hide');
        }
    }

    function toggleSelectAll() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.registration-checkbox');
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
    }

    function bulkApproveSelected() {
        const checkboxes = document.querySelectorAll('.registration-checkbox:checked');
        const selectedIds = Array.from(checkboxes).map(cb => cb.value);
        
        if (selectedIds.length === 0) {
            alert('Please select at least one registration to approve.');
            return;
        }
        
        if (confirm(`Are you sure you want to approve ${selectedIds.length} registration(s)?`)) {
            window.Livewire.find('<?php echo e($_instance->getId()); ?>').bulkApprove(selectedIds);
        }
    }

    // Listen for registration updates and refresh stats
    Livewire.on('registration-updated', () => {
        // Reset checkboxes
        document.getElementById('selectAll').checked = false;
        document.querySelectorAll('.registration-checkbox').forEach(cb => cb.checked = false);
    });
</script>
<?php $__env->stopPush(); ?><?php /**PATH /home/johnsonsebire/www/college/resources/views/livewire/finance/course-registration-approvals.blade.php ENDPATH**/ ?>