<x-dashboard.default title="Course Registration Management">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h1 class="card-title">
                            <i class="fas fa-user-cog me-2"></i>Course Registration Management
                        </h1>
                        <p class="card-subtitle text-muted mb-0">
                            Administrative interface for managing student course registrations
                        </p>
                    </div>
                    <div class="card-body">
                        <livewire:finance.course-registration-manager :studentId="$studentId" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard.default>