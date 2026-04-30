<x-public.layout :title="'Election Performance'">
    <div class="container py-4" id="performanceApp" data-url="{{ route('public.elections.performance.data', $election) }}">
        <div class="card mb-4">
            <div class="card-body">
                <div>
                    <h2 class="mb-1">{{ $election->name }}</h2>
                    <p class="text-muted mb-2">Real-time candidate and position performance</p>
                    <span id="electionStatus" class="badge bg-secondary">Loading status...</span>
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

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Live Position Leaderboard</h5>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button id="prevSlide" type="button" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <span id="slideCounter" class="small text-muted">1 / 1</span>
                    <button id="nextSlide" type="button" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="positionsContainer" class="mb-3">
                    <div class="text-center text-muted py-4">Loading live performance...</div>
                </div>
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
            const slideCounter = document.getElementById('slideCounter');
            const prevSlideButton = document.getElementById('prevSlide');
            const nextSlideButton = document.getElementById('nextSlide');

            const summaryPositions = document.getElementById('summaryPositions');
            const summaryVotes = document.getElementById('summaryVotes');
            const summaryVoters = document.getElementById('summaryVoters');
            const summaryTurnout = document.getElementById('summaryTurnout');

            let slideshowIndex = 0;
            let slideshowTimer = null;
            let positionsCache = [];

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
                    <div class="card border-0 shadow-sm overflow-hidden">
                        <div class="card-body p-4 p-lg-5 bg-light border-bottom">
                            <div class="row align-items-center g-4">
                                <div class="col-lg-8">
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <span class="badge bg-dark">Single Candidate Vote</span>
                                        ${resultBadge}
                                    </div>
                                    <h2 class="mb-2">${escapeHtml(position.name)}</h2>
                                    <p class="text-muted mb-0">${position.total_votes} ballots recorded for this approval position.</p>
                                </div>
                                <div class="col-lg-4">
                                    <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3 justify-content-lg-end">
                                        <img src="${escapeHtml(candidate.photo_url)}" alt="${escapeHtml(candidate.name)}" class="rounded-4 shadow-sm w-100" style="max-width: 240px; aspect-ratio: 750 / 338; object-fit: cover;" onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.png') }}';">
                                        <div>
                                            <div class="text-uppercase small text-muted">Candidate</div>
                                            <h4 class="mb-1">${escapeHtml(candidate.name)}</h4>
                                            <div class="text-muted">Approval outcome in real time</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-4 p-lg-5">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="border rounded-4 p-4 h-100">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fw-semibold text-success">YES</span>
                                            <span class="fw-bold">${position.yes_votes} votes</span>
                                        </div>
                                        <div class="progress" style="height: 26px;">
                                            <div class="progress-bar bg-success fw-semibold" role="progressbar" style="width:${position.yes_percent}%">${position.yes_percent}%</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded-4 p-4 h-100">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="fw-semibold text-danger">NO</span>
                                            <span class="fw-bold">${position.no_votes} votes</span>
                                        </div>
                                        <div class="progress" style="height: 26px;">
                                            <div class="progress-bar bg-danger fw-semibold" role="progressbar" style="width:${position.no_percent}%">${position.no_percent}%</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }

            function renderMultiCandidatePosition(position) {
                const leadingCandidate = (position.candidates || [])[0] ?? null;
                const challengerRows = (position.candidates || []).map((candidate, index) => `
                    <div class="col-12 col-xl-6">
                        <div class="border rounded-4 p-3 h-100 ${index === 0 ? 'border-primary bg-primary bg-opacity-10' : 'bg-white'}">
                            <div class="position-relative mb-3">
                                <img src="${escapeHtml(candidate.photo_url)}" alt="${escapeHtml(candidate.name)}" class="rounded-4 shadow-sm w-100" style="aspect-ratio: 750 / 338; object-fit: cover;" onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.png') }}';">
                                <span class="position-absolute top-0 start-0 m-3 badge rounded-pill ${index === 0 ? 'bg-warning text-dark' : 'bg-secondary'}">#${index + 1}</span>
                            </div>
                            <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                                <div class="flex-grow-1 min-w-0">
                                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                        <h4 class="mb-0">${escapeHtml(candidate.name)}</h4>
                                        ${index === 0 ? '<span class="badge bg-warning text-dark">LEADING</span>' : ''}
                                    </div>
                                    <div class="text-muted">${candidate.votes} votes</div>
                                </div>
                                <div class="text-end">
                                    <div class="fs-4 fw-bold text-primary">${candidate.vote_percent}%</div>
                                </div>
                            </div>
                            <div class="progress" style="height: 18px;">
                                <div class="progress-bar ${index === 0 ? 'bg-primary' : 'bg-secondary'}" role="progressbar" style="width:${candidate.vote_percent}%"></div>
                            </div>
                        </div>
                    </div>
                `).join('');

                return `
                    <div class="card border-0 shadow-sm overflow-hidden">
                        <div class="card-body p-4 p-lg-5 bg-light border-bottom">
                            <div class="row g-4 align-items-center">
                                <div class="col-lg-8">
                                    <div class="text-uppercase small text-muted mb-2">Position</div>
                                    <h2 class="mb-2">${escapeHtml(position.name)}</h2>
                                    <p class="text-muted mb-0">${position.total_votes} total votes counted for this race.</p>
                                </div>
                                <div class="col-lg-4">
                                    ${leadingCandidate ? `
                                        <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3 justify-content-lg-end">
                                            <img src="${escapeHtml(leadingCandidate.photo_url)}" alt="${escapeHtml(leadingCandidate.name)}" class="rounded-4 shadow-sm w-100" style="max-width: 280px; aspect-ratio: 750 / 338; object-fit: cover;" onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.png') }}';">
                                            <div>
                                                <div class="text-uppercase small text-muted">Current Leader</div>
                                                <h4 class="mb-1">${escapeHtml(leadingCandidate.name)}</h4>
                                                <div class="fw-semibold text-primary">${leadingCandidate.vote_percent}%</div>
                                            </div>
                                        </div>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-4 p-lg-5">
                            <div class="row g-3">
                                ${challengerRows || '<div class="text-muted py-2">No active candidates.</div>'}
                            </div>
                        </div>
                    </div>
                `;
            }

            function renderSlide(position) {
                if (position.type === 'single_candidate_yes_no') {
                    positionsContainer.innerHTML = renderSingleCandidatePosition(position);
                    return;
                }

                positionsContainer.innerHTML = renderMultiCandidatePosition(position);
            }

            function updateSlideshow() {
                if (!positionsCache.length) {
                    positionsContainer.innerHTML = `
                        <div class="text-center text-muted py-4">No positions available for this election.</div>
                    `;
                    slideCounter.textContent = '0 / 0';
                    return;
                }

                if (slideshowIndex >= positionsCache.length) {
                    slideshowIndex = 0;
                }

                renderSlide(positionsCache[slideshowIndex]);
                slideCounter.textContent = `${slideshowIndex + 1} / ${positionsCache.length}`;
            }

            function nextSlide() {
                if (!positionsCache.length) {
                    return;
                }

                slideshowIndex = (slideshowIndex + 1) % positionsCache.length;
                updateSlideshow();
            }

            function prevSlide() {
                if (!positionsCache.length) {
                    return;
                }

                slideshowIndex = (slideshowIndex - 1 + positionsCache.length) % positionsCache.length;
                updateSlideshow();
            }

            function restartSlideshowTimer() {
                if (slideshowTimer) {
                    clearInterval(slideshowTimer);
                }

                slideshowTimer = setInterval(nextSlide, 6000);
            }

            function renderPositions(positions) {
                positionsCache = Array.isArray(positions) ? positions : [];
                updateSlideshow();
                restartSlideshowTimer();
            }

            async function loadPerformance() {
                try {
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
                }
            }
            prevSlideButton.addEventListener('click', () => {
                prevSlide();
                restartSlideshowTimer();
            });
            nextSlideButton.addEventListener('click', () => {
                nextSlide();
                restartSlideshowTimer();
            });
            loadPerformance();
            setInterval(loadPerformance, 10000);
        })();
    </script>
</x-public.layout>
