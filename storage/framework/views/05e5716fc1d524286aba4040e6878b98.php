<div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($questionSet): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-upload me-2"></i>Import/Export Questions - <?php echo e($questionSet->name); ?>

                </h5>
            </div>
            <div class="card-body">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('message')): ?>
                    <div class="alert alert-success">
                        <?php echo e(session('message')); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('error')): ?>
                    <div class="alert alert-danger">
                        <?php echo e(session('error')); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <!-- Export Section -->
                <div class="mb-4">
                    <h6><i class="bi bi-download me-2"></i>Export Questions</h6>
                    <p class="text-muted">Export all questions from this set as a CSV file.</p>
                    <button class="btn btn-success" wire:click="exportQuestions">
                        <i class="bi bi-file-earmark-spreadsheet me-1"></i>Export to CSV
                    </button>
                </div>

                <hr>

                <!-- Import Section -->
                <div>
                    <h6><i class="bi bi-upload me-2"></i>Import Questions</h6>
                    <p class="text-muted">
                        Import questions from a CSV file. The CSV should have the following columns:<br>
                        <code>Question Text, Option 1, Option 2, Option 3, Option 4, Correct Option Number, Marks, Difficulty, Explanation</code>
                    </p>

                    <div class="mb-3">
                        <label for="fileInput" class="form-label">Choose CSV File</label>
                        <input type="file" class="form-control" wire:model="file" accept=".csv,.txt" id="fileInput">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <button class="btn btn-primary" wire:click="importQuestions" 
                        <?php echo e(!$file ? 'disabled' : ''); ?>>
                        <i class="bi bi-upload me-1"></i>Import Questions
                    </button>

                    <!-- Loading indicator -->
                    <div wire:loading wire:target="importQuestions" class="mt-2">
                        <div class="d-flex align-items-center">
                            <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                            <span>Importing questions...</span>
                        </div>
                    </div>
                </div>

                <!-- Import Results -->
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($show_import_results && !empty($import_results)): ?>
                    <div class="mt-4">
                        <div class="card <?php echo e($import_results['errors'] > 0 ? 'border-warning' : 'border-success'); ?>">
                            <div class="card-header <?php echo e($import_results['errors'] > 0 ? 'bg-warning bg-opacity-25' : 'bg-success bg-opacity-25'); ?>">
                                <h6 class="mb-0">Import Results</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="text-center">
                                            <div class="h4 text-success"><?php echo e($import_results['success']); ?></div>
                                            <small class="text-muted">Successfully Imported</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="text-center">
                                            <div class="h4 text-danger"><?php echo e($import_results['errors']); ?></div>
                                            <small class="text-muted">Errors</small>
                                        </div>
                                    </div>
                                </div>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($import_results['details'])): ?>
                                    <hr>
                                    <h6>Details:</h6>
                                    <div class="small" style="max-height: 300px; overflow-y: auto;">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $import_results['details']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="mb-1 <?php echo e(str_contains($detail, 'successfully') ? 'text-success' : 'text-danger'); ?>">
                                                <?php echo e($detail); ?>

                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <!-- CSV Format Help -->
                <div class="mt-4">
                    <div class="card bg-light">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>CSV Format Guide</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-2"><strong>Required Columns (in order):</strong></p>
                            <ol class="small">
                                <li><strong>Question Text</strong> - The main question</li>
                                <li><strong>Option 1</strong> - First answer option</li>
                                <li><strong>Option 2</strong> - Second answer option</li>
                                <li><strong>Option 3</strong> - Third answer option (optional)</li>
                                <li><strong>Option 4</strong> - Fourth answer option (optional)</li>
                                <li><strong>Correct Option Number</strong> - Number (1-4) indicating which option is correct</li>
                                <li><strong>Marks</strong> - Points for this question (default: 1)</li>
                                <li><strong>Difficulty</strong> - easy, medium, or hard (default: medium)</li>
                                <li><strong>Explanation</strong> - Optional explanation for the answer</li>
                            </ol>
                            
                            <p class="mb-0 small text-muted">
                                <i class="bi bi-lightbulb me-1"></i>
                                <strong>Tip:</strong> You can export existing questions first to see the exact format expected.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">
            Question set not found.
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH /home/johnsonsebire/www/college/resources/views/livewire/question-import-export.blade.php ENDPATH**/ ?>