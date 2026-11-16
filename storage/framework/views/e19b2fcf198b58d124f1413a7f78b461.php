<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Students Export</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin-bottom: 5px;
        }
        .subheader {
            text-align: center;
            margin-bottom: 20px;
            font-style: italic;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .badge {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 10px;
        }
        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }
        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        .badge-secondary {
            background-color: #e2e3e5;
            color: #383d41;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Student Information Report</h1>
        <p>Export Date: <?php echo e(date('F j, Y, g:i a')); ?></p>
    </div>
    
    <div class="subheader">
        <p>Total Records: <?php echo e(count($students)); ?></p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Student Name</th>
                <th>Gender</th>
                <th>Email</th>
                <th>Mobile Number</th>
                <th>Program</th>
                <th>Cohort</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($student->student_id); ?></td>
                    <td><?php echo e($student->last_name); ?> <?php echo e($student->first_name); ?> <?php echo e($student->other_name); ?></td>
                    <td><?php echo e(ucfirst(strtolower($student->gender ?? 'N/A'))); ?></td>
                    <td><?php echo e($student->email); ?></td>
                    <td><?php echo e($student->mobile_number ?? 'Not provided'); ?></td>
                    <td><?php echo e($student->collegeClass->name ?? 'N/A'); ?></td>
                    <td><?php echo e($student->cohort->name ?? 'N/A'); ?></td>
                    <td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($student->status == 'Active'): ?>
                            <span class="badge badge-success"><?php echo e($student->status ?? 'Active'); ?></span>
                        <?php elseif($student->status == 'Inactive'): ?>
                            <span class="badge badge-danger"><?php echo e($student->status); ?></span>
                        <?php elseif($student->status == 'Pending'): ?>
                            <span class="badge badge-warning"><?php echo e($student->status); ?></span>
                        <?php else: ?>
                            <span class="badge badge-secondary"><?php echo e($student->status ?? 'Unknown'); ?></span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="8" style="text-align: center;">No students found.</td>
                </tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
    </table>
    
    <div class="footer">
        <p>This is an automatically generated report. Please contact the administration for any questions.</p>
    </div>
</body>
</html>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/exports/students-pdf.blade.php ENDPATH**/ ?>