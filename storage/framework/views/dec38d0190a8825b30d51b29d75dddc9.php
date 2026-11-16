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
                        <i class="bi bi-question-circle me-1"></i><?php echo e($questionsCount); ?> questions
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Question Form -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0 d-flex align-items-center justify-content-between">
                        <span>
                            <i class="bi bi-<?php echo e($isEditing ? 'pencil' : 'plus-circle'); ?> me-2"></i>
                            <?php echo e($isEditing ? 'Edit Question' : 'Create New Question'); ?>

                        </span>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isEditing && $hasResponses): ?>
                            <span class="badge bg-danger">
                                <i class="bi bi-lock me-1"></i>Has Responses - Cannot Edit
                            </span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isEditing && $hasResponses): ?>
                        <div class="alert alert-warning mb-4">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Warning:</strong> This question has been answered by students and cannot be edited. 
                            Any changes would invalidate existing student responses. Please create a new question instead.
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <form wire:submit="save">
                        <!-- Question Text -->
                        <div class="mb-4">
                            <label for="questionText" class="form-label">
                                Question Text <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control <?php $__errorArgs = ['questionText'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                      id="questionText" rows="4" wire:model="questionText" 
                                      placeholder="Enter the question text..."></textarea>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['questionText'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <small class="text-muted">Provide a clear and concise question</small>
                        </div>

                        <!-- Question Media -->
                        <div class="mb-4">
                            <label class="form-label">
                                Question Media <span class="text-muted">(Optional)</span>
                            </label>
                            <input type="file" wire:model="questionMedia" multiple accept=".jpg,.jpeg,.png,.webp,.mp4,.webm,.mp3,.wav,.ogg,.m4a"
                                   class="form-control <?php $__errorArgs = ['questionMedia.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['questionMedia'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <div wire:loading wire:target="questionMedia" class="text-muted">Uploading media…</div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['questionMedia.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback d-block"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <small class="text-muted">Upload images (JPG, PNG, WebP), video (MP4, WebM), or audio (MP3, WAV, OGG, M4A). Maximum 20 MB each, 10 files per upload.</small>

                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($questionMedia) && is_array($questionMedia) && count($questionMedia) > 0): ?>
                                <div class="d-flex flex-wrap gap-2 mt-3">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $questionMedia; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($image && is_object($image)): ?>
                                            <div class="position-relative">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array(strtolower($image->getClientOriginalExtension()), ['jpg', 'jpeg', 'png', 'webp', 'mp4', 'webm', 'mp3', 'wav', 'ogg', 'm4a'])): ?>
                                                <?php if (isset($component)) { $__componentOriginal09cfcc751b2da77571e71fe2236df1ac = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal09cfcc751b2da77571e71fe2236df1ac = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.question-media','data' => ['items' => [['type' => str_starts_with($image->getMimeType(), 'image/') ? 'image' : (str_starts_with($image->getMimeType(), 'video/') ? 'video' : 'audio'), 'url' => $image->temporaryUrl(), 'filename' => $image->getClientOriginalName(), 'mime_type' => $image->getMimeType()]]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('question-media'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['type' => str_starts_with($image->getMimeType(), 'image/') ? 'image' : (str_starts_with($image->getMimeType(), 'video/') ? 'video' : 'audio'), 'url' => $image->temporaryUrl(), 'filename' => $image->getClientOriginalName(), 'mime_type' => $image->getMimeType()]])]); ?>
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
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <button type="button" 
                                                        wire:click="removeNewMedia(<?php echo e($index); ?>)"
                                                        class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1"
                                                        style="padding: 0.15rem 0.4rem; line-height: 1;">
                                                    ×
                                                </button>
                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isEditing && !empty($existingMedia) && count($existingMedia) > 0): ?>
                                <div class="mt-3">
                                    <label class="form-label fw-semibold">Current Media:</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $existingMedia; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="position-relative">
                                                <?php if (isset($component)) { $__componentOriginal09cfcc751b2da77571e71fe2236df1ac = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal09cfcc751b2da77571e71fe2236df1ac = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.question-media','data' => ['items' => [$image]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('question-media'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([$image])]); ?>
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
                                                <button type="button" 
                                                        wire:click="removeExistingMedia(<?php echo e($image['id']); ?>)"
                                                        class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1"
                                                        style="padding: 0.15rem 0.4rem; line-height: 1;">
                                                    ×
                                                </button>
                                                <small class="d-block text-muted text-center mt-1" style="font-size: 11px;">
                                                    <?php echo e($image['size']); ?>

                                                </small>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <!-- Table Data (Simple JSON input for now) -->
                        <div class="mb-4">
                            <label class="form-label">
                                Table Data <span class="text-muted">(Optional)</span>
                            </label>
                            <textarea class="form-control <?php $__errorArgs = ['tableData'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                      wire:model="tableData" 
                                      rows="4" 
                                      placeholder='{"headers":["Column 1","Column 2"],"rows":[["Data 1","Data 2"]]}'></textarea>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['tableData'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <small class="text-muted">Enter table data in JSON format with headers and rows</small>
                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tableData || $existingTableData): ?>
                                <div class="mt-2">
                                    <button type="button" wire:click="clearTableData" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i> Clear Table
                                    </button>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($existingTableData && !$tableData): ?>
                                <div class="alert alert-info mt-2" role="alert">
                                    <small><i class="bi bi-info-circle"></i> This question has an existing table. Edit the JSON above to modify it.</small>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div class="row">
                            <!-- Question Type -->
                            <div class="col-md-4">
                                <div class="mb-4">
                                    <label for="questionType" class="form-label">
                                        Question Type <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select <?php $__errorArgs = ['questionType'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                            id="questionType" wire:model="questionType">
                                        <option value="multiple_choice">Multiple Choice</option>
                                        <option value="true_false">True/False</option>
                                        <option value="short_answer">Short Answer</option>
                                    </select>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['questionType'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>

                            <!-- Difficulty Level -->
                            <div class="col-md-4">
                                <div class="mb-4">
                                    <label for="difficultyLevel" class="form-label">
                                        Difficulty Level <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select <?php $__errorArgs = ['difficultyLevel'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                            id="difficultyLevel" wire:model="difficultyLevel">
                                        <option value="easy">Easy</option>
                                        <option value="medium">Medium</option>
                                        <option value="hard">Hard</option>
                                    </select>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['difficultyLevel'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>

                            <!-- Marks -->
                            <div class="col-md-4">
                                <div class="mb-4">
                                    <label for="marks" class="form-label">
                                        Marks <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" class="form-control <?php $__errorArgs = ['marks'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           id="marks" wire:model="marks" min="1" max="100">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['marks'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Options Section -->
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($questionType === 'multiple_choice'): ?>
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <label class="form-label">
                                        Answer Options <span class="text-danger">*</span>
                                    </label>
                                    <div>
                                        <button type="button" class="btn btn-outline-primary btn-sm" 
                                                wire:click="addOption" <?php if(count($options) >= 10): ?> disabled <?php endif; ?>>
                                            <i class="bi bi-plus me-1"></i>Add Option
                                        </button>
                                    </div>
                                </div>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['options'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="alert alert-danger"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                <div class="options-container">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="card mb-3">
                                            <div class="card-body">
                                                <div class="row align-items-center">
                                    <!-- Correct Answer Checkbox -->
                                    <div class="col-md-1">
                                        <div class="form-check">
                                            <input class="form-check-input <?php $__errorArgs = ['correctOptions'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                                   type="checkbox"
                                                   id="correct<?php echo e($index); ?>" 
                                                   wire:click="toggleCorrectOption(<?php echo e($index); ?>)"
                                                   <?php if(in_array($index, $correctOptions)): ?> checked <?php endif; ?>>
                                            <label class="form-check-label" for="correct<?php echo e($index); ?>">
                                                <span class="badge bg-secondary"><?php echo e(chr(65 + $index)); ?></span>
                                            </label>
                                        </div>
                                    </div>                                                    <!-- Option Text -->
                                                    <div class="col-md-10">
                                                        <input type="text" class="form-control <?php $__errorArgs = ['options.'.$index];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                                               wire:model="options.<?php echo e($index); ?>" 
                                                               placeholder="Enter option <?php echo e(chr(65 + $index)); ?>...">
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['options.'.$index];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </div>

                                                    <!-- Remove Button -->
                                                    <div class="col-md-1">
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($options) > 2): ?>
                                                            <button type="button" class="btn btn-outline-danger btn-sm" 
                                                                    wire:click="removeOption(<?php echo e($index); ?>)">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['correctOption'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="text-danger small"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Select one or more checkboxes to mark the correct answers. Minimum 2 options required.
                                </small>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <!-- Section/Category -->
                        <div class="mb-4">
                            <label for="examSection" class="form-label">Section/Category</label>
                            <input type="text" class="form-control <?php $__errorArgs = ['examSection'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                   id="examSection" wire:model="examSection" 
                                   placeholder="e.g., Chapter 1, Mathematics, etc.">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['examSection'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <small class="text-muted">Optional: Categorize the question for organization</small>
                        </div>

                        <!-- Explanation -->
                        <div class="mb-4">
                            <label for="explanation" class="form-label">Explanation</label>
                            <textarea class="form-control <?php $__errorArgs = ['explanation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                      id="explanation" rows="3" wire:model="explanation" 
                                      placeholder="Explain why this is the correct answer..."></textarea>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['explanation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <small class="text-muted">Optional: Provide an explanation for the correct answer</small>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <button type="button" class="btn btn-outline-secondary me-2" wire:click="resetForm">
                                    <i class="bi bi-arrow-clockwise me-1"></i>Reset
                                </button>
                                <a href="<?php echo e(route('question.sets.questions', $questionSetId)); ?>" class="btn btn-outline-danger">
                                    <i class="bi bi-x-lg me-1"></i>Cancel
                                </a>
                            </div>
                            <div class="d-flex gap-2">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$isEditing): ?>
                                    <button type="button" class="btn btn-outline-primary" 
                                            wire:click="addAnotherQuestion"
                                            wire:loading.attr="disabled" 
                                            wire:target="addAnotherQuestion,questionMedia">
                                        <span wire:loading.remove wire:target="addAnotherQuestion,questionMedia">
                                            <i class="bi bi-plus-lg me-1"></i>Add Another Question
                                        </span>
                                        <span wire:loading wire:target="addAnotherQuestion,questionMedia">
                                            <i class="bi bi-hourglass-split me-1"></i>Adding...
                                        </span>
                                    </button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <button type="submit" class="btn btn-primary" 
                                        wire:loading.attr="disabled" wire:target="save,createAllQuestions,questionMedia"
                                        <?php if($isEditing && $hasResponses): ?> disabled title="Cannot edit questions that have been answered" <?php endif; ?>>
                                    <span wire:loading.remove wire:target="save,createAllQuestions,questionMedia">
                                        <i class="bi bi-<?php echo e($isEditing ? 'check-lg' : 'plus-lg'); ?> me-1"></i>
                                        <?php echo e($isEditing ? 'Update Question' : 'Save All Questions'); ?>

                                    </span>
                                    <span wire:loading wire:target="save,createAllQuestions,questionMedia">
                                        <i class="bi bi-hourglass-split me-1"></i>
                                        <?php echo e($isEditing ? 'Updating...' : 'Saving...'); ?>

                                    </span>
                                </button>
                            </div>
                        </div>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$isEditing && count($questions) > 0): ?>
                            <div class="mt-4">
                                <h5>Added Questions</h5>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $questionData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="card mt-3">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <h6 class="card-title">Question <?php echo e($index + 1); ?></h6>
                                                <button type="button" class="btn btn-outline-danger btn-sm" 
                                                        wire:click="removeQuestion(<?php echo e($index); ?>)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                            <p><?php echo e($questionData['questionText']); ?></p>
                                            <div class="small text-muted">
                                                <span class="me-3">Type: <?php echo e(ucfirst($questionData['questionType'])); ?></span>
                                                <span class="me-3">Difficulty: <?php echo e(ucfirst($questionData['difficultyLevel'])); ?></span>
                                                <span>Marks: <?php echo e($questionData['marks']); ?></span>
                                            </div>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($questionData['questionType'] === 'multiple_choice'): ?>
                                                <div class="mt-2">
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $questionData['options']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $optIndex => $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty(trim($option))): ?>
                                                            <div class="form-check disabled">
                                                                <input class="form-check-input" type="checkbox" disabled 
                                                                       <?php if(in_array($optIndex, $questionData['correctOptions'])): ?> checked <?php endif; ?>>
                                                                <label class="form-check-label">
                                                                    <?php echo e(chr(65 + $optIndex)); ?>. <?php echo e($option); ?>

                                                                </label>
                                                            </div>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <!-- Question Guidelines -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-lightbulb me-2"></i>Question Guidelines
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="fw-bold">Writing Good Questions:</h6>
                        <ul class="small">
                            <li>Be clear and concise</li>
                            <li>Avoid ambiguous wording</li>
                            <li>Test specific knowledge</li>
                            <li>Use proper grammar</li>
                            <li>Avoid negative statements</li>
                        </ul>
                    </div>

                    <div class="mb-3">
                        <h6 class="fw-bold">Multiple Choice Tips:</h6>
                        <ul class="small">
                            <li>All options should be plausible</li>
                            <li>Keep options similar in length</li>
                            <li>Avoid "all of the above"</li>
                            <li>Only one option should be correct</li>
                            <li>Use 3-5 options typically</li>
                        </ul>
                    </div>

                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Difficulty Levels:</strong><br>
                        <strong>Easy:</strong> Basic recall<br>
                        <strong>Medium:</strong> Application<br>
                        <strong>Hard:</strong> Analysis & synthesis
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
                        <a href="<?php echo e(route('question.sets.import', $questionSetId)); ?>" class="btn btn-outline-primary">
                            <i class="bi bi-upload me-1"></i>Bulk Import Questions
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

            <!-- Preview -->
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($questionText)): ?>
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-eye me-2"></i>Preview
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="question-preview">
                            <h6><?php echo e($questionText); ?></h6>
                            
                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($questionMedia) && is_array($questionMedia) && count($questionMedia) > 0): ?>
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $questionMedia; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($image && is_object($image)): ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array(strtolower($image->getClientOriginalExtension()), ['jpg', 'jpeg', 'png', 'webp', 'mp4', 'webm', 'mp3', 'wav', 'ogg', 'm4a'])): ?>
                                                <?php if (isset($component)) { $__componentOriginal09cfcc751b2da77571e71fe2236df1ac = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal09cfcc751b2da77571e71fe2236df1ac = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.question-media','data' => ['items' => [['type' => str_starts_with($image->getMimeType(), 'image/') ? 'image' : (str_starts_with($image->getMimeType(), 'video/') ? 'video' : 'audio'), 'url' => $image->temporaryUrl(), 'filename' => $image->getClientOriginalName(), 'mime_type' => $image->getMimeType()]]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('question-media'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['type' => str_starts_with($image->getMimeType(), 'image/') ? 'image' : (str_starts_with($image->getMimeType(), 'video/') ? 'video' : 'audio'), 'url' => $image->temporaryUrl(), 'filename' => $image->getClientOriginalName(), 'mime_type' => $image->getMimeType()]])]); ?>
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
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($existingMedia) && count($existingMedia) > 0): ?>
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $existingMedia; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if (isset($component)) { $__componentOriginal09cfcc751b2da77571e71fe2236df1ac = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal09cfcc751b2da77571e71fe2236df1ac = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.question-media','data' => ['items' => [$image]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('question-media'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([$image])]); ?>
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
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($questionType === 'multiple_choice' && count(array_filter($options)) > 0): ?>
                                <div class="options-preview mt-3">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty(trim($option))): ?>
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="radio" 
                                                       name="preview" disabled 
                                                       <?php if(in_array($index, $correctOptions)): ?> checked <?php endif; ?>>
                                                <label class="form-check-label">
                                                    <strong><?php echo e(chr(65 + $index)); ?>.</strong> <?php echo e($option); ?>

                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($index, $correctOptions)): ?>
                                                        <span class="badge bg-success ms-2">Correct</span>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </label>
                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($explanation)): ?>
                                <div class="mt-3 p-2 bg-light rounded">
                                    <small><strong>Explanation:</strong> <?php echo e($explanation); ?></small>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <!-- Loading Overlay -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isSaving): ?>
    <div class="position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center" 
         style="background-color: rgba(0,0,0,0.5); z-index: 9999;">
        <div class="bg-white p-4 rounded shadow">
            <div class="text-center">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div>
                    <span><?php echo e($isEditing ? 'Updating' : 'Creating'); ?> question...</span>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH /home/johnsonsebire/www/college/resources/views/livewire/question-set-question-form.blade.php ENDPATH**/ ?>