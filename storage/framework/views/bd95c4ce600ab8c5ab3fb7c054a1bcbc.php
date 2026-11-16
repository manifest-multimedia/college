<div 
    x-data="{
        refreshing: false,
        openAccordions: {},
        
        initAccordions() {
            // Try to load saved state from localStorage
            const savedState = localStorage.getItem('electionAccordionState_<?php echo e($election->id); ?>');
            if (savedState) {
                this.openAccordions = JSON.parse(savedState);
            }
            
            // Set up event listeners after a short delay to ensure DOM is ready
            this.$nextTick(() => {
                document.querySelectorAll('.accordion-collapse').forEach(item => {
                    item.addEventListener('show.bs.collapse', (e) => {
                        const positionId = e.target.id.replace('collapse', '');
                        this.openAccordions[positionId] = true;
                        this.saveAccordionState();
                    });
                    
                    item.addEventListener('hide.bs.collapse', (e) => {
                        const positionId = e.target.id.replace('collapse', '');
                        this.openAccordions[positionId] = false;
                        this.saveAccordionState();
                    });
                });
                
                // Apply saved state to DOM
                this.applyAccordionState();
            });
        },
        
        applyAccordionState() {
            Object.keys(this.openAccordions).forEach(positionId => {
                if (this.openAccordions[positionId]) {
                    const accordionEl = document.getElementById(`collapse${positionId}`);
                    if (accordionEl) {
                        const bsCollapse = new bootstrap.Collapse(accordionEl, {toggle: false});
                        bsCollapse.show();
                    }
                }
            });
        },
        
        saveAccordionState() {
            localStorage.setItem('electionAccordionState_<?php echo e($election->id); ?>', JSON.stringify(this.openAccordions));
        },
        
        isAccordionOpen(positionId) {
            return this.openAccordions[positionId] === true;
        },
        
        showVoteDetails: {}
    }"
    x-init="
        initAccordions();
        setInterval(() => { 
            $wire.$refresh(); 
            refreshing = true; 
            setTimeout(() => { refreshing = false; }, 1000) 
        }, <?php echo e($refreshInterval * 1000); ?>);
    "
