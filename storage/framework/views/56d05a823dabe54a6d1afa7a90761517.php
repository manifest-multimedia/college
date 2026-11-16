<div> <!-- Root Element --> 

<div class="card shadow-sm" wire:poll.30s="refresh">
    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <div>
                <i class="fas fa-users-line me-2"></i>
                <strong>Active Participants</strong>
            </div>
            <span class="badge bg-light text-dark fs-6">
                <?php echo e($totalActiveCount); ?> <?php echo e(Str::plural('Student', $totalActiveCount)); ?>

            </span>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showExcessWarning): ?>
                <span class="badge bg-warning text-dark" data-bs-toggle="tooltip" 
                      title="More participants than expected!">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo e($totalActiveCount - $expectedParticipants); ?> over capacity
                </span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <button wire:click="refresh" class="btn btn-sm btn-light" data-bs-toggle="tooltip" 
                title="Refresh now">
            <i class="fas fa-sync-alt"></i>
        </button>
    </div>

    <div class="card-body">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($activeSessions) === 0): ?>
            <div class="text-center text-muted py-4">
                <i class="fas fa-user-clock fa-3x mb-3 opacity-50"></i>
                <p class="mb-0">No students are currently taking this exam.</p>
                <small>Waiting for participants to start...</small>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $activeSessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-md-6 col-lg-4 col-xl-3">
                        <div class="card h-100 border <?php echo e($session['has_device_conflict'] ? 'border-danger border-2' : 'border-secondary'); ?> 
                                    position-relative session-card" 
                             data-bs-toggle="popover" 
                             data-bs-trigger="hover focus"
                             data-bs-placement="top"
                             data-bs-html="true"
                             data-bs-content="
                                <strong>Student:</strong> <?php echo e($session['student_name']); ?><br>
                                <strong>ID:</strong> <?php echo e($session['student_id']); ?><br>
                                <strong>Email:</strong> <?php echo e($session['student_email']); ?><br>
                                <strong>Started:</strong> <?php echo e(\Carbon\Carbon::parse($session['started_at'])->format('h:i A')); ?><br>
                                <strong>Expected End:</strong> <?php echo e(\Carbon\Carbon::parse($session['expected_end_time'])->format('h:i A')); ?><br>
                                <strong>Time Remaining:</strong> <?php echo e($session['remaining_minutes']); ?> min<br>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($session['is_restored']): ?>
                                    <strong>Session Restored:</strong> <?php echo e(\Carbon\Carbon::parse($session['restored_at'])->format('h:i A')); ?><br>
                                    <strong>Total Extra Time:</strong> <?php echo e($session['total_extra_time']); ?> min<br>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($session['extra_time'] > 0 && $session['extra_time'] != $session['total_extra_time']): ?>
                                        <strong>Additional Time Given:</strong> <?php echo e($session['extra_time']); ?> min<br>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php elseif($session['extra_time'] > 0): ?>
                                    <strong>Extra Time Granted:</strong> <?php echo e($session['extra_time']); ?> min<br>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <strong>Active Duration:</strong> <?php echo e($session['session_duration_minutes']); ?> minutes<br>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($session['device_info']): ?>
                                    <strong>Device:</strong> <?php echo e($session['device_info']['device_type'] ?? 'Unknown'); ?><br>
                                    <strong>OS:</strong> <?php echo e($session['device_info']['os'] ?? 'Unknown'); ?><br>
                                    <strong>Browser:</strong> <?php echo e($session['device_info']['browser'] ?? 'Unknown'); ?><br>
                                    <strong>IP:</strong> <?php echo e($session['device_info']['ip_address'] ?? 'N/A'); ?><br>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($session['has_device_conflict']): ?>
                                    <span class='badge bg-danger mt-2'>
                                        <i class='fas fa-exclamation-triangle'></i> 
                                        <?php echo e($session['device_changes_count']); ?> device change(s)
                                    </span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                             ">
                            
                            <!-- Status Badges -->
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($session['has_device_conflict']): ?>
                                <span class="position-absolute top-0 end-0 m-2">
                                    <span class="badge bg-danger pulse-badge">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </span>
                                </span>
                            <?php elseif($session['is_restored']): ?>
                                <span class="position-absolute top-0 end-0 m-2">
                                    <span class="badge bg-warning text-dark" data-bs-toggle="tooltip" 
                                          title="Session was restored on <?php echo e(\Carbon\Carbon::parse($session['restored_at'])->format('M d, h:i A')); ?>">
                                        <i class="fas fa-rotate-right"></i>
                                    </span>
                                </span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <div class="card-body text-center p-3">
                                <!-- Video Feed & Device Icon Row -->
                                <div class="mb-2 d-flex align-items-center justify-content-center gap-2">
                                    <!-- Device Icon -->
                                    <?php
                                        $deviceType = $session['device_info']['device_type'] ?? 'desktop';
                                        $iconMap = [
                                            'mobile' => 'fa-mobile-screen',
                                            'tablet' => 'fa-tablet-screen-button',
                                            'desktop' => 'fa-desktop',
                                            'laptop' => 'fa-laptop',
                                        ];
                                        $icon = $iconMap[$deviceType] ?? 'fa-desktop';
                                    ?>
                                    <div>
                                        <i class="fas <?php echo e($icon); ?> fa-2x <?php echo e($session['has_device_conflict'] ? 'text-danger' : 'text-primary'); ?>"></i>
                                    </div>
                                    
                                    <!-- Video Feed Circle -->
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($session['has_video_feed']): ?>
                                        <div class="video-feed-circle" 
                                             data-bs-toggle="modal" 
                                             data-bs-target="#videoFeedModal<?php echo e($session['id']); ?>"
                                             data-bs-toggle="tooltip"
                                             title="Click to view live feed">
                                            <div class="video-placeholder">
                                                <i class="fas fa-video text-white"></i>
                                                <div class="recording-indicator"></div>
                                            </div>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>

                                <!-- Student Name -->
                                <h6 class="card-title mb-1 text-truncate" data-bs-toggle="tooltip" 
                                    title="<?php echo e($session['student_name']); ?>">
                                    <?php echo e($session['student_name']); ?>

                                </h6>
                                
                                <!-- Student ID -->
                                <p class="text-muted small mb-2"><?php echo e($session['student_id']); ?></p>

                                <!-- Time Remaining Badge (Primary Info) -->
                                <div class="mb-2">
                                    <?php
                                        $remainingMin = $session['remaining_minutes'];
                                        $badgeColor = $remainingMin > 10 ? 'success' : ($remainingMin > 5 ? 'warning' : 'danger');
                                    ?>
                                    <span class="badge bg-<?php echo e($badgeColor); ?> text-white">
                                        <i class="fas fa-hourglass-half"></i>
                                        <?php echo e($remainingMin); ?> min left
                                    </span>
                                </div>

                                <!-- Session Duration Badge -->
                                <span class="badge bg-info text-dark small">
                                    <i class="fas fa-clock"></i>
                                    <?php echo e($session['session_duration_minutes']); ?> min active
                                </span>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($session['is_restored']): ?>
                                    <span class="badge bg-warning text-dark small ms-1" data-bs-toggle="tooltip" 
                                          title="Session restored - <?php echo e($session['total_extra_time']); ?> min total extra time">
                                        <i class="fas fa-rotate-right"></i> Restored
                                    </span>
                                <?php elseif($session['extra_time'] > 0): ?>
                                    <span class="badge bg-warning text-dark small ms-1" data-bs-toggle="tooltip" 
                                          title="Extra time granted">
                                        <i class="fas fa-plus"></i> <?php echo e($session['extra_time']); ?>

                                    </span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <!-- Activity Indicator -->
                            <div class="card-footer bg-light border-top p-2 text-center small text-muted">
                                <i class="fas fa-circle text-success pulse-dot" style="font-size: 0.5rem;"></i>
                                Active
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <!-- Statistics Summary -->
            <div class="mt-4 pt-3 border-top">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="stat-box">
                            <i class="fas fa-users text-primary mb-2"></i>
                            <h5 class="mb-0"><?php echo e($totalActiveCount); ?></h5>
                            <small class="text-muted">Total Active</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-box">
                            <i class="fas fa-shield-halved text-success mb-2"></i>
                            <h5 class="mb-0"><?php echo e(collect($activeSessions)->where('has_device_conflict', false)->count()); ?></h5>
                            <small class="text-muted">Clean Sessions</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-box">
                            <i class="fas fa-triangle-exclamation text-danger mb-2"></i>
                            <h5 class="mb-0"><?php echo e(collect($activeSessions)->where('has_device_conflict', true)->count()); ?></h5>
                            <small class="text-muted">Flagged</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-box">
                            <i class="fas fa-stopwatch text-info mb-2"></i>
                            <h5 class="mb-0"><?php echo e(round(collect($activeSessions)->avg('session_duration_minutes') ?: 0)); ?></h5>
                            <small class="text-muted">Avg. Duration (min)</small>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <div class="card-footer bg-light text-muted small">
        <i class="fas fa-sync-alt"></i>
        Auto-refreshes every 30 seconds
        <span class="float-end">
            Last updated: <?php echo e(now()->format('h:i:s A')); ?>

        </span>
    </div>
