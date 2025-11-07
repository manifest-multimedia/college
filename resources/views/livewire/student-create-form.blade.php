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

    <form wire:submit="createStudent">
        <div class="row">
            <!-- Personal Information -->
            <div class="col-lg-6 mb-5">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <div class="card-title">
                            <h3 class="card-title fw-bold text-gray-800">
                                <i class="fas fa-user me-2"></i>Personal Information
                            </h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" id="first_name" class="form-control @error('first_name') is-invalid @enderror" wire:model="first_name" required>
                            @error('first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" id="last_name" class="form-control @error('last_name') is-invalid @enderror" wire:model="last_name" required>
                            @error('last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="other_name" class="form-label">Other Name</label>
                            <input type="text" id="other_name" class="form-control @error('other_name') is-invalid @enderror" wire:model="other_name">
                            @error('other_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="student_id_number" class="form-label">Student ID <span class="text-danger">*</span></label>
                            <input type="text" id="student_id_number" class="form-control @error('student_id_number') is-invalid @enderror" wire:model="student_id_number" required>
                            @error('student_id_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">This must be unique for each student.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" id="email" class="form-control @error('email') is-invalid @enderror" wire:model="email" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="mobile_number" class="form-label">Mobile Number</label>
                            <input type="text" id="mobile_number" class="form-control @error('mobile_number') is-invalid @enderror" wire:model="mobile_number">
                            @error('mobile_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Academic Information -->
            <div class="col-lg-6 mb-5">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <div class="card-title">
                            <h3 class="card-title fw-bold text-gray-800">
                                <i class="fas fa-graduation-cap me-2"></i>Academic Information
                            </h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="college_class_id" class="form-label">Program <span class="text-danger">*</span></label>
                            <select id="college_class_id" class="form-select @error('college_class_id') is-invalid @enderror" wire:model="college_class_id" required>
                                <option value="">Select Program</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                            @error('college_class_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="cohort_id" class="form-label">Cohort <span class="text-danger">*</span></label>
                            <select id="cohort_id" class="form-select @error('cohort_id') is-invalid @enderror" wire:model="cohort_id" required>
                                <option value="">Select Cohort</option>
                                @foreach ($cohorts as $cohort)
                                    <option value="{{ $cohort->id }}">{{ $cohort->name }}</option>
                                @endforeach
                            </select>
                            @error('cohort_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select id="status" class="form-select @error('status') is-invalid @enderror" wire:model="status" required>
                                <option value="">Select Status</option>
                                @foreach ($statuses as $statusOption)
                                    <option value="{{ $statusOption }}">{{ $statusOption }}</option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="notice d-flex bg-light-info rounded border border-info border-dashed p-6 mt-5">
                            <span class="svg-icon svg-icon-2tx svg-icon-info me-4">
                                <i class="fas fa-info-circle fs-1 text-info"></i>
                            </span>
                            <div class="d-flex flex-stack flex-grow-1">
                                <div class="fw-semibold">
                                    <h4 class="text-gray-900 fw-bold">User Account</h4>
                                    <div class="fs-6 text-gray-700">
                                        A user account will be automatically created for this student with a random password. They can reset it using the "Forgot Password" feature.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end gap-2 mt-5">
                    <a href="{{ route('students') }}" class="btn btn-light me-3">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <span class="indicator-label">
                            <i class="fas fa-plus me-2"></i>Create Student
                        </span>
                        <span class="indicator-progress">
                            Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
