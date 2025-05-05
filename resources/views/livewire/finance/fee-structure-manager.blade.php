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

    <!-- Modals remain unchanged -->
</div>
