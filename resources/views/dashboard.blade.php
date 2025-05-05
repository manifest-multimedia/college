<x-dashboard.default title="User Dashboard">

   <!--begin::Row-->
   <div class="mb-5 row g-5 gx-xl-10 mb-xl-10">
    <!--begin::Col-->
    <div class="col-xl-8">
        <div class="flex-wrap p-8 shadow d-flex flex-stack flex-md-nowrap card-rounded p-lg-12 mb-n5 mb-lg-n13" style="background: linear-gradient(90deg, #20AA3E 0%, #03A588 100%);">
            <!--begin::Content-->
            <div class="my-2 me-5">
                <!--begin::Title-->
                <div class="mb-2 text-white fs-1 fs-lg-2qx fw-bold">Welcome to your dashboard! 
                {{-- <span class="fw-normal">Welcome to your Dashbaord!</span> --}}
            </div>
                <!--end::Title-->
                <!--begin::Description-->
                <div class="text-white opacity-75 fs-6 fs-lg-5 fw-semibold">
                    Dear {{ Auth::user()->name }}, <br />

                    We are excited to announce a more user-friendly experience! Your favorite apps and tools are now just a click away. Use the icons below or the sidebar to effortlessly access what you need. <br />Start exploring now!</div>
                <!--end::Description-->
            </div>
            <!--end::Content-->
            <!--begin::Link-->
            {{-- <a href="https://1.envato.market/EA4JP" class="flex-shrink-0 my-2 border-2 btn btn-lg btn-outline btn-outline-white">Purchase on Themeforest</a> --}}
            <!--end::Link-->
        </div>

      <div class="pt-12 mt-12 row">
        {{-- Welcome Component End --}}
        
        <!-- Dashboard Counters -->
        <livewire:dashboard-counters />
        <!-- End Dashboard Counters -->


        <div class="col-6">
            <!--begin::Card-->
            <a class="p-10 text-gray-800 card flex-column justfiy-content-start align-items-center text-start w-100 text-hover-primary" href="{{ route('examcenter') }}">
                <i class="mb-5 text-gray-500 ki-duotone ki-gift fs-2tx ms-n1">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                    <span class="path4"></span>
                </i>
                <span class="fs-4 fw-bold">Exam Center</span>
            </a>
            <!--end::Card-->
        </div>
        <div class="col-6">
            <!--begin::Card-->
            <a class="p-10 text-gray-800 card flex-column justfiy-content-start align-items-center text-start w-100 text-hover-primary" href="{{ route('questionbank') }}">
                <i class="mb-5 text-gray-500 ki-duotone ki-gift fs-2tx ms-n1">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                    <span class="path4"></span>
                </i>
                <span class="fs-4 fw-bold">Question Bank</span>
            </a>
            <!--end::Card-->
        </div>
      </div>
      <div class="mt-5 row">
        <div class="col-6">
            <!--begin::Card-->
            <a class="p-10 text-gray-800 card flex-column justfiy-content-start align-items-center text-start w-100 text-hover-primary" href="{{ route('staffmail') }}">
                <i class="mb-5 text-gray-500 ki-duotone ki-gift fs-2tx ms-n1">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                    <span class="path4"></span>
                </i>
                <span class="fs-4 fw-bold">Staff Mail</span>
            </a>
            <!--end::Card-->
        </div>
        <div class="col-6">
            <!--begin::Card-->
            <a class="p-10 text-gray-800 card flex-column justfiy-content-start align-items-center text-start w-100 text-hover-primary" href="{{ route('supportcenter') }}">
                <i class="mb-5 text-gray-500 ki-duotone ki-gift fs-2tx ms-n1">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                    <span class="path4"></span>
                </i>
                <span class="fs-4 fw-bold">Support Center</span>
            </a>
            <!--end::Card-->
        </div>
      </div>

    </div>

    
    <!--end::Col-->
    <!--begin::Col-->
    {{-- if Route is dashbaord --}}
    @if (Route::currentRouteName() == 'dashboard')
   <x-app.support-widget />
   @endif 
    <!--end::Col-->
</div>




</x-dashboard.default>
