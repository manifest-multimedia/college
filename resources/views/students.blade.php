<x-dashboard.default title="Exam Center" pageActions="examcenter">

    <div class="pb-20 mb-10 row">

        <div class="flex-wrap p-8 shadow justify-content-center d-flex flex-md-nowrap card-rounded" style="background: linear-gradient(90deg, #20AA3E 0%, #03A588 100%);">
            <!--begin::Content-->
            <div class="text-center">
                <!--begin::Title-->
                <div class="mb-2 text-center text-white fs-1 fs-lg-2qx fw-bold">Manage Student Admissions, Generate IDs, and Access Student Information
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-person-badge-fill" viewBox="0 0 16 16">
                        <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2zm4.5 0a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1zM8 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6m5 2.755C12.146 12.825 10.623 12 8 12s-4.146.826-5 1.755V14a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1z"/>
                      </svg>
                </div>
                <!--end::Title-->
                <!--begin::Description-->
                <div class="text-white opacity-75 fs-6 fs-lg-5 fw-semibold">
                  
            </div>
            <!--end::Content-->
          
        </div>
    </div>
    @livewire('students-table-widget')
    
</x-dashboard.default>