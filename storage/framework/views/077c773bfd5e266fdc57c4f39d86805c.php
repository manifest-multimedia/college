<?php if (isset($component)) { $__componentOriginal78458ab11901cb5e12c9728346766652 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78458ab11901cb5e12c9728346766652 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.default','data' => ['title' => 'Student Dashboard']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.default'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Student Dashboard']); ?>
    <div class="row">
        <div class="col-12">
            <!-- Welcome Header -->
            <div class="card mb-5">
                <div class="card-body p-8" style="background: linear-gradient(90deg, #20AA3E 0%, #03A588 100%);">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="text-white">
                                <h2 class="text-white fw-bold mb-2">Welcome, <?php echo e(Auth::user()->name); ?>!</h2>
                                <p class="mb-3 opacity-75">Student Portal - Access your academic information, view grades, and manage your studies</p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($student): ?>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="text-white-50 fw-semibold">Student ID</div>
                                            <div class="text-white fw-bold"><?php echo e($student->student_id ?? 'Not Assigned'); ?></div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-white-50 fw-semibold">Program</div>
                                            <div class="text-white fw-bold"><?php echo e($student->collegeClass->name ?? 'Not Assigned'); ?></div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-white-50 fw-semibold">Status</div>
                                            <div class="text-white fw-bold"><?php echo e(ucfirst($student->status ?? 'Active')); ?></div>
                                        </div>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <i class="fas fa-user-graduate text-white" style="font-size: 4rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-info d-flex justify-content-between align-items-center flex-wrap gap-3">
                <span><strong>Tutor Evaluation:</strong> Review your lecturers anonymously during an open semester evaluation.</span>
                <a class="btn btn-primary btn-sm" href="<?php echo e(route('student.tutor-evaluations')); ?>">View Tutor Evaluations</a>
            </div>

            <!-- Quick Stats -->
            <div class="row g-5 mb-5">
                <div class="col-xl-3 col-md-6">
                    <div class="card card-stretch">
                        <div class="card-header border-0 bg-primary text-white">
                            <h3 class="card-title fw-bold text-white">Enrolled Courses</h3>
                        </div>
                        <div class="card-body text-center">
                            <h1 class="text-primary fw-bold"><?php echo e($enrolledCourses); ?></h1>
                            <div class="text-muted">This Semester</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6">
                    <div class="card card-stretch">
                        <div class="card-header border-0 bg-success text-white">
                            <h3 class="card-title fw-bold text-white">Fee Payment</h3>
                        </div>
                        <div class="card-body text-center">
                            <h1 class="text-success fw-bold"><?php echo e(number_format($paymentPercentage, 1)); ?>%</h1>
                            <div class="text-muted">Current Semester</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6">
                    <div class="card card-stretch">
                        <div class="card-header border-0 bg-info text-white">
                            <h3 class="card-title fw-bold text-white">Exams Taken</h3>
                        </div>
                        <div class="card-body text-center">
                            <h1 class="text-info fw-bold"><?php echo e($examsTaken); ?></h1>
                            <div class="text-muted">This Semester</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6">
                    <div class="card card-stretch">
                        <div class="card-header border-0 bg-warning text-white">
                            <h3 class="card-title fw-bold text-white">Outstanding Balance</h3>
                        </div>
                        <div class="card-body text-center">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($balanceDisplayType ?? 'zero') === 'credit'): ?>
                                <h1 class="fw-bold text-success">(GH₵<?php echo e(number_format($balanceDisplayAmount ?? 0, 2)); ?>)</h1>
                                <div class="text-muted small">Credit in your favor</div>
                            <?php elseif(($balanceDisplayType ?? 'zero') === 'debit'): ?>
                                <h1 class="fw-bold text-danger">GH₵<?php echo e(number_format($balanceDisplayAmount ?? 0, 2)); ?></h1>
                                <div class="text-muted">Current Semester</div>
                            <?php else: ?>
                                <h1 class="fw-bold text-body">GH₵<?php echo e(number_format($balanceDisplayAmount ?? 0, 2)); ?></h1>
                                <div class="text-muted">Current Semester</div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row g-5 mb-5">
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-book-open me-2"></i>Course Registration
                            </h3>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($currentSemester) && $currentSemester->hasRegistrationDeadline()): ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentSemester->isRegistrationExpired()): ?>
                                    <span class="badge bg-danger">Closed</span>
                                <?php elseif(! $currentSemester->isRegistrationStarted()): ?>
                                    <span class="badge bg-info">Upcoming</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Open</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div class="card-body">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($currentSemester) && $currentSemester->isRegistrationExpired()): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-lock me-2"></i>
                                    Registration closed on <?php echo e($currentSemester->formatted_registration_deadline); ?>.
                                </div>
                                <p class="text-muted small">New course registrations for this semester are no longer accepted.</p>
                                <a href="<?php echo e(route('courseregistration')); ?>" class="btn btn-outline-secondary">
                                    <i class="fas fa-eye me-2"></i>View Registered Courses
                                </a>
                            <?php elseif(isset($currentSemester) && ! $currentSemester->isRegistrationStarted()): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-calendar-alt me-2"></i>
                                    Registration opens on <?php echo e($currentSemester->registration_starts_at->format('M d, Y \a\t h:i A')); ?>.
                                </div>
                                <p class="text-muted small">Course registration will become available once the registration window opens.</p>
                            <?php elseif($paymentPercentage >= 60): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    You are eligible for course registration (<?php echo e(number_format($paymentPercentage, 1)); ?>% fees paid)<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($balanceDisplayType ?? 'zero') === 'credit'): ?> — credit (GH₵<?php echo e(number_format($balanceDisplayAmount ?? 0, 2)); ?>)<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($currentSemester) && $currentSemester->hasRegistrationDeadline()): ?>
                                    <p class="text-muted small mb-3">
                                        <i class="fas fa-clock me-1 text-warning"></i>
                                        Deadline: <strong><?php echo e($currentSemester->formatted_registration_deadline); ?></strong>
                                        (<?php echo e(now()->diffForHumans($currentSemester->registration_deadline, ['syntax' => \Carbon\CarbonInterface::DIFF_RELATIVE_TO_NOW])); ?>)
                                    </p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <a href="<?php echo e(route('courseregistration')); ?>" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Register for Courses
                                </a>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    You need to pay at least 60% of your fees to register for courses. 
                                    Current payment: <?php echo e(number_format($paymentPercentage, 1)); ?>%
                                </div>
                                <p class="text-muted">Please visit the Finance Department to make payments.</p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-file-invoice-dollar me-2"></i>Financial Status
                            </h3>
                        </div>
                        <div class="card-body">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($paymentPercentage >= 100): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    All fees paid - You're eligible for exams
                                </div>
                            <?php elseif($paymentPercentage >= 60): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Partial payment completed - Course registration available
                                </div>
                            <?php else: ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-times-circle me-2"></i>
                                    Payment below 60% - Limited access to services
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($balanceDisplayType ?? 'zero') === 'debit'): ?>
                                <p><strong>Outstanding Balance:</strong> <span class="text-danger fw-bold">GH₵<?php echo e(number_format($balanceDisplayAmount ?? 0, 2)); ?></span></p>
                            <?php elseif(($balanceDisplayType ?? 'zero') === 'credit'): ?>
                                <p><strong>Credit balance:</strong> <span class="text-success fw-bold">(GH₵<?php echo e(number_format($balanceDisplayAmount ?? 0, 2)); ?>)</span></p>
                            <?php else: ?>
                                <p><strong>Outstanding Balance:</strong> <span class="text-body">GH₵0.00</span></p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity & Information -->
            <div class="row g-5">
                <!-- Registered Courses -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-graduation-cap me-2"></i>My Courses This Semester
                            </h3>
                        </div>
                        <div class="card-body">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($registeredCourses->count() > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Course Code</th>
                                                <th>Course Title</th>
                                                <th>Credit Hours</th>
                                                <th>Registration Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $registeredCourses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $registration): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <tr>
                                                    <td><?php echo e($registration->subject->course_code ?? 'N/A'); ?></td>
                                                    <td><?php echo e($registration->subject->name ?? 'N/A'); ?></td>
                                                    <td><?php echo e($registration->subject->credit_hours ?? 'N/A'); ?></td>
                                                    <td><?php echo e($registration->registered_at->format('M d, Y')); ?></td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-book-open text-muted" style="font-size: 3rem;"></i>
                                    <h5 class="text-muted mt-3">No courses registered yet</h5>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($currentSemester) && $currentSemester->isRegistrationExpired()): ?>
                                        <span class="badge bg-danger fs-7 mt-2 p-2">Registration Closed</span>
                                    <?php elseif($paymentPercentage >= 60): ?>
                                        <a href="<?php echo e(route('courseregistration')); ?>" class="btn btn-primary">Register Now</a>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Academic Info & Quick Links -->
                <div class="col-lg-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-user me-2"></i>Academic Information
                            </h3>
                        </div>
                        <div class="card-body">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($student): ?>
                                <div class="mb-3">
                                    <span class="text-muted">Student ID:</span>
                                    <div class="fw-bold"><?php echo e($student->student_id ?? 'Not Assigned'); ?></div>
                                </div>
                                <div class="mb-3">
                                    <span class="text-muted">Program:</span>
                                    <div class="fw-bold"><?php echo e($student->collegeClass->name ?? 'Not Assigned'); ?></div>
                                </div>
                                <div class="mb-3">
                                    <span class="text-muted">Email:</span>
                                    <div class="fw-bold"><?php echo e($student->email); ?></div>
                                </div>
                                <div class="mb-3">
                                    <span class="text-muted">Status:</span>
                                    <span class="badge badge-light-<?php echo e($student->status === 'active' ? 'success' : 'warning'); ?>">
                                        <?php echo e(ucfirst($student->status ?? 'Active')); ?>

                                    </span>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-external-link-alt me-2"></i>Quick Links
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="<?php echo e(route('courseregistration')); ?>" class="btn btn-light-primary">
                                    <i class="fas fa-book me-2"></i>Course Registration
                                </a>
                                <a href="<?php echo e(route('courseregistration.history')); ?>" class="btn btn-light-info">
                                    <i class="fas fa-history me-2"></i>Registration History
                                </a>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Route::has('student.grades') && auth()->user()->can('view grades')): ?>
                                    <a href="<?php echo e(route('student.grades')); ?>" class="btn btn-light-success">
                                        <i class="fas fa-star me-2"></i>View Grades
                                    </a>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Route::has('student.transcripts') && auth()->user()->can('view transcripts')): ?>
                                    <a href="<?php echo e(route('student.transcripts')); ?>" class="btn btn-light-warning">
                                        <i class="fas fa-file-alt me-2"></i>Academic Transcript
                                    </a>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78458ab11901cb5e12c9728346766652)): ?>
<?php $attributes = $__attributesOriginal78458ab11901cb5e12c9728346766652; ?>
<?php unset($__attributesOriginal78458ab11901cb5e12c9728346766652); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78458ab11901cb5e12c9728346766652)): ?>
<?php $component = $__componentOriginal78458ab11901cb5e12c9728346766652; ?>
<?php unset($__componentOriginal78458ab11901cb5e12c9728346766652); ?>
<?php endif; ?>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/students/dashboard.blade.php ENDPATH**/ ?>