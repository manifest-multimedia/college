<x-dashboard.default>
    <x-slot name="title">
        Edit Student Grade
    </x-slot>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h1 class="card-title">
                                <i class="fas fa-edit me-2"></i>Edit Student Grade
                            </h1>
                            <a href="{{ route('academics.student-grades.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back to Grades
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form action="{{ route('academics.student-grades.update', $studentGrade) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="student_id" class="form-label">Student <span class="text-danger">*</span></label>
                                        <select name="student_id" id="student_id" class="form-select @error('student_id') is-invalid @enderror" required>
                                            <option value="">Select Student</option>
                                            @foreach($students as $student)
                                                <option value="{{ $student->id }}" 
                                                    {{ old('student_id', $studentGrade->student_id) == $student->id ? 'selected' : '' }}>
                                                    {{ $student->first_name }} {{ $student->last_name }} ({{ $student->student_id }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('student_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="college_class_id" class="form-label">Program <span class="text-danger">*</span></label>
                                        <select name="college_class_id" id="college_class_id" class="form-select @error('college_class_id') is-invalid @enderror" required>
                                            <option value="">Select Program</option>
                                            @foreach($collegeClasses as $class)
                                                <option value="{{ $class->id }}" 
                                                    {{ old('college_class_id', $studentGrade->college_class_id) == $class->id ? 'selected' : '' }}>
                                                    {{ $class->name }} @if($class->getProgramCode())({{ $class->getProgramCode() }})@endif
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('college_class_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="grade_id" class="form-label">Grade <span class="text-danger">*</span></label>
                                        <select name="grade_id" id="grade_id" class="form-select @error('grade_id') is-invalid @enderror" required>
                                            <option value="">Select Grade</option>
                                            @foreach($gradeTypes as $gradeType)
                                                <option value="{{ $gradeType->id }}" 
                                                    {{ old('grade_id', $studentGrade->grade_id) == $gradeType->id ? 'selected' : '' }}>
                                                    {{ $gradeType->letter }} ({{ $gradeType->value }}) - {{ $gradeType->description }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('grade_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="comments" class="form-label">Comments</label>
                                        <textarea name="comments" id="comments" rows="3" 
                                            class="form-control @error('comments') is-invalid @enderror" 
                                            placeholder="Enter any comments about this grade...">{{ old('comments', $studentGrade->comments) }}</textarea>
                                        @error('comments')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <a href="{{ route('academics.student-grades.index') }}" class="btn btn-secondary me-2">
                                    <i class="fas fa-times me-1"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Update Grade
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard.default>