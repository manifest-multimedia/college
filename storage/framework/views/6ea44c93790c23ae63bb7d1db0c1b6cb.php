<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($election->name); ?> - Results</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ddd;
        }
        .election-info {
            margin-bottom: 20px;
        }
        .election-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .election-info td {
            padding: 5px;
        }
        .summary {
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .position {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        .position-header {
            background-color: #f0f0f0;
            padding: 8px;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f5f5f5;
        }
        .votes-bar {
            height: 20px;
            background-color: #007bff;
            display: inline-block;
        }
        .winner {
            background-color: #28a745;
            color: white;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 12px;
        }
        .page-footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Election Results</h1>
        <h2><?php echo e($election->name); ?></h2>
    </div>
    
    <div class="election-info">
        <table>
            <tr>
                <td width="25%"><strong>Description:</strong></td>
                <td width="75%"><?php echo e($election->description); ?></td>
            </tr>
            <tr>
                <td><strong>Election Period:</strong></td>
                <td><?php echo e($election->start_time->format('M d, Y h:i A')); ?> to <?php echo e($election->end_time->format('M d, Y h:i A')); ?></td>
            </tr>
        </table>
    </div>
    
    <div class="summary">
        <table>
            <tr>
                <td width="50%"><strong>Total Votes Cast:</strong> <?php echo e($totalVotes); ?></td>
                <td width="50%"><strong>Total Voters:</strong> <?php echo e($totalVoters); ?></td>
            </tr>
            <tr>
                <td><strong>Voter Turnout:</strong> <?php echo e(round(($totalVoters / max(1, \App\Models\Student::count()) * 100), 1)); ?>%</td>
                <td><strong>Report Generated:</strong> <?php echo e($exportDate); ?></td>
            </tr>
        </table>
    </div>
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $positions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $position): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="position">
            <div class="position-header">
                <h3><?php echo e($position->name); ?></h3>
                <p><?php echo e($position->description); ?></p>
                <p><strong>Maximum Selections:</strong> <?php echo e($position->max_votes_allowed); ?></p>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th width="10%">Rank</th>
                        <th width="40%">Candidate</th>
                        <th width="15%">Votes</th>
                        <th width="35%">Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $totalPositionVotes = $position->candidates->sum('votes_count');
                        $rank = 1;
                    ?>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $position->candidates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $candidate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $percentage = $totalPositionVotes > 0 
                                ? round(($candidate->votes_count / $totalPositionVotes) * 100, 1) 
                                : 0;
                        ?>
                        <tr>
                            <td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($rank === 1): ?>
                                    <span class="winner">Winner</span>
                                <?php else: ?>
                                    <?php echo e($rank); ?>

                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td>
                                <?php echo e($candidate->name); ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$candidate->is_active): ?>
                                    (Inactive)
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td><?php echo e($candidate->votes_count); ?></td>
                            <td>
                                <?php echo e($percentage); ?>%
                            </td>
                        </tr>
                        <?php $rank++; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($totalPositionVotes === 0): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 15px;">
                                No votes recorded for this position.
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    
    <div class="page-footer">
        <p>This is an official record of the election results. Generated on <?php echo e($exportDate); ?>.</p>
    </div>
</body>
</html><?php /**PATH /home/johnsonsebire/www/college/resources/views/exports/election-results-pdf.blade.php ENDPATH**/ ?>