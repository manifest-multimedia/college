<div>
    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- Backup Options -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <div class="card-title">
                        <h3 class="card-title fw-bold text-gray-800">
                            <i class="fas fa-download me-2"></i>Create Backup
                        </h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <button type="button" class="btn btn-primary" wire:click="createBackup('db')" wire:loading.attr="disabled">
                            <div wire:loading.remove wire:target="createBackup">
                                <i class="fas fa-database me-2"></i> Backup Database Only
                            </div>
                            <div wire:loading wire:target="createBackup">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Creating Backup...
                            </div>
                        </button>
                        
                        <button type="button" class="btn btn-info" wire:click="createBackup('full')" wire:loading.attr="disabled">
                            <div wire:loading.remove wire:target="createBackup">
                                <i class="fas fa-server me-2"></i> Full Backup (Files + DB)
                            </div>
                            <div wire:loading wire:target="createBackup">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Creating Backup...
                            </div>
                        </button>
                    </div>
                    
                    <div class="alert alert-info mt-4 mb-0">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-info-circle fs-4 me-3 mt-1"></i>
                            <div>
                                <div class="fw-bold">Last Backup:</div>
                                <div>{{ $lastBackupTime }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <h3 class="card-title fw-bold text-gray-800">
                            <i class="fas fa-upload me-2"></i>Restore Backup
                        </h3>
                    </div>
                </div>
                <div class="card-body">
                    <form wire:submit="uploadRestore">
                        <div class="mb-4">
                            <label for="restoreFile" class="form-label fw-bold">Upload Backup File</label>
                            <input type="file" wire:model="restoreFile" id="restoreFile" class="form-control @error('restoreFile') is-invalid @enderror">
                            <div wire:loading wire:target="restoreFile" class="text-sm text-gray-500 mt-1">
                                Uploading...
                            </div>
                            @error('restoreFile')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text mt-2">
                                Upload a backup zip file to restore the system.
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-warning w-100">
                            <i class="fas fa-sync-alt me-2"></i> Restore System
                        </button>
                    </form>
                    
                    <div class="alert alert-warning mt-4 mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> Restoring a backup will overwrite all current data. This action cannot be undone.
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Backup Files List -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <h3 class="card-title fw-bold text-gray-800">
                            <i class="fas fa-history me-2"></i>Backup History
                        </h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-row-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr class="fw-bold fs-6 text-gray-800">
                                    <th>File Name</th>
                                    <th>Size</th>
                                    <th>Date</th>
                                    <th>Age</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($backupFiles as $file)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="symbol symbol-40px me-3">
                                                    <span class="symbol-label bg-light-primary">
                                                        <i class="fas fa-file-archive text-primary"></i>
                                                    </span>
                                                </div>
                                                <div>
                                                    <span class="fw-bold d-block">{{ $file['name'] }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $file['size'] }}</td>
                                        <td>{{ $file['date'] }}</td>
                                        <td>{{ $file['age'] }}</td>
                                        <td class="text-end">
                                            <div class="d-inline-flex">
                                                <a href="{{ route('settings.backup.download', ['path' => $file['path']]) }}" class="btn btn-sm btn-icon btn-light-primary me-2" title="Download">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-icon btn-light-danger" title="Delete" wire:click="confirmDelete('{{ $file['path'] }}')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4">No backup files found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Backup File</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this backup file? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="deleteBackup" data-bs-dismiss="modal">
                        <span wire:loading.remove wire:target="deleteBackup">Delete</span>
                        <span wire:loading wire:target="deleteBackup">Deleting...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            const deleteConfirmationModal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
            
            @this.on('showDeleteConfirmation', () => {
                deleteConfirmationModal.show();
            });
        });
    </script>
    @endpush
</div>