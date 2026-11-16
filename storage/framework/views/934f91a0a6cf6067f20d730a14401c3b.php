<?php if (isset($component)) { $__componentOriginal78458ab11901cb5e12c9728346766652 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78458ab11901cb5e12c9728346766652 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.default','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.default'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?><div class="container-fluid py-5"><a class="btn btn-light mb-4" href="<?php echo e(route('exams.answer-review.index', ['exam' => $session->exam_id])); ?>">Back to attempts</a><h1>Student Answer Review</h1><p><?php echo e($session->student?->name); ?> · <?php echo e($session->exam->display_title); ?> · <?php echo e($session->exam->course?->name); ?></p><div class="card mb-5"><div class="card-body"><h3>Performance summary</h3><p><strong><?php echo e($score['score']); ?></strong> · <?php echo e(number_format($score['percentage'], 2)); ?>% · <?php echo e($score['obtained_marks']); ?> / <?php echo e($score['total_marks']); ?> marks</p><p><?php echo e($review->where('status', 'correct')->count()); ?> correct · <?php echo e($review->where('status', 'incorrect')->count()); ?> incorrect · <?php echo e($review->where('status', 'unanswered')->count()); ?> unanswered</p></div></div>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $review; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div class="card mb-3"><div class="card-body"><div class="d-flex justify-content-between"><h4>Question <?php echo e($loop->iteration); ?></h4><span class="badge badge-light-<?php echo e($item['status'] === 'correct' ? 'success' : ($item['status'] === 'incorrect' ? 'danger' : 'warning')); ?>"><?php echo e(ucfirst($item['status'])); ?></span></div><p><?php echo e($item['question']); ?></p><p><strong>Student answer:</strong> <?php echo e($item['selected'] ?? 'Not answered'); ?></p><p><strong>Correct answer:</strong> <?php echo e($item['correct'] ?? 'No correct option defined'); ?></p><p><strong>Question marks:</strong> <?php echo e($item['mark']); ?></p></div></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78458ab11901cb5e12c9728346766652)): ?>
<?php $attributes = $__attributesOriginal78458ab11901cb5e12c9728346766652; ?>
<?php unset($__attributesOriginal78458ab11901cb5e12c9728346766652); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78458ab11901cb5e12c9728346766652)): ?>
<?php $component = $__componentOriginal78458ab11901cb5e12c9728346766652; ?>
<?php unset($__componentOriginal78458ab11901cb5e12c9728346766652); ?>
<?php endif; ?>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/exams/student-answer-review-show.blade.php ENDPATH**/ ?>