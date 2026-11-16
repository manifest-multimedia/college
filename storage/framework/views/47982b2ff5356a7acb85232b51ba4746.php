<div>
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

    <div class="d-flex flex-column flex-md-row justify-content-between mb-4">
        <div class="d-flex position-relative me-md-2 mb-3 mb-md-0" style="max-width: 300px;">
            <span class="position-absolute top-50 translate-middle-y ms-3">
                <i class="fas fa-search text-muted"></i>
            </span>
            <input type="text" class="form-control ps-8" wire:model.debounce.300ms="search" placeholder="Search roles...">
        </div>
        <div>
            <button type="button" class="btn btn-primary" wire:click="openModal">
                <i class="fas fa-plus-circle me-2"></i> Create Role
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-row-bordered table-hover">
            <thead class="table-light">
                <tr class="fw-bold fs-6 text-gray-800">
                    <th wire:click="sortBy('name')" style="cursor: pointer;" class="min-w-125px">
                        Role Name
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sortField === 'name'): ?>
                            <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?> ms-1"></i>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </th>
                    <th class="min-w-150px">Description</th>
                    <th class="min-w-150px">Permissions</th>
                    <th class="min-w-100px">Users Count</th>
                    <th class="text-end min-w-125px">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td class="align-middle fw-bold">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-circle symbol-40px overflow-hidden me-3">
                                    <div class="symbol-label <?php echo e(in_array($role->name, ['Super Admin', 'Administrator']) ? 'bg-light-danger text-danger' : 'bg-light-primary text-primary'); ?>">
                                        <?php echo e(strtoupper(substr($role->name, 0, 1))); ?>

                                    </div>
                                </div>
                                <div>
                                    <?php echo e($role->name); ?>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($role->name, ['Super Admin', 'Administrator', 'Student', 'Lecturer'])): ?>
                                        <span class="badge badge-light-warning ms-2">System Role</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="align-middle"><?php echo e($role->description ?? 'No description'); ?></td>
                        <td class="align-middle">
                            <span class="badge badge-light-success"><?php echo e($role->permissions->count()); ?> permissions</span>
                        </td>
                        <td class="align-middle"><?php echo e($role->users->count()); ?> users</td>
                        <td class="align-middle text-end">
                            <div class="d-inline-flex">
                                <button type="button" class="btn btn-sm btn-icon btn-light-info me-2" wire:click="viewRole(<?php echo e($role->id); ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-icon btn-light-primary me-2" wire:click="editRole(<?php echo e($role->id); ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-icon btn-light-danger" wire:click="confirmDelete(<?php echo e($role->id); ?>)"
                                    <?php echo e(in_array($role->name, ['Super Admin', 'Administrator', 'Student', 'Lecturer']) ? 'disabled' : ''); ?>>
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4">No roles found</td>
                    </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center mt-4">
        <?php echo e($roles->links()); ?>

    </div>

    <!-- Modal for Adding/Editing/Viewing Role -->
    <div class="modal fade" id="roleFormModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($viewMode): ?>
                            View Role Details
                        <?php elseif($editMode): ?>
                            Edit Role
                        <?php else: ?>
                            Create New Role
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="roleForm">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label fw-bold">Role Name</label>
                                    <input type="text" wire:model="name" id="name" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        <?php echo e($viewMode ? 'disabled' : ''); ?>

                                        <?php echo e($editMode && in_array($name, ['Super Admin', 'Administrator', 'Student', 'Lecturer']) ? 'disabled' : ''); ?>>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
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
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="description" class="form-label fw-bold">Description (Optional)</label>
                                    <input type="text" wire:model="description" id="description" class="form-control <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        <?php echo e($viewMode ? 'disabled' : ''); ?>>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['description'];
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

                        <div class="mb-4">
                            <label class="form-label fw-bold d-block">Permissions</label>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['selectedPermissions'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="text-danger mb-2"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            
                            <div class="accordion" id="permissionsAccordion">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $permissionGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $groupName => $permissions): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading<?php echo e(\Str::slug($groupName)); ?>">
                                            <button class="accordion-button <?php echo e($loop->first ? '' : 'collapsed'); ?>" type="button" data-bs-toggle="collapse" 
                                                data-bs-target="#collapse<?php echo e(\Str::slug($groupName)); ?>" aria-expanded="<?php echo e($loop->first ? 'true' : 'false'); ?>" 
                                                aria-controls="collapse<?php echo e(\Str::slug($groupName)); ?>">
                                                <div class="d-flex justify-content-between w-100 align-items-center">
                                                    <span><?php echo e($groupName); ?> Permissions</span>
                                                    <span class="badge bg-primary rounded-pill ms-2"><?php echo e(count($permissions)); ?></span>
                                                </div>
                                            </button>
                                        </h2>
                                        <div id="collapse<?php echo e(\Str::slug($groupName)); ?>" class="accordion-collapse collapse <?php echo e($loop->first ? 'show' : ''); ?>" 
                                            aria-labelledby="heading<?php echo e(\Str::slug($groupName)); ?>" data-bs-parent="#permissionsAccordion">
                                            <div class="accordion-body">
                                                <div class="row">
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <div class="col-lg-3 col-md-4 col-sm-6 mb-2">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" 
                                                                    wire:model="selectedPermissions" 
                                                                    value="<?php echo e($permission->id); ?>" 
                                                                    id="perm_<?php echo e($permission->id); ?>"
                                                                    <?php echo e($viewMode ? 'disabled' : ''); ?>>
                                                                <label class="form-check-label" for="perm_<?php echo e($permission->id); ?>">
                                                                    <?php echo e(ucfirst(str_replace(['-', '.', '_'], ' ', $permission->name))); ?>

                                                                </label>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?php echo e($viewMode ? 'Close' : 'Cancel'); ?></button>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$viewMode): ?>
                        <button type="button" class="btn btn-primary" wire:click="saveRole">
                            <span wire:loading.remove wire:target="saveRole">Save</span>
                            <span wire:loading wire:target="saveRole">Saving...</span>
                        </button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this role? This action cannot be undone.</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Roles with assigned users cannot be deleted.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="deleteRole" data-bs-dismiss="modal">
                        <span wire:loading.remove wire:target="deleteRole">Delete</span>
                        <span wire:loading wire:target="deleteRole">Deleting...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Direct initialization of modals
            const roleFormModalEl = document.getElementById('roleFormModal');
            const deleteConfirmationModalEl = document.getElementById('deleteConfirmationModal');
            
            // Initialize Bootstrap modals
            const roleFormModal = new bootstrap.Modal(roleFormModalEl);
            const deleteConfirmationModal = new bootstrap.Modal(deleteConfirmationModalEl);
            
            // Wait for Livewire to be fully initialized
            document.addEventListener('livewire:initialized', () => {
                // When role data is loaded, show the modal
                Livewire.on('roleDataLoaded', () => {
                    console.log('Role data loaded event received, showing modal');
                    setTimeout(() => roleFormModal.show(), 100);
                    
                    // Force input field values to match Livewire state
                    setTimeout(() => {
                        console.log('Setting form values from Livewire state');
                        console.log('Name:', window.Livewire.find('<?php echo e($_instance->getId()); ?>').name);
                        console.log('Description:', window.Livewire.find('<?php echo e($_instance->getId()); ?>').description);
                        console.log('Permissions:', window.Livewire.find('<?php echo e($_instance->getId()); ?>').selectedPermissions);
                        
                        if (document.getElementById('name')) {
                            document.getElementById('name').value = window.Livewire.find('<?php echo e($_instance->getId()); ?>').name || '';
                        }
                        
                        if (document.getElementById('description')) {
                            document.getElementById('description').value = window.Livewire.find('<?php echo e($_instance->getId()); ?>').description || '';
                        }
                        
                        // Check permission checkboxes
                        const permissionIds = window.Livewire.find('<?php echo e($_instance->getId()); ?>').selectedPermissions || [];
                        permissionIds.forEach(id => {
                            const checkbox = document.getElementById(`perm_${id}`);
                            if (checkbox) checkbox.checked = true;
                        });
                    }, 150);
                });
                
                // Modal state change events
                Livewire.on('modalStateChanged', (state) => {
                    if (!state.isOpen) {
                        roleFormModal.hide();
                    }
                });
                
                // Close modal event
                Livewire.on('closeModal', () => {
                    roleFormModal.hide();
                });
                
                // Show delete confirmation event
                Livewire.on('showDeleteConfirmation', () => {
                    deleteConfirmationModal.show();
                });
            });
            
            // Create role button (Add New)
            const createButton = document.querySelector('button[wire\\:click="openModal"]');
            if (createButton) {
                createButton.addEventListener('click', function() {
                    setTimeout(() => roleFormModal.show(), 250);
                });
            }
            
            // Fix for checkbox selection in permissions
            document.addEventListener('click', function(e) {
                if (e.target && e.target.matches('[wire\\:model="selectedPermissions"]')) {
                    e.stopPropagation();
                }
            }, true);
            
            // Close modal on ESC key or when clicking the backdrop
            roleFormModalEl.addEventListener('hidden.bs.modal', () => {
                Livewire.dispatch('closeModalAction');
            });
            
            // Direct data population when modal is shown
            roleFormModalEl.addEventListener('shown.bs.modal', () => {
                console.log('Role modal shown, checking if data needs to be loaded');
                console.log('Edit mode:', window.Livewire.find('<?php echo e($_instance->getId()); ?>').editMode, 'View mode:', window.Livewire.find('<?php echo e($_instance->getId()); ?>').viewMode);
                console.log('Role name:', window.Livewire.find('<?php echo e($_instance->getId()); ?>').name);
                console.log('Role description:', window.Livewire.find('<?php echo e($_instance->getId()); ?>').description);
                console.log('Selected permissions:', window.Livewire.find('<?php echo e($_instance->getId()); ?>').selectedPermissions);
                
                // Double check form elements are properly populated
                if (window.Livewire.find('<?php echo e($_instance->getId()); ?>').editMode || window.Livewire.find('<?php echo e($_instance->getId()); ?>').viewMode) {
                    // Name field
                    const nameInput = document.getElementById('name');
                    if (nameInput && (!nameInput.value || nameInput.value !== window.Livewire.find('<?php echo e($_instance->getId()); ?>').name)) {
                        nameInput.value = window.Livewire.find('<?php echo e($_instance->getId()); ?>').name || '';
                    }
                    
                    // Description field
                    const descInput = document.getElementById('description');
                    if (descInput && (!descInput.value || descInput.value !== window.Livewire.find('<?php echo e($_instance->getId()); ?>').description)) {
                        descInput.value = window.Livewire.find('<?php echo e($_instance->getId()); ?>').description || '';
                    }
                    
                    // Permission checkboxes
                    const permissionIds = window.Livewire.find('<?php echo e($_instance->getId()); ?>').selectedPermissions || [];
                    permissionIds.forEach(id => {
                        const checkbox = document.getElementById(`perm_${id}`);
                        if (checkbox && !checkbox.checked) {
                            checkbox.checked = true;
                        }
                    });
                    
                    // Manually trigger checkbox input changes to ensure Livewire knows about them
                    document.querySelectorAll('input[type="checkbox"][wire\\:model="selectedPermissions"]').forEach(checkbox => {
                        if (permissionIds.includes(parseInt(checkbox.value))) {
                            if (!checkbox.checked) {
                                checkbox.checked = true;
                                checkbox.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        }
                    });
                }
            });
        });
    </script>
    <?php $__env->stopPush(); ?>
</div><?php /**PATH /home/johnsonsebire/www/college/resources/views/livewire/settings/role-management.blade.php ENDPATH**/ ?>