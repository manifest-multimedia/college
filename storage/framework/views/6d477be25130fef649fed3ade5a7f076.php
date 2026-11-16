<div>
    <div class="row">
        <div class="col-12">
            <!-- Student Information Card -->
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($student): ?>
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-graduate me-2"></i>Student Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Student ID:</strong> <?php echo e($student->student_id); ?></p>
                            <p><strong>Name:</strong> <?php echo e($student->first_name); ?> <?php echo e($student->last_name); ?></p>
                            <p><strong>Program:</strong> <?php echo e($student->collegeClass->name ?? 'N/A'); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Academic Year:</strong> <?php echo e($currentAcademicYear->name ?? 'N/A'); ?></p>
                            <p><strong>Semester:</strong> <?php echo e($currentSemester->name ?? 'N/A'); ?></p>
                            <p><strong>Status:</strong> 
                                <span class="badge badge-<?php echo e($registrationMessageType == 'success' ? 'success' : ($registrationMessageType == 'warning' ? 'warning' : 'danger')); ?>">
                                    <?php echo e($student->status ?? 'Active'); ?>

                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <!-- Registration Eligibility Status -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="alert alert-<?php echo e($registrationMessageType); ?> mb-0">
                        <i class="fas fa-<?php echo e($registrationMessageType == 'success' ? 'check-circle' : ($registrationMessageType == 'warning' ? 'exclamation-triangle' : 'times-circle')); ?> me-2"></i>
                        <?php echo e($registrationMessage); ?>

                    </div>
                </div>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($registrationAllowed && $availableSubjects->isNotEmpty()): ?>
                <!-- Course Selection Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-book me-2"></i>Available Courses for Registration
                        </h5>
                    </div>
                    <div class="card-body">
                        <form wire:submit.prevent="submitRegistration">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="5%">Select</th>
                                            <th width="15%">Course Code</th>
                                            <th width="45%">Course Title</th>
                                            <th width="10%">Credits</th>
                                            <th width="25%">Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $availableSubjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td class="text-center">
                                                    <input type="checkbox" 
                                                           wire:model="selectedSubjects" 
                                                           value="<?php echo e($subject->id); ?>" 
                                                           class="form-check-input" 
                                                           id="subject-<?php echo e($subject->id); ?>">
                                                </td>
                                                <td>
                                                    <label for="subject-<?php echo e($subject->id); ?>" class="form-label mb-0 fw-bold">
                                                        <?php echo e($subject->course_code); ?>

                                                    </label>
                                                </td>
                                                <td>
                                                    <label for="subject-<?php echo e($subject->id); ?>" class="form-label mb-0">
                                                        <?php echo e($subject->name); ?>

                                                    </label>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-info"><?php echo e($subject->credit_hours ?? 3); ?></span>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <?php echo e(Str::limit($subject->description ?? 'Course Description', 50)); ?>

                                                    </small>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['selectedSubjects'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="alert alert-danger mt-3">
                                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo e($message); ?>

                                </div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <!-- Registration Summary -->
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title">Registration Summary</h6>
                                            <p class="mb-1"><strong>Selected Courses:</strong> <?php echo e(count($selectedSubjects)); ?></p>
                                            <p class="mb-0"><strong>Total Credits:</strong> 
                                                <span class="badge badge-primary"><?php echo e($totalCredits); ?></span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 d-flex align-items-end">
                                    <div class="w-100">
                                        <button type="submit" 
                                                class="btn btn-success btn-lg w-100" 
                                                <?php echo e(count($selectedSubjects) > 0 ? '' : 'disabled'); ?>>
                                            <i class="fas fa-paper-plane me-2"></i>
                                            Submit Course Registration
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            <?php elseif($registrationAllowed && $availableSubjects->isEmpty()): ?>
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Courses Available</h5>
                        <p class="text-muted">There are no courses available for registration in the current semester. Please contact the academic office.</p>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <!-- Current Registrations -->
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentRegistrations->isNotEmpty()): ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list me-2"></i>Your Current Course Registrations
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Course Code</th>
                                        <th>Course Title</th>
                                        <th>Credits</th>
                                        <th>Registration Date</th>
                                        <th>Status</th>
                                        <th>Approval Info</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $currentRegistrations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $registration): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($registration->subject->course_code ?? 'N/A'); ?></td>
                                            <td><?php echo e($registration->subject->name ?? 'N/A'); ?></td>
                                            <td class="text-center">
                                                <span class="badge badge-info"><?php echo e($registration->subject->credit_hours ?? 3); ?></span>
                                            </td>
                                            <td><?php echo e($registration->registered_at->format('M d, Y')); ?></td>
                                            <td>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($registration->is_approved): ?>
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check me-1"></i>Approved
                                                    </span>
                                                <?php elseif($registration->rejected_at): ?>
                                                    <span class="badge badge-danger">
                                                        <i class="fas fa-times me-1"></i>Rejected
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-clock me-1"></i>Pending
                                                    </span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($registration->is_approved && $registration->approved_at): ?>
                                                    <small class="text-success">
                                                        <i class="fas fa-check-circle me-1"></i>
                                                        Approved on <?php echo e($registration->approved_at->format('M d, Y')); ?>

                                                    </small>
                                                <?php elseif($registration->rejected_at): ?>
                                                    <small class="text-danger">
                                                        <i class="fas fa-times-circle me-1"></i>
                                                        Rejected on <?php echo e($registration->rejected_at->format('M d, Y')); ?>

                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($registration->rejection_reason): ?>
                                                            <br><em class="text-muted"><?php echo e($registration->rejection_reason); ?></em>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </small>
                                                <?php else: ?>
                                                    <small class="text-muted">
                                                        <i class="fas fa-hourglass-half me-1"></i>
                                                        Awaiting Finance Department approval
                                                    </small>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Total Registered Courses:</strong> <?php echo e($currentRegistrations->count()); ?>

                                    <br>
                                    <strong>Total Credits:</strong> <?php echo e($currentRegistrations->sum(fn($reg) => $reg->subject->credit_hours ?? 3)); ?>

                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Note:</strong> Course registrations require approval from the Finance Department. 
                                    You will be notified once your registration is processed.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('success')): ?>
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
            <i class="fas fa-times-circle me-2"></i>
            <?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/livewire/student/course-registration.blade.php ENDPATH**/ ?>