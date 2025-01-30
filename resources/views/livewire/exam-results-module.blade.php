<div>
    {{-- Be like water. --}}
    <div class="container">
        @if($mode === 'index')
            <h2 class="my-4">Exam Results Dashboard</h2>

            <!-- Filters -->
            <div class="mb-4">
                <div class="row g-2">
                    <div class="col-md-8">
                        <label for="exam-select" class="form-label">Filter By Exam</label>
                        <select class="form-select" wire:model.live="selected_exam_id">
                            <option value="">Select Exam</option>
                            @foreach ($exams as $exam)
                                <option value="{{ $exam->id }}">{{ $exam->course->name }}</option>
                            @endforeach
                        </select>
                    </div>
                   

                  
                        <div class="col-md-4 d-flex align-items-end">
                            <button class="btn {{ $selected_exam_id ? 'btn-success' : 'btn-secondary' }}" wire:click="exportResults" @disabled(!$selected_exam_id)>
                                Download Results
                            </button>
                        </div>

                </div>
            </div>

            @if($selected_exam_id)
                @if($isGeneratingResults)
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <h5 class="text-muted">Generating Results...</h5>
                        <div class="progress mb-3" style="height: 20px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                role="progressbar" 
                                style="width: {{ $processingProgress }}%"
                                aria-valuenow="{{ $processingProgress }}" 
                                aria-valuemin="0" 
                                aria-valuemax="100">
                                {{ round($processingProgress) }}%
                            </div>
                        </div>
                        <p class="text-muted small">This may take a moment for large datasets</p>
                    </div>
                @else
                    @if($results->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Student ID</th>
                                        <th>Student Name</th>
                                        <th>Course</th>
                                        <th>Score</th>
                                        <th>Answered</th>
                                        <th>Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($results as $result)
                                        <tr>
                                            <td>{{ $result['date'] }}</td>
                                            <td>{{ $result['student_id'] }}</td>
                                            <td>{{ $result['student_name'] }}</td>
                                            <td>{{ $result['course'] }}</td>
                                            <td>{{ $result['score'] }}</td>
                                            <td>{{ $result['answered'] }}</td>
                                            <td>{{ $result['percentage'] }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                {{ $results->links() }}
                                <button wire:click="exportResults" class="btn btn-primary">
                                    Export Results
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <p class="text-muted">No results found for this exam.</p>
                        </div>
                    @endif
                @endif
            @else
                <div class="text-center py-5">
                    <p class="text-muted">Select an exam to view results.</p>
                </div>
            @endif
        @endif
    </div>
</div>


