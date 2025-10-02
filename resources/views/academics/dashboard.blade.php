<x-dashboard.default>
    <x-slot name="title">
        Academics Dashboard
    </x-slot>
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title">
                                <i class="fas fa-university me-2"></i>Academics Management
                            </h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Current Academic Year & Semester -->
                            <div class="col-md-12 mb-4">
                                <div class="alert alert-info">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-1">Current Academic Settings</h5>
                                            @php
                                                $currentYear = App\Models\AcademicYear::where('is_current', true)->first();
                                                $currentSemester = App\Models\Semester::current()->first();
                                            @endphp
                                            @if($currentYear && $currentSemester)
                                                <p class="mb-0">
                                                    <strong>Academic Year:</strong> {{ $currentYear->name }} | 
                                                    <strong>Semester:</strong> {{ $currentSemester->name }}
                                                </p>
                                            @else
                                                <p class="mb-0">No current academic year or semester set. Please update settings.</p>
                                            @endif
                                        </div>
                                        <a href="{{ route('academics.settings.index') }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-cogs me-1"></i> Settings
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Quick Access Cards -->
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-header bg-secondary text-white">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-layer-group me-1"></i> Years
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <p>Manage academic years (e.g., Year 1, Year 2, Year 3) for programs.</p>
                                        <a href="{{ route('academics.years.index') }}" class="btn btn-outline-secondary">Manage</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-calendar-alt me-1"></i> Academic Years
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <p>Manage academic years and set the current year for the system.</p>
                                        <a href="{{ route('academics.academic-years.index') }}" class="btn btn-outline-primary">Manage</a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-clock me-1"></i> Semesters
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <p>Manage semesters and set the active semester for the current academic year.</p>
                                        <a href="{{ route('academics.semesters.index') }}" class="btn btn-outline-success">Manage</a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-chalkboard me-1"></i> Programs
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <p>Manage college classes, assign courses and instructors.</p>
                                        <a href="{{ route('academics.classes.index') }}" class="btn btn-outline-info">Manage</a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-header bg-warning text-dark">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-user-friends me-1"></i> Cohorts
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <p>Manage student cohorts and assign students to specific cohorts.</p>
                                        <a href="{{ route('academics.cohorts.index') }}" class="btn btn-outline-warning">Manage</a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-book me-1"></i> Courses
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <p>Create and manage courses offered by the institution.</p>
                                        <a href="{{ route('academics.courses.index') }}" class="btn btn-outline-primary">Manage</a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-header bg-danger text-white">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-star me-1"></i> Grade Types
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <p>Manage grade types used for student evaluation.</p>
                                        <a href="{{ route('academics.grades.index') }}" class="btn btn-outline-danger">Manage</a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-header bg-secondary text-white">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-graduation-cap me-1"></i> Student Grades
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <p>Assign and manage student grades for classes.</p>
                                        <a href="{{ route('academics.student-grades.index') }}" class="btn btn-outline-secondary">Manage</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard.default>