<div>
    <!-- Success Message -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('message')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e(session('message')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    
    <!-- Error Message -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Exam Header -->
    <div class="card mb-5 mb-xl-10">
        <div class="card-header border-0 d-flex justify-content-between align-items-center">
            <div class="card-title m-0">
                <h3 class="fw-bold m-0"><?php echo e($exam->display_title); ?></h3>
                <span class="badge badge-light-<?php echo e(match($exam->status) {
                    'upcoming' => 'primary',
                    'active' => 'success',
                    'completed' => 'info',
                    default => 'secondary'
                }); ?> mt-2"><?php echo e(ucfirst($exam->status)); ?></span>
            </div>
            <div class="d-flex gap-2">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Auth::user()->id === $exam->user_id || 
                    (method_exists(Auth::user(), 'hasRole') && Auth::user()->hasRole(['admin', 'Super Admin', 'System', 'Administrator'])) ||
                    Auth::user()->hasAnyRole(['System', 'admin', 'Super Admin', 'Administrator'])): ?>
                    <button class="btn btn-sm btn-primary" wire:click="startEditing">
                        <i class="ki-duotone ki-pencil fs-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Edit Exam
                    </button>
                    <button class="btn btn-sm btn-danger" wire:click="confirmDelete">
                        <i class="ki-duotone ki-trash fs-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                            <span class="path4"></span>
                            <span class="path5"></span>
                        </i>
                        Delete
                    </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
        <div class="card-body border-top p-9">
            <div class="row mb-7">
                <div class="col-lg-6">
                    <h5 class="mb-4">Basic Information</h5>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($exam->title): ?>
                    <div class="row mb-3">
                        <label class="col-lg-4 fw-semibold text-muted">Exam Title</label>
                        <div class="col-lg-8">
                            <span class="fw-bold fs-6 text-gray-800"><?php echo e($exam->title); ?></span>
                        </div>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div class="row mb-3">
                        <label class="col-lg-4 fw-semibold text-muted">Course</label>
                        <div class="col-lg-8">
                            <span class="fw-bold fs-6 text-gray-800">
                                <?php echo e($exam->course ? $exam->course->name : 'N/A'); ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($exam->course && $exam->course->course_code): ?>
                                    (<?php echo e($exam->course->course_code); ?>)
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-4 fw-semibold text-muted">Duration</label>
                        <div class="col-lg-8">
                            <span class="fw-bold fs-6 text-gray-800"><?php echo e($exam->duration); ?> minutes</span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-4 fw-semibold text-muted">Questions Per Session</label>
                        <div class="col-lg-8">
                            <span class="fw-bold fs-6 text-gray-800"><?php echo e($exam->questions_per_session); ?></span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-4 fw-semibold text-muted">Total Questions</label>
                        <div class="col-lg-8">
                            <span class="fw-bold fs-6 text-gray-800"><?php echo e($totalQuestions); ?></span>
                        </div>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($exam->passing_percentage): ?>
                        <div class="row mb-3">
                            <label class="col-lg-4 fw-semibold text-muted">Passing Percentage</label>
                            <div class="col-lg-8">
                                <span class="fw-bold fs-6 text-gray-800"><?php echo e($exam->passing_percentage); ?>%</span>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if((method_exists(Auth::user(), 'hasRole') && Auth::user()->hasRole(['admin', 'Super Admin', 'System', 'Administrator'])) ||
                        Auth::user()->hasAnyRole(['System', 'admin', 'Super Admin', 'Administrator'])): ?>
                        <div class="row mb-3">
                            <label class="col-lg-4 fw-semibold text-muted">Exam Password</label>
                            <div class="col-lg-8">
                                <span class="badge badge-light-success fs-6"><?php echo e($exam->password); ?></span>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="col-lg-6">
                    <h5 class="mb-4">Schedule & Settings</h5>
                    <div class="row mb-3">
                        <label class="col-lg-4 fw-semibold text-muted">Start Date</label>
                        <div class="col-lg-8">
                            <span class="fw-bold fs-6 text-gray-800">
                                <?php echo e($exam->start_date?->format('M d, Y h:i A') ?? 'Not set'); ?>

                            </span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-4 fw-semibold text-muted">End Date</label>
                        <div class="col-lg-8">
                            <span class="fw-bold fs-6 text-gray-800">
                                <?php echo e($exam->end_date?->format('M d, Y h:i A') ?? 'Not set'); ?>

                            </span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-4 fw-semibold text-muted">Program</label>
                        <div class="col-lg-8">
                            <span class="fw-bold fs-6 text-gray-800">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($exam->course && $exam->course->collegeClass && $exam->course->year && $exam->course->semester): ?>
                                    <?php echo e($exam->course->collegeClass->name); ?> - <?php echo e($exam->course->year->name); ?> (<?php echo e($exam->course->semester->name); ?>)
                                <?php else: ?>
                                    No details available
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-4 fw-semibold text-muted">Created By</label>
                        <div class="col-lg-8">
                            <span class="fw-bold fs-6 text-gray-800"><?php echo e($exam->user ? $exam->user->name : 'Unknown'); ?></span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-lg-4 fw-semibold text-muted">Created On</label>
                        <div class="col-lg-8">
                            <span class="fw-bold fs-6 text-gray-800"><?php echo e($exam->created_at->format('M d, Y h:i A')); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="separator separator-dashed my-6"></div>
            <div class="d-flex gap-3">
                <a href="<?php echo e(route('questionbank.with.slug', $exam->slug ? $exam->slug : $exam->id)); ?>" class="btn btn-light-primary">
                    <i class="ki-duotone ki-notepad-edit fs-3">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    Manage Question Bank
                </a>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if((method_exists(Auth::user(), 'hasRole') && Auth::user()->hasRole(['admin', 'Super Admin', 'System', 'Administrator'])) ||
                    Auth::user()->hasAnyRole(['System', 'admin', 'Super Admin', 'Administrator'])): ?>
                    <a href="<?php echo e(route('exams.edit', $exam->slug ? $exam->slug : $exam->id)); ?>" class="btn btn-light-info">
                        <i class="ki-duotone ki-pencil fs-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Edit Details
                    </a>
                    
                    <a href="<?php echo e(route('exams.preview', $exam)); ?>" class="btn btn-light-primary">
                        <i class="ki-duotone ki-eye fs-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        Preview Exam
                    </a>
                    
                    <a href="<?php echo e(route('exams.results', ['exam_id' => $exam->id])); ?>" class="btn btn-light-success">
                        <i class="ki-duotone ki-chart fs-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        View Results
                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>

 
    
    <!-- Question Sets Section -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($exam->questionSets && $exam->questionSets->count() > 0): ?>
        <div class="card mb-5 mb-xl-10">
            <div class="card-header border-0">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold fs-3 mb-1">Question Sets</span>
                    <span class="text-muted mt-1 fw-semibold fs-7"><?php echo e($exam->questionSets->count()); ?> set(s) assigned</span>
                </h3>
            </div>
            <div class="card-body py-3">
                <div class="table-responsive">
                    <table class="table align-middle gs-0 gy-4">
                        <thead>
                            <tr class="fw-bold text-muted bg-light">
                                <th class="ps-4 min-w-200px rounded-start">Question Set</th>
                                <th class="min-w-100px">Total Questions</th>
                                <th class="min-w-100px">Questions to Pick</th>
                                <th class="min-w-100px">Shuffle</th>
                                <th class="min-w-100px rounded-end">Difficulty</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $exam->questionSets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $set): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="d-flex justify-content-start flex-column">
                                                <span class="text-dark fw-bold fs-6"><?php echo e($set->name); ?></span>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($set->description): ?>
                                                    <span class="text-muted fw-semibold text-muted d-block fs-7">
                                                        <?php echo e(Str::limit($set->description, 50)); ?>

                                                    </span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-dark fw-bold d-block fs-6"><?php echo e($set->questions->count()); ?></span>
                                    </td>
                                    <td>
                                        <span class="text-dark fw-bold d-block fs-6">
                                            <?php echo e($set->pivot->questions_to_pick > 0 ? $set->pivot->questions_to_pick : 'All'); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($set->pivot->shuffle_questions): ?>
                                            <span class="badge badge-light-success">Yes</span>
                                        <?php else: ?>
                                            <span class="badge badge-light-secondary">No</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-light-<?php echo e(match($set->difficulty_level) {
                                            'easy' => 'success',
                                            'hard' => 'danger',
                                            default => 'warning',
                                        }); ?>">
                                            <?php echo e(ucfirst($set->difficulty_level)); ?>

                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
   <!-- Active Participants Section-->
    
        <div class="mb-5 mb-xl-10">
            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('active-exam-sessions', ['exam' => $exam, 'expectedParticipants' => $expectedParticipants ?? 0]);

