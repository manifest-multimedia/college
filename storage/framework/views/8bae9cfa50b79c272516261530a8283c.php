<div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('warning')): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <?php echo e(session('warning')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="row">
        <!-- Import Form -->
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3 py-3">
                    <h3 class="card-title fw-bold text-gray-800 mb-0">
                        <i class="fas fa-file-import me-2"></i>Import Students
                    </h3>
                    <div class="card-toolbar m-0">
                        <a href="<?php echo e(route('students.import.template')); ?>" class="btn btn-sm btn-light-primary">
                            <i class="fas fa-download me-1" aria-hidden="true"></i> Download Template
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form wire:submit="import">
                        <div class="mb-3">
                            <label for="file" class="form-label">Excel or CSV File <span class="text-danger">*</span></label>
                            <input type="file" class="form-control <?php $__errorArgs = ['file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="file" wire:model="file">
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
                            <small class="text-muted">Upload an Excel (.xlsx, .xls) or CSV file. After upload, map its actual headings to student fields.</small>
                        </div>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($file): ?>
                            <div class="mb-3">
                                <label for="headingRow" class="form-label">Heading Row</label>
                                <select id="headingRow" class="form-select" wire:model.live="headingRow">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($row = 1; $row <= 10; $row++): ?>
                                        <option value="<?php echo e($row); ?>">Row <?php echo e($row); ?></option>
                                    <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </select>
                                <small class="text-muted">Choose a later row only when the spreadsheet has a title or notes above its column headings.</small>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="programId" class="form-label">Program <span class="text-danger">*</span></label>
                                    <select id="programId" class="form-select <?php $__errorArgs = ['programId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" wire:model="programId">
                                        <option value="">Select Program</option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $programs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $program): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($program->id); ?>"><?php echo e($program->name); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </select>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['programId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <small class="text-muted">All imported students will be assigned to this program</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="cohortId" class="form-label">Cohort <span class="text-danger">*</span></label>
                                    <select id="cohortId" class="form-select <?php $__errorArgs = ['cohortId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" wire:model="cohortId">
                                        <option value="">Select Cohort</option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $cohorts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cohort): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($cohort->id); ?>"><?php echo e($cohort->name); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </select>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['cohortId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <small class="text-muted">All imported students will be assigned to this cohort</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="academicYearId" class="form-label">Academic Year</label>
                            <select id="academicYearId" class="form-select <?php $__errorArgs = ['academicYearId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" wire:model="academicYearId">
                                <option value="">Use Current Academic Year</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $academicYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $academicYear): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($academicYear->id); ?>"><?php echo e($academicYear->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['academicYearId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <small class="text-muted">Select the academic year for student ID generation. If not selected, the current academic year will be used.</small>
                        </div>
                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array('date_of_birth', $columnMapping, true)): ?>
                            <?php
                                $dateParser = new \App\Services\StudentImportDateParser;
                                $dateAnalysis = $dateFormatAnalysis ?: $dateParser->inspect([]);
                                $dateStatus = $dateFormatAnalysis === [] ? 'unavailable' : $dateParser->status($dateAnalysis);
                            ?>
                            <div class="mb-4">
                                <label for="dateFormat" class="form-label">Date of Birth Format</label>
                                <select id="dateFormat" class="form-select <?php $__errorArgs = ['dateFormat'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" wire:model.live="dateFormat">
                                    <option value="auto">Detect from the date column</option>
                                    <option value="ymd">Year / Month / Day (YYYY-MM-DD)</option>
                                    <option value="dmy">Day / Month / Year (DD/MM/YYYY)</option>
                                    <option value="mdy">Month / Day / Year (MM/DD/YYYY)</option>
                                </select>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['dateFormat'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <div class="alert <?php echo e(in_array($dateStatus, ['ambiguous', 'mixed'], true) ? 'alert-warning' : 'alert-info'); ?> mt-2 mb-2" role="status">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($dateStatus === 'unavailable'): ?>
                                        Date preview is unavailable. Confirm the mapping or re-upload the file; import will recheck the column before proceeding.
                                    <?php elseif($dateStatus === 'detected'): ?>
                                        Detected <strong><?php echo e($dateAnalysis['dmy'] > 0 ? 'day/month/year' : 'month/day/year'); ?></strong>
                                        from <?php echo e($dateAnalysis['dmy'] + $dateAnalysis['mdy']); ?> unambiguous numeric dates in the full column.
                                    <?php elseif($dateStatus === 'ambiguous'): ?>
                                        Both day-first and month-first interpretations are possible. Choose a format using the examples below; automatic import is blocked.
                                    <?php elseif($dateStatus === 'mixed'): ?>
                                        Conflicting day-first and month-first dates were found. Correct the column or choose one format consistently; incompatible rows will be reported as invalid.
                                    <?php elseif($dateStatus === 'empty'): ?>
                                        No dates supplied in the mapped column. Dates of birth will remain optional.
                                    <?php elseif($dateStatus === 'invalid'): ?>
                                        No valid dates were recognized in this column. Check the mapping and source values before importing.
                                    <?php else: ?>
                                        No day/month choice is needed for the recognized year-first, named-month or Excel dates.
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($dateFormat !== 'auto'): ?>
                                        <div class="mt-1"><strong>Your selection:</strong> <?php echo e(['ymd' => 'year/month/day', 'dmy' => 'day/month/year', 'mdy' => 'month/day/year'][$dateFormat]); ?>. <?php echo e($dateFormat === 'ymd' ? 'Year-first, named-month and Excel dates are accepted; day-first or month-first numeric text must use the matching option.' : 'Numeric dates will use this order consistently.'); ?></div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($dateAnalysis['invalid'] > 0): ?>
                                        <div class="mt-1"><?php echo e($dateAnalysis['invalid']); ?> invalid dates found. Review the row errors after import.</div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($dateAnalysis['examples']): ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered mb-1">
                                            <thead><tr><th>File value</th><th>Day-first meaning</th><th>Month-first meaning</th></tr></thead>
                                            <tbody>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $dateAnalysis['examples']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $example): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <tr>
                                                        <td><?php echo e($example['value']); ?></td>
                                                        <td><?php echo e($example['dmy'] ? (new \DateTimeImmutable($example['dmy']))->format('j M Y') : 'Invalid'); ?></td>
                                                        <td><?php echo e($example['mdy'] ? (new \DateTimeImmutable($example['mdy']))->format('j M Y') : 'Invalid'); ?></td>
                                                    </tr>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <small class="text-muted">The full mapped column is checked before import. Year-first dates such as 1994-12-27 and Excel date cells are supported automatically. Valid dates are saved as YYYY-MM-DD.</small>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <div class="alert alert-info mb-4">
                            <i class="fas fa-user-check me-2"></i>
                            <strong>Student portal accounts are created automatically.</strong>
                            Every imported student must have a unique, valid email address. Their initial password is their Student ID.
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-light" wire:click="resetForm">
                                <i class="fas fa-redo me-1"></i> Reset
                            </button>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="import" <?php if(empty($uploadedHeaders)): echo 'disabled'; endif; ?>>
                                <span wire:loading.remove wire:target="import">
                                    <i class="fas fa-file-import me-1"></i> Import Students
                                </span>
                                <span wire:loading wire:target="import">
                                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    Processing...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Column Mapping -->
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($file): ?>
            <div class="card shadow-sm mt-4">
                <div class="card-header">
                    <div class="card-title">
                        <h3 class="card-title fw-bold text-gray-800">
                            <i class="fas fa-exchange-alt me-2"></i>Column Mapping
                        </h3>
                    </div>
                </div>
                <div class="card-body">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($uploadedHeaders)): ?>
                        <div class="alert alert-light-warning mb-0">
                            <i class="fas fa-circle-notch fa-spin me-2" wire:loading wire:target="file,headingRow"></i>
                            We could not yet identify the headings. Choose the row containing your field names, or upload a file with headings.
                        </div>
                    <?php else: ?>
                    <p>The columns below are from the uploaded file. Suggested matches are selected automatically; confirm or adjust them before import.</p>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Spreadsheet Column</th>
                                    <th>Sample Value</th>
                                    <th>Database Field</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $uploadedHeaders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $header): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="fw-semibold"><?php echo e($header['label']); ?></td>
                                        <td class="text-muted text-truncate" style="max-width: 220px;"><?php echo e($header['sample'] ?: '—'); ?></td>
                                        <td>
                                            <select class="form-select form-select-sm" wire:model.live="columnMapping.<?php echo e($header['key']); ?>">
                                                <option value="">-- Not Mapped --</option>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $availableFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fieldKey => $fieldName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($fieldKey); ?>"><?php echo e($fieldName); ?></option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </select>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['columnMapping'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="text-danger small mt-2"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        
        <!-- Instructions and Results -->
        <div class="col-lg-5">
            <!-- Import Results -->
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($importResults): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header <?php echo e($importResults['skipped'] > 0 || $importResults['failed'] > 0 ? 'bg-warning text-dark' : 'bg-success text-white'); ?>">
                        <div class="card-title">
                            <h3 class="card-title fw-bold">
                                <i class="fas fa-check-circle me-2"></i>Import Results
                            </h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Total Records:</span>
                                <span class="fw-bold"><?php echo e($importResults['total']); ?></span>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($importResults['processed'])): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Successfully Processed:</span>
                                <span class="fw-bold text-info"><?php echo e($importResults['processed']); ?></span>
                            </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Successfully Created:</span>
                                <span class="fw-bold text-success"><?php echo e($importResults['created']); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Successfully Updated:</span>
                                <span class="fw-bold text-primary"><?php echo e($importResults['updated']); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Failed:</span>
                                <span class="fw-bold <?php echo e($importResults['failed'] > 0 ? 'text-danger' : 'text-muted'); ?>"><?php echo e($importResults['failed']); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Skipped:</span>
                                <span class="fw-bold <?php echo e($importResults['skipped'] > 0 ? 'text-warning' : 'text-muted'); ?>"><?php echo e($importResults['skipped']); ?></span>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($importResults['ids_generated']) && $importResults['ids_generated'] > 0): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Student IDs Generated:</span>
                                <span class="fw-bold text-info"><?php echo e($importResults['ids_generated']); ?></span>
                            </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($importResults['accounts_created'])): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Student Accounts Created:</span>
                                <span class="fw-bold text-success"><?php echo e($importResults['accounts_created']); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Existing Accounts Linked:</span>
                                <span class="fw-bold text-info"><?php echo e($importResults['accounts_linked'] ?? 0); ?></span>
                            </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($importResults['validation_errors']) && count($importResults['validation_errors']) > 0): ?>
                            <div class="mt-3">
                                <h6>Validation Errors:</h6>
                                <div class="alert alert-warning" style="max-height: 200px; overflow-y: auto;">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $importResults['validation_errors']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="mb-2">
                                            <strong>Row <?php echo e($error['row']); ?>:</strong>
                                            <ul class="mb-0">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $error['errors']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $err): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <li><?php echo e($err); ?></li>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </ul>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($importResults['emergency_contacts_imported'])): ?>
                            <div class="mt-3">Emergency contacts saved: <strong><?php echo e($importResults['emergency_contacts_imported']); ?></strong></div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($importResults['sync_output'])): ?>
                            <div class="mt-3">
                                <h6>User Account Sync Results:</h6>
                                <pre class="bg-light p-2" style="font-size: 0.8rem; max-height: 300px; overflow-y: auto;"><?php echo e($importResults['sync_output']); ?></pre>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <!-- Instructions -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <div class="card-title">
                        <h3 class="card-title fw-bold text-gray-800">
                            <i class="fas fa-info-circle me-2"></i>Instructions
                        </h3>
                    </div>
                </div>
                <div class="card-body">
                    <h5>File Format Requirements</h5>
                    <ul>
                        <li>Upload an Excel (.xlsx, .xls) or CSV file</li>
                        <li>The file may use its own column names; select the heading row and map the detected columns after upload</li>
                        <li>Every import requires First Name, Last Name, and Email because student portal accounts are created automatically</li>
                    </ul>
                    
                    <h5>Emergency Contacts (Optional)</h5>
                    <p>Map emergency contact columns when available. Each contact needs a full name, relationship, and mobile number. Alternate phone, email, GPS address, and residential address are optional.</p>
                    <p>The template includes two contacts. You can map up to five contacts per student, and add more from the student record. Re-importing updates the corresponding imported contacts. Blank or unmapped contact columns leave saved contacts unchanged.</p>
                    <h5>Expected Excel Columns</h5>
                    <ul>
                        <li><strong>Student ID</strong>: Unique identifier (optional — the system generates one when it is not supplied)</li>
                        <li><strong>First Name</strong>: Student's first name (required for ID generation)</li>
                        <li><strong>Last Name</strong>: Student's last name (required for ID generation)</li>
                        <li><strong>Other Name(s)</strong>: Middle names or other names</li>
                        <li><strong>Email</strong>: Required and must be unique for student account creation</li>
                        <li><strong>Mobile Number</strong>: Contact number</li>
                        <li><strong>Date of Birth</strong>: The format is detected from the full column. Choose day-first or month-first when ambiguous; YYYY-MM-DD and Excel date cells are also supported.</li>
                        <li>... and other demographic information</li>
                    </ul>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-lightbulb me-2"></i>
                        <strong>Automatic ID Generation:</strong> If Student ID is not mapped or is empty for a valid row, the system automatically generates one using the configured format based on the selected Program and Academic Year.
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Existing students will be updated if their Student ID or Email matches records in the database.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/livewire/student-import.blade.php ENDPATH**/ ?>