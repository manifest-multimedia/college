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
                <h1 class="mb-0 text-gray-900 d-flex flex-column fw-bold fs-3">Welcome {{ Auth::user()->name }}</h1>
                <!--end::Title-->
                <!--begin::Breadcrumb-->
                <ul class="pt-1 breadcrumb breadcrumb-separatorless fw-semibold fs-7">
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-muted">
                        <a href="index.html" class="text-muted text-hover-primary">Home</a>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bg-gray-200 bullet w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-muted">Dashboards</li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bg-gray-200 bullet w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="text-gray-900 breadcrumb-item">School</li>
                    <!--end::Item-->
                </ul>
                <!--end::Breadcrumb-->
            </div>
            <!--end::Page title-->
            <!--begin::Actions-->
            <div class="py-2 d-flex align-items-center">
                <!--begin::Wrapper-->
                <div class="me-4">
                    <!--begin::Menu-->
                    <a href="#"
                        class="btn btn-sm btn-flex btn-light btn-active-primary fw-bold"
                        data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                        <i class="text-gray-500 ki-duotone ki-filter fs-5 me-1">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>Filter</a>
                    <!--begin::Menu 1-->
                    <div class="menu menu-sub menu-sub-dropdown w-250px w-md-300px"
                        data-kt-menu="true" id="kt_menu_6678196235d4d">
                        <!--begin::Header-->
                        <div class="px-7 py-5">
                            <div class="text-gray-900 fs-5 fw-bold">Filter Options</div>
                        </div>
                        <!--end::Header-->
                        <!--begin::Menu separator-->
                        <div class="border-gray-200 separator"></div>
                        <!--end::Menu separator-->
                        <!--begin::Form-->
                        <div class="px-7 py-5">
                            <!--begin::Input group-->
                            <div class="mb-10">
                                <!--begin::Label-->
                                <label class="form-label fw-semibold">Status:</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <div>
                                    <select class="form-select form-select-solid"
                                        multiple="multiple" data-kt-select2="true"
                                        data-close-on-select="false"
                                        data-placeholder="Select option"
                                        data-dropdown-parent="#kt_menu_6678196235d4d"
                                        data-allow-clear="true">
                                        <option></option>
                                        <option value="1">Approved</option>
                                        <option value="2">Pending</option>
                                        <option value="2">In Process</option>
                                        <option value="2">Rejected</option>
                                    </select>
                                </div>
                                <!--end::Input-->
                            </div>
                            <!--end::Input group-->
                            <!--begin::Input group-->
                            <div class="mb-10">
                                <!--begin::Label-->
                                <label class="form-label fw-semibold">Member Type:</label>
                                <!--end::Label-->
                                <!--begin::Options-->
                                <div class="d-flex">
                                    <!--begin::Options-->
                                    <label
                                        class="form-check form-check-sm form-check-custom form-check-solid me-5">
                                        <input class="form-check-input" type="checkbox"
                                            value="1" />
                                        <span class="form-check-label">Author</span>
                                    </label>
                                    <!--end::Options-->
                                    <!--begin::Options-->
                                    <label
                                        class="form-check form-check-sm form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox"
                                            value="2" checked="checked" />
                                        <span class="form-check-label">Customer</span>
                                    </label>
                                    <!--end::Options-->
                                </div>
                                <!--end::Options-->
                            </div>
                            <!--end::Input group-->
                            <!--begin::Input group-->
                            <div class="mb-10">
                                <!--begin::Label-->
                                <label class="form-label fw-semibold">Notifications:</label>
                                <!--end::Label-->
                                <!--begin::Switch-->
                                <div
                                    class="form-check form-switch form-switch-sm form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" value=""
                                        name="notifications" checked="checked" />
                                    <label class="form-check-label">Enabled</label>
                                </div>
                                <!--end::Switch-->
                            </div>
                            <!--end::Input group-->
                            <!--begin::Actions-->
                            <div class="d-flex justify-content-end">
                                <button type="reset"
                                    class="btn btn-sm btn-light btn-active-light-primary me-2"
                                    data-kt-menu-dismiss="true">Reset</button>
                                <button type="submit" class="btn btn-sm btn-primary"
                                    data-kt-menu-dismiss="true">Apply</button>
                            </div>
                            <!--end::Actions-->
                        </div>
                        <!--end::Form-->
                    </div>
                    <!--end::Menu 1-->
                                    <!--end::Menu-->
                                </div>
                                <!--end::Wrapper-->
                                <!--begin::Button-->
                                <a href="#" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#kt_modal_create_app" id="kt_toolbar_primary_button">Create</a>
                                <!--end::Button-->
                            </div>
                            <!--end::Actions-->
                        </div>
                        <!--end::Container-->
                    </div>
                    <!--end::Toolbar-->
                    <!--begin::Post-->
                    <div class="post d-flex flex-column-fluid" id="kt_post">
                        <!--begin::Container-->
                        <div id="kt_content_container" class="container-xxl">
                            <!--begin::Row-->
                            <div class="mb-5 row g-5 gx-xl-10 mb-xl-10">
                                <!--begin::Col-->
                                <div class="col-xl-8">
                                    <!--begin::Table widget 8-->
                                    <div class="card h-xl-100">
                                        <!--begin::Header-->
                                        <div class="py-0 card-header position-relative border-bottom-2">
                                            <!--begin::Nav-->
                                            <ul class="mt-3 nav nav-stretch nav-pills nav-pills-custom d-flex">
                                                <!--begin::Nav item-->
                                                <li class="p-0 nav-item ms-0 me-8">
                                                    <!--begin::Nav link-->
                                                    <a class="px-0 nav-link btn btn-color-muted"
                                                        data-bs-toggle="tab"
                                                        href="#kt_table_widget_7_tab_content_1">
                                                        <!--begin::Title-->
                                                        <span class="mb-3 nav-text fw-semibold fs-4">Monday</span>
                                                        <!--end::Title-->
                                                        <!--begin::Bullet-->
                                                        <span
                                                            class="rounded bullet-custom position-absolute z-index-2 w-100 h-2px top-100 bottom-n100 bg-primary"></span>
                                                        <!--end::Bullet-->
                                                    </a>
                                                    <!--end::Nav link-->
                                                </li>
                                                <!--end::Nav item-->
                                                <!--begin::Nav item-->
                                                <li class="p-0 nav-item ms-0 me-8">
                                                    <!--begin::Nav link-->
                                                    <a class="px-0 nav-link btn btn-color-muted"
                                                        data-bs-toggle="tab"
                                                        href="#kt_table_widget_7_tab_content_2">
                                                        <!--begin::Title-->
                                                        <span class="mb-3 nav-text fw-semibold fs-4">Tuesday</span>
                                                        <!--end::Title-->
                                                        <!--begin::Bullet-->
                                                        <span
                                                            class="rounded bullet-custom position-absolute z-index-2 w-100 h-2px top-100 bottom-n100 bg-primary"></span>
                                                        <!--end::Bullet-->
                                                    </a>
                                                    <!--end::Nav link-->
                                                </li>
                                                <!--end::Nav item-->
                                                <!--begin::Nav item-->
                                                <li class="p-0 nav-item ms-0 me-8">
                                                    <!--begin::Nav link-->
                                                    <a class="px-0 nav-link btn btn-color-muted show active"
                                                        data-bs-toggle="tab"
                                                        href="#kt_table_widget_7_tab_content_3">
                                                        <!--begin::Title-->
                                                        <span class="mb-3 nav-text fw-semibold fs-4">Wednesday</span>
                                                        <!--end::Title-->
                                                        <!--begin::Bullet-->
                                                        <span
                                                            class="rounded bullet-custom position-absolute z-index-2 w-100 h-2px top-100 bottom-n100 bg-primary"></span>
                                                        <!--end::Bullet-->
                                                    </a>
                                                    <!--end::Nav link-->
                                                </li>
                                                <!--end::Nav item-->
                                                <!--begin::Nav item-->
                                                <li class="p-0 nav-item ms-0 me-8">
                                                    <!--begin::Nav link-->
                                                    <a class="px-0 nav-link btn btn-color-muted"
                                                        data-bs-toggle="tab"
                                                        href="#kt_table_widget_7_tab_content_4">
                                                        <!--begin::Title-->
                                                        <span class="mb-3 nav-text fw-semibold fs-4">Thursday</span>
                                                        <!--end::Title-->
                                                        <!--begin::Bullet-->
                                                        <span
                                                            class="rounded bullet-custom position-absolute z-index-2 w-100 h-2px top-100 bottom-n100 bg-primary"></span>
                                                        <!--end::Bullet-->
                                                    </a>
                                                    <!--end::Nav link-->
                                                </li>
                                                <!--end::Nav item-->
                                                <!--begin::Nav item-->
                                                <li class="p-0 nav-item ms-0 me-8">
                                                    <!--begin::Nav link-->
                                                    <a class="px-0 nav-link btn btn-color-muted"
                                                        data-bs-toggle="tab"
                                                        href="#kt_table_widget_7_tab_content_5">
                                                        <!--begin::Title-->
                                                        <span class="mb-3 nav-text fw-semibold fs-4">Friday</span>
                                                        <!--end::Title-->
                                                        <!--begin::Bullet-->
                                                        <span
                                                            class="rounded bullet-custom position-absolute z-index-2 w-100 h-2px top-100 bottom-n100 bg-primary"></span>
                                                        <!--end::Bullet-->
                                                    </a>
                                                    <!--end::Nav link-->
                                                </li>
                                                <!--end::Nav item-->
                                            </ul>
                                            <!--end::Nav-->
                                            <!--begin::Toolbar-->
                                            <div class="card-toolbar">
                                                <!--begin::Daterangepicker(defined in src/js/layout/app.js)-->
                                                <div data-kt-daterangepicker="true"
                                                    data-kt-daterangepicker-opens="left"
                                                    class="px-4 btn btn-sm btn-light d-flex align-items-center">
                                                    <!--begin::Display range-->
                                                    <div class="text-gray-600 fw-bold">Loading date range...</div>
                                                    <!--end::Display range-->
                                                    <i class="ki-duotone ki-calendar-8 fs-1 ms-2 me-0">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                        <span class="path3"></span>
                                                        <span class="path4"></span>
                                                        <span class="path5"></span>
                                                        <span class="path6"></span>
                                                    </i>
                                                </div>
                                                <!--end::Daterangepicker-->
                                            </div>
                                            <!--end::Toolbar-->
                                        </div>
                                        <!--end::Header-->
                                        <!--begin::Body-->
                                        <div class="card-body">
                                            <!--begin::Tab Content (ishlamayabdi)-->
                                            <div class="mb-2 tab-content">
                                                <!--begin::Tap pane-->
                                                <div class="tab-pane fade" id="kt_table_widget_7_tab_content_1">
                                                    <!--begin::Table container-->
                                                    <div class="table-responsive">
                                                        <!--begin::Table-->
                                                        <table class="table align-middle">
                                                            <!--begin::Table head-->
                                                            <thead>
                                                                <tr>
                                                                    <th class="p-0 min-w-150px"></th>
                                                                    <th class="p-0 min-w-200px"></th>
                                                                    <th class="p-0 min-w-100px"></th>
                                                                    <th class="p-0 min-w-80px"></th>
                                                                </tr>
                                                            </thead>
                                                            <!--end::Table head-->
                                                            <!--begin::Table body-->
                                                            <tbody>
                                                                <tr>
                                                                    <td class="text-gray-800 fs-6 fw-bold">
                                                                        11:43-09:43am</td>
                                                                    <td class="text-gray-500 fs-6 fw-bold">lesson 1:
                                                                        <span class="text-gray-800">French
                                                                            class</span>
                                                                    </td>
                                                                    <td class="text-gray-500 fs-6 fw-bold">Cabinet:
                                                                        <span class="text-gray-800">5</span>
                                                                    </td>
                                                                    <td class="pe-0 text-end">
                                                                        <button
                                                                            class="btn btn-sm btn-light">Skip</button>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="px-3 py-2 text-gray-600 rounded bg-light fs-8 fw-bold"
                                                                        colspan="4">Break 205min</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="text-gray-800 fs-6 fw-bold">
                                                                        09:40-11:20am</td>
                                                                    <td class="text-gray-500 fs-6 fw-bold">lesson 2:
                                                                        <span class="text-gray-800">Physics
                                                                            class</span>
                                                                    </td>
                                                                    <td class="text-gray-500 fs-6 fw-bold">Cabinet:
                                                                        <span class="text-gray-800">13</span>
                                                                    </td>
                                                                    <td class="pe-0 text-end">
                                                                        <button
                                                                            class="btn btn-sm btn-light">Skip</button>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="px-3 py-2 text-gray-600 rounded bg-light fs-8 fw-bold"
                                                                        colspan="4">Break 25min</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="text-gray-800 fs-6 fw-bold">
                                                                        10:35-43:09am</td>
                                                                    <td class="text-gray-500 fs-6 fw-bold">lesson 3:
                                                                        <span class="text-gray-800">Chemistry
                                                                            class</span>
                                                                    </td>
                                                                    <td class="text-gray-500 fs-6 fw-bold">Court:
                                                                        <span class="text-gray-800">Main</span>
                                                                    </td>
                                                                    <td class="pe-0 text-end">
                                                                        <button
                                                                            class="btn btn-sm btn-light">Skip</button>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="px-3 py-2 text-gray-600 rounded bg-light fs-8 fw-bold"
                                                                        colspan="4">Break 15min</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="text-gray-800 fs-6 fw-bold">
                                                                        15:30-12:23pm</td>
                                                                    <td class="text-gray-500 fs-6 fw-bold">lesson 4:
                                                                        <span class="text-gray-800">Biology
                                                                            class</span>
                                                                        <!--begin::Icon-->
                                                                        <span class="cursor-pointer"
                                                                            data-bs-toggle="tooltip"
                                                                            data-bs-placement="top"
                                                                            title="Held by Dr. Ana">
                                                                            <i
                                                                                class="ki-duotone ki-information fs-1 text-warning">
                                                                                <span class="path1"></span>
                                                                                <span class="path2"></span>
                                                                                <span class="path3"></span>
                                                                            </i>
                                                                        </span>
                                                                        <!--end::Icon-->
                                                                    </td>
                                                                    <td class="text-gray-500 fs-6 fw-bold">Cabinet:
                                                                        <span class="text-gray-800">23</span>
                                                                    </td>
                                                                    <td class="pe-0 text-end">
                                                                        <button
                                                                            class="btn btn-sm btn-light">Skip</button>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                            <!--end::Table body-->
                                                        </table>
                                                        <!--end::Table-->
                                                    </div>
                                                    <!--end::Table container-->
                                                </div>
                                                <!--end::Tap pane-->
                                                <!--begin::Tap pane-->
                                                <div class="tab-pane fade" id="kt_table_widget_7_tab_content_2">
                                                    <!--begin::Table container-->
                                                    <div class="table-responsive">
                                                        <!--begin::Table-->
                                                        <table class="table align-middle">
                                                            <!--begin::Table head-->
                                                            <thead>
                                                                <tr>
                                                                    <th class="p-0 min-w-150px"></th>
                                                                    <th class="p-0 min-w-200px"></th>
                                                                    <th class="p-0 min-w-100px"></th>
                                                                    <th class="p-0 min-w-80px"></th>
                                                                </tr>
                                                            </thead>
                                                            <!--end::Table head-->
                                                            <!--begin::Table body-->
                                                            <tbody>
                                                                <tr>
                                                                    <td class="text-gray-800 fs-6 fw-bold">
                                                                        09:15-12:23am</td>
                                                                    <td class="text-gray-500 fs-6 fw-bold">lesson 1:
                                                                        <span class="text-gray-800">Geography
                                                                            class</span>
                                                                    </td>
                                                                    <td class="text-gray-500 fs-6 fw-bold">Cabinet:
                                                                        <span class="text-gray-800">45</span>
                                                                    </td>
                                                                    <td class="pe-0 text-end">
                                                                        <button
                                                                            class="btn btn-sm btn-light">Skip</button>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="px-3 py-2 text-gray-600 rounded bg-light fs-8 fw-bold"
                                                                        colspan="4">Break 20min</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="text-gray-800 fs-6 fw-bold">
                                                                        08:30-09:30am</td>
                                                                    <td class="text-gray-500 fs-6 fw-bold">lesson 2:
                                                                        <span class="text-gray-800">English
                                                                            class</span>
                                                                    </td>
                                                                    <td class="text-gray-500 fs-6 fw-bold">Cabinet:
                                                                        <span class="text-gray-800">9</span>
                                                                    </td>
                                                                    <td class="pe-0 text-end">
                                                                        <button
                                                                            class="btn btn-sm btn-light">Skip</button>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="px-3 py-2 text-gray-600 rounded bg-light fs-8 fw-bold"
                                                                        colspan="4">Break 20min</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="text-gray-800 fs-6 fw-bold">
                                                                        11:15-12:13am</td>
                                                                    <td class="text-gray-500 fs-6 fw-bold">lesson 3:
                                                                        <span class="text-gray-800">Sports
                                                                            class</span>
                                                                    </td>
                                                                    <td class="text-gray-500 fs-6 fw-bold">Court:
                                                                        <span class="text-gray-800">Main</span>
                                                                    </td>
                                                                    <td class="pe-0 text-end">
                                                                        <button
                                                                            class="btn btn-sm btn-light">Skip</button>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="px-3 py-2 text-gray-600 rounded bg-light fs-8 fw-bold"
                                                                        colspan="4">Break 25min</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="text-gray-800 fs-6 fw-bold">
                                                                        14:10-15:35pm</td>
                                                                    <td class="text-gray-500 fs-6 fw-bold">lesson 4:
                                                                        <span class="text-gray-800">Picture
                                                                            class</span>
                                                                        <!--begin::Icon-->
                                                                        <span class="cursor-pointer"
                                                                            data-bs-toggle="tooltip"
                                                                            data-bs-placement="top"
                                                                            title="Held by Dr. Lebron">
                                                                            <i
                                                                                class="ki-duotone ki-information fs-1 text-warning">
                                                                                <span class="path1"></span>
                                                                                <span class="path2"></span>
                                                                                <span class="path3"></span>
                                                                            </i>
                                                                        </span>
                                                                        <!--end::Icon-->
                                                                    </td>
                                                                    <td class="text-gray-500 fs-6 fw-bold">Cabinet:
                                                                        <span class="text-gray-800">12</span>
                                                                    </td>
                                                                    <td class="pe-0 text-end">
                                                                        <button
                                                                            class="btn btn-sm btn-light">Skip</button>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                            <!--end::Table body-->
                                                        </table>
                                                        <!--end::Table-->
                                                    </div>
                                                    <!--end::Table container-->
                                                </div>
                                                <!--end::Tap pane-->
                                                <!--begin::Tap pane-->
                                                <div class="tab-pane fade show active"
                                                    id="kt_table_widget_7_tab_content_3">
                                                    <!--begin::Table container-->
                                                    <div class="table-responsive">
                                                        <!--begin::Table-->
                                                        <table class="table align-middle">
                                                            <!--begin::Table head-->
                                                            <thead>
                                                                <tr>
                                                                    <th class="p-0 min-w-150px"></th>
                                                                    <th class="p-0 min-w-200px"></th>
                                                                    <th class="p-0 min-w-100px"></th>
                                                                    <th class="p-0 min-w-80px"></th>
                                                                </tr>
                                                            </thead>
                                                            <!--end::Table head-->
                                                            <!--begin::Table body-->
                                                            <tbody>
                                                                <tr>
                                                                    <td class="text-gray-800 fs-6 fw-bold">
                                                                        08:30-09:12am</td>
                                                                    <td class="text-gray-500 fs-6 fw-bold">lesson 1:
                                                                        <span class="text-gray-800">Math class</span>
                                                                    </td>
                                                                    <td class="text-gray-500 fs-6 fw-bold">Cabinet:
                                                                        <span class="text-gray-800">45</span>
                                                                    </td>
                                                                    <td class="pe-0 text-end">
                                                                        <button
                                                                            class="btn btn-sm btn-light">Skip</button>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="px-3 py-2 text-gray-600 rounded bg-light fs-8 fw-bold"
                                                                        colspan="4">Break 15min</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="text-gray-800 fs-6 fw-bold">
                                                                        09:30-10:50am</td>
                                                                    <td class="text-gray-500 fs-6 fw-bold">lesson 2:
                                                                        <span class="text-gray-800">History
                                                                            class</span>
                                                                    </td>
                                                                    <td class="text-gray-500 fs-6 fw-bold">Cabinet:
                                                                        <span class="text-gray-800">12</span>
                                                                    </td>
                                                                    <td class="pe-0 text-end">
                                                                        <button
                                                                            class="btn btn-sm btn-light">Skip</button>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="px-3 py-2 text-gray-600 rounded bg-light fs-8 fw-bold"
                                                                        colspan="4">Break 20min</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="text-gray-800 fs-6 fw-bold">
                                                                        10:35-11:20am</td>
                                                                    <td class="text-gray-500 fs-6 fw-bold">lesson 3:
                                                                        <span class="text-gray-800">Sports
                                                                            class</span>
                                                                    </td>
                                                                    <td class="text-gray-500 fs-6 fw-bold">Court:
                                                                        <span class="text-gray-800">Main</span>
                                                                    </td>
                                                                    <td class="pe-0 text-end">
                                                                        <button
                                                                            class="btn btn-sm btn-light">Skip</button>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="px-3 py-2 text-gray-600 rounded bg-light fs-8 fw-bold"
                                                                        colspan="4">Break 15min</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="text-gray-800 fs-6 fw-bold">
                                                                        12:40-13:25pm</td>
                                                                    <td class="text-gray-500 fs-6 fw-bold">lesson 4:
                                                                        <span class="text-gray-800">Chemistry
                                                                            class</span>
                                                                        <!--begin::Icon-->
                                                                        <span class="cursor-pointer"
                                                                            data-bs-toggle="tooltip"
                                                                            data-bs-placement="top"
                                                                            title="Held by Dr. Natali">
                                                                            <i
                                                                                class="ki-duotone ki-information fs-1 text-warning">
                                                                                <span class="path1"></span>
                                                                                <span class="path2"></span>
                                                                                <span class="path3"></span>
                                                                            </i>
                                                                        </span>
                                                                        <!--end::Icon-->
                                                                    </td>
                                                                    <td class="text-gray-500 fs-6 fw-bold">Cabinet:
                                                                        <span class="text-gray-800">19</span>
                                                                    </td>
                                                                    <td class="pe-0 text-end">
                                                                        <button
                                                                            class="btn btn-sm btn-light">Skip</button>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                            <!--end::Table body-->
                                                        </table>
                                                        <!--end::Table-->
                                                    </div>
                                                    <!--end::Table container-->
                                                </div>
                                                <!--end::Tap pane-->
                                                <!--begin::Tap pane-->
                                                <div class="tab-pane fade" id="kt_table_widget_7_tab_content_4">
                                                    <!--begin::Table container-->
                                                    <div class="table-responsive">
                                                        <!--begin::Table-->
                                                        <table class="table align-middle">
                                                            <!--begin::Table head-->
                                                            <thead>
                                                                <tr>
                                                                    <th class="p-0 min-w-150px"></th>
                                                                    <th class="p-0 min-w-200px"></th>
                                                                    <th class="p-0 min-w-100px"></th>
                                                                    <th class="p-0 min-w-80px"></th>
                                                                </tr>
                                                            </thead>
                                                            <!--end::Table head-->
                                                            <!--begin::Table body-->
                                                            <tbody>
                                                                <tr>
                                                                    <td class="text-gray-800 fs-6 fw-bold">
                                                                        11:25-14:13am</td>
                                                                    <td class="text-gray-500 fs-6 fw-bold">lesson 1:
                                                                        <span class="text-gray-800">Geography
                                                                            class</span>
                                                                    </td>
                                                                    <td class="text-gray-500 fs-6 fw-bold">Cabinet:
                                                                        <span class="text-gray-800">15</span>
                                                                    </td>
                                                                    <td class="pe-0 text-end">
                                                                        <button
                                                                            class="btn btn-sm btn-light">Skip</button>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="px-3 py-2 text-gray-600 rounded bg-light fs-8 fw-bold"
                                                                        colspan="4">Break 25min</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="text-gray-800 fs-6 fw-bold">
                                                                        08:30-09:30am</td>
                                                                    <td class="text-gray-500 fs-6 fw-bold">lesson 2:
                                                                        <span class="text-gray-800">English
                                                                            class</span>
                                                                    </td>
                                                                    <td class="text-gray-500 fs-6 fw-bold">Cabinet:
                                                                        <span class="text-gray-800">9</span>
                                                                    </td>
                                                                    <td class="pe-0 text-end">
                                                                        <button
                                                                            class="btn btn-sm btn-light">Skip</button>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="px-3 py-2 text-gray-600 rounded bg-light fs-8 fw-bold"
                                                                        colspan="4">Break 20min</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="text-gray-800 fs-6 fw-bold">
                                                                        11:15-12:13am</td>
                                                                    <td class="text-gray-500 fs-6 fw-bold">lesson 3:
                                                                        <span class="text-gray-800">Sports
                                                                            class</span>
                                                                    </td>
                                                                    <td class="text-gray-500 fs-6 fw-bold">Court:
                                                                        <span class="text-gray-800">Main</span>
                                                                    </td>
                                                                    <td class="pe-0 text-end">
                                                                        <button
                                                                            class="btn btn-sm btn-light">Skip</button>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="px-3 py-2 text-gray-600 rounded bg-light fs-8 fw-bold"
                                                                        colspan="4">Break 25min</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="text-gray-800 fs-6 fw-bold">
                                                                        14:10-15:35pm</td>
                                                                    <td class="text-gray-500 fs-6 fw-bold">lesson 4:
                                                                        <span class="text-gray-800">Picture
                                                                            class</span>
                                                                        <!--begin::Icon-->
                                                                        <span class="cursor-pointer"
                                                                            data-bs-toggle="tooltip"
                                                                            data-bs-placement="top"
                                                                            title="Held by Dr. Kevin">
                                                                            <i
                                                                                class="ki-duotone ki-information fs-1 text-warning">
                                                                                <span class="path1"></span>
                                                                                <span class="path2"></span>
                                                                                <span class="path3"></span>
                                                                            </i>
                                                                        </span>
                                                                        <!--end::Icon-->
                                                                    </td>
                                                                    <td class="text-gray-500 fs-6 fw-bold">Cabinet:
                                                                        <span class="text-gray-800">12</span>
                                                                    </td>
                                                                    <td class="pe-0 text-end">
                                                                        <button
                                                                            class="btn btn-sm btn-light">Skip</button>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                            <!--end::Table body-->
                                                        </table>
                                                        <!--end::Table-->
                                                    </div>
                                                    <!--end::Table container-->
                                                </div>
                                                <!--end::Tap pane-->
                                                <!--begin::Tap pane-->
                                                <div class="tab-pane fade" id="kt_table_widget_7_tab_content_5">
                                                    <!--begin::Table container-->
                                                    <div class="table-responsive">
                                                        <!--begin::Table-->
                                                        <table class="table align-middle">
                                                            <!--begin::Table head-->
                                                            <thead>
                                                                <tr>
                                                                    <th class="p-0 min-w-150px"></th>
                                                                    <th class="p-0 min-w-200px"></th>
                                                                    <th class="p-0 min-w-100px"></th>
                                                                    <th class="p-0 min-w-80px"></th>
                                                                </tr>
                                                            </thead>
                                                            <!--end::Table head-->
                                                            <!--begin::Table body-->
                                                            <tbody>
                                                                <tr>
                                                                    <td class="text-gray-800 fs-6 fw-bold">
                                                                        11:43-09:43am</td>
                                                                    <td class="text-gray-500 fs-6 fw-bold">lesson 1:
                                                                        <span class="text-gray-800">French
                                                                            class</span>
                                                                    </td>
                                                                    <td class="text-gray-500 fs-6 fw-bold">Cabinet:
                                                                        <span class="text-gray-800">5</span>
                                                                    </td>
                                                                    <td class="pe-0 text-end">
                                                                        <button
                                                                            class="btn btn-sm btn-light">Skip</button>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="px-3 py-2 text-gray-600 rounded bg-light fs-8 fw-bold"
                                                                        colspan="4">Break 205min</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="text-gray-800 fs-6 fw-bold">
                                                                        09:40-11:20am</td>
                                                                    <td class="text-gray-500 fs-6 fw-bold">lesson 2:
                                                                        <span class="text-gray-800">Physics
                                                                            class</span>
                                                                    </td>
                                                                    <td class="text-gray-500 fs-6 fw-bold">Cabinet:
                                                                        <span class="text-gray-800">13</span>
                                                                    </td>
                                                                    <td class="pe-0 text-end">
                                                                        <button
                                                                            class="btn btn-sm btn-light">Skip</button>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="px-3 py-2 text-gray-600 rounded bg-light fs-8 fw-bold"
                                                                        colspan="4">Break 25min</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="text-gray-800 fs-6 fw-bold">
                                                                        10:35-43:09am</td>
                                                                    <td class="text-gray-500 fs-6 fw-bold">lesson 3:
                                                                        <span class="text-gray-800">Chemistry
                                                                            class</span>
                                                                    </td>
                                                                    <td class="text-gray-500 fs-6 fw-bold">Court:
                                                                        <span class="text-gray-800">Main</span>
                                                                    </td>
                                                                    <td class="pe-0 text-end">
                                                                        <button
                                                                            class="btn btn-sm btn-light">Skip</button>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="px-3 py-2 text-gray-600 rounded bg-light fs-8 fw-bold"
                                                                        colspan="4">Break 15min</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="text-gray-800 fs-6 fw-bold">
                                                                        15:30-12:23pm</td>
                                                                    <td class="text-gray-500 fs-6 fw-bold">lesson 4:
                                                                        <span class="text-gray-800">Biology
                                                                            class</span>
                                                                        <!--begin::Icon-->
                                                                        <span class="cursor-pointer"
                                                                            data-bs-toggle="tooltip"
                                                                            data-bs-placement="top"
                                                                            title="Held by Dr. Emma">
                                                                            <i
                                                                                class="ki-duotone ki-information fs-1 text-warning">
                                                                                <span class="path1"></span>
                                                                                <span class="path2"></span>
                                                                                <span class="path3"></span>
                                                                            </i>
                                                                        </span>
                                                                        <!--end::Icon-->
                                                                    </td>
                                                                    <td class="text-gray-500 fs-6 fw-bold">Cabinet:
                                                                        <span class="text-gray-800">23</span>
                                                                    </td>
                                                                    <td class="pe-0 text-end">
                                                                        <button
                                                                            class="btn btn-sm btn-light">Skip</button>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                            <!--end::Table body-->
                                                        </table>
                                                        <!--end::Table-->
                                                    </div>
                                                    <!--end::Table container-->
                                                </div>
                                                <!--end::Tap pane-->
                                            </div>
                                            <!--end::Tab Content-->
                                            <!--begin::Action-->
                                            <div class="float-end">
                                                <a href="#" class="btn btn-sm btn-light me-2"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#kt_modal_create_project">Add Lesson</a>
                                                <a href="#" class="btn btn-sm btn-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#kt_modal_create_app">Call Sick for Today</a>
                                            </div>
                                            <!--end::Action-->
                                        </div>
                                        <!--end: Card Body-->
                                    </div>
                                    <!--end::Table widget 8-->
                                </div>
                                <!--end::Col-->
                                <!--begin::Col-->
                                <div class="col-xl-4">
                                    <!--begin::Engage widget 5-->
                                    <div class="card bg-primary h-xl-100">
                                        <!--begin::Body-->
                                        <div class="pb-14 card-body d-flex flex-column pt-13">
                                            <!--begin::Heading-->
                                            <div class="m-0">
                                                <!--begin::Title-->
                                                <h1 class="mb-9 text-center text-white fw-semibold lh-lg">How are you
                                                    feeling today?
                                                    <span class="fw-bolder">Here we are to Help</span>
                                                </h1>
                                                <!--end::Title-->
                                                <!--begin::Illustration-->
                                                <div class="my-5 flex-grow-1 bgi-no-repeat bgi-size-contain bgi-position-x-center card-rounded-bottom h-200px mh-200px mb-lg-12"
                                                    style="background-image:url('assets/media/svg/illustrations/easy/6.svg')">
                                                </div>
                                                <!--end::Illustration-->
                                            </div>
                                            <!--end::Heading-->
                                            <!--begin::Links-->
                                            <div class="text-center">
                                                <!--begin::Link-->
                                                <a class="btn btn-sm btn-success btn-color-white me-2"
                                                    data-bs-target="#kt_modal_invite_friends"
                                                    data-bs-toggle="modal">Psychologist</a>
                                                <!--end::Link-->
                                                <!--begin::Link-->
                                                <a class="bg-white bg-opacity-20 btn btn-sm btn-color-white"
                                                    href="pages/careers/list.html">Nurse</a>
                                                <!--end::Link-->
                                            </div>
                                            <!--end::Links-->
                                        </div>
                                        <!--end::Body-->
                                    </div>
                                    <!--end::Engage widget 5-->
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Row-->
                            <!--begin::Row-->
                            <div class="mb-5 row g-5 g-xl-10 mb-xl-10">
                                <!--begin::Col-->
                                <div class="col-xxl-8">
                                    <!--begin::Chart widget 22-->
                                    <div class="card h-xl-100">
                                        <!--begin::Header-->
                                        <div class="py-0 card-header position-relative border-bottom-2">
                                            <!--begin::Nav-->
                                            <ul class="mt-3 nav nav-stretch nav-pills nav-pills-custom d-flex">
                                                <!--begin::Item-->
                                                <li class="p-0 nav-item ms-0 me-8">
                                                    <!--begin::Link-->
                                                    <a class="px-0 nav-link btn btn-color-muted active"
                                                        data-bs-toggle="tab" id="kt_chart_widgets_22_tab_1"
                                                        href="#kt_chart_widgets_22_tab_content_1">
                                                        <!--begin::Subtitle-->
                                                        <span class="mb-3 nav-text fw-semibold fs-4">Overview</span>
                                                        <!--end::Subtitle-->
                                                        <!--begin::Bullet-->
                                                        <span
                                                            class="rounded bullet-custom position-absolute z-index-2 w-100 h-2px top-100 bottom-n100 bg-primary"></span>
                                                        <!--end::Bullet-->
                                                    </a>
                                                    <!--end::Link-->
                                                </li>
                                                <!--end::Item-->
                                                <!--begin::Item-->
                                                <li class="p-0 nav-item ms-0">
                                                    <!--begin::Link-->
                                                    <a class="px-0 nav-link btn btn-color-muted"
                                                        data-bs-toggle="tab" id="kt_chart_widgets_22_tab_2"
                                                        href="#kt_chart_widgets_22_tab_content_2">
                                                        <!--begin::Subtitle-->
                                                        <span
                                                            class="mb-3 nav-text fw-semibold fs-4">Performance</span>
                                                        <!--end::Subtitle-->
                                                        <!--begin::Bullet-->
                                                        <span
                                                            class="rounded bullet-custom position-absolute z-index-2 w-100 h-2px top-100 bottom-n100 bg-primary"></span>
                                                        <!--end::Bullet-->
                                                    </a>
                                                    <!--end::Link-->
                                                </li>
                                                <!--end::Item-->
                                            </ul>
                                            <!--end::Nav-->
                                            <!--begin::Toolbar-->
                                            <div class="card-toolbar">
                                                <!--begin::Daterangepicker(defined in src/js/layout/app.js)-->
                                                <div data-kt-daterangepicker="true"
                                                    data-kt-daterangepicker-opens="left"
                                                    class="px-4 btn btn-sm btn-light d-flex align-items-center">
                                                    <!--begin::Display range-->
                                                    <span class="text-gray-600 fw-bold">Loading date range...</span>
                                                    <!--end::Display range-->
                                                    <i
                                                        class="text-gray-500 ki-duotone ki-calendar-8 lh-0 fs-2 ms-2 me-0">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                        <span class="path3"></span>
                                                        <span class="path4"></span>
                                                        <span class="path5"></span>
                                                        <span class="path6"></span>
                                                    </i>
                                                </div>
                                                <!--end::Daterangepicker-->
                                            </div>
                                            <!--end::Toolbar-->
                                        </div>
                                        <!--end::Header-->
                                        <!--begin::Body-->
                                        <div class="pb-3 card-body">
                                            <!--begin::Tab Content-->
                                            <div class="tab-content">
                                                <!--begin::Tap pane-->
                                                <div class="tab-pane fade show active"
                                                    id="kt_chart_widgets_22_tab_content_1">
                                                    <!--begin::Wrapper-->
                                                    <div class="flex-wrap d-flex flex-md-nowrap">
                                                        <!--begin::Items-->
                                                        <div class="me-md-5 w-100">
                                                            <!--begin::Item-->
                                                            <div
                                                                class="p-6 mb-6 rounded border border-gray-300 border-dashed d-flex">
                                                                <!--begin::Block-->
                                                                <div
                                                                    class="d-flex align-items-center flex-grow-1 me-2 me-sm-5">
                                                                    <!--begin::Symbol-->
                                                                    <div class="symbol symbol-50px me-4">
                                                                        <span class="symbol-label">
                                                                            <i
                                                                                class="ki-duotone ki-timer fs-2qx text-primary">
                                                                                <span class="path1"></span>
                                                                                <span class="path2"></span>
                                                                                <span class="path3"></span>
                                                                            </i>
                                                                        </span>
                                                                    </div>
                                                                    <!--end::Symbol-->
                                                                    <!--begin::Section-->
                                                                    <div class="me-2">
                                                                        <a href="#"
                                                                            class="text-gray-800 text-hover-primary fs-6 fw-bold">Attendance</a>
                                                                        <span
                                                                            class="text-gray-500 fw-bold d-block fs-7">Great,
                                                                            you always attending class. keep it
                                                                            up</span>
                                                                    </div>
                                                                    <!--end::Section-->
                                                                </div>
                                                                <!--end::Block-->
                                                                <!--begin::Info-->
                                                                <div class="d-flex align-items-center">
                                                                    <span
                                                                        class="text-gray-900 fw-bolder fs-2x">73</span>
                                                                    <span
                                                                        class="pt-1 mx-1 text-gray-600 fw-semibold fs-2">/</span>
                                                                    <span
                                                                        class="pt-2 text-gray-600 fw-semibold fs-2 me-3">76</span>
                                                                    <span
                                                                        class="px-2 badge badge-lg badge-light-success align-self-center">95%</span>
                                                                </div>
                                                                <!--end::Info-->
                                                            </div>
                                                            <!--end::Item-->
                                                            <!--begin::Item-->
                                                            <div
                                                                class="p-6 mb-6 rounded border border-gray-300 border-dashed d-flex">
                                                                <!--begin::Block-->
                                                                <div
                                                                    class="d-flex align-items-center flex-grow-1 me-2 me-sm-5">
                                                                    <!--begin::Symbol-->
                                                                    <div class="symbol symbol-50px me-4">
                                                                        <span class="symbol-label">
                                                                            <i
                                                                                class="ki-duotone ki-element-11 fs-2qx text-primary">
                                                                                <span class="path1"></span>
                                                                                <span class="path2"></span>
                                                                                <span class="path3"></span>
                                                                                <span class="path4"></span>
                                                                            </i>
                                                                        </span>
                                                                    </div>
                                                                    <!--end::Symbol-->
                                                                    <!--begin::Section-->
                                                                    <div class="me-2">
                                                                        <a href="#"
                                                                            class="text-gray-800 text-hover-primary fs-6 fw-bold">Homeworks</a>
                                                                        <span
                                                                            class="text-gray-500 fw-bold d-block fs-7">Don’t
                                                                            forget to turn in your task</span>
                                                                    </div>
                                                                    <!--end::Section-->
                                                                </div>
                                                                <!--end::Block-->
                                                                <!--begin::Info-->
                                                                <div class="d-flex align-items-center">
                                                                    <span
                                                                        class="text-gray-900 fw-bolder fs-2x">207</span>
                                                                    <span
                                                                        class="pt-1 mx-1 text-gray-600 fw-semibold fs-2">/</span>
                                                                    <span
                                                                        class="pt-2 text-gray-600 fw-semibold fs-2 me-3">214</span>
                                                                    <span
                                                                        class="px-2 badge badge-lg badge-light-success align-self-center">92%</span>
                                                                </div>
                                                                <!--end::Info-->
                                                            </div>
                                                            <!--end::Item-->
                                                            <!--begin::Item-->
                                                            <div
                                                                class="p-6 mb-6 rounded border border-gray-300 border-dashed d-flex">
                                                                <!--begin::Block-->
                                                                <div
                                                                    class="d-flex align-items-center flex-grow-1 me-2 me-sm-5">
                                                                    <!--begin::Symbol-->
                                                                    <div class="symbol symbol-50px me-4">
                                                                        <span class="symbol-label">
                                                                            <i
                                                                                class="ki-duotone ki-abstract-24 fs-2qx text-primary">
                                                                                <span class="path1"></span>
                                                                                <span class="path2"></span>
                                                                            </i>
                                                                        </span>
                                                                    </div>
                                                                    <!--end::Symbol-->
                                                                    <!--begin::Section-->
                                                                    <div class="me-2">
                                                                        <a href="#"
                                                                            class="text-gray-800 text-hover-primary fs-6 fw-bold">Tests</a>
                                                                        <span
                                                                            class="text-gray-500 fw-bold d-block fs-7">You
                                                                            take 12 subjects at this semester</span>
                                                                    </div>
                                                                    <!--end::Section-->
                                                                </div>
                                                                <!--end::Block-->
                                                                <!--begin::Info-->
                                                                <div class="d-flex align-items-center">
                                                                    <span
                                                                        class="text-gray-900 fw-bolder fs-2x">27</span>
                                                                    <span
                                                                        class="pt-1 mx-1 text-gray-600 fw-semibold fs-2">/</span>
                                                                    <span
                                                                        class="pt-2 text-gray-600 fw-semibold fs-2 me-3">38</span>
                                                                    <span
                                                                        class="px-2 badge badge-lg badge-light-warning align-self-center">80%</span>
                                                                </div>
                                                                <!--end::Info-->
                                                            </div>
                                                            <!--end::Item-->
                                                        </div>
                                                        <!--end::Items-->
                                                        <!--begin::Container-->
                                                        <div
                                                            class="pt-3 pb-10 mx-auto d-flex justify-content-between flex-column w-225px w-md-600px mx-md-0">
                                                            <!--begin::Title-->
                                                            <div class="mb-5 text-center text-gray-900 fs-4 fw-bold">
                                                                Session Attendance
                                                                <br />for Current Academic Year
                                                            </div>
                                                            <!--end::Title-->
                                                            <!--begin::Chart-->
                                                            <div id="kt_chart_widgets_22_chart_1"
                                                                class="mx-auto mb-4"></div>
                                                            <!--end::Chart-->
                                                            <!--begin::Labels-->
                                                            <div class="mx-auto">
                                                                <!--begin::Label-->
                                                                <div class="mb-2 d-flex align-items-center">
                                                                    <!--begin::Bullet-->
                                                                    <div
                                                                        class="bullet bullet-dot w-8px h-7px bg-success me-2">
                                                                    </div>
                                                                    <!--end::Bullet-->
                                                                    <!--begin::Label-->
                                                                    <div class="fs-8 fw-semibold text-muted">
                                                                        Precent(133)</div>
                                                                    <!--end::Label-->
                                                                </div>
                                                                <!--end::Label-->
                                                                <!--begin::Label-->
                                                                <div class="mb-2 d-flex align-items-center">
                                                                    <!--begin::Bullet-->
                                                                    <div
                                                                        class="bullet bullet-dot w-8px h-7px bg-primary me-2">
                                                                    </div>
                                                                    <!--end::Bullet-->
                                                                    <!--begin::Label-->
                                                                    <div class="fs-8 fw-semibold text-muted">
                                                                        Illness(9)</div>
                                                                    <!--end::Label-->
                                                                </div>
                                                                <!--end::Label-->
                                                                <!--begin::Label-->
                                                                <div class="mb-2 d-flex align-items-center">
                                                                    <!--begin::Bullet-->
                                                                    <div
                                                                        class="bullet bullet-dot w-8px h-7px bg-info me-2">
                                                                    </div>
                                                                    <!--end::Bullet-->
                                                                    <!--begin::Label-->
                                                                    <div class="fs-8 fw-semibold text-muted">Late(2)
                                                                    </div>
                                                                    <!--end::Label-->
                                                                </div>
                                                                <!--end::Label-->
                                                                <!--begin::Label-->
                                                                <div class="mb-2 d-flex align-items-center">
                                                                    <!--begin::Bullet-->
                                                                    <div
                                                                        class="bullet bullet-dot w-8px h-7px bg-danger me-2">
                                                                    </div>
                                                                    <!--end::Bullet-->
                                                                    <!--begin::Label-->
                                                                    <div class="fs-8 fw-semibold text-muted">Absent(3)
                                                                    </div>
                                                                    <!--end::Label-->
                                                                </div>
                                                                <!--end::Label-->
                                                            </div>
                                                            <!--end::Labels-->
                                                        </div>
                                                        <!--end::Container-->
                                                    </div>
                                                    <!--end::Wrapper-->
                                                </div>
                                                <!--end::Tap pane-->
                                                <!--begin::Tap pane-->
                                                <div class="tab-pane fade" id="kt_chart_widgets_22_tab_content_2">
                                                    <!--begin::Wrapper-->
                                                    <div class="flex-wrap d-flex flex-md-nowrap">
                                                        <!--begin::Items-->
                                                        <div class="me-md-5 w-100">
                                                            <!--begin::Item-->
                                                            <div
                                                                class="p-6 mb-6 rounded border border-gray-300 border-dashed d-flex">
                                                                <!--begin::Block-->
                                                                <div
                                                                    class="d-flex align-items-center flex-grow-1 me-2 me-sm-5">
                                                                    <!--begin::Symbol-->
                                                                    <div class="symbol symbol-50px me-4">
                                                                        <span class="symbol-label">
                                                                            <i
                                                                                class="ki-duotone ki-element-11 fs-2qx text-primary">
                                                                                <span class="path1"></span>
                                                                                <span class="path2"></span>
                                                                                <span class="path3"></span>
                                                                                <span class="path4"></span>
                                                                            </i>
                                                                        </span>
                                                                    </div>
                                                                    <!--end::Symbol-->
                                                                    <!--begin::Section-->
                                                                    <div class="me-2">
                                                                        <a href="#"
                                                                            class="text-gray-800 text-hover-primary fs-6 fw-bold">Homeworks</a>
                                                                        <span
                                                                            class="text-gray-500 fw-bold d-block fs-7">Don’t
                                                                            forget to turn in your task</span>
                                                                    </div>
                                                                    <!--end::Section-->
                                                                </div>
                                                                <!--end::Block-->
                                                                <!--begin::Info-->
                                                                <div class="d-flex align-items-center">
                                                                    <span
                                                                        class="text-gray-900 fw-bolder fs-2x">423</span>
                                                                    <span
                                                                        class="pt-1 mx-1 text-gray-600 fw-semibold fs-2">/</span>
                                                                    <span
                                                                        class="pt-2 text-gray-600 fw-semibold fs-2 me-3">154</span>
                                                                    <span
                                                                        class="px-2 badge badge-lg badge-light-danger align-self-center">74%</span>
                                                                </div>
                                                                <!--end::Info-->
                                                            </div>
                                                            <!--end::Item-->
                                                            <!--begin::Item-->
                                                            <div
                                                                class="p-6 mb-6 rounded border border-gray-300 border-dashed d-flex">
                                                                <!--begin::Block-->
                                                                <div
                                                                    class="d-flex align-items-center flex-grow-1 me-2 me-sm-5">
                                                                    <!--begin::Symbol-->
                                                                    <div class="symbol symbol-50px me-4">
                                                                        <span class="symbol-label">
                                                                            <i
                                                                                class="ki-duotone ki-abstract-24 fs-2qx text-primary">
                                                                                <span class="path1"></span>
                                                                                <span class="path2"></span>
                                                                            </i>
                                                                        </span>
                                                                    </div>
                                                                    <!--end::Symbol-->
                                                                    <!--begin::Section-->
                                                                    <div class="me-2">
                                                                        <a href="#"
                                                                            class="text-gray-800 text-hover-primary fs-6 fw-bold">Tests</a>
                                                                        <span
                                                                            class="text-gray-500 fw-bold d-block fs-7">You
                                                                            take 12 subjects at this semester</span>
                                                                    </div>
                                                                    <!--end::Section-->
                                                                </div>
                                                                <!--end::Block-->
                                                                <!--begin::Info-->
                                                                <div class="d-flex align-items-center">
                                                                    <span
                                                                        class="text-gray-900 fw-bolder fs-2x">43</span>
                                                                    <span
                                                                        class="pt-1 mx-1 text-gray-600 fw-semibold fs-2">/</span>
                                                                    <span
                                                                        class="pt-2 text-gray-600 fw-semibold fs-2 me-3">53</span>
                                                                    <span
                                                                        class="px-2 badge badge-lg badge-light-info align-self-center">65%</span>
                                                                </div>
                                                                <!--end::Info-->
                                                            </div>
                                                            <!--end::Item-->
                                                            <!--begin::Item-->
                                                            <div
                                                                class="p-6 mb-6 rounded border border-gray-300 border-dashed d-flex">
                                                                <!--begin::Block-->
                                                                <div
                                                                    class="d-flex align-items-center flex-grow-1 me-2 me-sm-5">
                                                                    <!--begin::Symbol-->
                                                                    <div class="symbol symbol-50px me-4">
                                                                        <span class="symbol-label">
                                                                            <i
                                                                                class="ki-duotone ki-timer fs-2qx text-primary">
                                                                                <span class="path1"></span>
                                                                                <span class="path2"></span>
                                                                                <span class="path3"></span>
                                                                            </i>
                                                                        </span>
                                                                    </div>
                                                                    <!--end::Symbol-->
                                                                    <!--begin::Section-->
                                                                    <div class="me-2">
                                                                        <a href="#"
                                                                            class="text-gray-800 text-hover-primary fs-6 fw-bold">Attendance</a>
                                                                        <span
                                                                            class="text-gray-500 fw-bold d-block fs-7">Great,
                                                                            you always attending class. keep it
                                                                            up</span>
                                                                    </div>
                                                                    <!--end::Section-->
                                                                </div>
                                                                <!--end::Block-->
                                                                <!--begin::Info-->
                                                                <div class="d-flex align-items-center">
                                                                    <span
                                                                        class="text-gray-900 fw-bolder fs-2x">53</span>
                                                                    <span
                                                                        class="pt-1 mx-1 text-gray-600 fw-semibold fs-2">/</span>
                                                                    <span
                                                                        class="pt-2 text-gray-600 fw-semibold fs-2 me-3">94</span>
                                                                    <span
                                                                        class="px-2 badge badge-lg badge-light-primary align-self-center">87%</span>
                                                                </div>
                                                                <!--end::Info-->
                                                            </div>
                                                            <!--end::Item-->
                                                        </div>
                                                        <!--end::Items-->
                                                        <!--begin::Container-->
                                                        <div
                                                            class="pt-3 pb-10 mx-auto d-flex justify-content-between flex-column w-225px w-md-600px mx-md-0">
                                                            <!--begin::Title-->
                                                            <div class="mb-5 text-center text-gray-900 fs-4 fw-bold">
                                                                Session Attendance
                                                                <br />for Current Academic Year
                                                            </div>
                                                            <!--end::Title-->
                                                            <!--begin::Chart-->
                                                            <div id="kt_chart_widgets_22_chart_2"
                                                                class="mx-auto mb-4"></div>
                                                            <!--end::Chart-->
                                                            <!--begin::Labels-->
                                                            <div class="mx-auto">
                                                                <!--begin::Label-->
                                                                <div class="mb-2 d-flex align-items-center">
                                                                    <!--begin::Bullet-->
                                                                    <div
                                                                        class="bullet bullet-dot w-8px h-7px bg-success me-2">
                                                                    </div>
                                                                    <!--end::Bullet-->
                                                                    <!--begin::Label-->
                                                                    <div class="fs-8 fw-semibold text-muted">
                                                                        Precent(133)</div>
                                                                    <!--end::Label-->
                                                                </div>
                                                                <!--end::Label-->
                                                                <!--begin::Label-->
                                                                <div class="mb-2 d-flex align-items-center">
                                                                    <!--begin::Bullet-->
                                                                    <div
                                                                        class="bullet bullet-dot w-8px h-7px bg-primary me-2">
                                                                    </div>
                                                                    <!--end::Bullet-->
                                                                    <!--begin::Label-->
                                                                    <div class="fs-8 fw-semibold text-muted">
                                                                        Illness(9)</div>
                                                                    <!--end::Label-->
                                                                </div>
                                                                <!--end::Label-->
                                                                <!--begin::Label-->
                                                                <div class="mb-2 d-flex align-items-center">
                                                                    <!--begin::Bullet-->
                                                                    <div
                                                                        class="bullet bullet-dot w-8px h-7px bg-info me-2">
                                                                    </div>
                                                                    <!--end::Bullet-->
                                                                    <!--begin::Label-->
                                                                    <div class="fs-8 fw-semibold text-muted">Late(2)
                                                                    </div>
                                                                    <!--end::Label-->
                                                                </div>
                                                                <!--end::Label-->
                                                                <!--begin::Label-->
                                                                <div class="mb-2 d-flex align-items-center">
                                                                    <!--begin::Bullet-->
                                                                    <div
                                                                        class="bullet bullet-dot w-8px h-7px bg-danger me-2">
                                                                    </div>
                                                                    <!--end::Bullet-->
                                                                    <!--begin::Label-->
                                                                    <div class="fs-8 fw-semibold text-muted">Absent(3)
                                                                    </div>
                                                                    <!--end::Label-->
                                                                </div>
                                                                <!--end::Label-->
                                                            </div>
                                                            <!--end::Labels-->
                                                        </div>
                                                        <!--end::Container-->
                                                    </div>
                                                    <!--end::Wrapper-->
                                                </div>
                                                <!--end::Tap pane-->
                                            </div>
                                            <!--end::Tab Content-->
                                        </div>
                                        <!--end: Card Body-->
                                    </div>
                                    <!--end::Chart widget 22-->
                                </div>
                                <!--end::Col-->
                                <!--begin::Col-->
                                <div class="col-xxl-4">
                                    <!--begin::Slider Widget 3-->
                                    <div id="kt_sliders_widget_3_slider"
                                        class="card card-flush carousel slide h-xl-100" data-bs-ride="carousel"
                                        data-bs-interval="5000">
                                        <!--begin::Header-->
                                        <div class="pt-5 mb-5 card-header">
                                            <!--begin::Title-->
                                            <h3 class="card-title align-items-start flex-column">
                                                <span class="text-gray-900 card-label fw-bold">Academic
                                                    Performance</span>
                                                <span class="mt-1 text-gray-500 fw-semibold fs-7">Avg. 72% completed
                                                    lessons</span>
                                            </h3>
                                            <!--end::Title-->
                                            <!--begin::Toolbar-->
                                            <div class="card-toolbar">
                                                <div class="d-flex justify-content-end">
                                                    <a href="#kt_sliders_widget_3_slider"
                                                        class="carousel-control-prev position-relative me-5 active"
                                                        role="button" data-bs-slide="prev">
                                                        <i class="text-gray-500 ki-duotone ki-left-square fs-2x">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                        </i>
                                                    </a>
                                                    <a href="#kt_sliders_widget_3_slider"
                                                        class="carousel-control-next position-relative me-1"
                                                        role="button" data-bs-slide="next">
                                                        <i class="text-gray-500 ki-duotone ki-right-square fs-2x">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                        </i>
                                                    </a>
                                                </div>
                                            </div>
                                            <!--end::Toolbar-->
                                        </div>
                                        <!--end::Header-->
                                        <!--begin::Body-->
                                        <div class="p-0 card-body">
                                            <!--begin::Carousel-->
                                            <div class="carousel-inner">
                                                <!--begin::Item-->
                                                <div class="carousel-item active show">
                                                    <!--begin::Title-->
                                                    <span
                                                        class="px-8 mb-3 text-gray-800 fw-bold fs-4">Chemistry</span>
                                                    <!--end::Title-->
                                                    <!--begin::Statistics-->
                                                    <div class="px-8 d-flex align-items-center w-100">
                                                        <!--begin::Number-->
                                                        <span class="text-gray-800 fs-2qx fw-bold">6</span>
                                                        <!--end::Number-->
                                                        <!--begin::Progress-->
                                                        <div class="mx-3 progress h-6px w-100 bg-light-primary">
                                                            <div class="progress-bar bg-primary" role="progressbar"
                                                                style="width: 62%" aria-valuenow="62"
                                                                aria-valuemin="0" aria-valuemax="100"></div>
                                                        </div>
                                                        <!--end::Progress-->
                                                        <!--begin::Value-->
                                                        <span class="text-gray-500 fw-bold fs-4">62%</span>
                                                        <!--end::Value-->
                                                    </div>
                                                    <!--end::Statistics-->
                                                    <!--begin::Chart-->
                                                    <div id="kt_sliders_widget_3_chart_1"
                                                        class="min-h-auto ps-4 pe-6" style="height: 330px"></div>
                                                    <!--end::Chart-->
                                                </div>
                                                <!--end::Item-->
                                                <!--begin::Item-->
                                                <div class="carousel-item">
                                                    <!--begin::Title-->
                                                    <span
                                                        class="px-8 mb-3 text-gray-800 fw-bold fs-4">Mathematics</span>
                                                    <!--end::Title-->
                                                    <!--begin::Statistics-->
                                                    <div class="px-8 d-flex align-items-center w-100">
                                                        <!--begin::Number-->
                                                        <span class="text-gray-800 fs-2qx fw-bold">4</span>
                                                        <!--end::Number-->
                                                        <!--begin::Progress-->
                                                        <div class="mx-3 progress h-6px w-100 bg-light-danger">
                                                            <div class="progress-bar bg-danger" role="progressbar"
                                                                style="width: 73%" aria-valuenow="73"
                                                                aria-valuemin="0" aria-valuemax="100"></div>
                                                        </div>
                                                        <!--end::Progress-->
                                                        <!--begin::Value-->
                                                        <span class="text-gray-500 fw-bold fs-4">73%</span>
                                                        <!--end::Value-->
                                                    </div>
                                                    <!--end::Statistics-->
                                                    <!--begin::Chart-->
                                                    <div id="kt_sliders_widget_3_chart_2"
                                                        class="min-h-auto ps-4 pe-6" style="height: 330px"></div>
                                                    <!--end::Chart-->
                                                </div>
                                                <!--end::Item-->
                                            </div>
                                            <!--end::Carousel-->
                                        </div>
                                        <!--end::Body-->
                                    </div>
                                    <!--end::Slider Widget 3-->
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Row-->
                            <!--begin::Row-->
                            <div class="row g-5 g-xl-10">
                                <!--begin::Col-->
                                <div class="col-xxl-8">
                                    <!--begin::Timeline widget 2-->
                                    <div class="card h-xl-100" id="kt_timeline_widget_2_card">
                                        <!--begin::Header-->
                                        <div class="py-0 card-header position-relative border-bottom-2">
                                            <!--begin::Nav-->
                                            <ul class="mt-3 nav nav-stretch nav-pills nav-pills-custom d-flex">
                                                <!--begin::Item-->
                                                <li class="p-0 nav-item ms-0 me-8">
                                                    <!--begin::Link-->
                                                    <a class="px-0 nav-link btn btn-color-muted active"
                                                        data-bs-toggle="pill" href="#kt_timeline_widget_2_tab_1">
                                                        <!--begin::Subtitle-->
                                                        <span class="mb-3 nav-text fw-semibold fs-4">Today
                                                            Homeworks</span>
                                                        <!--end::Subtitle-->
                                                        <!--begin::Bullet-->
                                                        <span
                                                            class="rounded bullet-custom position-absolute z-index-2 w-100 h-2px top-100 bottom-n100 bg-primary"></span>
                                                        <!--end::Bullet-->
                                                    </a>
                                                    <!--end::Link-->
                                                </li>
                                                <!--end::Item-->
                                                <!--begin::Item-->
                                                <li class="p-0 nav-item ms-0 me-8">
                                                    <!--begin::Link-->
                                                    <a class="px-0 nav-link btn btn-color-muted"
                                                        data-bs-toggle="pill" href="#kt_timeline_widget_2_tab_2">
                                                        <!--begin::Subtitle-->
                                                        <span class="mb-3 nav-text fw-semibold fs-4">Recent</span>
                                                        <!--end::Subtitle-->
                                                        <!--begin::Bullet-->
                                                        <span
                                                            class="rounded bullet-custom position-absolute z-index-2 w-100 h-2px top-100 bottom-n100 bg-primary"></span>
                                                        <!--end::Bullet-->
                                                    </a>
                                                    <!--end::Link-->
                                                </li>
                                                <!--end::Item-->
                                                <!--begin::Item-->
                                                <li class="p-0 nav-item ms-0">
                                                    <!--begin::Link-->
                                                    <a class="px-0 nav-link btn btn-color-muted"
                                                        data-bs-toggle="pill" href="#kt_timeline_widget_2_tab_3">
                                                        <!--begin::Subtitle-->
                                                        <span class="mb-3 nav-text fw-semibold fs-4">Future</span>
                                                        <!--end::Subtitle-->
                                                        <!--begin::Bullet-->
                                                        <span
                                                            class="rounded bullet-custom position-absolute z-index-2 w-100 h-2px top-100 bottom-n100 bg-primary"></span>
                                                        <!--end::Bullet-->
                                                    </a>
                                                    <!--end::Link-->
                                                </li>
                                                <!--end::Item-->
                                            </ul>
                                            <!--end::Nav-->
                                        </div>
                                        <!--end::Header-->
                                        <!--begin::Body-->
                                        <div class="card-body">
                                            <!--begin::Tab Content-->
                                            <div class="tab-content">
                                                <!--begin::Tap pane-->
                                                <div class="tab-pane fade show active"
                                                    id="kt_timeline_widget_2_tab_1">
                                                    <!--begin::Table container-->
                                                    <div class="table-responsive">
                                                        <!--begin::Table-->
                                                        <table class="table align-middle gs-0 gy-4">
                                                            <!--begin::Table head-->
                                                            <thead>
                                                                <tr>
                                                                    <th class="p-0 w-10px"></th>
                                                                    <th class="p-0 w-25px"></th>
                                                                    <th class="p-0 min-w-400px"></th>
                                                                    <th class="p-0 min-w-100px"></th>
                                                                    <th class="p-0 min-w-125px"></th>
                                                                </tr>
                                                            </thead>
                                                            <!--end::Table head-->
                                                            <!--begin::Table body-->
                                                            <tbody>
                                                                <tr>
                                                                    <td>
                                                                        <span data-kt-element="bullet"
                                                                            class="bullet bullet-vertical d-flex align-items-center h-40px bg-success"></span>
                                                                    </td>
                                                                    <td class="ps-0">
                                                                        <div
                                                                            class="form-check form-check-custom form-check-success form-check-solid">
                                                                            <input class="form-check-input"
                                                                                type="checkbox" value=""
                                                                                checked="checked"
                                                                                data-kt-element="checkbox" />
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <a href="#"
                                                                            class="text-gray-800 text-hover-primary fw-bold fs-6">Book
                                                                            p. 77-85, read & complete tasks 1-6 on p.
                                                                            85</a>
                                                                        <span
                                                                            class="text-gray-500 fw-bold fs-7 d-block">Physics</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <span data-kt-element="status"
                                                                            class="badge badge-light-success">Done</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <!--begin::Icon-->
                                                                        <div
                                                                            class="flex-shrink-0 d-flex justify-content-end">
                                                                            <!--begin::Print-->
                                                                            <a href="#"
                                                                                class="btn btn-icon btn-color-muted btn-bg-light btn-active-color-primary btn-sm me-3">
                                                                                <i class="ki-duotone ki-printer fs-3">
                                                                                    <span class="path1"></span>
                                                                                    <span class="path2"></span>
                                                                                    <span class="path3"></span>
                                                                                    <span class="path4"></span>
                                                                                    <span class="path5"></span>
                                                                                </i>
                                                                            </a>
                                                                            <!--end::Print-->
                                                                            <!--begin::Chat-->
                                                                            <a href="#"
                                                                                class="btn btn-icon btn-color-muted btn-bg-light btn-active-color-primary btn-sm me-3">
                                                                                <i class="ki-duotone ki-sms fs-3">
                                                                                    <span class="path1"></span>
                                                                                    <span class="path2"></span>
                                                                                </i>
                                                                            </a>
                                                                            <!--end::Chat-->
                                                                            <!--begin::Attach-->
                                                                            <a href="#"
                                                                                class="btn btn-icon btn-color-muted btn-bg-light btn-active-color-primary btn-sm">
                                                                                <i
                                                                                    class="ki-duotone ki-paper-clip fs-3"></i>
                                                                            </a>
                                                                            <!--end::Attach-->
                                                                        </div>
                                                                        <!--end::Icon-->
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        <span data-kt-element="bullet"
                                                                            class="bullet bullet-vertical d-flex align-items-center h-40px bg-primary"></span>
                                                                    </td>
                                                                    <td class="ps-0">
                                                                        <div
                                                                            class="form-check form-check-custom form-check-solid">
                                                                            <input class="form-check-input"
                                                                                type="checkbox" value=""
                                                                                data-kt-element="checkbox" />
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <a href="#"
                                                                            class="text-gray-800 text-hover-primary fw-bold fs-6">Workbook
                                                                            p. 17, tasks 1-6</a>
                                                                        <span
                                                                            class="text-gray-500 fw-bold fs-7 d-block">Mathematics</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <span data-kt-element="status"
                                                                            class="badge badge-light-primary">In
                                                                            Process</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <!--begin::Icon-->
                                                                        <div
                                                                            class="flex-shrink-0 d-flex justify-content-end">
                                                                            <!--begin::Print-->
                                                                            <a href="#"
                                                                                class="btn btn-icon btn-color-muted btn-bg-light btn-active-color-primary btn-sm me-3">
                                                                                <i class="ki-duotone ki-printer fs-3">
                                                                                    <span class="path1"></span>
                                                                                    <span class="path2"></span>
                                                                                    <span class="path3"></span>
                                                                                    <span class="path4"></span>
                                                                                    <span class="path5"></span>
                                                                                </i>
                                                                            </a>
                                                                            <!--end::Print-->
                                                                            <!--begin::Chat-->
                                                                            <a href="#"
                                                                                class="btn btn-icon btn-color-muted btn-bg-light btn-active-color-primary btn-sm me-3">
                                                                                <i class="ki-duotone ki-sms fs-3">
                                                                                    <span class="path1"></span>
                                                                                    <span class="path2"></span>
                                                                                </i>
                                                                            </a>
                                                                            <!--end::Chat-->
                                                                            <!--begin::Attach-->
                                                                            <a href="#"
                                                                                class="btn btn-icon btn-color-muted btn-bg-light btn-active-color-primary btn-sm">
                                                                                <i
                                                                                    class="ki-duotone ki-paper-clip fs-3"></i>
                                                                            </a>
                                                                            <!--end::Attach-->
                                                                        </div>
                                                                        <!--end::Icon-->
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        <span data-kt-element="bullet"
                                                                            class="bullet bullet-vertical d-flex align-items-center h-40px bg-success"></span>
                                                                    </td>
                                                                    <td class="ps-0">
                                                                        <div
                                                                            class="form-check form-check-custom form-check-success form-check-solid">
                                                                            <input class="form-check-input"
                                                                                type="checkbox" value=""
                                                                                checked="checked"
                                                                                data-kt-element="checkbox" />
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <a href="#"
                                                                            class="text-gray-800 text-hover-primary fw-bold fs-6">Learn
                                                                            paragraph p. 99, Exercise 1,2,3Scoping &
                                                                            Estimations</a>
                                                                        <span
                                                                            class="text-gray-500 fw-bold fs-7 d-block">Chemistry</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <span data-kt-element="status"
                                                                            class="badge badge-light-success">Done</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <!--begin::Icon-->
                                                                        <div
                                                                            class="flex-shrink-0 d-flex justify-content-end">
                                                                            <!--begin::Print-->
                                                                            <a href="#"
                                                                                class="btn btn-icon btn-color-muted btn-bg-light btn-active-color-primary btn-sm me-3">
                                                                                <i class="ki-duotone ki-printer fs-3">
                                                                                    <span class="path1"></span>
                                                                                    <span class="path2"></span>
                                                                                    <span class="path3"></span>
                                                                                    <span class="path4"></span>
                                                                                    <span class="path5"></span>
                                                                                </i>
                                                                            </a>
                                                                            <!--end::Print-->
                                                                            <!--begin::Chat-->
                                                                            <a href="#"
                                                                                class="btn btn-icon btn-color-muted btn-bg-light btn-active-color-primary btn-sm me-3">
                                                                                <i class="ki-duotone ki-sms fs-3">
                                                                                    <span class="path1"></span>
                                                                                    <span class="path2"></span>
                                                                                </i>
                                                                            </a>
                                                                            <!--end::Chat-->
                                                                            <!--begin::Attach-->
                                                                            <a href="#"
                                                                                class="btn btn-icon btn-color-muted btn-bg-light btn-active-color-primary btn-sm">
                                                                                <i
                                                                                    class="ki-duotone ki-paper-clip fs-3"></i>
                                                                            </a>
                                                                            <!--end::Attach-->
                                                                        </div>
                                                                        <!--end::Icon-->
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        <span data-kt-element="bullet"
                                                                            class="bullet bullet-vertical d-flex align-items-center h-40px bg-primary"></span>
                                                                    </td>
                                                                    <td class="ps-0">
                                                                        <div
                                                                            class="form-check form-check-custom form-check-solid">
                                                                            <input class="form-check-input"
                                                                                type="checkbox" value=""
                                                                                data-kt-element="checkbox" />
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <a href="#"
                                                                            class="text-gray-800 text-hover-primary fw-bold fs-6">Write
                                                                            essay 1000 words “WW2 results”</a>
                                                                        <span
                                                                            class="text-gray-500 fw-bold fs-7 d-block">History</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <span data-kt-element="status"
                                                                            class="badge badge-light-primary">In
                                                                            Process</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <!--begin::Icon-->
                                                                        <div
                                                                            class="flex-shrink-0 d-flex justify-content-end">
                                                                            <!--begin::Print-->
                                                                            <a href="#"
                                                                                class="btn btn-icon btn-color-muted btn-bg-light btn-active-color-primary btn-sm me-3">
                                                                                <i class="ki-duotone ki-printer fs-3">
                                                                                    <span class="path1"></span>
                                                                                    <span class="path2"></span>
                                                                                    <span class="path3"></span>
                                                                                    <span class="path4"></span>
                                                                                    <span class="path5"></span>
                                                                                </i>
                                                                            </a>
                                                                            <!--end::Print-->
                                                                            <!--begin::Chat-->
                                                                            <a href="#"
                                                                                class="btn btn-icon btn-color-muted btn-bg-light btn-active-color-primary btn-sm me-3">
                                                                                <i class="ki-duotone ki-sms fs-3">
                                                                                    <span class="path1"></span>
                                                                                    <span class="path2"></span>
                                                                                </i>
                                                                            </a>
                                                                            <!--end::Chat-->
                                                                            <!--begin::Attach-->
                                                                            <a href="#"
                                                                                class="btn btn-icon btn-color-muted btn-bg-light btn-active-color-primary btn-sm">
                                                                                <i
                                                                                    class="ki-duotone ki-paper-clip fs-3"></i>
                                                                            </a>
                                                                            <!--end::Attach-->
                                                                        </div>
                                                                        <!--end::Icon-->
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        <span data-kt-element="bullet"
                                                                            class="bullet bullet-vertical d-flex align-items-center h-40px bg-primary"></span>
                                                                    </td>
                                                                    <td class="ps-0">
                                                                        <div
                                                                            class="form-check form-check-custom form-check-solid">
                                                                            <input class="form-check-input"
                                                                                type="checkbox" value=""
                                                                                data-kt-element="checkbox" />
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <a href="#"
                                                                            class="text-gray-800 text-hover-primary fw-bold fs-6">Internal
                                                                            conflicts in Philip Larkin poems, read p
                                                                            380-515</a>
                                                                        <span
                                                                            class="text-gray-500 fw-bold fs-7 d-block">English
                                                                            Language</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <span data-kt-element="status"
                                                                            class="badge badge-light-primary">In
                                                                            Process</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <!--begin::Icon-->
                                                                        <div
                                                                            class="flex-shrink-0 d-flex justify-content-end">
                                                                            <!--begin::Print-->
                                                                            <a href="#"
                                                                                class="btn btn-icon btn-color-muted btn-bg-light btn-active-color-primary btn-sm me-3">
                                                                                <i class="ki-duotone ki-printer fs-3">
                                                                                    <span class="path1"></span>
                                                                                    <span class="path2"></span>
                                                                                    <span class="path3"></span>
                                                                                    <span class="path4"></span>
                                                                                    <span class="path5"></span>
                                                                                </i>
                                                                            </a>
                                                                            <!--end::Print-->
                                                                            <!--begin::Chat-->
                                                                            <a href="#"
                                                                                class="btn btn-icon btn-color-muted btn-bg-light btn-active-color-primary btn-sm me-3">
                                                                                <i class="ki-duotone ki-sms fs-3">
                                                                                    <span class="path1"></span>
                                                                                    <span class="path2"></span>
                                                                                </i>
                                                                            </a>
                                                                            <!--end::Chat-->
                                                                            <!--begin::Attach-->
                                                                            <a href="#"
                                                                                class="btn btn-icon btn-color-muted btn-bg-light btn-active-color-primary btn-sm">
                                                                                <i
                                                                                    class="ki-duotone ki-paper-clip fs-3"></i>
                                                                            </a>
                                                                            <!--end::Attach-->
                                                                        </div>
                                                                        <!--end::Icon-->
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                            <!--end::Table body-->
                                                        </table>
                                                    </div>
                                                    <!--end::Table-->
                                                </div>
                                                <!--end::Tap pane-->
                                                <!--begin::Tap pane-->
                                                <div class="tab-pane fade" id="kt_timeline_widget_2_tab_2">
                                                    <!--begin::Table container-->
                                                    <div class="table-responsive">
                                                        <!--begin::Table-->
                                                        <table class="table align-middle gs-0 gy-4">
                                                            <!--begin::Table head-->
                                                            <thead>
                                                                <tr>
                                                                    <th class="p-0 w-10px"></th>
                                                                    <th class="p-0 w-25px"></th>
                                                                    <th class="p-0 min-w-400px"></th>
                                                                    <th class="p-0 min-w-100px"></th>
                                                                    <th class="p-0 min-w-125px"></th>
                                                                </tr>
                                                            </thead>
                                                            <!--end::Table head-->
                                                            <!--begin::Table body-->
                                                            <tbody>
                                                                <tr>
                                                                    <td>
                                                                        <span data-kt-element="bullet"
                                                                            class="bullet bullet-vertical d-flex align-items-center h-40px bg-success"></span>
                                                                    </td>
                                                                    <td class="ps-0">
                                                                        <div
                                                                            class="form-check form-check-custom form-check-success form-check-solid">
                                                                            <input class="form-check-input"
                                                                                type="checkbox" value=""
                                                                                checked="checked"
                                                                                data-kt-element="checkbox" />
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <a href="#"
                                                                            class="text-gray-800 text-hover-primary fw-bold fs-6">Book
                                                                            p. 77-85, read & complete tasks 1-6 on p.
                                                                            85</a>
                                                                        <span
                                                                            class="text-gray-500 fw-bold fs-7 d-block">Physics</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <span data-kt-element="status"
                                                                            class="badge badge-light-success">Done</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <!--begin::Icon-->
                                                                        <div
                                                                            class="flex-shrink-0 d-flex justify-content-end">
                                                                            <!--begin::Print-->
                                                                            <a href="#"
                                                                                class="btn btn-icon btn-color-muted btn-bg-light btn-active-color-primary btn-sm me-3">
                                                                                <i class="ki-duotone ki-printer fs-3">
                                                                                    <span class="path1"></span>
                                                                                    <span class="path2"></span>
                                                                                    <span class="path3"></span>
                                                                                    <span class="path4"></span>
                                                                                    <span class="path5"></span>
                                                                                </i>
                                                                            </a>
                                                                            <!--end::Print-->
                                                                            <!--begin::Chat-->
                                                                            <a href="#"
                                                                                class="btn btn-icon btn-color-muted btn-bg-light btn-active-color-primary btn-sm me-3">
                                                                                <i class="ki-duotone ki-sms fs-3">
                                                                                    <span class="path1"></span>
                                                                                    <span class="path2"></span>
                                                                                </i>
                                                                            </a>
                                                                            <!--end::Chat-->
                                                                            <!--begin::Attach-->
                                                                            <a href="#"
                                                                                class="btn btn-icon btn-color-muted btn-bg-light btn-active-color-primary btn-sm">
                                                                                <i
                                                                                    class="ki-duotone ki-paper-clip fs-3"></i>
                                                                            </a>
                                                                            <!--end::Attach-->
                                                                        </div>
                                                                        <!--end::Icon-->
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        <span data-kt-element="bullet"
                                                                            class="bullet bullet-vertical d-flex align-items-center h-40px bg-primary"></span>
                                                                    </td>
                                                                    <td class="ps-0">
                                                                        <div
                                                                            class="form-check form-check-custom form-check-solid">
                                                                            <input class="form-check-input"
                                                                                type="checkbox" value=""
                                                                                data-kt-element="checkbox" />
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <a href="#"
                                                                            class="text-gray-800 text-hover-primary fw-bold fs-6">Workbook
                                                                            p. 17, tasks 1-6</a>
                                                                        <span
                                                                            class="text-gray-500 fw-bold fs-7 d-block">Mathematics</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <span data-kt-element="status"
                                                                            class="badge badge-light-primary">In
                                                                            Process</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <!--begin::Icon-->
                                                                        <div
                                                                            class="flex-shrink-0 d-flex justify-content-end">
                                                                            <!--begin::Print-->
                                                                            <a href="#"
                                                                                class="btn btn-icon btn-color-muted btn-bg-light btn-active-color-primary btn-sm me-3">
                                                                                <i class="ki-duotone ki-printer fs-3">
                                                                                    <span class="path1"></span>
                                                                                    <span class="path2"></span>
                                                                                    <span class="path3"></span>
                                                                                    <span class="path4"></span>
                                                                                    <span class="path5"></span>
                                                                                </i>
                                                                            </a>
                                                                            <!--end::Print-->
                                                                            <!--begin::Chat-->
                                                                            <a href="#"
                                                                                class="btn btn-icon btn-color-muted btn-bg-light btn-active-color-primary btn-sm me-3">
                                                                                <i class="ki-duotone ki-sms fs-3">
                                                                                    <span class="path1"></span>
                                                                                    <span class="path2"></span>
                                                                                </i>
                                                                            </a>
                                                                            <!--end::Chat-->
                                                                            <!--begin::Attach-->
                                                                            <a href="#"
                                                                                class="btn btn-icon btn-color-muted btn-bg-light btn-active-color-primary btn-sm">
                                                                                <i
                                                                                    class="ki-duotone ki-paper-clip fs-3"></i>
                                                                            </a>
                                                                            <!--end::Attach-->
                                                                        </div>
                                                                        <!--end::Icon-->
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        <span data-kt-element="bullet"
                                                                            class="bullet bullet-vertical d-flex align-items-center h-40px bg-success"></span>
                                                                    </td>
                                                                    <td class="ps-0">
                                                                        <div
                                                                            class="form-check form-check-custom form-check-success form-check-solid">
                                                                            <input class="form-check-input"
                                                                                type="checkbox" value=""
                                                                                checked="checked"
                                                                                data-kt-element="checkbox" />
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <a href="#"
                                                                            class="text-gray-800 text-hover-primary fw-bold fs-6">Learn
                                                                            paragraph p. 99, Exercise 1,2,3Scoping &
                                                                            Estimations</a>
                                                                        <span
                                                                            class="text-gray-500 fw-bold fs-7 d-block">Chemistry</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <span data-kt-element="status"
                                                                            class="badge badge-light-success">Done</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <!--begin::Icon-->
                                                                        <div
                                                                            class="flex-shrink-0 d-flex justify-content-end">
                                                                            <!--begin::Print-->
                                                                            <a href="#"
                                                                                class="btn btn-icon btn-color-muted btn-bg-light btn-active-color-primary btn-sm me-3">
                                                                                <i class="ki-duotone ki-printer fs-3">
                                                                                    <span class="path1"></span>
                                                                                    <span class="path2"></span>
                                                                                    <span class="path3"></span>
                                                                                    <span class="path4"></span>
                                                                                    <span class="path5"></span>
                                                                                </i>
                                                                            </a>
                                                                            <!--end::Print-->
                                                                            <!--begin::Chat-->
                                                                            <a href="#"
                                                                                class="btn btn-icon btn-color-muted btn-bg-light btn-active-color-primary btn-sm me-3">
                                                                                <i class="ki-duotone ki-sms fs-3">
                                                                                    <span class="path1"></span>
                                                                                    <span class="path2"></span>
                                                                                </i>
                                                                            </a>
                                                                            <!--end::Chat-->
                                                                            <!--begin::Attach-->
                                                                            <a href="#"
                                                                                class="btn btn-icon btn-color-muted btn-bg-light btn-active-color-primary btn-sm">
                                                                                <i
                                                                                    class="ki-duotone ki-paper-clip fs-3"></i>
                                                                            </a>
                                                                            <!--end::Attach-->
                                                                        </div>
                                                                        <!--end::Icon-->
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        <span data-kt-element="bullet"
                                                                            class="bullet bullet-vertical d-flex align-items-center h-40px bg-primary"></span>
                                                                    </td>
                                                                    <td class="ps-0">
                                                                        <div
                                                                            class="form-check form-check-custom form-check-solid">
                                                                            <input class="form-check-input"
                                                                                type="checkbox" value=""
                                                                                data-kt-element="checkbox" />
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <a href="#"
                                                                            class="text-gray-800 text-hover-primary fw-bold fs-6">Write
                                                                            essay 1000 words “WW2 results”</a>
                                                                        <span
                                                                            class="text-gray-500 fw-bold fs-7 d-block">History</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <span data-kt-element="status"
                                                                            class="badge badge-light-primary">In
                                                                            Process</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <!--begin::Icon-->
                                                                        <div
                                                                            class="flex-shrink-0 d-flex justify-content-end">
                                                                            <!--begin::Print-->
                                                                            <a href="#"
                                                                                class="btn btn-icon btn-color-muted btn-bg-light btn-active-color-primary btn-sm me-3">
                                                                                <i class="ki-duotone ki-printer fs-3">
                                                                                    <span class="path1"></span>
                                                                                    <span class="path2"></span>
                                                                                    <span class="path3"></span>
                                                                                    <span class="path4"></span>
                                                                                    <span class="path5"></span>
                                                                                </i>
                                                                            </a>
                                                                            <!--end::Print-->
                                                                            <!--begin::Chat-->
                                                                            <a href="#"
                                                                                class="btn btn-icon btn-color-muted btn-bg-light btn-active-color-primary btn-sm me-3">
                                                                                <i class="ki-duotone ki-sms fs-3">
                                                                                    <span class="path1"></span>
                                                                                    <span class="path2"></span>
                                                                                </i>
                                                                            </a>
                                                                            <!--end::Chat-->
                                                                            <!--begin::Attach-->
                                                                            <a href="#"
                                                                                class="btn btn-icon btn-color-muted btn-bg-light btn-active-color-primary btn-sm">
                                                                                <i
                                                                                    class="ki-duotone ki-paper-clip fs-3"></i>
                                                                            </a>
                                                                            <!--end::Attach-->
                                                                        </div>
                                                                        <!--end::Icon-->
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                            <!--end::Table body-->
                                                        </table>
                                                    </div>
                                                    <!--end::Table-->
                                                </div>
                                                <!--end::Tap pane-->
                                                <!--begin::Tap pane-->
                                                <div class="tab-pane fade" id="kt_timeline_widget_2_tab_3">
                                                    <!--begin::Table container-->
                                                    <div class="table-responsive">
                                                        <!--begin::Table-->
                                                        <table class="table align-middle gs-0 gy-4">
                                                            <!--begin::Table head-->
                                                            <thead>
                                                                <tr>
                                                                    <th class="p-0 w-10px"></th>
                                                                    <th class="p-0 w-25px"></th>
                                                                    <th class="p-0 min-w-400px"></th>
                                                                    <th class="p-0 min-w-100px"></th>
                                                                    <th class="p-0 min-w-125px"></th>
                                                                </tr>
                                                            </thead>
                                                            <!--end::Table head-->
                                                            <!--begin::Table body-->
                                                            <tbody>
                                                                <tr>
                                                                    <td>
                                                                        <span data-kt-element="bullet"
                                                                            class="bullet bullet-vertical d-flex align-items-center h-40px bg-primary"></span>
                                                                    </td>
                                                                    <td class="ps-0">
                                                                        <div
                                                                            class="form-check form-check-custom form-check-solid">
                                                                            <input class="form-check-input"
                                                                                type="checkbox" value=""
                                                                                data-kt-element="checkbox" />
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <a href="#"
                                                                            class="text-gray-800 text-hover-primary fw-bold fs-6">Workbook
                                                                            p. 17, tasks 1-6</a>
                                                                        <span
                                                                            class="text-gray-500 fw-bold fs-7 d-block">Mathematics</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <span data-kt-element="status"
                                                                            class="badge badge-light-primary">In
                                                                            Process</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <!--begin::Icon-->
                                                                        <div
                                                                            class="flex-shrink-0 d-flex justify-content-end">
                                                                            <!--begin::Print-->
                                                                            <a href="#"
                                                                                class="btn btn-icon btn-color-muted btn-bg-light btn-active-color-primary btn-sm me-3">
                                                                                <i class="ki-duotone ki-printer fs-3">
                                                                                    <span class="path1"></span>
                                                                                    <span class="path2"></span>
                                                                                    <span class="path3"></span>
                                                                                    <span class="path4"></span>
                                                                                    <span class="path5"></span>
                                                                                </i>
                                                                            </a>
                                                                            <!--end::Print-->
                                                                            <!--begin::Chat-->
                                                                            <a href="#"
                                                                                class="btn btn-icon btn-color-muted btn-bg-light btn-active-color-primary btn-sm me-3">
                                                                                <i class="ki-duotone ki-sms fs-3">
                                                                                    <span class="path1"></span>
                                                                                    <span class="path2"></span>
                                                                                </i>
                                                                            </a>
                                                                            <!--end::Chat-->
                                                                            <!--begin::Attach-->
                                                                            <a href="#"
                                                                                class="btn btn-icon btn-color-muted btn-bg-light btn-active-color-primary btn-sm">
                                                                                <i
                                                                                    class="ki-duotone ki-paper-clip fs-3"></i>
                                                                            </a>
                                                                            <!--end::Attach-->
                                                                        </div>
                                                                        <!--end::Icon-->
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        <span data-kt-element="bullet"
                                                                            class="bullet bullet-vertical d-flex align-items-center h-40px bg-success"></span>
                                                                    </td>
                                                                    <td class="ps-0">
                                                                        <div
                                                                            class="form-check form-check-custom form-check-success form-check-solid">
                                                                            <input class="form-check-input"
                                                                                type="checkbox" value=""
                                                                                checked="checked"
                                                                                data-kt-element="checkbox" />
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <a href="#"
                                                                            class="text-gray-800 text-hover-primary fw-bold fs-6">Learn
                                                                            paragraph p. 99, Exercise 1,2,3Scoping &
                                                                            Estimations</a>
                                                                        <span
                                                                            class="text-gray-500 fw-bold fs-7 d-block">Chemistry</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <span data-kt-element="status"
                                                                            class="badge badge-light-success">Done</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <!--begin::Icon-->
                                                                        <div
                                                                            class="flex-shrink-0 d-flex justify-content-end">
                                                                            <!--begin::Print-->
                                                                            <a href="#"
                                                                                class="btn btn-icon btn-color-muted btn-bg-light btn-active-color-primary btn-sm me-3">
                                                                                <i class="ki-duotone ki-printer fs-3">
                                                                                    <span class="path1"></span>
                                                                                    <span class="path2"></span>
                                                                                    <span class="path3"></span>
                                                                                    <span class="path4"></span>
                                                                                    <span class="path5"></span>
                                                                                </i>
                                                                            </a>
                                                                            <!--end::Print-->
                                                                            <!--begin::Chat-->
                                                                            <a href="#"
                                                                                class="btn btn-icon btn-color-muted btn-bg-light btn-active-color-primary btn-sm me-3">
                                                                                <i class="ki-duotone ki-sms fs-3">
                                                                                    <span class="path1"></span>
                                                                                    <span class="path2"></span>
                                                                                </i>
                                                                            </a>
                                                                            <!--end::Chat-->
                                                                            <!--begin::Attach-->
                                                                            <a href="#"
                                                                                class="btn btn-icon btn-color-muted btn-bg-light btn-active-color-primary btn-sm">
                                                                                <i
                                                                                    class="ki-duotone ki-paper-clip fs-3"></i>
                                                                            </a>
                                                                            <!--end::Attach-->
                                                                        </div>
                                                                        <!--end::Icon-->
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        <span data-kt-element="bullet"
                                                                            class="bullet bullet-vertical d-flex align-items-center h-40px bg-primary"></span>
                                                                    </td>
                                                                    <td class="ps-0">
                                                                        <div
                                                                            class="form-check form-check-custom form-check-solid">
                                                                            <input class="form-check-input"
                                                                                type="checkbox" value=""
                                                                                data-kt-element="checkbox" />
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <a href="#"
                                                                            class="text-gray-800 text-hover-primary fw-bold fs-6">Write
                                                                            essay 1000 words “WW2 results”</a>
                                                                        <span
                                                                            class="text-gray-500 fw-bold fs-7 d-block">History</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <span data-kt-element="status"
                                                                            class="badge badge-light-primary">In
                                                                            Process</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <!--begin::Icon-->
                                                                        <div
                                                                            class="flex-shrink-0 d-flex justify-content-end">
                                                                            <!--begin::Print-->
                                                                            <a href="#"
                                                                                class="btn btn-icon btn-color-muted btn-bg-light btn-active-color-primary btn-sm me-3">
                                                                                <i class="ki-duotone ki-printer fs-3">
                                                                                    <span class="path1"></span>
                                                                                    <span class="path2"></span>
                                                                                    <span class="path3"></span>
                                                                                    <span class="path4"></span>
                                                                                    <span class="path5"></span>
                                                                                </i>
                                                                            </a>
                                                                            <!--end::Print-->
                                                                            <!--begin::Chat-->
                                                                            <a href="#"
                                                                                class="btn btn-icon btn-color-muted btn-bg-light btn-active-color-primary btn-sm me-3">
                                                                                <i class="ki-duotone ki-sms fs-3">
                                                                                    <span class="path1"></span>
                                                                                    <span class="path2"></span>
                                                                                </i>
                                                                            </a>
                                                                            <!--end::Chat-->
                                                                            <!--begin::Attach-->
                                                                            <a href="#"
                                                                                class="btn btn-icon btn-color-muted btn-bg-light btn-active-color-primary btn-sm">
                                                                                <i
                                                                                    class="ki-duotone ki-paper-clip fs-3"></i>
                                                                            </a>
                                                                            <!--end::Attach-->
                                                                        </div>
                                                                        <!--end::Icon-->
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>
                                                                        <span data-kt-element="bullet"
                                                                            class="bullet bullet-vertical d-flex align-items-center h-40px bg-primary"></span>
                                                                    </td>
                                                                    <td class="ps-0">
                                                                        <div
                                                                            class="form-check form-check-custom form-check-solid">
                                                                            <input class="form-check-input"
                                                                                type="checkbox" value=""
                                                                                data-kt-element="checkbox" />
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <a href="#"
                                                                            class="text-gray-800 text-hover-primary fw-bold fs-6">Internal
                                                                            conflicts in Philip Larkin poems, read p
                                                                            380-515</a>
                                                                        <span
                                                                            class="text-gray-500 fw-bold fs-7 d-block">English
                                                                            Language</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <span data-kt-element="status"
                                                                            class="badge badge-light-primary">In
                                                                            Process</span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        <!--begin::Icon-->
                                                                        <div
                                                                            class="flex-shrink-0 d-flex justify-content-end">
                                                                            <!--begin::Print-->
                                                                            <a href="#"
                                                                                class="btn btn-icon btn-color-muted btn-bg-light btn-active-color-primary btn-sm me-3">
                                                                                <i class="ki-duotone ki-printer fs-3">
                                                                                    <span class="path1"></span>
                                                                                    <span class="path2"></span>
                                                                                    <span class="path3"></span>
                                                                                    <span class="path4"></span>
                                                                                    <span class="path5"></span>
                                                                                </i>
                                                                            </a>
                                                                            <!--end::Print-->
                                                                            <!--begin::Chat-->
                                                                            <a href="#"
                                                                                class="btn btn-icon btn-color-muted btn-bg-light btn-active-color-primary btn-sm me-3">
                                                                                <i class="ki-duotone ki-sms fs-3">
                                                                                    <span class="path1"></span>
                                                                                    <span class="path2"></span>
                                                                                </i>
                                                                            </a>
                                                                            <!--end::Chat-->
                                                                            <!--begin::Attach-->
                                                                            <a href="#"
                                                                                class="btn btn-icon btn-color-muted btn-bg-light btn-active-color-primary btn-sm">
                                                                                <i
                                                                                    class="ki-duotone ki-paper-clip fs-3"></i>
                                                                            </a>
                                                                            <!--end::Attach-->
                                                                        </div>
                                                                        <!--end::Icon-->
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                            <!--end::Table body-->
                                                        </table>
                                                    </div>
                                                    <!--end::Table-->
                                                </div>
                                                <!--end::Tap pane-->
                                            </div>
                                            <!--end::Tab Content-->
                                        </div>
                                        <!--end::Body-->
                                    </div>
                                    <!--end::Tables Widget 2-->
                                </div>
                                <!--end::Col-->
                                <!--begin::Col-->
                                <div class="col-xxl-4">
                                    <!--begin::List widget 20-->
                                    <div class="card h-xl-100">
                                        <!--begin::Header-->
                                        <div class="pt-5 border-0 card-header">
                                            <h3 class="card-title align-items-start flex-column">
                                                <span class="text-gray-900 card-label fw-bold">Recommended for
                                                    you</span>
                                                <span class="mt-1 text-muted fw-semibold fs-7">8k social
                                                    visitors</span>
                                            </h3>
                                            <!--begin::Toolbar-->
                                            <div class="card-toolbar">
                                                <a href="#" class="btn btn-sm btn-light">All Courses</a>
                                            </div>
                                            <!--end::Toolbar-->
                                        </div>
                                        <!--end::Header-->
                                        <!--begin::Body-->
                                        <div class="pt-6 card-body">
                                            <!--begin::Item-->
                                            <div class="d-flex flex-stack">
                                                <!--begin::Symbol-->
                                                <div class="symbol symbol-40px me-4">
                                                    <div
                                                        class="symbol-label fs-2 fw-semibold bg-danger text-inverse-danger">
                                                        M</div>
                                                </div>
                                                <!--end::Symbol-->
                                                <!--begin::Section-->
                                                <div class="flex-wrap d-flex align-items-center flex-row-fluid">
                                                    <!--begin:Author-->
                                                    <div class="flex-grow-1 me-2">
                                                        <a href="pages/user-profile/overview.html"
                                                            class="text-gray-800 text-hover-primary fs-6 fw-bold">UI/UX
                                                            Design</a>
                                                        <span class="text-muted fw-semibold d-block fs-7">40+
                                                            Courses</span>
                                                    </div>
                                                    <!--end:Author-->
                                                    <!--begin::Actions-->
                                                    <a href="#"
                                                        class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px">
                                                        <i class="ki-duotone ki-arrow-right fs-2">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                        </i>
                                                    </a>
                                                    <!--begin::Actions-->
                                                </div>
                                                <!--end::Section-->
                                            </div>
                                            <!--end::Item-->
                                            <!--begin::Separator-->
                                            <div class="my-4 separator separator-dashed"></div>
                                            <!--end::Separator-->
                                            <!--begin::Item-->
                                            <div class="d-flex flex-stack">
                                                <!--begin::Symbol-->
                                                <div class="symbol symbol-40px me-4">
                                                    <div
                                                        class="symbol-label fs-2 fw-semibold bg-success text-inverse-success">
                                                        Q</div>
                                                </div>
                                                <!--end::Symbol-->
                                                <!--begin::Section-->
                                                <div class="flex-wrap d-flex align-items-center flex-row-fluid">
                                                    <!--begin:Author-->
                                                    <div class="flex-grow-1 me-2">
                                                        <a href="pages/user-profile/overview.html"
                                                            class="text-gray-800 text-hover-primary fs-6 fw-bold">QA
                                                            Analysis</a>
                                                        <span class="text-muted fw-semibold d-block fs-7">18
                                                            Courses</span>
                                                    </div>
                                                    <!--end:Author-->
                                                    <!--begin::Actions-->
                                                    <a href="#"
                                                        class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px">
                                                        <i class="ki-duotone ki-arrow-right fs-2">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                        </i>
                                                    </a>
                                                    <!--begin::Actions-->
                                                </div>
                                                <!--end::Section-->
                                            </div>
                                            <!--end::Item-->
                                            <!--begin::Separator-->
                                            <div class="my-4 separator separator-dashed"></div>
                                            <!--end::Separator-->
                                            <!--begin::Item-->
                                            <div class="d-flex flex-stack">
                                                <!--begin::Symbol-->
                                                <div class="symbol symbol-40px me-4">
                                                    <div
                                                        class="symbol-label fs-2 fw-semibold bg-info text-inverse-info">
                                                        W</div>
                                                </div>
                                                <!--end::Symbol-->
                                                <!--begin::Section-->
                                                <div class="flex-wrap d-flex align-items-center flex-row-fluid">
                                                    <!--begin:Author-->
                                                    <div class="flex-grow-1 me-2">
                                                        <a href="pages/user-profile/overview.html"
                                                            class="text-gray-800 text-hover-primary fs-6 fw-bold">Web
                                                            Development</a>
                                                        <span class="text-muted fw-semibold d-block fs-7">120+
                                                            Courses</span>
                                                    </div>
                                                    <!--end:Author-->
                                                    <!--begin::Actions-->
                                                    <a href="#"
                                                        class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px">
                                                        <i class="ki-duotone ki-arrow-right fs-2">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                        </i>
                                                    </a>
                                                    <!--begin::Actions-->
                                                </div>
                                                <!--end::Section-->
                                            </div>
                                            <!--end::Item-->
                                            <!--begin::Separator-->
                                            <div class="my-4 separator separator-dashed"></div>
                                            <!--end::Separator-->
                                            <!--begin::Item-->
                                            <div class="d-flex flex-stack">
                                                <!--begin::Symbol-->
                                                <div class="symbol symbol-40px me-4">
                                                    <div
                                                        class="symbol-label fs-2 fw-semibold bg-primary text-inverse-primary">
                                                        M</div>
                                                </div>
                                                <!--end::Symbol-->
                                                <!--begin::Section-->
                                                <div class="flex-wrap d-flex align-items-center flex-row-fluid">
                                                    <!--begin:Author-->
                                                    <div class="flex-grow-1 me-2">
                                                        <a href="pages/user-profile/overview.html"
                                                            class="text-gray-800 text-hover-primary fs-6 fw-bold">Marketing</a>
                                                        <span class="text-muted fw-semibold d-block fs-7">50+
                                                            Courses.</span>
                                                    </div>
                                                    <!--end:Author-->
                                                    <!--begin::Actions-->
                                                    <a href="#"
                                                        class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px">
                                                        <i class="ki-duotone ki-arrow-right fs-2">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                        </i>
                                                    </a>
                                                    <!--begin::Actions-->
                                                </div>
                                                <!--end::Section-->
                                            </div>
                                            <!--end::Item-->
                                            <!--begin::Separator-->
                                            <div class="my-4 separator separator-dashed"></div>
                                            <!--end::Separator-->
                                            <!--begin::Item-->
                                            <div class="d-flex flex-stack">
                                                <!--begin::Symbol-->
                                                <div class="symbol symbol-40px me-4">
                                                    <div
                                                        class="symbol-label fs-2 fw-semibold bg-warning text-inverse-warning">
                                                        P</div>
                                                </div>
                                                <!--end::Symbol-->
                                                <!--begin::Section-->
                                                <div class="flex-wrap d-flex align-items-center flex-row-fluid">
                                                    <!--begin:Author-->
                                                    <div class="flex-grow-1 me-2">
                                                        <a href="pages/user-profile/overview.html"
                                                            class="text-gray-800 text-hover-primary fs-6 fw-bold">Philosophy</a>
                                                        <span class="text-muted fw-semibold d-block fs-7">24+
                                                            Courses</span>
                                                    </div>
                                                    <!--end:Author-->
                                                    <!--begin::Actions-->
                                                    <a href="#"
                                                        class="btn btn-sm btn-icon btn-bg-light btn-active-color-primary w-30px h-30px">
                                                        <i class="ki-duotone ki-arrow-right fs-2">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                        </i>
                                                    </a>
                                                    <!--begin::Actions-->
                                                </div>
                                                <!--end::Section-->
                                            </div>
                                            <!--end::Item-->
                                        </div>
                                        <!--end::Body-->
                                    </div>
                                    <!--end::List widget 20-->
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Row-->
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
                            <span class="text-muted fw-semibold me-1">2024&copy;</span>
                            <a href="https://keenthemes.com" target="_blank"
                                class="text-gray-800 text-hover-primary">Keenthemes</a>
                        </div>
                        <!--end::Copyright-->
                        <!--begin::Menu-->
                        <ul class="order-1 menu menu-gray-600 menu-hover-primary fw-semibold">
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
                        </ul>
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
    <!--begin::Activities drawer-->
    <div id="kt_activities" class="bg-body" data-kt-drawer="true" data-kt-drawer-name="activities"
        data-kt-drawer-activate="true" data-kt-drawer-overlay="true"
        data-kt-drawer-width="{default:'300px', 'lg': '900px'}" data-kt-drawer-direction="end"
        data-kt-drawer-toggle="#kt_activities_toggle" data-kt-drawer-close="#kt_activities_close">
        <div class="border-0 shadow-none card rounded-0">
            <!--begin::Header-->
            <div class="card-header" id="kt_activities_header">
                <h3 class="text-gray-900 card-title fw-bold">Activity Logs</h3>
                <div class="card-toolbar">
                    <button type="button" class="btn btn-sm btn-icon btn-active-light-primary me-n5"
                        id="kt_activities_close">
                        <i class="ki-duotone ki-cross fs-1">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </button>
                </div>
            </div>
            <!--end::Header-->
            <!--begin::Body-->
            <div class="card-body position-relative" id="kt_activities_body">
                <!--begin::Content-->
                <div id="kt_activities_scroll" class="position-relative scroll-y me-n5 pe-5" data-kt-scroll="true"
                    data-kt-scroll-height="auto" data-kt-scroll-wrappers="#kt_activities_body"
                    data-kt-scroll-dependencies="#kt_activities_header, #kt_activities_footer"
                    data-kt-scroll-offset="5px">
                    <!--begin::Timeline items-->
                    <div class="timeline timeline-border-dashed">
                        <!--begin::Timeline item-->
                        <div class="timeline-item">
                            <!--begin::Timeline line-->
                            <div class="timeline-line"></div>
                            <!--end::Timeline line-->
                            <!--begin::Timeline icon-->
                            <div class="timeline-icon">
                                <i class="text-gray-500 ki-duotone ki-message-text-2 fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                            </div>
                            <!--end::Timeline icon-->
                            <!--begin::Timeline content-->
                            <div class="mb-10 timeline-content mt-n1">
                                <!--begin::Timeline heading-->
                                <div class="mb-5 pe-3">
                                    <!--begin::Title-->
                                    <div class="mb-2 fs-5 fw-semibold">There are 2 new tasks for you in “AirPlus
                                        Mobile App” project:</div>
                                    <!--end::Title-->
                                    <!--begin::Description-->
                                    <div class="mt-1 d-flex align-items-center fs-6">
                                        <!--begin::Info-->
                                        <div class="text-muted me-2 fs-7">Added at 4:23 PM by</div>
                                        <!--end::Info-->
                                        <!--begin::User-->
                                        <div class="symbol symbol-circle symbol-25px" data-bs-toggle="tooltip"
                                            data-bs-boundary="window" data-bs-placement="top" title="Nina Nilson">
                                            <img src="assets/media/avatars/300-14.jpg" alt="img" />
                                        </div>
                                        <!--end::User-->
                                    </div>
                                    <!--end::Description-->
                                </div>
                                <!--end::Timeline heading-->
                                <!--begin::Timeline details-->
                                <div class="overflow-auto pb-5">
                                    <!--begin::Record-->
                                    <div
                                        class="px-7 py-3 mb-5 rounded border border-gray-300 border-dashed d-flex align-items-center min-w-750px">
                                        <!--begin::Title-->
                                        <a href="apps/projects/project.html"
                                            class="text-gray-900 fs-5 text-hover-primary fw-semibold w-375px min-w-200px">Meeting
                                            with customer</a>
                                        <!--end::Title-->
                                        <!--begin::Label-->
                                        <div class="min-w-175px pe-2">
                                            <span class="badge badge-light text-muted">Application Design</span>
                                        </div>
                                        <!--end::Label-->
                                        <!--begin::Users-->
                                        <div
                                            class="flex-nowrap symbol-group symbol-hover flex-grow-1 min-w-100px pe-2">
                                            <!--begin::User-->
                                            <div class="symbol symbol-circle symbol-25px">
                                                <img src="assets/media/avatars/300-2.jpg" alt="img" />
                                            </div>
                                            <!--end::User-->
                                            <!--begin::User-->
                                            <div class="symbol symbol-circle symbol-25px">
                                                <img src="assets/media/avatars/300-14.jpg" alt="img" />
                                            </div>
                                            <!--end::User-->
                                            <!--begin::User-->
                                            <div class="symbol symbol-circle symbol-25px">
                                                <div
                                                    class="symbol-label fs-8 fw-semibold bg-primary text-inverse-primary">
                                                    A</div>
                                            </div>
                                            <!--end::User-->
                                        </div>
                                        <!--end::Users-->
                                        <!--begin::Progress-->
                                        <div class="min-w-125px pe-2">
                                            <span class="badge badge-light-primary">In Progress</span>
                                        </div>
                                        <!--end::Progress-->
                                        <!--begin::Action-->
                                        <a href="apps/projects/project.html"
                                            class="btn btn-sm btn-light btn-active-light-primary">View</a>
                                        <!--end::Action-->
                                    </div>
                                    <!--end::Record-->
                                    <!--begin::Record-->
                                    <div
                                        class="px-7 py-3 mb-0 rounded border border-gray-300 border-dashed d-flex align-items-center min-w-750px">
                                        <!--begin::Title-->
                                        <a href="apps/projects/project.html"
                                            class="text-gray-900 fs-5 text-hover-primary fw-semibold w-375px min-w-200px">Project
                                            Delivery Preparation</a>
                                        <!--end::Title-->
                                        <!--begin::Label-->
                                        <div class="min-w-175px">
                                            <span class="badge badge-light text-muted">CRM System Development</span>
                                        </div>
                                        <!--end::Label-->
                                        <!--begin::Users-->
                                        <div class="flex-nowrap symbol-group symbol-hover flex-grow-1 min-w-100px">
                                            <!--begin::User-->
                                            <div class="symbol symbol-circle symbol-25px">
                                                <img src="assets/media/avatars/300-20.jpg" alt="img" />
                                            </div>
                                            <!--end::User-->
                                            <!--begin::User-->
                                            <div class="symbol symbol-circle symbol-25px">
                                                <div
                                                    class="symbol-label fs-8 fw-semibold bg-success text-inverse-primary">
                                                    B</div>
                                            </div>
                                            <!--end::User-->
                                        </div>
                                        <!--end::Users-->
                                        <!--begin::Progress-->
                                        <div class="min-w-125px">
                                            <span class="badge badge-light-success">Completed</span>
                                        </div>
                                        <!--end::Progress-->
                                        <!--begin::Action-->
                                        <a href="apps/projects/project.html"
                                            class="btn btn-sm btn-light btn-active-light-primary">View</a>
                                        <!--end::Action-->
                                    </div>
                                    <!--end::Record-->
                                </div>
                                <!--end::Timeline details-->
                            </div>
                            <!--end::Timeline content-->
                        </div>
                        <!--end::Timeline item-->
                        <!--begin::Timeline item-->
                        <div class="timeline-item">
                            <!--begin::Timeline line-->
                            <div class="timeline-line"></div>
                            <!--end::Timeline line-->
                            <!--begin::Timeline icon-->
                            <div class="timeline-icon me-4">
                                <i class="text-gray-500 ki-duotone ki-flag fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </div>
                            <!--end::Timeline icon-->
                            <!--begin::Timeline content-->
                            <div class="mb-10 timeline-content mt-n2">
                                <!--begin::Timeline heading-->
                                <div class="overflow-auto pe-3">
                                    <!--begin::Title-->
                                    <div class="mb-2 fs-5 fw-semibold">Invitation for crafting engaging designs that
                                        speak human workshop</div>
                                    <!--end::Title-->
                                    <!--begin::Description-->
                                    <div class="mt-1 d-flex align-items-center fs-6">
                                        <!--begin::Info-->
                                        <div class="text-muted me-2 fs-7">Sent at 4:23 PM by</div>
                                        <!--end::Info-->
                                        <!--begin::User-->
                                        <div class="symbol symbol-circle symbol-25px" data-bs-toggle="tooltip"
                                            data-bs-boundary="window" data-bs-placement="top" title="Alan Nilson">
                                            <img src="assets/media/avatars/300-1.jpg" alt="img" />
                                        </div>
                                        <!--end::User-->
                                    </div>
                                    <!--end::Description-->
                                </div>
                                <!--end::Timeline heading-->
                            </div>
                            <!--end::Timeline content-->
                        </div>
                        <!--end::Timeline item-->
                        <!--begin::Timeline item-->
                        <div class="timeline-item">
                            <!--begin::Timeline line-->
                            <div class="timeline-line"></div>
                            <!--end::Timeline line-->
                            <!--begin::Timeline icon-->
                            <div class="timeline-icon">
                                <i class="text-gray-500 ki-duotone ki-disconnect fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                    <span class="path4"></span>
                                    <span class="path5"></span>
                                </i>
                            </div>
                            <!--end::Timeline icon-->
                            <!--begin::Timeline content-->
                            <div class="mb-10 timeline-content mt-n1">
                                <!--begin::Timeline heading-->
                                <div class="mb-5 pe-3">
                                    <!--begin::Title-->
                                    <a href="#"
                                        class="mb-2 text-gray-800 fs-5 fw-semibold text-hover-primary">3 New Incoming
                                        Project Files:</a>
                                    <!--end::Title-->
                                    <!--begin::Description-->
                                    <div class="mt-1 d-flex align-items-center fs-6">
                                        <!--begin::Info-->
                                        <div class="text-muted me-2 fs-7">Sent at 10:30 PM by</div>
                                        <!--end::Info-->
                                        <!--begin::User-->
                                        <div class="symbol symbol-circle symbol-25px" data-bs-toggle="tooltip"
                                            data-bs-boundary="window" data-bs-placement="top" title="Jan Hummer">
                                            <img src="assets/media/avatars/300-23.jpg" alt="img" />
                                        </div>
                                        <!--end::User-->
                                    </div>
                                    <!--end::Description-->
                                </div>
                                <!--end::Timeline heading-->
                                <!--begin::Timeline details-->
                                <div class="overflow-auto pb-5">
                                    <div
                                        class="p-5 rounded border border-gray-300 border-dashed d-flex align-items-center min-w-700px">
                                        <!--begin::Item-->
                                        <div class="d-flex flex-aligns-center pe-10 pe-lg-20">
                                            <!--begin::Icon-->
                                            <img alt="" class="w-30px me-3"
                                                src="assets/media/svg/files/pdf.svg" />
                                            <!--end::Icon-->
                                            <!--begin::Info-->
                                            <div class="ms-1 fw-semibold">
                                                <!--begin::Desc-->
                                                <a href="apps/projects/project.html"
                                                    class="fs-6 text-hover-primary fw-bold">Finance KPI App
                                                    Guidelines</a>
                                                <!--end::Desc-->
                                                <!--begin::Number-->
                                                <div class="text-gray-500">1.9mb</div>
                                                <!--end::Number-->
                                            </div>
                                            <!--begin::Info-->
                                        </div>
                                        <!--end::Item-->
                                        <!--begin::Item-->
                                        <div class="d-flex flex-aligns-center pe-10 pe-lg-20">
                                            <!--begin::Icon-->
                                            <img alt="apps/projects/project.html" class="w-30px me-3"
                                                src="assets/media/svg/files/doc.svg" />
                                            <!--end::Icon-->
                                            <!--begin::Info-->
                                            <div class="ms-1 fw-semibold">
                                                <!--begin::Desc-->
                                                <a href="#" class="fs-6 text-hover-primary fw-bold">Client UAT
                                                    Testing Results</a>
                                                <!--end::Desc-->
                                                <!--begin::Number-->
                                                <div class="text-gray-500">18kb</div>
                                                <!--end::Number-->
                                            </div>
                                            <!--end::Info-->
                                        </div>
                                        <!--end::Item-->
                                        <!--begin::Item-->
                                        <div class="d-flex flex-aligns-center">
                                            <!--begin::Icon-->
                                            <img alt="apps/projects/project.html" class="w-30px me-3"
                                                src="assets/media/svg/files/css.svg" />
                                            <!--end::Icon-->
                                            <!--begin::Info-->
                                            <div class="ms-1 fw-semibold">
                                                <!--begin::Desc-->
                                                <a href="#" class="fs-6 text-hover-primary fw-bold">Finance
                                                    Reports</a>
                                                <!--end::Desc-->
                                                <!--begin::Number-->
                                                <div class="text-gray-500">20mb</div>
                                                <!--end::Number-->
                                            </div>
                                            <!--end::Icon-->
                                        </div>
                                        <!--end::Item-->
                                    </div>
                                </div>
                                <!--end::Timeline details-->
                            </div>
                            <!--end::Timeline content-->
                        </div>
                        <!--end::Timeline item-->
                        <!--begin::Timeline item-->
                        <div class="timeline-item">
                            <!--begin::Timeline line-->
                            <div class="timeline-line"></div>
                            <!--end::Timeline line-->
                            <!--begin::Timeline icon-->
                            <div class="timeline-icon">
                                <i class="text-gray-500 ki-duotone ki-abstract-26 fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </div>
                            <!--end::Timeline icon-->
                            <!--begin::Timeline content-->
                            <div class="mb-10 timeline-content mt-n1">
                                <!--begin::Timeline heading-->
                                <div class="mb-5 pe-3">
                                    <!--begin::Title-->
                                    <div class="mb-2 fs-5 fw-semibold">Task
                                        <a href="#" class="text-primary fw-bold me-1">#45890</a>merged with
                                        <a href="#" class="text-primary fw-bold me-1">#45890</a>in “Ads Pro
                                        Admin Dashboard project:
                                    </div>
                                    <!--end::Title-->
                                    <!--begin::Description-->
                                    <div class="mt-1 d-flex align-items-center fs-6">
                                        <!--begin::Info-->
                                        <div class="text-muted me-2 fs-7">Initiated at 4:23 PM by</div>
                                        <!--end::Info-->
                                        <!--begin::User-->
                                        <div class="symbol symbol-circle symbol-25px" data-bs-toggle="tooltip"
                                            data-bs-boundary="window" data-bs-placement="top" title="Nina Nilson">
                                            <img src="assets/media/avatars/300-14.jpg" alt="img" />
                                        </div>
                                        <!--end::User-->
                                    </div>
                                    <!--end::Description-->
                                </div>
                                <!--end::Timeline heading-->
                            </div>
                            <!--end::Timeline content-->
                        </div>
                        <!--end::Timeline item-->
                        <!--begin::Timeline item-->
                        <div class="timeline-item">
                            <!--begin::Timeline line-->
                            <div class="timeline-line"></div>
                            <!--end::Timeline line-->
                            <!--begin::Timeline icon-->
                            <div class="timeline-icon">
                                <i class="text-gray-500 ki-duotone ki-pencil fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </div>
                            <!--end::Timeline icon-->
                            <!--begin::Timeline content-->
                            <div class="mb-10 timeline-content mt-n1">
                                <!--begin::Timeline heading-->
                                <div class="mb-5 pe-3">
                                    <!--begin::Title-->
                                    <div class="mb-2 fs-5 fw-semibold">3 new application design concepts added:</div>
                                    <!--end::Title-->
                                    <!--begin::Description-->
                                    <div class="mt-1 d-flex align-items-center fs-6">
                                        <!--begin::Info-->
                                        <div class="text-muted me-2 fs-7">Created at 4:23 PM by</div>
                                        <!--end::Info-->
                                        <!--begin::User-->
                                        <div class="symbol symbol-circle symbol-25px" data-bs-toggle="tooltip"
                                            data-bs-boundary="window" data-bs-placement="top"
                                            title="Marcus Dotson">
                                            <img src="assets/media/avatars/300-2.jpg" alt="img" />
                                        </div>
                                        <!--end::User-->
                                    </div>
                                    <!--end::Description-->
                                </div>
                                <!--end::Timeline heading-->
                                <!--begin::Timeline details-->
                                <div class="overflow-auto pb-5">
                                    <div
                                        class="p-7 rounded border border-gray-300 border-dashed d-flex align-items-center min-w-700px">
                                        <!--begin::Item-->
                                        <div class="overlay me-10">
                                            <!--begin::Image-->
                                            <div class="overlay-wrapper">
                                                <img alt="img" class="rounded w-150px"
                                                    src="assets/media/stock/600x400/img-29.jpg" />
                                            </div>
                                            <!--end::Image-->
                                            <!--begin::Link-->
                                            <div class="bg-opacity-10 rounded overlay-layer bg-dark">
                                                <a href="#"
                                                    class="btn btn-sm btn-primary btn-shadow">Explore</a>
                                            </div>
                                            <!--end::Link-->
                                        </div>
                                        <!--end::Item-->
                                        <!--begin::Item-->
                                        <div class="overlay me-10">
                                            <!--begin::Image-->
                                            <div class="overlay-wrapper">
                                                <img alt="img" class="rounded w-150px"
                                                    src="assets/media/stock/600x400/img-31.jpg" />
                                            </div>
                                            <!--end::Image-->
                                            <!--begin::Link-->
                                            <div class="bg-opacity-10 rounded overlay-layer bg-dark">
                                                <a href="#"
                                                    class="btn btn-sm btn-primary btn-shadow">Explore</a>
                                            </div>
                                            <!--end::Link-->
                                        </div>
                                        <!--end::Item-->
                                        <!--begin::Item-->
                                        <div class="overlay">
                                            <!--begin::Image-->
                                            <div class="overlay-wrapper">
                                                <img alt="img" class="rounded w-150px"
                                                    src="assets/media/stock/600x400/img-40.jpg" />
                                            </div>
                                            <!--end::Image-->
                                            <!--begin::Link-->
                                            <div class="bg-opacity-10 rounded overlay-layer bg-dark">
                                                <a href="#"
                                                    class="btn btn-sm btn-primary btn-shadow">Explore</a>
                                            </div>
                                            <!--end::Link-->
                                        </div>
                                        <!--end::Item-->
                                    </div>
                                </div>
                                <!--end::Timeline details-->
                            </div>
                            <!--end::Timeline content-->
                        </div>
                        <!--end::Timeline item-->
                        <!--begin::Timeline item-->
                        <div class="timeline-item">
                            <!--begin::Timeline line-->
                            <div class="timeline-line"></div>
                            <!--end::Timeline line-->
                            <!--begin::Timeline icon-->
                            <div class="timeline-icon">
                                <i class="text-gray-500 ki-duotone ki-sms fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </div>
                            <!--end::Timeline icon-->
                            <!--begin::Timeline content-->
                            <div class="mb-10 timeline-content mt-n1">
                                <!--begin::Timeline heading-->
                                <div class="mb-5 pe-3">
                                    <!--begin::Title-->
                                    <div class="mb-2 fs-5 fw-semibold">New case
                                        <a href="#" class="text-primary fw-bold me-1">#67890</a>is assigned to
                                        you in Multi-platform Database Design project
                                    </div>
                                    <!--end::Title-->
                                    <!--begin::Description-->
                                    <div class="overflow-auto pb-5">
                                        <!--begin::Wrapper-->
                                        <div class="mt-1 d-flex align-items-center fs-6">
                                            <!--begin::Info-->
                                            <div class="text-muted me-2 fs-7">Added at 4:23 PM by</div>
                                            <!--end::Info-->
                                            <!--begin::User-->
                                            <a href="#" class="text-primary fw-bold me-1">Alice Tan</a>
                                            <!--end::User-->
                                        </div>
                                        <!--end::Wrapper-->
                                    </div>
                                    <!--end::Description-->
                                </div>
                                <!--end::Timeline heading-->
                            </div>
                            <!--end::Timeline content-->
                        </div>
                        <!--end::Timeline item-->
                        <!--begin::Timeline item-->
                        <div class="timeline-item">
                            <!--begin::Timeline line-->
                            <div class="timeline-line"></div>
                            <!--end::Timeline line-->
                            <!--begin::Timeline icon-->
                            <div class="timeline-icon">
                                <i class="text-gray-500 ki-duotone ki-pencil fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </div>
                            <!--end::Timeline icon-->
                            <!--begin::Timeline content-->
                            <div class="mb-10 timeline-content mt-n1">
                                <!--begin::Timeline heading-->
                                <div class="mb-5 pe-3">
                                    <!--begin::Title-->
                                    <div class="mb-2 fs-5 fw-semibold">You have received a new order:</div>
                                    <!--end::Title-->
                                    <!--begin::Description-->
                                    <div class="mt-1 d-flex align-items-center fs-6">
                                        <!--begin::Info-->
                                        <div class="text-muted me-2 fs-7">Placed at 5:05 AM by</div>
                                        <!--end::Info-->
                                        <!--begin::User-->
                                        <div class="symbol symbol-circle symbol-25px" data-bs-toggle="tooltip"
                                            data-bs-boundary="window" data-bs-placement="top" title="Robert Rich">
                                            <img src="assets/media/avatars/300-4.jpg" alt="img" />
                                        </div>
                                        <!--end::User-->
                                    </div>
                                    <!--end::Description-->
                                </div>
                                <!--end::Timeline heading-->
                                <!--begin::Timeline details-->
                                <div class="overflow-auto pb-5">
                                    <!--begin::Notice-->
                                    <div
                                        class="flex-shrink-0 p-6 rounded border border-dashed notice d-flex bg-light-primary border-primary min-w-lg-600px">
                                        <!--begin::Icon-->
                                        <i class="ki-duotone ki-devices-2 fs-2tx text-primary me-4">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                        </i>
                                        <!--end::Icon-->
                                        <!--begin::Wrapper-->
                                        <div class="flex-wrap d-flex flex-stack flex-grow-1 flex-md-nowrap">
                                            <!--begin::Content-->
                                            <div class="mb-3 mb-md-0 fw-semibold">
                                                <h4 class="text-gray-900 fw-bold">Database Backup Process Completed!
                                                </h4>
                                                <div class="text-gray-700 fs-6 pe-7">Login into Admin Dashboard to
                                                    make sure the data integrity is OK</div>
                                            </div>
                                            <!--end::Content-->
                                            <!--begin::Action-->
                                            <a href="#"
                                                class="px-6 btn btn-primary align-self-center text-nowrap">Proceed</a>
                                            <!--end::Action-->
                                        </div>
                                        <!--end::Wrapper-->
                                    </div>
                                    <!--end::Notice-->
                                </div>
                                <!--end::Timeline details-->
                            </div>
                            <!--end::Timeline content-->
                        </div>
                        <!--end::Timeline item-->
                        <!--begin::Timeline item-->
                        <div class="timeline-item">
                            <!--begin::Timeline line-->
                            <div class="timeline-line"></div>
                            <!--end::Timeline line-->
                            <!--begin::Timeline icon-->
                            <div class="timeline-icon">
                                <i class="text-gray-500 ki-duotone ki-basket fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                    <span class="path4"></span>
                                </i>
                            </div>
                            <!--end::Timeline icon-->
                            <!--begin::Timeline content-->
                            <div class="timeline-content mt-n1">
                                <!--begin::Timeline heading-->
                                <div class="mb-5 pe-3">
                                    <!--begin::Title-->
                                    <div class="mb-2 fs-5 fw-semibold">New order
                                        <a href="#" class="text-primary fw-bold me-1">#67890</a>is placed for
                                        Workshow Planning & Budget Estimation
                                    </div>
                                    <!--end::Title-->
                                    <!--begin::Description-->
                                    <div class="mt-1 d-flex align-items-center fs-6">
                                        <!--begin::Info-->
                                        <div class="text-muted me-2 fs-7">Placed at 4:23 PM by</div>
                                        <!--end::Info-->
                                        <!--begin::User-->
                                        <a href="#" class="text-primary fw-bold me-1">Jimmy Bold</a>
                                        <!--end::User-->
                                    </div>
                                    <!--end::Description-->
                                </div>
                                <!--end::Timeline heading-->
                            </div>
                            <!--end::Timeline content-->
                        </div>
                        <!--end::Timeline item-->
                    </div>
                    <!--end::Timeline items-->
                </div>
                <!--end::Content-->
            </div>
            <!--end::Body-->
            <!--begin::Footer-->
            <div class="py-5 text-center card-footer" id="kt_activities_footer">
                <a href="pages/user-profile/activity.html" class="btn btn-bg-body text-primary">View All Activities
                    <i class="ki-duotone ki-arrow-right fs-3 text-primary">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i></a>
            </div>
            <!--end::Footer-->
        </div>
    </div>
    <!--end::Activities drawer-->
    <!--begin::Chat drawer-->
    <div id="kt_drawer_chat" class="bg-body" data-kt-drawer="true" data-kt-drawer-name="chat"
        data-kt-drawer-activate="true" data-kt-drawer-overlay="true"
        data-kt-drawer-width="{default:'300px', 'md': '500px'}" data-kt-drawer-direction="end"
        data-kt-drawer-toggle="#kt_drawer_chat_toggle" data-kt-drawer-close="#kt_drawer_chat_close">
        <!--begin::Messenger-->
        <div class="border-0 card w-100 rounded-0" id="kt_drawer_chat_messenger">
            <!--begin::Card header-->
            <div class="card-header pe-5" id="kt_drawer_chat_messenger_header">
                <!--begin::Title-->
                <div class="card-title">
                    <!--begin::User-->
                    <div class="d-flex justify-content-center flex-column me-3">
                        <a href="#" class="mb-2 text-gray-900 fs-4 fw-bold text-hover-primary me-1 lh-1">Brian
                            Cox</a>
                        <!--begin::Info-->
                        <div class="mb-0 lh-1">
                            <span class="badge badge-success badge-circle w-10px h-10px me-1"></span>
                            <span class="fs-7 fw-semibold text-muted">Active</span>
                        </div>
                        <!--end::Info-->
                    </div>
                    <!--end::User-->
                </div>
                <!--end::Title-->
                <!--begin::Card toolbar-->
                <div class="card-toolbar">
                    <!--begin::Menu-->
                    <div class="me-0">
                        <button class="btn btn-sm btn-icon btn-active-color-primary" data-kt-menu-trigger="click"
                            data-kt-menu-placement="bottom-end">
                            <i class="ki-duotone ki-dots-square fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                                <span class="path4"></span>
                            </i>
                        </button>
                        <!--begin::Menu 3-->
                        <div class="py-3 menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-200px"
                            data-kt-menu="true">
                            <!--begin::Heading-->
                            <div class="px-3 menu-item">
                                <div class="px-3 pb-2 menu-content text-muted fs-7 text-uppercase">Contacts</div>
                            </div>
                            <!--end::Heading-->
                            <!--begin::Menu item-->
                            <div class="px-3 menu-item">
                                <a href="#" class="px-3 menu-link" data-bs-toggle="modal"
                                    data-bs-target="#kt_modal_users_search">Add Contact</a>
                            </div>
                            <!--end::Menu item-->
                            <!--begin::Menu item-->
                            <div class="px-3 menu-item">
                                <a href="#" class="px-3 menu-link flex-stack" data-bs-toggle="modal"
                                    data-bs-target="#kt_modal_invite_friends">Invite Contacts
                                    <span class="ms-2" data-bs-toggle="tooltip"
                                        title="Specify a contact email to send an invitation">
                                        <i class="ki-duotone ki-information fs-7">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                        </i>
                                    </span></a>
                            </div>
                            <!--end::Menu item-->
                            <!--begin::Menu item-->
                            <div class="px-3 menu-item" data-kt-menu-trigger="hover"
                                data-kt-menu-placement="right-start">
                                <a href="#" class="px-3 menu-link">
                                    <span class="menu-title">Groups</span>
                                    <span class="menu-arrow"></span>
                                </a>
                                <!--begin::Menu sub-->
                                <div class="py-4 menu-sub menu-sub-dropdown w-175px">
                                    <!--begin::Menu item-->
                                    <div class="px-3 menu-item">
                                        <a href="#" class="px-3 menu-link" data-bs-toggle="tooltip"
                                            title="Coming soon">Create Group</a>
                                    </div>
                                    <!--end::Menu item-->
                                    <!--begin::Menu item-->
                                    <div class="px-3 menu-item">
                                        <a href="#" class="px-3 menu-link" data-bs-toggle="tooltip"
                                            title="Coming soon">Invite Members</a>
                                    </div>
                                    <!--end::Menu item-->
                                    <!--begin::Menu item-->
                                    <div class="px-3 menu-item">
                                        <a href="#" class="px-3 menu-link" data-bs-toggle="tooltip"
                                            title="Coming soon">Settings</a>
                                    </div>
                                    <!--end::Menu item-->
                                </div>
                                <!--end::Menu sub-->
                            </div>
                            <!--end::Menu item-->
                            <!--begin::Menu item-->
                            <div class="px-3 my-1 menu-item">
                                <a href="#" class="px-3 menu-link" data-bs-toggle="tooltip"
                                    title="Coming soon">Settings</a>
                            </div>
                            <!--end::Menu item-->
                        </div>
                        <!--end::Menu 3-->
                    </div>
                    <!--end::Menu-->
                    <!--begin::Close-->
                    <div class="btn btn-sm btn-icon btn-active-color-primary" id="kt_drawer_chat_close">
                        <i class="ki-duotone ki-cross-square fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </div>
                    <!--end::Close-->
                </div>
                <!--end::Card toolbar-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body" id="kt_drawer_chat_messenger_body">
                <!--begin::Messages-->
                <div class="scroll-y me-n5 pe-5" data-kt-element="messages" data-kt-scroll="true"
                    data-kt-scroll-activate="true" data-kt-scroll-height="auto"
                    data-kt-scroll-dependencies="#kt_drawer_chat_messenger_header, #kt_drawer_chat_messenger_footer"
                    data-kt-scroll-wrappers="#kt_drawer_chat_messenger_body" data-kt-scroll-offset="0px">
                    <!--begin::Message(in)-->
                    <div class="mb-10 d-flex justify-content-start">
                        <!--begin::Wrapper-->
                        <div class="d-flex flex-column align-items-start">
                            <!--begin::User-->
                            <div class="mb-2 d-flex align-items-center">
                                <!--begin::Avatar-->
                                <div class="symbol symbol-35px symbol-circle">
                                    <img alt="Pic" src="assets/media/avatars/300-25.jpg" />
                                </div>
                                <!--end::Avatar-->
                                <!--begin::Details-->
                                <div class="ms-3">
                                    <a href="#"
                                        class="text-gray-900 fs-5 fw-bold text-hover-primary me-1">Brian Cox</a>
                                    <span class="mb-1 text-muted fs-7">2 mins</span>
                                </div>
                                <!--end::Details-->
                            </div>
                            <!--end::User-->
                            <!--begin::Text-->
                            <div class="p-5 text-gray-900 rounded bg-light-info fw-semibold mw-lg-400px text-start"
                                data-kt-element="message-text">How likely are you to recommend our company to your
                                friends and family ?</div>
                            <!--end::Text-->
                        </div>
                        <!--end::Wrapper-->
                    </div>
                    <!--end::Message(in)-->
                    <!--begin::Message(out)-->
                    <div class="mb-10 d-flex justify-content-end">
                        <!--begin::Wrapper-->
                        <div class="d-flex flex-column align-items-end">
                            <!--begin::User-->
                            <div class="mb-2 d-flex align-items-center">
                                <!--begin::Details-->
                                <div class="me-3">
                                    <span class="mb-1 text-muted fs-7">5 mins</span>
                                    <a href="#"
                                        class="text-gray-900 fs-5 fw-bold text-hover-primary ms-1">You</a>
                                </div>
                                <!--end::Details-->
                                <!--begin::Avatar-->
                                <div class="symbol symbol-35px symbol-circle">
                                    <img alt="Pic" src="assets/media/avatars/300-1.jpg" />
                                </div>
                                <!--end::Avatar-->
                            </div>
                            <!--end::User-->
                            <!--begin::Text-->
                            <div class="p-5 text-gray-900 rounded bg-light-primary fw-semibold mw-lg-400px text-end"
                                data-kt-element="message-text">Hey there, we’re just writing to let you know that
                                you’ve been subscribed to a repository on GitHub.</div>
                            <!--end::Text-->
                        </div>
                        <!--end::Wrapper-->
                    </div>
                    <!--end::Message(out)-->
                    <!--begin::Message(in)-->
                    <div class="mb-10 d-flex justify-content-start">
                        <!--begin::Wrapper-->
                        <div class="d-flex flex-column align-items-start">
                            <!--begin::User-->
                            <div class="mb-2 d-flex align-items-center">
                                <!--begin::Avatar-->
                                <div class="symbol symbol-35px symbol-circle">
                                    <img alt="Pic" src="assets/media/avatars/300-25.jpg" />
                                </div>
                                <!--end::Avatar-->
                                <!--begin::Details-->
                                <div class="ms-3">
                                    <a href="#"
                                        class="text-gray-900 fs-5 fw-bold text-hover-primary me-1">Brian Cox</a>
                                    <span class="mb-1 text-muted fs-7">1 Hour</span>
                                </div>
                                <!--end::Details-->
                            </div>
                            <!--end::User-->
                            <!--begin::Text-->
                            <div class="p-5 text-gray-900 rounded bg-light-info fw-semibold mw-lg-400px text-start"
                                data-kt-element="message-text">Ok, Understood!</div>
                            <!--end::Text-->
                        </div>
                        <!--end::Wrapper-->
                    </div>
                    <!--end::Message(in)-->
                    <!--begin::Message(out)-->
                    <div class="mb-10 d-flex justify-content-end">
                        <!--begin::Wrapper-->
                        <div class="d-flex flex-column align-items-end">
                            <!--begin::User-->
                            <div class="mb-2 d-flex align-items-center">
                                <!--begin::Details-->
                                <div class="me-3">
                                    <span class="mb-1 text-muted fs-7">2 Hours</span>
                                    <a href="#"
                                        class="text-gray-900 fs-5 fw-bold text-hover-primary ms-1">You</a>
                                </div>
                                <!--end::Details-->
                                <!--begin::Avatar-->
                                <div class="symbol symbol-35px symbol-circle">
                                    <img alt="Pic" src="assets/media/avatars/300-1.jpg" />
                                </div>
                                <!--end::Avatar-->
                            </div>
                            <!--end::User-->
                            <!--begin::Text-->
                            <div class="p-5 text-gray-900 rounded bg-light-primary fw-semibold mw-lg-400px text-end"
                                data-kt-element="message-text">You’ll receive notifications for all issues, pull
                                requests!</div>
                            <!--end::Text-->
                        </div>
                        <!--end::Wrapper-->
                    </div>
                    <!--end::Message(out)-->
                    <!--begin::Message(in)-->
                    <div class="mb-10 d-flex justify-content-start">
                        <!--begin::Wrapper-->
                        <div class="d-flex flex-column align-items-start">
                            <!--begin::User-->
                            <div class="mb-2 d-flex align-items-center">
                                <!--begin::Avatar-->
                                <div class="symbol symbol-35px symbol-circle">
                                    <img alt="Pic" src="assets/media/avatars/300-25.jpg" />
                                </div>
                                <!--end::Avatar-->
                                <!--begin::Details-->
                                <div class="ms-3">
                                    <a href="#"
                                        class="text-gray-900 fs-5 fw-bold text-hover-primary me-1">Brian Cox</a>
                                    <span class="mb-1 text-muted fs-7">3 Hours</span>
                                </div>
                                <!--end::Details-->
                            </div>
                            <!--end::User-->
                            <!--begin::Text-->
                            <div class="p-5 text-gray-900 rounded bg-light-info fw-semibold mw-lg-400px text-start"
                                data-kt-element="message-text">You can unwatch this repository immediately by clicking
                                here:
                                <a href="https://keenthemes.com">Keenthemes.com</a>
                            </div>
                            <!--end::Text-->
                        </div>
                        <!--end::Wrapper-->
                    </div>
                    <!--end::Message(in)-->
                    <!--begin::Message(out)-->
                    <div class="mb-10 d-flex justify-content-end">
                        <!--begin::Wrapper-->
                        <div class="d-flex flex-column align-items-end">
                            <!--begin::User-->
                            <div class="mb-2 d-flex align-items-center">
                                <!--begin::Details-->
                                <div class="me-3">
                                    <span class="mb-1 text-muted fs-7">4 Hours</span>
                                    <a href="#"
                                        class="text-gray-900 fs-5 fw-bold text-hover-primary ms-1">You</a>
                                </div>
                                <!--end::Details-->
                                <!--begin::Avatar-->
                                <div class="symbol symbol-35px symbol-circle">
                                    <img alt="Pic" src="assets/media/avatars/300-1.jpg" />
                                </div>
                                <!--end::Avatar-->
                            </div>
                            <!--end::User-->
                            <!--begin::Text-->
                            <div class="p-5 text-gray-900 rounded bg-light-primary fw-semibold mw-lg-400px text-end"
                                data-kt-element="message-text">Most purchased Business courses during this sale!</div>
                            <!--end::Text-->
                        </div>
                        <!--end::Wrapper-->
                    </div>
                    <!--end::Message(out)-->
                    <!--begin::Message(in)-->
                    <div class="mb-10 d-flex justify-content-start">
                        <!--begin::Wrapper-->
                        <div class="d-flex flex-column align-items-start">
                            <!--begin::User-->
                            <div class="mb-2 d-flex align-items-center">
                                <!--begin::Avatar-->
                                <div class="symbol symbol-35px symbol-circle">
                                    <img alt="Pic" src="assets/media/avatars/300-25.jpg" />
                                </div>
                                <!--end::Avatar-->
                                <!--begin::Details-->
                                <div class="ms-3">
                                    <a href="#"
                                        class="text-gray-900 fs-5 fw-bold text-hover-primary me-1">Brian Cox</a>
                                    <span class="mb-1 text-muted fs-7">5 Hours</span>
                                </div>
                                <!--end::Details-->
                            </div>
                            <!--end::User-->
                            <!--begin::Text-->
                            <div class="p-5 text-gray-900 rounded bg-light-info fw-semibold mw-lg-400px text-start"
                                data-kt-element="message-text">Company BBQ to celebrate the last quater achievements
                                and goals. Food and drinks provided</div>
                            <!--end::Text-->
                        </div>
                        <!--end::Wrapper-->
                    </div>
                    <!--end::Message(in)-->
                    <!--begin::Message(template for out)-->
                    <div class="mb-10 d-flex justify-content-end d-none" data-kt-element="template-out">
                        <!--begin::Wrapper-->
                        <div class="d-flex flex-column align-items-end">
                            <!--begin::User-->
                            <div class="mb-2 d-flex align-items-center">
                                <!--begin::Details-->
                                <div class="me-3">
                                    <span class="mb-1 text-muted fs-7">Just now</span>
                                    <a href="#"
                                        class="text-gray-900 fs-5 fw-bold text-hover-primary ms-1">You</a>
                                </div>
                                <!--end::Details-->
                                <!--begin::Avatar-->
                                <div class="symbol symbol-35px symbol-circle">
                                    <img alt="Pic" src="assets/media/avatars/300-1.jpg" />
                                </div>
                                <!--end::Avatar-->
                            </div>
                            <!--end::User-->
                            <!--begin::Text-->
                            <div class="p-5 text-gray-900 rounded bg-light-primary fw-semibold mw-lg-400px text-end"
                                data-kt-element="message-text"></div>
                            <!--end::Text-->
                        </div>
                        <!--end::Wrapper-->
                    </div>
                    <!--end::Message(template for out)-->
                    <!--begin::Message(template for in)-->
                    <div class="mb-10 d-flex justify-content-start d-none" data-kt-element="template-in">
                        <!--begin::Wrapper-->
                        <div class="d-flex flex-column align-items-start">
                            <!--begin::User-->
                            <div class="mb-2 d-flex align-items-center">
                                <!--begin::Avatar-->
                                <div class="symbol symbol-35px symbol-circle">
                                    <img alt="Pic" src="assets/media/avatars/300-25.jpg" />
                                </div>
                                <!--end::Avatar-->
                                <!--begin::Details-->
                                <div class="ms-3">
                                    <a href="#"
                                        class="text-gray-900 fs-5 fw-bold text-hover-primary me-1">Brian Cox</a>
                                    <span class="mb-1 text-muted fs-7">Just now</span>
                                </div>
                                <!--end::Details-->
                            </div>
                            <!--end::User-->
                            <!--begin::Text-->
                            <div class="p-5 text-gray-900 rounded bg-light-info fw-semibold mw-lg-400px text-start"
                                data-kt-element="message-text">Right before vacation season we have the next Big Deal
                                for you.</div>
                            <!--end::Text-->
                        </div>
                        <!--end::Wrapper-->
                    </div>
                    <!--end::Message(template for in)-->
                </div>
                <!--end::Messages-->
            </div>
            <!--end::Card body-->
            <!--begin::Card footer-->
            <div class="pt-4 card-footer" id="kt_drawer_chat_messenger_footer">
                <!--begin::Input-->
                <textarea class="mb-3 form-control form-control-flush" rows="1" data-kt-element="input"
                    placeholder="Type a message"></textarea>
                <!--end::Input-->
                <!--begin:Toolbar-->
                <div class="d-flex flex-stack">
                    <!--begin::Actions-->
                    <div class="d-flex align-items-center me-2">
                        <button class="btn btn-sm btn-icon btn-active-light-primary me-1" type="button"
                            data-bs-toggle="tooltip" title="Coming soon">
                            <i class="ki-duotone ki-paper-clip fs-3"></i>
                        </button>
                        <button class="btn btn-sm btn-icon btn-active-light-primary me-1" type="button"
                            data-bs-toggle="tooltip" title="Coming soon">
                            <i class="ki-duotone ki-cloud-add fs-3">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                        </button>
                    </div>
                    <!--end::Actions-->
                    <!--begin::Send-->
                    <button class="btn btn-primary" type="button" data-kt-element="send">Send</button>
                    <!--end::Send-->
                </div>
                <!--end::Toolbar-->
            </div>
            <!--end::Card footer-->
        </div>
        <!--end::Messenger-->
    </div>
    <!--end::Chat drawer-->
    <!--begin::Chat drawer-->
    <div id="kt_shopping_cart" class="bg-body" data-kt-drawer="true" data-kt-drawer-name="cart"
        data-kt-drawer-activate="true" data-kt-drawer-overlay="true"
        data-kt-drawer-width="{default:'300px', 'md': '500px'}" data-kt-drawer-direction="end"
        data-kt-drawer-toggle="#kt_drawer_shopping_cart_toggle"
        data-kt-drawer-close="#kt_drawer_shopping_cart_close">
        <!--begin::Messenger-->
        <div class="card card-flush w-100 rounded-0">
            <!--begin::Card header-->
            <div class="card-header">
                <!--begin::Title-->
                <h3 class="text-gray-900 card-title fw-bold">Shopping Cart</h3>
                <!--end::Title-->
                <!--begin::Card toolbar-->
                <div class="card-toolbar">
                    <!--begin::Close-->
                    <div class="btn btn-sm btn-icon btn-active-light-primary" id="kt_drawer_shopping_cart_close">
                        <i class="ki-duotone ki-cross fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </div>
                    <!--end::Close-->
                </div>
                <!--end::Card toolbar-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="pt-5 card-body hover-scroll-overlay-y h-400px">
                <!--begin::Item-->
                <div class="d-flex flex-stack">
                    <!--begin::Wrapper-->
                    <div class="d-flex flex-column me-3">
                        <!--begin::Section-->
                        <div class="mb-3">
                            <a href="apps/ecommerce/sales/details.html"
                                class="text-gray-800 text-hover-primary fs-4 fw-bold">Iblender</a>
                            <span class="text-gray-500 fw-semibold d-block">The best kitchen gadget in 2022</span>
                        </div>
                        <!--end::Section-->
                        <!--begin::Section-->
                        <div class="d-flex align-items-center">
                            <span class="text-gray-800 fw-bold fs-5">$ 350</span>
                            <span class="mx-2 text-muted">for</span>
                            <span class="text-gray-800 fw-bold fs-5 me-3">5</span>
                            <a href="#"
                                class="btn btn-sm btn-light-success btn-icon-success btn-icon w-25px h-25px me-2">
                                <i class="ki-duotone ki-minus fs-4"></i>
                            </a>
                            <a href="#" class="btn btn-sm btn-light-success btn-icon w-25px h-25px">
                                <i class="ki-duotone ki-plus fs-4"></i>
                            </a>
                        </div>
                        <!--end::Wrapper-->
                    </div>
                    <!--end::Wrapper-->
                    <!--begin::Pic-->
                    <div class="flex-shrink-0 symbol symbol-70px symbol-2by3">
                        <img src="assets/media/stock/600x400/img-1.jpg" alt="" />
                    </div>
                    <!--end::Pic-->
                </div>
                <!--end::Item-->
                <!--begin::Separator-->
                <div class="my-6 separator separator-dashed"></div>
                <!--end::Separator-->
                <!--begin::Item-->
                <div class="d-flex flex-stack">
                    <!--begin::Wrapper-->
                    <div class="d-flex flex-column me-3">
                        <!--begin::Section-->
                        <div class="mb-3">
                            <a href="apps/ecommerce/sales/details.html"
                                class="text-gray-800 text-hover-primary fs-4 fw-bold">SmartCleaner</a>
                            <span class="text-gray-500 fw-semibold d-block">Smart tool for cooking</span>
                        </div>
                        <!--end::Section-->
                        <!--begin::Section-->
                        <div class="d-flex align-items-center">
                            <span class="text-gray-800 fw-bold fs-5">$ 650</span>
                            <span class="mx-2 text-muted">for</span>
                            <span class="text-gray-800 fw-bold fs-5 me-3">4</span>
                            <a href="#"
                                class="btn btn-sm btn-light-success btn-icon-success btn-icon w-25px h-25px me-2">
                                <i class="ki-duotone ki-minus fs-4"></i>
                            </a>
                            <a href="#" class="btn btn-sm btn-light-success btn-icon w-25px h-25px">
                                <i class="ki-duotone ki-plus fs-4"></i>
                            </a>
                        </div>
                        <!--end::Wrapper-->
                    </div>
                    <!--end::Wrapper-->
                    <!--begin::Pic-->
                    <div class="flex-shrink-0 symbol symbol-70px symbol-2by3">
                        <img src="assets/media/stock/600x400/img-3.jpg" alt="" />
                    </div>
                    <!--end::Pic-->
                </div>
                <!--end::Item-->
                <!--begin::Separator-->
                <div class="my-6 separator separator-dashed"></div>
                <!--end::Separator-->
                <!--begin::Item-->
                <div class="d-flex flex-stack">
                    <!--begin::Wrapper-->
                    <div class="d-flex flex-column me-3">
                        <!--begin::Section-->
                        <div class="mb-3">
                            <a href="apps/ecommerce/sales/details.html"
                                class="text-gray-800 text-hover-primary fs-4 fw-bold">CameraMaxr</a>
                            <span class="text-gray-500 fw-semibold d-block">Professional camera for edge</span>
                        </div>
                        <!--end::Section-->
                        <!--begin::Section-->
                        <div class="d-flex align-items-center">
                            <span class="text-gray-800 fw-bold fs-5">$ 150</span>
                            <span class="mx-2 text-muted">for</span>
                            <span class="text-gray-800 fw-bold fs-5 me-3">3</span>
                            <a href="#"
                                class="btn btn-sm btn-light-success btn-icon-success btn-icon w-25px h-25px me-2">
                                <i class="ki-duotone ki-minus fs-4"></i>
                            </a>
                            <a href="#" class="btn btn-sm btn-light-success btn-icon w-25px h-25px">
                                <i class="ki-duotone ki-plus fs-4"></i>
                            </a>
                        </div>
                        <!--end::Wrapper-->
                    </div>
                    <!--end::Wrapper-->
                    <!--begin::Pic-->
                    <div class="flex-shrink-0 symbol symbol-70px symbol-2by3">
                        <img src="assets/media/stock/600x400/img-8.jpg" alt="" />
                    </div>
                    <!--end::Pic-->
                </div>
                <!--end::Item-->
                <!--begin::Separator-->
                <div class="my-6 separator separator-dashed"></div>
                <!--end::Separator-->
                <!--begin::Item-->
                <div class="d-flex flex-stack">
                    <!--begin::Wrapper-->
                    <div class="d-flex flex-column me-3">
                        <!--begin::Section-->
                        <div class="mb-3">
                            <a href="apps/ecommerce/sales/details.html"
                                class="text-gray-800 text-hover-primary fs-4 fw-bold">$D Printer</a>
                            <span class="text-gray-500 fw-semibold d-block">Manfactoring unique objekts</span>
                        </div>
                        <!--end::Section-->
                        <!--begin::Section-->
                        <div class="d-flex align-items-center">
                            <span class="text-gray-800 fw-bold fs-5">$ 1450</span>
                            <span class="mx-2 text-muted">for</span>
                            <span class="text-gray-800 fw-bold fs-5 me-3">7</span>
                            <a href="#"
                                class="btn btn-sm btn-light-success btn-icon-success btn-icon w-25px h-25px me-2">
                                <i class="ki-duotone ki-minus fs-4"></i>
                            </a>
                            <a href="#" class="btn btn-sm btn-light-success btn-icon w-25px h-25px">
                                <i class="ki-duotone ki-plus fs-4"></i>
                            </a>
                        </div>
                        <!--end::Wrapper-->
                    </div>
                    <!--end::Wrapper-->
                    <!--begin::Pic-->
                    <div class="flex-shrink-0 symbol symbol-70px symbol-2by3">
                        <img src="assets/media/stock/600x400/img-26.jpg" alt="" />
                    </div>
                    <!--end::Pic-->
                </div>
                <!--end::Item-->
                <!--begin::Separator-->
                <div class="my-6 separator separator-dashed"></div>
                <!--end::Separator-->
                <!--begin::Item-->
                <div class="d-flex flex-stack">
                    <!--begin::Wrapper-->
                    <div class="d-flex flex-column me-3">
                        <!--begin::Section-->
                        <div class="mb-3">
                            <a href="apps/ecommerce/sales/details.html"
                                class="text-gray-800 text-hover-primary fs-4 fw-bold">MotionWire</a>
                            <span class="text-gray-500 fw-semibold d-block">Perfect animation tool</span>
                        </div>
                        <!--end::Section-->
                        <!--begin::Section-->
                        <div class="d-flex align-items-center">
                            <span class="text-gray-800 fw-bold fs-5">$ 650</span>
                            <span class="mx-2 text-muted">for</span>
                            <span class="text-gray-800 fw-bold fs-5 me-3">7</span>
                            <a href="#"
                                class="btn btn-sm btn-light-success btn-icon-success btn-icon w-25px h-25px me-2">
                                <i class="ki-duotone ki-minus fs-4"></i>
                            </a>
                            <a href="#" class="btn btn-sm btn-light-success btn-icon w-25px h-25px">
                                <i class="ki-duotone ki-plus fs-4"></i>
                            </a>
                        </div>
                        <!--end::Wrapper-->
                    </div>
                    <!--end::Wrapper-->
                    <!--begin::Pic-->
                    <div class="flex-shrink-0 symbol symbol-70px symbol-2by3">
                        <img src="assets/media/stock/600x400/img-21.jpg" alt="" />
                    </div>
                    <!--end::Pic-->
                </div>
                <!--end::Item-->
                <!--begin::Separator-->
                <div class="my-6 separator separator-dashed"></div>
                <!--end::Separator-->
                <!--begin::Item-->
                <div class="d-flex flex-stack">
                    <!--begin::Wrapper-->
                    <div class="d-flex flex-column me-3">
                        <!--begin::Section-->
                        <div class="mb-3">
                            <a href="apps/ecommerce/sales/details.html"
                                class="text-gray-800 text-hover-primary fs-4 fw-bold">Samsung</a>
                            <span class="text-gray-500 fw-semibold d-block">Profile info,Timeline etc</span>
                        </div>
                        <!--end::Section-->
                        <!--begin::Section-->
                        <div class="d-flex align-items-center">
                            <span class="text-gray-800 fw-bold fs-5">$ 720</span>
                            <span class="mx-2 text-muted">for</span>
                            <span class="text-gray-800 fw-bold fs-5 me-3">6</span>
                            <a href="#"
                                class="btn btn-sm btn-light-success btn-icon-success btn-icon w-25px h-25px me-2">
                                <i class="ki-duotone ki-minus fs-4"></i>
                            </a>
                            <a href="#" class="btn btn-sm btn-light-success btn-icon w-25px h-25px">
                                <i class="ki-duotone ki-plus fs-4"></i>
                            </a>
                        </div>
                        <!--end::Wrapper-->
                    </div>
                    <!--end::Wrapper-->
                    <!--begin::Pic-->
                    <div class="flex-shrink-0 symbol symbol-70px symbol-2by3">
                        <img src="assets/media/stock/600x400/img-34.jpg" alt="" />
                    </div>
                    <!--end::Pic-->
                </div>
                <!--end::Item-->
                <!--begin::Separator-->
                <div class="my-6 separator separator-dashed"></div>
                <!--end::Separator-->
                <!--begin::Item-->
                <div class="d-flex flex-stack">
                    <!--begin::Wrapper-->
                    <div class="d-flex flex-column me-3">
                        <!--begin::Section-->
                        <div class="mb-3">
                            <a href="apps/ecommerce/sales/details.html"
                                class="text-gray-800 text-hover-primary fs-4 fw-bold">$D Printer</a>
                            <span class="text-gray-500 fw-semibold d-block">Manfactoring unique objekts</span>
                        </div>
                        <!--end::Section-->
                        <!--begin::Section-->
                        <div class="d-flex align-items-center">
                            <span class="text-gray-800 fw-bold fs-5">$ 430</span>
                            <span class="mx-2 text-muted">for</span>
                            <span class="text-gray-800 fw-bold fs-5 me-3">8</span>
                            <a href="#"
                                class="btn btn-sm btn-light-success btn-icon-success btn-icon w-25px h-25px me-2">
                                <i class="ki-duotone ki-minus fs-4"></i>
                            </a>
                            <a href="#" class="btn btn-sm btn-light-success btn-icon w-25px h-25px">
                                <i class="ki-duotone ki-plus fs-4"></i>
                            </a>
                        </div>
                        <!--end::Wrapper-->
                    </div>
                    <!--end::Wrapper-->
                    <!--begin::Pic-->
                    <div class="flex-shrink-0 symbol symbol-70px symbol-2by3">
                        <img src="assets/media/stock/600x400/img-27.jpg" alt="" />
                    </div>
                    <!--end::Pic-->
                </div>
                <!--end::Item-->
            </div>
            <!--end::Card body-->
            <!--begin::Card footer-->
            <div class="card-footer">
                <!--begin::Item-->
                <div class="d-flex flex-stack">
                    <span class="text-gray-600 fw-bold">Total</span>
                    <span class="text-gray-800 fw-bolder fs-5">$ 1840.00</span>
                </div>
                <!--end::Item-->
                <!--begin::Item-->
                <div class="d-flex flex-stack">
                    <span class="text-gray-600 fw-bold">Sub total</span>
                    <span class="text-primary fw-bolder fs-5">$ 246.35</span>
                </div>
                <!--end::Item-->
                <!--end::Action-->
                <div class="mt-9 d-flex justify-content-end">
                    <a href="#" class="btn btn-primary d-flex justify-content-end">Pleace Order</a>
                </div>
                <!--end::Action-->
            </div>
            <!--end::Card footer-->
        </div>
        <!--end::Messenger-->
    </div>
    <!--end::Chat drawer-->
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
    <!--begin::Modal - Upgrade plan-->
    <div class="modal fade" id="kt_modal_upgrade_plan" tabindex="-1" aria-hidden="true">
        <!--begin::Modal dialog-->
        <div class="modal-dialog modal-xl">
            <!--begin::Modal content-->
            <div class="rounded modal-content">
                <!--begin::Modal header-->
                <div class="pb-0 border-0 modal-header justify-content-end">
                    <!--begin::Close-->
                    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                        <i class="ki-duotone ki-cross fs-1">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </div>
                    <!--end::Close-->
                </div>
                <!--end::Modal header-->
                <!--begin::Modal body-->
                <div class="px-5 pt-0 modal-body pb-15 px-xl-20">
                    <!--begin::Heading-->
                    <div class="text-center mb-13">
                        <h1 class="mb-3">Upgrade a Plan</h1>
                        <div class="text-muted fw-semibold fs-5">If you need more info, please check
                            <a href="#" class="link-primary fw-bold">Pricing Guidelines</a>.
                        </div>
                    </div>
                    <!--end::Heading-->
                    <!--begin::Plans-->
                    <div class="d-flex flex-column">
                        <!--begin::Nav group-->
                        <div class="mx-auto nav-group nav-group-outline" data-kt-buttons="true">
                            <button
                                class="px-6 py-3 btn btn-color-gray-500 btn-active btn-active-secondary me-2 active"
                                data-kt-plan="month">Monthly</button>
                            <button class="px-6 py-3 btn btn-color-gray-500 btn-active btn-active-secondary"
                                data-kt-plan="annual">Annual</button>
                        </div>
                        <!--end::Nav group-->
                        <!--begin::Row-->
                        <div class="mt-10 row">
                            <!--begin::Col-->
                            <div class="mb-10 col-lg-6 mb-lg-0">
                                <!--begin::Tabs-->
                                <div class="nav flex-column">
                                    <!--begin::Tab link-->
                                    <label
                                        class="p-6 mb-6 nav-link btn btn-outline btn-outline-dashed btn-color-dark btn-active btn-active-primary d-flex flex-stack text-start active"
                                        data-bs-toggle="tab" data-bs-target="#kt_upgrade_plan_startup">
                                        <!--end::Description-->
                                        <div class="d-flex align-items-center me-2">
                                            <!--begin::Radio-->
                                            <div
                                                class="flex-shrink-0 form-check form-check-custom form-check-solid form-check-success me-6">
                                                <input class="form-check-input" type="radio" name="plan"
                                                    checked="checked" value="startup" />
                                            </div>
                                            <!--end::Radio-->
                                            <!--begin::Info-->
                                            <div class="flex-grow-1">
                                                <div class="flex-wrap d-flex align-items-center fs-2 fw-bold">Startup
                                                </div>
                                                <div class="opacity-75 fw-semibold">Best for startups</div>
                                            </div>
                                            <!--end::Info-->
                                        </div>
                                        <!--end::Description-->
                                        <!--begin::Price-->
                                        <div class="ms-5">
                                            <span class="mb-2">$</span>
                                            <span class="fs-3x fw-bold" data-kt-plan-price-month="39"
                                                data-kt-plan-price-annual="399">39</span>
                                            <span class="opacity-50 fs-7">/
                                                <span data-kt-element="period">Mon</span></span>
                                        </div>
                                        <!--end::Price-->
                                    </label>
                                    <!--end::Tab link-->
                                    <!--begin::Tab link-->
                                    <label
                                        class="p-6 mb-6 nav-link btn btn-outline btn-outline-dashed btn-color-dark btn-active btn-active-primary d-flex flex-stack text-start"
                                        data-bs-toggle="tab" data-bs-target="#kt_upgrade_plan_advanced">
                                        <!--end::Description-->
                                        <div class="d-flex align-items-center me-2">
                                            <!--begin::Radio-->
                                            <div
                                                class="flex-shrink-0 form-check form-check-custom form-check-solid form-check-success me-6">
                                                <input class="form-check-input" type="radio" name="plan"
                                                    value="advanced" />
                                            </div>
                                            <!--end::Radio-->
                                            <!--begin::Info-->
                                            <div class="flex-grow-1">
                                                <div class="flex-wrap d-flex align-items-center fs-2 fw-bold">Advanced
                                                </div>
                                                <div class="opacity-75 fw-semibold">Best for 100+ team size</div>
                                            </div>
                                            <!--end::Info-->
                                        </div>
                                        <!--end::Description-->
                                        <!--begin::Price-->
                                        <div class="ms-5">
                                            <span class="mb-2">$</span>
                                            <span class="fs-3x fw-bold" data-kt-plan-price-month="339"
                                                data-kt-plan-price-annual="3399">339</span>
                                            <span class="opacity-50 fs-7">/
                                                <span data-kt-element="period">Mon</span></span>
                                        </div>
                                        <!--end::Price-->
                                    </label>
                                    <!--end::Tab link-->
                                    <!--begin::Tab link-->
                                    <label
                                        class="p-6 mb-6 nav-link btn btn-outline btn-outline-dashed btn-color-dark btn-active btn-active-primary d-flex flex-stack text-start"
                                        data-bs-toggle="tab" data-bs-target="#kt_upgrade_plan_enterprise">
                                        <!--end::Description-->
                                        <div class="d-flex align-items-center me-2">
                                            <!--begin::Radio-->
                                            <div
                                                class="flex-shrink-0 form-check form-check-custom form-check-solid form-check-success me-6">
                                                <input class="form-check-input" type="radio" name="plan"
                                                    value="enterprise" />
                                            </div>
                                            <!--end::Radio-->
                                            <!--begin::Info-->
                                            <div class="flex-grow-1">
                                                <div class="flex-wrap d-flex align-items-center fs-2 fw-bold">
                                                    Enterprise
                                                    <span
                                                        class="px-3 py-2 badge badge-light-success ms-2 fs-7">Popular</span>
                                                </div>
                                                <div class="opacity-75 fw-semibold">Best value for 1000+ team</div>
                                            </div>
                                            <!--end::Info-->
                                        </div>
                                        <!--end::Description-->
                                        <!--begin::Price-->
                                        <div class="ms-5">
                                            <span class="mb-2">$</span>
                                            <span class="fs-3x fw-bold" data-kt-plan-price-month="999"
                                                data-kt-plan-price-annual="9999">999</span>
                                            <span class="opacity-50 fs-7">/
                                                <span data-kt-element="period">Mon</span></span>
                                        </div>
                                        <!--end::Price-->
                                    </label>
                                    <!--end::Tab link-->
                                    <!--begin::Tab link-->
                                    <label
                                        class="p-6 mb-6 nav-link btn btn-outline btn-outline-dashed btn-color-dark btn-active btn-active-primary d-flex flex-stack text-start"
                                        data-bs-toggle="tab" data-bs-target="#kt_upgrade_plan_custom">
                                        <!--end::Description-->
                                        <div class="d-flex align-items-center me-2">
                                            <!--begin::Radio-->
                                            <div
                                                class="flex-shrink-0 form-check form-check-custom form-check-solid form-check-success me-6">
                                                <input class="form-check-input" type="radio" name="plan"
                                                    value="custom" />
                                            </div>
                                            <!--end::Radio-->
                                            <!--begin::Info-->
                                            <div class="flex-grow-1">
                                                <div class="flex-wrap d-flex align-items-center fs-2 fw-bold">Custom
                                                </div>
                                                <div class="opacity-75 fw-semibold">Requet a custom license</div>
                                            </div>
                                            <!--end::Info-->
                                        </div>
                                        <!--end::Description-->
                                        <!--begin::Price-->
                                        <div class="ms-5">
                                            <a href="#" class="btn btn-sm btn-success">Contact Us</a>
                                        </div>
                                        <!--end::Price-->
                                    </label>
                                    <!--end::Tab link-->
                                </div>
                                <!--end::Tabs-->
                            </div>
                            <!--end::Col-->
                            <!--begin::Col-->
                            <div class="col-lg-6">
                                <!--begin::Tab content-->
                                <div class="p-10 rounded tab-content h-100 bg-light">
                                    <!--begin::Tab Pane-->
                                    <div class="tab-pane fade show active" id="kt_upgrade_plan_startup">
                                        <!--begin::Heading-->
                                        <div class="pb-5">
                                            <h2 class="text-gray-900 fw-bold">What’s in Startup Plan?</h2>
                                            <div class="text-muted fw-semibold">Optimal for 10+ team size and new
                                                startup</div>
                                        </div>
                                        <!--end::Heading-->
                                        <!--begin::Body-->
                                        <div class="pt-1">
                                            <!--begin::Item-->
                                            <div class="mb-7 d-flex align-items-center">
                                                <span class="text-gray-700 fw-semibold fs-5 flex-grow-1">Up to 10
                                                    Active Users</span>
                                                <i class="ki-duotone ki-check-circle fs-1 text-success">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </div>
                                            <!--end::Item-->
                                            <!--begin::Item-->
                                            <div class="mb-7 d-flex align-items-center">
                                                <span class="text-gray-700 fw-semibold fs-5 flex-grow-1">Up to 30
                                                    Project Integrations</span>
                                                <i class="ki-duotone ki-check-circle fs-1 text-success">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </div>
                                            <!--end::Item-->
                                            <!--begin::Item-->
                                            <div class="mb-7 d-flex align-items-center">
                                                <span class="text-gray-700 fw-semibold fs-5 flex-grow-1">Analytics
                                                    Module</span>
                                                <i class="ki-duotone ki-check-circle fs-1 text-success">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </div>
                                            <!--end::Item-->
                                            <!--begin::Item-->
                                            <div class="mb-7 d-flex align-items-center">
                                                <span class="fw-semibold fs-5 text-muted flex-grow-1">Finance
                                                    Module</span>
                                                <i class="ki-duotone ki-cross-circle fs-1">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </div>
                                            <!--end::Item-->
                                            <!--begin::Item-->
                                            <div class="mb-7 d-flex align-items-center">
                                                <span class="fw-semibold fs-5 text-muted flex-grow-1">Accounting
                                                    Module</span>
                                                <i class="ki-duotone ki-cross-circle fs-1">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </div>
                                            <!--end::Item-->
                                            <!--begin::Item-->
                                            <div class="mb-7 d-flex align-items-center">
                                                <span class="fw-semibold fs-5 text-muted flex-grow-1">Network
                                                    Platform</span>
                                                <i class="ki-duotone ki-cross-circle fs-1">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </div>
                                            <!--end::Item-->
                                            <!--begin::Item-->
                                            <div class="d-flex align-items-center">
                                                <span class="fw-semibold fs-5 text-muted flex-grow-1">Unlimited Cloud
                                                    Space</span>
                                                <i class="ki-duotone ki-cross-circle fs-1">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </div>
                                            <!--end::Item-->
                                        </div>
                                        <!--end::Body-->
                                    </div>
                                    <!--end::Tab Pane-->
                                    <!--begin::Tab Pane-->
                                    <div class="tab-pane fade" id="kt_upgrade_plan_advanced">
                                        <!--begin::Heading-->
                                        <div class="pb-5">
                                            <h2 class="text-gray-900 fw-bold">What’s in Startup Plan?</h2>
                                            <div class="text-muted fw-semibold">Optimal for 100+ team size and grown
                                                company</div>
                                        </div>
                                        <!--end::Heading-->
                                        <!--begin::Body-->
                                        <div class="pt-1">
                                            <!--begin::Item-->
                                            <div class="mb-7 d-flex align-items-center">
                                                <span class="text-gray-700 fw-semibold fs-5 flex-grow-1">Up to 10
                                                    Active Users</span>
                                                <i class="ki-duotone ki-check-circle fs-1 text-success">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </div>
                                            <!--end::Item-->
                                            <!--begin::Item-->
                                            <div class="mb-7 d-flex align-items-center">
                                                <span class="text-gray-700 fw-semibold fs-5 flex-grow-1">Up to 30
                                                    Project Integrations</span>
                                                <i class="ki-duotone ki-check-circle fs-1 text-success">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </div>
                                            <!--end::Item-->
                                            <!--begin::Item-->
                                            <div class="mb-7 d-flex align-items-center">
                                                <span class="text-gray-700 fw-semibold fs-5 flex-grow-1">Analytics
                                                    Module</span>
                                                <i class="ki-duotone ki-check-circle fs-1 text-success">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </div>
                                            <!--end::Item-->
                                            <!--begin::Item-->
                                            <div class="mb-7 d-flex align-items-center">
                                                <span class="text-gray-700 fw-semibold fs-5 flex-grow-1">Finance
                                                    Module</span>
                                                <i class="ki-duotone ki-check-circle fs-1 text-success">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </div>
                                            <!--end::Item-->
                                            <!--begin::Item-->
                                            <div class="mb-7 d-flex align-items-center">
                                                <span class="text-gray-700 fw-semibold fs-5 flex-grow-1">Accounting
                                                    Module</span>
                                                <i class="ki-duotone ki-check-circle fs-1 text-success">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </div>
                                            <!--end::Item-->
                                            <!--begin::Item-->
                                            <div class="mb-7 d-flex align-items-center">
                                                <span class="fw-semibold fs-5 text-muted flex-grow-1">Network
                                                    Platform</span>
                                                <i class="ki-duotone ki-cross-circle fs-1">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </div>
                                            <!--end::Item-->
                                            <!--begin::Item-->
                                            <div class="d-flex align-items-center">
                                                <span class="fw-semibold fs-5 text-muted flex-grow-1">Unlimited Cloud
                                                    Space</span>
                                                <i class="ki-duotone ki-cross-circle fs-1">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </div>
                                            <!--end::Item-->
                                        </div>
                                        <!--end::Body-->
                                    </div>
                                    <!--end::Tab Pane-->
                                    <!--begin::Tab Pane-->
                                    <div class="tab-pane fade" id="kt_upgrade_plan_enterprise">
                                        <!--begin::Heading-->
                                        <div class="pb-5">
                                            <h2 class="text-gray-900 fw-bold">What’s in Startup Plan?</h2>
                                            <div class="text-muted fw-semibold">Optimal for 1000+ team and enterpise
                                            </div>
                                        </div>
                                        <!--end::Heading-->
                                        <!--begin::Body-->
                                        <div class="pt-1">
                                            <!--begin::Item-->
                                            <div class="mb-7 d-flex align-items-center">
                                                <span class="text-gray-700 fw-semibold fs-5 flex-grow-1">Up to 10
                                                    Active Users</span>
                                                <i class="ki-duotone ki-check-circle fs-1 text-success">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </div>
                                            <!--end::Item-->
                                            <!--begin::Item-->
                                            <div class="mb-7 d-flex align-items-center">
                                                <span class="text-gray-700 fw-semibold fs-5 flex-grow-1">Up to 30
                                                    Project Integrations</span>
                                                <i class="ki-duotone ki-check-circle fs-1 text-success">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </div>
                                            <!--end::Item-->
                                            <!--begin::Item-->
                                            <div class="mb-7 d-flex align-items-center">
                                                <span class="text-gray-700 fw-semibold fs-5 flex-grow-1">Analytics
                                                    Module</span>
                                                <i class="ki-duotone ki-check-circle fs-1 text-success">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </div>
                                            <!--end::Item-->
                                            <!--begin::Item-->
                                            <div class="mb-7 d-flex align-items-center">
                                                <span class="text-gray-700 fw-semibold fs-5 flex-grow-1">Finance
                                                    Module</span>
                                                <i class="ki-duotone ki-check-circle fs-1 text-success">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </div>
                                            <!--end::Item-->
                                            <!--begin::Item-->
                                            <div class="mb-7 d-flex align-items-center">
                                                <span class="text-gray-700 fw-semibold fs-5 flex-grow-1">Accounting
                                                    Module</span>
                                                <i class="ki-duotone ki-check-circle fs-1 text-success">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </div>
                                            <!--end::Item-->
                                            <!--begin::Item-->
                                            <div class="mb-7 d-flex align-items-center">
                                                <span class="text-gray-700 fw-semibold fs-5 flex-grow-1">Network
                                                    Platform</span>
                                                <i class="ki-duotone ki-check-circle fs-1 text-success">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </div>
                                            <!--end::Item-->
                                            <!--begin::Item-->
                                            <div class="d-flex align-items-center">
                                                <span class="text-gray-700 fw-semibold fs-5 flex-grow-1">Unlimited
                                                    Cloud Space</span>
                                                <i class="ki-duotone ki-check-circle fs-1 text-success">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </div>
                                            <!--end::Item-->
                                        </div>
                                        <!--end::Body-->
                                    </div>
                                    <!--end::Tab Pane-->
                                    <!--begin::Tab Pane-->
                                    <div class="tab-pane fade" id="kt_upgrade_plan_custom">
                                        <!--begin::Heading-->
                                        <div class="pb-5">
                                            <h2 class="text-gray-900 fw-bold">What’s in Startup Plan?</h2>
                                            <div class="text-muted fw-semibold">Optimal for corporations</div>
                                        </div>
                                        <!--end::Heading-->
                                        <!--begin::Body-->
                                        <div class="pt-1">
                                            <!--begin::Item-->
                                            <div class="mb-7 d-flex align-items-center">
                                                <span class="text-gray-700 fw-semibold fs-5 flex-grow-1">Unlimited
                                                    Users</span>
                                                <i class="ki-duotone ki-check-circle fs-1 text-success">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </div>
                                            <!--end::Item-->
                                            <!--begin::Item-->
                                            <div class="mb-7 d-flex align-items-center">
                                                <span class="text-gray-700 fw-semibold fs-5 flex-grow-1">Unlimited
                                                    Project Integrations</span>
                                                <i class="ki-duotone ki-check-circle fs-1 text-success">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </div>
                                            <!--end::Item-->
                                            <!--begin::Item-->
                                            <div class="mb-7 d-flex align-items-center">
                                                <span class="text-gray-700 fw-semibold fs-5 flex-grow-1">Analytics
                                                    Module</span>
                                                <i class="ki-duotone ki-check-circle fs-1 text-success">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </div>
                                            <!--end::Item-->
                                            <!--begin::Item-->
                                            <div class="mb-7 d-flex align-items-center">
                                                <span class="text-gray-700 fw-semibold fs-5 flex-grow-1">Finance
                                                    Module</span>
                                                <i class="ki-duotone ki-check-circle fs-1 text-success">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </div>
                                            <!--end::Item-->
                                            <!--begin::Item-->
                                            <div class="mb-7 d-flex align-items-center">
                                                <span class="text-gray-700 fw-semibold fs-5 flex-grow-1">Accounting
                                                    Module</span>
                                                <i class="ki-duotone ki-check-circle fs-1 text-success">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </div>
                                            <!--end::Item-->
                                            <!--begin::Item-->
                                            <div class="mb-7 d-flex align-items-center">
                                                <span class="text-gray-700 fw-semibold fs-5 flex-grow-1">Network
                                                    Platform</span>
                                                <i class="ki-duotone ki-check-circle fs-1 text-success">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </div>
                                            <!--end::Item-->
                                            <!--begin::Item-->
                                            <div class="d-flex align-items-center">
                                                <span class="text-gray-700 fw-semibold fs-5 flex-grow-1">Unlimited
                                                    Cloud Space</span>
                                                <i class="ki-duotone ki-check-circle fs-1 text-success">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </div>
                                            <!--end::Item-->
                                        </div>
                                        <!--end::Body-->
                                    </div>
                                    <!--end::Tab Pane-->
                                </div>
                                <!--end::Tab content-->
                            </div>
                            <!--end::Col-->
                        </div>
                        <!--end::Row-->
                    </div>
                    <!--end::Plans-->
                    <!--begin::Actions-->
                    <div class="pt-12 d-flex flex-center flex-row-fluid">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="kt_modal_upgrade_plan_btn">
                            <!--begin::Indicator label-->
                            <span class="indicator-label">Upgrade Plan</span>
                            <!--end::Indicator label-->
                            <!--begin::Indicator progress-->
                            <span class="indicator-progress">Please wait...
                                <span class="align-middle spinner-border spinner-border-sm ms-2"></span></span>
                            <!--end::Indicator progress-->
                        </button>
                    </div>
                    <!--end::Actions-->
                </div>
                <!--end::Modal body-->
            </div>
            <!--end::Modal content-->
        </div>
        <!--end::Modal dialog-->
    </div>
    <!--end::Modal - Upgrade plan-->
   
    <!--end::Modals-->
    <x-partials.dash-footer />
    <!--begin::Javascript-->
