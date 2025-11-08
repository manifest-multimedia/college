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

    <div class="card mb-5">
        <div class="card-header">
            <div class="card-title">
                <h3 class="card-title">
                    <i class="fas fa-cogs me-2"></i>
                    General Settings
                </h3>
            </div>
        </div>
        <div class="card-body">
            <form wire:submit="save">
                <div class="row">
                    <!-- School Information -->
                    <div class="col-lg-6">
                        <h4 class="mb-4 fw-bold">School Information</h4>
                        
                        <div class="mb-4">
                            <label for="schoolName" class="form-label fw-bold">School Name</label>
                            <input type="text" wire:model="schoolName" id="schoolName" class="form-control @error('schoolName') is-invalid @enderror">
                            @error('schoolName')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <label for="schoolNamePrefix" class="form-label fw-bold">School Name Prefix</label>
                            <input type="text" wire:model="schoolNamePrefix" id="schoolNamePrefix" class="form-control @error('schoolNamePrefix') is-invalid @enderror" placeholder="PNMTC or PNMTC/DA or PNMTC-DA" maxlength="15">
                            <div class="form-text">
                                This prefix will be used for generating Student IDs (e.g., PNMTC/DA/RM/22/23/001). 
                                Format: PREFIX/PROGRAM_CODE/ACADEMIC_YEAR/SEQUENCE_NUMBER. Allowed characters: letters, numbers, hyphens (-), and forward slashes (/). Will be converted to uppercase.
                            </div>
                            @error('schoolNamePrefix')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <label for="schoolEmail" class="form-label fw-bold">School Email</label>
                            <input type="email" wire:model="schoolEmail" id="schoolEmail" class="form-control @error('schoolEmail') is-invalid @enderror">
                            @error('schoolEmail')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <label for="schoolPhone" class="form-label fw-bold">School Phone</label>
                            <input type="text" wire:model="schoolPhone" id="schoolPhone" class="form-control @error('schoolPhone') is-invalid @enderror">
                            @error('schoolPhone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <label for="schoolAddress" class="form-label fw-bold">School Address</label>
                            <textarea wire:model="schoolAddress" id="schoolAddress" class="form-control @error('schoolAddress') is-invalid @enderror" rows="3"></textarea>
                            @error('schoolAddress')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <label for="schoolWebsite" class="form-label fw-bold">School Website</label>
                            <input type="url" wire:model="schoolWebsite" id="schoolWebsite" class="form-control @error('schoolWebsite') is-invalid @enderror" placeholder="https://example.com">
                            @error('schoolWebsite')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- System Settings -->
                    <div class="col-lg-6">
                        <h4 class="mb-4 fw-bold">System Settings</h4>
                        
                        <div class="mb-4">
                            <label for="systemTimeZone" class="form-label fw-bold">System Timezone</label>
                            <select wire:model="systemTimeZone" id="systemTimeZone" class="form-select @error('systemTimeZone') is-invalid @enderror">
                                @foreach($timezones as $timezone)
                                    <option value="{{ $timezone }}">{{ $timezone }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">Current server time: {{ now()->format('Y-m-d H:i:s') }}</div>
                            @error('systemTimeZone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <label for="academicYear" class="form-label fw-bold">Current Academic Year</label>
                            <input type="text" wire:model="academicYear" id="academicYear" class="form-control @error('academicYear') is-invalid @enderror" placeholder="2024-2025">
                            @error('academicYear')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <label for="schoolLogo" class="form-label fw-bold">School Logo</label>
                            <div class="d-flex align-items-center mb-2">
                                @if($currentLogo)
                                    <div class="me-4 border p-2 bg-light">
                                        <img src="{{ asset($currentLogo) }}" alt="School Logo" class="img-thumbnail" style="max-height: 100px">
                                    </div>
                                @endif
                            </div>
                            <input type="file" wire:model="schoolLogo" id="schoolLogo" class="form-control @error('schoolLogo') is-invalid @enderror" accept="image/*">
                            <div class="form-text">Recommended size: 200x200 pixels. Max 1MB.</div>
                            <div wire:loading wire:target="schoolLogo" class="text-sm text-primary mt-1">
                                <i class="fas fa-spinner fa-spin me-1"></i> Uploading...
                            </div>
                            @error('schoolLogo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> 
                            System settings will be applied immediately after saving.
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="save">
                            <i class="fas fa-save me-2"></i> Save Settings
                        </span>
                        <span wire:loading wire:target="save">
                            <i class="fas fa-spinner fa-spin me-2"></i> Saving...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>