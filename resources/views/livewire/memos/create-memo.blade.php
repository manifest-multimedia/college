<div>
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <h3 class="card-title">
                    <i class="ki-duotone ki-document-add fs-1 me-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>
                    Create New Memo
                </h3>
            </div>
        </div>
        
        <div class="card-body">
            @if (session()->has('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            
            <form wire:submit.prevent="saveAsPending">
                <div class="row mb-5">
                    <!-- Memo Title -->
                    <div class="col-md-12 mb-5">
                        <label class="form-label required">Memo Title</label>
                        <input type="text" wire:model="title" class="form-control form-control-solid" placeholder="Enter memo title">
                        @error('title') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    
                    <!-- Priority and Recipient Type selection -->
                    <div class="col-md-6 mb-5">
                        <label class="form-label required">Priority</label>
                        <select wire:model="priority" class="form-select form-select-solid">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                        @error('priority') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="col-md-6 mb-5">
                        <label class="form-label required">Send To</label>
                        <div class="d-flex">
                            <div class="form-check form-check-custom form-check-solid me-5">
                                <input class="form-check-input" type="radio" value="user" id="send_to_user" wire:model.live="recipientType">
                                <label class="form-check-label" for="send_to_user">
                                    Specific User
                                </label>
                            </div>
                            <div class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="radio" value="department" id="send_to_department" wire:model.live="recipientType">
                                <label class="form-check-label" for="send_to_department">
                                    Department
                                </label>
                            </div>
                        </div>
                        @error('recipientType') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    
                    <!-- Recipient Selection based on type -->
                    @if ($recipientType === 'user')
                        <div class="col-md-6 mb-5">
                            <label class="form-label required">Select User</label>
                            <select wire:model="recipientId" class="form-select form-select-solid">
                                <option value="">Select a user</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                            @error('recipientId') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    @else
                        <div class="col-md-6 mb-5">
                            <label class="form-label required">Select Department</label>
                            <select wire:model="recipientDepartmentId" class="form-select form-select-solid">
                                <option value="">Select a department</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                            @error('recipientDepartmentId') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    @endif
                    
                    <!-- Requested Action -->
                    <div class="col-md-6 mb-5">
                        <label class="form-label">Requested Action</label>
                        <input type="text" wire:model="requestedAction" class="form-control form-control-solid" placeholder="What action is needed?">
                        @error('requestedAction') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    
                    <!-- Memo Description -->
                    <div class="col-md-12 mb-5">
                        <label class="form-label required">Description</label>
                        <textarea wire:model="description" class="form-control form-control-solid" rows="6" placeholder="Enter detailed description of the memo"></textarea>
                        @error('description') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    
                    <!-- Attachments -->
                    <div class="col-md-12 mb-5">
                        <label class="form-label">Attachments</label>
                        <div class="dropzone-panel mb-lg-0 mb-2">
                            <input type="file" wire:model="attachments" class="form-control form-control-solid" multiple>
                            <div wire:loading wire:target="attachments">
                                <span class="spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true"></span> Uploading...
                            </div>
                        </div>
                        @error('attachments.*') <span class="text-danger">{{ $message }}</span> @enderror
                        
                        <!-- Show previews of uploaded files -->
                        @if(count($attachments) > 0)
                            <div class="mt-3">
                                <h6>Selected Files:</h6>
                                <div class="d-flex flex-wrap gap-5">
                                    @foreach($attachments as $index => $attachment)
                                        <div class="border rounded p-3 position-relative">
                                            <button type="button" wire:click="removeAttachment({{ $index }})" class="btn btn-icon btn-sm btn-danger position-absolute top-0 end-0 m-2">
                                                <i class="ki-duotone ki-cross fs-2">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </button>
                                            
                                            @if(str_starts_with($attachment->getMimeType(), 'image/'))
                                                <img src="{{ $attachment->temporaryUrl() }}" class="img-fluid" style="max-height: 100px;" alt="{{ $attachment->getClientOriginalName() }}">
                                            @else
                                                <i class="ki-duotone ki-file fs-2x mb-2">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            @endif
                                            
                                            <div class="text-gray-600 text-center mt-2" style="max-width: 120px; overflow: hidden; text-overflow: ellipsis;">
                                                {{ $attachment->getClientOriginalName() }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                
                <div class="separator mb-5"></div>
                
                <div class="d-flex justify-content-end">
                    <a href="{{ route('memos') }}" class="btn btn-light me-3">Cancel</a>
                    <button type="button" wire:click="saveDraft" class="btn btn-light-primary me-3">
                        <span wire:loading.remove wire:target="saveDraft">Save as Draft</span>
                        <span wire:loading wire:target="saveDraft" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <span wire:loading.remove wire:target="saveAsPending">Submit Memo</span>
                        <span wire:loading wire:target="saveAsPending" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
