<x-partials.dash-head :title="isset($title) ? $title : 'Dashboard'" description="Dashboard" />
<!--end::Head-->
<!--begin::Body-->
<x-partials.dash-header />
<!--begin::Content-->
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    @if(session()->has('impersonator_id'))
    <!-- Impersonation Floating Bar -->
    <div id="impersonationBar" class="position-fixed bottom-4 right-4 shadow-lg rounded bg-warning text-dark px-4 py-3 d-flex align-items-center gap-3" style="bottom: 1rem; right: 1rem; z-index: 9999 !important;">
        <i class="fas fa-user-secret"></i>
        <span>Impersonation active</span>
        <a href="{{ route('impersonate.stop') }}" class="btn btn-sm btn-dark">Exit</a>
        <button type="button" id="hideImpersonationBar" class="btn btn-sm btn-light">Hide</button>
    </div>
    <script>
        (function(){
            const bar = document.getElementById('impersonationBar');
            const hideBtn = document.getElementById('hideImpersonationBar');
            const key = 'cis.impersonationBar.hidden';
            if(localStorage.getItem(key) === '1'){
                hideBar();
                // Create a small reveal tab
                createReveal();
            }
            function createReveal(){
                if(document.getElementById('impersonationReveal')) return;
                const reveal = document.createElement('div');
                reveal.id = 'impersonationReveal';
                reveal.className = 'position-fixed bottom-4 right-4 rounded bg-warning text-dark px-3 py-2 shadow';
                reveal.style.bottom = '1rem';
                reveal.style.right = '1rem';
                reveal.style.cursor = 'pointer';
                reveal.style.zIndex = '9999';
                reveal.innerHTML = '<i class="fas fa-user-secret me-1"></i> Show Impersonation';
                reveal.addEventListener('click', ()=>{
                    showBar();
                    localStorage.setItem(key, '0');
                    reveal.remove();
                });
                document.body.appendChild(reveal);
            }
            function hideBar(){
                // Use multiple strategies to ensure hide in all CSS stacks
                bar.style.display = 'none';
                bar.style.visibility = 'hidden';
                bar.style.pointerEvents = 'none';
                bar.classList.add('d-none');
            }
            function showBar(){
                bar.classList.remove('d-none');
                bar.style.display = 'flex';
                bar.style.visibility = '';
                bar.style.pointerEvents = '';
            }
            hideBtn?.addEventListener('click', (e)=>{
                e.preventDefault();
                e.stopPropagation();
                hideBar();
                localStorage.setItem(key, '1');
                createReveal();
            });
        })();
    </script>
    @endif
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
                    @if(isset($title) && $title)
                        <li class="text-gray-900 breadcrumb-item">{{ $title }}</li>
                    @else 
                        <li class="text-gray-900 breadcrumb-item">Dashboard</li>
                    @endif
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
                <a href="{{ route('exams.create') }}" class="btn btn-sm btn-success me-2"
                    >Create Exam</a>
                    {{-- If Current Route is Not questionbank --}}
                    @if(Route::currentRouteName() != 'questionbank')
                <a href="{{ route('questionbank') }}" class="btn btn-sm btn-primary"
                 >Access Question Bank</a>
                    @endif
            </div>
            

                @break

            @case('students')
            <div class="float-end">
                <a href="{{ route('students.create') }}" class="btn btn-sm btn-success me-2"
                    ><i class="fas fa-plus me-2"></i>Add Student</a>
                <a href="{{ route('students.import') }}" class="btn btn-sm btn-primary"
                    ><i class="fas fa-file-import me-2"></i>Import Students</a>
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
                  
                  
                        <!--begin::Container-->
                        <div id="kt_content_container" class="container-xxl">
                         
                          {{ $slot }}
                     
                        </div>
                        <!--end::Container-->
                  
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


{{-- Load Systemwide Modals --}}

<livewire:feature-request-modal />
<livewire:support-request-modal />

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
