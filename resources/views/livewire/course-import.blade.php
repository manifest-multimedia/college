<div class="container mt-5">
    <h2>Import Courses</h2>
    
    <form wire:submit.prevent="importCourses">
        <div class="mb-3">
            <label for="file" class="form-label">Upload Excel File</label>
            <input type="file" wire:model="file" class="form-control" id="file" />
            @error('file') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="mb-3">
            @if ($progress > 0 && $progress < 100)
                <div class="progress">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: {{ $progress }}%" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <p>{{ $progress }}% uploaded...</p>
            @elseif ($progress == 100)
                <div class="alert alert-success">
                    Import Complete! {{ $progress }}%
                </div>
            @endif
        </div>

        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
            @if ($importing) Importing... @else Import Courses @endif
        </button>
    </form>

    @if (session()->has('message'))
        <div class="mt-3 alert alert-success">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mt-3 alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
</div>
