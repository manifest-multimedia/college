<x-dashboard.default>
    <x-slot name="title">
        Grade Type Details
    </x-slot>
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h1 class="card-title">
                                <i class="fas fa-star me-2"></i>Grade Type: {{ $grade->name }}
                            </h1>
                            <div>
                                <a href="{{ route('academics.grades.edit', $grade) }}" class="btn btn-primary me-2">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </a>
                                <a href="{{ route('academics.grades.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> Back to Grade Types
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-striped">
                                    <tbody>
                                        <tr>
                                            <th style="width: 30%;">Name</th>
                                            <td>{{ $grade->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Type</th>
                                            <td>{{ $grade->type ?? 'Not specified' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Value</th>
                                            <td>{{ $grade->value ?? 'Not specified' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Description</th>
                                            <td>{{ $grade->description ?? 'No description available' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Created At</th>
                                            <td>{{ $grade->created_at->format('F j, Y, g:i a') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Last Updated</th>
                                            <td>{{ $grade->updated_at->format('F j, Y, g:i a') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <h3>Usage Statistics</h3>
                            <p>This grade type is used in <strong>{{ $grade->studentGrades()->count() }}</strong> student records.</p>
                            
                            <button type="button" class="btn btn-danger" 
                                    onclick="event.preventDefault(); 
                                             if(confirm('Are you sure you want to delete this grade type? This action cannot be undone.')) { 
                                                document.getElementById('delete-form').submit(); 
                                             }">
                                <i class="fas fa-trash me-1"></i> Delete Grade Type
                            </button>
                            <form id="delete-form" action="{{ route('academics.grades.destroy', $grade) }}" method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard.default>