<div>
    <!-- Heading outside the card with badge -->
    <div class="mb-3 mt-20 mb-10">
        <h1 class="text-gray-900 fw-bold">Student Information 
            <span class="badge bg-primary rounded-pill ms-2 text-white px-3 py-2 fs-6">{{ $studentsTotal }}</span>
        </h1>
    </div>

    <!-- Success message -->
    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Error message -->
    @if(session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Info message -->
    @if(session()->has('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card mb-xl-10">
        <!-- Filter toolbar -->
        <div class="card-header border-0 py-3">
            <div class="card-title">
                <h3 class="card-title fw-bold text-gray-800">Students List</h3>
            </div>
            <div class="d-flex flex-column flex-md-row align-items-md-center gap-3">
                <!-- Search box -->
                <div class="position-relative me-md-3 flex-grow-1" style="max-width: 300px;">
                    <span class="position-absolute top-50 translate-middle-y ms-3 text-muted">
                        <i class="fas fa-search fs-5"></i>
                    </span>
                    <input type="text" class="form-control form-control-sm form-control-solid ps-8" 
                           placeholder="Search students..." 
                           wire:model.live.debounce.500ms="search">
                </div>
                
                <!-- Program Filter -->
                <div class="me-md-3" style="min-width: 170px;">
                    <select class="form-select form-select-sm form-select-solid" 
                        wire:model.live="programFilter">
                        <option value="">All Programs</option>
                        @foreach ($programs as $program)
                            <option value="{{ $program->id }}">{{ $program->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Cohort Filter -->
                <div class="me-md-3" style="min-width: 170px;">
                    <select class="form-select form-select-sm form-select-solid" 
                        wire:model.live="cohortFilter">
                        <option value="">All Cohorts</option>
                        @foreach ($cohorts as $cohort)
                            <option value="{{ $cohort->id }}">{{ $cohort->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Action buttons - using flex-fill on mobile to space them out -->
                <div class="d-flex gap-2 flex-md-nowrap ms-auto">
                    <a href="{{ route('students.import') }}" class="btn btn-sm btn-primary px-3 d-flex align-items-center">
                        <i class="fas fa-file-import me-2"></i>
                        Import
                    </a>
                    <button class="btn btn-sm btn-light-primary px-3 d-flex align-items-center" wire:click="exportStudents">
                        <i class="fas fa-file-export me-2"></i>
                        Export
                    </button>

                    @if($cohortFilter)
                    <button class="btn btn-sm btn-light-warning px-3 d-flex align-items-center" wire:click="confirmIdRegeneration">
                        <i class="fas fa-sync-alt me-2"></i>
                        Regenerate IDs
                    </button>
                    @endif
                   
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <table class="table align-middle table-row-dashed table-row-gray-300 gs-0 gy-4">
                <thead>
                    <tr class="fw-bold text-gray-800 border-bottom-2 border-gray-200">
                        <th class="ps-4 min-w-100px">Student ID</th>
                        <th class="min-w-150px">Student Name</th>
                        <th class="min-w-100px">Program</th>
                        <th class="min-w-100px">Cohort</th>
                        <th class="min-w-100px">Status</th>
                        <th class="text-center min-w-100px pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($students as $student)
                        <tr>
                            <td class="ps-4">
                                <a href="{{ route('students.show', $student->id) }}" class="text-gray-800 text-hover-primary fw-bold">
                                    {{ $student->student_id }}
                                </a>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if ($student->profile_photo_url)
                                        <div class="me-3">
                                            <a href="{{ route('students.show', $student->id) }}">
                                                <img class="rounded-circle" src="{{ $student->profile_photo_url }}" alt="avatar" width="40" height="40">
                                            </a>
                                        </div>
                                    @endif
                                    <div>
                                        <a href="{{ route('students.show', $student->id) }}" class="text-gray-800 text-hover-primary fw-bold">
                                            {{ $student->last_name }} {{ $student->first_name }} {{ $student->other_name }}
                                        </a>
                                        <div class="text-gray-600 fs-7">
                                            <a href="{{ route('students.show', $student->id) }}" class="text-gray-600 text-hover-primary">
                                                {{ $student->email }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $student->CollegeClass()->first()?->name }}</td>
                            <td>{{ $student->Cohort()->first()->name ?? 'N/A' }}</td>
                            <td>
                                @if($student->status == 'Active')
                                    <span class="badge badge-light-success">{{ $student->status ?? 'Active' }}</span>
                                @elseif($student->status == 'Inactive')
                                    <span class="badge badge-light-danger">{{ $student->status }}</span>
                                @elseif($student->status == 'Pending')
                                    <span class="badge badge-light-warning">{{ $student->status }}</span>
                                @else
                                    <span class="badge badge-light-secondary">{{ $student->status ?? 'Unknown' }}</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light-primary btn-active-light-primary" type="button" id="dropdownMenuButton{{ $student->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                        Actions
                                        <i class="fas fa-chevron-down ms-2 fs-7"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton{{ $student->id }}">
                                        <li><a class="dropdown-item" href="{{ route('students.edit', $student->id) }}"><i class="fas fa-edit me-2 text-primary"></i>Edit</a></li>
                                        <li><a class="dropdown-item" href="{{ route('students.show', $student->id) }}"><i class="fas fa-eye me-2 text-info"></i>View</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="#" 
                                               wire:click.prevent="confirmStudentDeletion({{ $student->id }})">
                                               <i class="fas fa-trash-alt me-2"></i>Delete
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    
                    @if(count($students) == 0)
                        <tr>
                            <td colspan="6" class="text-center">
                                <div class="pt-10 pb-10 fs-6 fw-bold">
                                    No students found.<br />
                                    <a class="mt-5 btn btn-sm btn-primary" href="/students/create">Add New Student</a>
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-center py-3">
            {{ $students->links() }}
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    @if($confirmingStudentDeletion)
    <div class="modal fade show" tabindex="-1" style="display: block; background-color: rgba(0,0,0,0.5);" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Delete Student
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="cancelStudentDeletion" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <p class="fs-5 text-gray-800 mb-0">
                        Are you sure you want to delete this student? This action cannot be undone.
                    </p>
                    <div class="alert alert-warning mt-4 mb-0">
                        <div class="d-flex">
                            <i class="fas fa-info-circle fs-4 me-3"></i>
                            <div>
                                <p class="mb-1">This will remove the student from the system including:</p>
                                <ul class="mb-0 ps-3">
                                    <li>Student academic records</li>
                                    <li>Course registrations</li>
                                    <li>Financial records</li>
                                    <li>Associated documents</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" wire:click="cancelStudentDeletion">
                        <i class="fas fa-times me-1"></i>
                        Cancel
                    </button>
                    <button type="button" class="btn btn-danger" wire:click="deleteStudent" wire:loading.attr="disabled">
                        <i class="fas fa-trash-alt me-1"></i>
                        <span wire:loading.remove>Delete Student</span>
                        <span wire:loading wire:target="deleteStudent">Deleting...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- ID Regeneration Confirmation Modal -->
    @if($confirmingIdRegeneration)
    <div class="modal fade show" tabindex="-1" style="display: block; background-color: rgba(0,0,0,0.5);" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Regenerate Student IDs
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="cancelIdRegeneration" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <p class="fs-5 text-gray-800 mb-0">
                        Are you sure you want to regenerate IDs for the selected cohort?
                    </p>
                    <div class="alert alert-danger mt-4 mb-0">
                        <div class="d-flex">
                            <i class="fas fa-exclamation-circle fs-4 me-3"></i>
                            <div>
                                <p class="mb-1 fw-bold">Warning: This is a destructive action!</p>
                                <p class="mb-0">All students in this cohort will be assigned NEW Student IDs based on the current configuration. Existing IDs will be overwritten.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" wire:click="cancelIdRegeneration">
                        <i class="fas fa-times me-1"></i>
                        Cancel
                    </button>
                    <button type="button" class="btn btn-warning" wire:click="regenerateIds" wire:loading.attr="disabled">
                        <i class="fas fa-sync-alt me-1"></i>
                        <span wire:loading.remove>Regenerate IDs</span>
                        <span wire:loading wire:target="regenerateIds">Processing...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Export Format Selection Modal -->
    @if($showingExportModal)
    <div class="modal fade show" tabindex="-1" style="display: block; background-color: rgba(0,0,0,0.5);" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-file-export me-2"></i>
                        Export Students
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="cancelExport" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <p class="fs-5 text-gray-800 mb-4">
                        Please select your preferred export format:
                    </p>
                    
                    <div class="d-flex flex-column gap-3">
                        <!-- Excel Option -->
                        <div class="form-check form-check-custom form-check-solid">
                            <input class="form-check-input" type="radio" value="excel" id="export_excel" wire:model.live="exportFormat">
                            <label class="form-check-label d-flex align-items-center" for="export_excel">
                                <span class="symbol symbol-30px me-3">
                                    <i class="fas fa-file-excel text-success fs-1"></i>
                                </span>
                                <div>
                                    <span class="fw-bold d-block">Excel (.xlsx)</span>
                                    <span class="text-muted">Export to Microsoft Excel spreadsheet format</span>
                                </div>
                            </label>
                        </div>
                        
                        <!-- PDF Option -->
                        <div class="form-check form-check-custom form-check-solid">
                            <input class="form-check-input" type="radio" value="pdf" id="export_pdf" wire:model="exportFormat">
                            <label class="form-check-label d-flex align-items-center" for="export_pdf">
                                <span class="symbol symbol-30px me-3">
                                    <i class="fas fa-file-pdf text-danger fs-1"></i>
                                </span>
                                <div>
                                    <span class="fw-bold d-block">PDF (.pdf)</span>
                                    <span class="text-muted">Export to Portable Document Format</span>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-4 mb-0">
                        <div class="d-flex">
                            <i class="fas fa-info-circle fs-4 me-3"></i>
                            <div>
                                <p class="mb-0">The export will include {{ $studentsTotal }} student records based on your current filters.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" wire:click="cancelExport">
                        <i class="fas fa-times me-1"></i>
                        Cancel
                    </button>
                    <button type="button" class="btn btn-primary" wire:click="processExport" wire:loading.attr="disabled" @if(!$exportFormat) disabled @endif>
                        <i class="fas fa-file-export me-1"></i>
                        <span wire:loading.remove>Export</span>
                        <span wire:loading wire:target="processExport">Exporting...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
