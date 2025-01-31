<div>
    <div class="card">
        <div class="card-header">
            <h3>Export Exam Results</h3>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label for="exam-select" class="form-label">Select Exam</label>
                <select class="form-select" wire:model="selected_exam_id">
                    <option value="">Select Exam</option>
                    @foreach($exams as $exam)
                        <option value="{{ $exam->id }}">{{ $exam->course->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <button class="btn btn-primary" wire:click="export" @disabled(!$selected_exam_id)>
                Export Results
            </button>
        </div>
    </div>
</div> 