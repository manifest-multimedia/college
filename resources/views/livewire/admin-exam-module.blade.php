<div>
    <div class="card">
        <div class="card-header">
            <div class="card-title d-flex justify-content-between align-items-center">
                <div>

                    <h3>Track Exam Submissions</h3>
                </div>
                <div>

                    <p class="float-right">Select Student and Exam to View Responses</p>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="mb-3 row">
                <div class="col-md-4">
                    <label for="student">Select Student</label>
                    <select name="student" id="student" wire:model="selected_student" class="form-control">
                        <option value="">Select Student</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="exam">Select Exam</label>
                    <select name="exam" id="exam" wire:model="selected_exam" class="form-control">
                        <option value="">Select Exam</option>
                        @foreach ($exams as $exam)
                            <option value="{{ $exam->id }}">{{ $exam->name }} ({{ $exam->exam_code }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="exam_session">Select Session</label>
                    <select name="exam_session" id="exam_session" wire:model="selected_session" class="form-control">
                        <option value="">Select Session</option>
                        @foreach ($examSessions as $session)
                            <option value="{{ $session->id }}">
                                {{ $session->course->name }} - {{ $session->exam->exam_code }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    @if($responses->isEmpty())
                        <p>No responses found for the selected criteria.</p>
                    @else
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Question</th>
                                    <th>Answer</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($responses as $response)
                                    <tr>
                                        <td>{{ $response->question->question }}</td>
                                        <td>{{ $response->answer }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
