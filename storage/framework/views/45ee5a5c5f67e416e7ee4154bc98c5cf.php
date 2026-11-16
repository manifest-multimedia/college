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
        Semester Details
     <?php $__env->endSlot(); ?>
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title">
                                <i class="fas fa-clock me-2"></i>Semester Details
                            </h5>
                            <div>
                                <a href="<?php echo e(route('academics.semesters.index')); ?>" class="btn btn-sm btn-secondary me-2">
                                    <i class="fas fa-arrow-left me-1"></i> Back to Semesters
                                </a>
                                <a href="<?php echo e(route('academics.semesters.edit', $semester)); ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8 offset-md-2">
                                <table class="table table-striped">
                                    <tbody>
                                        <tr>
                                            <th style="width: 30%">Name</th>
                                            <td><?php echo e($semester->name); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Description</th>
                                            <td><?php echo e($semester->description ?: 'No description'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Academic Year</th>
                                            <td>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($semester->academicYear): ?>
                                                    <a href="<?php echo e(route('academics.academic-years.show', $semester->academicYear)); ?>">
                                                        <?php echo e($semester->academicYear->name); ?>

                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">No academic year assigned</span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Start Date</th>
                                            <td><?php echo e($semester->start_date ? $semester->start_date->format('F d, Y') : 'Not set'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>End Date</th>
                                            <td><?php echo e($semester->end_date ? $semester->end_date->format('F d, Y') : 'Not set'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Registration Opens</th>
                                            <td>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($semester->registration_starts_at): ?>
                                                    <?php echo e($semester->registration_starts_at->format('F d, Y h:i A')); ?>

                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $semester->isRegistrationStarted()): ?>
                                                        <span class="badge bg-info ms-2">Upcoming</span>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Immediately upon start</span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Registration Deadline</th>
                                            <td>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($semester->registration_deadline): ?>
                                                    <?php echo e($semester->registration_deadline->format('F d, Y h:i A')); ?>

                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($semester->isRegistrationExpired()): ?>
                                                        <span class="badge bg-danger ms-2">Closed / Passed</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success ms-2">Open</span>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">No deadline set</span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
                                            <td>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($semester->is_current): ?>
                                                    <span class="badge bg-success">Current Semester</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inactive</span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                
                                <!-- Classes -->
                                <div class="mt-4">
                                    <h4>Programs</h4>
                                    <hr>
                                    
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($semester->collegeClasses && $semester->collegeClasses->count() > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Name</th>
                                                        <th>Course</th>
                                                        <th>Instructor</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $semester->collegeClasses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <tr>
                                                            <td><?php echo e($class->name); ?></td>
                                                            <td><?php echo e(optional($class->course)->name ?? 'No course assigned'); ?></td>
                                                            <td><?php echo e(optional($class->instructor)->name ?? 'No instructor assigned'); ?></td>
                                                            <td>
                                                                <a href="<?php echo e(route('academics.classes.show', $class)); ?>" class="btn btn-sm btn-info">
                                                                    <i class="fas fa-eye"></i> View
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-info">
                                            No classes have been created for this semester.
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    
                                    <a href="<?php echo e(route('academics.classes.create')); ?>" class="btn btn-primary mt-2">
                                        <i class="fas fa-plus me-1"></i> Add Program
                                    </a>
                                </div>
                                
                                <!-- Set as Current Semester -->
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$semester->is_current): ?>
                                    <div class="mt-4">
                                        <form action="<?php echo e(route('academics.settings.update')); ?>" method="POST">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="academic_year_id" value="<?php echo e($semester->academic_year_id); ?>">
                                            <input type="hidden" name="semester_id" value="<?php echo e($semester->id); ?>">
                                            <button type="submit" class="btn btn-success" onclick="return confirm('Set <?php echo e($semester->name); ?> as the current semester? This will update system-wide defaults.')">
                                                <i class="fas fa-check-circle me-1"></i> Set as Current Semester
                                            </button>
                                        </form>
                                    </div>
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
<?php /**PATH /home/johnsonsebire/www/college/resources/views/academics/semesters/show.blade.php ENDPATH**/ ?>