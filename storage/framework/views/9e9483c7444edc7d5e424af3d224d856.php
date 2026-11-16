<?php $__env->startPush('scripts'); ?>
    <script>
        document.querySelectorAll('.js-set-current-academic-year').forEach((button) => {
            button.addEventListener('click', (event) => {
                event.preventDefault();

                Swal.fire({
                    title: 'Set current Academic Year?',
                    text: `${button.dataset.academicYearName} and its first semester will become the active academic period.`,
                    icon: 'warning',
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: 'Yes, set as current',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        confirmButton: 'btn fw-bold btn-success',
                        cancelButton: 'btn fw-bold btn-active-light-primary'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        button.form.submit();
                    }
                });
            });
        });
    </script>
<?php $__env->stopPush(); ?>

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
        Academic Year Details
     <?php $__env->endSlot(); ?>
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title">
                                <i class="fas fa-calendar-alt me-2"></i>Academic Year Details
                            </h5>
                            <div>
                                <a href="<?php echo e(route('academics.academic-years.index')); ?>" class="btn btn-sm btn-secondary me-2">
                                    <i class="fas fa-arrow-left me-1"></i> Back to Academic Years
                                </a>
                                <a href="<?php echo e(route('academics.academic-years.edit', $academicYear)); ?>" class="btn btn-sm btn-warning">
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
                                            <th style="width: 30%">Academic Year</th>
                                            <td><?php echo e($academicYear->name); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Year</th>
                                            <td><?php echo e($academicYear->year); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Start Date</th>
                                            <td><?php echo e($academicYear->start_date->format('F d, Y')); ?></td>
                                        </tr>
                                        <tr>
                                            <th>End Date</th>
                                            <td><?php echo e($academicYear->end_date->format('F d, Y')); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
                                            <td>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($academicYear->is_current): ?>
                                                    <span class="badge bg-success">Current Academic Year</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inactive</span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                
                                <!-- Semesters -->
                                <div class="mt-4">
                                    <h4>Semesters</h4>
                                    <hr>
                                    
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($academicYear->semesters->count() > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Name</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $academicYear->semesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $semester): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <tr>
                                                            <td><?php echo e($semester->name); ?></td>
                                                            <td>
                                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($semester->is_current): ?>
                                                                    <span class="badge bg-success">Current</span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-secondary">Inactive</span>
                                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <a href="<?php echo e(route('academics.semesters.show', $semester)); ?>" class="btn btn-sm btn-info">
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
                                            No semesters have been created for this academic year.
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    
                                    <a href="<?php echo e(route('academics.semesters.create')); ?>" class="btn btn-primary mt-2">
                                        <i class="fas fa-plus me-1"></i> Add Semester
                                    </a>
                                </div>
                                
                                <!-- Set as Current Year -->
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$academicYear->is_current): ?>
                                    <div class="mt-4">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($academicYear->semesters->isNotEmpty()): ?>
                                            <form action="<?php echo e(route('academics.settings.update')); ?>" method="POST">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="academic_year_id" value="<?php echo e($academicYear->id); ?>">
                                                <input type="hidden" name="semester_id" value="<?php echo e($academicYear->semesters->first()?->id); ?>">
                                                <button type="submit" class="btn btn-success js-set-current-academic-year" data-academic-year-name="<?php echo e($academicYear->name); ?>">
                                                    <i class="fas fa-check-circle me-1"></i> Set as Current Academic Year
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <div class="alert alert-warning mb-0">
                                                Add a semester for this Academic Year before making it current.
                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
<?php /**PATH /home/johnsonsebire/www/college/resources/views/academics/academic-years/show.blade.php ENDPATH**/ ?>