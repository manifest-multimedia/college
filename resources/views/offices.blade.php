<x-dashboard.default title="Office Management">

    <div class="pb-20 mb-10 row">
        <div class="flex-wrap p-8 shadow justify-content-center d-flex flex-md-nowrap card-rounded" style="background: linear-gradient(90deg, #20AA3E 0%, #03A588 100%);">
            <div class="text-center">
                <div class="mb-2 text-center text-white fs-1 fs-lg-2qx fw-bold">
                    Manage Offices and Departments
                    <i class="bi bi-building text-white ms-2" style="font-size: 40px;"></i>
                </div>
                <div class="text-white opacity-75 fs-6 fs-lg-5 fw-semibold">
                    Organize your institution's offices and manage their information
                </div>
            </div>
        </div>
    </div>

    @livewire('office-manager')
    
</x-dashboard.default>
