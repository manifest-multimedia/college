<x-dashboard.default>
    <x-slot name="title">
        Create Student Grade
    </x-slot>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h1 class="card-title">
                                <i class="fas fa-graduation-cap me-2"></i>Assign Student Grade
                            </h1>
                            <div>
                                <a href="{{ route('academics.student-grades.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> Back to Grades
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('academics.student-grades.store') }}">
                            @csrf
                            
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <div class="alert alert-info">
                                        @if($currentSemester)
                                            <p class="mb-0">
                                                <i class="fas fa-info-circle me-2"></i>
                                                You are assigning grades for <strong>{{ $currentSemester->name }} ({{ $currentSemester->academicYear->name }})</strong>.
                                            </p>
                                        @else
                                            <p class="mb-0">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                No current semester is set. Please set a current semester in 
                                                <a href="{{ route('academics.settings') }}">Academic Settings</a>.
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="student_id" class="form-label">Student <span class="text-danger">*</span></label>
                                    <select name="student_id" id="student_id" class="form-select @error('student_id') is-invalid @enderror" required>
                                        <option value="">-- Select Student --</option>
                                        @foreach($students as $student)
                                            <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                                {{ $student->first_name }} {{ $student->last_name }} ({{ $student->student_id ?? 'No ID' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('student_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="college_class_id" class="form-label">Class <span class="text-danger">*</span></label>
                                    <select name="college_class_id" id="college_class_id" class="form-select @error('college_class_id') is-invalid @enderror" required>
                                        <option value="">-- Select Class --</option>
                                        @foreach($collegeClasses as $class)
                                            <option value="{{ $class->id }}" {{ old('college_class_id') == $class->id ? 'selected' : '' }}>
                                                {{ $class->name }} - {{ $class->course->title }} 
                                                ({{ $class->semester->name }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('college_class_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="grade_id" class="form-label">Grade <span class="text-danger">*</span></label>
                                    <select name="grade_id" id="grade_id" class="form-select @error('grade_id') is-invalid @enderror" required>
                                        <option value="">-- Select Grade --</option>
                                        @foreach($gradeTypes as $grade)
                                            <option value="{{ $grade->id }}" {{ old('grade_id') == $grade->id ? 'selected' : '' }}>
                                                {{ $grade->letter }} ({{ $grade->name }}) - {{ $grade->value }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('grade_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="comments" class="form-label">Comments</label>
                                    <textarea name="comments" id="comments" class="form-control @error('comments') is-invalid @enderror" rows="3">{{ old('comments') }}</textarea>
                                    @error('comments')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Assign Grade
                                    </button>
                                    <a href="{{ route('academics.student-grades.index') }}" class="btn btn-secondary ms-2">
                                        Cancel
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard.default>