<div class="row g-5 g-xl-10 mb-5 mb-xl-10">
    <!-- Sidebar for Report Selection -->
    <div class="col-md-4 col-lg-3">
        <div class="card card-flush">
            <div class="card-header pt-7">
                <div class="card-title">
                    <h2>Available Reports</h2>
                </div>
            </div>
            <div class="card-body pt-5">
                <div class="menu menu-column menu-rounded menu-state-bg menu-state-title-primary menu-state-icon-primary menu-state-bullet-primary mb-10">
                    @foreach($reportsByModule as $module => $reports)
                        <div class="menu-item mb-3">
                            <span class="menu-heading text-uppercase text-muted fw-bold fs-7">{{ $module }}</span>
                        </div>
                        @foreach($reports as $report)
                            <div class="menu-item mb-1">
                                <a wire:click.prevent="selectReport('{{ $report->getId() }}')" 
                                   class="menu-link {{ $selectedReportId === $report->getId() ? 'active' : '' }}" 
                                   href="#" style="cursor: pointer;">
                                    <span class="menu-icon">
                                        <i class="{{ $report->getIcon() }} fs-2"></i>
                                    </span>
                                    <span class="menu-title">{{ $report->getName() }}</span>
                                </a>
                            </div>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="col-md-8 col-lg-9">
        @if($selectedReport)
            <div class="card card-flush mb-5">
                <div class="card-header pt-7">
                    <div class="card-title d-flex flex-column">
                        <h2>{{ $selectedReport->getName() }}</h2>
                        <span class="text-gray-400 pt-1 fw-semibold fs-6">{{ $selectedReport->getDescription() }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters Form -->
                    <form wire:submit.prevent="generateReport">
                        <div class="row g-5">
                            @foreach($selectedReport->getFilters() as $filter)
                                <div class="col-md-{{ $filter['col'] ?? 4 }}">
                                    <label class="form-label {{ ($filter['required'] ?? false) ? 'required' : '' }}">
                                        {{ $filter['label'] }}
                                    </label>
                                    
                                    @if(($filter['type'] ?? 'text') === 'select')
                                        <select wire:model="filters.{{ $filter['key'] }}" class="form-select form-select-solid">
                                            <option value="">Select {{ $filter['label'] }}...</option>
                                            @foreach($filter['options'] ?? [] as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    @elseif(($filter['type'] ?? 'text') === 'date')
                                        <input type="date" wire:model="filters.{{ $filter['key'] }}" class="form-control form-control-solid" />
                                    @else
                                        <input type="{{ $filter['type'] ?? 'text' }}" wire:model="filters.{{ $filter['key'] }}" class="form-control form-control-solid" placeholder="{{ $filter['placeholder'] ?? '' }}" />
                                    @endif
                                    
                                    @error('filters.'.$filter['key']) <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="d-flex justify-content-end mt-5">
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="generateReport">Generate Report</span>
                                <span wire:loading wire:target="generateReport">Generating...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Results Section -->
            @if($reportData !== null)
                <div class="card card-flush">
                    <div class="card-header pt-7">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold text-gray-800">Results</span>
                        </h3>
                        <div class="card-toolbar">
                            @if(in_array('pdf', $selectedReport->exportFormats()))
                                <button wire:click="exportPdf" class="btn btn-sm btn-light-danger me-2" wire:loading.attr="disabled">
                                    <i class="fas fa-file-pdf"></i> Export PDF
                                </button>
                            @endif
                            @if(in_array('excel', $selectedReport->exportFormats()))
                                <button class="btn btn-sm btn-light-success disabled">
                                    <i class="fas fa-file-excel"></i> Export Excel (Coming Soon)
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        @if(count($reportData) > 0)
                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed fs-6 gy-5">
                                    <thead>
                                        <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                            @foreach($columns as $key => $label)
                                                <th>{{ $label }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody class="fw-semibold text-gray-600">
                                        @foreach($reportData as $row)
                                            <tr>
                                                @foreach($columns as $key => $label)
                                                    <td>{{ $row[$key] ?? '-' }}</td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">
                                No data found for the selected filters.
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        @else
            <div class="card card-flush h-100">
                <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                    <i class="fas fa-chart-line fs-5x text-muted mb-5"></i>
                    <h2 class="text-gray-800">Reports Module</h2>
                    <p class="text-gray-400 fs-5">Please select a report from the sidebar to view its details and generate data.</p>
                </div>
            </div>
        @endif
    </div>
</div>
