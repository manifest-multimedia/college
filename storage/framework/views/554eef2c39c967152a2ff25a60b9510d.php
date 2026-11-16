<div>
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <h3 class="card-title">
                    <i class="ki-duotone ki-message-text-2 fs-1 me-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>
                    Send SMS
                </h3>
            </div>
        </div>
        
        <div class="card-body">
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

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (\Illuminate\Support\Facades\Blade::check('hasanyrole', 'System|Super Admin')): ?>
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 border rounded p-4 mb-5 bg-light">
                    <div>
                        <span class="fw-bold">Callbly balances</span>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($balance && ($balance['success'] ?? false)): ?>
                            <span class="text-muted ms-3">SMS credits: <strong><?php echo e($balance['sms_balance'] ?? '—'); ?></strong></span>
                            <span class="text-muted ms-3">Wallet: <strong><?php echo e($balance['formatted_wallet_balance'] ?? (($balance['currency'] ?? 'GHS').' '.($balance['wallet_balance'] ?? '—'))); ?></strong></span>
                        <?php elseif($balance): ?>
                            <span class="text-danger ms-3"><?php echo e($balance['message'] ?? 'Unable to retrieve Callbly balances.'); ?></span>
                        <?php else: ?>
                            <span class="text-muted ms-3">Loading balances…</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <button type="button" class="btn btn-light-primary btn-sm" wire:click="refreshBalances" wire:loading.attr="disabled">Refresh balances</button>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <form wire:submit.prevent="sendSms">
                <div class="mb-5">
                    <label class="form-label fw-semibold">SMS Type</label>
                    <div class="d-flex flex-wrap gap-3">
                        <div class="form-check form-check-custom form-check-solid">
                            <input class="form-check-input" type="radio" value="single" id="single" wire:model.live="sendType">
                            <label class="form-check-label" for="single">
                                Single Recipient
                            </label>
                        </div>
                        <div class="form-check form-check-custom form-check-solid">
                            <input class="form-check-input" type="radio" value="bulk" id="bulk" wire:model.live="sendType">
                            <label class="form-check-label" for="bulk">
                                Multiple Recipients
                            </label>
                        </div>
                        <div class="form-check form-check-custom form-check-solid">
                            <input class="form-check-input" type="radio" value="group" id="group" wire:model.live="sendType">
                            <label class="form-check-label" for="group">
                                Recipient Group
                            </label>
                        </div>
                        <div class="form-check form-check-custom form-check-solid">
                            <input class="form-check-input" type="radio" value="audience" id="audience" wire:model.live="sendType">
                            <label class="form-check-label" for="audience">
                                Institution Audience
                            </label>
                        </div>
                    </div>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sendType === 'single'): ?>
                    <div class="mb-5">
                        <label class="form-label required">Recipient Phone Number</label>
                        <input type="text" class="form-control <?php $__errorArgs = ['recipient'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="+1234567890" wire:model.live.debounce.300ms="recipient">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['recipient'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php elseif($sendType === 'bulk'): ?>
                    <div class="mb-5">
                        <label class="form-label">Add Recipients</label>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" placeholder="+1234567890" wire:model="recipient">
                            <button class="btn btn-secondary" type="button" wire:click="addRecipient">Add</button>
                        </div>
                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($recipients) > 0): ?>
                            <div class="mt-3">
                                <h6>Recipients (<?php echo e(count($recipients)); ?>)</h6>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $recipients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $recip): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <span class="badge bg-light text-dark p-2">
                                            <?php echo e($recip); ?>

                                            <i class="ki-duotone ki-cross fs-7 ms-2" style="cursor: pointer" wire:click="removeRecipient(<?php echo e($index); ?>)">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php elseif($sendType === 'group'): ?>
                    <div class="mb-5">
                        <label class="form-label required">Select Recipient Group</label>
                        <select class="form-select <?php $__errorArgs = ['recipientListId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" wire:model.live="recipientListId">
                            <option value="">Select a group</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $recipientLists; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $list): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($list['id']); ?>">
                                    <?php echo e($list['name']); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($list['items_count'])): ?> (<?php echo e($list['items_count']); ?> <?php echo e(\Illuminate\Support\Str::plural('recipient', $list['items_count'])); ?>)<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['recipientListId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php elseif($sendType === 'audience'): ?>
                    <div class="mb-5">
                        <label class="form-label required">Audience</label>
                        <select class="form-select <?php $__errorArgs = ['audience'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" wire:model.live="audience">
                            <option value="all_students">All active students</option>
                            <option value="cohort">Students in a cohort</option>
                            <option value="program">Students in a program</option>
                            <option value="all_staff">All staff</option>
                        </select>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['audience'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($audience === 'cohort'): ?>
                        <div class="mb-5">
                            <label class="form-label required">Cohort</label>
                            <select class="form-select <?php $__errorArgs = ['audienceCohortId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" wire:model.live="audienceCohortId">
                                <option value="">Select a cohort</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $cohorts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cohort): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($cohort['id']); ?>"><?php echo e($cohort['name']); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['audienceCohortId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php elseif($audience === 'program'): ?>
                        <div class="mb-5">
                            <label class="form-label required">Program</label>
                            <select class="form-select <?php $__errorArgs = ['audienceProgramId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" wire:model.live="audienceProgramId">
                                <option value="">Select a program</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $programs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $program): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($program['id']); ?>"><?php echo e($program['name']); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </select>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['audienceProgramId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div class="border rounded bg-light p-4 mb-5">
                        <div class="fw-bold mb-2">Recipient summary: <?php echo e($audienceSummary['label'] ?? 'Selected audience'); ?></div>
                        <div class="row g-3 small">
                            <div class="col-sm-3"><span class="text-muted d-block">Records found</span><strong><?php echo e($audienceSummary['total_records'] ?? 0); ?></strong></div>
                            <div class="col-sm-3"><span class="text-muted d-block">Valid mobile numbers</span><strong class="text-success"><?php echo e($audienceSummary['valid_numbers'] ?? 0); ?></strong></div>
                            <div class="col-sm-3"><span class="text-muted d-block">No/invalid contact</span><strong class="text-warning"><?php echo e($audienceSummary['skipped_records'] ?? 0); ?></strong></div>
                            <div class="col-sm-3"><span class="text-muted d-block">Duplicate numbers</span><strong><?php echo e($audienceSummary['duplicate_numbers'] ?? 0); ?></strong></div>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="mb-5">
                    <label class="form-label required">Sender ID</label>
                    <select class="form-select <?php $__errorArgs = ['selectedSenderId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" wire:model="selectedSenderId">
                        <option value="">Select an approved sender ID</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $senderIds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $senderId): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($senderId); ?>"><?php echo e($senderId); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                    <div class="form-text">Only sender IDs approved in Callbly can be used.</div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['selectedSenderId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="invalid-feedback"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="mb-5" x-data="{
                    text: <?php if ((object) ('message') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('message'->value()); ?>')<?php echo e('message'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('message'); ?>')<?php endif; ?>.live,
                    get charCount() {
                        return (this.text || '').length;
                    },
                    get pages() {
                        return this.charCount > 0 ? Math.ceil(this.charCount / 160) : 1;
                    },
                    get remainingInPage() {
                        if (this.charCount === 0) return 160;
                        const remainder = this.charCount % 160;
                        return remainder === 0 ? 0 : (160 - remainder);
                    }
                }">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label required fw-semibold mb-0">Message</label>
                        <span class="badge" 
                              :class="pages > 1 ? 'badge-light-warning text-warning fw-bold' : 'badge-light-primary text-primary fw-semibold'"
                              x-text="pages + (pages === 1 ? ' SMS Page (1 credit / recipient)' : ' SMS Pages (' + pages + ' credits / recipient)')">
                            <?php echo e($this->pageCount); ?> <?php echo e($this->pageCount === 1 ? 'SMS Page (1 credit / recipient)' : 'SMS Pages ('.$this->pageCount.' credits / recipient)'); ?>

                        </span>
                    </div>

                    <textarea class="form-control <?php $__errorArgs = ['message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                              rows="5" 
                              wire:model.live.debounce.300ms="message" 
                              x-model="text" 
                              placeholder="Type your SMS message here..."></textarea>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger d-block mt-1"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div class="d-flex flex-wrap justify-content-between align-items-center mt-2 text-muted fs-7">
                        <div>
                            <span>160 characters per SMS page.</span>
                            <span class="text-muted ms-1">Longer messages are split into multiple pages and charged 1 credit per page.</span>
                        </div>
                        <div class="text-end">
                            <span class="fw-bold text-gray-800" x-text="charCount"><?php echo e(strlen($message)); ?></span> characters
                            <span class="text-gray-300 mx-1">•</span>
                            <span class="fw-semibold" x-text="remainingInPage"><?php echo e((strlen($message) % 160 === 0 && strlen($message) > 0) ? 0 : (160 - (strlen($message) % 160))); ?></span> chars left on current page
                        </div>
                    </div>

                    <div x-show="pages > 1" class="alert alert-info py-2 px-3 mt-3 d-flex align-items-center mb-0" style="display: none;">
                        <i class="ki-duotone ki-information-2 fs-2 me-2 text-info">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        <div class="fs-7 text-gray-700">
                            This message is <strong x-text="charCount"></strong> characters long (<strong x-text="pages"></strong> pages). Each recipient will consume <strong x-text="pages"></strong> SMS credits.
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div class="text-muted fs-7">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->estimatedRecipientsCount > 0): ?>
                            Estimated deduction: 
                            <strong class="text-gray-800"><?php echo e($this->estimatedRecipientsCount); ?> <?php echo e(\Illuminate\Support\Str::plural('recipient', $this->estimatedRecipientsCount)); ?></strong> 
                            &times; 
                            <strong class="text-gray-800"><?php echo e($this->pageCount); ?> <?php echo e(\Illuminate\Support\Str::plural('page', $this->pageCount)); ?></strong> 
                            = 
                            <span class="badge badge-light-success fw-bold fs-7"><?php echo e($this->estimatedCredits); ?> <?php echo e(\Illuminate\Support\Str::plural('credit', $this->estimatedCredits)); ?></span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading wire:target="sendSms" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Send SMS
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/livewire/communication/send-sms.blade.php ENDPATH**/ ?>