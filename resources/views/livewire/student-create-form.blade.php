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

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h4 class="mb-1 text-danger">Please fix the following errors:</h4>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form wire:submit="createStudent">
        <!-- Photo Upload Section -->
        <div class="row mb-6">
            <div class="col-lg-12">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h4 class="text-gray-700 mb-4">Student Photo</h4>
                        
                        @if ($photo)
                            <div class="mb-3 position-relative d-inline-block">
                                <img src="{{ $photo->temporaryUrl() }}" class="rounded-circle" width="150" height="150" alt="Preview">
                                <button type="button" wire:click="$set('photo', null)" 
                                        class="position-absolute top-0 end-0 btn btn-danger rounded-circle p-0 border-0"
                                        style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; line-height: 0;"
                                        title="Remove photo">
                                    <i class="fas fa-times" style="font-size: 14px; margin: 0; padding: 0; display: block;"></i>
                                </button>
                            </div>
                        @else
                            <div class="bg-light rounded-circle d-inline-flex justify-content-center align-items-center mb-3" style="width: 150px; height: 150px;">
                                <i class="fas fa-user fs-1 text-muted"></i>
                            </div>
                        @endif
                        
                        <div class="mt-3">
                            <input type="file" wire:model="photo" class="form-control" accept="image/*" id="photoInput">
                            @error('photo') 
                                <div class="text-danger mt-2">{{ $message }}</div> 
                            @enderror
                            <div class="form-text">Only *.png, *.jpg and *.jpeg image files are accepted (Max: 2MB)</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Personal Details -->
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title fw-bold text-gray-800">
                            <i class="fas fa-user me-2"></i>Personal Details
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- First Name -->
                            <div class="col-md-4">
                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="first_name" class="form-control @error('first_name') is-invalid @enderror" required>
                                @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Other Name -->
                            <div class="col-md-4">
                                <label class="form-label">Other Name</label>
                                <input type="text" wire:model="other_name" class="form-control @error('other_name') is-invalid @enderror">
                                @error('other_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Last Name -->
                            <div class="col-md-4">
                                <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="last_name" class="form-control @error('last_name') is-invalid @enderror" required>
                                @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Gender -->
                            <div class="col-md-4">
                                <label class="form-label">Gender</label>
                                <select wire:model="gender" class="form-select @error('gender') is-invalid @enderror">
                                    <option value="">Select Gender</option>
                                    @foreach($genders as $genderOption)
                                        <option value="{{ $genderOption }}">{{ $genderOption }}</option>
                                    @endforeach
                                </select>
                                @error('gender')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Date of Birth -->
                            <div class="col-md-4">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" wire:model="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror">
                                @error('date_of_birth')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Nationality -->
                            <div class="col-md-4">
                                <label class="form-label">Nationality</label>
                                <input type="text" wire:model="nationality" class="form-control @error('nationality') is-invalid @enderror" placeholder="e.g., Ghanaian">
                                @error('nationality')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Religion -->
                            <div class="col-md-4">
                                <label class="form-label">Religion</label>
                                <select wire:model="religion" class="form-select @error('religion') is-invalid @enderror">
                                    <option value="">Select Religion</option>
                                    @foreach($religions as $religionOption)
                                        <option value="{{ $religionOption }}">{{ $religionOption }}</option>
                                    @endforeach
                                </select>
                                @error('religion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Marital Status -->
                            <div class="col-md-4">
                                <label class="form-label">Marital Status</label>
                                <select wire:model="marital_status" class="form-select @error('marital_status') is-invalid @enderror">
                                    <option value="">Select Marital Status</option>
                                    @foreach($maritalStatuses as $status)
                                        <option value="{{ $status }}">{{ $status }}</option>
                                    @endforeach
                                </select>
                                @error('marital_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Location Details -->
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title fw-bold text-gray-800">
                            <i class="fas fa-map-marker-alt me-2"></i>Location Details
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Country of Residence -->
                            <div class="col-md-4">
                                <label class="form-label">Country of Residence</label>
                                <input type="text" wire:model="country_of_residence" class="form-control @error('country_of_residence') is-invalid @enderror" placeholder="e.g., Ghana">
                                @error('country_of_residence')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Home Region -->
                            <div class="col-md-4">
                                <label class="form-label">Home Region</label>
                                <input type="text" wire:model="home_region" class="form-control @error('home_region') is-invalid @enderror" placeholder="e.g., Greater Accra">
                                @error('home_region')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Home Town -->
                            <div class="col-md-4">
                                <label class="form-label">Home Town</label>
                                <input type="text" wire:model="home_town" class="form-control @error('home_town') is-invalid @enderror" placeholder="e.g., Accra">
                                @error('home_town')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title fw-bold text-gray-800">
                            <i class="fas fa-address-book me-2"></i>Contact Information
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Email -->
                            <div class="col-md-6">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Mobile Number -->
                            <div class="col-md-6">
                                <label class="form-label">Mobile Number</label>
                                <input type="tel" wire:model="mobile_number" class="form-control @error('mobile_number') is-invalid @enderror">
                                @error('mobile_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- GPS Address -->
                            <div class="col-md-4">
                                <label class="form-label">GPS Address</label>
                                <input type="text" wire:model="gps_address" class="form-control @error('gps_address') is-invalid @enderror" placeholder="e.g., GA-123-4567">
                                @error('gps_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Postal Address -->
                            <div class="col-md-4">
                                <label class="form-label">Postal Address</label>
                                <input type="text" wire:model="postal_address" class="form-control @error('postal_address') is-invalid @enderror" placeholder="e.g., P.O. Box 123">
                                @error('postal_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Residential Address -->
                            <div class="col-md-4">
                                <label class="form-label">Residential Address</label>
                                <input type="text" wire:model="residential_address" class="form-control @error('residential_address') is-invalid @enderror">
                                @error('residential_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Academic Information -->
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title fw-bold text-gray-800">
                            <i class="fas fa-graduation-cap me-2"></i>Academic Information
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Student ID (Optional - will be auto-generated) -->
                            <div class="col-md-4">
                                <label class="form-label">Student ID</label>
                                <input type="text" wire:model="student_id_number" class="form-control @error('student_id_number') is-invalid @enderror" placeholder="Leave blank to auto-generate">
                                @error('student_id_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Leave blank to auto-generate (e.g., STU2025000)</div>
                            </div>

                            <!-- Program -->
                            <div class="col-md-4">
                                <label class="form-label">Program <span class="text-danger">*</span></label>
                                <select wire:model="college_class_id" class="form-select @error('college_class_id') is-invalid @enderror" required>
                                    <option value="">Select Program</option>
                                    @foreach ($classes as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                                    @endforeach
                                </select>
                                @error('college_class_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Cohort -->
                            <div class="col-md-4">
                                <label class="form-label">Cohort <span class="text-danger">*</span></label>
                                <select wire:model="cohort_id" class="form-select @error('cohort_id') is-invalid @enderror" required>
                                    <option value="">Select Cohort</option>
                                    @foreach ($cohorts as $cohort)
                                        <option value="{{ $cohort->id }}">{{ $cohort->name }}</option>
                                    @endforeach
                                </select>
                                @error('cohort_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="col-md-4">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select wire:model="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="">Select Status</option>
                                    @foreach ($statuses as $statusOption)
                                        <option value="{{ $statusOption }}">{{ $statusOption }}</option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="notice d-flex bg-light-info rounded border border-info border-dashed p-6 mt-5">
                            <span class="svg-icon svg-icon-2tx svg-icon-info me-4">
                                <i class="fas fa-info-circle fs-1 text-info"></i>
                            </span>
                            <div class="d-flex flex-stack flex-grow-1">
                                <div class="fw-semibold">
                                    <h4 class="text-gray-900 fw-bold">User Account Creation</h4>
                                    <div class="fs-6 text-gray-700">
                                        A user account will be automatically created for this student with default password "password". 
                                        The student can login and change their password after account creation.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-5">
            <a href="{{ route('students') }}" class="btn btn-light me-3">
                <i class="fas fa-times me-2"></i>Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <span class="indicator-label">
                    <i class="fas fa-save me-2"></i>Create Student
                </span>
                <span class="indicator-progress">
                    Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                </span>
            </button>
        </div>
    </form>
</div>
