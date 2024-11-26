<div>
    {{-- Nothing in the world is as soft and yielding as water. --}}
    <div class="mt-20 card">
        <div class="card-header">
            <h3 class="card-title">Create New Exam</h3>
       
        </div>
        <div class="container mt-10">

            {{-- If Any Errors --}}
            @if($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            
        @endif
        </div>
        <!-- Begin Exam Creation Form -->
        <form wire:submit.prevent="createExam">
            <div class="card-body">
    <div class="mb-3">
        {{-- Class --}}
        <label for="class" class="form-label">Program</label>
        <select class="form-select" name="class" wire:model.live="class" id="class">
            <option value="">Select a class</option>
            @foreach ($classes as $class)
                <option value="{{ $class->id }}">{{ $class->name }}</option>
            @endforeach
        </select>
        @error('class') <!-- Display error message for class -->
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
    {{-- Year --}}
    <div class="mb-3">
        <label for="year" class="form-label">Year</label>
        <select class="form-select" name="year" wire:model.live="year" id="year">
            <option value="">Select a year</option>
            @foreach ($years as $year)
                <option value="{{ $year->id }}">{{ $year->name }}</option>
            @endforeach
        </select>
        @error('year') <!-- Display error message for year -->
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
    {{-- Semester --}}
    <div class="mb-3">
        <label for="semester" class="form-label">Semester</label>
        <select class="form-select" name="semester" wire:model.live="semester" id="semester">
            <option value="">Select a semester</option>
            @foreach ($semesters as $semester)
                <option value="{{ $semester->id }}">{{ $semester->name }}</option>
            @endforeach
        </select>
        @error('semester') <!-- Display error message for semester -->
            <span class="text-danger">{{ $message }}</span>
        @enderror
    </div>
                <!-- Course Code -->
                <div class="mb-3">
                    <label for="course" class="form-label">Course</label>
<select class="form-select" name="course_code" wire:model="course_code" id="course_code">
                        <option value="">Select a course</option>
                        @foreach ($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->name }} ({{ $course->course_code }}) </option>
                        @endforeach
                    </select>                    @error('course_code') <!-- Display error message for course_code -->
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
    
                <!-- Exam Type -->
                <div class="mb-3">
                    <label for="exam_type" class="form-label">Exam Type</label>
                    <select id="exam_type" class="form-select" wire:model="exam_type">
                        <option value="mcq">MCQ</option>
                        <option value="short_answer">Short Answer</option>
                        <option value="essay">Essay</option>
                        <option value="mixed">Mixed</option>
                    </select>
                    @error('exam_type') <!-- Display error message for exam_type -->
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
    
                <!-- Exam Duration -->
                <div class="mb-3">
                    <label for="exam_duration" class="form-label">Exam Duration (minutes)</label>
                    <input type="number" id="exam_duration" class="form-control" wire:model="exam_duration">
                    @error('exam_duration') <!-- Display error message for exam_duration -->
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
    
                <!-- Exam Password (hidden from lecturer) -->
                <div class="mb-3">
                    {{-- <label for="exam_password" class="form-label">Exam Password</label> --}}
                    <input type="hidden" id="exam_password" class="form-control" wire:model="exam_password" readonly>
                    @error('exam_password') <!-- Display error message for exam_password -->
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                @if(Auth::user()->role=='Super Admin')
                {{-- List Users --}}
                <div class="mb-3">
                    <label for="users" class="form-label">Users</label>
                    <select class="form-select" name="users" wire:model.live="user_id" id="users">
                        <option value="">Select a user</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }}) </option>
                        @endforeach
                    </select>
                    @error('users') <!-- Display error message for users -->
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                @endif
    
                <!-- Submit Button -->
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Create Exam</button>
                </div>
            </div>
        </form>
        <!-- End Exam Creation Form -->
    </div>
    
</div>
