<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="card-title">
                        <h1>
                            <i class="ki-duotone ki-eye fs-2 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Exam Audit Tool
                        </h1>
                    </div>
                        <div class="float-end">
                            
                        <p class="text-muted">Track and analyze student exam sessions and responses.</p>
                        </div>
                
                </div>
                <div class="card-body">
                    <div class="mb-4 row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="student_id" class="form-label">Search by Student ID</label>
                                <input type="text" id="student_id" wire:model.live.debounce.500ms="student_id" class="form-control" 
                                       placeholder="Enter student ID (e.g. COLLEGE/DEPT/RGN/24/25/001)">
                            </div>
                        </div>
                    </div>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($studentFound): ?>
                        <div class="mb-4 alert alert-success">
                            <h4 class="alert-heading">Student Found!</h4>
                            <p><strong>Name:</strong> <?php echo e($foundStudent->first_name); ?> <?php echo e($foundStudent->last_name); ?> <?php echo e($foundStudent->other_name); ?></p>
                            <p><strong>Student ID:</strong> <?php echo e($foundStudent->student_id); ?></p>
                            <p><strong>Email:</strong> <?php echo e($foundStudent->email); ?></p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$foundUser): ?>
                                <div class="alert alert-warning mt-2">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    This student does not have an associated user account. No exam sessions will be found.
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($foundUser): ?>
                            <div class="mb-4 row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="exam_id" class="form-label">Select Exam</label>
                                        <select id="exam_id" wire:model.live="exam_id" class="form-select">
                                            <option value="">-- Select an Exam --</option>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $exams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exam): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($exam->id); ?>">

                                                    
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->hasRole('System')): ?>
                                                        <?php echo e($exam->id); ?> -
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    
                                                    <?php echo e($exam->display_title); ?> — <?php echo e($exam->course->name ?? 'Unknown Course'); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($exam_id && count($studentExamSessions) > 0): ?>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="session_id" class="form-label">Select Exam Session</label>
                                            <select id="session_id" wire:model.live="session_id" class="form-select">
                                                <option value="">-- Select a Session --</option>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $studentExamSessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($session->id); ?>">
                                                        Session #<?php echo e($session->id); ?> - <?php echo e($session->created_at->format('d M, Y H:i:s')); ?>

                                                    </option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </select>
                                        </div>
                                    </div>
                                <?php elseif($exam_id): ?>
                                    <div class="col-md-6">
                                        <div class="alert alert-info mt-4">
                                            No exam sessions found for this student and exam.
                                        </div>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php elseif($student_id && strlen($student_id) >= 3): ?>
                        <div class="mb-4 alert alert-warning">
                            <h4 class="alert-heading">Student Not Found</h4>
                            <p>No student found with ID containing "<?php echo e($student_id); ?>". Please check and try again.</p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($session_id && $responsesFound): ?>
                        <div class="card mt-4 mb-4">
                            <div class="card-header">
                                <div class="card-title">
                                    <h3>
                                        <i class="ki-duotone ki-chart fs-2 me-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        Score Summary
                                    </h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="card bg-light h-100">
                                            <div class="card-body">
                                                <h5 class="card-title">Questions</h5>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h3 class="mb-0"><?php echo e($totalAttempted); ?> / <?php echo e($totalQuestions); ?></h3>
                                                    <span class="badge bg-<?php echo e($totalAttempted === $totalQuestions ? 'success' : 'warning'); ?> fs-6">
                                                        <?php echo e(round(($totalAttempted / max(1, $totalQuestions)) * 100, 1)); ?>%
                                                    </span>
                                                </div>
                                                <p class="text-muted mt-2">Questions attempted</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="card bg-light h-100">
                                            <div class="card-body">
                                                <h5 class="card-title">Marks</h5>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h3 class="mb-0"><?php echo e($obtainedMarks); ?> / <?php echo e($totalMarks); ?></h3>
                                                    <span class="badge bg-<?php echo e($scorePercentage >= 50 ? 'success' : 'danger'); ?> fs-6">
                                                        <?php echo e($scorePercentage); ?>%
                                                    </span>
                                                </div>
                                                <p class="text-muted mt-2">Marks obtained</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="card bg-light h-100">
                                            <div class="card-body">
                                                <h5 class="card-title">Overall Performance</h5>
                                                <?php
                                                    $performanceClass = 'danger';
                                                    $performanceText = 'Failed';
                                                    
                                                    if ($scorePercentage >= 80) {
                                                        $performanceClass = 'success';
                                                        $performanceText = 'Excellent';
                                                    } elseif ($scorePercentage >= 70) {
                                                        $performanceClass = 'primary';
                                                        $performanceText = 'Very Good';
                                                    } elseif ($scorePercentage >= 60) {
                                                        $performanceClass = 'info';
                                                        $performanceText = 'Good';
                                                    } elseif ($scorePercentage >= 50) {
                                                        $performanceClass = 'warning';
                                                        $performanceText = 'Pass';
                                                    }
                                                ?>
                                                <div class="d-flex justify-content-center align-items-center">
                                                    <div class="text-center">
                                                        <h3 class="mb-0"><?php echo e($scorePercentage); ?>%</h3>
                                                        <span class="badge bg-<?php echo e($performanceClass); ?> fs-6 mt-2">
                                                            <?php echo e($performanceText); ?>

                                                        </span>
                                                    </div>
                                                </div>
                                                <p class="text-center text-muted mt-2"><?php echo e($totalCorrect); ?> of <?php echo e($totalQuestions); ?> correct</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header">
                                <div class="card-title">
                                    <h3>
                                        <i class="ki-duotone ki-document fs-2 me-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        Student Responses
                                    </h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Question</th>
                                                <th>Student's Answer</th>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->hasRole('System')): ?>
                                                    <th>Option ID</th>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <th>Correct Answer</th>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->hasRole('System')): ?>
                                                    <th>Correct ID</th>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <th>Status</th>
                                                <th>All Options</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $sessionResponses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $response): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <tr class="<?php echo e($response['is_correct'] ? 'table-success' : 'table-danger'); ?>">
                                                    <td><?php echo e($index + 1); ?></td>
                                                    <td><?php echo e($response['question_text']); ?></td>
                                                    <td><?php echo e($response['selected_option_text']); ?></td>
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->hasRole('System')): ?>
                                                        <td><?php echo e($response['selected_option_id'] ?? 'N/A'); ?></td>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    <td><?php echo e($response['correct_option_text']); ?></td>
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->hasRole('System')): ?>
                                                        <td><?php echo e($response['correct_option_id'] ?? 'N/A'); ?></td>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    <td>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($response['is_correct']): ?>
                                                            <span class="badge bg-success">Correct</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">Incorrect</span>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#optionsModal<?php echo e($response['question_id']); ?>">
                                                            View Options
                                                        </button>
                                                        
                                                        <!-- Modal for Options -->
                                                        <div class="modal fade" id="optionsModal<?php echo e($response['question_id']); ?>" tabindex="-1" 
                                                             aria-labelledby="optionsModalLabel<?php echo e($response['question_id']); ?>" aria-hidden="true">
                                                            <div class="modal-dialog">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="optionsModalLabel<?php echo e($response['question_id']); ?>">
                                                                            Question Options
                                                                        </h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <p><strong>Question:</strong> <?php echo e($response['question_text']); ?></p>
                                                                        <hr>
                                                                        <h6>All Options:</h6>
                                                                        <ul class="list-group">
                                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $response['all_options']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                                <li class="list-group-item 
                                                                                    <?php echo e($option['id'] == $response['selected_option_id'] ? 'list-group-item-primary' : ''); ?>

                                                                                    <?php echo e($option['is_correct'] ? 'list-group-item-success' : ''); ?>">
                                                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->hasRole('System')): ?>
                                                                                        <strong>ID: <?php echo e($option['id']); ?></strong> - 
                                                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                                                    <?php echo e($option['text']); ?>

                                                                                    
                                                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($option['id'] == $response['selected_option_id']): ?>
                                                                                        <span class="badge bg-primary float-end">Selected</span>
                                                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                                                    
                                                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($option['is_correct']): ?>
                                                                                        <span class="badge bg-success float-end">Correct</span>
                                                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                                                </li>
                                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                                        </ul>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Devices Section -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <div class="card-title">
                                    <h3>
                                        <i class="ki-duotone ki-devices fs-2 me-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        Devices Used
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($deviceAccessLogs) > 1): ?>
                                            <span class="badge bg-warning ms-2"><?php echo e(count($deviceAccessLogs)); ?> devices</span>
                                        <?php elseif(count($deviceAccessLogs) == 1): ?>
                                            <span class="badge bg-success ms-2">Single device</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary ms-2">No device logs</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($deviceAccessLogs) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Access Time</th>
                                                    <th>IP Address</th>
                                                    <th>Browser</th>
                                                    <th>Platform</th>
                                                    <th>Device Type</th>
                                                    <th>Screen Resolution</th>
                                                    <th>Timezone</th>
                                                    <th>Status</th>
                                                    <th>Details</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $deviceAccessLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <tr class="<?php echo e($log['is_conflict'] ? 'table-warning' : ''); ?>">
                                                        <td><?php echo e($index + 1); ?></td>
                                                        <td>
                                                            <small>
                                                                <?php echo e($log['access_time']->format('d M, Y H:i:s')); ?>

                                                            </small>
                                                        </td>
                                                        <td>
                                                            <code><?php echo e($log['ip_address'] ?? 'N/A'); ?></code>
                                                        </td>
                                                        <td><?php echo e($log['browser']); ?></td>
                                                        <td><?php echo e($log['platform']); ?></td>
                                                        <td><?php echo e($log['device_type']); ?></td>
                                                        <td><small><?php echo e($log['screen_resolution']); ?></small></td>
                                                        <td><small><?php echo e($log['timezone']); ?></small></td>
                                                        <td>
                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($log['is_conflict']): ?>
                                                                <span class="badge bg-danger">Conflict Detected</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-success">Normal</span>
                                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <button class="btn btn-sm btn-info" 
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#deviceModal<?php echo e($log['id']); ?>">
                                                                View Full Details
                                                            </button>
                                                            
                                                            <!-- Modal for Device Details -->
                                                            <div class="modal fade" id="deviceModal<?php echo e($log['id']); ?>" tabindex="-1" 
                                                                 aria-labelledby="deviceModalLabel<?php echo e($log['id']); ?>" aria-hidden="true">
                                                                <div class="modal-dialog modal-lg">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h5 class="modal-title" id="deviceModalLabel<?php echo e($log['id']); ?>">
                                                                                Device Details - Access #<?php echo e($index + 1); ?>

                                                                            </h5>
                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            <div class="row mb-3">
                                                                                <div class="col-md-6">
                                                                                    <p><strong>Access Time:</strong></p>
                                                                                    <p class="text-muted"><?php echo e($log['access_time']->format('d M, Y H:i:s')); ?></p>
                                                                                </div>
                                                                                <div class="col-md-6">
                                                                                    <p><strong>Conflict Status:</strong></p>
                                                                                    <p>
                                                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($log['is_conflict']): ?>
                                                                                            <span class="badge bg-danger fs-6">Conflict Detected</span>
                                                                                        <?php else: ?>
                                                                                            <span class="badge bg-success fs-6">Normal</span>
                                                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                                                    </p>
                                                                                </div>
                                                                            </div>
                                                                            
                                                                            <hr>
                                                                            
                                                                            <div class="row mb-3">
                                                                                <div class="col-md-6">
                                                                                    <p><strong>IP Address:</strong></p>
                                                                                    <p class="text-muted"><code><?php echo e($log['ip_address'] ?? 'N/A'); ?></code></p>
                                                                                </div>
                                                                                <div class="col-md-6">
                                                                                    <p><strong>Device Identifier:</strong></p>
                                                                                    <p class="text-muted"><code><?php echo e($log['device_identifier'] ?? 'Unknown'); ?></code></p>
                                                                                </div>
                                                                            </div>
                                                                            
                                                                            <div class="row mb-3">
                                                                                <div class="col-md-6">
                                                                                    <p><strong>Browser:</strong></p>
                                                                                    <p class="text-muted"><?php echo e($log['browser']); ?></p>
                                                                                </div>
                                                                                <div class="col-md-6">
                                                                                    <p><strong>Platform:</strong></p>
                                                                                    <p class="text-muted"><?php echo e($log['platform']); ?></p>
                                                                                </div>
                                                                            </div>
                                                                            
                                                                            <div class="row mb-3">
                                                                                <div class="col-md-6">
                                                                                    <p><strong>Device Type:</strong></p>
                                                                                    <p class="text-muted"><?php echo e($log['device_type']); ?></p>
                                                                                </div>
                                                                                <div class="col-md-6">
                                                                                    <p><strong>Screen Resolution:</strong></p>
                                                                                    <p class="text-muted"><?php echo e($log['screen_resolution']); ?></p>
                                                                                </div>
                                                                            </div>
                                                                            
                                                                            <div class="row mb-3">
                                                                                <div class="col-md-12">
                                                                                    <p><strong>Timezone:</strong></p>
                                                                                    <p class="text-muted"><?php echo e($log['timezone']); ?></p>
                                                                                </div>
                                                                            </div>
                                                                            
                                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($log['user_agent']): ?>
                                                                                <hr>
                                                                                <p><strong>User Agent:</strong></p>
                                                                                <p class="text-muted small"><code><?php echo e($log['user_agent']); ?></code></p>
                                                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                                            
                                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->hasRole('System') && !empty($log['all_info'])): ?>
                                                                                <hr>
                                                                                <p><strong>Raw Device Info:</strong></p>
                                                                                <pre class="bg-light p-3 rounded"><small><?php echo e(json_encode($log['all_info'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)); ?></small></pre>
                                                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($deviceAccessLogs) > 1): ?>
                                        <div class="alert alert-warning mt-4">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <strong>Multiple Devices Detected:</strong> This student accessed the exam from <?php echo e(count($deviceAccessLogs)); ?> different devices. 
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(collect($deviceAccessLogs)->where('is_conflict', true)->count() > 0): ?>
                                                Some accesses were flagged as conflicts. Review the device details and timestamps carefully.
                                            <?php else: ?>
                                                Verify if all device changes are legitimate (e.g., session timeout and re-login on same device).
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        No device access logs found for this exam session. This may indicate that device tracking was not enabled during the exam.
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    <?php elseif($session_id && !$responsesFound): ?>
                        <div class="alert alert-info mt-4">
                            <h4 class="alert-heading">No Responses Found</h4>
                            <p>No responses were found for this exam session. The student may not have answered any questions yet.</p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/livewire/admin/exam-response-tracker.blade.php ENDPATH**/ ?>