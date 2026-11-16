<tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-50px me-5 ps-5">
                                    <span class="symbol-label bg-primary">
                                        <span class="text-white fs-6"><?php echo e($exam->course ? getFirstLetter($exam->course->name) : 'N/A'); ?></span>
                                    </span>
                                </div>
                                <div class="d-flex justify-content-start flex-column">
                                    <div class="d-flex align-items-center">
                                        <a href="<?php echo e(route('exams.show', $exam->slug ? $exam->slug : $exam->id)); ?>" class="mb-1 text-dark fw-bold text-hover-primary fs-6"><?php echo e($exam->display_title); ?></a>
                                        <span class="badge badge-light-primary ms-2"><?php echo e($exam->total_questions_count ?? 0); ?> questions</span>
                                    </div>
                                    <span class="text-muted fw-semibold d-block fs-7">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($exam->course && $exam->course->collegeClass && $exam->course->year && $exam->course->semester): ?>
                                            <?php echo e($exam->course->collegeClass->name . ' - ' . $exam->course->year->name . ' (' . $exam->course->semester->name . ')'); ?>

                                        <?php else: ?>
                                            No details available
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td>
                            
                            <span class="text-muted fw-semibold d-block fs-7"><?php echo e($exam->created_at); ?></span>
                            <span class="text-muted fw-semibold d-block fs-7">
                                <small>Created by: <?php echo e($exam->user ? $exam->user->name : 'Unknown'); ?></small>
                            </span>
                        </td>
                        <td>    
                            
                            <span class="text-muted fw-semibold d-block fs-7"><?php echo e($exam->duration); ?></span>
                        </td>
                        <td>
                            
                            <span class="text-muted fw-semibold d-block fs-7">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($exam->course && $exam->course->collegeClass && $exam->course->year && $exam->course->semester): ?>
                                    <?php echo e($exam->course->collegeClass->name . ' - ' . $exam->course->year->name . ' (' . $exam->course->semester->name . ')'); ?>

                                <?php else: ?>
                                    No details available
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </span>
                        </td>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Auth::user()->hasAnyRole(['System', 'admin', 'Super Admin'])): ?>
                        <td>
                            
                            <span class="text-muted fw-semibold d-block fs-7"><?php echo e($exam->password); ?></span>
                        </td>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <td>
                            <?php
                                $statusClass = [
                                    'upcoming' => 'badge-light-primary',
                                    'active' => 'badge-light-success',
                                    'completed' => 'badge-light-info',
                                    'cancelled' => 'badge-light-danger',
                                ][$exam->status] ?? 'badge-light-warning';
                            ?>
                            <span class="badge <?php echo e($statusClass); ?>"><?php echo e(ucfirst($exam->status)); ?></span>
                        </td>
                        <td class="px-3">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light btn-active-light-primary" type="button" id="dropdownMenuButton-<?php echo e($exam->id); ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                    Actions
                                    <i class="ki-duotone ki-down fs-5 ms-1"></i>
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton-<?php echo e($exam->id); ?>">
                                    <li>
                                        <a href="<?php echo e(route('exams.show', $exam->slug ? $exam->slug : $exam->id)); ?>" class="dropdown-item">
                                            <i class="ki-duotone ki-information-5 fs-6 me-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                            </i>View Details
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?php echo e(route('exams.preview', $exam)); ?>" class="dropdown-item">
                                            <i class="ki-duotone ki-eye fs-6 me-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                            </i>Preview Exam
                                        </a>
                                    </li>
                                    <li>
                                        <a href="<?php echo e(route('questionbank.with.slug', $exam->slug ? $exam->slug : $exam->id)); ?>" class="dropdown-item">
                                            <i class="ki-duotone ki-bank fs-6 me-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>Question Bank
                                        </a>
                                    </li>
                                    
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Auth::user()->hasAnyRole(['System', 'admin', 'Super Admin'])): ?>
                                        <li>
                                            <a href="<?php echo e(route('exams.edit', $exam->slug ? $exam->slug : $exam->id)); ?>" class="dropdown-item">
                                                <i class="ki-duotone ki-pencil fs-6 me-2">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>Edit
                                            </a>
                                        </li>
                                        <li>
                                            <a href="<?php echo e(route('exams.results', ['exam_id' => $exam->id])); ?>" class="dropdown-item">
                                                <i class="ki-duotone ki-document fs-6 me-2">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>Generate Results
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a href="javascript:void(0)" wire:click="$dispatch('confirmDelete', { examId: <?php echo e($exam->id); ?>, examName: '<?php echo e(addslashes($exam->display_title)); ?>' })" class="dropdown-item text-danger">
                                                <i class="ki-duotone ki-trash fs-6 me-2">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                    <span class="path4"></span>
                                                    <span class="path5"></span>
                                                </i>Delete
                                            </a>
                                        </li>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </ul>
                            </div>
                        </td>
                    </tr>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/components/partials/exam-table-row.blade.php ENDPATH**/ ?>