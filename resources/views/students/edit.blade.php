<x-dashboard.default title="Edit Student">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <h3 class="card-title fw-bold text-gray-800">
                            <i class="fas fa-edit me-2"></i>Edit Student Information
                        </h3>
                    </div>
                </div>
                <div class="card-body">
                    <livewire:student-edit-form :studentId="$studentId" />
                </div>
            </div>
        </div>
    </div>
</x-dashboard.default>