<div>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Edit Exam</h3>
            </div>
            
            <div class="card-body">
                @if (session()->has('message'))
                    <div class="alert alert-success">
                        {{ session('message') }}
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Display Course Info (Non-editable) -->
                <div class="mb-4">
                    <h4>{{ $course->name }}</h4>
                    <p class="text-muted">{{ $exam->description }}</p>
                </div>

                <form wire:submit.prevent="updateExam">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="duration" class="form-label">Duration (minutes)</label>
                                <input type="number" class="form-control @error('duration') is-invalid @enderror" 
                                    wire:model="duration" id="duration">
                                @error('duration') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="questions_per_session" class="form-label">Questions Per Session</label>
                                <input type="number" class="form-control @error('questions_per_session') is-invalid @enderror" 
                                    wire:model="questions_per_session" id="questions_per_session">
                                @error('questions_per_session') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">Exam Password</label>
                                <input type="text" class="form-control @error('password') is-invalid @enderror" 
                                    wire:model="password" id="password">
                                @error('password') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status (Current: {{ $status }})</label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                    wire:model="status" id="status">
                                    <option value="">Select Status</option>
                                    <option value="upcoming">Upcoming</option>
                                    <option value="active">Active</option>
                                    <option value="completed">Completed</option>
                                </select>
                                @error('status') 
                                    <span class="invalid-feedback">{{ $message }}</span> 
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="passing_percentage" class="form-label">Passing Percentage</label>
                        <input type="number" step="0.01" class="form-control @error('passing_percentage') is-invalid @enderror" 
                            wire:model="passing_percentage" id="passing_percentage">
                        @error('passing_percentage') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-secondary" wire:click="cancel">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Exam</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> 