<div>
<div class="container">

<style>
    .pagination-container {
        display: flex;
        justify-content: center;
        margin-top: 20px;
    }
    .pagination-container p{
        margin: 0;
    }
</style>

    @if ($mode === 'index')


        <div class="card border-">
          

                <div class="card-header d-flex align-items-center">
                    <div class="card-title">
                        <h2 class="my-4">Exam Sessions Management</h2>
                    </div>
                    <div class="gap-2 card-toolbar d-flex justify-content-between align-items-center flex-grow-1">
                        <!-- Filters -->
                        <div class="flex-row gap-2 d-flex g-2 justify-content-between align-items-center">

                            <div>
                                <input type="text" class="form-control" wire:model.live="filter_student_id"
                                    placeholder="Search by Student ID">
                            </div>
                            <div>
                                <input type="text" class="form-control" wire:model.live="filter_email"
                                    placeholder="Search by Email">
                            </div>
                            <div>
                                {{-- <input type="text" class="form-control" wire:model.live="filter_exam_id"
                                    placeholder="Search by Exam ID"> --}}
                                    <select name="filter_by_exam" id="exam_id" class="form-select form-control"
                                    wire:model.live="filter_by_exam"
                                    >
                                        <option value="">Select Exam</option>
                                        @forelse ($exams as $exam)
                                            <option value="{{ $exam->id }}">{{ $exam->course->name }}</option>
                                            @empty
                                            <option value="">No Exams Found</option>
                                        @endforelse
                                    </select>

                                
                            </div>
                            <div>
                                    {{-- Filter By Class --}}
                                    <select name="filter_by_class" id="class_id"
                                    wire:model.live="filter_by_class" class="form-select form-control"
                                    >
                                        <option value="">Select Class</option>
                                        @forelse ($classes as $class)
                                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                                            @empty
                                            <option value="">No Classes Found</option>
                                        @endforelse
                                </select>
                            </div>

                            <div>
                                <button class="btn btn-success" wire:click="downloadResults">Download</button>

                            </div>
                        </div>
                    </div>
                </div>
                  <div class="card-body">

        <!-- Table -->
        <div class="overflow-auto table-responsive">
            <table class="table align-middle table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Student ID</th>
                        {{-- <th>Student User ID </th> --}}
                        <th>Name</th>
                        <th>Email</th>
                        <th>Sessions & Responses</th>
                        <th>Actions</th>
                    </tr>
                </thead>


                <tbody>
                    @forelse($students as $student)
                        <tr>
                            <td>{{ // Loop iteration for paginated data
                                ($students->currentPage() - 1) * $students->perPage() + $loop->iteration }}
                            </td>
                            <td>{{ $student->student_id }}</td>
                            {{-- <td>{{ $student->user->id ?? 'Not Found' }}</td> --}}
                            <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                            <td>{{ $student->email }}</td>
                            <td>
                                {{-- @php
                                $examSessions = \App\Models\ExamSession::where('student_id', $student->id)->with('exam.course', 'responses')->get();

                                @endphp --}}
                                @php
                                    $examSessions = \App\Models\ExamSession::where(
                                        'student_id',
                                        optional($student->user)->id,
                                    )
                                        ->with('exam.course', 'responses')
                                        ->get();

                                @endphp
                                @if ($examSessions->isNotEmpty())
                                    <span class="text-white badge bg-dark">Sessions: {{ $examSessions->count() }}</span>
                                    @foreach ($examSessions as $session)
                                        <div class="p-2 rounded border border-1 border-success">
                                            Course Name: {{ optional($session->exam->course)->name }}<br>
                                            <span class="badge bg-success">Attempted
                                                {{ computeResults($session->id, 'total_answered') }} Questions</span>

                                            {{-- Output Score --}}
                                            <div class="border d-flex badge border-success">
                                                Score: {{ computeResults($session->id, 'score') }}

                                                Percentage: {{ computeResults($session->id) }}
                                            </div>

                                            

                                        </div>
                                        <button class="gap-2 m-2 d-flex btn btn-danger btn-sm"
                                                wire:click="removeSession({{ $session->id }})">Delete Session Data</button>
                                    @endforeach
                                @else
                                    <span class="badge bg-danger">No sessions or responses found</span>
                                @endif

                            </td>
                            <td>
                                <button class="btn btn-primary btn-sm"
                                    wire:click="downloadResults({{ $student->id }})">Download Result</button>
                            </td>
                        </tr>
                        
                       
                    @empty
                       <tr>
                           <td colspan="6" class="text-center">No records found</td>   
                       </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-container">
            {{ $students->links() }}
        </div>
    @elseif ($mode === 'view')
        <h2 class="my-4">Details for Student: {{ $student->first_name }}
            {{ $student->last_name }}{{ $student->other_name }}</h2>

        <!-- User Account -->
        <div class="mb-4">
            <h3>User Account</h3>
            <p><strong>Name:</strong> {{ $user->name ?? 'Not Found' }}</p>
            <p><strong>Email:</strong> {{ $user->email ?? 'Not Found' }}</p>
            <button class="btn btn-secondary" wire:click="editDetails('user')">Edit User</button>
        </div>

        <!-- Student Details -->
        <div class="mb-4">
            <h3>Student Details</h3>
            <p><strong>Student ID:</strong> {{ $student->student_id }}</p>
            <p><strong>Email:</strong> {{ $student->email }}</p>
            <button class="btn btn-secondary" wire:click="editDetails('student')">Edit Student</button>
        </div>

        <!-- Exam Session -->

        @if ($examSessions->count() > 0)
            @foreach ($examSessions as $examSession)
                <div class="mb-4">
                    <h3>Exam Session</h3>
                    <p><strong>Course Name:</strong> {{ optional($examSession->exam->course)->name }}</p>
                    <p><strong>Started At:</strong> {{ $examSession->started_at }}</p>
                    <p><strong>Completed At:</strong> {{ $examSession->completed_at }}</p>
                    <p><strong>Duration:</strong> {{ $examSession->duration }} minutes</p>
                    <p>Total Responses Received: {{ $examSession->responses->count() }}</p>
                    <button class="btn btn-secondary" wire:click="editDetails('examSession')">Edit ExamSession</button>
                </div>
            @endforeach
        @endif

        <button class="btn btn-primary" wire:click="back">Back</button>
    @elseif (str_starts_with($mode, 'edit'))
        <div class="my-4">
            @if ($mode === 'edit-student')
                <h2>Edit Student Details</h2>
                <div class="mb-3">
                    <label for="firstName" class="form-label">First Name</label>
                    <input type="text" id="firstName" class="form-control" wire:model="student.first_name">
                </div>
                <div class="mb-3">
                    <label for="lastName" class="form-label">Last Name</label>
                    <input type="text" id="lastName" class="form-control" wire:model="student.last_name">
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" class="form-control" wire:model="student.email">
                </div>
                <button class="btn btn-success" wire:click="updateStudent">Save</button>
                <button class="btn btn-primary" wire:click="back">Back</button>
            @elseif ($mode === 'edit-user')
                <h2>Edit User Details</h2>
                <div class="mb-3">
                    <label for="userName" class="form-label">Name</label>
                    <input type="text" id="userName" class="form-control" wire:model="user.name">
                </div>
                <div class="mb-3">
                    <label for="userEmail" class="form-label">Email</label>
                    <input type="email" id="userEmail" class="form-control" wire:model="user.email">
                </div>
                <button class="btn btn-success" wire:click="updateUser">Save</button>
                <button class="btn btn-primary" wire:click="back">Back</button>
            @elseif ($mode === 'edit-examSession')
                <h2>Edit ExamSession Details</h2>
                <div class="mb-3">
                    <label for="startedAt" class="form-label">Started At</label>
                    <input type="text" id="startedAt" class="form-control" wire:model="examSession.started_at">
                </div>
                <div class="mb-3">
                    <label for="completedAt" class="form-label">Completed At</label>
                    <input type="text" id="completedAt" class="form-control"
                        wire:model="examSession.completed_at">
                </div>
                <button class="btn btn-success" wire:click="updateExamSession">Save</button>
                <button class="btn btn-primary" wire:click="back">Back</button>
            @endif
        </div>
    @endif
            </div>

        </div>




</div>
</div>