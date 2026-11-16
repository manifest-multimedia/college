<div class="container mt-4">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="alert alert-success">
            <?php echo e(session('success')); ?>

        </div>
        <p>Processed File Path: <?php echo e(session('file_path')); ?></p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <form action="<?php echo e(route('file.upload')); ?>" method="POST" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <div class="mb-3">
        
            
            <input type="hidden" name="exam_id" id="exam_id" class="form-control" value="<?php echo e($examId); ?>" required>
        </div>
        <div class="mb-3">
            <label for="file" class="form-label">Choose File</label>
            <div class="input-group">
                <input type="file" name="file" id="file" class="form-control" required>
                <button type="submit" class="btn btn-primary">Upload</button>
            </div>
        </div>
    </form>
</div>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/components/bulk-upload.blade.php ENDPATH**/ ?>