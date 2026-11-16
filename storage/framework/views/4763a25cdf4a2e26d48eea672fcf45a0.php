<div>
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <h1 class="card-title mb-0 d-flex align-items-center">
                    <i class="fas fa-book me-3 text-primary"></i> Course Registration Management
                </h1>
            </div>
            <div class="card-toolbar">
                <p class="card-subtitle text-muted mb-0">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->check() && (auth()->user()->hasRole('Student') || auth()->user()->role === 'student')): ?>
                        Register and manage your course enrollments for the semester
                    <?php else: ?>
                        Manage and review student course registrations for the semester
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </p>
            </div>
        </div>
        <div class="card-body">
            <!-- Student Selection and Filters -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="studentId">Student</label>
                        <select wire:model.live="studentId" class="form-control <?php $__errorArgs = ['studentId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <option value="">-- Select Student --</option>
                            <!-- Admin can select a student, students will see their own ID pre-selected -->
                            
                        </select>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['studentId'];
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
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="academicYearId">Academic Year</label>
                        <select wire:model.live="academicYearId" class="form-control <?php $__errorArgs = ['academicYearId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <option value="">-- Select Academic Year --</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $academicYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($year->id); ?>"><?php echo e($year->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['academicYearId'];
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
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="semesterId">Semester</label>
                        <select wire:model.live="semesterId" class="form-control <?php $__errorArgs = ['semesterId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <option value="">-- Select Semester --</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $semesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $semester): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($semester->id); ?>">
                                    <?php echo e($semester->name); ?><?php echo e(empty($academicYearId) && $semester->academicYear ? ' (' . $semester->academicYear->name . ')' : ''); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['semesterId'];
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
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="yearId">Year of study</label>
                        <select wire:model.live="yearId" class="form-control <?php $__errorArgs = ['yearId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <option value="">-- Select Year --</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $years; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($y->id); ?>"><?php echo e($y->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                        <small class="form-text text-muted">e.g. Year 1, Year 2 – only courses for this level are shown</small>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['yearId'];
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
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->selectedSemester && $this->selectedSemester->hasRegistrationDeadline()): ?>
                <div class="alert alert-<?php echo e($this->selectedSemester->isRegistrationExpired() ? 'warning' : 'info'); ?> d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <i class="fas <?php echo e($this->selectedSemester->isRegistrationExpired() ? 'fa-lock' : 'fa-clock'); ?> me-2"></i>
                        <strong>Semester Registration Deadline:</strong> <?php echo e($this->selectedSemester->formatted_registration_deadline); ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->selectedSemester->isRegistrationExpired()): ?>
                            <span class="badge bg-danger ms-2">Deadline Passed</span>
                        <?php else: ?>
                            <span class="badge bg-success ms-2">Open</span>
                            <span class="ms-2 text-muted small">(<?php echo e(now()->diffForHumans($this->selectedSemester->registration_deadline, ['syntax' => \Carbon\CarbonInterface::DIFF_RELATIVE_TO_NOW])); ?>)</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($student): ?>
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-user-graduate me-2 text-primary"></i>Student Information
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($student->photo): ?>
                                            <img src="<?php echo e(asset('storage/' . $student->photo)); ?>" 
                                                alt="Student Photo" class="img-fluid rounded mb-3">
                                        <?php else: ?>
                                            <div class="bg-secondary rounded d-flex justify-content-center align-items-center text-white"
                                                style="height: 150px;">
                                                <i class="fas fa-user fa-3x"></i>
                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Name:</strong> <?php echo e($student->full_name); ?></p>
                                                <p><strong>Student ID:</strong> <?php echo e($student->student_id); ?></p>
                                                <p><strong>Program:</strong> <?php echo e($student->program->name ?? 'Not Assigned'); ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Program:</strong> <?php echo e($student->collegeClass->name ?? 'Not Assigned'); ?></p>
                                                <p><strong>Level:</strong> <?php echo e($student->level ?? 'Not Assigned'); ?></p>
                                                <p><strong>Status:</strong> <?php echo e($student->status); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Fee Payment Status and Registration Eligibility -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="alert alert-<?php echo e($registrationMessageType); ?> mb-4">
                            <div class="d-flex">
                                <div class="me-3">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($registrationMessageType == 'success'): ?>
                                        <i class="fas fa-check-circle fa-2x"></i>
                                    <?php elseif($registrationMessageType == 'warning'): ?>
                                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                                    <?php else: ?>
                                        <i class="fas fa-times-circle fa-2x"></i>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <div>
                                    <h5 class="alert-heading">Fee Payment Status</h5>
                                    <p class="mb-0"><?php echo e($registrationMessage); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($registrationAllowed): ?>
                    <!-- Course Selection Form -->
                    <form wire:submit.prevent="registerCourses">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-book-open me-2 text-primary"></i>Available Courses for Registration
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($yearId)): ?>
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle"></i> Select <strong>Year of study</strong> (e.g. Year 1, Year 2) above to see courses for that level.
                                            </div>
                                        <?php elseif($availableCourses->isEmpty()): ?>
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle"></i> No courses available for this program, semester and year of study.
                                            </div>
                                        <?php else: ?>
                                            <div class="table-responsive">
                                                <table class="table table-bordered">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th width="5%">Select</th>
                                                            <th width="15%">Code</th>
                                                            <th width="45%">Course Title</th>
                                                            <th width="10%">Credits</th>
                                                            <th width="25%">Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $availableCourses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <tr>
                                                                <td class="text-center">
                                                                    <div class="form-check">
                                                                        <input type="checkbox" 
                                                                            wire:model.live="selectedCourses" 
                                                                            value="<?php echo e($subject->id); ?>" 
                                                                            class="form-check-input" 
                                                                            id="subject-<?php echo e($subject->id); ?>">
                                                                    </div>
                                                                </td>
                                                                <td><?php echo e($subject->course_code); ?></td>
                                                                <td><?php echo e($subject->name); ?></td>
                                                                <td><?php echo e($subject->credit_hours); ?></td>
                                                                <td>
                                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($subject->id, $selectedCourses)): ?>
                                                                        <span class="badge bg-success">Selected</span>
                                                                    <?php else: ?>
                                                                        <span class="badge bg-secondary">Not Selected</span>
                                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            
                                            <div class="mt-3 d-flex justify-content-between">
                                                <div>
                                                    <span class="fw-bold">Selected:</span> <?php echo e(count($selectedCourses)); ?> courses
                                                </div>
                                                <div>
                                                    <button type="submit" class="btn btn-primary" <?php echo e(count($selectedCourses) ? '' : 'disabled'); ?>>
                                                        <i class="fas fa-save"></i> Register Selected Courses
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> Course registration is disabled until the payment requirement is met.
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                
                <!-- Current Registrations -->
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($registeredCourses->isNotEmpty()): ?>
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-history me-2 text-primary"></i>Current Course Registrations
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th width="12%">Code</th>
                                                    <th width="35%">Course Title</th>
                                                    <th width="8%">Credits</th>
                                                    <th width="15%">Registration Date</th>
                                                    <th width="15%">Status</th>
                                                    <th width="15%">Approval Info</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $registeredCourses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $registration): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <tr>
                                                        <td><?php echo e($registration->subject->course_code ?? 'N/A'); ?></td>
                                                        <td><?php echo e($registration->subject->name ?? 'N/A'); ?></td>
                                                        <td><?php echo e($registration->subject->credit_hours ?? 'N/A'); ?></td>
                                                        <td><?php echo e($registration->registered_at->format('d M Y')); ?></td>
                                                        <td>
                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($registration->is_approved): ?>
                                                                <span class="badge badge-success">
                                                                    <i class="fas fa-check"></i> Approved
                                                                </span>
                                                            <?php elseif($registration->rejected_at): ?>
                                                                <span class="badge badge-danger">
                                                                    <i class="fas fa-times"></i> Rejected
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="badge badge-warning">
                                                                    <i class="fas fa-clock"></i> Pending
                                                                </span>
                                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($registration->is_approved && $registration->approved_at): ?>
                                                                <small class="text-muted">
                                                                    Approved on<br><?php echo e($registration->approved_at->format('M d, Y')); ?>

                                                                </small>
                                                            <?php elseif($registration->rejected_at): ?>
                                                                <small class="text-muted">
                                                                    Rejected on<br><?php echo e($registration->rejected_at->format('M d, Y')); ?>

                                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($registration->rejection_reason): ?>
                                                                        <br><em><?php echo e(Str::limit($registration->rejection_reason, 30)); ?></em>
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
                                    
                                    <div class="mt-3">
                                        <a href="#" onclick="window.print()" class="btn btn-secondary">
                                            <i class="fas fa-print"></i> Print Registration
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            
            <!-- Flash Messages -->
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('message')): ?>
                <div class="alert alert-success mt-3">
                    <i class="fas fa-check-circle"></i> <?php echo e(session('message')); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('error')): ?>
                <div class="alert alert-danger mt-3">
                    <i class="fas fa-times-circle"></i> <?php echo e(session('error')); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/livewire/finance/course-registration-manager.blade.php ENDPATH**/ ?>