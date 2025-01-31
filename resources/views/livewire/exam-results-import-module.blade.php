<div>
    <div class="card">
        <div class="card-header">
            <h3>Import Exam Results</h3>
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

            <div class="mb-3">
                <label for="file" class="form-label">Select File</label>
                <input type="file" class="form-control" wire:model="file">
            </div>

            @if($importing)
                <div class="progress mb-3">
                    <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%">
                        {{ $progress }}%
                    </div>
                </div>
            @endif

            <button class="btn btn-primary" wire:click="import" @disabled(!$file || !$selected_exam_id)>
                Import Results
            </button>
        </div>
    </div>
</div> 