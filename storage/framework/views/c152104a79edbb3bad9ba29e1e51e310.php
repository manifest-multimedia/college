<div>
    <div class="card">
        <div class="card-header"><h3 class="card-title"><i class="ki-duotone ki-message-text-2 fs-2 me-2"></i>Callbly SMS Provider</h3></div>
        <div class="card-body">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?> <div class="alert alert-success"><?php echo e(session('success')); ?></div> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?> <div class="alert alert-danger"><?php echo e(session('error')); ?></div> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tokenUndecryptable): ?>
                <div class="alert alert-warning d-flex align-items-center mb-4">
                    <i class="fas fa-exclamation-triangle fs-3 me-3 text-warning"></i>
                    <div>
                        <strong>Action required:</strong> The Callbly API token stored in the database cannot be decrypted with the current application key (<code>APP_KEY</code>). This typically occurs after a deployment, key regeneration, or database restoration from another environment. Please re-enter the Callbly API token below and save, or set <code>CALLBLY_API_TOKEN</code> in your <code>.env</code> file.
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="alert alert-info d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>Callbly is the supported SMS provider. Credentials can be managed here in System Settings or via <code>CALLBLY_API_TOKEN</code> in the environment.</div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isConfiguredInEnv): ?>
                    <span class="badge bg-light-primary text-primary px-3 py-2"><i class="fas fa-server me-1"></i>Environment fallback active (.env)</span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <form wire:submit="save">
                <div class="row g-4">
                    <div class="col-md-7">
                        <label class="form-label required">
                            Callbly API token
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasApiToken && ! $tokenUndecryptable): ?>
                                <span class="badge bg-light-success text-success ms-2"><i class="fas fa-check-circle me-1 text-success"></i>Configured</span>
                            <?php elseif($tokenUndecryptable): ?>
                                <span class="badge bg-light-warning text-warning ms-2"><i class="fas fa-exclamation-triangle me-1 text-warning"></i>Re-entry needed</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </label>
                        <input type="password" wire:model="apiToken" class="form-control <?php $__errorArgs = ['apiToken'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="<?php echo e($tokenUndecryptable ? 'Re-enter Callbly API token to fix decryption' : ($hasApiToken ? 'Configured — enter a new value only to rotate it' : 'Paste the Callbly bearer token')); ?>">
                        <div class="form-text">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tokenUndecryptable): ?>
                                <span class="text-warning">The stored token cannot be read with the current application key. Re-enter the token from Callbly to restore sending.</span>
                            <?php elseif($hasApiToken): ?>
                                A token is already configured and verified. Enter a new token only if you want to rotate it.
                            <?php else: ?>
                                Paste your API token from Callbly (Settings &gt; API Tokens).
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['apiToken'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="col-md-5">
                        <div class="d-flex align-items-center justify-content-between mb-2"><label class="form-label required mb-0">Approved sender IDs</label><button type="button" wire:click="addSenderId" class="btn btn-sm btn-light-primary"><i class="fas fa-plus me-1"></i>Add sender ID</button></div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $senderIds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $senderId): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="input-group mb-2" wire:key="sender-id-<?php echo e($index); ?>">
                                <div class="input-group-text"><input class="form-check-input mt-0" type="radio" wire:model="defaultSenderId" value="<?php echo e($senderId); ?>" aria-label="Set as default sender ID"></div>
                                <input type="text" wire:model.live="senderIds.<?php echo e($index); ?>" maxlength="11" class="form-control <?php $__errorArgs = ['senderIds.'.$index];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="College360">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($senderIds) > 1): ?><button type="button" wire:click="removeSenderId(<?php echo e($index); ?>)" class="btn btn-light-danger" title="Remove sender ID"><i class="fas fa-trash"></i></button><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['senderIds.'.$index];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger fs-7 mb-2"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['defaultSenderId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger fs-7 mb-2"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div class="form-text">Select the radio button for the default sender ID. Each sender ID must be approved in Callbly and contain at most 11 characters.</div>
                    </div>
                </div>
                <div class="form-check form-switch mt-5"><input class="form-check-input" type="checkbox" id="callblyEnabled" wire:model="enabled"><label class="form-check-label" for="callblyEnabled">Enable Callbly SMS sending</label></div>
                <div class="d-flex gap-3 mt-5"><button class="btn btn-primary" type="submit" wire:loading.attr="disabled">Save Callbly Settings</button><button class="btn btn-light-primary" type="button" wire:click="refreshBalances" wire:loading.attr="disabled">Check SMS & Wallet Balances</button></div>
            </form>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($balance): ?>
                <div class="row g-4 mt-2"><div class="col-md-6"><div class="border rounded p-4"><div class="text-muted">SMS credits</div><div class="fs-2 fw-bold"><?php echo e($balance['sms_balance'] ?? '—'); ?></div></div></div><div class="col-md-6"><div class="border rounded p-4"><div class="text-muted">Wallet balance</div><div class="fs-2 fw-bold"><?php echo e($balance['formatted_wallet_balance'] ?? (($balance['currency'] ?? 'GHS').' '.($balance['wallet_balance'] ?? '—'))); ?></div></div></div></div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/livewire/settings/sms-provider-settings.blade.php ENDPATH**/ ?>