<?php
    $academicsService = app(\App\Services\AcademicsService::class);
    $currentAcademicYear = $academicsService->getCurrentAcademicYear();
    $currentSemester = $academicsService->getCurrentSemester();
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentAcademicYear || $currentSemester): ?>
    <div class="d-none d-md-flex align-items-center me-2 me-lg-4" title="Current academic period">
        <div class="d-flex align-items-center rounded px-3 py-2 bg-white bg-opacity-10 text-white">
            <i class="ki-duotone ki-calendar-8 fs-2 me-2">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
            <div class="lh-sm">
                <div class="fs-8 opacity-75">Current Academic Period</div>
                <div class="fw-semibold fs-7">
                    <?php echo e($currentAcademicYear?->name ?? 'Academic Year not set'); ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentSemester): ?>
                        <span class="opacity-75 mx-1">•</span><?php echo e($currentSemester->name); ?>

                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/components/academic-context.blade.php ENDPATH**/ ?>