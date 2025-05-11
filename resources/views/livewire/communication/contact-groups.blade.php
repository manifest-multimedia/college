<div>
    <div class="card mb-5">
        <div class="card-header">
            <div class="card-title">
                <h3 class="card-title">
                    <i class="ki-duotone ki-abstract-25 fs-1 me-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    {{ $editMode ? 'Edit Contact Group' : 'Create New Contact Group' }}
                </h3>
            </div>
        </div>
        
        <div class="card-body">
            @if (session()->has('success'))
                <div class="alert alert-success mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if (session()->has('error'))
                <div class="alert alert-danger mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <form wire:submit.prevent="{{ $editMode ? 'updateGroup' : 'createGroup' }}">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label required">Group Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                id="name" wire:model.live="name" placeholder="Enter group name">
                            @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="type" class="form-label required">Group Type</label>
                            <select class="form-select @error('type') is-invalid @enderror" id="type" wire:model.live="type">
                                <option value="sms">SMS Only</option>
                                <option value="email">Email Only</option>
                                <option value="both">Both SMS & Email</option>
                            </select>
                            @error('type') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" 
                        wire:model.live="description" rows="2" placeholder="Enter description"></textarea>
                    @error('description') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" wire:model.live="is_active">
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-light" wire:click="resetForm">
                        {{ $editMode ? 'Cancel' : 'Reset' }}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <span wire:loading wire:target="{{ $editMode ? 'updateGroup' : 'createGroup' }}" class="spinner-border spinner-border-sm me-1" role="status"></span>
                        {{ $editMode ? 'Update Group' : 'Create Group' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <h3 class="card-title">
                    <i class="ki-duotone ki-profile-user fs-1 me-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    SMS Contact Groups
                </h3>
            </div>
            
            <div class="card-toolbar">
                <div class="d-flex align-items-center position-relative">
                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-4">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    <input type="text" class="form-control form-control-solid ps-12" 
                        placeholder="Search groups..." wire:model.live.debounce.300ms="searchTerm">
                </div>
            </div>
        </div>
        
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                    <thead>
                        <tr class="fw-bold text-muted bg-light">
                            <th class="min-w-150px">Name</th>
                            <th class="min-w-100px">Type</th>
                            <th class="min-w-100px">Contacts</th>
                            <th class="min-w-100px">Status</th>
                            <th class="min-w-100px text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($contactGroups as $group)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="d-flex justify-content-start flex-column">
                                            <span class="text-dark fw-bold text-hover-primary fs-6">{{ $group->name }}</span>
                                            @if($group->description)
                                                <span class="text-muted fw-semibold text-muted d-block fs-7">{{ Str::limit($group->description, 50) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($group->type == 'sms')
                                        <span class="badge badge-light-primary">SMS Only</span>
                                    @elseif($group->type == 'email')
                                        <span class="badge badge-light-info">Email Only</span>
                                    @else
                                        <span class="badge badge-light-success">SMS & Email</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-light-info">{{ $group->items->count() }}</span>
                                </td>
                                <td>
                                    @if($group->is_active)
                                        <span class="badge badge-light-success">Active</span>
                                    @else
                                        <span class="badge badge-light-danger">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end flex-shrink-0">
                                        <a href="{{ route('communication.contacts', ['groupId' => $group->id]) }}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" title="View Contacts">
                                            <i class="ki-duotone ki-eye fs-3">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                            </i>
                                        </a>
                                        <button type="button" wire:click="editGroup({{ $group->id }})" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" title="Edit">
                                            <i class="ki-duotone ki-pencil fs-3">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </button>
                                        <button type="button" wire:click="confirmDelete({{ $group->id }})" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm" title="Delete">
                                            <i class="ki-duotone ki-trash fs-3">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                                <span class="path4"></span>
                                                <span class="path5"></span>
                                            </i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">No contact groups found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-between align-items-center flex-wrap mt-5">
                {{ $contactGroups->links() }}
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this contact group? This will also delete all contacts in this group. This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="confirmDeleteBtn" class="btn btn-danger">Delete</button>
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
            let deleteId = null;
            
            @this.on('showDeleteConfirmation', (event) => {
                deleteId = event.id;
                deleteModal.show();
            });
            
            document.getElementById('confirmDeleteBtn').addEventListener('click', () => {
                if (deleteId) {
                    @this.deleteGroup(deleteId);
                    deleteModal.hide();
                }
            });
        });
    </script>
    @endpush
</div>
