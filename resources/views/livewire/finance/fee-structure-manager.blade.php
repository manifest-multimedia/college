<div>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card my-4">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Fee Structure Management</h6>
                    </div>
                    <div class="card-body">
                        <!-- Flash Messages -->
                        @if (session()->has('message'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('message') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if (session()->has('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <!-- Search Input -->
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input wire:model.live="search" type="text" class="form-control" placeholder="Search fee structures...">
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#feeStructureFormModal">
                                    <i class="fas fa-plus"></i> Add New Fee Structure
                                </button>
                            </div>
                        </div>

                        <!-- Filters -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <select wire:model.live="selectedClass" class="form-select">
                                    <option value="">All Classes</option>
                                    @foreach($collegeClasses as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select wire:model.live="selectedYear" class="form-select">
                                    <option value="">All Academic Years</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select wire:model.live="selectedSemester" class="form-select">
                                    <option value="">All Semesters</option>
                                    @foreach($semesters as $semester)
                                        <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 text-end">
                                <button wire:click="resetFilters" class="btn btn-outline-secondary">
                                    <i class="fas fa-filter"></i> Reset Filters
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Fee Type</th>
                                        <th>Class</th>
                                        <th>Academic Year</th>
                                        <th>Semester</th>
                                        <th>Amount</th>
                                        <th class="text-center">Mandatory</th>
                                        <th class="text-center">Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($feeStructures as $feeStructure)
                                        <tr>
                                            <td>{{ $feeStructure->feeType->name }}</td>
                                            <td>{{ $feeStructure->collegeClass->name }}</td>
                                            <td>{{ $feeStructure->academicYear->name }}</td>
                                            <td>{{ $feeStructure->semester->name }}</td>
                                            <td>${{ number_format($feeStructure->amount, 2) }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-{{ $feeStructure->is_mandatory ? 'info' : 'secondary' }}">
                                                    {{ $feeStructure->is_mandatory ? 'Yes' : 'No' }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-{{ $feeStructure->is_active ? 'success' : 'secondary' }}">
                                                    {{ $feeStructure->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td>
                                                <button wire:click="editFeeStructure({{ $feeStructure->id }})" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#feeStructureFormModal">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button wire:click="confirmFeeStructureDeletion({{ $feeStructure->id }})" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">No fee structures found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $feeStructures->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Fee Structure Form Modal -->
    <div class="modal fade" id="feeStructureFormModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">{{ $editingFeeStructureId ? 'Edit' : 'Add New' }} Fee Structure</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label for="fee_type_id" class="form-label">Fee Type</label>
                            <select wire:model="fee_type_id" id="fee_type_id" class="form-select @error('fee_type_id') is-invalid @enderror">
                                <option value="">Select Fee Type</option>
                                @foreach($feeTypes as $feeType)
                                    <option value="{{ $feeType->id }}">{{ $feeType->name }}</option>
                                @endforeach
                            </select>
                            @error('fee_type_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="college_class_id" class="form-label">Class</label>
                            <select wire:model="college_class_id" id="college_class_id" class="form-select @error('college_class_id') is-invalid @enderror">
                                <option value="">Select Class</option>
                                @foreach($collegeClasses as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                            @error('college_class_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="academic_year_id" class="form-label">Academic Year</label>
                            <select wire:model="academic_year_id" id="academic_year_id" class="form-select @error('academic_year_id') is-invalid @enderror">
                                <option value="">Select Academic Year</option>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}">{{ $year->name }}</option>
                                @endforeach
                            </select>
                            @error('academic_year_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="semester_id" class="form-label">Semester</label>
                            <select wire:model="semester_id" id="semester_id" class="form-select @error('semester_id') is-invalid @enderror">
                                <option value="">Select Semester</option>
                                @foreach($semesters as $semester)
                                    <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                                @endforeach
                            </select>
                            @error('semester_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="amount" class="form-label">Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" wire:model="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" step="0.01" min="0">
                            </div>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" wire:model="is_mandatory" id="is_mandatory" class="form-check-input">
                            <label for="is_mandatory" class="form-check-label">Mandatory Fee</label>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" wire:model="is_active" id="is_active" class="form-check-input">
                            <label for="is_active" class="form-check-label">Active</label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="cancelEdit">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="saveFeeStructure">
                        {{ $editingFeeStructureId ? 'Update' : 'Save' }} Fee Structure
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this fee structure? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="deleteFeeStructure">Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>
