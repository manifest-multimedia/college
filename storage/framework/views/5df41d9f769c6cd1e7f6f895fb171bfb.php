
    <div class="container-fluid px-4">
        <h1 class="mt-4">Student Elections</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
            <li class="breadcrumb-item active">Elections</li>
        </ol>
        
        <div class="row">
            <div class="col-xl-12">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-vote-yea me-1"></i>
                            Available Elections
                        </div>
                        <div>
                            <button wire:click="$refresh" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-sync-alt me-1"></i> Refresh
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($activeElections) > 0): ?>
                            <div class="row">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $activeElections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $election): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="col-md-6 col-xl-4 mb-4">
                                        <div class="card h-100 border-0 shadow-sm">
                                            <div class="card-header bg-primary text-white">
                                                <h5 class="card-title mb-0"><?php echo e($election->name); ?></h5>
                                            </div>
                                            <div class="card-body d-flex flex-column">
                                                <p class="card-text"><?php echo e($election->description); ?></p>
                                                
                                                <div class="mb-3">
                                                    <small class="text-muted">
                                                        <strong>Start:</strong> <?php echo e($election->start_time->format('M d, Y h:i A')); ?>

                                                    </small>
                                                    <br>
                                                    <small class="text-muted">
                                                        <strong>End:</strong> <?php echo e($election->end_time->format('M d, Y h:i A')); ?>

                                                    </small>
                                                </div>
                                                
                                                <?php
                                                    $now = now();
                                                    $totalDuration = $election->end_time->diffInSeconds($election->start_time);
                                                    $elapsedDuration = $now->diffInSeconds($election->start_time);
                                                    $percentage = min(100, max(0, ($elapsedDuration / $totalDuration) * 100));
                                                ?>
                                                
                                                <div class="mb-3">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <small>Election Progress</small>
                                                        <small><?php echo e(round($percentage)); ?>%</small>
                                                    </div>
                                                    <div class="progress" style="height: 8px;">
                                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo e($percentage); ?>%"></div>
                                                    </div>
                                                </div>
                                                
                                                <div class="mt-auto">
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($userHasVoted($election->id)): ?>
                                                        <div class="alert alert-success mb-3 py-2">
                                                            <i class="fas fa-check-circle me-2"></i>
                                                            You have already voted in this election
                                                        </div>
                                                        
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (\Illuminate\Support\Facades\Blade::check('hasrole', 'System|Super Admin|Administrator')): ?>
                                                            <a href="<?php echo e(route('election.results', $election)); ?>" class="btn btn-info btn-sm d-block">
                                                                <i class="fas fa-chart-bar me-1"></i>
                                                                View Results
                                                            </a>
                                                        <?php else: ?>
                                                            <a href="<?php echo e(route('public.elections.show', $election)); ?>" class="btn btn-outline-info btn-sm d-block">
                                                                <i class="fas fa-info-circle me-1"></i>
                                                                View Details
                                                            </a>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    <?php else: ?>
                                                        <a href="<?php echo e(route('election.verify', $election)); ?>" class="btn btn-primary d-block">
                                                            <i class="fas fa-vote-yea me-2"></i>
                                                            Cast Your Vote
                                                        </a>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="card-footer bg-light">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <small>
                                                        <i class="fas fa-users me-1"></i> 
                                                        <?php echo e($election->positions->count()); ?> positions
                                                    </small>
                                                    <small>
                                                        <i class="fas fa-clock me-1"></i>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($now->greaterThan($election->end_time)): ?>
                                                            <span class="text-danger">Ended</span>
                                                        <?php elseif($now->lessThan($election->start_time)): ?>
                                                            <span class="text-primary">Starts <?php echo e($now->diffForHumans($election->start_time)); ?></span>
                                                        <?php else: ?>
                                                            <span class="text-success">Ends <?php echo e($now->diffForHumans($election->end_time)); ?></span>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <div class="d-flex">
                                    <div class="me-3">
                                        <i class="fas fa-info-circle fa-2x"></i>
                                    </div>
                                    <div>
                                        <h5 class="alert-heading">No Active Elections</h5>
                                        <p class="mb-0">There are currently no active elections available for voting. Please check back later.</p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($pastElections) > 0): ?>
            <div class="row mt-4">
                <div class="col-xl-12">
                    <div class="card mb-4">
                        <div class="card-header ">
                            <div class="card-title" style="font-weight:normal; font-size:12px" >
                            <i class="fas fa-history me-1"></i>
                            Past Elections
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Election</th>
                                            <th>Period</th>
                                            <th>Your Participation</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $pastElections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $election): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-bold"><?php echo e($election->name); ?></div>
                                                    <div class="small text-muted"><?php echo e(Str::limit($election->description, 100)); ?></div>
                                                </td>
                                                <td>
                                                    <?php echo e($election->start_time->format('M d, Y')); ?> - <?php echo e($election->end_time->format('M d, Y')); ?>

                                                </td>
                                                <td>
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($userHasVoted($election->id)): ?>
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check-circle me-1"></i> Voted
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">
                                                            <i class="fas fa-times-circle me-1"></i> Did not vote
                                                        </span>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (\Illuminate\Support\Facades\Blade::check('hasrole', 'System|Super Admin|Administrator')): ?>
                                                        <a href="<?php echo e(route('election.results', $election)); ?>" class="btn btn-outline-info btn-sm">
                                                            <i class="fas fa-chart-bar me-1"></i>
                                                            View Results
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="<?php echo e(route('public.elections.show', $election)); ?>" class="btn btn-outline-primary btn-sm">
                                                            <i class="fas fa-info-circle me-1"></i>
                                                            View Details
                                                        </a>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (\Illuminate\Support\Facades\Blade::check('hasrole', 'System|Super Admin|Administrator')): ?>
            <div class="row mt-2 mb-4">
                <div class="col-12">
                    <div class="card bg-light border-0">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-cog fa-2x text-primary"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1">Election Administration</h5>
                                    <p class="mb-2">As an administrator, you can manage all aspects of the election system.</p>
                                    <a href="<?php echo e(route('elections')); ?>" class="btn btn-primary">
                                        <i class="fas fa-users-cog me-1"></i>
                                        Election Management
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/livewire/active-elections.blade.php ENDPATH**/ ?>