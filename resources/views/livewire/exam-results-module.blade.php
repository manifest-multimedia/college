<div>
    {{-- Be like water. --}}
    <div class="container">
        @if($mode === 'index')
            <h2 class="my-4">Exam Results Dashboard</h2>

            <!-- Filters -->
            <div class="mb-4">
                <div class="row g-2 d-flex justify-content-between">
                    <div class="col-md-4">
                        <label for="exam-select" class="form-label">Filter By Exam</label>
                        <select class="form-select" wire:model.live="selected_exam_id">
                            <option value="">Select Exam</option>
                            @foreach ($exams as $exam)
                                <option value="{{ $exam->id }}">{{ $exam->course->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="college-class-select" class="form-label">Filter By College Class</label>
                        <select class="form-select" wire:model.live="selected_college_class_id">
                            <option value="">Select College Class</option>
                            @foreach ($collegeClasses as $collegeClass)
                                <option value="{{ $collegeClass->id }}">{{ $collegeClass->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end gap-2">
                        <button class="btn {{ $selected_exam_id && $selected_college_class_id ? 'btn-primary' : 'btn-secondary' }}" wire:click="generateResults" @disabled(!$selected_exam_id || !$selected_college_class_id)>
                            Generate Results
                        </button>
                        <button class="btn {{ $selected_exam_id && $selected_college_class_id ? 'btn-primary' : 'btn-secondary' }}" wire:click="exportResults" @disabled(!$selected_exam_id || !$selected_college_class_id)>
                            Download
                        </button>
                        <button class="btn btn-primary" wire:click="exportBulkResults">
                            Bulk Export
                        </button>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <h3>Results</h3>
                    </div>
                </div>
                <div class="card-body">
                    @if($selected_exam_id && $selected_college_class_id)
                        @if($results->count() > 0)
                            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Date</th>
                                            <th>Student ID</th>
                                            <th>Student Name</th>
                                            <th>Course</th>
                                            <th>Score</th>
                                            <th>Answered</th>
                                            <th>Percentage</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($results as $index => $result)
                                            <tr>
                                                <!-- Number Results from 1 to n keeping in mind that results have been sorted in ascending order -->
                                                <td>{{$loop->iteration}}</td>
                                                <td>{{ $result['date'] }}</td>
                                                <td>{{ $result['student_id'] }}</td>
                                                <td>{{ $result['student_name'] }}</td>
                                                <td>{{ $result['course'] }}</td>
                                                <td>{{ $result['score'] }}</td>
                                                <td>{{ $result['answered'] }}</td>
                                                <td>{{ $result['percentage'] }}%</td>
                                                <td>
                                                    <!-- Export Button -->
                                                    <a href="javascript:void(0)" class="btn btn-primary export-button" wire:click="exportStudentResult('{{ $result['student_id'] }}')">
                                                       Export
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <div class="action-buttons">
                                    <button wire:click="exportResults" class="btn btn-primary">
                                        Download Results
                                    </button>
                                </div>
                            </div>
                        @else
                            <div class="d-flex justify-content-center py-5 align-items-center gap-2">
                                <button class="btn btn-primary" wire:click="generateResults">
                                    Generate Results
                                </button>
                                <button class="btn btn-primary" wire:click="exportResults">Download Results</button>
                            </div>
                        @endif
                    @else
                        <div class="d-flex justify-content-center py-5 align-items-center">
                            <p class="text-muted">Select an exam and college class to generate and view results.</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>


