<div class="container my-5 <?php echo e($examExpired && !$canStillSubmit ? 'exam-expired' : ''); ?>">
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($isPreview) && $isPreview): ?>
        <div class="alert alert-info mb-4 d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-eye me-2"></i> <strong>Preview Mode</strong> — Viewing as: <?php echo e($student_name); ?> | No data will be saved
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-secondary">Theme: <?php echo e(ucfirst($theme)); ?></span>
                <button wire:click="switchTheme('default')" class="btn btn-sm btn-light <?php echo e($theme === 'default' ? 'active' : ''); ?>" type="button">Default</button>
                <button wire:click="switchTheme('one-by-one')" class="btn btn-sm btn-light <?php echo e($theme === 'one-by-one' ? 'active' : ''); ?>" type="button">One-by-One</button>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="row">
        <!-- Main Exam Content -->
        <div class="mb-4 text-center">
            <h2><?php echo e($exam->display_title); ?></h2>
            <p class="text-muted mb-0"><?php echo e($exam->course->name ?? 'Unknown Course'); ?></p>
            Date of Exam: <?php echo e($examSession->started_at); ?>

            <p>Student Name: <?php echo e($student_name); ?> | Student ID : <?php echo e($student_index); ?> </p>
            <p>Proctor: AI Sensei </p>

            <div class="p-3 rounded border shadow-lg row bg-light">
                <div class="col-md-12">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center">
                        <div class="p-3 pt-4 card-text w-100" style="font-size:18px;font-weight:600">
                            <div class="d-flex justify-content-center">
                                <h4 class="text-center text-danger"><strong> Instructions <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($isPreview) && $isPreview): ?>(One-by-One Mode)<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></strong></h4>
                            </div>
                            <p>
                                You're being proctored by AI Sensei. <br />Any suspecious activity will result in immediate
                                disqualification. Answer one question at a time using the navigation below.
                                Total questions: <?php echo e(count($questions)); ?>.
                                <br>
                                <?php
                                    $baseDuration = $examSession->exam->duration ?? 0;
                                    $extraTime = $examSession->extra_time_minutes ?? 0;
                                    $totalAllocated = $baseDuration + $extraTime;
                                    
                                // For restored sessions, calculate actual restoration time (excluding catch-up time)
                                $actualRestorationTime = $extraTime;
                                if (($examSession->is_restored ?? false) && $examSession->restored_at && $examSession->started_at) {
                                    $minutesFromStartToRestore = $examSession->started_at->diffInMinutes($examSession->restored_at);
                                    $catchUpTime = max(0, $minutesFromStartToRestore - $baseDuration);
                                    $actualRestorationTime = ceil(max(0, $extraTime - $catchUpTime));
                                }
                                ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($examSession->is_restored ?? false): ?>
                                    <span class="badge bg-warning text-dark"><i class="fas fa-rotate-right"></i> Restored Session</span>
                                    This session was restored with <span class="text-danger"><?php echo e($actualRestorationTime); ?> minutes</span> allocated.<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($actualRestorationTime > 0): ?> <span class="badge bg-info text-white ms-1"><i class="fas fa-clock-rotate-left"></i> <?php echo e($actualRestorationTime); ?> min restoration time</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php else: ?>
                                    This exam is <span class="text-danger"><?php echo e($totalAllocated); ?> minutes</span> long.<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($extraTime > 0): ?> <span class="badge bg-warning text-dark ms-1"><i class="fas fa-plus"></i> <?php echo e($extraTime); ?> min extra time</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </p>

                            <div class="d-flex justify-content-center w-100">
                                <!-- Timer Component (now enabled in preview mode for testing) -->
                                <?php if (isset($component)) { $__componentOriginalb42fd315d5d343fef69a687a02ef7d1e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb42fd315d5d343fef69a687a02ef7d1e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.exam.timer','data' => ['examSessionId' => $examSession->id,'startedAt' => $examSession->started_at->toIso8601String(),'completedAt' => $examSession->adjustedCompletionTime->toIso8601String(),'hasExtraTime' => $hasExtraTime,'extraTimeMinutes' => $extraTimeMinutes,'isRestored' => $examSession->is_restored ?? false,'debug' => false,'class' => 'mt-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('exam.timer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['examSessionId' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($examSession->id),'startedAt' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($examSession->started_at->toIso8601String()),'completedAt' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($examSession->adjustedCompletionTime->toIso8601String()),'hasExtraTime' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($hasExtraTime),'extraTimeMinutes' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($extraTimeMinutes),'isRestored' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($examSession->is_restored ?? false),'debug' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false),'class' => 'mt-3']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb42fd315d5d343fef69a687a02ef7d1e)): ?>
<?php $attributes = $__attributesOriginalb42fd315d5d343fef69a687a02ef7d1e; ?>
<?php unset($__attributesOriginalb42fd315d5d343fef69a687a02ef7d1e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb42fd315d5d343fef69a687a02ef7d1e)): ?>
<?php $component = $__componentOriginalb42fd315d5d343fef69a687a02ef7d1e; ?>
<?php unset($__componentOriginalb42fd315d5d343fef69a687a02ef7d1e); ?>
<?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- row -->
        </div>

        <div class="row h-100">
            <!-- Main Exam Content -->
            <div class="col-md-9 d-flex flex-column">
                <div class="p-4 shadow-lg card question-card position-relative exam-protected" style="min-height: 550px; width: 100%;">
                    <div class="watermark"><?php echo e($student_name); ?></div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($examExpired && !$canStillSubmit): ?>
                        <div class="alert alert-warning mb-4">
                            <h4 class="alert-heading"><i class="bi bi-exclamation-triangle me-2"></i> Exam Completed</h4>
                            <p>Your exam time has expired, and your answers have been submitted automatically. You can
                                no longer modify your responses.</p>
                        </div>
                    <?php elseif($examExpired && $canStillSubmit): ?>
                        <div class="alert alert-info mb-4">
                            <h4 class="alert-heading"><i class="bi bi-clock-history me-2"></i> Extra Time Active</h4>
                            <p>Regular exam time has expired, but you are allowed to continue answering questions using
                                your extra time allocation.</p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <!-- One Question At A Time -->
                    <?php
                        $qCount = count($questions);
                        $qIndex = $currentIndex ?? 0;
                        $q = $qCount > 0 ? $questions[$qIndex] : null;
                    ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($q): ?>
                        <div class="p-3 mb-2 rounded-border">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-primary fs-6">Question <?php echo e($qIndex + 1); ?> of <?php echo e($qCount); ?></span>
                                <span class="text-muted">Marks: <?php echo e($q['marks']); ?></span>
                            </div>
                            <div class="mb-4" style="font-size: 18px; font-weight: 600;">
                                <strong>Q<?php echo e($qIndex + 1); ?>:</strong> <?php echo $q['question']; ?>

                            </div>
                            <ul class="list-unstyled">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $q['options']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li class="mb-3">
                                        <label class="form-check-label d-flex align-items-center">
                                            <input type="radio" class="mx-2 form-check-input"
                                                name="responses_one[<?php echo e($q['id']); ?>]"
                                                value="<?php echo e($option['id']); ?>"
                                                wire:click="storeResponse(<?php echo e($q['id']); ?>, <?php echo e($option['id']); ?>)"
                                                <?php if(isset($responses[$q['id']]) && $responses[$q['id']] == $option['id']): ?> checked <?php endif; ?>>
                                            <span style="font-size: 16px;"><?php echo e(str_replace(['→', '&rarr;', '->'], '', $option['option_text'])); ?></span>
                                        </label>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </ul>
                        </div>

                        <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                            <button class="btn btn-light" wire:click="prevQuestion" <?php if($currentIndex === 0): echo 'disabled'; endif; ?>>
                                <i class="bi bi-arrow-left me-2"></i> Previous
                            </button>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($examExpired && !$canStillSubmit): ?>
                                <a href="<?php echo e(route('take-exam')); ?>" class="btn btn-secondary">
                                    <i class="bi bi-box-arrow-left me-2"></i> Return to Exam Login
                                </a>
                            <?php else: ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentIndex < $qCount - 1): ?>
                                    <button class="btn btn-primary" wire:click="nextQuestion">
                                        Next <i class="bi bi-arrow-right ms-2"></i>
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-success" wire:click="submitExam">
                                        Submit Exam <i class="bi bi-check2-circle ms-2"></i>
                                    </button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">No questions available for this exam.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <!-- Sidebar Overview -->
            <div class="shadow-lg col-md-3 sidebar d-flex flex-column card question-card" style="height:550px">
                <div class="p-4 text-center">
                    <h5>Questions Overview</h5>
                    <p class="mb-0">
                        Questions Answered: <strong id="answeredCount"><?php echo e(count(array_filter($responses))); ?></strong> /
                        <?php echo e(count($questions)); ?>

                    </p>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($examExpired && !$canStillSubmit): ?>
                        <div class="alert alert-info mt-3 mb-0 py-2 small">
                            <i class="bi bi-info-circle me-1"></i> Exam has been submitted
                        </div>
                    <?php elseif($examExpired && $canStillSubmit): ?>
                        <div class="alert alert-warning mt-3 mb-0 py-2 small">
                            <i class="bi bi-clock-history me-1"></i> Using extra time
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div id="questionsOverview" class="overflow-y-auto p-3 mb-2 flex-grow-1">
                    <div class="flex-wrap gap-3 tracker-container d-flex justify-content-center">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="tracker-item rounded-circle text-center 
                                   <?php if(isset($responses[$question['id']])): ?> answered <?php else: ?> unanswered <?php endif; ?>
                                   <?php if($index === $currentIndex): ?> current-question <?php endif; ?>"
                                 style="width: 50px; height: 50px; line-height: 50px; cursor: pointer;"
                                 wire:click="goToQuestion(<?php echo e($index); ?>)">
                                <?php echo e($index + 1); ?>

                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                <div class="bg-white card-footer d-flex justify-content-center align-items-center">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($examExpired && !$canStillSubmit): ?>
                        <a href="<?php echo e(route('take-exam')); ?>" class="btn btn-secondary w-100">
                            <i class="bi bi-box-arrow-left me-2"></i> Return to Exam Login
                        </a>
                    <?php else: ?>
                        <button class="btn btn-primary w-100" wire:click="submitExam" id="submitBtn">Submit Exam</button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php $__env->startPush('styles'); ?>

    <?php echo $__env->make('components.partials.styles.exam-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('components.partials.styles.scrollbar-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('components.partials.exam-security', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <style>
        .question{ min-width:720px !important; width:100% !important; }
        .tracker-item { transition: all 0.3s ease; }
        .tracker-item:hover { transform: scale(1.1); box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .tracker-item.answered { background-color: #28a745; color: #fff; }
        .tracker-item.unanswered { background-color: #f8f9fa; }
        .tracker-item.current-question { 
            border: 3px solid #007bff; 
            box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
        }
    </style>
    <!-- Timer scripts and styles -->
    <link href="<?php echo e(asset('css/exam-timer.css')); ?>" rel="stylesheet">
   <?php $__env->stopPush(); ?>

    <?php $__env->startPush('scripts'); ?>
 <script src="<?php echo e(asset('js/services/ExamTimerService.js')); ?>"></script>

    <script>
        document.addEventListener('livewire:initialized', function() {
            Livewire.on('responseUpdated', () => {
                updateQuestionOverview();
            });
        });

        function updateQuestionOverview() {
            const answeredCount = document.querySelectorAll('input[type="radio"]:checked').length;
            const totalQuestions = <?php echo e(count($questions)); ?>;
            const answeredCountEl = document.getElementById('answeredCount');
            if (answeredCountEl) {
                answeredCountEl.textContent = answeredCount;
            }
        }
    </script>
    <?php $__env->stopPush(); ?>
</div>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/livewire/online-examination-one.blade.php ENDPATH**/ ?>