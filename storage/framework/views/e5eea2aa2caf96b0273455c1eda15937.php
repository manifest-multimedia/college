<div class="container">
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-info shadow-sm">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-info-circle-fill me-3 fs-4"></i>
                            <div>
                                <strong>Exam Review Mode</strong> - You are viewing this exam in read-only mode. The exam was completed on <?php echo e($examSession->completed_at->format('F j, Y \a\t g:i A')); ?>.
                            </div>
                        </div>
                        <a href="<?php echo e(route('take-exam')); ?>" class="btn btn-sm btn-outline-info ms-3 flex-shrink-0">
                            <i class="bi bi-box-arrow-left me-2"></i>Exit Review
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h1 class="card-title h4 mb-0">
                                <i class="bi bi-file-text me-2"></i> <?php echo e($exam->display_title); ?> Results
                            </h1>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Student:</strong> <?php echo e($student->user->name ?? $student->first_name . ' ' . $student->last_name); ?></p>
                                <p class="mb-0"><strong>Student ID:</strong> <?php echo e($student_index); ?></p>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <p class="mb-0"><strong>Completion Date:</strong> <?php echo e($examSession->completed_at->format('F j, Y g:i A')); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Main Questions Column -->
            <div class="col-lg-9 mb-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="card shadow-sm mb-4 question-card exam-protected" id="question-<?php echo e($index + 1); ?>">
                        <div class="card-header bg-white py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Question <?php echo e($index + 1); ?> of <?php echo e(count($questions)); ?></h5>
                                <span class="badge bg-secondary"><?php echo e($question['marks']); ?> <?php echo e($question['marks'] > 1 ? 'marks' : 'mark'); ?></span>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="question-text mb-4"><?php echo $question['question']; ?></div>
                            
                            <div class="options">
                                <?php
                                    $selectedOption = $responses[$question['id']] ?? null;
                                ?>
                                
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $question['options']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($option['option_text'])): ?>
                                        <div class="option-wrapper mb-3">
                                            <div class="card option-card <?php if($option['id'] == $selectedOption): ?> border-success selected-option <?php else: ?> border-light <?php endif; ?>">
                                                <div class="card-body py-3">
                                                    <div class="form-check d-flex align-items-center">
                                                        <input class="form-check-input me-3" 
                                                                type="radio" 
                                                                disabled
                                                                <?php echo e(($selectedOption == $option['id']) ? 'checked' : ''); ?>>
                                                        <label class="form-check-label <?php if($selectedOption == $option['id']): ?> fw-medium <?php endif; ?>">
                                                            <?php echo e($option['option_text']); ?>

                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($option['id'] == $selectedOption): ?>
                                                                <span class="badge bg-success ms-2">Your Answer</span>
                                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                
                <div class="d-flex justify-content-center mt-4 mb-5">
                    <a href="<?php echo e(route('take-exam')); ?>" class="btn btn-primary px-4">
                        <i class="bi bi-arrow-left me-2"></i> Back to Exam Portal
                    </a>
                </div>
            </div>
            
            <!-- Questions Overview Sidebar -->
            <div class="col-lg-3">
                <div class="card shadow-sm sticky-top" style="top: 20px; z-index: 100;">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0">Questions Overview</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-center mb-3">
                            <div class="text-center">
                                <div class="h4 mb-0"><?php echo e(count(array_filter($responses))); ?> / <?php echo e(count($questions)); ?></div>
                                <div class="text-muted small">Questions Answered</div>
                            </div>
                        </div>
                        
                        <hr class="my-3">
                        
                        <div class="questions-navigator">
                            <div class="d-flex flex-wrap justify-content-center">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $selectedOption = $responses[$question['id']] ?? null;
                                        $isAnswered = $selectedOption !== null;
                                        $statusClass = $isAnswered ? 'bg-success text-white' : 'bg-light text-dark';
                                    ?>
                                    
                                    <div 
                                        class="question-bubble d-flex align-items-center justify-content-center m-1 <?php echo e($statusClass); ?>"
                                        data-question-id="<?php echo e($index + 1); ?>"
                                        onclick="scrollToQuestion(<?php echo e($index + 1); ?>)"
                                    >
                                        <?php echo e($index + 1); ?>

                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="mt-4 pt-2 border-top">
                            <div class="legend small">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="legend-indicator bg-success me-2"></div>
                                    <span>Answered</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="legend-indicator bg-light border me-2"></div>
                                    <span>Not Answered</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php echo $__env->make('components.partials.exam-security', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    
    <style>
        
        /* Question styling */
        .question-text {
            font-size: 1rem;
            line-height: 1.6;
        }
        
        /* Option card styling */
        .option-card {
            transition: all 0.2s ease;
            border-width: 1px;
            border-radius: 0.5rem;
            overflow: hidden;
        }

        label {
            cursor: pointer;
            color:black;
            opacity: 0.8 !important;
        }
        
        .option-card:hover {
            /* box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); */
        }
        
        .selected-option {
            background-color: rgba(40, 167, 69, 0.05);
            border-width: 2px !important;
        }
        
        .form-check-input:checked {
            background-color: #28a745;
            border-color: #28a745;
        }
        
        /* Question bubble navigation */
        .question-bubble {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            font-weight: 500;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            transition: all 0.2s ease;
        }
        
        .question-bubble:hover {
            transform: scale(1.1);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        /* Legend indicators */
        .legend-indicator {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }
        
        /* Highlight animation */
        .highlight-question {
            animation: pulse 1.5s;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(0, 123, 255, 0); }
            100% { box-shadow: 0 0 0 0 rgba(0, 123, 255, 0); }
        }
    </style>
    
    <script>
        function scrollToQuestion(questionNumber) {
            const questionElement = document.getElementById('question-' + questionNumber);
            if (questionElement) {
                questionElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                
                // Highlight the question briefly
                questionElement.classList.add('highlight-question');
                setTimeout(() => {
                    questionElement.classList.remove('highlight-question');
                }, 2000);
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // Send heartbeats to keep the session alive for viewing purposes
            const heartbeatInterval = setInterval(function() {
                if (document.visibilityState === 'visible') {
                    window.Livewire.find('<?php echo e($_instance->getId()); ?>').heartbeat();
                }
            }, 60000); // Send heartbeat every minute
            
            // Clear interval on page unload
            window.addEventListener('beforeunload', function() {
                clearInterval(heartbeatInterval);
            });
        });
    </script>
</div>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/livewire/exam-review-mode.blade.php ENDPATH**/ ?>