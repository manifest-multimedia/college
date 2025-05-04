<div>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card my-4">
                    <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                        <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3 d-flex justify-content-between align-items-center">
                            <h6 class="text-white text-capitalize ps-3 mb-0">Fee Structure Management</h6>
                        </div>
                    </div>
                    <div class="card-body px-0 pb-2">
                        <!-- Flash Messages -->
                        @if (session()->has('message'))
                            <div class="alert alert-success mx-3">
                                {{ session('message') }}
                            </div>
                        @endif

                        @if (session()->has('error'))
                            <div class="alert alert-danger mx-3">
                                {{ session('error') }}
                            </div>
                        @endif

                        <div class="row px-3">
                            <div class="col-md-6">
                                <!-- Search Input -->
                                <div class="input-group mb-3">
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
                        <div class="row px-3 mb-3">
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
                            <div class="col-md-3">
                                <button wire:click="resetFilters" class="btn btn-outline-secondary">
                                    <i class="fas fa-filter"></i> Reset Filters
                                </button>
                            </div>
                        </div>
                        
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Fee Type</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Class</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Academic Year</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Semester</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Amount</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Mandatory</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                        <th class="text-secondary opacity-7">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($feeStructures as $feeStructure)
                                        <tr>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">{{ $feeStructure->feeType->name }}</h6>
                                                        <p class="text-xs text-secondary mb-0">{{ $feeStructure->feeType->code }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">{{ $feeStructure->collegeClass->name }}</p>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">{{ $feeStructure->academicYear->name }}</p>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">{{ $feeStructure->semester->name }}</p>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">{{ number_format($feeStructure->amount, 2) }}</p>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="badge badge-sm {{ $feeStructure->is_mandatory ? 'bg-gradient-info' : 'bg-gradient-secondary' }}">
                                                    {{ $feeStructure->is_mandatory ? 'Yes' : 'No' }}
                                                </span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="badge badge-sm {{ $feeStructure->is_active ? 'bg-gradient-success' : 'bg-gradient-secondary' }}">
                                                    {{ $feeStructure->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td class="align-middle">
                                                <button wire:click="editFeeStructure({{ $feeStructure->id }})" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#feeStructureFormModal">
                                                    <i class="fas fa-edit text-white"></i>
                                                </button>
                                                <button wire:click="confirmFeeStructureDeletion({{ $feeStructure->id }})" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash text-white"></i>
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
                        
                        <div class="px-3 mt-3">
                            {{ $feeStructures->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Fee Structure Form Modal -->
    <div wire:ignore.self class="modal fade" id="feeStructureFormModal" tabindex="-1" aria-labelledby="feeStructureFormModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="feeStructureFormModalLabel">
                        {{ $editingFeeStructureId ? 'Edit Fee Structure' : 'Add New Fee Structure' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="saveFeeStructure">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="fee_type_id" class="form-label">Fee Type</label>
                                <select class="form-select" id="fee_type_id" wire:model="fee_type_id">
                                    <option value="">Select Fee Type</option>
                                    @foreach($feeTypes as $feeType)
                                        <option value="{{ $feeType->id }}">{{ $feeType->name }}</option>
                                    @endforeach
                                </select>
                                @error('fee_type_id') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="college_class_id" class="form-label">Class</label>
                                <select class="form-select" id="college_class_id" wire:model="college_class_id">
                                    <option value="">Select Class</option>
                                    @foreach($collegeClasses as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                                    @endforeach
                                </select>
                                @error('college_class_id') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="academic_year_id" class="form-label">Academic Year</label>
                                <select class="form-select" id="academic_year_id" wire:model="academic_year_id">
                                    <option value="">Select Academic Year</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                                    @endforeach
                                </select>
                                @error('academic_year_id') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="semester_id" class="form-label">Semester</label>
                                <select class="form-select" id="semester_id" wire:model="semester_id">
                                    <option value="">Select Semester</option>
                                    @foreach($semesters as $semester)
                                        <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                                    @endforeach
                                </select>
                                @error('semester_id') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label for="amount" class="form-label">Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" class="form-control" id="amount" wire:model="amount">
                                </div>
                                @error('amount') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="is_mandatory" wire:model="is_mandatory">
                                <label class="form-check-label" for="is_mandatory">Mandatory</label>
                                @error('is_mandatory') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="is_active" wire:model="is_active">
                                <label class="form-check-label" for="is_active">Active</label>
                                @error('is_active') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" wire:click="cancelEdit">Cancel</button>
                            <button type="submit" class="btn btn-primary">{{ $editingFeeStructureId ? 'Update' : 'Save' }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div wire:ignore.self class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this fee structure? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" wire:click="deleteFeeStructure">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Alpine.js for handling modal state -->
    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('show-delete-modal', () => {
                let deleteModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
                deleteModal.show();
            });
        });
    </script>
</div>