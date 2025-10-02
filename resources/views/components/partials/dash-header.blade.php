<body id="kt_body" class="header-tablet-and-mobile-fixed aside-enabled">
    @stack('styles')
    <!--begin::Theme mode setup on page load-->
    <script>
        var defaultThemeMode = "light";
        var themeMode;
        if (document.documentElement) {
            if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
                themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
            } else {
                if (localStorage.getItem("data-bs-theme") !== null) {
                    themeMode = localStorage.getItem("data-bs-theme");
                } else {
                    themeMode = defaultThemeMode;
                }
            }
            if (themeMode === "system") {
                themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
            }
            document.documentElement.setAttribute("data-bs-theme", themeMode);
        }
    </script>
    <!--end::Theme mode setup on page load-->
    <!--begin::Main-->
    <!--begin::Root-->
    <div class="d-flex flex-column flex-root">
        <!--begin::Page-->
        <div class="flex-row page d-flex flex-column-fluid">
            <x-app.sidebar />
            <!--begin::Wrapper-->
            <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">
                <!--begin::Header-->
                <div id="kt_header" class="header header-bg">
                    <!--begin::Container-->
                    <div class="container-fluid">
                        <!--begin::Brand-->
                        <div class="header-brand me-5">
                            <!--begin::Aside toggle-->
                            <div class="d-flex align-items-center d-lg-none ms-n2 me-2" title="Show aside menu">
                                <div class="btn btn-icon btn-color-white btn-active-color-primary w-30px h-30px"
                                    id="kt_aside_toggle">
                                    <i class="ki-duotone ki-abstract-14 fs-1">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                </div>
                            </div>
                            <!--end::Aside toggle-->
                            <!--begin::Logo-->
                            <a href="{{ url('/') }}">
                                <img alt="Logo" src="{{ asset('images/mhtia-white.png') }}"
                                    class="h-25px h-lg-30px d-none d-md-block" />
                                <img alt="Logo" src="assets/media/logos/default-small.svg"
                                    class="h-25px d-block d-md-none" />
                            </a>
                            <!--end::Logo-->
                        </div>
                        <!--end::Brand-->
                        <!--begin::Topbar-->
                        <div class="topbar d-flex align-items-stretch">
                            <!--begin::Item-->
                            <div class="d-flex align-items-stretch me-2 me-lg-4">
                                <!--begin::Search-->
                                <div id="kt_header_search_disabled"
                                    class="header-search d-flex align-items-center w-lg-250px">
                                    <!--begin::Tablet and mobile search toggle (disabled)-->
                                    <div class="search-toggle-mobile d-flex d-lg-none align-items-center">
                                        <div
                                            class="bg-white bg-opacity-10 d-flex btn btn-icon btn-borderless btn-color-white btn-active-primary">
                                            <i class="text-white ki-duotone ki-magnifier fs-1">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </div>
                                    </div>
                                    <!--end::Tablet and mobile search toggle (disabled)-->
                                    <!--begin::Form(use d-none d-lg-block classes for responsive search)-->
                                    {{-- <form data-kt-search-element="form"
                                        class="mb-2 d-none d-lg-block w-100 position-relative mb-lg-0"
                                        autocomplete="off">
                                        <!--begin::Hidden input(Added to disable form autocomplete)-->
                                        <input type="hidden" />
                                        <!--end::Hidden input-->
                                        <!--begin::Icon-->
                                        <i
                                            class="ki-duotone ki-magnifier fs-2 position-absolute top-50 translate-middle-y ms-0 ms-lg-4">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        <!--end::Icon-->
                                        <!--begin::Input-->
                                        <input type="text" class="form-control form-control-flush ps-8 ps-lg-12"
                                            name="search" value="" placeholder="Search"
                                            data-kt-search-element="input" />
                                        <!--end::Input-->
                                        <!--begin::Spinner-->
                                        <span
                                            class="position-absolute top-50 end-0 translate-middle-y lh-0 d-none me-lg-5"
                                            data-kt-search-element="spinner">
                                            <span
                                                class="text-gray-500 align-middle spinner-border h-15px w-15px"></span>
                                        </span>
                                        <!--end::Spinner-->
                                        <!--begin::Reset-->
                                        <span
                                            class="btn btn-flush btn-active-color-primary position-absolute top-50 end-0 translate-middle-y lh-0 d-none me-lg-4"
                                            data-kt-search-element="clear">
                                            <i class="ki-duotone ki-cross fs-2 fs-lg-1 me-0">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </span>
                                        <!--end::Reset-->
                                    </form> --}}
                                    <!--end::Form-->
                                    <!--begin::Menu-->
                                    
                                    <!--end::Menu-->
                                </div>
                                <!--end::Search-->
                            </div>
                            <!--end::Item-->
                            <!--begin::Item-->
                            {{-- Notification --}}
                          <x-notifications />
                            <!--end::Item-->
                            <!--begin::Item-->
                            <div class="d-flex align-items-center me-2 me-lg-4">
                                <a href="#"
                                    class="bg-white bg-opacity-10 btn btn-icon btn-borderless btn-color-white btn-active-primary"
                                    data-kt-menu-trigger="click" data-kt-menu-attach="parent"
                                    data-kt-menu-placement="bottom-end">
                                    <i class="text-white ki-duotone ki-user fs-1">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                </a>
                                <!--begin::User account menu-->
                                <div class="py-4 menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold fs-6 w-275px"
                                    data-kt-menu="true">
                                    <!--begin::Menu item-->
                                    <div class="px-3 menu-item">
                                        <div class="px-3 menu-content d-flex align-items-center">
                                            <!--begin::Avatar-->
                                            <div class="symbol symbol-50px me-5">
                                                <img alt="Avatar" src="{{ Auth::user()->profile_photo_url }}" />
                                            </div>
                                            <!--end::Avatar-->
                                            <!--begin::Username-->
                                            <div class="d-flex flex-column">
                                                <div class="fw-bold d-flex align-items-center fs-5">{{ Auth::user()->name }}
                                                    <span
                                                        class="px-2 py-1 badge badge-light-success fw-bold fs-8 ms-2">{{ Auth::user()->role }}</span>
                                                </div>
                                                <a href="#"
                                                    class="fw-semibold text-muted text-hover-primary fs-7">{{ Auth::user()->email }}</a>
                                            </div>
                                            <!--end::Username-->
                                        </div>
                                    </div>
                                    <!--end::Menu item-->
                                    <!--begin::Menu separator-->
                                    <div class="my-2 separator"></div>
                                    <!--end::Menu separator-->
                                    <!--begin::Menu item-->
                                    {{-- <div class="px-5 menu-item">
                                        <a href="account/overview.html" class="px-5 menu-link">My Profile</a>
                                    </div> --}}
                                    <!--end::Menu item-->
                                    {{-- <!--begin::Menu item-->
                                    <div class="px-5 menu-item">
                                        <a href="apps/projects/list.html" class="px-5 menu-link">
                                            <span class="menu-text">My Projects</span>
                                            <span class="menu-badge">
                                                <span
                                                    class="badge badge-light-danger badge-circle fw-bold fs-7">3</span>
                                            </span>
                                        </a>
                                    </div> --}}
                                    <!--end::Menu item-->
                                    {{-- <!--begin::Menu item-->
                                    <div class="px-5 menu-item"
                                        data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
                                        data-kt-menu-placement="right-start" data-kt-menu-offset="-15px, 0">
                                        <a href="#" class="px-5 menu-link">
                                            <span class="menu-title">My Subscription</span>
                                            <span class="menu-arrow"></span>
                                        </a>
                                        <!--begin::Menu sub-->
                                        <div class="py-4 menu-sub menu-sub-dropdown w-175px">
                                            <!--begin::Menu item-->
                                            <div class="px-3 menu-item">
                                                <a href="account/referrals.html"
                                                    class="px-5 menu-link">Referrals</a>
                                            </div>
                                            <!--end::Menu item-->
                                            <!--begin::Menu item-->
                                            <div class="px-3 menu-item">
                                                <a href="account/billing.html" class="px-5 menu-link">Billing</a>
                                            </div>
                                            <!--end::Menu item-->
                                            <!--begin::Menu item-->
                                            <div class="px-3 menu-item">
                                                <a href="account/statements.html"
                                                    class="px-5 menu-link">Payments</a>
                                            </div>
                                            <!--end::Menu item-->
                                            <!--begin::Menu item-->
                                            <div class="px-3 menu-item">
                                                <a href="account/statements.html"
                                                    class="px-5 menu-link d-flex flex-stack">Statements
                                                    <span class="ms-2 lh-0" data-bs-toggle="tooltip"
                                                        title="View your statements">
                                                        <i class="ki-duotone ki-information-5 fs-5">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                            <span class="path3"></span>
                                                        </i>
                                                    </span></a>
                                            </div>
                                            <!--end::Menu item-->
                                            <!--begin::Menu separator-->
                                            <div class="my-2 separator"></div>
                                            <!--end::Menu separator-->
                                            <!--begin::Menu item-->
                                            <div class="px-3 menu-item">
                                                <div class="px-3 menu-content">
                                                    <label
                                                        class="form-check form-switch form-check-custom form-check-solid">
                                                        <input class="form-check-input w-30px h-20px"
                                                            type="checkbox" value="1" checked="checked"
                                                            name="notifications" />
                                                        <span
                                                            class="form-check-label text-muted fs-7">Notifications</span>
                                                    </label>
                                                </div>
                                            </div>
                                            <!--end::Menu item-->
                                        </div>
                                        <!--end::Menu sub-->
                                    </div> --}}
                                    <!--end::Menu item-->
                                    <!--begin::Menu item-->
                                    {{-- <div class="px-5 menu-item">
                                        <a href="account/statements.html" class="px-5 menu-link">My Statements</a>
                                    </div> --}}
                                    <!--end::Menu item-->
                                    <!--begin::Menu separator-->
                                    {{-- <div class="my-2 separator"></div> --}}
                                    <!--end::Menu separator-->
                                    <!--begin::Menu item-->
                                    <div class="px-5 menu-item"
                                        data-kt-menu-trigger="{default: 'click', lg: 'hover'}"
                                        data-kt-menu-placement="right-start" data-kt-menu-offset="-15px, 0">
                                        <a href="#" class="px-5 menu-link">
                                            <span class="menu-title position-relative">Language
                                                <span
                                                    class="px-3 py-2 rounded fs-8 bg-light position-absolute translate-middle-y top-50 end-0">English
                                                    <img class="w-15px h-15px rounded-1 ms-2"
                                                        src="assets/media/flags/united-states.svg"
                                                        alt="" /></span></span>
                                        </a>
                                        <!--begin::Menu sub-->
                                        <div class="py-4 menu-sub menu-sub-dropdown w-175px">
                                            <!--begin::Menu item-->
                                            <div class="px-3 menu-item">
                                                <a href="javascript:void(0)"
                                                    class="px-5 menu-link d-flex active">
                                                    <span class="symbol symbol-20px me-4">
                                                        <img class="rounded-1"
                                                            src="{{ asset('dashboard/assets/media/flags/united-states.svg') }}"
                                                            alt="" />
                                                    </span>English</a>
                                            </div>
                                            <!--end::Menu item-->
                                            <!--begin::Menu item-->
                                            {{-- <div class="px-3 menu-item">
                                                <a href="account/settings.html" class="px-5 menu-link d-flex">
                                                    <span class="symbol symbol-20px me-4">
                                                        <img class="rounded-1" src="assets/media/flags/spain.svg"
                                                            alt="" />
                                                    </span>Spanish</a>
                                            </div> --}}
                                            <!--end::Menu item-->
                                            <!--begin::Menu item-->
                                            {{-- <div class="px-3 menu-item">
                                                <a href="account/settings.html" class="px-5 menu-link d-flex">
                                                    <span class="symbol symbol-20px me-4">
                                                        <img class="rounded-1" src="assets/media/flags/germany.svg"
                                                            alt="" />
                                                    </span>German</a>
                                            </div> --}}
                                            <!--end::Menu item-->
                                            <!--begin::Menu item-->
                                            {{-- <div class="px-3 menu-item">
                                                <a href="account/settings.html" class="px-5 menu-link d-flex">
                                                    <span class="symbol symbol-20px me-4">
                                                        <img class="rounded-1" src="assets/media/flags/japan.svg"
                                                            alt="" />
                                                    </span>Japanese</a>
                                            </div> --}}
                                            <!--end::Menu item-->
                                            <!--begin::Menu item-->
                                            {{-- <div class="px-3 menu-item">
                                                <a href="account/settings.html" class="px-5 menu-link d-flex">
                                                    <span class="symbol symbol-20px me-4">
                                                        <img class="rounded-1" src="assets/media/flags/france.svg"
                                                            alt="" />
                                                    </span>French</a>
                                            </div> --}}
                                            <!--end::Menu item-->
                                        </div>
                                        <!--end::Menu sub-->
                                    </div>
                                    <!--end::Menu item-->
                                    <!--begin::Menu item-->
                                    {{-- <div class="px-5 my-1 menu-item">
                                        <a href="account/settings.html" class="px-5 menu-link">Account Settings</a>
                                    </div> --}}
                                    <!--end::Menu item-->
                                    <!--begin::Menu item-->
                                    <div class="px-5 menu-item">
                                        {{-- Logout Form --}}
                                        <form method="POST" action="{{ route('logout') }}">
                                           
                                            @csrf

                                        <a href="javascript:void(0)" type="submit" onclick="event.preventDefault(); this.closest('form').submit();"
                                            class="px-5 menu-link">Sign Out</a>
                                        </form>
                                    </div>
                                    <!--end::Menu item-->
                                </div>
                                <!--end::User account menu-->
                            </div>
                            <!--end::Item-->
                            <!--begin::Theme mode-->
                            <div class="d-flex align-items-center me-2 me-lg-4">
                                <!--begin::Menu toggle-->
                                <a href="#"
                                    class="bg-white bg-opacity-10 btn btn-icon btn-borderless btn-color-white btn-active-primary"
                                    data-kt-menu-trigger="{default:'click', lg: 'hover'}"
                                    data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
                                    <i class="ki-duotone ki-night-day theme-light-show fs-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                        <span class="path4"></span>
                                        <span class="path5"></span>
                                        <span class="path6"></span>
                                        <span class="path7"></span>
                                        <span class="path8"></span>
                                        <span class="path9"></span>
                                        <span class="path10"></span>
                                    </i>
                                    <i class="ki-duotone ki-moon theme-dark-show fs-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                </a>
                                <!--begin::Menu toggle-->
                                <!--begin::Menu-->
                                <div class="py-4 menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-title-gray-700 menu-icon-gray-500 menu-active-bg menu-state-color fw-semibold fs-base w-150px"
                                    data-kt-menu="true" data-kt-element="theme-mode-menu">
                                    <!--begin::Menu item-->
                                    <div class="px-3 my-0 menu-item">
                                        <a href="#" class="px-3 py-2 menu-link" data-kt-element="mode"
                                            data-kt-value="light">
                                            <span class="menu-icon" data-kt-element="icon">
                                                <i class="ki-duotone ki-night-day fs-2">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                    <span class="path4"></span>
                                                    <span class="path5"></span>
                                                    <span class="path6"></span>
                                                    <span class="path7"></span>
                                                    <span class="path8"></span>
                                                    <span class="path9"></span>
                                                    <span class="path10"></span>
                                                </i>
                                            </span>
                                            <span class="menu-title">Light</span>
                                        </a>
                                    </div>
                                    <!--end::Menu item-->
                                    <!--begin::Menu item-->
                                    <div class="px-3 my-0 menu-item">
                                        <a href="#" class="px-3 py-2 menu-link" data-kt-element="mode"
                                            data-kt-value="dark">
                                            <span class="menu-icon" data-kt-element="icon">
                                                <i class="ki-duotone ki-moon fs-2">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </span>
                                            <span class="menu-title">Dark</span>
                                        </a>
                                    </div>
                                    <!--end::Menu item-->
                                    <!--begin::Menu item-->
                                    <div class="px-3 my-0 menu-item">
                                        <a href="#" class="px-3 py-2 menu-link" data-kt-element="mode"
                                            data-kt-value="system">
                                            <span class="menu-icon" data-kt-element="icon">
                                                <i class="ki-duotone ki-screen fs-2">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                    <span class="path4"></span>
                                                </i>
                                            </span>
                                            <span class="menu-title">System</span>
                                        </a>
                                    </div>
                                    <!--end::Menu item-->
                                </div>
                                <!--end::Menu-->
                            </div>
                            <!--end::Theme mode-->
                            <!--begin::Item-->
                            {{-- <div class="d-flex align-items-center me-2 me-lg-4">
                                <a href="#" class="px-3 border-0 btn btn-success px-lg-6"
                                    data-bs-toggle="modal" data-bs-target="#kt_modal_create_campaign">New Goal</a>
                            </div> --}}
                            <!--end::Item-->
                            <!--begin::Item-->
                            {{-- <div class="d-flex align-items-center">
                                <a href="index.html"
                                    class="border-0 btn btn-icon btn-color-white btn-active-color-primary me-n3"
                                    data-bs-toggle="tooltip" data-bs-placement="left" title="Return to launcher">
                                    <i class="text-white ki-duotone ki-cross-square fs-2x">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                </a>
                            </div> --}}
                            <!--end::Item-->
                        </div>
                        <!--end::Topbar-->
                    </div>
                    <!--end::Container-->
                </div>
                <!--end::Header-->
                