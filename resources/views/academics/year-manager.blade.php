<x-dashboard.default>
    <x-slot name="title">
        Manage Years
    </x-slot>
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title">
                                <i class="fas fa-layer-group me-2"></i>Manage Years
                            </h5>
                            <a href="{{ route('academics.dashboard') }}" class="btn btn-sm btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <livewire:academics.year-manager />
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard.default>