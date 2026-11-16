<?php $__env->startSection('content'); ?>
<div>
	<?php echo $__env->make('larecipe::partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
	
	<div class="documentation is-<?php echo e(config('larecipe.ui.code_theme')); ?>" :class="{'expanded': ! sidebar}">
		<?php echo $content; ?>

		<?php echo $__env->make('larecipe::plugins.forum', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
	</div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('larecipe::default', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/johnsonsebire/www/college/vendor/binarytorch/larecipe/resources/views/docs.blade.php ENDPATH**/ ?>