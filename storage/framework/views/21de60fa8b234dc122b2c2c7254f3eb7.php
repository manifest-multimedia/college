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
        Grade Type Details
     <?php $__env->endSlot(); ?>
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h1 class="card-title">
                                <i class="fas fa-star me-2"></i>Grade Type: <?php echo e($grade->name); ?>

                            </h1>
                            <div>
                                <a href="<?php echo e(route('academics.grades.edit', $grade)); ?>" class="btn btn-primary me-2">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </a>
                                <a href="<?php echo e(route('academics.grades.index')); ?>" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> Back to Grade Types
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
                                <table class="table table-striped">
                                    <tbody>
                                        <tr>
                                            <th style="width: 30%;">Name</th>
                                            <td><?php echo e($grade->name); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Type</th>
                                            <td><?php echo e($grade->type ?? 'Not specified'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Value</th>
                                            <td><?php echo e($grade->value ?? 'Not specified'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Description</th>
                                            <td><?php echo e($grade->description ?? 'No description available'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Created At</th>
                                            <td><?php echo e($grade->created_at->format('F j, Y, g:i a')); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Last Updated</th>
                                            <td><?php echo e($grade->updated_at->format('F j, Y, g:i a')); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <h3>Usage Statistics</h3>
                            <p>This grade type is used in <strong><?php echo e($grade->studentGrades()->count()); ?></strong> student records.</p>
                            
                            <button type="button" class="btn btn-danger" 
                                    onclick="event.preventDefault(); 
                                             if(confirm('Are you sure you want to delete this grade type? This action cannot be undone.')) { 
                                                document.getElementById('delete-form').submit(); 
                                             }">
                                <i class="fas fa-trash me-1"></i> Delete Grade Type
                            </button>
                            <form id="delete-form" action="<?php echo e(route('academics.grades.destroy', $grade)); ?>" method="POST" style="display: none;">
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
<?php endif; ?><?php /**PATH /home/johnsonsebire/www/college/resources/views/academics/grades/show.blade.php ENDPATH**/ ?>