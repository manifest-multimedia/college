<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;

class PermissionsManagement extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';

    public $sortField = 'name';

    public $sortDirection = 'asc';

    public $categoryFilter = '';

    // Form properties for add/edit permission
    public $permissionId = null;

    public $name;

    public $description;

    public $category;

    public $guardName = 'web';

    public $isOpen = false;

    public $editMode = false;

    public $viewMode = false;

    protected $listeners = [
        'deleteConfirmed' => 'deletePermission',
        'editPermission' => 'editPermission',
        'viewPermission' => 'viewPermission',
        'closeModalAction' => 'closeModal',
    ];

    protected $rules = [
        'name' => 'required|string|max:100',
        'description' => 'nullable|string|max:255',
        'category' => 'required|string|max:50',
        'guardName' => 'required|string|max:50',
    ];

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function openModal($mode = 'add')
    {
        $this->resetValidation();
        $this->reset(['name', 'description', 'category', 'guardName']);

        if ($mode === 'add') {
            $this->guardName = 'web';
        }

        $this->editMode = $mode === 'edit';
        $this->viewMode = $mode === 'view';
        $this->isOpen = true;

        // Dispatch events to notify JavaScript to open modal
        $this->dispatch('permissionModalStateChanged', ['isOpen' => true]);

        // Also dispatch permissionDataLoaded event to ensure modal is shown
        // This is needed for consistency with edit and view operations
        $this->dispatch('permissionDataLoaded');

        // Log the action for debugging
        Log::info('Permission modal opened in mode: '.$mode);
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->reset(['permissionId', 'name', 'description', 'category', 'guardName', 'editMode', 'viewMode']);

        // Dispatch event to notify JavaScript to close modal
        $this->dispatch('closeModal');
    }

    public function editPermission($id)
    {
        try {
            // Load the permission
            $permission = Permission::findOrFail($id);

            // Reset form values first
            $this->reset(['name', 'description', 'category', 'guardName']);

            // Extract category from permission name
            $category = $this->extractCategoryFromName($permission->name);

            // Set component properties
            $this->permissionId = $permission->id;
            $this->name = $permission->name;
            $this->description = $permission->description ?? '';
            $this->category = $category;
            $this->guardName = $permission->guard_name;

            // Log the data loading process
            Log::info('Permission data loaded for editing', [
                'permission_id' => $permission->id,
                'name' => $permission->name,
            ]);

            // Set modal state
            $this->editMode = true;
            $this->viewMode = false;
            $this->isOpen = true;

            // Dispatch events
            $this->dispatch('permissionModalStateChanged', ['isOpen' => true]);
            $this->dispatch('permissionDataLoaded');

        } catch (\Exception $e) {
            Log::error('Error editing permission: '.$e->getMessage(), [
                'permission_id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);
            session()->flash('error', 'Failed to load permission for editing.');
        }
    }

    public function viewPermission($id)
    {
        try {
            // Load the permission
            $permission = Permission::findOrFail($id);

            // Reset form values first
            $this->reset(['name', 'description', 'category', 'guardName']);

            // Extract category from permission name
            $category = $this->extractCategoryFromName($permission->name);

            // Set component properties
            $this->permissionId = $permission->id;
            $this->name = $permission->name;
            $this->description = $permission->description ?? '';
            $this->category = $category;
            $this->guardName = $permission->guard_name;

            // Log the data loading process
            Log::info('Permission data loaded for viewing', [
                'permission_id' => $permission->id,
                'name' => $permission->name,
            ]);

            // Set modal state
            $this->editMode = false;
            $this->viewMode = true;
            $this->isOpen = true;

            // Dispatch events
            $this->dispatch('permissionModalStateChanged', ['isOpen' => true]);
            $this->dispatch('permissionDataLoaded');

        } catch (\Exception $e) {
            Log::error('Error viewing permission: '.$e->getMessage(), [
                'permission_id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);
            session()->flash('error', 'Failed to load permission details.');
        }
    }

    public function savePermission()
    {
        // Skip validation in view mode
        if (! $this->viewMode) {
            $this->validate();
        }

        try {
            // Format permission name to include category (e.g., users.create)
            if (! empty($this->category) && ! $this->editMode) {
                // Only format name for new permissions or if specifically requested
                // For new permissions, we want to ensure proper formatting
                if (strpos($this->name, '.') === false && strpos($this->name, '-') === false) {
                    $categorySlug = strtolower(trim($this->category));
                    $nameSlug = strtolower(trim($this->name));
                    $this->name = "{$categorySlug}.{$nameSlug}";
                }

                Log::info('Permission name formatted with category', [
                    'category' => $this->category,
                    'formatted_name' => $this->name,
                ]);
            }

            $permissionData = [
                'name' => $this->name,
                'description' => $this->description,
                'guard_name' => $this->guardName,
            ];

            if ($this->editMode) {
                $permission = Permission::findOrFail($this->permissionId);

                // Check if the permission is in use and if name is changed
                if ($permission->name !== $this->name && $permission->roles()->count() > 0) {
                    session()->flash('warning', 'Permission name updated. Note that this permission is assigned to '.$permission->roles()->count().' roles.');
                }

                $permission->update($permissionData);
                session()->flash('success', 'Permission updated successfully.');
            } else {
                // Check if permission with this name already exists
                if (Permission::where('name', $this->name)->exists()) {
                    session()->flash('error', 'A permission with this name already exists.');

                    return;
                }

                Permission::create($permissionData);
                session()->flash('success', 'Permission created successfully.');
            }

            $this->closeModal();
        } catch (\Exception $e) {
            Log::error('Error saving permission: '.$e->getMessage());
            session()->flash('error', 'Failed to save permission. Please try again later.');
        }
    }

    public function confirmDelete($id)
    {
        $this->permissionId = $id;
        $this->dispatch('showDeleteConfirmation');
    }

    public function deletePermission()
    {
        try {
            $permission = Permission::findOrFail($this->permissionId);

            // Check if permission is assigned to any roles
            if ($permission->roles()->count() > 0) {
                session()->flash('error', 'Permission cannot be deleted because it is assigned to '.$permission->roles()->count().' roles.');

                return;
            }

            $permission->delete();
            session()->flash('success', 'Permission deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting permission: '.$e->getMessage());
            session()->flash('error', 'Failed to delete permission. Please try again later.');
        }
    }

    /**
     * Extract category from permission name
     *
     * @param  string  $name
     * @return string
     */
    private function extractCategoryFromName($name)
    {
        // Convert from camelCase or snake_case to readable format
        if (strpos($name, '-') !== false) {
            return ucwords(strtolower(explode('-', $name)[0]));
        } elseif (strpos($name, '.') !== false) {
            return ucwords(strtolower(explode('.', $name)[0]));
        } else {
            // Default case: use the first word
            $words = preg_split('/(?=[A-Z])/', $name);

            return ucwords(strtolower($words[0]));
        }
    }

    /**
     * Get all available permission categories
     *
     * @return array
     */
    public function getPermissionCategories()
    {
        return Permission::all()->map(function ($permission) {
            return $this->extractCategoryFromName($permission->name);
        })->unique()->sort()->values()->toArray();
    }

    public function render()
    {
        // Get permissions with optional filtering
        $permissions = Permission::query()
            ->when($this->search, function ($query) {
                // Only search in the name field since description may not exist
                return $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->when($this->categoryFilter, function ($query) {
                $categoryPattern = $this->categoryFilter.'%';

                return $query->where(function ($q) use ($categoryPattern) {
                    $q->where('name', 'like', $categoryPattern)
                        ->orWhere('name', 'like', strtolower($categoryPattern));
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        // Group permissions by category for display
        $permissionsByCategory = $permissions->groupBy(function ($permission) {
            return $this->extractCategoryFromName($permission->name);
        });

        return view('livewire.settings.permissions-management', [
            'permissions' => $permissions,
            'permissionsByCategory' => $permissionsByCategory,
            'categories' => $this->getPermissionCategories(),
        ]);
    }
}
