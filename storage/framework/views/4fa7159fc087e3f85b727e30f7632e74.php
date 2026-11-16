<div>
    <!-- Compact Professional Page Header -->
    <div class="mb-7 mb-lg-8">
        <h1 class="text-gray-900 fw-bold mb-1" style="font-size: 1.5rem; letter-spacing: -0.01em;">Student Information</h1>
        <p class="text-muted fs-7 mb-0">Manage student records, admissions and academic information.</p>
    </div>

    <!-- Alert Notifications -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('success')): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-5" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-5" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('info')): ?>
        <div class="alert alert-info alert-dismissible fade show shadow-sm mb-5" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            <?php echo e(session('info')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Single Cohesive Workspace Container -->
    <div class="card mb-xl-10 border border-gray-200 shadow-none rounded-3">
        <!-- Toolbar & Filter Surface -->
        <div class="p-4 p-md-5 border-bottom border-gray-200">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                
                <!-- Title & Count Badge -->
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <h3 class="fw-bold text-gray-900 fs-4 mb-0">Students</h3>
                    <span class="text-muted fs-5 fw-normal">·</span>
                    <span class="badge bg-light text-gray-700 fw-semibold fs-7 px-2.5 py-1 border border-gray-300 rounded-2" style="font-variant-numeric: tabular-nums;">
                        <?php echo e(number_format($studentsTotal)); ?>

                    </span>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($selectedStudents) && count($selectedStudents) > 0): ?>
                        <span class="badge bg-light-primary text-primary fw-semibold fs-7 px-2.5 py-1 border border-primary border-opacity-25 rounded-2 ms-1">
                            <?php echo e(count($selectedStudents)); ?> selected
                        </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <!-- Desktop Single-Row Filter System -->
                <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3 flex-grow-1 justify-content-lg-end">
                    
                    <!-- Search Input (Flexible 40-50%) -->
                    <div class="position-relative flex-grow-1" style="max-width: 420px; min-width: 220px;">
                        <span class="position-absolute top-50 translate-middle-y ms-3 text-muted">
                            <i class="fas fa-search fs-6"></i>
                        </span>
                        <input type="text" 
                               class="form-control form-control-solid ps-9" 
                               style="height: 42px; border-radius: 8px; font-size: 0.875rem;"
                               placeholder="Search by ID, name or email..." 
                               wire:model.live.debounce.400ms="search">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($search): ?>
                            <button type="button" 
                                    class="btn btn-sm btn-icon position-absolute top-50 end-0 translate-middle-y me-2 text-muted text-hover-primary border-0" 
                                    wire:click="$set('search', '')">
                                <i class="fas fa-times fs-7"></i>
                            </button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <!-- Programme Filter (220-280px) -->
                    <div style="min-width: 200px; max-width: 260px;" class="flex-grow-1 flex-sm-grow-0">
                        <select class="form-select form-select-solid" 
                                style="height: 42px; border-radius: 8px; font-size: 0.875rem;" 
                                wire:model.live="programFilter">
                            <option value="">All Programmes</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $programs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $program): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($program->id); ?>"><?php echo e($program->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                    </div>

                    <!-- Cohort Filter (180-240px) -->
                    <div style="min-width: 180px; max-width: 220px;" class="flex-grow-1 flex-sm-grow-0">
                        <select class="form-select form-select-solid" 
                                style="height: 42px; border-radius: 8px; font-size: 0.875rem;" 
                                wire:model.live="cohortFilter">
                            <option value="">All Cohorts</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $cohorts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cohort): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($cohort->id); ?>"><?php echo e($cohort->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                    </div>

                    <!-- Gender Filter (130-160px) -->
                    <div style="min-width: 130px; max-width: 160px;" class="flex-grow-1 flex-sm-grow-0">
                        <select class="form-select form-select-solid" 
                                style="height: 42px; border-radius: 8px; font-size: 0.875rem;" 
                                wire:model.live="genderFilter">
                            <option value="">All Genders</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div style="min-width: 140px; max-width: 170px;" class="flex-grow-1 flex-sm-grow-0">
                        <select class="form-select form-select-solid"
                                style="height: 42px; border-radius: 8px; font-size: 0.875rem;"
                                wire:model.live="statusFilter">
                            <option value="">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="graduated">Graduated</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>

                    <!-- Tertiary Export & Actions -->
                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                        <button type="button" 
                                class="btn btn-light-secondary border border-gray-300 text-gray-700 fw-medium d-inline-flex align-items-center px-3" 
                                style="height: 42px; border-radius: 8px; font-size: 0.875rem;"
                                wire:click="exportStudents">
                            <i class="fas fa-file-export me-1.5 text-muted fs-7"></i>
                            Export
                        </button>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($cohortFilter): ?>
                            <button type="button" 
                                    class="btn btn-light-warning border border-warning border-opacity-50 text-warning-dark fw-medium d-inline-flex align-items-center px-3" 
                                    style="height: 42px; border-radius: 8px; font-size: 0.875rem;"
                                    wire:click="confirmIdRegeneration">
                                <i class="fas fa-sync-alt me-1.5 fs-7"></i>
                                Regenerate IDs
                            </button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user() && auth()->user()->hasRole('System')): ?>
                            <div class="dropdown">
                                <button class="btn btn-light-warning border border-warning border-opacity-50 text-warning-dark fw-medium d-inline-flex align-items-center px-3" 
                                        type="button" 
                                        id="dropdownPasswordReset" 
                                        data-bs-toggle="dropdown" 
                                        aria-expanded="false" 
                                        style="height: 42px; border-radius: 8px; font-size: 0.875rem;">
                                    <i class="fas fa-key me-1.5 fs-7"></i>
                                    Reset Passwords
                                    <i class="fas fa-chevron-down ms-1 fs-8"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-gray-200 fs-7" aria-labelledby="dropdownPasswordReset">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($cohortFilter): ?>
                                        <li>
                                            <a class="dropdown-item py-2" href="#" wire:click.prevent="openStudentPasswordReset('cohort')">
                                                <i class="fas fa-users-cog me-2 text-warning fs-7"></i>Reset for Current Cohort
                                            </a>
                                        </li>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <li>
                                        <a class="dropdown-item py-2 text-danger" href="#" wire:click.prevent="openStudentPasswordReset('all')">
                                            <i class="fas fa-user-shield me-2 fs-7"></i>Reset for All Active Students
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                </div>

            </div>

            <!-- Contextual Selected Action Bar -->
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($selectedStudents) && count($selectedStudents) > 0): ?>
                <div class="mt-3 pt-3 border-top border-gray-200 d-flex align-items-center justify-content-between bg-light-primary rounded-2 px-3 py-2">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-check-circle text-primary fs-6"></i>
                        <span class="fw-semibold text-gray-800 fs-7">
                            <?php echo e(count($selectedStudents)); ?> student(s) selected
                        </span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user() && auth()->user()->hasRole('System')): ?>
                            <button type="button" class="btn btn-sm btn-warning py-1 px-3 fs-7 fw-semibold text-dark" wire:click="openStudentPasswordReset('selected')">
                                <i class="fas fa-key me-1"></i> Reset Passwords
                            </button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <button type="button" class="btn btn-sm btn-primary py-1 px-3 fs-7 fw-semibold" wire:click="exportStudents">
                            Export Selected
                        </button>
                        <button type="button" class="btn btn-sm btn-light py-1 px-3 fs-7 fw-semibold text-gray-700" wire:click="$set('selectedStudents', []); $set('selectAll', false)">
                            Clear
                        </button>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <style>
            .student-table-row {
                transition: background-color 0.15s ease-in-out;
            }
            .student-table-row:hover {
                background-color: #F8FAFC !important;
            }
            .student-table-row.selected-row {
                background-color: #EFF6FF !important;
            }
            .student-table-row.selected-row:hover {
                background-color: #DBEAFE !important;
            }
            .btn-table-action {
                background-color: #F8FAFC !important;
                color: #334155 !important;
                border: 1px solid #CBD5E1 !important;
                font-size: 0.8125rem !important;
                font-weight: 600 !important;
                transition: all 0.15s ease-in-out !important;
            }
            .btn-table-action i {
                color: #64748B !important;
                transition: color 0.15s ease-in-out !important;
            }
            .btn-table-action:hover, 
            .btn-table-action:focus, 
            .btn-table-action:active,
            .show > .btn-table-action {
                background-color: #2563EB !important;
                color: #FFFFFF !important;
                border-color: #1D4ED8 !important;
            }
            .btn-table-action:hover i, 
            .btn-table-action:focus i, 
            .btn-table-action:active i,
            .show > .btn-table-action i {
                color: #FFFFFF !important;
            }

            .badge-light-success {
                background-color: #E8FFF3 !important;
                color: #50CD89 !important;
                border: none !important;
            }
            .badge-light-danger {
                background-color: #FFF5F8 !important;
                color: #F1416C !important;
                border: none !important;
            }
            .badge-light-warning {
                background-color: #FFF8DD !important;
                color: #F1C40F !important;
                border: none !important;
            }
            .badge-light-info {
                background-color: #F8F5FF !important;
                color: #7239EA !important;
                border: none !important;
            }
            .badge-light-primary {
                background-color: #F1FAFF !important;
                color: #009EF7 !important;
                border: none !important;
            }
            .badge-light-secondary {
                background-color: #F5F8FA !important;
                color: #7E8299 !important;
                border: none !important;
            }
        </style>

        <!-- Table Surface -->
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
                    <thead style="background-color: #F8FAFC; border-bottom: 2px solid #E2E8F0;">
                        <tr>
                            <th class="text-center align-middle" style="width: 48px; min-width: 48px; max-width: 48px; padding: 12px 16px;">
                                <div class="d-flex justify-content-center align-items-center">
                                    <input type="checkbox" 
                                           class="form-check-input m-0"
                                           style="cursor: pointer; width: 16px; height: 16px;"
                                           wire:model.live="selectAll"
                                           title="Select all students">
                                </div>
                            </th>
                            <th class="align-middle fw-bold text-uppercase" style="width: 150px; min-width: 150px; font-size: 0.75rem; letter-spacing: 0.05em; padding: 12px 16px; color: #475569;">Student ID</th>
                            <th class="align-middle fw-bold text-uppercase" style="min-width: 240px; font-size: 0.75rem; letter-spacing: 0.05em; padding: 12px 16px; color: #475569;">Student Name</th>
                            <th class="align-middle fw-bold text-uppercase" style="width: 100px; min-width: 100px; font-size: 0.75rem; letter-spacing: 0.05em; padding: 12px 16px; color: #475569;">Gender</th>
                            <th class="align-middle fw-bold text-uppercase" style="min-width: 220px; font-size: 0.75rem; letter-spacing: 0.05em; padding: 12px 16px; color: #475569;">Programme</th>
                            <th class="align-middle fw-bold text-uppercase" style="width: 140px; min-width: 140px; font-size: 0.75rem; letter-spacing: 0.05em; padding: 12px 16px; color: #475569;">Cohort</th>
                            <th class="align-middle fw-bold text-uppercase text-center" style="width: 110px; min-width: 110px; font-size: 0.75rem; letter-spacing: 0.05em; padding: 12px 16px; color: #475569;">Status</th>
                            <th class="align-middle fw-bold text-uppercase text-end" style="width: 110px; min-width: 110px; font-size: 0.75rem; letter-spacing: 0.05em; padding: 12px 16px; color: #475569;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $isSelected = in_array((string)$student->id, $selectedStudents);
                                $statusStr = $student->status ?? 'Active';
                            ?>
                            <tr class="student-table-row <?php echo e($isSelected ? 'selected-row' : ''); ?>" style="border-bottom: 1px solid #F1F5F9; height: 56px;">
                                <td class="text-center align-middle" style="width: 48px; min-width: 48px; max-width: 48px; padding: 14px 16px;">
                                    <div class="d-flex justify-content-center align-items-center">
                                        <input type="checkbox" 
                                               class="form-check-input m-0"
                                               style="cursor: pointer; width: 16px; height: 16px;"
                                               value="<?php echo e($student->id); ?>"
                                               wire:model.live="selectedStudents">
                                    </div>
                                </td>
                                <td class="align-middle" style="padding: 14px 16px;">
                                    <a href="<?php echo e(route('students.show', $student->id)); ?>" class="fw-semibold text-gray-900 text-hover-primary" style="font-variant-numeric: tabular-nums; font-size: 0.875rem; letter-spacing: 0.02em;">
                                        <?php echo e($student->student_id); ?>

                                    </a>
                                </td>
                                <td class="align-middle" style="padding: 14px 16px;">
                                    <div class="d-flex align-items-center">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($student->profile_photo_url): ?>
                                            <div class="me-3 flex-shrink-0">
                                                <a href="<?php echo e(route('students.show', $student->id)); ?>">
                                                    <img class="rounded-circle" src="<?php echo e($student->profile_photo_url); ?>" alt="avatar" width="34" height="34">
                                                </a>
                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <div class="overflow-hidden">
                                            <a href="<?php echo e(route('students.show', $student->id)); ?>" class="fw-bold text-gray-900 text-hover-primary d-block text-truncate fs-7" title="<?php echo e($student->last_name); ?> <?php echo e($student->first_name); ?> <?php echo e($student->other_name); ?>">
                                                <?php echo e($student->last_name); ?> <?php echo e($student->first_name); ?> <?php echo e($student->other_name); ?>

                                            </a>
                                            <div class="text-muted fs-8 text-truncate" style="max-width: 240px;">
                                                <?php echo e($student->email); ?>

                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="align-middle" style="padding: 14px 16px;">
                                    <span class="text-gray-700 fs-7">
                                        <?php echo e(ucfirst(strtolower($student->gender ?? 'N/A'))); ?>

                                    </span>
                                </td>
                                <td class="align-middle" style="padding: 14px 16px;">
                                    <span class="text-gray-800 fw-normal fs-7">
                                        <?php echo e($student->CollegeClass()->first()?->name ?? 'N/A'); ?>

                                    </span>
                                </td>
                                <td class="align-middle" style="padding: 14px 16px;">
                                    <span class="text-muted fs-7">
                                        <?php echo e($student->Cohort()->first()->name ?? 'N/A'); ?>

                                    </span>
                                </td>
                                <td class="text-center align-middle" style="padding: 14px 16px;">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($statusStr == 'Active'): ?>
                                        <span class="badge badge-light-success px-3 py-1.5 fs-7 fw-bold rounded-pill">Active</span>
                                    <?php elseif($statusStr == 'Inactive'): ?>
                                        <span class="badge badge-light-danger px-3 py-1.5 fs-7 fw-bold rounded-pill">Inactive</span>
                                    <?php elseif($statusStr == 'Pending'): ?>
                                        <span class="badge badge-light-warning px-3 py-1.5 fs-7 fw-bold rounded-pill">Pending</span>
                                    <?php elseif($statusStr == 'Graduated'): ?>
                                        <span class="badge badge-light-info px-3 py-1.5 fs-7 fw-bold rounded-pill">Graduated</span>
                                    <?php elseif($statusStr == 'Suspended'): ?>
                                        <span class="badge badge-light-primary px-3 py-1.5 fs-7 fw-bold rounded-pill">Suspended</span>
                                    <?php else: ?>
                                        <span class="badge badge-light-secondary px-3 py-1.5 fs-7 fw-bold rounded-pill"><?php echo e($statusStr); ?></span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="text-end align-middle" style="padding: 14px 16px;">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border border-gray-300 rounded-2 px-2.5 py-1 text-gray-700 fw-medium fs-7" type="button" id="dropdownMenuButton<?php echo e($student->id); ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                            Actions
                                            <i class="fas fa-chevron-down ms-1 fs-8 text-muted"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-gray-200 fs-7" aria-labelledby="dropdownMenuButton<?php echo e($student->id); ?>">
                                            <li><a class="dropdown-item py-2" href="<?php echo e(route('students.show', $student->id)); ?>"><i class="fas fa-eye me-2 text-info fs-7"></i>View Details</a></li>
                                            <li><a class="dropdown-item py-2" href="<?php echo e(route('students.edit', $student->id)); ?>"><i class="fas fa-edit me-2 text-primary fs-7"></i>Edit Student</a></li>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user() && auth()->user()->hasRole('System')): ?>
                                                <li>
                                                    <a class="dropdown-item py-2 text-warning-dark" href="#" 
                                                       wire:click.prevent="openStudentPasswordReset('individual', <?php echo e($student->id); ?>)">
                                                       <i class="fas fa-key me-2 text-warning fs-7"></i>Reset Password
                                                    </a>
                                                </li>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <li><hr class="dropdown-divider my-1"></li>
                                            <li>
                                                <a class="dropdown-item py-2 text-danger" href="#" 
                                                   wire:click.prevent="confirmStudentDeletion(<?php echo e($student->id); ?>)">
                                                   <i class="fas fa-trash-alt me-2 fs-7"></i>Delete Student
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        
                        <!-- Empty State A: Filter/Search returns no matches -->
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($students) == 0 && ($search != '' || $programFilter != '' || $cohortFilter != '' || $genderFilter != '' || $statusFilter != '')): ?>
                            <tr>
                                <td colspan="8" class="p-0 border-0">
                                    <div class="d-flex flex-column align-items-center justify-content-center text-center py-12 px-4" style="min-height: 220px;">
                                        <div class="d-flex align-items-center justify-content-center rounded-3 mb-3" style="width: 52px; height: 52px; background-color: #EFF6FF; border: 1px solid #BFDBFE;">
                                            <i class="fas fa-search fs-3 text-primary"></i>
                                        </div>
                                        <h4 class="fw-bold text-gray-900 mb-1" style="font-size: 1.125rem;">No matching students</h4>
                                        <p class="text-muted fs-7 mb-4 mx-auto" style="max-width: 440px; line-height: 1.5;">
                                            We couldn't find any students matching your current search criteria or filters.
                                        </p>
                                        <button type="button" class="btn btn-sm btn-light-primary border border-primary border-opacity-25 fw-semibold px-4 py-2.5 d-inline-flex align-items-center gap-2" style="height: 42px; border-radius: 8px;" wire:click="resetFilters">
                                            <i class="fas fa-undo fs-7"></i>
                                            Clear filters
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <!-- Empty State B: No students exist in the system at all -->
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($students) == 0 && $search == '' && $programFilter == '' && $cohortFilter == '' && $genderFilter == '' && $statusFilter == ''): ?>
                            <tr>
                                <td colspan="8" class="p-0 border-0">
                                    <div class="d-flex flex-column align-items-center justify-content-center text-center py-12 px-4" style="min-height: 240px;">
                                        <div class="d-flex align-items-center justify-content-center rounded-3 mb-3" style="width: 52px; height: 52px; background-color: #EFF6FF; border: 1px solid #BFDBFE;">
                                            <i class="fas fa-user-graduate fs-3 text-primary"></i>
                                        </div>
                                        <h4 class="fw-bold text-gray-900 mb-1" style="font-size: 1.125rem;">No students yet</h4>
                                        <p class="text-muted fs-7 mb-4 mx-auto" style="max-width: 440px; line-height: 1.5;">
                                            Add your first student record or import existing student data to get started.
                                        </p>
                                        <div class="d-flex align-items-center justify-content-center gap-3 flex-wrap">
                                            <a href="<?php echo e(route('students.create')); ?>" class="btn btn-sm btn-primary fw-semibold px-4 py-2.5 d-inline-flex align-items-center gap-2" style="height: 42px; border-radius: 8px;">
                                                <i class="fas fa-plus fs-7"></i>
                                                Add Student
                                            </a>
                                            <a href="<?php echo e(route('students.import')); ?>" class="btn btn-sm btn-light-primary border border-primary border-opacity-25 fw-semibold px-4 py-2.5 d-inline-flex align-items-center gap-2" style="height: 42px; border-radius: 8px;">
                                                <i class="fas fa-file-import fs-7"></i>
                                                Import Students
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer Pagination Surface (Option B: Hidden when 0 records exist) -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($students->total() > 0): ?>
            <div class="p-4 border-top border-gray-200 d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">
                <div class="text-muted fs-7">
                    Showing <span class="fw-semibold text-gray-800"><?php echo e($students->firstItem()); ?></span> to <span class="fw-semibold text-gray-800"><?php echo e($students->lastItem()); ?></span> of <span class="fw-semibold text-gray-800"><?php echo e(number_format($students->total())); ?></span> students
                </div>
                <div>
                    <?php echo e($students->links()); ?>

                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <!-- Delete Confirmation Modal -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($confirmingStudentDeletion): ?>
    <div class="modal fade show" tabindex="-1" style="display: block; background-color: rgba(0,0,0,0.5);" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-danger text-white py-3 px-4">
                    <h5 class="modal-title text-white fw-bold fs-6">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Delete Student
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('confirmingStudentDeletion', false)"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="mb-0 text-gray-800 fs-6">Are you sure you want to delete this student record? This action cannot be undone.</p>
                </div>
                <div class="modal-footer bg-light py-3 px-4 border-0">
                    <button type="button" class="btn btn-sm btn-light-secondary border" wire:click="$set('confirmingStudentDeletion', false)">Cancel</button>
                    <button type="button" class="btn btn-sm btn-danger fw-semibold px-4" wire:click="deleteStudent">Delete</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- ID Regeneration Modal -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($confirmingIdRegeneration): ?>
    <div class="modal fade show" tabindex="-1" style="display: block; background-color: rgba(0,0,0,0.5);" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-warning text-dark py-3 px-4">
                    <h5 class="modal-title fw-bold fs-6">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Regenerate Student IDs
                    </h5>
                    <button type="button" class="btn-close" wire:click="$set('confirmingIdRegeneration', false)"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="mb-0 text-gray-800 fs-6">This action will regenerate IDs for all students in the selected cohort. Are you sure you want to proceed?</p>
                </div>
                <div class="modal-footer bg-light py-3 px-4 border-0">
                    <button type="button" class="btn btn-sm btn-light-secondary border" wire:click="$set('confirmingIdRegeneration', false)">Cancel</button>
                    <button type="button" class="btn btn-sm btn-warning text-dark fw-semibold px-4" wire:click="regenerateIds">Regenerate</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Export Format Selection Modal -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showingExportModal): ?>
    <div class="modal fade show" tabindex="-1" style="display: block; background-color: rgba(0,0,0,0.5);" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white py-3 px-4">
                    <h5 class="modal-title text-white fw-bold fs-6">
                        <i class="fas fa-file-export me-2"></i>
                        Export Students
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="cancelExport" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="fs-6 text-gray-800 mb-4">
                        Please select your preferred export format:
                    </p>
                    
                    <div class="d-flex flex-column gap-3">
                        <!-- Excel Option -->
                        <div class="form-check form-check-custom form-check-solid p-3 rounded-2 border border-gray-200 cursor-pointer" onclick="document.getElementById('export_excel').click()">
                            <input class="form-check-input me-3" type="radio" value="excel" id="export_excel" wire:model.live="exportFormat">
                            <label class="form-check-label d-flex align-items-center w-100 cursor-pointer" for="export_excel">
                                <span class="me-3">
                                    <i class="fas fa-file-excel text-success fs-2"></i>
                                </span>
                                <div>
                                    <span class="fw-bold text-gray-900 d-block">Excel (.xlsx)</span>
                                    <span class="text-muted fs-7">Export to Microsoft Excel spreadsheet format</span>
                                </div>
                            </label>
                        </div>
                        
                        <!-- PDF Option -->
                        <div class="form-check form-check-custom form-check-solid p-3 rounded-2 border border-gray-200 cursor-pointer" onclick="document.getElementById('export_pdf').click()">
                            <input class="form-check-input me-3" type="radio" value="pdf" id="export_pdf" wire:model.live="exportFormat">
                            <label class="form-check-label d-flex align-items-center w-100 cursor-pointer" for="export_pdf">
                                <span class="me-3">
                                    <i class="fas fa-file-pdf text-danger fs-2"></i>
                                </span>
                                <div>
                                    <span class="fw-bold text-gray-900 d-block">PDF (.pdf)</span>
                                    <span class="text-muted fs-7">Export to Portable Document Format</span>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-4 mb-0 py-3 px-4 d-flex align-items-center">
                        <i class="fas fa-info-circle fs-4 me-3 text-info"></i>
                        <div class="fs-7 text-gray-800">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($selectedStudents) && count($selectedStudents) > 0): ?>
                                The export will include <strong><?php echo e(count($selectedStudents)); ?></strong> selected student record(s).
                            <?php else: ?>
                                The export will include <strong><?php echo e(number_format($studentsTotal)); ?></strong> student record(s) based on your current filters.
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-3 px-4 border-0">
                    <button type="button" class="btn btn-sm btn-light-secondary border" wire:click="cancelExport">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-primary fw-semibold px-4" wire:click="processExport" wire:loading.attr="disabled" <?php if(!$exportFormat): ?> disabled <?php endif; ?>>
                        <i class="fas fa-file-export me-1.5 fs-7"></i>
                        <span wire:loading.remove wire:target="processExport">Export</span>
                        <span wire:loading wire:target="processExport">Exporting...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Student Password Reset Modal -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPasswordResetModal): ?>
    <div class="modal fade show" tabindex="-1" style="display: block; background-color: rgba(0,0,0,0.55); z-index: 1060;" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-warning py-3 px-4 text-dark border-0">
                    <h5 class="modal-title fw-bold fs-5 d-flex align-items-center">
                        <i class="fas fa-key me-2 text-dark"></i>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($resetScope === 'individual'): ?>
                            Reset Password: <?php echo e($resetTargetStudentName); ?> (<?php echo e($resetTargetStudentCode); ?>)
                        <?php elseif($resetScope === 'selected'): ?>
                            Reset Passwords for <?php echo e($resetTargetCount); ?> Selected Students
                        <?php elseif($resetScope === 'cohort'): ?>
                            Reset Passwords for Cohort: <?php echo e($resetTargetStudentName); ?> (<?php echo e($resetTargetCount); ?> students)
                        <?php elseif($resetScope === 'all'): ?>
                            Reset Passwords for All Active Students (<?php echo e($resetTargetCount); ?> students)
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeStudentPasswordReset"></button>
                </div>

                <div class="modal-body p-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($resetSummary): ?>
                        <!-- Summary View after execution -->
                        <div class="alert alert-success d-flex align-items-center mb-4">
                            <i class="fas fa-check-circle fs-2 me-3 text-success"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Password Reset Completed</h6>
                                <p class="mb-0 fs-7 text-muted">Summary of the credential reset operation:</p>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-6 col-md-3">
                                <div class="bg-light p-3 rounded-2 text-center border">
                                    <span class="fs-8 text-muted d-block text-uppercase fw-semibold">Targeted</span>
                                    <span class="fs-4 fw-bold text-gray-900"><?php echo e($resetSummary['total'] ?? 0); ?></span>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="bg-light-success p-3 rounded-2 text-center border border-success border-opacity-25">
                                    <span class="fs-8 text-success d-block text-uppercase fw-semibold">Processed</span>
                                    <span class="fs-4 fw-bold text-success"><?php echo e($resetSummary['processed'] ?? 0); ?></span>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="bg-light-primary p-3 rounded-2 text-center border border-primary border-opacity-25">
                                    <span class="fs-8 text-primary d-block text-uppercase fw-semibold">Emails Sent</span>
                                    <span class="fs-4 fw-bold text-primary"><?php echo e($resetSummary['emails_sent'] ?? 0); ?></span>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="bg-light-info p-3 rounded-2 text-center border border-info border-opacity-25">
                                    <span class="fs-8 text-info d-block text-uppercase fw-semibold">SMS Sent</span>
                                    <span class="fs-4 fw-bold text-info"><?php echo e($resetSummary['sms_sent'] ?? 0); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Form Configuration View -->
                        <div class="alert alert-light-warning border border-warning border-opacity-50 p-3 mb-4 rounded-2">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-shield-alt text-warning fs-3 me-3"></i>
                                <div class="fs-7 text-gray-800">
                                    <strong>System Administrator Privilege:</strong> You are about to reset student account credentials. This will overwrite existing passwords. If a student does not yet have a user account, one will automatically be provisioned for them.
                                </div>
                            </div>
                        </div>

                        <!-- Password Generation Option -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-gray-800 fs-7">Temporary Password Setting</label>
                            <div class="d-flex flex-column gap-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" id="student_pwd_random" value="random" wire:model.live="resetPasswordMode">
                                    <label class="form-check-label fs-7 fw-medium" for="student_pwd_random">
                                        Auto-generate secure random temporary password <span class="badge bg-light text-muted ms-1">Recommended</span>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" id="student_pwd_custom" value="custom" wire:model.live="resetPasswordMode">
                                    <label class="form-check-label fs-7 fw-medium" for="student_pwd_custom">
                                        Specify a uniform temporary password
                                    </label>
                                </div>
                            </div>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($resetPasswordMode === 'custom'): ?>
                                <div class="mt-2" style="max-width: 320px;">
                                    <input type="text" class="form-control form-control-sm <?php $__errorArgs = ['resetCustomPassword'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           placeholder="Enter temporary password (min 8 chars)" 
                                           wire:model="resetCustomPassword">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['resetCustomPassword'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="text-danger fs-8 mt-1"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <!-- Notification Channel Option -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-gray-800 fs-7">Credential Notification Channel</label>
                            <div class="row g-2">
                                <div class="col-sm-6">
                                    <div class="form-check border p-2.5 rounded-2 <?php if($resetChannel === 'both'): ?> bg-light-primary border-primary <?php endif; ?>">
                                        <input class="form-check-input me-2" type="radio" id="channel_both" value="both" wire:model.live="resetChannel">
                                        <label class="form-check-label fs-7 fw-semibold cursor-pointer w-100" for="channel_both">
                                            <i class="fas fa-paper-plane text-primary me-1"></i> Both Email & SMS
                                        </label>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-check border p-2.5 rounded-2 <?php if($resetChannel === 'email'): ?> bg-light-primary border-primary <?php endif; ?>">
                                        <input class="form-check-input me-2" type="radio" id="channel_email" value="email" wire:model.live="resetChannel">
                                        <label class="form-check-label fs-7 fw-semibold cursor-pointer w-100" for="channel_email">
                                            <i class="fas fa-envelope text-primary me-1"></i> Email Only
                                        </label>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-check border p-2.5 rounded-2 <?php if($resetChannel === 'sms'): ?> bg-light-primary border-primary <?php endif; ?>">
                                        <input class="form-check-input me-2" type="radio" id="channel_sms" value="sms" wire:model.live="resetChannel">
                                        <label class="form-check-label fs-7 fw-semibold cursor-pointer w-100" for="channel_sms">
                                            <i class="fas fa-comment-dots text-info me-1"></i> SMS Only
                                        </label>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-check border p-2.5 rounded-2 <?php if($resetChannel === 'none'): ?> bg-light-secondary <?php endif; ?>">
                                        <input class="form-check-input me-2" type="radio" id="channel_none" value="none" wire:model.live="resetChannel">
                                        <label class="form-check-label fs-7 fw-semibold cursor-pointer w-100" for="channel_none">
                                            <i class="fas fa-bell-slash text-muted me-1"></i> Do Not Send (Manual)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Force Password Change Toggle -->
                        <div class="mb-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="student_force_change" wire:model="resetRequirePasswordChange">
                                <label class="form-check-label fs-7 fw-bold text-gray-800" for="student_force_change">
                                    Require password change upon initial login
                                </label>
                            </div>
                            <div class="text-muted fs-8 ms-6">
                                When enabled, recipients will be locked from navigating the portal until they choose their own personal password.
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="modal-footer bg-light py-3 px-4 border-0">
                    <button type="button" class="btn btn-sm btn-light-secondary border" wire:click="closeStudentPasswordReset">
                        <?php echo e($resetSummary ? 'Close' : 'Cancel'); ?>

                    </button>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $resetSummary): ?>
                        <button type="button" class="btn btn-warning text-dark fw-bold px-4" 
                                wire:click="executeStudentPasswordReset" 
                                wire:loading.attr="disabled">
                            <i class="fas fa-check-circle me-1.5" wire:loading.remove wire:target="executeStudentPasswordReset"></i>
                            <span wire:loading.remove wire:target="executeStudentPasswordReset">Confirm & Reset Passwords</span>
                            <span wire:loading wire:target="executeStudentPasswordReset">
                                <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                Processing...
                            </span>
                        </button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/livewire/students-table-widget.blade.php ENDPATH**/ ?>