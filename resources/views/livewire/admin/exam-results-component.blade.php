<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title d-flex justify-content-between align-items-center">
                        <h3>
                            <i class="ki-duotone ki-chart fs-2 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Exam Results
                        </h3>
                        <div>
                            <p class="text-muted mb-0">View and export exam results for all students</p>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Search & Filter Section -->
                    <div class="mb-4">
                        <div class="row g-3 align-items-end">
                            <!-- Exam Selection -->
                            <div class="col-md-5">
                                <label for="exam_id" class="form-label">Select Exam</label>
                                <select wire:model.live="exam_id" id="exam_id" class="form-select">
                                    <option value="">-- Select an Exam --</option>
                                    @foreach($exams as $exam)
                                        <option value="{{ $exam->id }}">
                                            {{ $exam->course->name ?? 'Unknown Course' }} ({{ $exam->created_at->format('d M, Y') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Class Filter -->
                            <div class="col-md-3">
                                <label for="college_class_id" class="form-label">Class</label>
                                <select wire:model.live="college_class_id" id="college_class_id" class="form-select">
                                    <option value="">All Classes</option>
                                    @foreach($collegeClasses as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Search -->
                            <div class="col-md-3">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" wire:model.live.debounce.500ms="search" id="search" 
                                       class="form-control" placeholder="Student ID, Name, Email...">
                            </div>
                            
                            <!-- Export Options -->
                            <div class="col-md-1">
                                <div class="dropdown">
                                    <button class="btn btn-primary dropdown-toggle" type="button" id="exportDropdown" 
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                        Export
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                                        <li><button wire:click="exportToExcel" class="dropdown-item">Excel</button></li>
                                        <li><button wire:click="exportToPDF" class="dropdown-item">PDF</button></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Results Stats Section (if results are available) -->
                    @if($hasResults && $exam_id)
                        <div class="mb-4">
                            <div class="row g-3">
                                <!-- Total Students Card -->
                                <div class="col-md-2">
                                    <div class="card bg-light h-100">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-muted mb-1">Students</h6>
                                            <h3 class="mb-0">{{ $totalStudents }}</h3>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Average Score Card -->
                                <div class="col-md-2">
                                    <div class="card bg-light h-100">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-muted mb-1">Average</h6>
                                            <h3 class="mb-0">{{ $averageScore }}%</h3>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Pass Rate Card -->
                                <div class="col-md-2">
                                    <div class="card bg-light h-100">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-muted mb-1">Pass Rate</h6>
                                            <h3 class="mb-0">{{ $passRate }}%</h3>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Highest Score Card -->
                                <div class="col-md-2">
                                    <div class="card bg-light h-100">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-muted mb-1">Highest</h6>
                                            <h3 class="mb-0">{{ $highestScore }}%</h3>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Lowest Score Card -->
                                <div class="col-md-2">
                                    <div class="card bg-light h-100">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-muted mb-1">Lowest</h6>
                                            <h3 class="mb-0">{{ $lowestScore }}%</h3>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Records Per Page -->
                                <div class="col-md-2">
                                    <div class="card bg-light h-100">
                                        <div class="card-body text-center">
                                            <h6 class="card-title text-muted mb-1">Display</h6>
                                            <select wire:model.live="perPage" class="form-select form-select-sm mx-auto" style="max-width: 80px;">
                                                <option value="15">15</option>
                                                <option value="25">25</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Results Table Section -->
                    @if($exam_id)
                        @if($hasResults)
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th wire:click="sortBy('student_id')" class="cursor-pointer">
                                                Student ID
                                                @if($sortField === 'student_id')
                                                    <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                                @endif
                                            </th>
                                            <th wire:click="sortBy('name')" class="cursor-pointer">
                                                Name
                                                @if($sortField === 'name')
                                                    <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                                @endif
                                            </th>
                                            <th wire:click="sortBy('class')" class="cursor-pointer">
                                                Class
                                                @if($sortField === 'class')
                                                    <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                                @endif
                                            </th>
                                            <th wire:click="sortBy('completed_at')" class="cursor-pointer">
                                                Date
                                                @if($sortField === 'completed_at')
                                                    <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                                @endif
                                            </th>
                                            <th wire:click="sortBy('score')" class="cursor-pointer">
                                                Score
                                                @if($sortField === 'score')
                                                    <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                                @endif
                                            </th>
                                            <th wire:click="sortBy('answered')" class="cursor-pointer">
                                                Answered
                                                @if($sortField === 'answered')
                                                    <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                                @endif
                                            </th>
                                            <th wire:click="sortBy('score_percentage')" class="cursor-pointer">
                                                Percentage
                                                @if($sortField === 'score_percentage')
                                                    <i class="bi bi-arrow-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                                @endif
                                            </th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($examResults as $result)
                                            @php
                                                $statusClass = 'danger';
                                                $statusText = 'Failed';
                                                
                                                if ($result['score_percentage'] >= 80) {
                                                    $statusClass = 'success';
                                                    $statusText = 'Excellent';
                                                } elseif ($result['score_percentage'] >= 70) {
                                                    $statusClass = 'primary';
                                                    $statusText = 'Very Good';
                                                } elseif ($result['score_percentage'] >= 60) {
                                                    $statusClass = 'info';
                                                    $statusText = 'Good';
                                                } elseif ($result['score_percentage'] >= 50) {
                                                    $statusClass = 'warning';
                                                    $statusText = 'Pass';
                                                }
                                            @endphp
                                            <tr>
                                                <td>{{ $result['student_id'] }}</td>
                                                <td>{{ $result['name'] }}</td>
                                                <td>{{ $result['class'] }}</td>
                                                <td>{{ $result['completed_at'] }}</td>
                                                <td>{{ $result['score'] }}</td>
                                                <td>{{ $result['answered'] }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-2">{{ $result['score_percentage'] }}%</div>
                                                        <div class="progress w-100" style="height: 6px;">
                                                            <div class="progress-bar bg-{{ $statusClass }}" 
                                                                role="progressbar" 
                                                                style="width: {{ $result['score_percentage'] }}%" 
                                                                aria-valuenow="{{ $result['score_percentage'] }}" 
                                                                aria-valuemin="0" 
                                                                aria-valuemax="100">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $statusClass }}">{{ $statusText }}</span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('exam.response.tracker') }}?student_id={{ $result['student_id'] }}" 
                                                       class="btn btn-sm btn-primary">
                                                        View Details
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <h4 class="alert-heading">No Results Found</h4>
                                <p>No exam results matching the selected criteria were found. Please try different filters or select another exam.</p>
                            </div>
                        @endif
                    @else
                        <div class="alert alert-info">
                            <h4 class="alert-heading">Select an Exam</h4>
                            <p>Please select an exam to view and export results.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
