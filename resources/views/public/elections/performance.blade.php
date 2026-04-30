<x-public.layout :title="'Election Performance'">
    <div class="container py-4" id="performanceApp" data-url="{{ route('public.elections.performance.data', $election) }}">
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                    <div>
                        <h2 class="mb-1">{{ $election->name }}</h2>
                        <p class="text-muted mb-2">Real-time candidate and position performance</p>
                        <span id="electionStatus" class="badge bg-secondary">Loading status...</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('public.elections.show', $election) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to Election
                        </a>
                        <button id="refreshButton" class="btn btn-primary" type="button">
                            <i class="fas fa-sync-alt me-1"></i> Refresh Now
                        </button>
                    </div>
                </div>
                <p class="text-muted mt-3 mb-0">
                    Last updated: <span id="lastUpdated">-</span>
                </p>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="card h-100 bg-primary text-white">
                    <div class="card-body">
                        <div class="small text-white-50">Positions</div>
                        <div id="summaryPositions" class="fs-3 fw-bold">0</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card h-100 bg-info text-white">
                    <div class="card-body">
                        <div class="small text-white-50">Total Votes</div>
                        <div id="summaryVotes" class="fs-3 fw-bold">0</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card h-100 bg-success text-white">
                    <div class="card-body">
                        <div class="small text-white-50">Total Voters</div>
                        <div id="summaryVoters" class="fs-3 fw-bold">0</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card h-100 bg-dark text-white">
                    <div class="card-body">
                        <div class="small text-white-50">Turnout</div>
                        <div id="summaryTurnout" class="fs-3 fw-bold">0%</div>
                    </div>
                </div>
            </div>
        </div>

        <div id="positionsContainer" class="d-flex flex-column gap-3">
            <div class="card">
                <div class="card-body text-center text-muted">Loading live performance...</div>
            </div>
        </div>
    </div>

    <script>
        (() => {
            const app = document.getElementById('performanceApp');
            if (!app) {
                return;
            }

            const dataUrl = app.dataset.url;
            const statusEl = document.getElementById('electionStatus');
            const lastUpdatedEl = document.getElementById('lastUpdated');
            const positionsContainer = document.getElementById('positionsContainer');
            const refreshButton = document.getElementById('refreshButton');

            const summaryPositions = document.getElementById('summaryPositions');
            const summaryVotes = document.getElementById('summaryVotes');
            const summaryVoters = document.getElementById('summaryVoters');
            const summaryTurnout = document.getElementById('summaryTurnout');

            function escapeHtml(value) {
                return String(value ?? '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#39;');
            }

            function statusBadgeClass(status) {
                if (status === 'active') {
                    return 'bg-success';
                }

                if (status === 'upcoming') {
                    return 'bg-warning text-dark';
                }

                if (status === 'completed') {
                    return 'bg-secondary';
                }

                return 'bg-dark';
            }

            function renderSummary(summary, election) {
                summaryPositions.textContent = summary.total_positions;
                summaryVotes.textContent = summary.total_votes;
                summaryVoters.textContent = summary.total_voters;
                summaryTurnout.textContent = `${summary.voter_turnout_percent}%`;

                statusEl.className = `badge ${statusBadgeClass(election.status)}`;
                statusEl.textContent = `Status: ${election.status.toUpperCase()}`;

                const updatedAt = new Date(summary.last_updated_at);
                lastUpdatedEl.textContent = Number.isNaN(updatedAt.getTime())
                    ? '-'
                    : updatedAt.toLocaleString();
            }

            function renderSingleCandidatePosition(position) {
                const candidate = position.candidate;
                if (!candidate) {
                    return `
                        <div class="card">
                            <div class="card-body">
                                <h5 class="mb-0">${escapeHtml(position.name)}</h5>
                                <p class="text-muted mb-0">No active candidate data found.</p>
                            </div>
                        </div>
                    `;
                }

                const resultBadge = position.has_won
                    ? '<span class="badge bg-success">APPROVED</span>'
                    : '<span class="badge bg-danger">REJECTED</span>';

                return `
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">${escapeHtml(position.name)}</h5>
                                <small class="text-muted">Single-candidate approval vote</small>
                            </div>
                            ${resultBadge}
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <img src="${escapeHtml(candidate.photo_url)}" alt="${escapeHtml(candidate.name)}" class="rounded-circle" style="width:64px;height:64px;object-fit:cover;" onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.png') }}';">
                                <div>
                                    <h6 class="mb-0">${escapeHtml(candidate.name)}</h6>
                                    <small class="text-muted">Total votes: ${position.total_votes}</small>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="small text-muted mb-1">YES (${position.yes_votes})</div>
                                    <div class="progress" style="height: 22px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width:${position.yes_percent}%">${position.yes_percent}%</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="small text-muted mb-1">NO (${position.no_votes})</div>
                                    <div class="progress" style="height: 22px;">
                                        <div class="progress-bar bg-danger" role="progressbar" style="width:${position.no_percent}%">${position.no_percent}%</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }

            function renderMultiCandidatePosition(position) {
                const candidateRows = (position.candidates || []).map((candidate) => `
                    <div class="d-flex align-items-center justify-content-between gap-3 py-2 border-bottom">
                        <div class="d-flex align-items-center gap-3">
                            <img src="${escapeHtml(candidate.photo_url)}" alt="${escapeHtml(candidate.name)}" class="rounded-circle" style="width:52px;height:52px;object-fit:cover;" onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.png') }}';">
                            <div>
                                <div class="fw-semibold">${escapeHtml(candidate.name)}</div>
                                <div class="small text-muted">${candidate.votes} votes</div>
                            </div>
                        </div>
                        <div class="text-end" style="min-width: 220px;">
                            <div class="small text-muted mb-1">${candidate.vote_percent}%</div>
                            <div class="progress" style="height: 14px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width:${candidate.vote_percent}%"></div>
                            </div>
                        </div>
                    </div>
                `).join('');

                return `
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">${escapeHtml(position.name)}</h5>
                                <small class="text-muted">${position.total_votes} total votes</small>
                            </div>
                        </div>
                        <div class="card-body py-2">
                            ${candidateRows || '<div class="text-muted py-2">No active candidates.</div>'}
                        </div>
                    </div>
                `;
            }

            function renderPositions(positions) {
                if (!Array.isArray(positions) || positions.length === 0) {
                    positionsContainer.innerHTML = `
                        <div class="card">
                            <div class="card-body text-center text-muted">No positions available for this election.</div>
                        </div>
                    `;
                    return;
                }

                positionsContainer.innerHTML = positions.map((position) => {
                    if (position.type === 'single_candidate_yes_no') {
                        return renderSingleCandidatePosition(position);
                    }

                    return renderMultiCandidatePosition(position);
                }).join('');
            }

            async function loadPerformance() {
                try {
                    refreshButton.disabled = true;
                    const response = await fetch(dataUrl, {
                        headers: {
                            'Accept': 'application/json',
                        },
                    });

                    if (!response.ok) {
                        throw new Error(`Failed with status ${response.status}`);
                    }

                    const payload = await response.json();
                    renderSummary(payload.summary, payload.election);
                    renderPositions(payload.positions);
                } catch (error) {
                    positionsContainer.innerHTML = `
                        <div class="card border-danger">
                            <div class="card-body text-danger">
                                Unable to load performance data. Please try again.
                            </div>
                        </div>
                    `;
                } finally {
                    refreshButton.disabled = false;
                }
            }

            refreshButton.addEventListener('click', loadPerformance);
            loadPerformance();
            setInterval(loadPerformance, 10000);
        })();
    </script>
</x-public.layout>
