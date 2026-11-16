<div class="table-responsive">
    <table class="table align-middle table-row-dashed fs-6 gy-5 table-bordered">
        <thead>
            <!-- Year Header -->
            <tr class="text-center text-gray-800 fw-bold fs-7 text-uppercase gs-0 bg-light">
                <th colspan="3" class="border-end border-bottom-0"></th>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $report->reportSemesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $yearLabel => $semesters): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <th colspan="<?php echo e(count($semesters)); ?>" class="border-end" style="background-color: #f5f5dc;">
                        <?php echo e(strtoupper($yearLabel)); ?>

                    </th>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <th colspan="3" class="border-start-0 border-bottom-0"></th>
            </tr>
            
            <!-- Semester Header -->
            <tr class="text-center text-gray-800 fw-bold fs-7 text-uppercase gs-0 bg-light">
                <th colspan="3" class="border-end border-top-0 border-bottom-0"></th>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $report->reportSemesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $yearLabel => $semesters): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $semesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $semesterKey => $semesterName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <th class="border-end" style="background-color: #e8f4f8;">
                            <?php echo e(strtoupper($semesterName)); ?>

                        </th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <th colspan="3" class="border-start-0 border-top-0 border-bottom-0"></th>
            </tr>
            
            <!-- Column Header -->
            <tr class="text-center text-gray-800 fw-bold fs-7 text-uppercase gs-0" style="background-color: #e0e0e0;">
                <th class="border-end">SERIAL NO</th>
                <th class="border-end text-start">INDEX NUMBER</th>
                <th class="border-end text-start">NAME</th>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $report->reportSemesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $yearLabel => $semesters): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $semesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $semesterKey => $semesterName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <th class="border-end">GPA</th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <th class="border-end" style="background-color: #ffd700;">CGPA</th>
                <th class="border-end" style="background-color: #ffd700;">CLASS DESIGNATION</th>
                <th style="background-color: #ffd700;">REMARKS</th>
            </tr>
        </thead>
        <tbody class="fw-semibold text-gray-600">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $remarkBg = '';
                    $remarkText = 'text-gray-800';
                    if ($student['remarks'] === 'REPEATED') {
                        $remarkBg = 'bg-light-danger';
                        $remarkText = 'text-danger';
                    }
                    if ($student['remarks'] === 'DISMISSED') {
                        $remarkBg = 'bg-light-dark';
                    }
                    if ($student['remarks'] === 'PASS') {
                        $remarkBg = 'bg-light-success';
                        $remarkText = 'text-success';
                    }
                ?>
                <tr class="text-center">
                    <td class="border-end"><?php echo e($index + 1); ?></td>
                    <td class="border-end text-start"><?php echo e($student['index_number']); ?></td>
                    <td class="border-end text-start"><?php echo e($student['name']); ?></td>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $report->reportSemesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $yearLabel => $semesters): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $semesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $semesterKey => $semesterName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <td class="border-end fw-bold text-gray-800">
                                <?php echo e($student[$semesterKey] ?? '-'); ?>

                            </td>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    
                    <td class="border-end fw-bold text-gray-800"><?php echo e($student['cgpa']); ?></td>
                    <td class="border-end fw-bold <?php echo e($remarkBg); ?> <?php echo e($remarkText); ?>"><?php echo e($student['class_designation']); ?></td>
                    <td class="fw-bold <?php echo e($remarkBg); ?> <?php echo e($remarkText); ?>"><?php echo e($student['remarks']); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
    </table>
</div>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/reports/ui/publication_sheet_ui.blade.php ENDPATH**/ ?>