 <!--begin::Col-->
    <div class="col-xl-12 " style="margin-bottom:50px">
        <div class="flex-wrap p-8 shadow d-flex flex-stack flex-md-nowrap card-rounded p-lg-12 mb-n5 mb-lg-n13 bg-info " >
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
    </div>
