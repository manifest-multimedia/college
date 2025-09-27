<x-dashboard.default>
    <x-slot name="header">
        <div class="header-left">
            <h2 class="page-title">Course Registration History</h2>
        </div>
    </x-slot>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h1 class="card-title">
                                    <i class="fas fa-history"></i> Registration History
                                </h1>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <livewire:student-registration-history />
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard.default>