>
    <div class="container-fluid px-4">
        <h1 class="mt-4">Election Results</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="<?php echo e(route('dashboard')); ?>">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?php echo e(route('elections')); ?>">Elections</a></li>
            <li class="breadcrumb-item active">Results</li>
        </ol>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-info-circle me-1"></i>
                            Election Details
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="me-2">
                                <div class="badge bg-info text-white" x-show="refreshing">
                                    <i class="fas fa-sync-alt fa-spin me-1"></i> <span class="opacity-50"> Refreshing... </span>
                                </div>
                            </div>
                            <a href="<?php echo e(route('elections')); ?>" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Back to Elections
                            </a>
                            <a href="<?php echo e(route('admin.election.voter-integrity', $election)); ?>" class="btn btn-outline-warning btn-sm ms-2">
                                <i class="fas fa-user-shield"></i> Voter Integrity
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo e($election->name); ?></h5>
                        <p class="card-text"><?php echo e($election->description); ?></p>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Start:</strong> <?php echo e($election->start_time->format('M d, Y h:i A')); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>End:</strong> <?php echo e($election->end_time->format('M d, Y h:i A')); ?></p>
                            </div>
                        </div>
                        <p class="mb-0">
                            <strong>Status:</strong> 
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($election->isActive()): ?>
                                <span class="badge bg-success">Active & In Progress</span>
                            <?php elseif($election->hasEnded()): ?>
                                <span class="badge bg-secondary">Completed</span>
                            <?php elseif(!$election->is_active): ?>
                                <span class="badge bg-warning text-dark">Inactive</span>
                            <?php else: ?>
                                <span class="badge bg-info">Upcoming</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card bg-primary text-white shadow">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-white-50">Total Voters</div>
                                        <div class="fs-3 fw-bold"><?php echo e($totalVoters); ?></div>
                                    </div>
                                    <i class="fas fa-users fa-2x text-white opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card bg-success text-white shadow">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-white-50">Voter Turnout</div>
                                        <div class="fs-3 fw-bold"><?php echo e($voterTurnout); ?>%</div>
                                    </div>
                                    <i class="fas fa-percentage fa-2x text-white opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card bg-info text-white shadow">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-white-50">Total Votes</div>
                                        <div class="fs-3 fw-bold"><?php echo e($totalVotes); ?></div>
                                    </div>
                                    <i class="fas fa-vote-yea fa-2x text-white opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card bg-dark text-white shadow">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="text-white-50">Positions</div>
                                        <div class="fs-3 fw-bold"><?php echo e(count($positions)); ?></div>
                                    </div>
                                    <i class="fas fa-list-ul fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-chart-bar me-1"></i>
                    Election Results
                </div>
                <div>
                    <div class="dropdown d-inline-block me-2">
                        <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-sync-alt me-1"></i> Auto-refresh: <?php echo e($refreshInterval); ?>s
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" wire:click="setRefreshInterval(5)">Every 5 seconds</a></li>
                            <li><a class="dropdown-item" href="#" wire:click="setRefreshInterval(10)">Every 10 seconds</a></li>
                            <li><a class="dropdown-item" href="#" wire:click="setRefreshInterval(30)">Every 30 seconds</a></li>
                            <li><a class="dropdown-item" href="#" wire:click="setRefreshInterval(60)">Every minute</a></li>
                        </ul>
                    </div>
                    
                    <button wire:click="showExportOptions" class="btn btn-sm btn-success">
                        <i class="fas fa-download me-1"></i> Export Results
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($positions) > 0): ?>
                    <div class="accordion" id="accordionResults">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $positions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $position): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button 
                                        class="accordion-button" 
                                        :class="{'collapsed': !isAccordionOpen('<?php echo e($position->id); ?>')}"
                                        type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#collapse<?php echo e($position->id); ?>"
                                        aria-expanded="false"
                                        aria-controls="collapse<?php echo e($position->id); ?>"
                                    >
                                        <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                            <span><?php echo e($position->name); ?></span>
                                            <span class="badge bg-primary"><?php echo e($position->getTotalVotes()); ?> votes</span>
                                        </div>
                                    </button>
                                </h2>
                                <div 
                                    id="collapse<?php echo e($position->id); ?>" 
                                    class="accordion-collapse collapse" 
                                    :class="{'show': isAccordionOpen('<?php echo e($position->id); ?>')}" 
                                    data-bs-parent="#accordionResults"
                                >
                                    <div class="accordion-body">
                                        <div class="table-responsive">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($position->hasSingleCandidate()): ?>
                                                <?php
                                                    $yesNoResults = $position->getYesNoVotes();
                                                ?>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($yesNoResults): ?>
                                                    <!-- YES/NO voting result display for single candidate -->
                                                    <div class="card mb-4">
                                                        <div class="card-header bg-light">
                                                            <div class="d-flex align-items-center">
                                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($yesNoResults['candidate']->image_path): ?>
                                                                    <img src="<?php echo e(Storage::url($yesNoResults['candidate']->image_path)); ?>" class="me-3 rounded-circle" style="width: 50px; height: 50px; object-fit: cover;" alt="<?php echo e($yesNoResults['candidate']->name); ?>">
                                                                <?php else: ?>
                                                                    <div class="bg-secondary rounded-circle me-3 d-flex justify-content-center align-items-center" style="width: 50px; height: 50px;">
                                                                        <i class="fas fa-user text-white"></i>
                                                                    </div>
                                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                                <div>
                                                                    <h5 class="mb-0"><?php echo e($yesNoResults['candidate']->name); ?></h5>
                                                                    <p class="small text-muted mb-0">Single Candidate Vote (Approval Required)</p>
                                                                </div>
                                                                
                                                            </div>
                                                            <div class="d-flex align-items-center justify-content-end">
                                                               
                                                                
                                                                <!-- Displaying the result status -->
                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($yesNoResults['has_won']): ?>
                                                                <div class="">
                                                                    <span class="badge bg-success ms-auto">APPROVED</span>
                                                                </div>
                                                                <?php else: ?>
                                                                <div class="ms-auto">
                                                                    <span class="badge bg-danger ms-auto">REJECTED</span>
                                                                </div>
                                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                            </div>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row mb-3">
                                                                <div class="col-md-6">
                                                                    <h6>YES Votes</h6>
                                                                    <div class="d-flex align-items-center justify-content-between">
                                                                        <div class="progress flex-grow-1 me-2" style="height: 24px;">
                                                                            <div 
                                                                                class="progress-bar bg-success" 
                                                                                role="progressbar" 
                                                                                style="width: <?php echo e($yesNoResults['yes_percent']); ?>%"
                                                                                aria-valuenow="<?php echo e($yesNoResults['yes_percent']); ?>" 
                                                                                aria-valuemin="0" 
                                                                                aria-valuemax="100"
                                                                            >
                                                                                <?php echo e($yesNoResults['yes_votes']); ?> (<?php echo e($yesNoResults['yes_percent']); ?>%)
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <h6>NO Votes</h6>
                                                                    <div class="d-flex align-items-center justify-content-between">
                                                                        <div class="progress flex-grow-1 me-2" style="height: 24px;">
                                                                            <div 
                                                                                class="progress-bar bg-danger" 
                                                                                role="progressbar" 
                                                                                style="width: <?php echo e($yesNoResults['no_percent']); ?>%"
                                                                                aria-valuenow="<?php echo e($yesNoResults['no_percent']); ?>" 
                                                                                aria-valuemin="0" 
                                                                                aria-valuemax="100"
                                                                            >
                                                                                <?php echo e($yesNoResults['no_votes']); ?> (<?php echo e($yesNoResults['no_percent']); ?>%)
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-12 text-center">
                                                                    <div class="alert <?php echo e($yesNoResults['has_won'] ? 'alert-success' : 'alert-danger'); ?> mb-0">
                                                                        <i class="fas <?php echo e($yesNoResults['has_won'] ? 'fa-check-circle' : 'fa-times-circle'); ?> me-2"></i>
                                                                        <strong>Result:</strong> 
                                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($yesNoResults['has_won']): ?>
                                                                            Candidate has been approved with <?php echo e($yesNoResults['yes_percent']); ?>% YES votes.
                                                                        <?php else: ?>
                                                                            Candidate has been rejected with <?php echo e($yesNoResults['no_percent']); ?>% NO votes.
                                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="card-footer bg-light">
                                                            <div class="d-flex justify-content-between text-muted small">
                                                                <span>Total votes: <?php echo e($yesNoResults['total_votes']); ?></span>
                                                                <span>Turnout: <?php echo e(round(($yesNoResults['total_votes'] / max(1, $totalVoters)) * 100, 1)); ?>%</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="alert alert-info">
                                                        <i class="fas fa-info-circle me-2"></i>
                                                        No voting data available for this single-candidate position.
                                                    </div>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <?php else: ?>
                                                <!-- Standard multi-candidate voting results -->
                                                <table class="table table-bordered">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th style="width: 80px;">#</th>
                                                            <th>Candidate</th>
                                                            <th class="text-center" style="width: 100px;">Votes</th>
                                                            <th class="text-center" style="width: 200px;">Percentage</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                            $totalPositionVotes = $position->candidates->sum('votes_count');
                                                            $rank = 1;
                                                        ?>
                                                        
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $position->candidates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $candidate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <tr>
                                                                <td class="text-center">
                                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($rank === 1): ?>
                                                                        <span class="badge bg-warning text-dark">
                                                                            <i class="fas fa-trophy"></i>
                                                                        </span>
                                                                    <?php else: ?>
                                                                        <?php echo e($rank); ?>

                                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                                </td>
                                                                <td>
                                                                    <div class="d-flex align-items-center">
                                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($candidate->image_path): ?>
                                                                            <img src="<?php echo e(Storage::url($candidate->image_path)); ?>" class="me-2 rounded-circle" style="width: 30px; height: 30px; object-fit: cover;" alt="<?php echo e($candidate->name); ?>">
                                                                        <?php else: ?>
                                                                            <div class="bg-secondary rounded-circle me-2 d-flex justify-content-center align-items-center" style="width: 30px; height: 30px;">
                                                                                <i class="fas fa-user text-white small"></i>
                                                                            </div>
                                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                                        <?php echo e($candidate->name); ?>

                                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$candidate->is_active): ?>
                                                                            <span class="badge bg-danger ms-2">Inactive</span>
                                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                                    </div>
                                                                </td>
                                                                <td class="text-center"><?php echo e($candidate->votes_count); ?></td>
                                                                <td>
                                                                    <?php
                                                                        $percentage = $totalPositionVotes > 0 
                                                                            ? round(($candidate->votes_count / $totalPositionVotes) * 100, 1) 
                                                                            : 0;
                                                                    ?>
                                                                    <div class="d-flex align-items-center justify-content-between">
                                                                        <div class="progress flex-grow-1 me-2" style="height: 10px;">
                                                                            <div 
                                                                                class="progress-bar <?php echo e($rank === 1 ? 'bg-success' : 'bg-primary'); ?>" 
                                                                                role="progressbar" 
                                                                                style="width: <?php echo e($percentage); ?>%"
                                                                                aria-valuenow="<?php echo e($percentage); ?>" 
                                                                                aria-valuemin="0" 
                                                                                aria-valuemax="100">
                                                                            </div>
                                                                        </div>
                                                                        <span class="text-end" style="min-width: 40px;"><?php echo e($percentage); ?>%</span>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <?php $rank++; ?>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                        
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($totalPositionVotes === 0): ?>
                                                            <tr>
                                                                <td colspan="4" class="text-center py-3 text-muted">
                                                                    <i class="fas fa-info-circle me-1"></i>
                                                                    No votes recorded for this position yet.
                                                                </td>
                                                            </tr>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </tbody>
                                                </table>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        No positions have been defined for this election.
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Export Options Modal -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showingExportOptions): ?>
        <div class="modal fade show" tabindex="-1" style="display: block;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Export Results</h5>
                        <button wire:click="hideExportOptions" type="button" class="btn-close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Choose your preferred export format:</p>
                        
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" wire:model="downloadType" id="excel" value="excel">
                            <label class="form-check-label" for="excel">
                                <i class="fas fa-file-excel text-success me-1"></i> Excel (.xlsx)
                            </label>
                        </div>
                        
                        <div class="form-check">
                            <input class="form-check-input" type="radio" wire:model="downloadType" id="pdf" value="pdf">
                            <label class="form-check-label" for="pdf">
                                <i class="fas fa-file-pdf text-danger me-1"></i> PDF
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" wire:click="hideExportOptions" class="btn btn-secondary">Cancel</button>
                        <button type="button" wire:click="exportResults" class="btn btn-primary">
                            <i class="fas fa-download me-1"></i> Download
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show"></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH /home/johnsonsebire/www/college/resources/views/livewire/election-results-dashboard.blade.php ENDPATH**/ ?>