<x-dashboard.default title="Election Voter Integrity">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-1">Voter Integrity Investigation</h3>
                <p class="text-muted mb-0">Election: {{ $election->name }}</p>
            </div>
            <a href="{{ route('election.results', $election) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Results
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('admin.election.voter-integrity', $election) }}" class="row g-3 align-items-end">
                    <div class="col-md-8">
                        <label for="student_id" class="form-label">Student ID</label>
                        <input id="student_id" name="student_id" type="text" class="form-control" value="{{ $studentId }}" placeholder="Enter student ID to investigate">
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i> Investigate
                        </button>
                        <a href="{{ route('admin.election.voter-integrity', $election) }}" class="btn btn-light">Clear</a>
                    </div>
                </form>
            </div>
        </div>

        @if ($studentId !== '')
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Student Profile</h5>
                </div>
                <div class="card-body">
                    @if ($student)
                        <div class="row">
                            <div class="col-md-4"><strong>Student ID:</strong> {{ $student->student_id }}</div>
                            <div class="col-md-4"><strong>Name:</strong> {{ $student->name }}</div>
                            <div class="col-md-4"><strong>Email:</strong> {{ $student->email ?: 'N/A' }}</div>
                        </div>
                    @else
                        <div class="alert alert-warning mb-0">No student record found for this ID in the students table.</div>
                    @endif
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="text-muted">Votes Found</div>
                            <div class="fs-3 fw-bold">{{ $votes->count() }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="text-muted">Voting Sessions</div>
                            <div class="fs-3 fw-bold">{{ $sessions->count() }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="text-muted">Audit Events</div>
                            <div class="fs-3 fw-bold">{{ $auditLogs->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Admin Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <form method="POST" action="{{ route('admin.election.voter-integrity.nullify', $election) }}">
                                @csrf
                                <input type="hidden" name="student_id" value="{{ $studentId }}">
                                <div class="mb-2">
                                    <label class="form-label">Reason (optional)</label>
                                    <textarea class="form-control" name="reason" rows="3" placeholder="Reason for nullifying votes"></textarea>
                                </div>
                                <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Nullify this student\'s votes for this election?')">
                                    <i class="fas fa-trash me-1"></i> Nullify Votes (Keep voter blocked)
                                </button>
                            </form>
                        </div>

                        <div class="col-lg-6">
                            <form method="POST" action="{{ route('admin.election.voter-integrity.allow-revote', $election) }}">
                                @csrf
                                <input type="hidden" name="student_id" value="{{ $studentId }}">
                                <div class="mb-2">
                                    <label class="form-label">Reason (optional)</label>
                                    <textarea class="form-control" name="reason" rows="3" placeholder="Reason for re-vote authorization"></textarea>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" value="1" id="nullify_existing_votes" name="nullify_existing_votes" checked>
                                    <label class="form-check-label" for="nullify_existing_votes">
                                        Also nullify existing votes before allowing re-vote
                                    </label>
                                </div>
                                <button type="submit" class="btn btn-success" onclick="return confirm('Allow re-vote for this student in this election?')">
                                    <i class="fas fa-rotate-right me-1"></i> Allow Re-vote
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Vote Trail (Where and When)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Position</th>
                                    <th>Candidate</th>
                                    <th>Vote Type</th>
                                    <th>IP</th>
                                    <th>User Agent</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($votes as $vote)
                                    <tr>
                                        <td>{{ $vote->created_at?->format('Y-m-d H:i:s') }}</td>
                                        <td>{{ $vote->position?->name ?: 'N/A' }}</td>
                                        <td>{{ $vote->candidate?->name ?: 'N/A' }}</td>
                                        <td>{{ $vote->vote_type }}</td>
                                        <td>{{ $vote->ip_address ?: 'N/A' }}</td>
                                        <td>{{ \Illuminate\Support\Str::limit($vote->user_agent ?: 'N/A', 70) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No votes recorded for this student in this election.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Session Trail</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Session ID</th>
                                    <th>Started</th>
                                    <th>Expires</th>
                                    <th>Completed</th>
                                    <th>Submitted</th>
                                    <th>IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($sessions as $session)
                                    <tr>
                                        <td>{{ $session->session_id }}</td>
                                        <td>{{ $session->started_at?->format('Y-m-d H:i:s') }}</td>
                                        <td>{{ $session->expires_at?->format('Y-m-d H:i:s') }}</td>
                                        <td>{{ $session->completed_at?->format('Y-m-d H:i:s') ?: 'N/A' }}</td>
                                        <td>
                                            @if ($session->vote_submitted)
                                                <span class="badge bg-success">Yes</span>
                                            @else
                                                <span class="badge bg-secondary">No</span>
                                            @endif
                                        </td>
                                        <td>{{ $session->ip_address ?: 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No sessions found for this student in this election.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Security/Audit Events</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Event</th>
                                    <th>Description</th>
                                    <th>IP</th>
                                    <th>User Agent</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($auditLogs as $log)
                                    <tr>
                                        <td>{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                                        <td>{{ $log->event }}</td>
                                        <td>{{ $log->description }}</td>
                                        <td>{{ $log->ip_address ?: 'N/A' }}</td>
                                        <td>{{ \Illuminate\Support\Str::limit($log->user_agent ?: 'N/A', 70) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No audit events found for this student in this election.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Shared IP Activity (Potential Collusion Signal)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Other Student ID</th>
                                    <th>Shared IP</th>
                                    <th>Votes Count</th>
                                    <th>Last Vote Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($sharedIpActivity as $activity)
                                    <tr>
                                        <td>{{ $activity->student_id }}</td>
                                        <td>{{ $activity->ip_address }}</td>
                                        <td>{{ $activity->votes_count }}</td>
                                        <td>{{ $activity->last_vote_at }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No shared-IP vote activity found for this voter.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-dashboard.default>
