@push('scripts')
    <script>
        document.querySelectorAll('.js-set-current-academic-year').forEach((button) => {
            button.addEventListener('click', (event) => {
                event.preventDefault();

                Swal.fire({
                    title: 'Set current Academic Year?',
                    text: `${button.dataset.academicYearName} and its first semester will become the active academic period.`,
                    icon: 'warning',
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: 'Yes, set as current',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        confirmButton: 'btn fw-bold btn-success',
                        cancelButton: 'btn fw-bold btn-active-light-primary'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        button.form.submit();
                    }
                });
            });
        });
    </script>
@endpush

<x-dashboard.default>
    <x-slot name="title">
        Academic Year Details
    </x-slot>
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title">
                                <i class="fas fa-calendar-alt me-2"></i>Academic Year Details
                            </h5>
                            <div>
                                <a href="{{ route('academics.academic-years.index') }}" class="btn btn-sm btn-secondary me-2">
                                    <i class="fas fa-arrow-left me-1"></i> Back to Academic Years
                                </a>
                                <a href="{{ route('academics.academic-years.edit', $academicYear) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8 offset-md-2">
                                <table class="table table-striped">
                                    <tbody>
                                        <tr>
                                            <th style="width: 30%">Academic Year</th>
                                            <td>{{ $academicYear->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Year</th>
                                            <td>{{ $academicYear->year }}</td>
                                        </tr>
                                        <tr>
                                            <th>Start Date</th>
                                            <td>{{ $academicYear->start_date->format('F d, Y') }}</td>
                                        </tr>
                                        <tr>
                                            <th>End Date</th>
                                            <td>{{ $academicYear->end_date->format('F d, Y') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
                                            <td>
                                                @if($academicYear->is_current)
                                                    <span class="badge bg-success">Current Academic Year</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                
                                <!-- Semesters -->
                                <div class="mt-4">
                                    <h4>Semesters</h4>
                                    <hr>
                                    
                                    @if($academicYear->semesters->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Name</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($academicYear->semesters as $semester)
                                                        <tr>
                                                            <td>{{ $semester->name }}</td>
                                                            <td>
                                                                @if($semester->is_current)
                                                                    <span class="badge bg-success">Current</span>
                                                                @else
                                                                    <span class="badge bg-secondary">Inactive</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <a href="{{ route('academics.semesters.show', $semester) }}" class="btn btn-sm btn-info">
                                                                    <i class="fas fa-eye"></i> View
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="alert alert-info">
                                            No semesters have been created for this academic year.
                                        </div>
                                    @endif
                                    
                                    <a href="{{ route('academics.semesters.create') }}" class="btn btn-primary mt-2">
                                        <i class="fas fa-plus me-1"></i> Add Semester
                                    </a>
                                </div>
                                
                                <!-- Set as Current Year -->
                                @if(!$academicYear->is_current)
                                    <div class="mt-4">
                                        @if($academicYear->semesters->isNotEmpty())
                                            <form action="{{ route('academics.settings.update') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="academic_year_id" value="{{ $academicYear->id }}">
                                                <input type="hidden" name="semester_id" value="{{ $academicYear->semesters->first()?->id }}">
                                                <button type="submit" class="btn btn-success js-set-current-academic-year" data-academic-year-name="{{ $academicYear->name }}">
                                                    <i class="fas fa-check-circle me-1"></i> Set as Current Academic Year
                                                </button>
                                            </form>
                                        @else
                                            <div class="alert alert-warning mb-0">
                                                Add a semester for this Academic Year before making it current.
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard.default>