</div>

<!-- Video Feed Modals -->
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $activeSessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($session['has_video_feed']): ?>
        <div class="modal fade" id="videoFeedModal<?php echo e($session['id']); ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-dark text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-video me-2"></i>
                            Live Feed - <?php echo e($session['student_name']); ?> (<?php echo e($session['student_id']); ?>)
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-0 bg-dark">
                        <div class="video-feed-container">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($session['video_feed_path']): ?>
                                <video class="video-feed-player" controls>
                                    <source src="<?php echo e(Storage::disk('exams')->url($session['video_feed_path'])); ?>" type="video/webm">
                                    Your browser does not support the video tag.
                                </video>
                            <?php else: ?>
                                <div class="text-center text-white p-5">
                                    <i class="fas fa-video-slash fa-3x mb-3"></i>
                                    <p>Video feed is being prepared...</p>
                                    <small>Live streaming will appear here once available</small>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                    <div class="modal-footer bg-dark text-white">
                        <div class="d-flex justify-content-between w-100 align-items-center">
                            <div>
                                <small class="text-muted">
                                    <i class="fas fa-circle text-danger pulse-dot me-1" style="font-size: 0.5rem;"></i>
                                    Recording in progress
                                </small>
                            </div>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<style>
    .session-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        cursor: pointer;
    }

    .session-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15) !important;
    }

    .pulse-badge {
        animation: pulse 2s infinite;
    }

    .pulse-dot {
        animation: pulse-dot 2s infinite;
    }

    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.7;
        }
    }

    @keyframes pulse-dot {
        0%, 100% {
            opacity: 1;
            transform: scale(1);
        }
        50% {
            opacity: 0.5;
            transform: scale(1.2);
        }
    }

    .stat-box {
        padding: 1rem;
    }

    .stat-box i {
        font-size: 1.5rem;
        display: block;
    }

    /* Video Feed Styles */
    .video-feed-circle {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        margin-left: 0.75rem;
        border: 3px solid #fff;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
    }

    .video-feed-circle:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.5);
    }

    .video-placeholder {
        color: white;
        font-size: 1.1rem;
    }

    .recording-indicator {
        position: absolute;
        top: -2px;
        right: -2px;
        width: 14px;
        height: 14px;
        background: #dc3545;
        border-radius: 50%;
        border: 2px solid white;
        animation: pulse-record 1.5s ease-in-out infinite;
    }

    @keyframes pulse-record {
        0%, 100% {
            transform: scale(1);
            opacity: 1;
        }
        50% {
            transform: scale(1.2);
            opacity: 0.7;
        }
    }

    .video-feed-container {
        position: relative;
        width: 100%;
        padding-bottom: 56.25%; /* 16:9 aspect ratio */
        background: #000;
    }

    .video-feed-player {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .modal-lg {
        max-width: 900px;
    }

    .video-feed-circle.disabled {
        background: #6c757d;
        cursor: not-allowed;
        opacity: 0.5;
    }

    .video-feed-circle.disabled:hover {
        transform: none;
        box-shadow: none;
    }
</style>



<?php $__env->startPush('scripts'); ?>
<script>
    // Initialize Bootstrap tooltips and popovers
    document.addEventListener('DOMContentLoaded', function() {
        initializeTooltipsPopovers();
    });

    // Re-initialize after Livewire updates
    document.addEventListener('livewire:update', function() {
        initializeTooltipsPopovers();
    });

    function initializeTooltipsPopovers() {
        // Dispose old instances
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            const instance = bootstrap.Tooltip.getInstance(tooltipTriggerEl);
            if (instance) instance.dispose();
            new bootstrap.Tooltip(tooltipTriggerEl);
        });

        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.forEach(function (popoverTriggerEl) {
            const instance = bootstrap.Popover.getInstance(popoverTriggerEl);
            if (instance) instance.dispose();
            new bootstrap.Popover(popoverTriggerEl);
        });
    }
</script>
<?php $__env->stopPush(); ?>
</div> <!-- End Root Element --><?php /**PATH /home/johnsonsebire/www/college/resources/views/livewire/active-exam-sessions.blade.php ENDPATH**/ ?>