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
                                        <h5 class="card-title mb-0 text-dark d-flex align-items-center">
                                            <i class="fas fa-layer-group fa-fw me-2"></i><span>Years</span>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <p>Define programme study levels, such as Year 1, Year 2, and Year 3. These are separate from Academic Years.</p>
                                        <a href="{{ route('academics.years.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
                                            <i class="fas fa-cog"></i><span>Manage</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="card-title mb-0 text-white d-flex align-items-center">
                                            <i class="fas fa-calendar-alt fa-fw me-2 text-white"></i><span>Academic Years</span>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <p>Manage institutional calendar periods and select the current Academic Year for the system.</p>
                                        <a href="{{ route('academics.academic-years.index') }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-2">
                                            <i class="fas fa-cog"></i><span>Manage</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="card-title mb-0 text-white d-flex align-items-center">
                                            <i class="fas fa-clock fa-fw me-2 text-white"></i><span>Semesters</span>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <p>Manage the teaching periods within each Academic Year and select the current semester.</p>
                                        <a href="{{ route('academics.semesters.index') }}" class="btn btn-outline-success d-inline-flex align-items-center gap-2">
                                            <i class="fas fa-cog"></i><span>Manage</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="card-title mb-0 text-white d-flex align-items-center">
                                            <i class="fas fa-chalkboard fa-fw me-2"></i><span>Programs</span>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <p>Manage college classes, assign courses and instructors.</p>
                                        <a href="{{ route('academics.classes.index') }}" class="btn btn-outline-info d-inline-flex align-items-center gap-2">
                                            <i class="fas fa-cog"></i><span>Manage</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-header bg-warning text-dark">
                                        <h5 class="card-title mb-0 text-dark d-flex align-items-center">
                                            <i class="fas fa-user-friends fa-fw me-2"></i><span>Cohorts</span>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <p>Manage student cohorts and assign students to specific cohorts.</p>
                                        <a href="{{ route('academics.cohorts.index') }}" class="btn btn-outline-warning d-inline-flex align-items-center gap-2">
                                            <i class="fas fa-cog"></i><span>Manage</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="card-title mb-0 text-white d-flex align-items-center">
                                            <i class="fas fa-book fa-fw me-2 text-white"></i><span>Courses</span>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <p>Create and manage courses offered by the institution.</p>
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="{{ route('academics.courses.index') }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-2">
                                                <i class="fas fa-cog"></i><span>Manage</span>
                                            </a>
                                            @hasrole('Super Admin|Administrator|System')
                                            <a href="{{ route('admin.course-assignments') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
                                                <i class="fas fa-user-plus"></i><span>Assign Courses</span>
                                            </a>
                                            @endhasrole
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-header bg-danger text-white">
                                        <h5 class="card-title mb-0 text-white d-flex align-items-center">
                                            <i class="fas fa-star fa-fw me-2 text-white"></i><span>Grade Types</span>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <p>Manage grade types used for student evaluation.</p>
                                        <a href="{{ route('academics.grades.index') }}" class="btn btn-outline-danger d-inline-flex align-items-center gap-2">
                                            <i class="fas fa-cog"></i><span>Manage</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <div class="card-header bg-secondary text-white">
                                        <h5 class="card-title mb-0 text-dark d-flex align-items-center">
                                            <i class="fas fa-graduation-cap fa-fw me-2"></i><span>Student Grades</span>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <p>Assign and manage student grades for programs.</p>
                                        <a href="{{ route('academics.student-grades.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
                                            <i class="fas fa-cog"></i><span>Manage</span>
                                        </a>
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
