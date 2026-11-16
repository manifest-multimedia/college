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
        Student Grade Details
     <?php $__env->endSlot(); ?>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h1 class="card-title">
                                <i class="fas fa-graduation-cap me-2"></i>Student Grade Details
                            </h1>
                            <div>
                                <a href="<?php echo e(route('academics.student-grades.edit', $studentGrade)); ?>" class="btn btn-primary me-2">
                                    <i class="fas fa-edit me-1"></i> Edit Grade
                                </a>
                                <a href="<?php echo e(route('academics.student-grades.index')); ?>" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> Back to Grades
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php echo e(session('success')); ?>

                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <div class="row">
                            <div class="col-md-6">
                                <h4>Student Information</h4>
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="30%">Student ID</th>
                                        <td><?php echo e($studentGrade->student->student_id); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Name</th>
                                        <td><?php echo e($studentGrade->student->first_name); ?> <?php echo e($studentGrade->student->last_name); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Program</th>
                                        <td><?php echo e($studentGrade->student && $studentGrade->student->program ? $studentGrade->student->program->name : 'N/A'); ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h4>Program Information</h4>
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="30%">Program</th>
                                        <td><?php echo e($studentGrade->collegeClass ? $studentGrade->collegeClass->name : 'N/A'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Program Code</th>
                                        <td><?php echo e($studentGrade->collegeClass ? $studentGrade->collegeClass->getProgramCode() : 'N/A'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Short Name</th>
                                        <td><?php echo e($studentGrade->collegeClass && $studentGrade->collegeClass->short_name ? $studentGrade->collegeClass->short_name : 'N/A'); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <h4>Grade Information</h4>
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="30%">Letter Grade</th>
                                        <td>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($studentGrade->grade): ?>
                                                <span class="badge bg-info fs-6"><?php echo e($studentGrade->grade->letter); ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary fs-6">N/A</span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Grade Value</th>
                                        <td><?php echo e($studentGrade->grade ? $studentGrade->grade->value : 'N/A'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Comments</th>
                                        <td><?php echo e($studentGrade->comments ?: 'No comments'); ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h4>Grading Information</h4>
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="30%">Graded By</th>
                                        <td><?php echo e($studentGrade->gradedBy?->name ?? 'System'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Graded On</th>
                                        <td><?php echo e($studentGrade->updated_at->format('M d, Y h:i A')); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Created On</th>
                                        <td><?php echo e($studentGrade->created_at->format('M d, Y h:i A')); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="button" class="btn btn-danger" 
                                    onclick="event.preventDefault(); 
                                             if(confirm('Are you sure you want to delete this grade record? This action cannot be undone.')) { 
                                                document.getElementById('delete-form').submit(); 
                                             }">
                                <i class="fas fa-trash me-1"></i> Delete Grade
                            </button>
                            <form id="delete-form" action="<?php echo e(route('academics.student-grades.destroy', $studentGrade)); ?>" method="POST" style="display: none;">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                            </form>
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
<?php endif; ?><?php /**PATH /home/johnsonsebire/www/college/resources/views/academics/student-grades/show.blade.php ENDPATH**/ ?>