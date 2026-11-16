<div class="container-fluid mt-4">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <ul>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </ul>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('success')): ?>
        <div class="alert alert-success">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('error')): ?>
        <div class="alert alert-danger">
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('message')): ?>
        <div class="alert alert-success">
            <?php echo e(session('message')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$viewingQuestionSet): ?>
        <!-- Question Sets View -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title m-0">Question Bank</h3>
                <div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($exam_id): ?>
                        <!-- When viewing from exam context, show exam info -->
                        <span class="badge bg-info me-2">
                            Exam: <?php echo e($exams->firstWhere('id', $exam_id)->title ?? 'Unknown'); ?>

                        </span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <button class="btn btn-primary" wire:click="$set('createNewSet', <?php echo e(!$createNewSet); ?>)">
                        <?php echo e($createNewSet ? 'Cancel' : 'Create New Set'); ?>

                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($createNewSet): ?>
                    <!-- Create New Question Set Form -->
                    <div class="p-4 mb-4 bg-light rounded border">
                        <h5 class="mb-3">Create New Question Set</h5>
                        <div class="mb-3">
                            <label for="subjectSelect" class="form-label">Subject <span class="text-danger">*</span></label>
                            <select id="subjectSelect" class="form-select" wire:model="subject_id" required>
                                <option value="">Select Subject</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($subject->id); ?>"><?php echo e($subject->course_code); ?> - <?php echo e($subject->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['subject_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Question Set Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" wire:model="newSetName" placeholder="Enter question set name" required>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['newSetName'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description (Optional)</label>
                            <textarea class="form-control" wire:model="newSetDescription" rows="3" placeholder="Describe this question set"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Difficulty Level</label>
                            <select class="form-select" wire:model="newSetDifficulty">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $difficultyLevels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $level): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($key); ?>"><?php echo e($level); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                        </div>
                        <div class="text-end">
                            <button class="btn btn-success" wire:click="createQuestionSet">
                                <i class="bi bi-save me-1"></i> Create Question Set
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$exam_id): ?>
                        <!-- Subject Filter (only show if not in exam context) -->
                        <div class="mb-4">
                            <label for="subjectFilter" class="form-label fw-bold">Filter by Course:</label>
                            <select id="subjectFilter" class="form-select" wire:model.live="subject_id">
                                <option value="">All Courses</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($subject->id); ?>"><?php echo e($subject->course_code); ?> - <?php echo e($subject->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                        </div>
                    <?php else: ?>
                        <!-- Exam Selection (when in exam context) -->
                        <div class="mb-4">
                            <label for="examSelect" class="form-label fw-bold">Select Exam:</label>
                            <select id="examSelect" class="form-select" wire:model="exam_id" wire:change="loadQuestions">
                                <option value="">Select Exam</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $exams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exam): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($exam->id); ?>"><?php echo e($exam->display_title); ?> — <?php echo e($exam->course->course_code ?? 'N/A'); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <!-- Question Sets Cards Grid -->
                    <div class="row g-4 py-3">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $filteredQuestionSets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $set): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="col-md-4 col-lg-3 mb-4">
                                <div class="card h-100">
                                    <div class="card-header p-3 <?php echo e(match($set->difficulty_level) {
                                        'easy' => 'bg-success-subtle',
                                        'hard' => 'bg-danger-subtle',
                                        default => 'bg-warning-subtle',
                                    }); ?>">
                                        <h5 class="card-title mb-0 fw-bold"><?php echo e($set->name); ?></h5>
                                    </div>
                                    <div class="card-body d-flex flex-column">
                                        <p class="text-muted small mb-1">
                                            <i class="bi bi-book"></i>
                                            <strong>Subject:</strong> <?php echo e($set->course->course_code ?? 'N/A'); ?>

                                        </p>
                                        <p class="text-muted small mb-3">
                                            <i class="bi bi-bar-chart"></i>
                                            <strong>Difficulty:</strong> 
                                            <span class="badge <?php echo e(match($set->difficulty_level) {
                                                'easy' => 'bg-success',
                                                'hard' => 'bg-danger',
                                                default => 'bg-warning',
                                            }); ?>">
                                                <?php echo e(ucfirst($set->difficulty_level)); ?>

                                            </span>
                                        </p>
                                        
                                        <p class="card-text mb-3">
                                            <?php echo e(Str::limit($set->description, 100) ?: 'No description available.'); ?>

                                        </p>
                                        
                                        <?php
                                            $questionCount = App\Models\Question::where('question_set_id', $set->id)->count();
                                        ?>
                                        
                                        <div class="d-flex align-items-center mt-auto mb-2">
                                            <span class="badge bg-secondary me-2"><?php echo e($questionCount); ?> Questions</span>
                                        </div>
                                        
                                        <div class="d-grid gap-2">
                                            <button class="btn btn-primary btn-sm" wire:click="viewQuestionSet(<?php echo e($set->id); ?>)">
                                                <i class="bi bi-pencil-square me-1"></i> Manage Questions
                                            </button>
                                            
                                            <div class="btn-group w-100" role="group">
                                                <a href="<?php echo e(route('question.sets.import', $set->id)); ?>" class="btn btn-outline-warning btn-sm">
                                                    <i class="bi bi-upload me-1"></i>Import
                                                </a>
                                                <a href="<?php echo e(route('question.sets.questions.create', $set->id)); ?>" class="btn btn-outline-success btn-sm">
                                                    <i class="bi bi-plus me-1"></i>Add Question
                                                </a>
                                            </div>
                                            
                                            <button type="button" class="btn btn-outline-info btn-sm" wire:click.prevent="showCopyModal(<?php echo e($set->id); ?>)">
                                                <i class="bi bi-files me-1"></i> Duplicate
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="col-12">
                                <div class="alert alert-info text-center">
                                    <i class="bi bi-info-circle me-2"></i>
                                    No question sets found. Create your first question set to get started.
                                </div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <!-- Question Set Questions View -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center pt-5">
                <div>
                    <button class="btn btn-sm btn-outline-secondary me-2" wire:click="backToQuestionSets">
                        <i class="bi bi-arrow-left me-1"></i> Back to Sets
                    </button>
                    <h4 class="d-inline m-0"><?php echo e($currentQuestionSet->name ?? 'Question Set'); ?></h4>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentQuestionSet): ?>
                        <span class="badge <?php echo e(match($currentQuestionSet->difficulty_level) {
                            'easy' => 'bg-success',
                            'hard' => 'bg-danger',
                            default => 'bg-warning',
                        }); ?> ms-2"><?php echo e(ucfirst($currentQuestionSet->difficulty_level)); ?></span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div>
                    <span class="badge bg-secondary"><?php echo e(count($questions)); ?> Questions</span>
            </div>
            <div class="card-body">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentQuestionSet): ?>
                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-3">
                            <span class="me-3"><i class="bi bi-book fs-5"></i></span>
                            <div>
                                <p class="mb-0"><strong>Subject:</strong> <?php echo e($currentQuestionSet->course->course_code ?? 'N/A'); ?> - <?php echo e($currentQuestionSet->course->name ?? 'Unknown'); ?></p>
                            </div>
                        </div>
                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentQuestionSet->description): ?>
                            <div class="d-flex align-items-center mb-3">
                                <span class="me-3"><i class="bi bi-info-circle fs-5"></i></span>
                                <div>
                                    <p class="mb-0"><?php echo e($currentQuestionSet->description); ?></p>
                                </div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <!-- Advanced Filters and Controls -->
                <div class="mb-4">
                    <div class="row g-3">
                        <!-- Search and Filters -->
                        <div class="col-md-12">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Search questions..." 
                                    wire:model.live="searchTerm">
                                <button class="btn btn-outline-secondary" type="button" 
                                    wire:click="$toggle('showAdvancedFilters')">
                                    <i class="bi bi-funnel"></i> Filters
                                </button>
                            </div>
                        </div>
                        
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showAdvancedFilters): ?>
                        <div class="mt-3 p-3 bg-light rounded">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Question Type:</label>
                                    <select class="form-select form-select-sm" wire:model.live="filterType">
                                        <option value="">All Types</option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $questionTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($type); ?>"><?php echo e($label); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Difficulty:</label>
                                    <select class="form-select form-select-sm" wire:model.live="filterDifficulty">
                                        <option value="">All Difficulties</option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $difficultyLevels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $level => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($level); ?>"><?php echo e($label); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button class="btn btn-outline-secondary btn-sm w-100" wire:click="clearFilters">
                                        <i class="bi bi-x-circle"></i> Clear Filters
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <!-- Bulk Operations -->
                        <div class="col-md-12">
                            <div class="btn-group w-100" role="group">
                                <button class="btn <?php echo e($bulkMode ? 'btn-warning' : 'btn-outline-secondary'); ?>" 
                                    wire:click="toggleBulkMode">
                                    <i class="bi bi-check2-square"></i> Bulk
                                </button>
                                <button class="btn btn-outline-info" wire:click="$toggle('showStatistics')">
                                    <i class="bi bi-bar-chart"></i> Stats
                                </button>
                                <a href="<?php echo e(route('question.sets.import', $question_set_id)); ?>" class="btn btn-outline-warning">
                                    <i class="bi bi-upload"></i> Import
                                </a>
                                <a href="<?php echo e(route('question.sets.questions.create', $question_set_id)); ?>" class="btn btn-outline-success">
                                    <i class="bi bi-plus-circle"></i> Add
                                </a>
                            </div>
                        </div>
                    </div>
                    
                </div>
                
                <!-- Question Set Statistics -->
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showStatistics && $questionSetStats): ?>
                    <div class="mb-4">
                        <div class="card bg-light">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Question Set Statistics</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <div class="h4 mb-0 text-primary"><?php echo e($questionSetStats['total_questions']); ?></div>
                                            <small class="text-muted">Total Questions</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <div class="h4 mb-0 text-success"><?php echo e($questionSetStats['total_marks']); ?></div>
                                            <small class="text-muted">Total Marks</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <div class="h4 mb-0 text-info"><?php echo e(number_format($questionSetStats['avg_marks_per_question'], 1)); ?></div>
                                            <small class="text-muted">Avg Marks/Question</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <div class="h4 mb-0 text-warning"><?php echo e($questionSetStats['questions_with_explanations']); ?></div>
                                            <small class="text-muted">With Explanations</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <h6>Difficulty Distribution</h6>
                                        <div class="d-flex justify-content-between">
                                            <span class="badge bg-success">Easy: <?php echo e($questionSetStats['difficulty_breakdown']['easy']); ?></span>
                                            <span class="badge bg-warning">Medium: <?php echo e($questionSetStats['difficulty_breakdown']['medium']); ?></span>
                                            <span class="badge bg-danger">Hard: <?php echo e($questionSetStats['difficulty_breakdown']['hard']); ?></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Question Types</h6>
                                        <div class="d-flex justify-content-between">
                                            <span class="badge bg-primary">MCQ: <?php echo e($questionSetStats['type_breakdown']['MCQ']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                
                <!-- Bulk Operations Panel -->
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bulkMode): ?>
                    <div class="mb-4">
                        <div class="card border-warning">
                            <div class="card-header bg-warning bg-opacity-25">
                                <h6 class="mb-0"><i class="bi bi-check2-square me-2"></i>Bulk Operations (<?php echo e(count($selectedQuestions)); ?> selected)</h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <button class="btn btn-outline-primary btn-sm w-100" wire:click="selectAllQuestions">
                                            <i class="bi bi-check-all"></i> Select All
                                        </button>
                                    </div>
                                    <div class="col-md-4">
                                        <button class="btn btn-outline-secondary btn-sm w-100" wire:click="deselectAllQuestions">
                                            <i class="bi bi-x-square"></i> Deselect All
                                        </button>
                                    </div>
                                    <div class="col-md-4">
                                        <button class="btn btn-outline-danger btn-sm w-100" wire:click="bulkDeleteQuestions"
                                            wire:confirm="Are you sure you want to delete the selected questions?"
                                            <?php echo e(empty($selectedQuestions) ? 'disabled' : ''); ?>>
                                            <i class="bi bi-trash"></i> Delete Selected
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Move to Question Set:</label>
                                        <div class="input-group">
                                            <select class="form-select form-select-sm" wire:model="targetQuestionSetForMove">
                                                <option value="">Select question set...</option>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $availableQuestionSetsForMove; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $set): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($set->id); ?>"><?php echo e($set->name); ?></option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </select>
                                            <button class="btn btn-outline-success btn-sm" wire:click="bulkMoveQuestions"
                                                <?php echo e(empty($selectedQuestions) || !$targetQuestionSetForMove ? 'disabled' : ''); ?>>
                                                <i class="bi bi-arrow-right"></i> Move
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label">Bulk Update Difficulty:</label>
                                        <div class="btn-group w-100" role="group">
                                            <button class="btn btn-outline-success btn-sm" wire:click="bulkUpdateDifficulty('easy')"
                                                <?php echo e(empty($selectedQuestions) ? 'disabled' : ''); ?>>Easy</button>
                                            <button class="btn btn-outline-warning btn-sm" wire:click="bulkUpdateDifficulty('medium')"
                                                <?php echo e(empty($selectedQuestions) ? 'disabled' : ''); ?>>Medium</button>
                                            <button class="btn btn-outline-danger btn-sm" wire:click="bulkUpdateDifficulty('hard')"
                                                <?php echo e(empty($selectedQuestions) ? 'disabled' : ''); ?>>Hard</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                
                          
 
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="p-4 mb-4 w-full rounded border bg-white shadow-sm hover:shadow-md transition-shadow <?php echo e($bulkMode && in_array($question['id'] ?? null, $selectedQuestions) ? 'border-warning bg-warning bg-opacity-10' : ''); ?>">
                        <div class="d-flex">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($bulkMode): ?>
                                <div class="me-3 d-flex justify-content-center align-items-center">
                                    <input type="checkbox" class="form-check-input" 
                                        wire:model.live="selectedQuestions" 
                                        value="<?php echo e($question['id']); ?>"
                                        <?php echo e(!isset($question['id']) ? 'disabled' : ''); ?>

                                        id="question-<?php echo e($question['id'] ?? 'new'); ?>">
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <div class="me-4 d-flex justify-content-center align-items-center rounded-circle bg-primary text-white" style="width: 48px; height: 48px; min-width: 48px; font-size: 1.1rem">
                                <span class="fw-bold"><?php echo e($index + 1); ?></span>
                            </div>
                            <div class="flex-grow-1">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Question Text:</label>
                                    <textarea rows="3" class="form-control" wire:model="questions.<?php echo e($index); ?>.question_text" placeholder="Enter Question"></textarea>
                                </div>

                                
                                <?php
                                    $mediaItems = collect($question['attachments'] ?? [])
                                        ->whereIn('attachment_type', ['image', 'audio', 'video'])
                                        ->map(fn ($attachment) => [
                                            'id' => $attachment['id'],
                                            'type' => $attachment['attachment_type'],
                                            'url' => route('question-media.show', $attachment['id']),
                                            'filename' => $attachment['original_filename'],
                                            'mime_type' => $attachment['mime_type'],
                                        ])->all();
                                ?>
                                <?php if (isset($component)) { $__componentOriginal09cfcc751b2da77571e71fe2236df1ac = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal09cfcc751b2da77571e71fe2236df1ac = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.question-media','data' => ['items' => $mediaItems]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('question-media'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($mediaItems)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal09cfcc751b2da77571e71fe2236df1ac)): ?>
<?php $attributes = $__attributesOriginal09cfcc751b2da77571e71fe2236df1ac; ?>
<?php unset($__attributesOriginal09cfcc751b2da77571e71fe2236df1ac); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal09cfcc751b2da77571e71fe2236df1ac)): ?>
<?php $component = $__componentOriginal09cfcc751b2da77571e71fe2236df1ac; ?>
<?php unset($__componentOriginal09cfcc751b2da77571e71fe2236df1ac); ?>
<?php endif; ?>

                                <div class="row">
                                    <div class="mb-3 col-md-3">
                                        <label class="form-label">Section:</label>
                                        <input type="text" class="form-control" wire:model="questions.<?php echo e($index); ?>.exam_section" placeholder="Section">
                                    </div>
                                    <div class="mb-3 col-md-3">
                                        <label class="form-label">Marks:</label>
                                        <input type="number" class="form-control" wire:model="questions.<?php echo e($index); ?>.marks" placeholder="Marks">
                                    </div>
                                    <div class="mb-3 col-md-3">
                                        <label class="form-label">Type:</label>
                                        <select class="form-control" wire:model="questions.<?php echo e($index); ?>.type">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $questionTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($type); ?>"><?php echo e($label); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3 col-md-3">
                                        <label class="form-label">Difficulty:</label>
                                        <select class="form-control" wire:model="questions.<?php echo e($index); ?>.difficulty_level">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $difficultyLevels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $level => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($level); ?>"><?php echo e($label); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Explanation (Optional):</label>
                                    <textarea class="form-control" wire:model="questions.<?php echo e($index); ?>.explanation" placeholder="Explanation for the correct answer" rows="2"></textarea>
                                </div>

                                <!-- Options Section (for MCQ) -->
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!isset($question['type']) || $question['type'] === 'MCQ'): ?>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Answer Options:</label>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $question['options']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $optIndex => $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="mb-2 input-group">
                                                <div class="input-group-text">
                                                    <input type="checkbox" 
                                                        wire:model.live="questions.<?php echo e($index); ?>.options.<?php echo e($optIndex); ?>.is_correct" 
                                                        <?php echo e(isset($option['is_correct']) && $option['is_correct'] ? 'checked' : ''); ?>

                                                        aria-label="Correct option checkbox">
                                                </div>
                                                <input type="text" class="form-control" 
                                                    wire:model="questions.<?php echo e($index); ?>.options.<?php echo e($optIndex); ?>.option_text" 
                                                    placeholder="Option <?php echo e($optIndex + 1); ?>">
                                                <button class="btn btn-outline-danger" 
                                                    wire:click.prevent="removeOption(<?php echo e($index); ?>, <?php echo e($optIndex); ?>)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <button class="btn btn-sm btn-outline-secondary" 
                                            wire:click.prevent="addOption(<?php echo e($index); ?>)">
                                            <i class="bi bi-plus-circle me-1"></i> Add Option
                                        </button>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                <div class="text-end mt-3">
                                    <button class="btn btn-sm btn-success me-2" 
                                        wire:click.prevent="saveQuestion(<?php echo e($index); ?>)">
                                        <i class="bi bi-save me-1"></i> Save Question
                                    </button>
                                    <button class="btn btn-sm btn-danger" 
                                        wire:click.prevent="deleteQuestion(<?php echo e($question['id'] ?? 0); ?>)"
                                        wire:confirm="Are you sure you want to delete this question?">
                                        <i class="bi bi-trash me-1"></i> Delete Question
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle fs-4 mb-3 d-block"></i>
                        <p class="mb-1">No questions in this set yet.</p>
                        <p>Click the "Add Question" button to create your first question.</p>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($questions) > 0): ?>
                    <div class="d-grid gap-2 col-md-6 mx-auto mt-4">
                        <button class="btn btn-success btn-lg" wire:click.prevent="saveQuestions">
                            <i class="bi bi-save me-2"></i> Save All Questions
                        </button>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    
    <!-- Duplicate Question Set Modal -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($duplicateSetId): ?>
        <div class="modal fade show d-block" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Duplicate Question Set</h5>
                        <button type="button" class="btn-close" wire:click="$set('duplicateSetId', null)"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">New Question Set Name</label>
                            <input type="text" class="form-control" wire:model="newDuplicateSetName" placeholder="Enter new name">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['newDuplicateSetName'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <p class="text-muted small">
                            <i class="bi bi-info-circle"></i>
                            This will create a copy of the question set with all its questions and options.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('duplicateSetId', null)">Cancel</button>
                        <button type="button" class="btn btn-success" wire:click="confirmDuplicate">
                            <i class="bi bi-copy me-1"></i>Duplicate Question Set
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Copy Question Set Modal -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isCopyModalOpen): ?>
        <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title text-white">
                            <i class="bi bi-files me-2 text-white"></i>Copy Question Set to Another Course
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeCopyModal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- New Question Set Name -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">New Question Set Name *</label>
                            <input type="text" class="form-control" wire:model="copySetName" 
                                placeholder="Enter name for copied question set">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['copySetName'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                                <div class="text-danger small mt-1"><?php echo e($message); ?></div> 
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <!-- Filter Section -->
                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <h6 class="card-title mb-3">
                                    <i class="bi bi-funnel me-2"></i>Filter Target Course
                                </h6>
                                
                                <div class="row g-3">
                                    <!-- Program Filter -->
                                    <div class="col-md-6">
                                        <label class="form-label">Program</label>
                                        <select class="form-select" wire:model.live="copyFilterProgram">
                                            <option value="">All Programs</option>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $availablePrograms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $program): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($program['id']); ?>"><?php echo e($program['name']); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </select>
                                    </div>

                                    <!-- Year Filter -->
                                    <div class="col-md-6">
                                        <label class="form-label">Year</label>
                                        <select class="form-select" wire:model.live="copyFilterYear">
                                            <option value="">All Years</option>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $availableYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($year['id']); ?>"><?php echo e($year['name']); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </select>
                                    </div>

                                    <!-- Semester Filter -->
                                    <div class="col-md-6">
                                        <label class="form-label">Semester</label>
                                        <select class="form-select" wire:model.live="copyFilterSemester">
                                            <option value="">All Semesters</option>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $availableSemesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $semester): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($semester['id']); ?>"><?php echo e($semester['name']); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </select>
                                    </div>

                                    <!-- Academic Year Filter -->
                                    <div class="col-md-6">
                                        <label class="form-label">Academic Year</label>
                                        <select class="form-select" wire:model.live="copyFilterAcademicYear">
                                            <option value="">All Academic Years</option>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $availableAcademicYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $academicYear): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($academicYear['id']); ?>"><?php echo e($academicYear['name']); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Target Course Selection -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Target Course *</label>
                            <select class="form-select" wire:model="copyTargetCourseId">
                                <option value="">-- Select Course --</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $filteredCourses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($course['id']); ?>">
                                        <?php echo e($course['course_code']); ?> - <?php echo e($course['name']); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['copyTargetCourseId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> 
                                <div class="text-danger small mt-1"><?php echo e($message); ?></div> 
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($filteredCourses) && ($copyFilterProgram || $copyFilterYear || $copyFilterSemester)): ?>
                                <div class="alert alert-warning small mt-2 mb-0">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    No courses found matching the selected filters. Try adjusting your filters.
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <!-- Info Message -->
                        <div class="alert alert-info small mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            This will create a complete copy of the question set including all questions, options, and settings.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeCopyModal">
                            <i class="bi bi-x-circle me-1"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="copyQuestionSet"
                            wire:loading.attr="disabled" wire:target="copyQuestionSet">
                            <span wire:loading.remove wire:target="copyQuestionSet">
                                <i class="bi bi-files me-1"></i>Copy Question Set
                            </span>
                            <span wire:loading wire:target="copyQuestionSet">
                                <span class="spinner-border spinner-border-sm me-1"></span>
                                Copying...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/livewire/question-bank-enhanced.blade.php ENDPATH**/ ?>