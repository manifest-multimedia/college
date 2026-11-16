<div>
    <div class="card">
        <div class="card-header">
            <h3>Import Exam Results</h3>
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

            <div class="mb-3">
                <label for="file" class="form-label">Select File</label>
                <input type="file" class="form-control" wire:model="file">
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($importing): ?>
                <div class="progress mb-3">
                    <div class="progress-bar" role="progressbar" style="width: <?php echo e($progress); ?>%">
                        <?php echo e($progress); ?>%
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <button class="btn btn-primary" wire:click="import" <?php if(!$file || !$selected_exam_id): echo 'disabled'; endif; ?>>
                Import Results
            </button>
        </div>
    </div>
</div> 
<?php /**PATH /home/johnsonsebire/www/college/resources/views/livewire/exam-results-import-module.blade.php ENDPATH**/ ?>