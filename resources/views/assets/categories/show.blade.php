<x-dashboard.default>
    <!--begin::Main-->
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <!--begin::Content wrapper-->
        <div class="d-flex flex-column flex-column-fluid">
            <!--begin::Toolbar-->
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                <!--begin::Toolbar container-->
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <!--begin::Page title-->
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                        <!--begin::Title-->
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                            Category Details - {{ $assetCategory->name }}
                        </h1>
                        <!--end::Title-->
                        <!--begin::Breadcrumb-->
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.assets.index') }}" class="text-muted text-hover-primary">Assets</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.asset-categories.index') }}" class="text-muted text-hover-primary">Categories</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">Details</li>
                        </ul>
                        <!--end::Breadcrumb-->
                    </div>
                    <!--end::Page title-->
                    <!--begin::Actions-->
                    <div class="d-flex align-items-center gap-2 gap-lg-3">
                        <a href="{{ route('admin.asset-categories.index') }}" class="btn btn-sm fw-bold btn-secondary">
                            <i class="ki-duotone ki-arrow-left fs-2"></i>Back to Categories
                        </a>
                        @hasanyrole(['System', 'Super Admin'])
                        <a href="{{ route('admin.asset-categories.edit', $assetCategory) }}" class="btn btn-sm fw-bold btn-primary">
                            <i class="ki-duotone ki-pencil fs-2"></i>Edit Category
                        </a>
                        @endhasanyrole
                    </div>
                    <!--end::Actions-->
                </div>
                <!--end::Toolbar container-->
            </div>
            <!--end::Toolbar-->

            <!--begin::Content-->
            <div id="kt_app_content" class="app-content flex-column-fluid">
                <!--begin::Content container-->
                <div id="kt_app_content_container" class="app-container container-xxl">
                    <!--begin::Row-->
                    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
                        <!--begin::Col-->
                        <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3 mb-md-5 mb-xl-10">
                            <!--begin::Card widget 20-->
                            <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-50 mb-5 mb-xl-10" style="background-color: #F1416C;background-image:url('{{ asset('assets/media/patterns/vector-1.png') }}')">
                                <!--begin::Header-->
                                <div class="card-header pt-5">
                                    <!--begin::Title-->
                                    <div class="card-title d-flex flex-column">
                                        <!--begin::Amount-->
                                        <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">{{ $assetCategory->assets->count() }}</span>
                                        <!--end::Amount-->
                                        <!--begin::Subtitle-->
                                        <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Total Assets</span>
                                        <!--end::Subtitle-->
                                    </div>
                                    <!--end::Title-->
                                </div>
                                <!--end::Header-->
                                <!--begin::Card body-->
                                <div class="card-body d-flex align-items-end pt-0">
                                    <!--begin::Progress-->
                                    <div class="d-flex align-items-center flex-column mt-3 w-100">
                                        <div class="d-flex justify-content-between fw-bold fs-6 text-white opacity-75 w-100 mt-auto mb-2">
                                            <span>{{ $assetCategory->assets->where('state', 'active')->count() }} Active</span>
                                            <span>{{ $assetCategory->assets->where('state', 'inactive')->count() }} Inactive</span>
                                        </div>
                                    </div>
                                    <!--end::Progress-->
                                </div>
                                <!--end::Card body-->
                            </div>
                            <!--end::Card widget 20-->
                        </div>
                        <!--end::Col-->
                        
                        <!--begin::Col-->
                        <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3 mb-md-5 mb-xl-10">
                            <!--begin::Card widget 7-->
                            <div class="card card-flush h-md-50 mb-5 mb-xl-10">
                                <!--begin::Header-->
                                <div class="card-header pt-5">
                                    <!--begin::Title-->
                                    <div class="card-title d-flex flex-column">
                                        <!--begin::Amount-->
                                        <span class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2">{{ $assetCategory->children->count() }}</span>
                                        <!--end::Amount-->
                                        <!--begin::Subtitle-->
                                        <span class="text-gray-400 pt-1 fw-semibold fs-6">Subcategories</span>
                                        <!--end::Subtitle-->
                                    </div>
                                    <!--end::Title-->
                                </div>
                                <!--end::Header-->
                                <!--begin::Card body-->
                                <div class="card-body pt-2 pb-4 d-flex flex-wrap align-items-center">
                                    <!--begin::Chart-->
                                    <div class="d-flex flex-center me-5 pt-2">
                                        <div id="kt_card_widget_7_chart" style="min-width: 70px; min-height: 70px" data-kt-size="70" data-kt-line="11"></div>
                                    </div>
                                    <!--end::Chart-->
                                    <!--begin::Labels-->
                                    <div class="d-flex flex-column content-justify-center flex-row-fluid">
                                        @if($assetCategory->children->count() > 0)
                                            @foreach($assetCategory->children->take(3) as $child)
                                            <!--begin::Label-->
                                            <div class="d-flex fw-semibold align-items-center">
                                                <!--begin::Bullet-->
                                                <div class="bullet w-8px h-6px rounded-2 bg-primary me-3"></div>
                                                <!--end::Bullet-->
                                                <!--begin::Label-->
                                                <div class="text-gray-500 flex-grow-1 me-4">{{ $child->name }}</div>
                                                <!--end::Label-->
                                                <!--begin::Stats-->
                                                <div class="fw-bolder text-gray-700 text-xxl-end">{{ $child->assets->count() }}</div>
                                                <!--end::Stats-->
                                            </div>
                                            <!--end::Label-->
                                            @endforeach
                                        @else
                                            <div class="text-gray-500">No subcategories</div>
                                        @endif
                                    </div>
                                    <!--end::Labels-->
                                </div>
                                <!--end::Card body-->
                            </div>
                            <!--end::Card widget 7-->
                        </div>
                        <!--end::Col-->
                    </div>
                    <!--end::Row-->

                    <!--begin::Category Information Card-->
                    <div class="card mb-5 mb-xl-10">
                        <!--begin::Card header-->
                        <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_profile_details" aria-expanded="true" aria-controls="kt_account_profile_details">
                            <!--begin::Card title-->
                            <div class="card-title m-0">
                                <h3 class="fw-bold m-0">Category Information</h3>
                            </div>
                            <!--end::Card title-->
                        </div>
                        <!--begin::Card header-->
                        <!--begin::Content-->
                        <div id="kt_account_profile_details" class="collapse show">
                            <!--begin::Card body-->
                            <div class="card-body border-top p-9">
                                <!--begin::Details-->
                                <div class="row mb-7">
                                    <!--begin::Label-->
                                    <label class="col-lg-4 fw-semibold text-muted">Category Name</label>
                                    <!--end::Label-->
                                    <!--begin::Col-->
                                    <div class="col-lg-8">
                                        <span class="fw-bold fs-6 text-gray-800">{{ $assetCategory->name }}</span>
                                    </div>
                                    <!--end::Col-->
                                </div>
                                <!--end::Details-->
                                
                                @if($assetCategory->description)
                                <!--begin::Details-->
                                <div class="row mb-7">
                                    <!--begin::Label-->
                                    <label class="col-lg-4 fw-semibold text-muted">Description</label>
                                    <!--end::Label-->
                                    <!--begin::Col-->
                                    <div class="col-lg-8 fv-row">
                                        <span class="fw-semibold text-gray-800 fs-6">{{ $assetCategory->description }}</span>
                                    </div>
                                    <!--end::Col-->
                                </div>
                                <!--end::Details-->
                                @endif

                                @if($assetCategory->parent)
                                <!--begin::Details-->
                                <div class="row mb-7">
                                    <!--begin::Label-->
                                    <label class="col-lg-4 fw-semibold text-muted">Parent Category</label>
                                    <!--end::Label-->
                                    <!--begin::Col-->
                                    <div class="col-lg-8 fv-row">
                                        <a href="{{ route('admin.asset-categories.show', $assetCategory->parent) }}" class="fw-semibold fs-6 text-gray-800 text-hover-primary">{{ $assetCategory->parent->name }}</a>
                                    </div>
                                    <!--end::Col-->
                                </div>
                                <!--end::Details-->
                                @endif

                                <!--begin::Details-->
                                <div class="row mb-7">
                                    <!--begin::Label-->
                                    <label class="col-lg-4 fw-semibold text-muted">Full Path</label>
                                    <!--end::Label-->
                                    <!--begin::Col-->
                                    <div class="col-lg-8 fv-row">
                                        <span class="fw-semibold text-gray-800 fs-6">{{ $assetCategory->full_path }}</span>
                                    </div>
                                    <!--end::Col-->
                                </div>
                                <!--end::Details-->

                                <!--begin::Details-->
                                <div class="row mb-7">
                                    <!--begin::Label-->
                                    <label class="col-lg-4 fw-semibold text-muted">Created</label>
                                    <!--end::Label-->
                                    <!--begin::Col-->
                                    <div class="col-lg-8 d-flex align-items-center">
                                        <span class="fw-bold fs-6 text-gray-800 me-2">{{ $assetCategory->created_at->format('M j, Y g:i A') }}</span>
                                    </div>
                                    <!--end::Col-->
                                </div>
                                <!--end::Details-->

                                <!--begin::Details-->
                                <div class="row">
                                    <!--begin::Label-->
                                    <label class="col-lg-4 fw-semibold text-muted">Last Modified</label>
                                    <!--end::Label-->
                                    <!--begin::Col-->
                                    <div class="col-lg-8">
                                        <span class="fw-bold fs-6 text-gray-800">{{ $assetCategory->updated_at->format('M j, Y g:i A') }}</span>
                                    </div>
                                    <!--end::Col-->
                                </div>
                                <!--end::Details-->
                            </div>
                            <!--end::Card body-->
                        </div>
                        <!--end::Content-->
                    </div>
                    <!--end::Category Information Card-->

                    @if($assetCategory->children->count() > 0)
                    <!--begin::Subcategories Card-->
                    <div class="card mb-5 mb-xl-10">
                        <!--begin::Card header-->
                        <div class="card-header border-0 pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold fs-3 mb-1">Subcategories</span>
                                <span class="text-muted mt-1 fw-semibold fs-7">{{ $assetCategory->children->count() }} subcategories</span>
                            </h3>
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body py-3">
                            <!--begin::Table container-->
                            <div class="table-responsive">
                                <!--begin::Table-->
                                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                    <!--begin::Table head-->
                                    <thead>
                                        <tr class="fw-bold text-muted">
                                            <th class="min-w-150px">Name</th>
                                            <th class="min-w-140px">Assets Count</th>
                                            <th class="min-w-120px">Created</th>
                                            <th class="min-w-100px text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <!--end::Table head-->
                                    <!--begin::Table body-->
                                    <tbody>
                                        @foreach($assetCategory->children as $child)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="d-flex justify-content-start flex-column">
                                                        <a href="{{ route('admin.asset-categories.show', $child) }}" class="text-dark fw-bold text-hover-primary fs-6">{{ $child->name }}</a>
                                                        @if($child->description)
                                                            <span class="text-muted fw-semibold text-muted d-block fs-7">{{ Str::limit($child->description, 50) }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-dark fw-bold d-block fs-6">{{ $child->assets->count() }}</span>
                                            </td>
                                            <td>
                                                <span class="text-muted fw-semibold text-muted d-block fs-7">{{ $child->created_at->format('M j, Y') }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-end flex-shrink-0">
                                                    <a href="{{ route('admin.asset-categories.show', $child) }}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1">
                                                        <i class="ki-duotone ki-switch fs-2">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                        </i>
                                                    </a>
                                                    @hasanyrole(['System', 'Super Admin'])
                                                    <a href="{{ route('admin.asset-categories.edit', $child) }}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm">
                                                        <i class="ki-duotone ki-pencil fs-2">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                        </i>
                                                    </a>
                                                    @endhasanyrole
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <!--end::Table body-->
                                </table>
                                <!--end::Table-->
                            </div>
                            <!--end::Table container-->
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Subcategories Card-->
                    @endif

                    @if($assetCategory->assets->count() > 0)
                    <!--begin::Assets Card-->
                    <div class="card">
                        <!--begin::Card header-->
                        <div class="card-header border-0 pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold fs-3 mb-1">Assets in this Category</span>
                                <span class="text-muted mt-1 fw-semibold fs-7">{{ $assetCategory->assets->count() }} assets</span>
                            </h3>
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body py-3">
                            <!--begin::Table container-->
                            <div class="table-responsive">
                                <!--begin::Table-->
                                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                    <!--begin::Table head-->
                                    <thead>
                                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                            <th class="min-w-150px px-4 py-3">
                                                <div class="d-flex align-items-center">
                                                    <i class="ki-duotone ki-tag fs-3 me-2 text-muted">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                        <span class="path3"></span>
                                                    </i>
                                                    Asset Tag
                                                </div>
                                            </th>
                                            <th class="min-w-140px px-4 py-3">
                                                <div class="d-flex align-items-center">
                                                    <i class="ki-duotone ki-abstract-26 fs-3 me-2 text-muted">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                    Asset Name
                                                </div>
                                            </th>
                                            <th class="min-w-120px px-4 py-3">
                                                <div class="d-flex align-items-center">
                                                    <i class="ki-duotone ki-status fs-3 me-2 text-muted">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                    Status
                                                </div>
                                            </th>
                                            <th class="min-w-120px px-4 py-3">
                                                <div class="d-flex align-items-center">
                                                    <i class="ki-duotone ki-geolocation fs-3 me-2 text-muted">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                    Location
                                                </div>
                                            </th>
                                            <th class="min-w-100px text-end px-4 py-3">
                                                <div class="d-flex align-items-center justify-content-end">
                                                    <i class="ki-duotone ki-setting-3 fs-3 me-2 text-muted">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                        <span class="path3"></span>
                                                        <span class="path4"></span>
                                                        <span class="path5"></span>
                                                    </i>
                                                    Actions
                                                </div>
                                            </th>
                                        </tr>
                                    </thead>
                                    <!--end::Table head-->
                                    <!--begin::Table body-->
                                    <tbody>
                                        @foreach($assetCategory->assets->take(20) as $asset)
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.assets.show', $asset) }}" class="text-dark fw-bold text-hover-primary d-block fs-6">{{ $asset->asset_tag }}</a>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="d-flex justify-content-start flex-column">
                                                        <span class="text-dark fw-bold d-block fs-6">{{ $asset->name }}</span>
                                                        @if($asset->serial_number)
                                                            <span class="text-muted fw-semibold text-muted d-block fs-7">SN: {{ $asset->serial_number }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @php
                                                    $stateColors = [
                                                        'new' => 'success',
                                                        'in_use' => 'primary',
                                                        'damaged' => 'danger',
                                                        'repaired' => 'warning',
                                                        'disposed' => 'dark',
                                                        'lost' => 'danger'
                                                    ];
                                                    $color = $stateColors[$asset->state] ?? 'secondary';
                                                @endphp
                                                <span class="badge badge-light-{{ $color }}">{{ ucfirst(str_replace('_', ' ', $asset->state)) }}</span>
                                            </td>
                                            <td>
                                                <span class="text-dark fw-bold d-block fs-6">{{ $asset->location ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-end flex-shrink-0">
                                                    <a href="{{ route('admin.assets.show', $asset) }}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1">
                                                        <i class="ki-duotone ki-switch fs-2">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                        </i>
                                                    </a>
                                                    <a href="{{ route('admin.assets.edit', $asset) }}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm">
                                                        <i class="ki-duotone ki-pencil fs-2">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                        </i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <!--end::Table body-->
                                </table>
                                <!--end::Table-->
                            </div>
                            <!--end::Table container-->
                            
                            @if($assetCategory->assets->count() > 20)
                                <div class="d-flex justify-content-center pt-5">
                                    <a href="{{ route('admin.assets.index', ['category_id' => $assetCategory->id]) }}" class="btn btn-sm btn-light-primary">
                                        View All {{ $assetCategory->assets->count() }} Assets
                                    </a>
                                </div>
                            @endif
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Assets Card-->
                    @endif
                </div>
                <!--end::Content container-->
            </div>
            <!--end::Content-->
        </div>
        <!--end::Content wrapper-->
    </div>
    <!--end::Main-->
</x-dashboard.default>