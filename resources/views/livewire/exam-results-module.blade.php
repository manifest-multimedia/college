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
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Student ID</th>
                                <th>Student Name</th>
                                <th>Course</th>
                                <th>Score</th>
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
                                    <td>{{ $result['percentage'] }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    
                    <div class="mt-4">
                        {{ $results->links() }}
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>


