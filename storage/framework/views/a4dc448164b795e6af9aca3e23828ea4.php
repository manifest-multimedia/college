<?php if (isset($component)) { $__componentOriginal78458ab11901cb5e12c9728346766652 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78458ab11901cb5e12c9728346766652 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.default','data' => ['title' => 'Election Voter Integrity']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.default'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Election Voter Integrity']); ?>
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-1">Voter Integrity Investigation</h3>
                <p class="text-muted mb-0">Election: <?php echo e($election->name); ?></p>
            </div>
            <a href="<?php echo e(route('election.results', $election)); ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Results
            </a>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
            <div class="alert alert-success"><?php echo e(session('success')); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="<?php echo e(route('admin.election.voter-integrity', $election)); ?>" class="row g-3 align-items-end">
                    <div class="col-md-8">
                        <label for="student_id" class="form-label">Student ID</label>
                        <input id="student_id" name="student_id" type="text" class="form-control" value="<?php echo e($studentId); ?>" placeholder="Enter student ID to investigate">
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i> Investigate
                        </button>
                        <a href="<?php echo e(route('admin.election.voter-integrity', $election)); ?>" class="btn btn-light">Clear</a>
                    </div>
                </form>
            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($studentId !== ''): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Student Profile</h5>
                </div>
                <div class="card-body">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($student): ?>
                        <div class="row">
                            <div class="col-md-4"><strong>Student ID:</strong> <?php echo e($student->student_id); ?></div>
                            <div class="col-md-4"><strong>Name:</strong> <?php echo e($student->name); ?></div>
                            <div class="col-md-4"><strong>Email:</strong> <?php echo e($student->email ?: 'N/A'); ?></div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning mb-0">No student record found for this ID in the students table.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="text-muted">Votes Found</div>
                            <div class="fs-3 fw-bold"><?php echo e($votes->count()); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="text-muted">Voting Sessions</div>
                            <div class="fs-3 fw-bold"><?php echo e($sessions->count()); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="text-muted">Audit Events</div>
                            <div class="fs-3 fw-bold"><?php echo e($auditLogs->count()); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Admin Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <form method="POST" action="<?php echo e(route('admin.election.voter-integrity.nullify', $election)); ?>">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="student_id" value="<?php echo e($studentId); ?>">
                                <div class="mb-2">
                                    <label class="form-label">Reason (optional)</label>
                                    <textarea class="form-control" name="reason" rows="3" placeholder="Reason for nullifying votes"></textarea>
                                </div>
                                <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Nullify this student\'s votes for this election?')">
                                    <i class="fas fa-trash me-1"></i> Nullify Votes (Keep voter blocked)
                                </button>
                            </form>
                        </div>

                        <div class="col-lg-6">
                            <form method="POST" action="<?php echo e(route('admin.election.voter-integrity.allow-revote', $election)); ?>">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="student_id" value="<?php echo e($studentId); ?>">
                                <div class="mb-2">
                                    <label class="form-label">Reason (optional)</label>
                                    <textarea class="form-control" name="reason" rows="3" placeholder="Reason for re-vote authorization"></textarea>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" value="1" id="nullify_existing_votes" name="nullify_existing_votes" checked>
                                    <label class="form-check-label" for="nullify_existing_votes">
                                        Also nullify existing votes before allowing re-vote
                                    </label>
                                </div>
                                <button type="submit" class="btn btn-success" onclick="return confirm('Allow re-vote for this student in this election?')">
                                    <i class="fas fa-rotate-right me-1"></i> Allow Re-vote
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Vote Trail (Where and When)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Position</th>
                                    <th>Candidate</th>
                                    <th>Vote Type</th>
                                    <th>IP</th>
                                    <th>User Agent</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $votes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($vote->created_at?->format('Y-m-d H:i:s')); ?></td>
                                        <td><?php echo e($vote->position?->name ?: 'N/A'); ?></td>
                                        <td><?php echo e($vote->candidate?->name ?: 'N/A'); ?></td>
                                        <td><?php echo e($vote->vote_type); ?></td>
                                        <td><?php echo e($vote->ip_address ?: 'N/A'); ?></td>
                                        <td><?php echo e(\Illuminate\Support\Str::limit($vote->user_agent ?: 'N/A', 70)); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No votes recorded for this student in this election.</td>
                                    </tr>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Session Trail</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Session ID</th>
                                    <th>Started</th>
                                    <th>Expires</th>
                                    <th>Completed</th>
                                    <th>Submitted</th>
                                    <th>IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($session->session_id); ?></td>
                                        <td><?php echo e($session->started_at?->format('Y-m-d H:i:s')); ?></td>
                                        <td><?php echo e($session->expires_at?->format('Y-m-d H:i:s')); ?></td>
                                        <td><?php echo e($session->completed_at?->format('Y-m-d H:i:s') ?: 'N/A'); ?></td>
                                        <td>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($session->vote_submitted): ?>
                                                <span class="badge bg-success">Yes</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">No</span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </td>
                                        <td><?php echo e($session->ip_address ?: 'N/A'); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No sessions found for this student in this election.</td>
                                    </tr>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Security/Audit Events</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Event</th>
                                    <th>Description</th>
                                    <th>IP</th>
                                    <th>User Agent</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $auditLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($log->created_at?->format('Y-m-d H:i:s')); ?></td>
                                        <td><?php echo e($log->event); ?></td>
                                        <td><?php echo e($log->description); ?></td>
                                        <td><?php echo e($log->ip_address ?: 'N/A'); ?></td>
                                        <td><?php echo e(\Illuminate\Support\Str::limit($log->user_agent ?: 'N/A', 70)); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No audit events found for this student in this election.</td>
                                    </tr>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Shared IP Activity (Potential Collusion Signal)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Other Student ID</th>
                                    <th>Shared IP</th>
                                    <th>Votes Count</th>
                                    <th>Last Vote Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $sharedIpActivity; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($activity->student_id); ?></td>
                                        <td><?php echo e($activity->ip_address); ?></td>
                                        <td><?php echo e($activity->votes_count); ?></td>
                                        <td><?php echo e($activity->last_vote_at); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No shared-IP vote activity found for this voter.</td>
                                    </tr>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78458ab11901cb5e12c9728346766652)): ?>
<?php $attributes = $__attributesOriginal78458ab11901cb5e12c9728346766652; ?>
<?php unset($__attributesOriginal78458ab11901cb5e12c9728346766652); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78458ab11901cb5e12c9728346766652)): ?>
<?php $component = $__componentOriginal78458ab11901cb5e12c9728346766652; ?>
<?php unset($__componentOriginal78458ab11901cb5e12c9728346766652); ?>
<?php endif; ?>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/admin/elections/voter-integrity.blade.php ENDPATH**/ ?>