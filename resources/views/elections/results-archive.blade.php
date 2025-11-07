<x-dashboard.default title="Election Results Archive">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <h3 class="card-title fw-bold text-gray-800">
                        <i class="ki-duotone ki-chart-pie-3 fs-2 me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        Election Results Archive
                    </h3>
                </div>
            </div>
            <div class="card-body">
                @livewire('election-results-archive')
            </div>
        </div>
    </div>
</x-dashboard.default>
