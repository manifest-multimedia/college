<!DOCTYPE html>
<html>
<head>
    <title>Exam Results - <?php echo e($exam->display_title); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }
        .header p {
            margin: 3px 0;
        }
        .stats {
            margin-bottom: 15px;
            width: 100%;
        }
        .stats td {
            padding: 5px;
            text-align: center;
            background-color: #f3f3f3;
            border: 1px solid #ddd;
        }
        .stats .label {
            font-weight: bold;
            font-size: 10px;
            color: #555;
        }
        .stats .value {
            font-size: 14px;
            font-weight: bold;
        }
        table.results {
            width: 100%;
            border-collapse: collapse;
        }
        table.results th {
            background-color: #444;
            color: white;
            text-align: left;
            padding: 5px;
            font-size: 11px;
        }
        table.results td {
            border: 1px solid #ddd;
            padding: 5px;
            font-size: 10px;
        }
        table.results tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #777;
        }
        .status-excellent { background-color: #d1e7dd; }
        .status-verygood { background-color: #cfe2ff; }
        .status-good { background-color: #d1ecf1; }
        .status-pass { background-color: #fff3cd; }
        .status-failed { background-color: #f8d7da; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Exam Results</h1>
        <p><strong>Examination:</strong> <?php echo e($exam->display_title); ?></p>
        <p><strong>Course:</strong> <?php echo e($exam->course->name ?? 'Unknown Course'); ?></p>
        <p><strong>Date Generated:</strong> <?php echo e(now()->format('F d, Y h:i A')); ?></p>
    </div>
    
    <table class="stats">
        <tr>
            <td width="20%">
                <div class="label">TOTAL STUDENTS</div>
                <div class="value"><?php echo e($stats['totalStudents']); ?></div>
            </td>
            <td width="20%">
                <div class="label">AVERAGE SCORE</div>
                <div class="value"><?php echo e($stats['averageScore']); ?>%</div>
            </td>
            <td width="20%">
                <div class="label">PASS RATE</div>
                <div class="value"><?php echo e($stats['passRate']); ?>%</div>
            </td>
            <td width="20%">
                <div class="label">HIGHEST SCORE</div>
                <div class="value"><?php echo e($stats['highestScore']); ?>%</div>
            </td>
            <td width="20%">
                <div class="label">LOWEST SCORE</div>
                <div class="value"><?php echo e($stats['lowestScore']); ?>%</div>
            </td>
        </tr>
    </table>
    
    <table class="results">
        <thead>
            <tr>
                <th>#</th>
                <th>Student ID</th>
                <th>Name</th>
                <th>Program</th>
                <th>Date</th>
                <th>Score</th>
                <th>Answered</th>
                <th>Percentage</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $results; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $result): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $statusClass = 'failed';
                    $statusText = 'Failed';
                    
                    if ($result['score_percentage'] >= 80) {
                        $statusClass = 'excellent';
                        $statusText = 'Excellent';
                    } elseif ($result['score_percentage'] >= 70) {
                        $statusClass = 'verygood';
                        $statusText = 'Very Good';
                    } elseif ($result['score_percentage'] >= 60) {
                        $statusClass = 'good';
                        $statusText = 'Good';
                    } elseif ($result['score_percentage'] >= 50) {
                        $statusClass = 'pass';
                        $statusText = 'Pass';
                    }
                ?>
                <tr>
                    <td><?php echo e($index + 1); ?></td>
                    <td><?php echo e($result['student_id']); ?></td>
                    <td><?php echo e($result['name']); ?></td>
                    <td><?php echo e($result['class']); ?></td>
                    <td><?php echo e($result['completed_at']); ?></td>
                    <td><?php echo e($result['score']); ?></td>
                    <td><?php echo e($result['answered']); ?></td>
                    <td><?php echo e($result['score_percentage']); ?>%</td>
                    <td class="status-<?php echo e($statusClass); ?>"><?php echo e($statusText); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
    </table>
    
    <div class="footer">
        Generated from the Examination System | <?php echo e(now()->format('Y-m-d H:i:s')); ?>

    </div>
</body>
</html>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/exports/exam-results-pdf.blade.php ENDPATH**/ ?>