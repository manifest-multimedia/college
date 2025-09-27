<x-dashboard.default title="Course Registration">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h1 class="card-title">
                            <i class="fas fa-graduation-cap me-2"></i>Course Registration
                        </h1>
                        <p class="card-subtitle text-muted mb-0">
                            Register for courses in the current semester
                        </p>
                    </div>
                    <div class="card-body">
                        <livewire:student.course-registration-form />
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard.default>