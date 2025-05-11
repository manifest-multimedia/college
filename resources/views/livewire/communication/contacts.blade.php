<div>
    <div class="card mb-5">
        <div class="card-header">
            <div class="card-title">
                <h3 class="card-title">
                    <i class="ki-duotone ki-profile-circle fs-1 me-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>
                    {{ $editMode ? 'Edit Contact' : 'Create New Contact' }}
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

            <form wire:submit.prevent="{{ $editMode ? 'updateContact' : 'createContact' }}">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label required">Contact Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                id="name" wire:model.live="name" placeholder="Enter contact name">
                            @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="recipient_list_id" class="form-label required">Contact Group</label>
                            <select class="form-select @error('recipient_list_id') is-invalid @enderror" 
                                id="recipient_list_id" wire:model.live="recipient_list_id">
                                <option value="">Select Group</option>
                                @foreach ($groups as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                            @error('recipient_list_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="phone" class="form-label required">Phone Number</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                id="phone" wire:model.live="phone" placeholder="Enter phone number (e.g. +233201234567)">
                            @error('phone') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                id="email" wire:model.live="email" placeholder="Enter email address (optional)">
                            @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" wire:model.live="is_active">
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                    <small class="text-muted">Inactive contacts will not receive SMS messages.</small>
                </div>
                
                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-light" wire:click="resetForm">
                        {{ $editMode ? 'Cancel' : 'Reset' }}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <span wire:loading wire:target="{{ $editMode ? 'updateContact' : 'createContact' }}" class="spinner-border spinner-border-sm me-1" role="status"></span>
                        {{ $editMode ? 'Update Contact' : 'Create Contact' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <h3 class="card-title">
                    <i class="ki-duotone ki-people fs-1 me-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                        <span class="path4"></span>
                        <span class="path5"></span>
                    </i>
                    SMS Contacts
                </h3>
            </div>
            
            <div class="card-toolbar">
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <div>
                        <select class="form-select form-select-sm" wire:model.live="selectedGroup">
                            <option value="">All Groups</option>
                            @foreach ($groups as $group)
                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="d-flex align-items-center position-relative">
                        <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-4">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        <input type="text" class="form-control form-control-sm form-control-solid ps-12" 
                            placeholder="Search contacts..." wire:model.live.debounce.300ms="searchTerm">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                    <thead>
                        <tr class="fw-bold text-muted bg-light">
                            <th class="min-w-150px">Name</th>
                            <th class="min-w-140px">Phone Number</th>
                            <th class="min-w-140px">Email</th>
                            <th class="min-w-120px">Group</th>
                            <th class="min-w-80px">Status</th>
                            <th class="min-w-100px text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($contacts as $contact)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="symbol symbol-45px me-5">
                                            <div class="symbol-label bg-light-primary">
                                                <span class="text-primary">{{ strtoupper(substr($contact->name, 0, 1)) }}</span>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-start flex-column">
                                            <span class="text-dark fw-bold text-hover-primary fs-6">{{ $contact->name }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-dark fw-semibold d-block fs-6">{{ $contact->phone }}</span>
                                </td>
                                <td>
                                    <span class="text-dark fw-semibold d-block fs-7">{{ $contact->email ?: '—' }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-light-primary">{{ $contact->recipientList->name }}</span>
                                </td>
                                <td>
                                    @if($contact->is_active)
                                        <span class="badge badge-light-success">Active</span>
                                    @else
                                        <span class="badge badge-light-danger">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end flex-shrink-0">
                                        <button type="button" wire:click="editContact({{ $contact->id }})" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" title="Edit">
                                            <i class="ki-duotone ki-pencil fs-3">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </button>
                                        <button type="button" wire:click="confirmDelete({{ $contact->id }})" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm" title="Delete">
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
                                <td colspan="6" class="text-center py-4">No contacts found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-between align-items-center flex-wrap mt-5">
                {{ $contacts->links() }}
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
                    <p>Are you sure you want to delete this contact? This action cannot be undone.</p>
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
                    @this.deleteContact(deleteId);
                    deleteModal.hide();
                }
            });
        });
    </script>
    @endpush
</div>
