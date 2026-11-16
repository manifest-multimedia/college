<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo e($report->getName()); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
        }
        .header-title {
            font-size: 14px;
            font-weight: bold;
            background-color: #fdf5e6;
            color: #000080;
        }
        .header-subtitle {
            font-size: 12px;
            font-weight: bold;
            background-color: #fdf5e6;
        }
        .header-programme {
            font-size: 12px;
            font-weight: bold;
            background-color: #f5deb3;
        }
        .bg-year { background-color: #f5f5dc; font-weight: bold; }
        .bg-sem { background-color: #e8f4f8; font-weight: bold; }
        .bg-col { background-color: #e0e0e0; font-weight: bold; }
        .bg-summary { background-color: #ffd700; font-weight: bold; }
        
        .empty-cell { border: none; }
        
        .remark-repeated { background-color: #ffe4e1; }
        .remark-dismissed { background-color: #dda0dd; }
        .remark-pass { background-color: #e8f5e9; }
    </style>
</head>
<body>
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
                <th colspan="<?php echo e($colspan); ?>" class="header-title">
                    <?php echo e(strtoupper(config('branding.institution.name', config('app.name', 'METHODIST HEALTH TRAINING INSTITUTE - AFOSU')))); ?>

                </th>
            </tr>
            <tr>
                <th colspan="<?php echo e($colspan); ?>" class="header-subtitle">
                    CUMMULATIVE GRADE POINT PUBLICATION SHEET
                </th>
            </tr>
            <tr>
                <th colspan="<?php echo e($colspan); ?>" class="header-programme">
                    PROGRAMME: <?php echo e(strtoupper($report->reportProgram ?? 'N/A')); ?>

                </th>
            </tr>
            <tr>
                <th class="empty-cell"></th>
                <th class="empty-cell"></th>
                <th class="empty-cell"></th>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $report->reportSemesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $yearLabel => $semesters): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <th colspan="<?php echo e(count($semesters)); ?>" class="bg-year"><?php echo e(strtoupper($yearLabel)); ?></th>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <th class="empty-cell"></th>
                <th class="empty-cell"></th>
                <th class="empty-cell"></th>
            </tr>
            <tr>
                <th class="empty-cell"></th>
                <th class="empty-cell"></th>
                <th class="empty-cell"></th>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $report->reportSemesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $yearLabel => $semesters): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $semesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $semesterKey => $semesterName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <th class="bg-sem"><?php echo e(strtoupper($semesterName)); ?></th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <th class="empty-cell"></th>
                <th class="empty-cell"></th>
                <th class="empty-cell"></th>
            </tr>
            <tr>
                <th class="bg-col">SERIAL NO</th>
                <th class="bg-col">INDEX NUMBER</th>
                <th class="bg-col">NAME</th>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $report->reportSemesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $yearLabel => $semesters): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $semesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $semesterKey => $semesterName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <th class="bg-col">GPA</th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <th class="bg-summary">CGPA</th>
                <th class="bg-summary">CLASS DESIGNATION</th>
                <th class="bg-summary">REMARKS</th>
            </tr>
        </thead>
        <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $remarkClass = '';
                    if ($student['remarks'] === 'REPEATED') $remarkClass = 'remark-repeated';
                    if ($student['remarks'] === 'DISMISSED') $remarkClass = 'remark-dismissed';
                    if ($student['remarks'] === 'PASS') $remarkClass = 'remark-pass';
                ?>
                <tr>
                    <td><?php echo e($index + 1); ?></td>
                    <td style="text-align: left;"><?php echo e($student['index_number']); ?></td>
                    <td style="text-align: left;"><?php echo e($student['name']); ?></td>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $report->reportSemesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $yearLabel => $semesters): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $semesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $semesterKey => $semesterName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <td style="font-weight: bold;">
                                <?php echo e($student[$semesterKey] ?? ''); ?>

                            </td>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    
                    <td style="font-weight: bold;"><?php echo e($student['cgpa']); ?></td>
                    <td class="<?php echo e($remarkClass); ?>" style="font-weight: bold;"><?php echo e($student['class_designation']); ?></td>
                    <td class="<?php echo e($remarkClass); ?>" style="font-weight: bold;"><?php echo e($student['remarks']); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
    </table>
</body>
</html>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/reports/export/publication_sheet_pdf.blade.php ENDPATH**/ ?>