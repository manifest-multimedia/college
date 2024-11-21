    <div>
    {{-- Nothing in the world is as soft and yielding as water. --}}
    <div class="mt-20 card">
        <div class="card-header">
            <h3 class="card-title">Create New Exam</h3>
        </div>
    
        <!-- Begin Exam Creation Form -->
        <form wire:submit.prevent="createExam">
            <div class="card-body">
    
                <!-- Course Code -->
                <div class="mb-3">
                    <label for="course_code" class="form-label">Course Code</label>
                    <input type="text" id="course_code" class="form-control" wire:model="course_code">
                    @error('course_code') <!-- Display error message for course_code -->
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
                    <label for="exam_password" class="form-label">Exam Password</label>
                    <input type="text" id="exam_password" class="form-control" wire:model="exam_password" readonly>
                    @error('exam_password') <!-- Display error message for exam_password -->
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
    
                <!-- Submit Button -->
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Create Exam</button>
                </div>
            </div>
        </form>
        <!-- End Exam Creation Form -->
    </div>
    
</div>
