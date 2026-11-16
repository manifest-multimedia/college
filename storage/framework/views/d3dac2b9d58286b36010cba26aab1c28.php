<div>
    <!-- Success/Error Messages -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i><?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i><?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$questionSet): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Error:</strong> Question set not found.
            <a href="<?php echo e(route('question.sets')); ?>" class="btn btn-primary btn-sm ms-2">
                <i class="bi bi-arrow-left me-1"></i>Back to Question Sets
            </a>
        </div>
    <?php else: ?>
        <!-- Question Set Info -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="bg-primary text-white rounded-circle p-2 me-3">
                        <i class="bi bi-collection fs-4"></i>
                    </div>
                    <div>
                        <h5 class="mb-1"><?php echo e($questionSet->name); ?></h5>
                        <p class="text-muted mb-0">
                            <i class="bi bi-book me-1"></i><?php echo e($questionSet->course->name ?? 'N/A'); ?> • 
                            <i class="bi bi-question-circle me-1"></i><?php echo e($questionsCount); ?> existing questions
                        </p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($questionSet): ?>
    <div class="row">
        <!-- Upload Form -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-cloud-upload me-2"></i>Upload Import File
                    </h5>
                </div>
                <div class="card-body">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$showPreview): ?>
                        <form wire:submit="preview">
                            <!-- File Upload -->
                            <div class="mb-4">
                                <label for="file" class="form-label">Select File <span class="text-danger">*</span></label>
                                <input type="file" class="form-control <?php $__errorArgs = ['file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="file" wire:model="file" accept=".xlsx,.xls,.csv,.txt">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <small class="text-muted">
                                    Supported formats: Excel (.xlsx, .xls), CSV (.csv), Aiken (.txt). Max size: 10MB
                                </small>
                            </div>

                            <!-- Format Selection -->
                            <div class="mb-4">
                                <label class="form-label">Import Format <span class="text-danger">*</span></label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="importFormat" 
                                                   id="formatExcel" value="excel" wire:model="importFormat">
                                            <label class="form-check-label" for="formatExcel">
                                                <i class="bi bi-file-earmark-spreadsheet text-success me-2"></i>
                                                <strong>Excel/CSV Format</strong>
                                                <small class="d-block text-muted">Structured spreadsheet with columns</small>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="importFormat" 
                                                   id="formatAiken" value="aiken" wire:model="importFormat">
                                            <label class="form-check-label" for="formatAiken">
                                                <i class="bi bi-file-text text-info me-2"></i>
                                                <strong>Aiken Format</strong>
                                                <small class="d-block text-muted">Plain text with structured format</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['importFormat'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="text-danger small"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-outline-secondary" wire:click="resetForm">
                                    <i class="bi bi-arrow-clockwise me-1"></i>Reset
                                </button>
                                <button type="submit" class="btn btn-primary" 
                                        wire:loading.attr="disabled" wire:target="preview">
                                    <span wire:loading.remove wire:target="preview">
                                        <i class="bi bi-eye me-1"></i>Preview Import
                                    </span>
                                    <span wire:loading wire:target="preview">
                                        <i class="bi bi-hourglass-split me-1"></i>Processing...
                                    </span>
                                </button>
                            </div>
                        </form>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <!-- Preview Section -->
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPreview): ?>
                        <div class="border rounded p-3 mb-4" style="background-color: #f8f9fa;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">
                                    <i class="bi bi-eye me-2"></i>Import Preview
                                    <span class="badge bg-info ms-2"><?php echo e(count($previewData)); ?> questions</span>
                                </h6>
                                <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="resetPreview">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>

                            <!-- Validation Errors -->
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($validationErrors)): ?>
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    <strong>Validation Issues Found:</strong>
                                    <ul class="mb-0 mt-2">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $validationErrors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <li><?php echo e($error); ?></li>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </ul>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <!-- Preview Table -->
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($previewData)): ?>
                                <div class="table-responsive" style="max-height: 400px;">
                                    <table class="table table-sm">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th>Line</th>
                                                <th>Question</th>
                                                <th>Options</th>
                                                <th>Correct</th>
                                                <th>Marks</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $previewData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <tr class="<?php echo e($question['is_valid'] ? '' : 'table-warning'); ?>">
                                                    <td class="fw-bold"><?php echo e($question['line']); ?></td>
                                                    <td>
                                                        <div class="text-truncate" style="max-width: 200px;" 
                                                             title="<?php echo e($question['question_text']); ?>">
                                                            <?php echo e($question['question_text']); ?>

                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $question['options']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <span class="badge <?php echo e($index === $question['correct_option'] ? 'bg-success' : 'bg-light text-dark'); ?> me-1">
                                                                <?php echo e($option['label'] ?? chr(65 + $index)); ?>: <?php echo e(Str::limit($option['text'], 20)); ?>

                                                            </span>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-success">
                                                            <?php echo e($question['options'][$question['correct_option']]['label'] ?? chr(65 + $question['correct_option'])); ?>

                                                        </span>
                                                    </td>
                                                    <td><?php echo e($question['marks']); ?></td>
                                                    <td>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($question['is_valid']): ?>
                                                            <i class="bi bi-check-circle text-success"></i>
                                                        <?php else: ?>
                                                            <i class="bi bi-exclamation-triangle text-warning"></i>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($previewData) === 20): ?>
                                    <div class="text-muted small mt-2">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Showing first 20 questions. All questions will be imported when confirmed.
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <!-- Import Actions -->
                            <div class="d-flex justify-content-between mt-3">
                                <button type="button" class="btn btn-outline-secondary" wire:click="resetPreview">
                                    <i class="bi bi-arrow-left me-1"></i>Back to Upload
                                </button>
                                
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($previewData) && empty($validationErrors)): ?>
                                    <button type="button" class="btn btn-success" 
                                            wire:click="import" wire:loading.attr="disabled" wire:target="import">
                                        <span wire:loading.remove wire:target="import">
                                            <i class="bi bi-check-lg me-1"></i>Confirm Import
                                        </span>
                                        <span wire:loading wire:target="import">
                                            <i class="bi bi-hourglass-split me-1"></i>Importing...
                                        </span>
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-success" disabled>
                                        <i class="bi bi-exclamation-triangle me-1"></i>Fix Errors First
                                    </button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <!-- Import Results -->
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($importResults): ?>
                        <div class="alert alert-success">
                            <h6 class="alert-heading">
                                <i class="bi bi-check-circle me-2"></i>Import Completed
                            </h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        <span><strong><?php echo e($importResults['imported']); ?></strong> questions imported</span>
                                    </div>
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($importResults['failed'] > 0): ?>
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-x-circle text-danger me-2"></i>
                                            <span><strong><?php echo e($importResults['failed']); ?></strong> questions failed</span>
                                        </div>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($importResults['errors'])): ?>
                                <hr>
                                <h6>Import Errors:</h6>
                                <ul class="mb-0">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $importResults['errors']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li><?php echo e($error); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </ul>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Import Guide -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-lightbulb me-2"></i>Quick Guide
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="fw-bold">Steps to Import:</h6>
                        <ol class="small">
                            <li>Select your file (Excel, CSV, or Aiken format)</li>
                            <li>Choose the appropriate format</li>
                            <li>Click "Preview Import" to validate</li>
                            <li>Review the preview for errors</li>
                            <li>Click "Confirm Import" to proceed</li>
                        </ol>
                    </div>

                    <div class="mb-3">
                        <h6 class="fw-bold">File Requirements:</h6>
                        <ul class="small">
                            <li>Maximum file size: 10MB</li>
                            <li>Questions must have at least 2 options</li>
                            <li>Correct answer must be specified</li>
                            <li>Question text cannot be empty</li>
                        </ul>
                    </div>

                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Need help?</strong> Check the format guide below for detailed examples and requirements.
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-lightning me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?php echo e(route('question.sets.questions.create', $questionSetId)); ?>" class="btn btn-outline-primary">
                            <i class="bi bi-plus-circle me-1"></i>Add Single Question
                        </a>
                        <a href="<?php echo e(route('question.sets.questions', $questionSetId)); ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-list me-1"></i>View All Questions
                        </a>
                        <a href="<?php echo e(route('question.sets.show', $questionSetId)); ?>" class="btn btn-outline-info">
                            <i class="bi bi-eye me-1"></i>Question Set Details
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div wire:loading wire:target="preview,import" class="position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center" 
         style="background-color: rgba(0,0,0,0.5); z-index: 9999;">
        <div class="bg-white p-4 rounded shadow">
            <div class="text-center">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div>
                    <span wire:loading wire:target="preview">Processing file...</span>
                    <span wire:loading wire:target="import">Importing questions...</span>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH /home/johnsonsebire/www/college/resources/views/livewire/question-set-bulk-import.blade.php ENDPATH**/ ?>