<x-dashboard.default>
    <x-slot name="title">
        Import Students
    </x-slot>

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-file-import me-2"></i>Import Students
                        </h1>
                        <div>
                            <a href="{{ route('students.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back to Students
                            </a>
                        </div>
                    </div>
                </div>

                <livewire:student-import />
            </div>
        </div>
    </div>
</x-dashboard.default>