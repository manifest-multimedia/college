<div>
    <!-- Compact Professional Page Header -->
    <div class="mb-5">
        <h1 class="text-gray-900 fw-bold mb-1" style="font-size: 1.5rem; letter-spacing: -0.01em;">Student Information</h1>
        <p class="text-muted fs-7 mb-0">Manage student records, admissions and academic information.</p>
    </div>

    <!-- Green Module Identity Banner -->
    <div class="mb-6 rounded-3 shadow-sm px-6 py-5 position-relative overflow-hidden" 
         style="background: linear-gradient(90deg, #10B981 0%, #059669 100%); border-radius: 12px; min-height: 96px;">
        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 position-relative" style="z-index: 2;">
            <div>
                <h2 class="text-white fw-bold mb-1" style="font-size: 1.35rem; letter-spacing: -0.01em; line-height: 1.3;">
                    Manage Student Admissions, Generate IDs, and Access Student Information
                </h2>
                <p class="text-white opacity-85 fs-7 mb-0 fw-medium">
                    Manage admissions, student records and identification from one workspace.
                </p>
            </div>
            <div class="d-none d-md-flex align-items-center justify-content-center flex-shrink-0 ms-4">
                <div class="rounded-circle bg-white bg-opacity-20 text-white d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="fas fa-id-card fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-5" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-5" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session()->has('info'))
        <div class="alert alert-info alert-dismissible fade show shadow-sm mb-5" role="alert">
            <i class="fas fa-info-circle me-2"></i>
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Single Cohesive Workspace Container -->
    <div class="card mb-xl-10 shadow-sm border-0 rounded-3">
        <!-- Toolbar & Filter Surface -->
        <div class="p-4 p-md-5 border-bottom border-gray-200">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                
                <!-- Title & Count Badge -->
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <h3 class="fw-bold text-gray-900 fs-4 mb-0">Students</h3>
                    <span class="text-muted fs-5 fw-normal">·</span>
                    <span class="badge bg-light text-gray-700 fw-semibold fs-7 px-2.5 py-1 border border-gray-300 rounded-2" style="font-variant-numeric: tabular-nums;">
                        {{ number_format($studentsTotal) }}
                    </span>
                    @if(count($selectedStudents) > 0)
                        <span class="badge bg-light-primary text-primary fw-semibold fs-7 px-2.5 py-1 border border-primary border-opacity-25 rounded-2 ms-1">
                            {{ count($selectedStudents) }} selected
                        </span>
                    @endif
                </div>

                <!-- Desktop Single-Row Filter System -->
                <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3 flex-grow-1 justify-content-lg-end">
                    
                    <!-- Search Input (Flexible 40-50%) -->
                    <div class="position-relative flex-grow-1" style="max-width: 420px; min-width: 220px;">
                        <span class="position-absolute top-50 translate-middle-y ms-3 text-muted">
                            <i class="fas fa-search fs-6"></i>
                        </span>
                        <input type="text" 
                               class="form-control form-control-solid ps-9" 
                               style="height: 42px; border-radius: 8px; font-size: 0.875rem;"
                               placeholder="Search by ID, name or email..." 
                               wire:model.live.debounce.400ms="search">
                        @if($search)
                            <button type="button" 
                                    class="btn btn-sm btn-icon position-absolute top-50 end-0 translate-middle-y me-2 text-muted text-hover-primary border-0" 
                                    wire:click="$set('search', '')">
                                <i class="fas fa-times fs-7"></i>
                            </button>
                        @endif
                    </div>

                    <!-- Programme Filter (220-280px) -->
                    <div style="min-width: 200px; max-width: 260px;" class="flex-grow-1 flex-sm-grow-0">
                        <select class="form-select form-select-solid" 
                                style="height: 42px; border-radius: 8px; font-size: 0.875rem;" 
                                wire:model.live="programFilter">
                            <option value="">All Programmes</option>
                            @foreach ($programs as $program)
                                <option value="{{ $program->id }}">{{ $program->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Cohort Filter (180-240px) -->
                    <div style="min-width: 180px; max-width: 220px;" class="flex-grow-1 flex-sm-grow-0">
                        <select class="form-select form-select-solid" 
                                style="height: 42px; border-radius: 8px; font-size: 0.875rem;" 
                                wire:model.live="cohortFilter">
                            <option value="">All Cohorts</option>
                            @foreach ($cohorts as $cohort)
                                <option value="{{ $cohort->id }}">{{ $cohort->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tertiary Export & Actions -->
                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                        <button type="button" 
                                class="btn btn-light-secondary border border-gray-300 text-gray-700 fw-medium d-inline-flex align-items-center px-3" 
                                style="height: 42px; border-radius: 8px; font-size: 0.875rem;"
                                wire:click="exportStudents">
                            <i class="fas fa-file-export me-1.5 text-muted fs-7"></i>
                            Export
                        </button>

                        @if($cohortFilter)
                            <button type="button" 
                                    class="btn btn-light-warning border border-warning border-opacity-50 text-warning-dark fw-medium d-inline-flex align-items-center px-3" 
                                    style="height: 42px; border-radius: 8px; font-size: 0.875rem;"
                                    wire:click="confirmIdRegeneration">
                                <i class="fas fa-sync-alt me-1.5 fs-7"></i>
                                Regenerate IDs
                            </button>
                        @endif
                    </div>

                </div>

            </div>

            <!-- Contextual Selected Action Bar -->
            @if(count($selectedStudents) > 0)
                <div class="mt-3 pt-3 border-top border-gray-200 d-flex align-items-center justify-content-between bg-light-primary rounded-2 px-3 py-2">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-check-circle text-primary fs-6"></i>
                        <span class="fw-semibold text-gray-800 fs-7">
                            {{ count($selectedStudents) }} student(s) selected
                        </span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-primary py-1 px-3 fs-7 fw-semibold" wire:click="exportStudents">
                            Export Selected
                        </button>
                        <button type="button" class="btn btn-sm btn-light py-1 px-3 fs-7 fw-semibold text-gray-700" wire:click="$set('selectedStudents', []); $set('selectAll', false)">
                            Clear
                        </button>
                    </div>
                </div>
            @endif
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
            .btn-table-action {
                background-color: #F8FAFC !important;
                color: #334155 !important;
                border: 1px solid #CBD5E1 !important;
                font-size: 0.8125rem !important;
                font-weight: 600 !important;
                transition: all 0.15s ease-in-out !important;
            }
            .btn-table-action i {
                color: #64748B !important;
                transition: color 0.15s ease-in-out !important;
            }
            .btn-table-action:hover, 
            .btn-table-action:focus, 
            .btn-table-action:active,
            .show > .btn-table-action {
                background-color: #2563EB !important;
                color: #FFFFFF !important;
                border-color: #1D4ED8 !important;
            }
            .btn-table-action:hover i, 
            .btn-table-action:focus i, 
            .btn-table-action:active i,
            .show > .btn-table-action i {
                color: #FFFFFF !important;
            }

            .badge-light-success {
                background-color: #E8FFF3 !important;
                color: #50CD89 !important;
                border: none !important;
            }
            .badge-light-danger {
                background-color: #FFF5F8 !important;
                color: #F1416C !important;
                border: none !important;
            }
            .badge-light-warning {
                background-color: #FFF8DD !important;
                color: #F1C40F !important;
                border: none !important;
            }
            .badge-light-info {
                background-color: #F8F5FF !important;
                color: #7239EA !important;
                border: none !important;
            }
            .badge-light-primary {
                background-color: #F1FAFF !important;
                color: #009EF7 !important;
                border: none !important;
            }
            .badge-light-secondary {
                background-color: #F5F8FA !important;
                color: #7E8299 !important;
                border: none !important;
            }
        </style>

        <!-- Table Surface -->
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
                            <th class="align-middle fw-bold text-uppercase" style="width: 150px; min-width: 150px; font-size: 0.75rem; letter-spacing: 0.05em; padding: 12px 16px; color: #475569;">Student ID</th>
                            <th class="align-middle fw-bold text-uppercase" style="min-width: 240px; font-size: 0.75rem; letter-spacing: 0.05em; padding: 12px 16px; color: #475569;">Student Name</th>
                            <th class="align-middle fw-bold text-uppercase" style="min-width: 220px; font-size: 0.75rem; letter-spacing: 0.05em; padding: 12px 16px; color: #475569;">Programme</th>
                            <th class="align-middle fw-bold text-uppercase" style="width: 140px; min-width: 140px; font-size: 0.75rem; letter-spacing: 0.05em; padding: 12px 16px; color: #475569;">Cohort</th>
                            <th class="align-middle fw-bold text-uppercase text-center" style="width: 110px; min-width: 110px; font-size: 0.75rem; letter-spacing: 0.05em; padding: 12px 16px; color: #475569;">Status</th>
                            <th class="align-middle fw-bold text-uppercase text-end" style="width: 110px; min-width: 110px; font-size: 0.75rem; letter-spacing: 0.05em; padding: 12px 16px; color: #475569;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($students as $student)
                            @php
                                $isSelected = in_array((string)$student->id, $selectedStudents);
                                $statusStr = $student->status ?? 'Active';
                            @endphp
                            <tr class="student-table-row {{ $isSelected ? 'selected-row' : '' }}" style="border-bottom: 1px solid #F1F5F9; height: 56px;">
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
                                                    <img class="rounded-circle" src="{{ $student->profile_photo_url }}" alt="avatar" width="34" height="34">
                                                </a>
                                            </div>
                                        @endif
                                        <div class="overflow-hidden">
                                            <a href="{{ route('students.show', $student->id) }}" class="fw-bold text-gray-900 text-hover-primary d-block text-truncate fs-7" title="{{ $student->last_name }} {{ $student->first_name }} {{ $student->other_name }}">
                                                {{ $student->last_name }} {{ $student->first_name }} {{ $student->other_name }}
                                            </a>
                                            <div class="text-muted fs-8 text-truncate" style="max-width: 240px;">
                                                {{ $student->email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="align-middle" style="padding: 14px 16px;">
                                    <span class="text-gray-800 fw-normal fs-7">
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
                                        <span class="badge badge-light-success px-3 py-1.5 fs-7 fw-bold rounded-pill">Active</span>
                                    @elseif($statusStr == 'Inactive')
                                        <span class="badge badge-light-danger px-3 py-1.5 fs-7 fw-bold rounded-pill">Inactive</span>
                                    @elseif($statusStr == 'Pending')
                                        <span class="badge badge-light-warning px-3 py-1.5 fs-7 fw-bold rounded-pill">Pending</span>
                                    @elseif($statusStr == 'Graduated')
                                        <span class="badge badge-light-info px-3 py-1.5 fs-7 fw-bold rounded-pill">Graduated</span>
                                    @elseif($statusStr == 'Suspended')
                                        <span class="badge badge-light-primary px-3 py-1.5 fs-7 fw-bold rounded-pill">Suspended</span>
                                    @else
                                        <span class="badge badge-light-secondary px-3 py-1.5 fs-7 fw-bold rounded-pill">{{ $statusStr }}</span>
                                    @endif
                                </td>
                                <td class="text-end align-middle" style="padding: 14px 16px;">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light border border-gray-300 rounded-2 px-2.5 py-1 text-gray-700 fw-medium fs-7" type="button" id="dropdownMenuButton{{ $student->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                            Actions
                                            <i class="fas fa-chevron-down ms-1 fs-8 text-muted"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-gray-200 fs-7" aria-labelledby="dropdownMenuButton{{ $student->id }}">
                                            <li><a class="dropdown-item py-2" href="{{ route('students.show', $student->id) }}"><i class="fas fa-eye me-2 text-info fs-7"></i>View Details</a></li>
                                            <li><a class="dropdown-item py-2" href="{{ route('students.edit', $student->id) }}"><i class="fas fa-edit me-2 text-primary fs-7"></i>Edit Student</a></li>
                                            <li><hr class="dropdown-divider my-1"></li>
                                            <li>
                                                <a class="dropdown-item py-2 text-danger" href="#" 
                                                   wire:click.prevent="confirmStudentDeletion({{ $student->id }})">
                                                   <i class="fas fa-trash-alt me-2 fs-7"></i>Delete Student
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        
                        <!-- Empty State A: Filter/Search returns no matches -->
                        @if(count($students) == 0 && ($search != '' || $programFilter != '' || $cohortFilter != ''))
                            <tr>
                                <td colspan="7" class="p-0 border-0">
                                    <div class="d-flex flex-column align-items-center justify-content-center text-center py-12 px-4" style="min-height: 220px;">
                                        <div class="d-flex align-items-center justify-content-center rounded-3 mb-3" style="width: 52px; height: 52px; background-color: #EFF6FF; border: 1px solid #BFDBFE;">
                                            <i class="fas fa-search fs-3 text-primary"></i>
                                        </div>
                                        <h4 class="fw-bold text-gray-900 mb-1" style="font-size: 1.125rem;">No matching students</h4>
                                        <p class="text-muted fs-7 mb-4 mx-auto" style="max-width: 440px; line-height: 1.5;">
                                            We couldn't find any students matching your current search criteria or filters.
                                        </p>
                                        <button type="button" class="btn btn-sm btn-light-primary border border-primary border-opacity-25 fw-semibold px-4 py-2.5 d-inline-flex align-items-center gap-2" style="height: 42px; border-radius: 8px;" wire:click="resetFilters">
                                            <i class="fas fa-undo fs-7"></i>
                                            Clear filters
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endif

                        <!-- Empty State B: No students exist in the system at all -->
                        @if(count($students) == 0 && $search == '' && $programFilter == '' && $cohortFilter == '')
                            <tr>
                                <td colspan="7" class="p-0 border-0">
                                    <div class="d-flex flex-column align-items-center justify-content-center text-center py-12 px-4" style="min-height: 240px;">
                                        <div class="d-flex align-items-center justify-content-center rounded-3 mb-3" style="width: 52px; height: 52px; background-color: #EFF6FF; border: 1px solid #BFDBFE;">
                                            <i class="fas fa-user-graduate fs-3 text-primary"></i>
                                        </div>
                                        <h4 class="fw-bold text-gray-900 mb-1" style="font-size: 1.125rem;">No students yet</h4>
                                        <p class="text-muted fs-7 mb-4 mx-auto" style="max-width: 440px; line-height: 1.5;">
                                            Add your first student record or import existing student data to get started.
                                        </p>
                                        <div class="d-flex align-items-center justify-content-center gap-3 flex-wrap">
                                            <a href="{{ route('students.create') }}" class="btn btn-sm btn-primary fw-semibold px-4 py-2.5 d-inline-flex align-items-center gap-2" style="height: 42px; border-radius: 8px;">
                                                <i class="fas fa-plus fs-7"></i>
                                                Add Student
                                            </a>
                                            <a href="{{ route('students.import') }}" class="btn btn-sm btn-light-primary border border-primary border-opacity-25 fw-semibold px-4 py-2.5 d-inline-flex align-items-center gap-2" style="height: 42px; border-radius: 8px;">
                                                <i class="fas fa-file-import fs-7"></i>
                                                Import Students
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer Pagination Surface (Option B: Hidden when 0 records exist) -->
        @if($students->total() > 0)
            <div class="p-4 border-top border-gray-200 d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">
                <div class="text-muted fs-7">
                    Showing <span class="fw-semibold text-gray-800">{{ $students->firstItem() }}</span> to <span class="fw-semibold text-gray-800">{{ $students->lastItem() }}</span> of <span class="fw-semibold text-gray-800">{{ number_format($students->total()) }}</span> students
                </div>
                <div>
                    {{ $students->links() }}
                </div>
            </div>
        @endif
    </div>

    <!-- Delete Confirmation Modal -->
    @if($confirmingStudentDeletion)
    <div class="modal fade show" tabindex="-1" style="display: block; background-color: rgba(0,0,0,0.5);" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-danger text-white py-3 px-4">
                    <h5 class="modal-title text-white fw-bold fs-6">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Delete Student
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="$set('confirmingStudentDeletion', false)"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="mb-0 text-gray-800 fs-6">Are you sure you want to delete this student record? This action cannot be undone.</p>
                </div>
                <div class="modal-footer bg-light py-3 px-4 border-0">
                    <button type="button" class="btn btn-sm btn-light-secondary border" wire:click="$set('confirmingStudentDeletion', false)">Cancel</button>
                    <button type="button" class="btn btn-sm btn-danger fw-semibold px-4" wire:click="deleteStudent">Delete</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- ID Regeneration Modal -->
    @if($confirmingIdRegeneration)
    <div class="modal fade show" tabindex="-1" style="display: block; background-color: rgba(0,0,0,0.5);" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-warning text-dark py-3 px-4">
                    <h5 class="modal-title fw-bold fs-6">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Regenerate Student IDs
                    </h5>
                    <button type="button" class="btn-close" wire:click="$set('confirmingIdRegeneration', false)"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="mb-0 text-gray-800 fs-6">This action will regenerate IDs for all students in the selected cohort. Are you sure you want to proceed?</p>
                </div>
                <div class="modal-footer bg-light py-3 px-4 border-0">
                    <button type="button" class="btn btn-sm btn-light-secondary border" wire:click="$set('confirmingIdRegeneration', false)">Cancel</button>
                    <button type="button" class="btn btn-sm btn-warning text-dark fw-semibold px-4" wire:click="regenerateIds">Regenerate</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
