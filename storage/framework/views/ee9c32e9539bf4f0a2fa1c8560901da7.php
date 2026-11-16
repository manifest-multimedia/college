<div class="card shadow-sm mt-5 mb-5">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3 py-3">
        <h3 class="card-title fw-bold mb-0"><i class="fas fa-address-book me-2" aria-hidden="true"></i>Emergency Contacts</h3>
        <div class="card-toolbar m-0">
            <button type="button" class="btn btn-sm btn-light-primary" wire:click="addContact">
                <i class="fas fa-plus me-1" aria-hidden="true"></i> Add Contact
            </button>
        </div>
    </div>
    <div class="card-body">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('emergency_contact_status')): ?>
            <div class="alert alert-success" role="status"><?php echo e(session('emergency_contact_status')); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showForm): ?>
            <form wire:submit="saveContact" class="border rounded p-4 mb-5">
                <h4 class="mb-4"><?php echo e($editingContactId ? 'Edit Emergency Contact' : 'Add Emergency Contact'); ?></h4>
                <?php echo $__env->make('livewire.partials.emergency-contact-fields', ['prefix' => 'contact', 'inputId' => 'emergency-contact'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <div class="d-flex flex-wrap justify-content-end gap-2 mt-4">
                    <button type="button" class="btn btn-light" wire:click="cancel">Cancel</button>
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="saveContact">
                        <span wire:loading.remove wire:target="saveContact">Save Contact</span>
                        <span wire:loading wire:target="saveContact">Saving…</span>
                    </button>
                </div>
            </form>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <div class="row g-4">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $contacts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="col-md-6" wire:key="emergency-contact-<?php echo e($entry->id); ?>">
                    <div class="border rounded p-4 h-100">
                        <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
                            <div><h4 class="mb-1"><?php echo e($entry->full_name); ?></h4><span class="text-muted"><?php echo e($entry->relationship); ?></span></div>
                            <div class="d-flex gap-2 align-items-start">
                                <button type="button" class="btn btn-sm btn-light-primary" wire:click="editContact(<?php echo e($entry->id); ?>)" aria-label="Edit contact <?php echo e($entry->full_name); ?>">Edit</button>
                                <button type="button" class="btn btn-sm btn-light-danger" wire:click="deleteContact(<?php echo e($entry->id); ?>)" wire:confirm="Remove this emergency contact?" aria-label="Remove contact <?php echo e($entry->full_name); ?>">Remove</button>
                            </div>
                        </div>
                        <dl class="row mb-0">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = \App\Services\StudentEmergencyContactService::FIELDS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($field !== 'full_name' && $field !== 'relationship' && $entry->{$field}): ?>
                                    <dt class="col-sm-5 text-muted fw-normal"><?php echo e($label); ?></dt>
                                    <dd class="col-sm-7 text-break" style="white-space: pre-line"><?php echo e($entry->{$field}); ?></dd>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </dl>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="col-12 text-muted py-3">No emergency contacts added. You can add a contact whenever the information is available.</div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/livewire/student-emergency-contacts.blade.php ENDPATH**/ ?>