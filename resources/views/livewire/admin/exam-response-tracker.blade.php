<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="card-title">
                        <h1>
                            <i class="ki-duotone ki-eye fs-2 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Exam Audit Tool
                        </h1>
                    </div>
                        <div class="float-end">
                            
                        <p class="text-muted">Track and analyze student exam sessions and responses.</p>
                        </div>
                
                </div>
                <div class="card-body">
                    <div class="mb-4 row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="student_id" class="form-label">Search by Student ID</label>
                                <input type="text" id="student_id" wire:model.live.debounce.500ms="student_id" class="form-control" 
                                       placeholder="Enter student ID (e.g. COLLEGE/DEPT/RGN/24/25/001)">
                            </div>
                        </div>
                    </div>
                    
                    @if($studentFound)
                        <div class="mb-4 alert alert-success">
                            <h4 class="alert-heading">Student Found!</h4>
                            <p><strong>Name:</strong> {{ $foundStudent->first_name }} {{ $foundStudent->last_name }} {{ $foundStudent->other_name }}</p>
                            <p><strong>Student ID:</strong> {{ $foundStudent->student_id }}</p>
                            <p><strong>Email:</strong> {{ $foundStudent->email }}</p>
                            @if(!$foundUser)
                                <div class="alert alert-warning mt-2">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    This student does not have an associated user account. No exam sessions will be found.
                                </div>
                            @endif
                        </div>
                        
                        @if($foundUser)
                            <div class="mb-4 row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="exam_id" class="form-label">Select Exam</label>
                                        <select id="exam_id" wire:model.live="exam_id" class="form-select">
                                            <option value="">-- Select an Exam --</option>
                                            @foreach($exams as $exam)
                                                <option value="{{ $exam->id }}">

                                                    {{-- Add Exam ID if user has role 'System' --}}
                                                    @if(auth()->user()->hasRole('System'))
                                                        {{ $exam->id }} -
                                                    @endif
                                                    
                                                    {{ $exam->course->name ?? 'Unknown Course' }} ({{ $exam->created_at->format('d M, Y') }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                
                                @if($exam_id && count($studentExamSessions) > 0)
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="session_id" class="form-label">Select Exam Session</label>
                                            <select id="session_id" wire:model.live="session_id" class="form-select">
                                                <option value="">-- Select a Session --</option>
                                                @foreach($studentExamSessions as $session)
                                                    <option value="{{ $session->id }}">
                                                        Session #{{ $session->id }} - {{ $session->created_at->format('d M, Y H:i:s') }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @elseif($exam_id)
                                    <div class="col-md-6">
                                        <div class="alert alert-info mt-4">
                                            No exam sessions found for this student and exam.
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    @elseif($student_id && strlen($student_id) >= 3)
                        <div class="mb-4 alert alert-warning">
                            <h4 class="alert-heading">Student Not Found</h4>
                            <p>No student found with ID containing "{{ $student_id }}". Please check and try again.</p>
                        </div>
                    @endif
                    
                    @if($session_id && $responsesFound)
                        <div class="card mt-4 mb-4">
                            <div class="card-header">
                                <div class="card-title">
                                    <h3>
                                        <i class="ki-duotone ki-chart fs-2 me-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        Score Summary
                                    </h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="card bg-light h-100">
                                            <div class="card-body">
                                                <h5 class="card-title">Questions</h5>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h3 class="mb-0">{{ $totalAttempted }} / {{ $totalQuestions }}</h3>
                                                    <span class="badge bg-{{ $totalAttempted === $totalQuestions ? 'success' : 'warning' }} fs-6">
                                                        {{ round(($totalAttempted / max(1, $totalQuestions)) * 100, 1) }}%
                                                    </span>
                                                </div>
                                                <p class="text-muted mt-2">Questions attempted</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="card bg-light h-100">
                                            <div class="card-body">
                                                <h5 class="card-title">Marks</h5>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h3 class="mb-0">{{ $obtainedMarks }} / {{ $totalMarks }}</h3>
                                                    <span class="badge bg-{{ $scorePercentage >= 50 ? 'success' : 'danger' }} fs-6">
                                                        {{ $scorePercentage }}%
                                                    </span>
                                                </div>
                                                <p class="text-muted mt-2">Marks obtained</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="card bg-light h-100">
                                            <div class="card-body">
                                                <h5 class="card-title">Overall Performance</h5>
                                                @php
                                                    $performanceClass = 'danger';
                                                    $performanceText = 'Failed';
                                                    
                                                    if ($scorePercentage >= 80) {
                                                        $performanceClass = 'success';
                                                        $performanceText = 'Excellent';
                                                    } elseif ($scorePercentage >= 70) {
                                                        $performanceClass = 'primary';
                                                        $performanceText = 'Very Good';
                                                    } elseif ($scorePercentage >= 60) {
                                                        $performanceClass = 'info';
                                                        $performanceText = 'Good';
                                                    } elseif ($scorePercentage >= 50) {
                                                        $performanceClass = 'warning';
                                                        $performanceText = 'Pass';
                                                    }
                                                @endphp
                                                <div class="d-flex justify-content-center align-items-center">
                                                    <div class="text-center">
                                                        <h3 class="mb-0">{{ $scorePercentage }}%</h3>
                                                        <span class="badge bg-{{ $performanceClass }} fs-6 mt-2">
                                                            {{ $performanceText }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <p class="text-center text-muted mt-2">{{ $totalCorrect }} of {{ $totalQuestions }} correct</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header">
                                <div class="card-title">
                                    <h3>
                                        <i class="ki-duotone ki-document fs-2 me-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        Student Responses
                                    </h3>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Question</th>
                                                <th>Student's Answer</th>
                                                @if(auth()->user()->hasRole('System'))
                                                    <th>Option ID</th>
                                                @endif
                                                <th>Correct Answer</th>
                                                @if(auth()->user()->hasRole('System'))
                                                    <th>Correct ID</th>
                                                @endif
                                                <th>Status</th>
                                                <th>All Options</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($sessionResponses as $index => $response)
                                                <tr class="{{ $response['is_correct'] ? 'table-success' : 'table-danger' }}">
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $response['question_text'] }}</td>
                                                    <td>{{ $response['selected_option_text'] }}</td>
                                                    @if(auth()->user()->hasRole('System'))
                                                        <td>{{ $response['selected_option_id'] ?? 'N/A' }}</td>
                                                    @endif
                                                    <td>{{ $response['correct_option_text'] }}</td>
                                                    @if(auth()->user()->hasRole('System'))
                                                        <td>{{ $response['correct_option_id'] ?? 'N/A' }}</td>
                                                    @endif
                                                    <td>
                                                        @if($response['is_correct'])
                                                            <span class="badge bg-success">Correct</span>
                                                        @else
                                                            <span class="badge bg-danger">Incorrect</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#optionsModal{{ $response['question_id'] }}">
                                                            View Options
                                                        </button>
                                                        
                                                        <!-- Modal for Options -->
                                                        <div class="modal fade" id="optionsModal{{ $response['question_id'] }}" tabindex="-1" 
                                                             aria-labelledby="optionsModalLabel{{ $response['question_id'] }}" aria-hidden="true">
                                                            <div class="modal-dialog">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="optionsModalLabel{{ $response['question_id'] }}">
                                                                            Question Options
                                                                        </h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <p><strong>Question:</strong> {{ $response['question_text'] }}</p>
                                                                        <hr>
                                                                        <h6>All Options:</h6>
                                                                        <ul class="list-group">
                                                                            @foreach($response['all_options'] as $option)
                                                                                <li class="list-group-item 
                                                                                    {{ $option['id'] == $response['selected_option_id'] ? 'list-group-item-primary' : '' }}
                                                                                    {{ $option['is_correct'] ? 'list-group-item-success' : '' }}">
                                                                                    @if(auth()->user()->hasRole('System'))
                                                                                        <strong>ID: {{ $option['id'] }}</strong> - 
                                                                                    @endif
                                                                                    {{ $option['text'] }}
                                                                                    
                                                                                    @if($option['id'] == $response['selected_option_id'])
                                                                                        <span class="badge bg-primary float-end">Selected</span>
                                                                                    @endif
                                                                                    
                                                                                    @if($option['is_correct'])
                                                                                        <span class="badge bg-success float-end">Correct</span>
                                                                                    @endif
                                                                                </li>
                                                                            @endforeach
                                                                        </ul>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Devices Section -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <div class="card-title">
                                    <h3>
                                        <i class="ki-duotone ki-devices fs-2 me-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        Devices Used
                                        @if(count($deviceAccessLogs) > 1)
                                            <span class="badge bg-warning ms-2">{{ count($deviceAccessLogs) }} devices</span>
                                        @elseif(count($deviceAccessLogs) == 1)
                                            <span class="badge bg-success ms-2">Single device</span>
                                        @else
                                            <span class="badge bg-secondary ms-2">No device logs</span>
                                        @endif
                                    </h3>
                                </div>
                            </div>
                            <div class="card-body">
                                @if(count($deviceAccessLogs) > 0)
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Access Time</th>
                                                    <th>IP Address</th>
                                                    <th>Browser</th>
                                                    <th>Platform</th>
                                                    <th>Device Type</th>
                                                    <th>Screen Resolution</th>
                                                    <th>Timezone</th>
                                                    <th>Status</th>
                                                    <th>Details</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($deviceAccessLogs as $index => $log)
                                                    <tr class="{{ $log['is_conflict'] ? 'table-warning' : '' }}">
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>
                                                            <small>
                                                                {{ $log['access_time']->format('d M, Y H:i:s') }}
                                                            </small>
                                                        </td>
                                                        <td>
                                                            <code>{{ $log['ip_address'] ?? 'N/A' }}</code>
                                                        </td>
                                                        <td>{{ $log['browser'] }}</td>
                                                        <td>{{ $log['platform'] }}</td>
                                                        <td>{{ $log['device_type'] }}</td>
                                                        <td><small>{{ $log['screen_resolution'] }}</small></td>
                                                        <td><small>{{ $log['timezone'] }}</small></td>
                                                        <td>
                                                            @if($log['is_conflict'])
                                                                <span class="badge bg-danger">Conflict Detected</span>
                                                            @else
                                                                <span class="badge bg-success">Normal</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <button class="btn btn-sm btn-info" 
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#deviceModal{{ $log['id'] }}">
                                                                View Full Details
                                                            </button>
                                                            
                                                            <!-- Modal for Device Details -->
                                                            <div class="modal fade" id="deviceModal{{ $log['id'] }}" tabindex="-1" 
                                                                 aria-labelledby="deviceModalLabel{{ $log['id'] }}" aria-hidden="true">
                                                                <div class="modal-dialog modal-lg">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h5 class="modal-title" id="deviceModalLabel{{ $log['id'] }}">
                                                                                Device Details - Access #{{ $index + 1 }}
                                                                            </h5>
                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            <div class="row mb-3">
                                                                                <div class="col-md-6">
                                                                                    <p><strong>Access Time:</strong></p>
                                                                                    <p class="text-muted">{{ $log['access_time']->format('d M, Y H:i:s') }}</p>
                                                                                </div>
                                                                                <div class="col-md-6">
                                                                                    <p><strong>Conflict Status:</strong></p>
                                                                                    <p>
                                                                                        @if($log['is_conflict'])
                                                                                            <span class="badge bg-danger fs-6">Conflict Detected</span>
                                                                                        @else
                                                                                            <span class="badge bg-success fs-6">Normal</span>
                                                                                        @endif
                                                                                    </p>
                                                                                </div>
                                                                            </div>
                                                                            
                                                                            <hr>
                                                                            
                                                                            <div class="row mb-3">
                                                                                <div class="col-md-6">
                                                                                    <p><strong>IP Address:</strong></p>
                                                                                    <p class="text-muted"><code>{{ $log['ip_address'] ?? 'N/A' }}</code></p>
                                                                                </div>
                                                                                <div class="col-md-6">
                                                                                    <p><strong>Device Identifier:</strong></p>
                                                                                    <p class="text-muted"><code>{{ $log['device_identifier'] ?? 'Unknown' }}</code></p>
                                                                                </div>
                                                                            </div>
                                                                            
                                                                            <div class="row mb-3">
                                                                                <div class="col-md-6">
                                                                                    <p><strong>Browser:</strong></p>
                                                                                    <p class="text-muted">{{ $log['browser'] }}</p>
                                                                                </div>
                                                                                <div class="col-md-6">
                                                                                    <p><strong>Platform:</strong></p>
                                                                                    <p class="text-muted">{{ $log['platform'] }}</p>
                                                                                </div>
                                                                            </div>
                                                                            
                                                                            <div class="row mb-3">
                                                                                <div class="col-md-6">
                                                                                    <p><strong>Device Type:</strong></p>
                                                                                    <p class="text-muted">{{ $log['device_type'] }}</p>
                                                                                </div>
                                                                                <div class="col-md-6">
                                                                                    <p><strong>Screen Resolution:</strong></p>
                                                                                    <p class="text-muted">{{ $log['screen_resolution'] }}</p>
                                                                                </div>
                                                                            </div>
                                                                            
                                                                            <div class="row mb-3">
                                                                                <div class="col-md-12">
                                                                                    <p><strong>Timezone:</strong></p>
                                                                                    <p class="text-muted">{{ $log['timezone'] }}</p>
                                                                                </div>
                                                                            </div>
                                                                            
                                                                            @if($log['user_agent'])
                                                                                <hr>
                                                                                <p><strong>User Agent:</strong></p>
                                                                                <p class="text-muted small"><code>{{ $log['user_agent'] }}</code></p>
                                                                            @endif
                                                                            
                                                                            @if(auth()->user()->hasRole('System') && !empty($log['all_info']))
                                                                                <hr>
                                                                                <p><strong>Raw Device Info:</strong></p>
                                                                                <pre class="bg-light p-3 rounded"><small>{{ json_encode($log['all_info'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</small></pre>
                                                                            @endif
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    @if(count($deviceAccessLogs) > 1)
                                        <div class="alert alert-warning mt-4">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <strong>Multiple Devices Detected:</strong> This student accessed the exam from {{ count($deviceAccessLogs) }} different devices. 
                                            @if(collect($deviceAccessLogs)->where('is_conflict', true)->count() > 0)
                                                Some accesses were flagged as conflicts. Review the device details and timestamps carefully.
                                            @else
                                                Verify if all device changes are legitimate (e.g., session timeout and re-login on same device).
                                            @endif
                                        </div>
                                    @endif
                                @else
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        No device access logs found for this exam session. This may indicate that device tracking was not enabled during the exam.
                                    </div>
                                @endif
                            </div>
                        </div>
                    @elseif($session_id && !$responsesFound)
                        <div class="alert alert-info mt-4">
                            <h4 class="alert-heading">No Responses Found</h4>
                            <p>No responses were found for this exam session. The student may not have answered any questions yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
