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
                            Exam Response Tracker
                        </h1>
                    </div>
                        <div class="float-end">
                            
                        <p class="text-muted">Track student responses to exam questions in real-time</p>
                        </div>
                
                </div>
                <div class="card-body">
                    <div class="mb-4 row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="student_id" class="form-label">Search by Student ID</label>
                                <input type="text" id="student_id" wire:model.live.debounce.500ms="student_id" class="form-control" 
                                       placeholder="Enter student ID (e.g. PNMTC/DA/RGN/24/25/001)">
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
                        <div class="card mt-4">
                            <div class="card-header bg-primary">
                                <div class="card-title">
                                    <h3 class="text-white">Student Responses</h3>
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
                                                <th>Correct Answer</th>
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
                                                    <td>{{ $response['correct_option_text'] }}</td>
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
