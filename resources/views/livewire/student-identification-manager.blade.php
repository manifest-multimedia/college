<div>
    <div class="card shadow-sm mb-5">
        <div class="card-header align-items-center">
            <div class="card-title">
                <h3 class="fw-bold text-gray-800 mb-0"><i class="fas fa-id-card me-2"></i>Identifications</h3>
            </div>
            <button type="button" wire:click="createIdentification" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i>Add Identification
            </button>
        </div>
        <div class="card-body">
            <div class="alert alert-light-primary d-flex align-items-center py-3">
                <i class="fas fa-lock me-3"></i>
                <span>Identity numbers are encrypted and masked. Card images are kept in private storage.</span>
            </div>

            @if($identifications->isEmpty())
                <p class="text-muted mb-0">No identification has been recorded.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-row-bordered align-middle">
                        <thead><tr class="text-muted fw-bold"><th>Type</th><th>Number</th><th>Status</th><th>Expires</th><th>Evidence</th><th class="text-end">Actions</th></tr></thead>
                        <tbody>
                            @foreach($identifications as $identification)
                                <tr>
                                    <td class="fw-semibold">{{ $identification->type_label }}</td>
                                    <td>{{ $identification->masked_identifier }}</td>
                                    <td>
                                        @if($identification->verification_status === 'verified')
                                            <span class="badge badge-light-success">Verified</span>
                                        @else
                                            <span class="badge badge-light-warning">Unverified</span>
                                        @endif
                                    </td>
                                    <td>{{ $identification->expires_on?->format('d M Y') ?? '—' }}</td>
                                    <td>
                                        @if($identification->image_path)
                                            <a class="btn btn-sm btn-light-primary" target="_blank" rel="noopener" href="{{ route('students.identifications.image', [$studentId, $identification->id]) }}">View image</a>
                                        @else
                                            <span class="text-muted">None</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <button type="button" wire:click="editIdentification({{ $identification->id }})" class="btn btn-sm btn-light me-1">Edit</button>
                                        <button type="button" wire:click="deleteIdentification({{ $identification->id }})" wire:confirm="Remove this identification and its image? This cannot be undone." class="btn btn-sm btn-light-danger">Remove</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    @if($showForm)
        <div class="card shadow-sm mb-5">
            <div class="card-header"><h3 class="card-title fw-bold">{{ $editingIdentificationId ? 'Edit Identification' : 'Add Identification' }}</h3></div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-4"><label class="form-label">Type <span class="text-danger">*</span></label><select wire:model="identificationType" class="form-select @error('identificationType') is-invalid @enderror">@foreach($identificationTypes as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select>@error('identificationType')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-4"><label class="form-label">Identification Number <span class="text-danger">*</span></label><input wire:model="identifierNumber" autocomplete="off" class="form-control @error('identifierNumber') is-invalid @enderror" placeholder="GHA-123456789-0">@error('identifierNumber')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-md-4"><label class="form-label">Issuing Country</label><input wire:model="issuingCountry" maxlength="2" class="form-control" placeholder="GH"></div>
                    <div class="col-md-4"><label class="form-label">Issuing Authority</label><input wire:model="issuingAuthority" class="form-control" placeholder="e.g. NIA"></div>
                    <div class="col-md-4"><label class="form-label">Expiry Date</label><input type="date" wire:model="expiresOn" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Card Image</label><input type="file" wire:model="cardImage" accept="image/png,image/jpeg,image/webp" class="form-control @error('cardImage') is-invalid @enderror"><div class="form-text">JPG, PNG, or WEBP; maximum 5 MB.</div>@error('cardImage')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="col-12"><label class="form-check form-switch form-check-custom form-check-solid"><input class="form-check-input" type="checkbox" wire:model="markVerified"><span class="form-check-label">I have checked this identification and mark it verified.</span></label></div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-5"><button type="button" wire:click="cancel" class="btn btn-light">Cancel</button><button type="button" wire:click="saveIdentification" class="btn btn-primary">Save Identification</button></div>
            </div>
        </div>
    @endif
</div>
