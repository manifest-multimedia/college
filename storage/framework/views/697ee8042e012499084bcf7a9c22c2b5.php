<?php if (isset($component)) { $__componentOriginal78458ab11901cb5e12c9728346766652 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78458ab11901cb5e12c9728346766652 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.default','data' => ['title' => 'User Dashboard']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.default'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'User Dashboard']); ?>

    <!--begin::Row-->
    <div class="row">

        <?php if (isset($component)) { $__componentOriginald95fc9fb33d872fb8027faa767a0f237 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald95fc9fb33d872fb8027faa767a0f237 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.partials.dashboard-notifications','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('partials.dashboard-notifications'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald95fc9fb33d872fb8027faa767a0f237)): ?>
<?php $attributes = $__attributesOriginald95fc9fb33d872fb8027faa767a0f237; ?>
<?php unset($__attributesOriginald95fc9fb33d872fb8027faa767a0f237); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald95fc9fb33d872fb8027faa767a0f237)): ?>
<?php $component = $__componentOriginald95fc9fb33d872fb8027faa767a0f237; ?>
<?php unset($__componentOriginald95fc9fb33d872fb8027faa767a0f237); ?>
<?php endif; ?>


        <!-- Dashboard Counters -->
        <div class="row g-5 g-xl-8 mt-5">
            <?php if (isset($component)) { $__componentOriginal66e616e34e84e5c289728a9e93a73807 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal66e616e34e84e5c289728a9e93a73807 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.counters','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.counters'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal66e616e34e84e5c289728a9e93a73807)): ?>
<?php $attributes = $__attributesOriginal66e616e34e84e5c289728a9e93a73807; ?>
<?php unset($__attributesOriginal66e616e34e84e5c289728a9e93a73807); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal66e616e34e84e5c289728a9e93a73807)): ?>
<?php $component = $__componentOriginal66e616e34e84e5c289728a9e93a73807; ?>
<?php unset($__componentOriginal66e616e34e84e5c289728a9e93a73807); ?>
<?php endif; ?>
        </div>
        <!-- End Dashboard Counters -->

        <!-- Dashboard Content Section -->
        <div class="row g-5 gx-xl-10 mt-5">

            <!-- Left Column - Icon Cards -->
            <div class="col-xl-8">
                <div class="row g-5">
                    
                    <?php if (isset($component)) { $__componentOriginalcca28088b9e9c025e4a3f4ba46d7c91f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcca28088b9e9c025e4a3f4ba46d7c91f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.icon-card','data' => ['title' => 'Exam Center','description' => 'Take Exams, Assignments and Quizes','icon' => 'ki-notepad-edit','route' => route('examcenter')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.icon-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Exam Center','description' => 'Take Exams, Assignments and Quizes','icon' => 'ki-notepad-edit','route' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('examcenter'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcca28088b9e9c025e4a3f4ba46d7c91f)): ?>
<?php $attributes = $__attributesOriginalcca28088b9e9c025e4a3f4ba46d7c91f; ?>
<?php unset($__attributesOriginalcca28088b9e9c025e4a3f4ba46d7c91f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcca28088b9e9c025e4a3f4ba46d7c91f)): ?>
<?php $component = $__componentOriginalcca28088b9e9c025e4a3f4ba46d7c91f; ?>
<?php unset($__componentOriginalcca28088b9e9c025e4a3f4ba46d7c91f); ?>
<?php endif; ?>

                    <?php if (isset($component)) { $__componentOriginalcca28088b9e9c025e4a3f4ba46d7c91f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcca28088b9e9c025e4a3f4ba46d7c91f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.icon-card','data' => ['title' => 'Question Bank','description' => 'Browse and Review Question Sets','icon' => 'ki-questionnaire-tablet','route' => route('questionbank')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.icon-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Question Bank','description' => 'Browse and Review Question Sets','icon' => 'ki-questionnaire-tablet','route' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('questionbank'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcca28088b9e9c025e4a3f4ba46d7c91f)): ?>
<?php $attributes = $__attributesOriginalcca28088b9e9c025e4a3f4ba46d7c91f; ?>
<?php unset($__attributesOriginalcca28088b9e9c025e4a3f4ba46d7c91f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcca28088b9e9c025e4a3f4ba46d7c91f)): ?>
<?php $component = $__componentOriginalcca28088b9e9c025e4a3f4ba46d7c91f; ?>
<?php unset($__componentOriginalcca28088b9e9c025e4a3f4ba46d7c91f); ?>
<?php endif; ?>

                    <?php if (isset($component)) { $__componentOriginalcca28088b9e9c025e4a3f4ba46d7c91f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcca28088b9e9c025e4a3f4ba46d7c91f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.icon-card','data' => ['title' => 'Staff Mail','description' => 'Access Corporate Email Services','icon' => 'ki-message-text-2','route' => route('staffmail')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.icon-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Staff Mail','description' => 'Access Corporate Email Services','icon' => 'ki-message-text-2','route' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('staffmail'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcca28088b9e9c025e4a3f4ba46d7c91f)): ?>
<?php $attributes = $__attributesOriginalcca28088b9e9c025e4a3f4ba46d7c91f; ?>
<?php unset($__attributesOriginalcca28088b9e9c025e4a3f4ba46d7c91f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcca28088b9e9c025e4a3f4ba46d7c91f)): ?>
<?php $component = $__componentOriginalcca28088b9e9c025e4a3f4ba46d7c91f; ?>
<?php unset($__componentOriginalcca28088b9e9c025e4a3f4ba46d7c91f); ?>
<?php endif; ?>

                    <?php if (isset($component)) { $__componentOriginalcca28088b9e9c025e4a3f4ba46d7c91f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcca28088b9e9c025e4a3f4ba46d7c91f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.icon-card','data' => ['title' => 'Support Center','description' => 'Access Technical Support','icon' => 'ki-rescue','route' => route('supportcenter')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.icon-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Support Center','description' => 'Access Technical Support','icon' => 'ki-rescue','route' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('supportcenter'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcca28088b9e9c025e4a3f4ba46d7c91f)): ?>
<?php $attributes = $__attributesOriginalcca28088b9e9c025e4a3f4ba46d7c91f; ?>
<?php unset($__attributesOriginalcca28088b9e9c025e4a3f4ba46d7c91f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcca28088b9e9c025e4a3f4ba46d7c91f)): ?>
<?php $component = $__componentOriginalcca28088b9e9c025e4a3f4ba46d7c91f; ?>
<?php unset($__componentOriginalcca28088b9e9c025e4a3f4ba46d7c91f); ?>
<?php endif; ?>

                </div>
            </div>
            <!-- End Left Column -->

            <!-- Right Column - Support Widget -->
            <div class="col-xl-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Route::currentRouteName() == 'dashboard'): ?>
                    <?php if (isset($component)) { $__componentOriginal087cafadd454f45ef86071c5bc81f217 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal087cafadd454f45ef86071c5bc81f217 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app.support-widget','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app.support-widget'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal087cafadd454f45ef86071c5bc81f217)): ?>
<?php $attributes = $__attributesOriginal087cafadd454f45ef86071c5bc81f217; ?>
<?php unset($__attributesOriginal087cafadd454f45ef86071c5bc81f217); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal087cafadd454f45ef86071c5bc81f217)): ?>
<?php $component = $__componentOriginal087cafadd454f45ef86071c5bc81f217; ?>
<?php unset($__componentOriginal087cafadd454f45ef86071c5bc81f217); ?>
<?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <!-- End Right Column -->

        </div>
        <!-- End Dashboard Content Section -->
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
<?php endif; ?>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/dashboard.blade.php ENDPATH**/ ?>