<div>
    <!-- Heading outside the card -->
    <div class="mb-3">
        <h3 class="text-gray-900 fw-bold">Student Information <span class="badge bg-primary rounded-pill ms-2">{{ $studentsTotal }}</span></h3>
    </div>

    <div class="card mb-xl-10">
        <div class="border-0 card-header d-flex align-items-center justify-content-between w-100">
            
            <!-- Toolbar (right aligned) -->
            <div class="card-toolbar d-flex align-items-center flex-wrap w-100">
            <!-- Search -->
            <div class="position-relative me-3 mb-3 mb-md-0 flex-grow-1">
                <form class="w-100 position-relative" autocomplete="off">
                <i class="fas fa-search fs-4 position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                <input type="text" class="form-control form-control-solid ps-8" placeholder="Search students..." 
                       wire:model.debounce.500ms="search" style="min-width: 250px;">
                </form>
            </div>
            
            <div class="d-flex flex-wrap justify-content-end">
                <button class="btn btn-sm btn-primary me-2 mb-3 mb-md-0" wire:click="importStudents">
                <i class="fas fa-file-import me-2"></i>Import Students
                </button>
                <button class="btn btn-sm btn-light-primary me-2 mb-3 mb-md-0" wire:click="exportStudents">
                <i class="fas fa-file-export me-2"></i>Export Students
                </button>
                <a href="/generate" class="btn btn-sm btn-light-primary mb-3 mb-md-0">
                <i class="fas fa-id-card me-2"></i>Generate IDs
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
                        <th class="text-end min-w-100px pe-4">Actions</th>
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