$__key = 'active-sessions-'.$exam->id;

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-2017674404-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key);

echo $__html;

unset($__html);
unset($__key);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
        </div>
    <!-- Edit Modal -->
    <div class="modal fade" id="editExamModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Exam</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" wire:click="cancelEditing"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="updateExam">
                        <div class="mb-3">
                            <label for="examTitle" class="form-label">Examination Title</label>
                            <input type="text" class="form-control <?php $__errorArgs = ['examTitle'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="examTitle"
                                wire:model="examTitle" maxlength="255" placeholder="e.g. Quiz 1, Mid-Semester Examination">
                            <div class="form-text">This title is shown across the Exam Center; the course remains separate.</div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['examTitle'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="examDuration" class="form-label required">Duration (minutes)</label>
                                <input type="number" class="form-control <?php $__errorArgs = ['examDuration'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="examDuration" wire:model="examDuration">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['examDuration'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="questionsPerSession" class="form-label required">Questions Per Session</label>
                                <input type="number" class="form-control <?php $__errorArgs = ['questionsPerSession'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="questionsPerSession" wire:model="questionsPerSession">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['questionsPerSession'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="startDate" class="form-label">Start Date & Time</label>
                                <input type="datetime-local" class="form-control <?php $__errorArgs = ['startDate'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="startDate" wire:model="startDate">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['startDate'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="endDate" class="form-label">End Date & Time</label>
                                <input type="datetime-local" class="form-control <?php $__errorArgs = ['endDate'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="endDate" wire:model="endDate">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['endDate'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="passingPercentage" class="form-label">Passing Percentage</label>
                                <input type="number" step="0.01" class="form-control <?php $__errorArgs = ['passingPercentage'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="passingPercentage" wire:model="passingPercentage">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['passingPercentage'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label required">Status</label>
                                <select class="form-select <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="status" wire:model="status">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $statusOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </select>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>

                        <div class="modal-footer px-0 pb-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal" wire:click="cancelEditing">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <span class="indicator-label">Save Changes</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" wire:click="cancelDelete"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this exam? This action cannot be undone.</p>
                    <div class="alert alert-warning">
                        <i class="ki-duotone ki-information-5 fs-2x text-warning me-4">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        All questions, responses, and results associated with this exam will be permanently deleted.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal" wire:click="cancelDelete">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="deleteExam">Delete Exam</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    document.addEventListener('livewire:init', function () {
        let editModal;
        let deleteModal;

        Livewire.on('startEditing', () => {
            if (!editModal) {
                editModal = new bootstrap.Modal(document.getElementById('editExamModal'));
            }
            editModal.show();
        });

        Livewire.on('examUpdated', () => {
            if (editModal) {
                editModal.hide();
            }
        });

        Livewire.on('confirmDelete', () => {
            if (!deleteModal) {
                deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
            }
            deleteModal.show();
        });

        Livewire.on('examDeleted', () => {
            if (deleteModal) {
                deleteModal.hide();
            }
        });
    });
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/livewire/exam-detail.blade.php ENDPATH**/ ?>