<?php if (isset($component)) { $__componentOriginal78458ab11901cb5e12c9728346766652 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78458ab11901cb5e12c9728346766652 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.default','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.default'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('title', null, []); ?> 
        Academics Dashboard
     <?php $__env->endSlot(); ?>
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title">
                                <i class="fas fa-university me-2"></i>Academics Management
                            </h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Current Academic Year & Semester -->
                            <div class="col-md-12 mb-4">
                                <div class="alert alert-info">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-1">Current Academic Settings</h5>
                                            <?php
                                                $currentYear = App\Models\AcademicYear::where('is_current', true)->first();
                                                $currentSemester = App\Models\Semester::current()->first();
                                            ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentYear && $currentSemester): ?>
                                                <p class="mb-0">
                                                    <strong>Academic Year:</strong> <?php echo e($currentYear->name); ?> | 
                                                    <strong>Semester:</strong> <?php echo e($currentSemester->name); ?>

                                                </p>
                                            <?php else: ?>
                                                <p class="mb-0">No current academic year or semester set. Please update settings.</p>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                        <a href="<?php echo e(route('academics.settings.index')); ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-cogs me-1"></i> Settings
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Quick Access Cards -->
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-header bg-secondary text-white">
                                        <h5 class="card-title mb-0 text-dark d-flex align-items-center">
                                            <i class="fas fa-layer-group fa-fw me-2"></i><span>Years</span>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <p>Define programme study levels, such as Year 1, Year 2, and Year 3. These are separate from Academic Years.</p>
                                        <a href="<?php echo e(route('academics.years.index')); ?>" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
                                            <i class="fas fa-cog"></i><span>Manage</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="card-title mb-0 text-white d-flex align-items-center">
                                            <i class="fas fa-calendar-alt fa-fw me-2 text-white"></i><span>Academic Years</span>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <p>Manage institutional calendar periods and select the current Academic Year for the system.</p>
                                        <a href="<?php echo e(route('academics.academic-years.index')); ?>" class="btn btn-outline-primary d-inline-flex align-items-center gap-2">
                                            <i class="fas fa-cog"></i><span>Manage</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="card-title mb-0 text-white d-flex align-items-center">
                                            <i class="fas fa-clock fa-fw me-2 text-white"></i><span>Semesters</span>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <p>Manage the teaching periods within each Academic Year and select the current semester.</p>
                                        <a href="<?php echo e(route('academics.semesters.index')); ?>" class="btn btn-outline-success d-inline-flex align-items-center gap-2">
                                            <i class="fas fa-cog"></i><span>Manage</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="card-title mb-0 text-white d-flex align-items-center">
                                            <i class="fas fa-chalkboard fa-fw me-2"></i><span>Programs</span>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <p>Manage college classes, assign courses and instructors.</p>
                                        <a href="<?php echo e(route('academics.classes.index')); ?>" class="btn btn-outline-info d-inline-flex align-items-center gap-2">
                                            <i class="fas fa-cog"></i><span>Manage</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-header bg-warning text-dark">
                                        <h5 class="card-title mb-0 text-dark d-flex align-items-center">
                                            <i class="fas fa-user-friends fa-fw me-2"></i><span>Cohorts</span>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <p>Manage student cohorts and assign students to specific cohorts.</p>
                                        <a href="<?php echo e(route('academics.cohorts.index')); ?>" class="btn btn-outline-warning d-inline-flex align-items-center gap-2">
                                            <i class="fas fa-cog"></i><span>Manage</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="card-title mb-0 text-white d-flex align-items-center">
                                            <i class="fas fa-book fa-fw me-2 text-white"></i><span>Courses</span>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <p>Create and manage courses offered by the institution.</p>
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="<?php echo e(route('academics.courses.index')); ?>" class="btn btn-outline-primary d-inline-flex align-items-center gap-2">
                                                <i class="fas fa-cog"></i><span>Manage</span>
                                            </a>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (\Illuminate\Support\Facades\Blade::check('hasrole', 'Super Admin|Administrator|System')): ?>
                                            <a href="<?php echo e(route('admin.course-assignments')); ?>" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
                                                <i class="fas fa-user-plus"></i><span>Assign Courses</span>
                                            </a>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-header bg-danger text-white">
                                        <h5 class="card-title mb-0 text-white d-flex align-items-center">
                                            <i class="fas fa-star fa-fw me-2 text-white"></i><span>Grade Types</span>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <p>Manage grade types used for student evaluation.</p>
                                        <a href="<?php echo e(route('academics.grades.index')); ?>" class="btn btn-outline-danger d-inline-flex align-items-center gap-2">
                                            <i class="fas fa-cog"></i><span>Manage</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-header bg-secondary text-white">
                                        <h5 class="card-title mb-0 text-dark d-flex align-items-center">
                                            <i class="fas fa-graduation-cap fa-fw me-2"></i><span>Student Grades</span>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <p>Assign and manage student grades for programs.</p>
                                        <a href="<?php echo e(route('academics.student-grades.index')); ?>" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
                                            <i class="fas fa-cog"></i><span>Manage</span>
                                        </a>
                                    </div>
                                </div>
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
<?php /**PATH /home/johnsonsebire/www/college/resources/views/academics/dashboard.blade.php ENDPATH**/ ?>