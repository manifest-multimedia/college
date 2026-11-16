<div>


<style>
    .pagination-container {
        display: flex;
        justify-content: center;
        margin-top: 20px;
    }
    .pagination-container p{
        margin: 0;
    }
</style>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mode === 'index'): ?>


        <div class="card border-">
          

                <div class="card-header d-flex align-items-center">
                    <div class="card-title">
                        <h2 class="my-4">Exam Sessions Management</h2>
                    </div>
                    <div class="gap-2 card-toolbar d-flex justify-content-between align-items-center flex-grow-1">
                        <!-- Filters -->
                        <div class="flex-row gap-2 d-flex g-2 justify-content-between align-items-center">

                            <div>
                                <input type="text" class="form-control" wire:model.live="filter_student_id"
                                    placeholder="Search by Student ID">
                            </div>
                            <div>
                                <input type="text" class="form-control" wire:model.live="filter_email"
                                    placeholder="Search by Email">
                            </div>
                            <div>
                                
                                    <select name="filter_by_exam" id="exam_id" class="form-select form-control"
                                    wire:model.live="filter_by_exam"
                                    >
                                        <option value="">Select Exam</option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $exams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exam): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <option value="<?php echo e($exam->id); ?>"><?php echo e($exam->display_title); ?> — <?php echo e($exam->course->name ?? 'Unknown Course'); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <option value="">No Exams Found</option>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </select>

                                
                            </div>
                            <div>
                                    
                                    <select name="filter_by_class" id="class_id"
                                    wire:model.live="filter_by_class" class="form-select form-control"
                                    >
                                        <option value="">Select Program</option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <option value="<?php echo e($class->id); ?>"><?php echo e($class->name); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <option value="">Select an Exam with Valid Sessions to Load Programs</option>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </select>
                            </div>

                            <div>
                                <button class="btn btn-success" wire:click="downloadResults" style="width:150px">
                                <i class="bi bi-file-earmark-spreadsheet"></i>
                                Download</button>

                            </div>
                        </div>
                    </div>
                </div>
                  <div class="card-body">

        <!-- Table -->
        <div class="overflow-auto table-responsive">
            <table class="table align-middle table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Student ID</th>
                        
                        <th>Name</th>
                        <th>Email</th>
                        <th>Performance</th>
                    </tr>
                </thead>


                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e(// Loop iteration for paginated data
                                ($students->currentPage() - 1) * $students->perPage() + $loop->iteration); ?>

                            </td>
                            <td><?php echo e($student->student_id); ?></td>
                            
                            <td><?php echo e($student->first_name); ?> <?php echo e($student->last_name); ?></td>
                            <td><?php echo e($student->email); ?></td>
                            <td>
                                
                   
                               <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($student->examSessions->isNotEmpty()): ?>
    <span class="text-white badge bg-dark">Sessions: <?php echo e($student->examSessions->count()); ?></span>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $student->examSessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="p-2 rounded border border-1 border-success">
            Course Name: <?php echo e(optional($session->exam->course)->name); ?><br>
            <span class="badge bg-success">Attempted
                <?php echo e(computeResults($session->id, 'total_answered')); ?> Questions</span>
            
            <div class="border d-flex badge border-success">
                Score: <?php echo e(computeResults($session->id, 'score')); ?>

                Percentage: <?php echo e(computeResults($session->id)); ?>

            </div>
        </div>
        
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php else: ?>
    <span class="badge bg-danger">No sessions or responses found</span>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            </td>
                           
                        </tr>
                        
                       
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                       <tr>
                           <td colspan="6" class="text-center">No records found</td>   
                       </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="pagination-container">
            <?php echo e($students->links()); ?>

        </div>
    <?php elseif($mode === 'view'): ?>
        <h2 class="my-4">Details for Student: <?php echo e($student->first_name); ?>

            <?php echo e($student->last_name); ?><?php echo e($student->other_name); ?></h2>

        <!-- User Account -->
        <div class="mb-4">
            <h3>User Account</h3>
            <p><strong>Name:</strong> <?php echo e($user->name ?? 'Not Found'); ?></p>
            <p><strong>Email:</strong> <?php echo e($user->email ?? 'Not Found'); ?></p>
            <button class="btn btn-secondary" wire:click="editDetails('user')">Edit User</button>
        </div>

        <!-- Student Details -->
        <div class="mb-4">
            <h3>Student Details</h3>
            <p><strong>Student ID:</strong> <?php echo e($student->student_id); ?></p>
            <p><strong>Email:</strong> <?php echo e($student->email); ?></p>
            <button class="btn btn-secondary" wire:click="editDetails('student')">Edit Student</button>
        </div>

        <!-- Exam Session -->

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($examSessions->count() > 0): ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $examSessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $examSession): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="mb-4">
                    <h3>Exam Session</h3>
                    <p><strong>Course Name:</strong> <?php echo e(optional($examSession->exam->course)->name); ?></p>
                    <p><strong>Started At:</strong> <?php echo e($examSession->started_at); ?></p>
                    <p><strong>Completed At:</strong> <?php echo e($examSession->completed_at); ?></p>
                    <p><strong>Duration:</strong> <?php echo e($examSession->duration); ?> minutes</p>
                    <p>Total Responses Received: <?php echo e($examSession->responses->count()); ?></p>
                    <button class="btn btn-secondary" wire:click="editDetails('examSession')">Edit ExamSession</button>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <button class="btn btn-primary" wire:click="back">Back</button>
    <?php elseif(str_starts_with($mode, 'edit')): ?>
        <div class="my-4">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mode === 'edit-student'): ?>
                <h2>Edit Student Details</h2>
                <div class="mb-3">
                    <label for="firstName" class="form-label">First Name</label>
                    <input type="text" id="firstName" class="form-control" wire:model="student.first_name">
                </div>
                <div class="mb-3">
                    <label for="lastName" class="form-label">Last Name</label>
                    <input type="text" id="lastName" class="form-control" wire:model="student.last_name">
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" class="form-control" wire:model="student.email">
                </div>
                <button class="btn btn-success" wire:click="updateStudent">Save</button>
                <button class="btn btn-primary" wire:click="back">Back</button>
            <?php elseif($mode === 'edit-user'): ?>
                <h2>Edit User Details</h2>
                <div class="mb-3">
                    <label for="userName" class="form-label">Name</label>
                    <input type="text" id="userName" class="form-control" wire:model="user.name">
                </div>
                <div class="mb-3">
                    <label for="userEmail" class="form-label">Email</label>
                    <input type="email" id="userEmail" class="form-control" wire:model="user.email">
                </div>
                <button class="btn btn-success" wire:click="updateUser">Save</button>
                <button class="btn btn-primary" wire:click="back">Back</button>
            <?php elseif($mode === 'edit-examSession'): ?>
                <h2>Edit ExamSession Details</h2>
                <div class="mb-3">
                    <label for="startedAt" class="form-label">Started At</label>
                    <input type="text" id="startedAt" class="form-control" wire:model="examSession.started_at">
                </div>
                <div class="mb-3">
                    <label for="completedAt" class="form-label">Completed At</label>
                    <input type="text" id="completedAt" class="form-control"
                        wire:model="examSession.completed_at">
                </div>
                <button class="btn btn-success" wire:click="updateExamSession">Save</button>
                <button class="btn btn-primary" wire:click="back">Back</button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

        </div>




</div>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/livewire/data-mismatch.blade.php ENDPATH**/ ?>