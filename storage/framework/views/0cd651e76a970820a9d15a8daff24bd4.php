<div class="container mt-4" x-data="{ isConfirmModalOpen: false, questionId: null }">
    <!-- Exam Selection Dropdown -->
   

    <!-- Questions Section -->
    <div class="mb-4 card">
        <div class="card-header">
            <h3 class="card-title">Question Bank</h3>
        </div>
        <div class="card-body">


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

            <div class="mb-4">
                <label for="examSelect" class="form-label">Select Exam:</label>
                <select id="examSelect" class="form-select" wire:model="exam_id" wire:change="loadQuestions">
                    <option value="">Select Exam</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $exams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exam): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($exam->id); ?>"><?php echo e($exam->display_title); ?> — <?php echo e($exam->course->course_code ?? ''); ?> <?php echo e($exam->course->name ?? 'Unknown Course'); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
               <div class="p-4 mb-4 w-full rounded d-flex border bg-white shadow-sm">
                
                <div class="me-4 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(145deg, #f8f9fa, #e9ecef); border: 2px solid #dee2e6; flex-shrink: 0;">
                    <span class="fw-bold text-primary"><?php echo e($index + 1); ?></span>
                </div>
                <div class="flex-grow-1">
                <div class="p-4 rounded border bg-light">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="badge bg-primary px-3 py-2">Question <?php echo e($index + 1); ?></span>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-primary" wire:click.prevent="saveQuestion(<?php echo e($index); ?>)">
                                <i class="bi bi-save me-1"></i>Save
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="openConfirmModal(<?php echo e($question['id']); ?>)">
                                <i class="bi bi-trash me-1"></i>Delete
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="question<?php echo e($index); ?>" class="form-label fw-bold">Question Text:</label>
                        <textarea rows="4" id="question<?php echo e($index); ?>" class="form-control" wire:model="questions.<?php echo e($index); ?>.question_text" placeholder="Enter your question here..."></textarea>
                    </div>

                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($question['attachments'])): ?>
                        <div class="alert alert-info">
                            <strong>DEBUG:</strong> Attachments count: <?php echo e(count($question['attachments'])); ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($question['attachments']) > 0): ?>
                                <br>First attachment type: <?php echo e($question['attachments'][0]['attachment_type'] ?? 'N/A'); ?>

                                <br>First attachment path: <?php echo e($question['attachments'][0]['file_path'] ?? 'N/A'); ?>

                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <strong>DEBUG:</strong> No attachments key found in question array
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    
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
                        <div class="mb-3 col-md-6">
                            <label for="section<?php echo e($index); ?>" class="form-label">Section:</label>
                            <input type="text" id="section<?php echo e($index); ?>" class="form-control" wire:model="questions.<?php echo e($index); ?>.exam_section" placeholder="Section">
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="marks<?php echo e($index); ?>" class="form-label">Marks:</label>
                            <input type="number" id="marks<?php echo e($index); ?>" class="form-control" wire:model="questions.<?php echo e($index); ?>.marks" placeholder="Marks">
                        </div>
                    </div>

                    <!-- Options Section -->
                    <div class="mt-4">
                        <label class="form-label fw-bold mb-3">
                            <i class="bi bi-list-check me-1"></i>Answer Options:
                        </label>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $question['options']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $optIndex => $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="mb-3 input-group">
                                <span class="input-group-text bg-light">
                                    <?php echo e(chr(65 + $optIndex)); ?>.
                                </span>
                                <input type="text" class="form-control" wire:model="questions.<?php echo e($index); ?>.options.<?php echo e($optIndex); ?>.option_text" placeholder="Enter option <?php echo e(chr(65 + $optIndex)); ?>">
                                <div class="input-group-text bg-light">
                                    <div class="form-check mb-0">
                                        <input type="checkbox" class="form-check-input" wire:model="questions.<?php echo e($index); ?>.options.<?php echo e($optIndex); ?>.is_correct" <?php echo e($option['is_correct'] ? 'checked' : ''); ?>>
                                        <label class="form-check-label ms-1">Correct</label>
                                    </div>
                                </div>
                                <button class="btn btn-outline-danger" wire:click.prevent="removeOption(<?php echo e($index); ?>, <?php echo e($optIndex); ?>)">
                                    <i class="bi bi-trash me-1"></i>Remove
                                </button>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <button class="btn btn-outline-success" wire:click.prevent="addOption(<?php echo e($index); ?>)">
                            <i class="bi bi-plus-circle me-1"></i>Add Option
                        </button>
                    </div>

                    <!-- Question Actions -->
                    <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                        <button class="btn btn-outline-secondary" wire:click.prevent="resetQuestion(<?php echo e($index); ?>)">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                        </button>
                        <div class="btn-group">
                            <button class="btn btn-primary" wire:click.prevent="saveQuestion(<?php echo e($index); ?>)">
                                <i class="bi bi-save me-1"></i>Save Changes
                            </button>
                            <button class="btn btn-outline-danger" onclick="openConfirmModal(<?php echo e($question['id']); ?>)">
                                <i class="bi bi-trash me-1"></i>Delete
                            </button>
                        </div>
                    </div>
                </div>
                </div>


               </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($exam_id): ?>    
                <div class="container py-4 rounded border bg-light">
                    <p class="mt-3 text-center text-muted">
                        No questions available for the selected exam. Start by creating a new question, or importing questions.
                    </p>
                    
                    <?php if (isset($component)) { $__componentOriginal8fe601015699dc42fe1bfae621194141 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8fe601015699dc42fe1bfae621194141 = $attributes; } ?>
<?php $component = App\View\Components\BulkUpload::resolve(['examId' => $exam_id] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('bulk-upload'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\BulkUpload::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8fe601015699dc42fe1bfae621194141)): ?>
<?php $attributes = $__attributesOriginal8fe601015699dc42fe1bfae621194141; ?>
<?php unset($__attributesOriginal8fe601015699dc42fe1bfae621194141); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8fe601015699dc42fe1bfae621194141)): ?>
<?php $component = $__componentOriginal8fe601015699dc42fe1bfae621194141; ?>
<?php unset($__componentOriginal8fe601015699dc42fe1bfae621194141); ?>
<?php endif; ?>
                    <!-- Loading Spinner -->
                    <div wire:loading wire:target="importQuestions" class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Processing...</span>
                        </div>
                        <p class="mt-2 text-muted">Importing questions. Please wait...</p>
                    </div>
                </div>
            <?php else: ?>
            <p class="text-center text-muted">Please select an exam to view questions.</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                 <!-- Bulk Import and Save Buttons -->
   
    
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($questions)>0): ?>
    <div class="d-flex justify-content-center">

        <button class="btn btn-success" wire:click.prevent="saveQuestions">Save Questions</button>

    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <!-- Confirmation Modal -->
     <!-- Use backdrop: static -->
    <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this question?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete()">Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let questionIdToDelete = null;

    function openConfirmModal(id) {
        questionIdToDelete = id;
        var myModal = new bootstrap.Modal(document.getElementById('confirmModal'));
        myModal.show();
    }

    function confirmDelete() {
        if (questionIdToDelete !== null) {
            Livewire.emit('deleteQuestion', questionIdToDelete);
            questionIdToDelete = null;
            var myModal = bootstrap.Modal.getInstance(document.getElementById('confirmModal'));
            myModal.hide();
        }
    }
</script>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/livewire/question-bank.blade.php ENDPATH**/ ?>