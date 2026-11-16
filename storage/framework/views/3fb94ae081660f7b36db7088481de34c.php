<?php
    $totalSemesters = 0;
    foreach($report->reportSemesters ?? [] as $semesters) {
        $totalSemesters += count($semesters);
    }
    $colspan = 3 + $totalSemesters + 3; // 3 info cols + semesters + 3 summary cols
?>
<table>
    <thead>
    <tr>
        <th colspan="<?php echo e($colspan); ?>" style="text-align: center; font-weight: bold; font-size: 14px; background-color: #fdf5e6; color: #000080;">
            <?php echo e(strtoupper(config('branding.institution.name', config('app.name', 'METHODIST HEALTH TRAINING INSTITUTE - AFOSU')))); ?>

        </th>
    </tr>
    <tr>
        <th colspan="<?php echo e($colspan); ?>" style="text-align: center; font-weight: bold; font-size: 12px; background-color: #fdf5e6;">
            CUMMULATIVE GRADE POINT PUBLICATION SHEET
        </th>
    </tr>
    <tr>
        <th colspan="<?php echo e($colspan); ?>" style="text-align: center; font-weight: bold; font-size: 12px; background-color: #f5deb3;">
            PROGRAMME: <?php echo e(strtoupper($report->reportProgram ?? 'N/A')); ?>

        </th>
    </tr>
    <tr>
        <th></th>
        <th></th>
        <th></th>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $report->reportSemesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $yearLabel => $semesters): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <th colspan="<?php echo e(count($semesters)); ?>" style="font-weight: bold; text-align: center; background-color: #f5f5dc; border: 1px solid #000000;"><?php echo e(strtoupper($yearLabel)); ?></th>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <th></th>
        <th></th>
        <th></th>
    </tr>
    <tr>
        <th></th>
        <th></th>
        <th></th>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $report->reportSemesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $yearLabel => $semesters): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $semesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $semesterId => $semesterName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <th style="font-weight: bold; text-align: center; background-color: #e8f4f8; border: 1px solid #000000;"><?php echo e(strtoupper($semesterName)); ?></th>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <th></th>
        <th></th>
        <th></th>
    </tr>
    <tr>
        <th style="font-weight: bold; border: 1px solid #000000; background-color: #e0e0e0;">SERIAL NO</th>
        <th style="font-weight: bold; border: 1px solid #000000; background-color: #e0e0e0;">INDEX NUMBER</th>
        <th style="font-weight: bold; border: 1px solid #000000; background-color: #e0e0e0;">NAME</th>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $report->reportSemesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $yearLabel => $semesters): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $semesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $semesterId => $semesterName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <th style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #e0e0e0;">GPA</th>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <th style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #ffd700;">CGPA</th>
        <th style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #ffd700;">CLASS DESIGNATION</th>
        <th style="font-weight: bold; text-align: center; border: 1px solid #000000; background-color: #ffd700;">REMARKS</th>
    </tr>
    </thead>
    <tbody>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $remarkColor = '#ffffff';
            if ($student['remarks'] === 'REPEATED') $remarkColor = '#ffe4e1';
            if ($student['remarks'] === 'DISMISSED') $remarkColor = '#dda0dd';
            if ($student['remarks'] === 'PASS') $remarkColor = '#e8f5e9';
        ?>
        <tr>
            <td style="text-align: center; border: 1px solid #000000;"><?php echo e($index + 1); ?></td>
            <td style="border: 1px solid #000000;"><?php echo e($student['index_number']); ?></td>
            <td style="border: 1px solid #000000;"><?php echo e($student['name']); ?></td>
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $report->reportSemesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $yearLabel => $semesters): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $semesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $semesterKey => $semesterName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <td style="text-align: center; border: 1px solid #000000; font-weight: bold;">
                        <?php echo e($student[$semesterKey] ?? ''); ?>

                    </td>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            
            <td style="text-align: center; border: 1px solid #000000; font-weight: bold;"><?php echo e($student['cgpa']); ?></td>
            <td style="text-align: center; border: 1px solid #000000; font-weight: bold; background-color: <?php echo e($remarkColor); ?>;"><?php echo e($student['class_designation']); ?></td>
            <td style="text-align: center; border: 1px solid #000000; font-weight: bold; background-color: <?php echo e($remarkColor); ?>;"><?php echo e($student['remarks']); ?></td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </tbody>
</table>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/reports/export/publication_sheet_excel.blade.php ENDPATH**/ ?>