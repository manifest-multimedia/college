<div class="container">
    @if ($mode === 'index')
        <h2 class="my-4">Data Mismatch Dashboard</h2>
   
        <!-- Filters -->
        <div class="mb-4">
            <div class="row g-2">
                <div class="col-md-4">
                    <input type="text" class="form-control" wire:model.live="filter_student_id" placeholder="Search by Student ID">
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control" wire:model.live="filter_email" placeholder="Search by Email">
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-control" wire:model.live="filter_exam_id" placeholder="Search by Exam ID">
                </div>
            </div>
        </div>

        <div class="rounded border-2 border-dark">
            <h1>
                Records for Leticia, RGN Class 
                
            </h1>
            <p>
                {{-- Check if Env is Production --}}
                @if(env('APP_ENV') === 'production')
                    <span class="badge bg-success">Production</span>
                @if(\App\Models\ExamSession::where('student_id', 131)->count() > 0)
                @foreach (\App\Models\ExamSession::where('student_id', 131)->get() as $sessions)
            {{ \App\Models\User::where('id', 131)->first()->name }} <br>   
                {{ $sessions->exam->exam_code }} <br>
                    {{ $sessions->exam->course->name }} <br>
                    {{ $sessions->responses->count() }} <br>
                    <hr>
                    
                @endforeach
                @endif
                @else
                    <span class="badge bg-warning">Local</span>
                    {{-- ID on Local is 90 --}}
                    @if(\App\Models\ExamSession::where('student_id', 90)->count() > 0)
                    @foreach (\App\Models\ExamSession::where('student_id', 90)->get() as $sessions)
                {{ \App\Models\User::where('id', 90)->first()->name }} <br>   
                    {{ $sessions->exam->exam_code }} <br>
                        {{ $sessions->exam->course->name }} <br>
                        {{ $sessions->responses->count() }} <br>
                        <hr>
                        
                    @endforeach
                    @endif
                @endif 
            </p>
        </div>


        <!-- Table -->
        <div class="table-responsive">
            <table class="table align-middle table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Sessions & Responses</th>
                        <th>Actions</th>
                    </tr>
                </thead>

               
                <tbody>
                    @foreach ($students as $student)
                        <tr>
                            <td>{{ $student->student_id }}</td>
                            <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                            <td>{{ $student->email }}</td>
                            <td>
                                @if(\App\Models\ExamSession::where('student_id', $student->id)->count() > 0)
                                    
                                <span class="text-white badge bg-dark">Sessions: {{ \App\Models\ExamSession::where('student_id', $student->id)->count() }}</span>
                                    @foreach (\App\Models\ExamSession::where('student_id', $student->id)->get() as $session)
                                <div class="p-2 rounded border border-1 border-success">
                                Course Name: {{ optional($session->exam->course)->name }}<br>    
                                <span class="badge bg-success">Responses: {{ $session->responses->count() }}</span>
                                </div>        
                                    @endforeach
                                @else
                                    <span class="badge bg-danger">No sessions or responses found</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-primary btn-sm" wire:click="viewDetails({{ $student->id }})">View</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-center">
            {{ $students->links() }}
        </div>
    @elseif ($mode === 'view')
        <h2 class="my-4">Details for Student: {{ $student->first_name }} {{ $student->last_name }}{{ $student->other_name }}</h2>
        
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
        {{-- Count Exam Sessions --}}
        @if($examSessions->count() > 0)
        @foreach ($examSessions as $examSession)
            
        <div class="mb-4">
            <h3>Exam Session</h3>
            {{-- <p><strong>Exam ID:</strong> {{ $examSession->exam_id }}</p> --}}
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
                    <input type="text" id="completedAt" class="form-control" wire:model="examSession.completed_at">
                </div>
                <button class="btn btn-success" wire:click="updateExamSession">Save</button>
                <button class="btn btn-primary" wire:click="back">Back</button>
            @endif
        </div>
    @endif
    
</div>
