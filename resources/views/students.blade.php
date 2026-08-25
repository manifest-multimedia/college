<x-dashboard.default title="Student Information" pageActions="students">

    <!-- Original Green Banner before Page Title -->
    <div class="mb-6">
        <div class="card-rounded p-6 p-lg-8 justify-content-center d-flex flex-wrap flex-md-nowrap align-items-center" style="background: linear-gradient(90deg, #20AA3E 0%, #03A588 100%); border-radius: 12px;">
            <div class="text-center">
                <div class="text-center text-white fs-2 fs-lg-1 fw-bold d-flex align-items-center justify-content-center gap-3">
                    <span>Manage Student Admissions, Generate IDs, and Access Student Information</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="currentColor" class="bi bi-person-badge-fill flex-shrink-0" viewBox="0 0 16 16">
                        <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2zm4.5 0a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1zM8 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6m5 2.755C12.146 12.825 10.623 12 8 12s-4.146.826-5 1.755V14a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    @livewire('students-table-widget')
    
</x-dashboard.default>