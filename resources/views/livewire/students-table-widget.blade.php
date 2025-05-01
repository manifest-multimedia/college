<div>
    <!-- Heading outside the card with badge -->
    <div class="mb-3 mt-20 mb-10">
        <h1 class="text-gray-900 fw-bold">Student Information 
            <span class="badge bg-primary rounded-pill ms-2 text-white px-3 py-2 fs-6">{{ $studentsTotal }}</span>
        </h1>
    </div>

    <div class="card mb-xl-10">
        <!-- Filter toolbar -->
        <div class="card-header border-0 py-3">
            <div class="d-flex flex-column flex-md-row align-items-md-center gap-3">
                <!-- Search box -->
                <div class="position-relative me-md-3 flex-grow-1" style="max-width: 300px;">
                    <span class="position-absolute top-50 translate-middle-y ms-3 text-muted">
                        <i class="fas fa-search fs-5"></i>
                    </span>
                    <input type="text" class="form-control form-control-sm form-control-solid ps-8" 
                           placeholder="Search students..." 
                           wire:model.debounce.500ms="search">
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
                    <button class="btn btn-sm btn-primary px-3 d-flex align-items-center" wire:click="importStudents">
                        <i class="fas fa-file-import me-2"></i>
                        Import
                    </button>
                    <button class="btn btn-sm btn-light-primary px-3 d-flex align-items-center" wire:click="exportStudents">
                        <i class="fas fa-file-export me-2"></i>
                        Export
                    </button>
                    <a href="/generate" class="btn btn-sm btn-light-primary px-3 d-flex align-items-center">
                        <i class="fas fa-id-card me-2"></i>
                        Generate IDs
                    </a>
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
                            <td class="ps-4">{{ $student->student_id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if ($student->profile_photo_url)
                                        <div class="me-3">
                                            <img class="rounded-circle" src="{{ $student->profile_photo_url }}" alt="avatar" width="40" height="40">
                                        </div>
                                    @endif
                                    <div>
                                        <span class="text-gray-800 fw-bold">
                                            {{ $student->last_name }} {{ $student->first_name }} {{ $student->other_name }}
                                        </span>
                                        <div class="text-gray-600 fs-7">{{ $student->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $student->CollegeClass()->first()->name }}</td>
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
                                        <li><a class="dropdown-item" href="/students/{{ $student->id }}/edit"><i class="fas fa-edit me-2 text-primary"></i>Edit</a></li>
                                        <li><a class="dropdown-item" href="/students/{{ $student->id }}"><i class="fas fa-eye me-2 text-info"></i>View</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="#" 
                                               onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this student?')) document.getElementById('delete-form-{{ $student->id }}').submit();">
                                               <i class="fas fa-trash-alt me-2"></i>Delete
                                            </a>
                                            <form id="delete-form-{{ $student->id }}" action="/students/{{ $student->id }}" method="POST" style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
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
</div>
