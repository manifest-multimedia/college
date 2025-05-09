<div class="container mt-4">
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        <p>Processed File Path: {{ session('file_path') }}</p>
    @endif

    <form action="{{ route('file.upload') }}" method="POST" enctype="multipart/form-data">
        @csrf
        {{-- <div class="mb-3">
        
            <label for="exam_id" class="form-label">Exam ID</label>
            <input type="text" name="exam_id" id="exam_id" class="form-control" value="{{ $examId }}" required>
        </div> --}}
        <div class="mb-3">
            <label for="file" class="form-label">Choose File</label>
            <div class="input-group">
                <input type="file" name="file" id="file" class="form-control" required>
                <button type="submit" class="btn btn-primary">Upload</button>
            </div>
        </div>
    </form>
</div>
