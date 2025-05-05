<x-dashboard.default title="Student Information">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <h3 class="card-title fw-bold text-gray-800">
                            <i class="fas fa-user-graduate me-2"></i>Student Details
                        </h3>
                    </div>
                </div>
                <div class="card-body">
                    <livewire:student-details :studentId="$studentId" />
                </div>
            </div>
        </div>
    </div>
</x-dashboard.default>
