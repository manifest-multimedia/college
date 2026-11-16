<div>
    
    <div class="container">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mode === 'index'): ?>
            <h2 class="my-4">Exam Results Dashboard</h2>

            <!-- Filters -->
            <div class="mb-4">
                <div class="row g-2 d-flex justify-content-between">
                    <div class="col-md-4">
                        <label for="exam-select" class="form-label">Filter By Exam</label>
                        <select class="form-select" wire:model.live="selected_exam_id">
                            <option value="">Select Exam</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $exams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exam): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($exam->id); ?>"><?php echo e($exam->display_title); ?> — <?php echo e($exam->course->name ?? 'Unknown Course'); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="college-class-select" class="form-label">Filter By Program</label>
                        <select class="form-select" wire:model.live="selected_college_class_id">
                            <option value="">Select Program</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $collegeClasses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $collegeClass): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($collegeClass->id); ?>"><?php echo e($collegeClass->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end gap-2">
                        <button class="btn <?php echo e($selected_exam_id && $selected_college_class_id ? 'btn-primary' : 'btn-secondary'); ?>" wire:click="generateResults" <?php if(!$selected_exam_id || !$selected_college_class_id): echo 'disabled'; endif; ?>>
                            Generate Results
                        </button>
                        <button class="btn <?php echo e($selected_exam_id && $selected_college_class_id ? 'btn-primary' : 'btn-secondary'); ?>" wire:click="exportResults" <?php if(!$selected_exam_id || !$selected_college_class_id): echo 'disabled'; endif; ?>>
                            Download
                        </button>
                        <button class="btn btn-primary" wire:click="exportBulkResults">
                            Bulk Export
                        </button>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <h3>Results</h3>
                    </div>
                </div>
                <div class="card-body">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selected_exam_id && $selected_college_class_id): ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($results->count() > 0): ?>
                            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Date</th>
                                            <th>Student ID</th>
                                            <th>Student Name</th>
                                            <th>Course</th>
                                            <th>Score</th>
                                            <th>Answered</th>
                                            <th>Percentage</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $results; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $result): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <!-- Number Results from 1 to n keeping in mind that results have been sorted in ascending order -->
                                                <td><?php echo e($loop->iteration); ?></td>
                                                <td><?php echo e($result['date']); ?></td>
                                                <td><?php echo e($result['student_id']); ?></td>
                                                <td><?php echo e($result['student_name']); ?></td>
                                                <td><?php echo e($result['course']); ?></td>
                                                <td><?php echo e($result['score']); ?></td>
                                                <td><?php echo e($result['answered']); ?></td>
                                                <td><?php echo e($result['percentage']); ?>%</td>
                                                <td>
                                                    <!-- Export Button -->
                                                    <a href="javascript:void(0)" class="btn btn-primary export-button" wire:click="exportStudentResult('<?php echo e($result['student_id']); ?>')">
                                                       Export
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <div class="action-buttons">
                                    <button wire:click="exportResults" class="btn btn-primary">
                                        Download Results
                                    </button>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="d-flex justify-content-center py-5 align-items-center gap-2">
                                <button class="btn btn-primary" wire:click="generateResults">
                                    Generate Results
                                </button>
                                <button class="btn btn-primary" wire:click="exportResults">Download Results</button>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php else: ?>
                        <div class="d-flex justify-content-center py-5 align-items-center">
                            <p class="text-muted">Select an exam and program to generate and view results.</p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/livewire/exam-results-module.blade.php ENDPATH**/ ?>