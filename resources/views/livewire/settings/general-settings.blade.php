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
                    <div class="d-flex align-items-center">
                        @if($currentLogo)
                            <div class="me-4">
                                <img src="{{ asset($currentLogo) }}" alt="School Logo" class="img-thumbnail" style="max-height: 100px">
                            </div>
                        @endif
                        <input type="file" wire:model="schoolLogo" id="schoolLogo" class="form-control @error('schoolLogo') is-invalid @enderror" accept="image/*">
                    </div>
                    <div wire:loading wire:target="schoolLogo" class="text-sm text-gray-500 mt-1">
                        Uploading...
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
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i> Save Settings
            </button>
        </div>
    </form>
</div>