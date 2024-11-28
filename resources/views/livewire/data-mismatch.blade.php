<div>
    @if ($mode === 'index')
        <h2>Data Mismatch Dashboard</h2>
        <input type="text" wire:model="filter_student_id" placeholder="Search by Student ID">
        <input type="text" wire:model="filter_email" placeholder="Search by Email">
        <input type="text" wire:model="filter_exam_id" placeholder="Search by Exam ID">

        <table>
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Email</th>
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
                            <button wire:click="viewDetails({{ $student->id }})">View</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @elseif ($mode === 'view')
        <h2>Details for Student: {{ $student->first_name }} {{ $student->last_name }}</h2>
        <h3>User Account</h3>
        <p>Name: {{ $user->name }}</p>
        <p>Email: {{ $user->email }}</p>
        <button wire:click="editDetails('user')">Edit User</button>

        <h3>Student Details</h3>
        <p>Student ID: {{ $student->student_id }}</p>
        <p>Email: {{ $student->email }}</p>
        <button wire:click="editDetails('student')">Edit Student</button>

        <h3>ExamSession</h3>
        <p>Exam ID: {{ $examSession->exam_id }}</p>
        <p>Started At: {{ $examSession->started_at }}</p>
        <button wire:click="editDetails('examSession')">Edit ExamSession</button>
    @elseif (str_starts_with($mode, 'edit'))
        @if ($mode === 'edit-student')
            <h2>Edit Student Details</h2>
            <input type="text" wire:model="student.first_name">
            <input type="text" wire:model="student.last_name">
            <input type="email" wire:model="student.email">
            <button wire:click="updateStudent">Save</button>
        @elseif ($mode === 'edit-user')
            <h2>Edit User Details</h2>
            <input type="text" wire:model="user.name">
            <input type="email" wire:model="user.email">
            <button wire:click="updateUser">Save</button>
        @elseif ($mode === 'edit-examSession')
            <h2>Edit ExamSession Details</h2>
            <input type="text" wire:model="examSession.started_at">
            <input type="text" wire:model="examSession.completed_at">
            <button wire:click="updateExamSession">Save</button>
        @endif
    @endif
</div>
