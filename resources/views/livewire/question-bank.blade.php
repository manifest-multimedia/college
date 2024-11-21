<div>
    {{-- Stop trying to control. --}}
    <div class="mt-5 mb-5 card mb-xl-10">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="text-gray-900 card-title fw-bold">Question Bank</h3>
            
            <div class="gap-4 d-flex align-items-center">
                <div class="filter-option d-flex align-items-center">
                    <label for="statusFilter" class="form-label me-2">Status:</label>
                    <select id="statusFilter" class="form-select" wire:model="filter" aria-label="Filter by status">
                        <option value="all">All</option>
                        <option value="upcoming">Upcoming</option>
                        <option value="active">Active</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                
                <div class="filter-option d-flex align-items-center">
                    <label for="searchInput" class="form-label me-2">Search:</label>
                    <input type="text" id="searchInput" class="form-control" wire:model="search" placeholder="Search..." aria-label="Search question bank">
                </div>
            </div>
          
        </div>
        @forelse ($questions as $question)
                
        @empty
        <div class="py-10 text-center fs-6 fw-bold">
            <p>Your Question bank is Empty. Select an Exam to view its questions or create a new one.</p>
            <div class="gap-3 mt-5 d-flex justify-content-center align-items-center">
                <a class="btn btn-sm btn-success" href="{{ route('questionbank') }}">Create a New Question</a>
                <span class="mx-2">or</span>
                <a class="btn btn-sm btn-primary" href="{{ route('questionbank') }}">Bulk Import Questions</a>
            </div>
        </div>
        
        @endforelse
    </div>
    
    
</div>
