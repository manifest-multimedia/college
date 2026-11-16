<div>
    <div class="card">
        <div class="card-header">
            <h3>Export Exam Results</h3>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="exam-select" class="form-label">Select Exam</label>
                <select class="form-select" wire:model="selected_exam_id">
                    <option value="">Select Exam</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $exams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exam): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($exam->id); ?>"><?php echo e($exam->display_title); ?> — <?php echo e($exam->course->name ?? 'Unknown Course'); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
            </div>
            
            <button class="btn btn-primary" wire:click="export" <?php if(!$selected_exam_id): echo 'disabled'; endif; ?>>
                Export Results
            </button>
        </div>
    </div>
</div> 
<?php /**PATH /home/johnsonsebire/www/college/resources/views/livewire/exam-results-export-module.blade.php ENDPATH**/ ?>