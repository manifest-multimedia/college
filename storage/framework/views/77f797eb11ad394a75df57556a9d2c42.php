<div><div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <div>
                        <h1 class="card-title mb-2">
                            <i class="fas fa-book-open me-2"></i>Course Registration
                        </h1>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($student): ?>
                            <p class="card-subtitle text-muted mb-1">
                                Student: <?php echo e($student->first_name); ?> <?php echo e($student->last_name); ?> (<?php echo e($student->student_id); ?>)
                            </p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentAcademicYear && $currentSemester): ?>
                                <p class="card-subtitle text-muted mb-0">
                                    Academic Year: <?php echo e($currentAcademicYear->name); ?> | Semester: <?php echo e($currentSemester->name); ?>

                                </p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Flash Messages -->
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i><?php echo e(session('success')); ?>

                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-times-circle me-2"></i><?php echo e(session('error')); ?>

                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$student): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>No student record found for your account. Please contact the administration.
                        </div>
                    <?php elseif(!$currentAcademicYear || !$currentSemester): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>No active academic year or semester found. Please contact the administration.
                        </div>
                    <?php else: ?>
                        <!-- Registration Deadline Status Banner -->
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentSemester): ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentSemester->isRegistrationExpired()): ?>
                                <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                                    <i class="fas fa-lock fa-2x me-3"></i>
                                    <div>
                                        <h5 class="alert-heading mb-1">Registration Closed</h5>
                                        <p class="mb-0">
                                            The course registration deadline for <strong><?php echo e($currentSemester->name); ?></strong> was <strong><?php echo e($currentSemester->formatted_registration_deadline); ?></strong>.
                                            New registrations or course changes can no longer be submitted through the student portal. If you need special clearance or late registration, please contact the Academic Office.
                                        </p>
                                    </div>
                                </div>
                            <?php elseif(! $currentSemester->isRegistrationStarted()): ?>
                                <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
                                    <i class="fas fa-calendar-alt fa-2x me-3"></i>
                                    <div>
                                        <h5 class="alert-heading mb-1">Registration Not Yet Open</h5>
                                        <p class="mb-0">
                                            Course registration for <strong><?php echo e($currentSemester->name); ?></strong> will officially open on <strong><?php echo e($currentSemester->registration_starts_at->format('M d, Y \a\t h:i A')); ?></strong>.
                                        </p>
                                    </div>
                                </div>
                            <?php elseif($currentSemester->hasRegistrationDeadline()): ?>
                                <div class="alert alert-primary d-flex align-items-center justify-content-between mb-4" role="alert">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-hourglass-half fa-2x me-3 text-primary"></i>
                                        <div>
                                            <h6 class="alert-heading mb-0 fw-bold">Course Registration Window Open</h6>
                                            <span class="text-muted small">
                                                Registration Deadline: <strong><?php echo e($currentSemester->formatted_registration_deadline); ?></strong>
                                                (<?php echo e(now()->diffForHumans($currentSemester->registration_deadline, ['syntax' => \Carbon\CarbonInterface::DIFF_RELATIVE_TO_NOW])); ?>)
                                            </span>
                                        </div>
                                    </div>
                                    <span class="badge bg-primary">Open</span>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <!-- Payment Status Card -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card border-left-<?php echo e($registrationMessageType === 'success' ? 'success' : ($registrationMessageType === 'danger' ? 'danger' : 'warning')); ?>">
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <i class="fas fa-credit-card me-2"></i>Registration Status
                                        </h5>
                                        <div class="alert alert-<?php echo e($registrationMessageType); ?> mb-0">
                                            <i class="fas fa-<?php echo e($registrationMessageType === 'success' ? 'check-circle' : ($registrationMessageType === 'danger' ? 'times-circle' : 'exclamation-triangle')); ?> me-2"></i>
                                            <?php echo e($registrationMessage); ?>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <i class="fas fa-chart-pie me-2"></i>Payment Progress
                                        </h5>
                                        <div class="progress mb-2">
                                            <div class="progress-bar bg-<?php echo e($paymentPercentage >= 60 ? 'success' : 'warning'); ?>" 
                                                 style="width: <?php echo e(min(100, $paymentPercentage)); ?>%">
                                                <?php echo e(number_format($paymentPercentage, 1)); ?>%
                                            </div>
                                        </div>
                                        <small class="text-muted">Required: 60% minimum for course registration</small>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($balanceDisplayType === 'credit'): ?>
                                            <p class="mb-0 mt-2 small"><strong>Balance:</strong> <span class="text-success fw-bold">(GH₵<?php echo e(number_format($balanceDisplayAmount, 2)); ?>)</span> credit</p>
                                        <?php elseif($balanceDisplayType === 'debit'): ?>
                                            <p class="mb-0 mt-2 small"><strong>Balance:</strong> <span class="text-danger fw-bold">GH₵<?php echo e(number_format($balanceDisplayAmount, 2)); ?></span> outstanding</p>
                                        <?php else: ?>
                                            <p class="mb-0 mt-2 small"><strong>Balance:</strong> <span class="text-body">GH₵0.00</span></p>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($registrationAllowed && $availableSubjects->isNotEmpty()): ?>
                            <!-- Course Selection Form -->
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-list-check me-2"></i>Available Subjects for Registration
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $availableSubjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="col-md-6 col-lg-4 mb-3">
                                                <div class="card h-100 subject-card <?php echo e(in_array($subject->id, $selectedSubjects) ? 'border-primary' : ''); ?>">
                                                    <div class="card-body">
                                                        <div class="form-check">
                                                            <input class="form-check-input" 
                                                                   type="checkbox" 
                                                                   wire:click="toggleSubject(<?php echo e($subject->id); ?>)"
                                                                   <?php echo e(in_array($subject->id, $selectedSubjects) ? 'checked' : ''); ?>

                                                                   id="subject-<?php echo e($subject->id); ?>">
                                                            <label class="form-check-label w-100" for="subject-<?php echo e($subject->id); ?>">
                                                                <h6 class="card-title"><?php echo e($subject->course_code ?? 'N/A'); ?></h6>
                                                                <h5 class="card-subtitle mb-2"><?php echo e($subject->name); ?></h5>
                                                                <p class="card-text">
                                                                    <small class="text-muted">
                                                                        <i class="fas fa-graduation-cap me-1"></i>
                                                                        <?php echo e($subject->credit_hours ?? 'N/A'); ?> Credit Hours
                                                                    </small>
                                                                </p>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($selectedSubjects) > 0): ?>
                                        <div class="mt-4 text-center">
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle me-2"></i>
                                                You have selected <?php echo e(count($selectedSubjects)); ?> subject(s) for registration.
                                            </div>
                                            <button type="button" 
                                                    wire:click="submitRegistration" 
                                                    class="btn btn-primary btn-lg"
                                                    wire:confirm="Are you sure you want to submit your course registration? This will replace any previous registrations for this semester.">
                                                <i class="fas fa-paper-plane me-2"></i>
                                                Submit Course Registration
                                            </button>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>

                        <?php elseif($registrationAllowed && $availableSubjects->isEmpty()): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                No subjects are available for registration in your class for this semester. Please contact the Academic Office.
                            </div>
                        <?php elseif($currentSemester && $currentSemester->isRegistrationExpired()): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-lock me-2"></i>
                                Course registration for this semester is closed as the deadline has passed (<?php echo e($currentSemester->formatted_registration_deadline); ?>).
                            </div>
                        <?php elseif($currentSemester && ! $currentSemester->isRegistrationStarted()): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-calendar-alt me-2"></i>
                                Course registration will open on <?php echo e($currentSemester->registration_starts_at->format('M d, Y \a\t h:i A')); ?>.
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Course registration is currently disabled. Please ensure you meet the payment requirements.
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <!-- Current/Previous Registrations -->
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($existingRegistrations->isNotEmpty()): ?>
                            <div class="card mt-4">
                                <div class="card-header bg-secondary text-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-history me-2"></i>Your Course Registrations
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Course Code</th>
                                                    <th>Subject Name</th>
                                                    <th>Credit Hours</th>
                                                    <th>Registration Date</th>
                                                    <th>Status</th>
                                                    <th>Approval Info</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $existingRegistrations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $registration): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <tr>
                                                        <td><?php echo e($registration->subject->course_code ?? 'N/A'); ?></td>
                                                        <td><?php echo e($registration->subject->name ?? 'N/A'); ?></td>
                                                        <td><?php echo e($registration->subject->credit_hours ?? 'N/A'); ?></td>
                                                        <td><?php echo e($registration->registered_at->format('M d, Y')); ?></td>
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
                                                                    Approved on <?php echo e($registration->approved_at->format('M d, Y')); ?>

                                                                </small>
                                                            <?php elseif($registration->rejected_at): ?>
                                                                <small class="text-danger">
                                                                    Rejected on <?php echo e($registration->rejected_at->format('M d, Y')); ?>

                                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($registration->rejection_reason): ?>
                                                                        <br><em><?php echo e($registration->rejection_reason); ?></em>
                                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                                </small>
                                                            <?php else: ?>
                                                                <small class="text-muted">Awaiting approval</small>
                                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.subject-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.subject-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.subject-card.border-primary {
    background-color: #f8f9ff;
}

.border-left-success {
    border-left: 4px solid #28a745 !important;
}

.border-left-warning {
    border-left: 4px solid #ffc107 !important;
}

.form-check-label {
    cursor: pointer;
}
</style></div><?php /**PATH /home/johnsonsebire/www/college/resources/views/livewire/student-course-registration.blade.php ENDPATH**/ ?>