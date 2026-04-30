<x-dashboard.default title="Voter Integrity">
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Select Election for Voter Integrity</h4>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Election</th>
                                <th>Start</th>
                                <th>End</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($elections as $election)
                                <tr>
                                    <td>{{ $election->name }}</td>
                                    <td>{{ $election->start_time?->format('Y-m-d H:i') }}</td>
                                    <td>{{ $election->end_time?->format('Y-m-d H:i') }}</td>
                                    <td>
                                        @if ($election->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.election.voter-integrity', ['election' => $election->id]) }}">
                                            Open Voter Integrity
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No elections found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-dashboard.default>
