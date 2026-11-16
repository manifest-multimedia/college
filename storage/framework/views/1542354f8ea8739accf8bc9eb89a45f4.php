<div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$student): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>No student record found for your account. Please contact the administration.
        </div>
    <?php else: ?>
        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="academicYear">Filter by Academic Year</label>
                    <select wire:model.live="selectedAcademicYear" class="form-control">
                        <option value="">All Academic Years</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $academicYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($year->id); ?>"><?php echo e($year->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="semester">Filter by Semester</label>
                    <select wire:model.live="selectedSemester" class="form-control">
                        <option value="">All Semesters</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $semesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $semester): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($semester->id); ?>">
                                <?php echo e($semester->name); ?><?php echo e(empty($selectedAcademicYear) && $semester->academicYear ? ' (' . $semester->academicYear->name . ')' : ''); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Registration History Table -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($registrations->count() > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>Academic Year</th>
                            <th>Semester</th>
                            <th>Course Code</th>
                            <th>Subject Name</th>
                            <th>Credit Hours</th>
                            <th>Registration Date</th>
                            <th>Status</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $registrations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $registration): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($registration->academicYear->name ?? 'N/A'); ?></td>
                                <td><?php echo e($registration->semester->name ?? 'N/A'); ?></td>
                                <td><?php echo e($registration->subject->course_code ?? 'N/A'); ?></td>
                                <td><?php echo e($registration->subject->name ?? 'N/A'); ?></td>
                                <td><?php echo e($registration->subject->credit_hours ?? 'N/A'); ?></td>
                                <td><?php echo e($registration->registered_at->format('M d, Y g:i A')); ?></td>
                                <td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($registration->is_approved): ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check me-1"></i>Approved
                                        </span>
                                    <?php elseif($registration->rejected_at): ?>
                                        <span class="badge bg-danger">
                                            <i class="fas fa-times me-1"></i>Rejected
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">
                                            <i class="fas fa-clock me-1"></i>Pending
                                        </span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($registration->is_approved && $registration->approved_at): ?>
                                        <small class="text-success">
                                            <strong>Approved:</strong><br>
                                            <?php echo e($registration->approved_at->format('M d, Y g:i A')); ?>

                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($registration->approvedBy): ?>
                                                <br>by <?php echo e($registration->approvedBy->name); ?>

                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </small>
                                    <?php elseif($registration->rejected_at): ?>
                                        <small class="text-danger">
                                            <strong>Rejected:</strong><br>
                                            <?php echo e($registration->rejected_at->format('M d, Y g:i A')); ?>

                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($registration->rejectedBy): ?>
                                                <br>by <?php echo e($registration->rejectedBy->name); ?>

                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($registration->rejection_reason): ?>
                                                <br><strong>Reason:</strong> <?php echo e($registration->rejection_reason); ?>

                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </small>
                                    <?php else: ?>
                                        <small class="text-muted">
                                            Awaiting approval from Finance Department
                                        </small>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Showing <?php echo e($registrations->firstItem()); ?> to <?php echo e($registrations->lastItem()); ?> 
                    of <?php echo e($registrations->total()); ?> registrations
                </div>
                <div>
                    <?php echo e($registrations->links()); ?>

                </div>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No course registrations found</h5>
                <p class="text-muted">You haven't registered for any courses yet.</p>
                <a href="<?php echo e(route('courseregistration')); ?>" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Register for Courses
                </a>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH /home/johnsonsebire/www/college/resources/views/livewire/student-registration-history.blade.php ENDPATH**/ ?>