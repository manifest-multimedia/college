<div>
    <!-- Heading outside the card with badge -->
    <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 mb-4 mt-2">
        <div class="d-flex align-items-center gap-3">
            <h1 class="text-gray-900 fw-bold fs-2 mb-0">Student Information</h1>
            <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold px-3 py-1.5 rounded-pill fs-7">
                {{ $studentsTotal }} total students
            </span>
        </div>
        @if(count($selectedStudents) > 0)
            <div class="badge bg-success bg-opacity-10 text-success fw-semibold px-3 py-1.5 rounded-pill fs-7 d-flex align-items-center gap-1.5">
                <i class="fas fa-check-circle fs-8 text-success"></i>
                {{ count($selectedStudents) }} student(s) selected
            </div>
        @endif
    </div>

    <!-- Success message -->
    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Error message -->
    @if(session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Info message -->
    @if(session()->has('info'))
        <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Bulk Action Toolbar -->
    @if(count($selectedStudents) > 0)
        <div class="card border-primary border-opacity-25 bg-primary bg-opacity-10 mb-4 shadow-sm">
            <div class="card-body py-3 px-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-check-circle text-primary fs-5"></i>
                        <span class="fw-bold text-gray-900 fs-7">
                            {{ count($selectedStudents) }} student(s) selected
                        </span>
                        <span class="text-muted fs-7 d-none d-sm-inline">Choose an action for the selected records:</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap ms-auto">
                        <button type="button" class="btn btn-sm btn-primary px-3 fw-medium" wire:click="exportStudents">
                            <i class="fas fa-file-export me-1.5 fs-7"></i>
                            Export Selected
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary px-3 fw-medium bg-white" wire:click="$set('selectedStudents', []); $set('selectAll', false)">
                            <i class="fas fa-times me-1.5 fs-7"></i>
                            Clear Selection
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="card mb-xl-10 shadow-sm border-0">
        <!-- Filter toolbar -->
        <div class="card-header border-0 py-3">
            <div class="card-title">
                <h3 class="card-title fw-bold text-gray-800 fs-5 mb-0">
                    <i class="fas fa-users text-primary me-2 fs-5"></i>
                    Students List
                </h3>
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
                    <a href="{{ route('students.import') }}" class="btn btn-sm btn-primary px-3 d-flex align-items-center fw-medium">
                        <i class="fas fa-file-import me-2"></i>
                        Import
                    </a>
                    <button class="btn btn-sm btn-light-primary px-3 d-flex align-items-center fw-medium" wire:click="exportStudents">
                        <i class="fas fa-file-export me-2"></i>
                        Export
                    </button>

                    @if($cohortFilter)
                    <button class="btn btn-sm btn-light-warning px-3 d-flex align-items-center fw-medium" wire:click="confirmIdRegeneration">
                        <i class="fas fa-sync-alt me-2"></i>
                        Regenerate IDs
                    </button>
                    @endif
                   
                </div>
            </div>
        </div>

        <style>
            .student-table-row {
                transition: background-color 0.15s ease-in-out;
            }
            .student-table-row:hover {
                background-color: #F8FAFC !important;
            }
            .student-table-row.selected-row {
                background-color: #EFF6FF !important;
            }
            .student-table-row.selected-row:hover {
                background-color: #DBEAFE !important;
            }
        </style>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
                    <thead style="background-color: #F8FAFC; border-bottom: 2px solid #E2E8F0;">
                        <tr>
                            <th class="text-center align-middle" style="width: 48px; min-width: 48px; max-width: 48px; padding: 12px 16px;">
                                <div class="d-flex justify-content-center align-items-center">
                                    <input type="checkbox" 
                                           class="form-check-input m-0"
                                           style="cursor: pointer; width: 16px; height: 16px;"
                                           wire:model.live="selectAll"
                                           title="Select all students">
                                </div>
                            </th>
                            <th class="align-middle fw-semibold text-secondary text-uppercase" style="width: 150px; min-width: 150px; font-size: 0.75rem; letter-spacing: 0.05em; padding: 12px 16px;">Student ID</th>
                            <th class="align-middle fw-semibold text-secondary text-uppercase" style="min-width: 220px; font-size: 0.75rem; letter-spacing: 0.05em; padding: 12px 16px;">Student Name</th>
                            <th class="align-middle fw-semibold text-secondary text-uppercase" style="min-width: 200px; font-size: 0.75rem; letter-spacing: 0.05em; padding: 12px 16px;">Program</th>
                            <th class="align-middle fw-semibold text-secondary text-uppercase" style="width: 140px; min-width: 140px; font-size: 0.75rem; letter-spacing: 0.05em; padding: 12px 16px;">Cohort</th>
                            <th class="align-middle fw-semibold text-secondary text-uppercase text-center" style="width: 110px; min-width: 110px; font-size: 0.75rem; letter-spacing: 0.05em; padding: 12px 16px;">Status</th>
                            <th class="align-middle fw-semibold text-secondary text-uppercase text-end" style="width: 140px; min-width: 140px; font-size: 0.75rem; letter-spacing: 0.05em; padding: 12px 16px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($students as $student)
                            @php
                                $isSelected = in_array((string)$student->id, $selectedStudents);
                                $statusStr = $student->status ?? 'Active';
                            @endphp
                            <tr class="student-table-row {{ $isSelected ? 'selected-row' : '' }}" style="border-bottom: 1px solid #F1F5F9;">
                                <td class="text-center align-middle" style="width: 48px; min-width: 48px; max-width: 48px; padding: 14px 16px;">
                                    <div class="d-flex justify-content-center align-items-center">
                                        <input type="checkbox" 
                                               class="form-check-input m-0"
                                               style="cursor: pointer; width: 16px; height: 16px;"
                                               value="{{ $student->id }}"
                                               wire:model.live="selectedStudents">
                                    </div>
                                </td>
                                <td class="align-middle" style="padding: 14px 16px;">
                                    <a href="{{ route('students.show', $student->id) }}" class="fw-semibold text-gray-900 text-hover-primary" style="font-variant-numeric: tabular-nums; font-size: 0.875rem; letter-spacing: 0.02em;">
                                        {{ $student->student_id }}
                                    </a>
                                </td>
                                <td class="align-middle" style="padding: 14px 16px;">
                                    <div class="d-flex align-items-center">
                                        @if ($student->profile_photo_url)
                                            <div class="me-3 flex-shrink-0">
                                                <a href="{{ route('students.show', $student->id) }}">
                                                    <img class="rounded-circle" src="{{ $student->profile_photo_url }}" alt="avatar" width="36" height="36">
                                                </a>
                                            </div>
                                        @endif
                                        <div class="overflow-hidden">
                                            <a href="{{ route('students.show', $student->id) }}" class="fw-semibold text-gray-900 text-hover-primary d-block text-truncate fs-7" title="{{ $student->last_name }} {{ $student->first_name }} {{ $student->other_name }}">
                                                {{ $student->last_name }} {{ $student->first_name }} {{ $student->other_name }}
                                            </a>
                                            <div class="text-muted fs-7 text-truncate" style="max-width: 220px;">
                                                <a href="{{ route('students.show', $student->id) }}" class="text-muted text-hover-primary fs-7" title="{{ $student->email }}">
                                                    {{ $student->email }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="align-middle" style="padding: 14px 16px;">
                                    <span class="text-gray-800 fw-medium fs-7">
                                        {{ $student->CollegeClass()->first()?->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="align-middle" style="padding: 14px 16px;">
                                    <span class="text-muted fs-7">
                                        {{ $student->Cohort()->first()->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="text-center align-middle" style="padding: 14px 16px;">
                                    @if($statusStr == 'Active')
                                        <span class="d-inline-flex align-items-center px-2.5 py-1 rounded-pill fw-medium fs-8" style="background: #ECFDF5; color: #047857; border: 1px solid #A7F3D0;">
                                            <span class="rounded-circle me-1.5" style="width: 5px; height: 5px; background-color: #10B981;"></span>
                                            Active
                                        </span>
                                    @elseif($statusStr == 'Inactive')
                                        <span class="d-inline-flex align-items-center px-2.5 py-1 rounded-pill fw-medium fs-8" style="background: #FEF2F2; color: #B91C1C; border: 1px solid #FECACA;">
                                            <span class="rounded-circle me-1.5" style="width: 5px; height: 5px; background-color: #EF4444;"></span>
                                            Inactive
                                        </span>
                                    @elseif($statusStr == 'Pending')
                                        <span class="d-inline-flex align-items-center px-2.5 py-1 rounded-pill fw-medium fs-8" style="background: #FFFBEB; color: #B45309; border: 1px solid #FDE68A;">
                                            <span class="rounded-circle me-1.5" style="width: 5px; height: 5px; background-color: #F59E0B;"></span>
                                            Pending
                                        </span>
                                    @elseif($statusStr == 'Graduated')
                                        <span class="d-inline-flex align-items-center px-2.5 py-1 rounded-pill fw-medium fs-8" style="background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE;">
                                            <span class="rounded-circle me-1.5" style="width: 5px; height: 5px; background-color: #3B82F6;"></span>
                                            Graduated
                                        </span>
                                    @elseif($statusStr == 'Suspended')
                                        <span class="d-inline-flex align-items-center px-2.5 py-1 rounded-pill fw-medium fs-8" style="background: #F3E8FF; color: #6B21A8; border: 1px solid #E9D5FF;">
                                            <span class="rounded-circle me-1.5" style="width: 5px; height: 5px; background-color: #A855F7;"></span>
                                            Suspended
                                        </span>
                                    @else
                                        <span class="d-inline-flex align-items-center px-2.5 py-1 rounded-pill fw-medium fs-8" style="background: #F1F5F9; color: #475569; border: 1px solid #CBD5E1;">
                                            <span class="rounded-circle me-1.5" style="width: 5px; height: 5px; background-color: #64748B;"></span>
                                            {{ $statusStr }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end align-middle" style="padding: 14px 16px;">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary border-0 px-2.5 py-1 rounded-2 text-gray-700" type="button" id="dropdownMenuButton{{ $student->id }}" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.8125rem;">
                                            Actions
                                            <i class="fas fa-chevron-down ms-1.5 fs-8 text-muted"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-gray-200" aria-labelledby="dropdownMenuButton{{ $student->id }}">
                                            <li><a class="dropdown-item py-2 fs-7" href="{{ route('students.edit', $student->id) }}"><i class="fas fa-edit me-2 text-primary"></i>Edit</a></li>
                                            <li><a class="dropdown-item py-2 fs-7" href="{{ route('students.show', $student->id) }}"><i class="fas fa-eye me-2 text-info"></i>View</a></li>
                                            <li><hr class="dropdown-divider my-1"></li>
                                            <li>
                                                <a class="dropdown-item py-2 fs-7 text-danger" href="#" 
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
                                <td colspan="7" class="text-center py-5">
                                    <div class="fs-6 fw-semibold text-muted">
                                        No students found.<br />
                                        <a class="mt-3 btn btn-sm btn-primary" href="/students/create">Add New Student</a>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
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
