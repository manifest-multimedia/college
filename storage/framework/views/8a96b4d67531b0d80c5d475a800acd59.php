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
        Program Details
     <?php $__env->endSlot(); ?>
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title">
                                <i class="fas fa-graduation-cap me-2"></i>Program Details
                            </h5>
                            <div>
                                <a href="<?php echo e(route('academics.classes.index')); ?>" class="btn btn-sm btn-secondary me-2">
                                    <i class="fas fa-arrow-left me-1"></i> Back to Programs
                                </a>
                                <a href="<?php echo e(route('academics.classes.edit', $class)); ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit me-1"></i> Edit
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
                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo e(session('error')); ?>

                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <div class="row mb-4">
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="card-title mb-0">Program Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-striped">
                                            <tbody>
                                                <tr>
                                                    <th style="width: 30%">Program Name</th>
                                                    <td><?php echo e($class->name); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Short Name (Program Code)</th>
                                                    <td>
                                                        <span class="badge bg-primary"><?php echo e($class->getProgramCode()); ?></span>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($class->short_name)): ?>
                                                            <small class="text-muted ms-2">Auto-generated from program name</small>
                                                        <?php else: ?>
                                                            <small class="text-muted ms-2">Custom short name</small>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Description</th>
                                                    <td><?php echo e($class->description ?: 'No description provided'); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Status</th>
                                                    <td>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($class->is_active): ?>
                                                            <span class="badge bg-success">Active</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">Inactive</span>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Created</th>
                                                    <td><?php echo e($class->created_at->format('F d, Y \a\t g:i A')); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Last Updated</th>
                                                    <td><?php echo e($class->updated_at->format('F d, Y \a\t g:i A')); ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        
                                        <div class="alert alert-info mt-3">
                                            <i class="fas fa-info-circle me-2"></i>
                                            <strong>Academic Program:</strong> This is a semester-independent educational offering that provides a structured pathway for student learning across multiple academic periods.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="card-title mb-0">Quick Stats</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3 text-center">
                                            <h5>Enrolled Students</h5>
                                            <h1 class="display-5"><?php echo e($class->students->count()); ?></h1>
                                        </div>
                                        
                                        <div class="text-center">
                                            <h5>Graded Students</h5>
                                            <h1 class="display-5"><?php echo e($studentGrades->count()); ?></h1>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <div class="d-grid gap-2">
                                            <a href="<?php echo e(route('academics.classes.batch-grades', $class)); ?>" class="btn btn-primary">
                                                <i class="fas fa-star me-1"></i> Batch Assign Grades
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Student Roster -->
                        <div class="card mt-4">
                            <div class="card-header bg-secondary text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-user-graduate me-1"></i> Enrolled Students
                                    </h5>
                                    <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#addStudentsModal">
                                        <i class="fas fa-user-plus me-1"></i> Add Students
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($class->students->count() > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Student ID</th>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Current Grade</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $class->students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <tr>
                                                        <td><?php echo e($student->student_id ?? 'No ID'); ?></td>
                                                        <td><?php echo e($student->name); ?></td>
                                                        <td><?php echo e($student->email); ?></td>
                                                        <td>
                                                            <?php
                                                                $grade = $studentGrades->where('student_id', $student->id)->first();
                                                            ?>
                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($grade): ?>
                                                                <span class="badge bg-success"><?php echo e($grade->grade->name ?? 'Unknown Grade'); ?></span>
                                                            <?php else: ?>
                                                                <span class="badge bg-warning">Not Graded</span>
                                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm" role="group">
                                                                <!-- Assign Grade Button -->
                                                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" 
                                                                        data-bs-target="#gradeModal<?php echo e($student->id); ?>">
                                                                    <i class="fas fa-star"></i> Grade
                                                                </button>
                                                                
                                                                <!-- Remove from Class Button -->
                                                                <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                                                        data-bs-target="#removeStudentModal<?php echo e($student->id); ?>">
                                                                    <i class="fas fa-user-minus"></i>
                                                                </button>
                                                            </div>
                                                            
                                                            <!-- Grade Modal -->
                                                            <div class="modal fade" id="gradeModal<?php echo e($student->id); ?>" tabindex="-1" 
                                                                 aria-labelledby="gradeModalLabel<?php echo e($student->id); ?>" aria-hidden="true">
                                                                <div class="modal-dialog">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header bg-primary text-white">
                                                                            <h5 class="modal-title" id="gradeModalLabel<?php echo e($student->id); ?>">
                                                                                Assign Grade for <?php echo e($student->name); ?>

                                                                            </h5>
                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                        </div>
                                                                        <form action="<?php echo e(route('academics.student-grades.store')); ?>" method="POST">
                                                                            <?php echo csrf_field(); ?>
                                                                            <div class="modal-body">
                                                                                <input type="hidden" name="student_id" value="<?php echo e($student->id); ?>">
                                                                                <input type="hidden" name="college_class_id" value="<?php echo e($class->id); ?>">
                                                                                
                                                                                <div class="mb-3">
                                                                                    <label for="grade_id<?php echo e($student->id); ?>" class="form-label">Grade</label>
                                                                                    <select class="form-select" id="grade_id<?php echo e($student->id); ?>" name="grade_id" required>
                                                                                        <option value="">Select Grade</option>
                                                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = \App\Models\Grade::all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                                            <option value="<?php echo e($grade->id); ?>"><?php echo e($grade->name); ?> (<?php echo e($grade->description); ?>)</option>
                                                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                                                    </select>
                                                                                </div>
                                                                                
                                                                                <div class="mb-3">
                                                                                    <label for="comments<?php echo e($student->id); ?>" class="form-label">Comments (Optional)</label>
                                                                                    <textarea class="form-control" id="comments<?php echo e($student->id); ?>" name="comments" rows="3"></textarea>
                                                                                </div>
                                                                            </div>
                                                                            <div class="modal-footer">
                                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                                <button type="submit" class="btn btn-primary">Assign Grade</button>
                                                                            </div>
                                                                        </form>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- Remove Student Modal -->
                                                            <div class="modal fade" id="removeStudentModal<?php echo e($student->id); ?>" tabindex="-1"
                                                                 aria-labelledby="removeStudentModalLabel<?php echo e($student->id); ?>" aria-hidden="true">
                                                                <div class="modal-dialog">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header bg-danger text-white">
                                                                            <h5 class="modal-title" id="removeStudentModalLabel<?php echo e($student->id); ?>">
                                                                                Remove Student
                                                                            </h5>
                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            Are you sure you want to remove <strong><?php echo e($student->name); ?></strong> from this program?
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                            <form action="<?php echo e(route('academics.classes.students.remove', [$class->id, $student->id])); ?>" method="POST">
                                                                                <?php echo csrf_field(); ?>
                                                                                <?php echo method_field('DELETE'); ?>
                                                                                <button type="submit" class="btn btn-danger">Remove</button>
                                                                            </form>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        No students are currently enrolled in this program. Click "Add Students" to enroll students.
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Add Students Modal -->
                        <div class="modal fade" id="addStudentsModal" tabindex="-1" aria-labelledby="addStudentsModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title" id="addStudentsModalLabel">Add Students to Program</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="<?php echo e(route('academics.classes.students.add', $class->id)); ?>" method="POST">
                                        <?php echo csrf_field(); ?>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Select Students</label>
                                                <div class="alert alert-info">
                                                    Select students to add to this program. Hold Ctrl or ⌘ to select multiple students.
                                                </div>
                                                <select class="form-select" name="student_ids[]" multiple size="10" required>
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = \App\Models\Student::where('status', 'active')->where(function($query) use ($class) {
                                                        $query->whereNull('college_class_id')->orWhere('college_class_id', '!=', $class->id);
                                                    })->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($student->id); ?>">
                                                            <?php echo e($student->name); ?> (<?php echo e($student->student_id ?? 'No ID'); ?>) - <?php echo e($student->email); ?>

                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($student->college_class_id): ?>
                                                                <span class="text-muted">(Currently in: <?php echo e($student->collegeClass->name ?? 'Unknown Program'); ?>)</span>
                                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                        </option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Add Selected Students</button>
                                        </div>
                                    </form>
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
<?php endif; ?><?php /**PATH /home/johnsonsebire/www/college/resources/views/academics/classes/show.blade.php ENDPATH**/ ?>