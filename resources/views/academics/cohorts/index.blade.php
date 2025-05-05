<x-dashboard.default>
    <x-slot name="title">
        Cohorts
    </x-slot>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h1 class="card-title">
                                <i class="fas fa-users me-2"></i>Cohorts
                            </h1>
                            <a href="{{ route('academics.cohorts.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus-circle me-1"></i> Create New Cohort
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

                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Students</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($cohorts as $cohort)
                                        <tr>
                                            <td>{{ $cohort->name }}</td>
                                            <td>{{ $cohort->start_date ? $cohort->start_date->format('M d, Y') : 'N/A' }}</td>
                                            <td>{{ $cohort->end_date ? $cohort->end_date->format('M d, Y') : 'N/A' }}</td>
                                            <td>{{ $cohort->students_count ?? 0 }}</td>
                                            <td>
                                                @if($cohort->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('academics.cohorts.show', $cohort) }}" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('academics.cohorts.edit', $cohort) }}" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger" 
                                                            onclick="event.preventDefault(); 
                                                                     if(confirm('Are you sure you want to delete this cohort?')) { 
                                                                        document.getElementById('delete-form-{{ $cohort->id }}').submit(); 
                                                                     }">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    <form id="delete-form-{{ $cohort->id }}" action="{{ route('academics.cohorts.destroy', $cohort) }}" method="POST" style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No cohorts found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            {{ $cohorts->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard.default>