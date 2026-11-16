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
        <div class="d-flex flex-column flex-md-row gap-3 mb-3 mb-md-0">
            <div class="d-flex position-relative me-md-2">
                <span class="position-absolute top-50 translate-middle-y ms-3">
                    <i class="fas fa-search text-muted"></i>
                </span>
                <input type="text" class="form-control ps-8" wire:model.live="search" placeholder="Search users...">
            </div>
            <div>
                <select class="form-select" wire:model.live="roleFilter">
                    <option value="">All Roles</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($role->id); ?>"><?php echo e($role->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </select>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($selectedUsers) > 0 && auth()->user()->hasRole('System')): ?>
                <button type="button" class="btn btn-warning" wire:click="openStaffPasswordReset('selected')">
                    <i class="fas fa-key me-1"></i> Reset Selected (<?php echo e(count($selectedUsers)); ?>)
                </button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->hasRole('System')): ?>
                <div class="dropdown">
                    <button class="btn btn-light-warning border border-warning text-warning-dark" type="button" id="staffResetDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-key me-1"></i> Reset Passwords <i class="fas fa-chevron-down ms-1 fs-8"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-gray-200 fs-7" aria-labelledby="staffResetDropdown">
                        <li>
                            <a class="dropdown-item py-2 text-danger" href="#" wire:click.prevent="openStaffPasswordReset('all')">
                                <i class="fas fa-user-shield me-2 fs-7"></i>Reset All Staff Passwords
                            </a>
                        </li>
                    </ul>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <button type="button" class="btn btn-primary" wire:click="openModal">
                <i class="fas fa-plus-circle me-2"></i> Add User
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-row-bordered table-hover">
            <thead class="table-light">
                <tr class="fw-bold fs-6 text-gray-800">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (\Illuminate\Support\Facades\Blade::check('role', 'System')): ?>
                        <th style="width: 40px;" class="text-center align-middle">
                            <input type="checkbox" class="form-check-input" wire:model.live="selectAllUsers">
                        </th>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <th wire:click="sortBy('name')" style="cursor: pointer;" class="min-w-125px">
                        Name
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sortField === 'name'): ?>
                            <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?> ms-1"></i>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </th>
                    <th wire:click="sortBy('email')" style="cursor: pointer;" class="min-w-125px">
                        Email
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sortField === 'email'): ?>
                            <i class="fas fa-sort-<?php echo e($sortDirection === 'asc' ? 'up' : 'down'); ?> ms-1"></i>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </th>
                    <th class="min-w-125px">Phone</th>
                    <th class="min-w-150px">Role(s)</th>
                    <th class="min-w-150px">Direct Permissions</th>
                    <th class="text-end min-w-120px">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (\Illuminate\Support\Facades\Blade::check('role', 'System')): ?>
                            <td class="text-center align-middle">
                                <input type="checkbox" class="form-check-input" value="<?php echo e($user->id); ?>" wire:model.live="selectedUsers">
                            </td>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <td class="align-middle">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-circle symbol-40px overflow-hidden me-3">
                                    <div class="symbol-label bg-light-primary text-primary">
                                        <?php echo e(strtoupper(substr($user->name, 0, 1))); ?>

                                    </div>
                                </div>
                                <div>
                                    <span class="fw-bold d-block"><?php echo e($user->name); ?></span>
                                </div>
                            </div>
                        </td>
                        <td class="align-middle"><?php echo e($user->email); ?></td>
                        <td class="align-middle"><?php echo e($user->phone ?? 'N/A'); ?></td>
                        <td class="align-middle">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $user->roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($role->name !== 'System' || auth()->user()->hasRole('System')): ?>
                                    <span class="badge badge-light-primary"><?php echo e($role->name); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="align-middle">
                            <span class="badge badge-light-info"><?php echo e($user->permissions->count()); ?> direct permissions</span>
                        </td>
                        <td class="align-middle text-end">
                            <div class="d-inline-flex align-items-center gap-1">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (\Illuminate\Support\Facades\Blade::check('role', 'System')): ?>
                                    <button type="button" class="btn btn-sm btn-icon btn-light-warning" title="Reset Password" wire:click="openStaffPasswordReset('individual', <?php echo e($user->id); ?>)">
                                        <i class="fas fa-key"></i>
                                    </button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <button type="button" class="btn btn-sm btn-icon btn-light-primary" wire:click="editUser(<?php echo e($user->id); ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-icon btn-light-danger" wire:click="confirmDelete(<?php echo e($user->id); ?>)"
                                    <?php echo e($user->id === auth()->id() ? 'disabled' : ''); ?>>
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (\Illuminate\Support\Facades\Blade::check('role', 'System')): ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user->id !== auth()->id()): ?>
                                <form action="<?php echo e(route('impersonate.start', $user)); ?>" method="POST" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn btn-sm btn-icon btn-light-secondary" title="Impersonate User">
                                        <i class="fas fa-user-secret"></i>
                                    </button>
                                </form>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4">No users found</td>
                    </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center mt-4">
        <?php echo e($users->links()); ?>

    </div>

    <!-- Modal for Adding/Editing User -->
    <div class="modal fade <?php echo e($isOpen ? 'show d-block' : ''); ?>" id="userFormModal" tabindex="-1"
        aria-hidden="<?php echo e($isOpen ? 'false' : 'true'); ?>" aria-modal="<?php echo e($isOpen ? 'true' : 'false'); ?>"
        style="<?php echo e($isOpen ? 'background-color: rgba(0, 0, 0, 0.5);' : ''); ?>">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo e($editMode ? 'Edit User' : 'Add New User'); ?></h5>
                    <button type="button" class="btn-close" wire:click="closeModal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="userForm">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label fw-bold">Full Name</label>
                                    <input type="text" wire:model="name" id="name" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
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
                                    <label for="email" class="form-label fw-bold">Email Address</label>
                                    <input type="email" wire:model="email" id="email" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['email'];
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

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label fw-bold">Phone Number</label>
                                    <input type="text" wire:model="phone" id="phone" class="form-control <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['phone'];
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

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label fw-bold">
                                        Password <?php echo e($editMode ? '(leave blank to keep current)' : ''); ?>

                                    </label>
                                    <input type="password" wire:model="password" id="password" class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['password'];
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
                                    <label for="password_confirmation" class="form-label fw-bold">Confirm Password</label>
                                    <input type="password" wire:model="passwordConfirmation" id="password_confirmation" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold d-block">Roles</label>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['selectedRoles'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="text-danger mb-2"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <div class="row">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="col-md-3 col-sm-4 col-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" wire:model="selectedRoles" value="<?php echo e($role->id); ?>" id="role_<?php echo e($role->id); ?>">
                                            <label class="form-check-label" for="role_<?php echo e($role->id); ?>">
                                                <?php echo e($role->name); ?>

                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Direct Permissions -->
                        <div class="mb-4">
                            <label class="form-label fw-bold d-block">Direct Permissions (Optional)</label>
                            <p class="text-muted">These permissions will be assigned directly to the user, in addition to any permissions from their roles</p>
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
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                                data-bs-target="#collapse<?php echo e(\Str::slug($groupName)); ?>" aria-expanded="false" 
                                                aria-controls="collapse<?php echo e(\Str::slug($groupName)); ?>">
                                                <div class="d-flex justify-content-between w-100 align-items-center">
                                                    <span><?php echo e($groupName); ?> Permissions</span>
                                                    <span class="badge bg-primary rounded-pill ms-2"><?php echo e(count($permissions)); ?></span>
                                                </div>
                                            </button>
                                        </h2>
                                        <div id="collapse<?php echo e(\Str::slug($groupName)); ?>" class="accordion-collapse collapse" 
                                            aria-labelledby="heading<?php echo e(\Str::slug($groupName)); ?>" data-bs-parent="#permissionsAccordion">
                                            <div class="accordion-body">
                                                <div class="row">
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <div class="col-lg-3 col-md-4 col-sm-6 mb-2">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" 
                                                                    wire:model="selectedPermissions" 
                                                                    value="<?php echo e($permission->id); ?>" 
                                                                    id="perm_<?php echo e($permission->id); ?>">
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
                    <button type="button" class="btn btn-light" wire:click="closeModal">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="saveUser" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="saveUser">Save</span>
                        <span wire:loading wire:target="saveUser">Saving...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isOpen): ?>
        <div class="modal-backdrop fade show"></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this user? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="deleteUser" data-bs-dismiss="modal">
                        <span wire:loading.remove wire:target="deleteUser">Delete</span>
                        <span wire:loading wire:target="deleteUser">Deleting...</span>
                    </button>
                </div>
            </div>
        </div>
    <!-- Staff Password Reset Modal -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showPasswordResetModal): ?>
    <div class="modal fade show" tabindex="-1" style="display: block; background-color: rgba(0,0,0,0.55); z-index: 1060;" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-warning py-3 px-4 text-dark border-0">
                    <h5 class="modal-title fw-bold fs-5 d-flex align-items-center">
                        <i class="fas fa-key me-2 text-dark"></i>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($resetScope === 'individual'): ?>
                            Reset Password: <?php echo e($resetTargetUserName); ?> (<?php echo e($resetTargetUserEmail); ?>)
                        <?php elseif($resetScope === 'selected'): ?>
                            Reset Passwords for <?php echo e($resetTargetCount); ?> Selected Staff Members
                        <?php elseif($resetScope === 'all'): ?>
                            Reset Passwords for All Staff Members (<?php echo e($resetTargetCount); ?> staff)
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeStaffPasswordReset"></button>
                </div>

                <div class="modal-body p-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($resetSummary): ?>
                        <!-- Summary View after execution -->
                        <div class="alert alert-success d-flex align-items-center mb-4">
                            <i class="fas fa-check-circle fs-2 me-3 text-success"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Password Reset Completed</h6>
                                <p class="mb-0 fs-7 text-muted">Summary of the staff credential reset operation:</p>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-6 col-md-3">
                                <div class="bg-light p-3 rounded-2 text-center border">
                                    <span class="fs-8 text-muted d-block text-uppercase fw-semibold">Targeted</span>
                                    <span class="fs-4 fw-bold text-gray-900"><?php echo e($resetSummary['total'] ?? 0); ?></span>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="bg-light-success p-3 rounded-2 text-center border border-success border-opacity-25">
                                    <span class="fs-8 text-success d-block text-uppercase fw-semibold">Processed</span>
                                    <span class="fs-4 fw-bold text-success"><?php echo e($resetSummary['processed'] ?? 0); ?></span>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="bg-light-primary p-3 rounded-2 text-center border border-primary border-opacity-25">
                                    <span class="fs-8 text-primary d-block text-uppercase fw-semibold">Emails Sent</span>
                                    <span class="fs-4 fw-bold text-primary"><?php echo e($resetSummary['emails_sent'] ?? 0); ?></span>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="bg-light-info p-3 rounded-2 text-center border border-info border-opacity-25">
                                    <span class="fs-8 text-info d-block text-uppercase fw-semibold">SMS Sent</span>
                                    <span class="fs-4 fw-bold text-info"><?php echo e($resetSummary['sms_sent'] ?? 0); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Form Configuration View -->
                        <div class="alert alert-light-warning border border-warning border-opacity-50 p-3 mb-4 rounded-2">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-shield-alt text-warning fs-3 me-3"></i>
                                <div class="fs-7 text-gray-800">
                                    <strong>System Administrator Action:</strong> You are about to reset staff account password credentials. This will overwrite existing passwords and send updated credentials through the selected channels.
                                </div>
                            </div>
                        </div>

                        <!-- Password Generation Option -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-gray-800 fs-7">Temporary Password Setting</label>
                            <div class="d-flex flex-column gap-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" id="staff_pwd_random" value="random" wire:model.live="resetPasswordMode">
                                    <label class="form-check-label fs-7 fw-medium" for="staff_pwd_random">
                                        Auto-generate secure random temporary password <span class="badge bg-light text-muted ms-1">Recommended</span>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" id="staff_pwd_custom" value="custom" wire:model.live="resetPasswordMode">
                                    <label class="form-check-label fs-7 fw-medium" for="staff_pwd_custom">
                                        Specify a uniform temporary password
                                    </label>
                                </div>
                            </div>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($resetPasswordMode === 'custom'): ?>
                                <div class="mt-2" style="max-width: 320px;">
                                    <input type="text" class="form-control form-control-sm <?php $__errorArgs = ['resetCustomPassword'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           placeholder="Enter temporary password (min 8 chars)" 
                                           wire:model="resetCustomPassword">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['resetCustomPassword'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="text-danger fs-8 mt-1"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <!-- Notification Channel Option -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-gray-800 fs-7">Credential Notification Channel</label>
                            <div class="row g-2">
                                <div class="col-sm-6">
                                    <div class="form-check border p-2.5 rounded-2 <?php if($resetChannel === 'both'): ?> bg-light-primary border-primary <?php endif; ?>">
                                        <input class="form-check-input me-2" type="radio" id="staff_channel_both" value="both" wire:model.live="resetChannel">
                                        <label class="form-check-label fs-7 fw-semibold cursor-pointer w-100" for="staff_channel_both">
                                            <i class="fas fa-paper-plane text-primary me-1"></i> Both Email & SMS
                                        </label>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-check border p-2.5 rounded-2 <?php if($resetChannel === 'email'): ?> bg-light-primary border-primary <?php endif; ?>">
                                        <input class="form-check-input me-2" type="radio" id="staff_channel_email" value="email" wire:model.live="resetChannel">
                                        <label class="form-check-label fs-7 fw-semibold cursor-pointer w-100" for="staff_channel_email">
                                            <i class="fas fa-envelope text-primary me-1"></i> Email Only
                                        </label>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-check border p-2.5 rounded-2 <?php if($resetChannel === 'sms'): ?> bg-light-primary border-primary <?php endif; ?>">
                                        <input class="form-check-input me-2" type="radio" id="staff_channel_sms" value="sms" wire:model.live="resetChannel">
                                        <label class="form-check-label fs-7 fw-semibold cursor-pointer w-100" for="staff_channel_sms">
                                            <i class="fas fa-comment-dots text-info me-1"></i> SMS Only
                                        </label>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-check border p-2.5 rounded-2 <?php if($resetChannel === 'none'): ?> bg-light-secondary <?php endif; ?>">
                                        <input class="form-check-input me-2" type="radio" id="staff_channel_none" value="none" wire:model.live="resetChannel">
                                        <label class="form-check-label fs-7 fw-semibold cursor-pointer w-100" for="staff_channel_none">
                                            <i class="fas fa-bell-slash text-muted me-1"></i> Do Not Send (Manual)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Force Password Change Toggle -->
                        <div class="mb-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="staff_force_change" wire:model="resetRequirePasswordChange">
                                <label class="form-check-label fs-7 fw-bold text-gray-800" for="staff_force_change">
                                    Require password change upon initial login
                                </label>
                            </div>
                            <div class="text-muted fs-8 ms-6">
                                When enabled, staff will be redirected to choose a new secure password on next login.
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="modal-footer bg-light py-3 px-4 border-0">
                    <button type="button" class="btn btn-sm btn-light-secondary border" wire:click="closeStaffPasswordReset">
                        <?php echo e($resetSummary ? 'Close' : 'Cancel'); ?>

                    </button>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $resetSummary): ?>
                        <button type="button" class="btn btn-warning text-dark fw-bold px-4" 
                                wire:click="executeStaffPasswordReset" 
                                wire:loading.attr="disabled">
                            <i class="fas fa-check-circle me-1.5" wire:loading.remove wire:target="executeStaffPasswordReset"></i>
                            <span wire:loading.remove wire:target="executeStaffPasswordReset">Confirm & Reset Passwords</span>
                            <span wire:loading wire:target="executeStaffPasswordReset">
                                <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                                Processing...
                            </span>
                        </button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php $__env->startPush('scripts'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteConfirmationModalEl = document.getElementById('deleteConfirmationModal');

            const deleteConfirmationModal = new bootstrap.Modal(deleteConfirmationModalEl);
            
            // Fix for permissions and roles checkboxes - stop propagation to prevent accordion issues
            document.addEventListener('click', function(e) {
                if (e.target && (e.target.matches('[wire\\:model="selectedPermissions"]') || 
                                e.target.matches('[wire\\:model="selectedRoles"]'))) {
                    e.stopPropagation();
                }
            }, true);
            
            // Wait for Livewire to be fully initialized
            document.addEventListener('livewire:initialized', () => {
                // Delete confirmation modal handler
                Livewire.on('showDeleteConfirmation', () => {
                    deleteConfirmationModal.show();
                });
            });
        });
    </script>
    <?php $__env->stopPush(); ?>
</div>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/livewire/settings/user-management.blade.php ENDPATH**/ ?>