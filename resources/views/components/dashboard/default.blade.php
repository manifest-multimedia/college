<x-partials.dash-head :title="$title" description="Dashboard" />
<!--end::Head-->
<!--begin::Body-->
<x-partials.dash-header />
<!--begin::Content-->
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Toolbar-->
    <div class="mb-3 toolbar d-flex flex-stack mb-lg-5" id="kt_toolbar">
        <!--begin::Container-->
        <div id="kt_toolbar_container" class="flex-wrap container-fluid d-flex flex-stack">
            <!--begin::Page title-->
            <div class="py-2 page-title d-flex flex-column me-5">
                <!--begin::Title-->
                <h1 class="mb-0 text-gray-900 d-flex flex-column fw-bold fs-3">Welcome back, {{ Auth::user()->name }}</h1>
                <!--end::Title-->
                <!--begin::Breadcrumb-->
                <ul class="pt-1 breadcrumb breadcrumb-separatorless fw-semibold fs-7">
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Dashboard</a>
                    </li>
                    <!--end::Item-->
                   
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bg-gray-200 bullet w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="text-gray-900 breadcrumb-item">{{ $title }}</li>
                    <!--end::Item-->
                </ul>
                <!--end::Breadcrumb-->
            </div>
            <!--end::Page title-->
            <!--begin::Actions-->
          @if(isset($pageActions))
          @switch($pageActions)
            @case('examcenter')
            <div class="float-end">
                <a href="#" class="btn btn-sm btn-light me-2"
                    data-bs-toggle="modal"
                    data-bs-target="#kt_modal_create_project">Create Exam</a>
                <a href="#" class="btn btn-sm btn-danger"
                    data-bs-toggle="modal"
                    data-bs-target="#kt_modal_create_app">Access Question Bank</a>
            </div>
            

                @break
            @default
                
          @endswitch
          @endif
            <!--end::Actions-->
                        </div>
                        <!--end::Container-->
                    </div>
                    <!--end::Toolbar-->
                    <!--begin::Post-->
                    <div class="post d-flex flex-column-fluid" id="kt_post">
                        <!--begin::Container-->
                        <div id="kt_content_container" class="container-xxl">
                         
                          {{ $slot }}
                           {{-- <x-partials.content-one /> --}}
                        </div>
                        <!--end::Container-->
                    </div>
                    <!--end::Post-->
                </div>
                <!--end::Content-->
                <!--begin::Footer-->
                <div class="py-4 footer d-flex flex-lg-column" id="kt_footer">
                    <!--begin::Container-->
                    <div
                        class="container-fluid d-flex flex-column flex-md-row align-items-center justify-content-between">
                        <!--begin::Copyright-->
                        <div class="order-2 text-gray-900 order-md-1">
                            <span class="text-muted fw-semibold me-1">{{ date('Y') }} &copy;</span>
                            <a href="https://manifestghana.com" target="_blank"
                                class="text-gray-800 text-hover-primary">Manifest Digital</a>
                        </div>
                        <!--end::Copyright-->
                        <!--begin::Menu-->
                        {{-- <ul class="order-1 menu menu-gray-600 menu-hover-primary fw-semibold">
                            <li class="menu-item">
                                <a href="https://keenthemes.com" target="_blank" class="px-2 menu-link">About</a>
                            </li>
                            <li class="menu-item">
                                <a href="https://devs.keenthemes.com" target="_blank"
                                    class="px-2 menu-link">Support</a>
                            </li>
                            <li class="menu-item">
                                <a href="https://1.envato.market/EA4JP" target="_blank"
                                    class="px-2 menu-link">Purchase</a>
                            </li>
                        </ul> --}}
                        <!--end::Menu-->
                    </div>
                    <!--end::Container-->
                </div>
                <!--end::Footer-->
            </div>
            <!--end::Wrapper-->
        </div>
        <!--end::Page-->
    </div>
    <!--end::Root-->
    <!--begin::Drawers-->
  
{{-- Load Systemwide Drawers Here --}}
 
    <!--end::Drawers-->
    <!--end::Main-->
    <!--begin::Scrolltop-->
    <div id="kt_scrolltop" class="scrolltop" data-kt-scrolltop="true">
        <i class="ki-duotone ki-arrow-up">
            <span class="path1"></span>
            <span class="path2"></span>
        </i>
    </div>
    <!--end::Scrolltop-->
    <!--begin::Modals-->
    {{-- Add Systemwide Modals Here --}}
   
    <!--end::Modals-->
    <x-partials.dash-footer />
    <!--begin::Javascript-->
