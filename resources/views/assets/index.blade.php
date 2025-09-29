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
                            Asset Management
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
                            <li class="breadcrumb-item text-muted">Asset Management</li>
                        </ul>
                        <!--end::Breadcrumb-->
                    </div>
                    <!--end::Page title-->
                    <!--begin::Actions-->
                    <div class="d-flex align-items-center gap-2 gap-lg-3">
                        <a href="{{ route('admin.asset-categories.index') }}" class="btn btn-sm fw-bold btn-light-primary">
                            <i class="ki-duotone ki-category fs-2"></i>Categories
                        </a>
                        @hasanyrole(['System', 'Super Admin'])
                        <a href="{{ route('admin.asset-settings.index') }}" class="btn btn-sm fw-bold btn-light-secondary">
                            <i class="ki-duotone ki-setting-3 fs-2"></i>Settings
                        </a>
                        <a href="{{ route('admin.assets.create') }}" class="btn btn-sm fw-bold btn-primary">
                            <i class="ki-duotone ki-plus fs-2"></i>Add Asset
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

                    <!-- Flash Messages -->
                    @if (session('success'))
                        <div class="alert alert-dismissible alert-success d-flex flex-column flex-sm-row p-5 mb-10">
                            <i class="ki-duotone ki-check fs-2hx text-success me-4 mb-5 mb-sm-0">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            <div class="d-flex flex-column text-light pe-0 pe-sm-10">
                                <h4 class="mb-2 text-dark">Success!</h4>
                                <span>{{ session('success') }}</span>
                            </div>
                            <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto" data-bs-dismiss="alert">
                                <i class="ki-duotone ki-cross fs-1 text-success"><span class="path1"></span><span class="path2"></span></i>
                            </button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-dismissible alert-danger d-flex flex-column flex-sm-row p-5 mb-10">
                            <i class="ki-duotone ki-information fs-2hx text-danger me-4 mb-5 mb-sm-0">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                            <div class="d-flex flex-column text-light pe-0 pe-sm-10">
                                <h4 class="mb-2 text-dark">Error!</h4>
                                <span>{{ session('error') }}</span>
                            </div>
                            <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto" data-bs-dismiss="alert">
                                <i class="ki-duotone ki-cross fs-1 text-danger"><span class="path1"></span><span class="path2"></span></i>
                            </button>
                        </div>
                    @endif

                    <!--begin::Card-->
                    <div class="card">
                        <!--begin::Card header-->
                        <div class="card-header border-0 pt-6">
                            <!--begin::Card title-->
                            <div class="card-title">
                                <!--begin::Search-->
                                <div class="d-flex align-items-center position-relative my-1">
                                    <form method="GET" action="{{ route('admin.assets.index') }}" class="d-flex align-items-center">
                                        <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-6">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        <input type="text" name="search" value="{{ request('search') }}" 
                                               placeholder="Search assets..." 
                                               class="form-control form-control-solid w-250px ps-14" />
                                        <button type="submit" class="btn btn-light-primary ms-3">Search</button>
                                    </form>
                                </div>
                                <!--end::Search-->
                            </div>
                            <!--end::Card title-->
                            <!--begin::Card toolbar-->
                            <div class="card-toolbar">
                                <!--begin::Toolbar-->
                                <div class="d-flex justify-content-end" data-kt-asset-table-toolbar="base">
                                    <!--begin::Filter-->
                                    <button type="button" class="btn btn-light-primary me-3" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                        <i class="ki-duotone ki-filter fs-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>Filter
                                    </button>
                                    <!--begin::Menu-->
                                    <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true">
                                        <!--begin::Header-->
                                        <div class="px-7 py-5">
                                            <div class="fs-5 text-dark fw-bold">Filter Options</div>
                                        </div>
                                        <!--end::Header-->
                                        <!--begin::Separator-->
                                        <div class="separator border-gray-200"></div>
                                        <!--end::Separator-->
                                        <!--begin::Content-->
                                        <form method="GET" action="{{ route('admin.assets.index') }}">
                                            <div class="px-7 py-5">
                                                <!--begin::Input group-->
                                                <div class="mb-10">
                                                    <label class="form-label fs-6 fw-semibold">Category:</label>
                                                    <select name="category_id" class="form-select form-select-solid fw-bold" data-kt-select2="true" data-placeholder="Select category" data-allow-clear="true">
                                                        <option></option>
                                                        @foreach($categories as $category)
                                                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                                                {{ $category->full_path }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <!--end::Input group-->
                                                <!--begin::Input group-->
                                                <div class="mb-10">
                                                    <label class="form-label fs-6 fw-semibold">State:</label>
                                                    <select name="state" class="form-select form-select-solid fw-bold" data-kt-select2="true" data-placeholder="Select state" data-allow-clear="true">
                                                        <option></option>
                                                        <option value="new" {{ request('state') == 'new' ? 'selected' : '' }}>New</option>
                                                        <option value="in_use" {{ request('state') == 'in_use' ? 'selected' : '' }}>In Use</option>
                                                        <option value="damaged" {{ request('state') == 'damaged' ? 'selected' : '' }}>Damaged</option>
                                                        <option value="repaired" {{ request('state') == 'repaired' ? 'selected' : '' }}>Repaired</option>
                                                        <option value="disposed" {{ request('state') == 'disposed' ? 'selected' : '' }}>Disposed</option>
                                                        <option value="lost" {{ request('state') == 'lost' ? 'selected' : '' }}>Lost</option>
                                                    </select>
                                                </div>
                                                <!--end::Input group-->
                                                <!--begin::Actions-->
                                                <div class="d-flex justify-content-end">
                                                    <button type="reset" class="btn btn-light btn-active-light-primary fw-semibold me-2 px-6">Reset</button>
                                                    <button type="submit" class="btn btn-primary fw-semibold px-6">Apply</button>
                                                </div>
                                                <!--end::Actions-->
                                            </div>
                                        </form>
                                        <!--end::Content-->
                                    </div>
                                    <!--end::Menu-->
                                    <!--end::Filter-->
                                </div>
                                <!--end::Toolbar-->
                            </div>
                            <!--end::Card toolbar-->
                        </div>
                        <!--end::Card header-->

                        <!--begin::Card body-->
                        <div class="card-body pt-0">
                            <!--begin::Table-->
                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_assets_table">
                                    <!--begin::Table head-->
                                    <thead>
                                        <!--begin::Table row-->
                                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                            <th class="min-w-125px">
                                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'asset_tag', 'direction' => request('sort') == 'asset_tag' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-muted text-hover-primary">
                                                    Asset Tag
                                                    @if(request('sort') == 'asset_tag')
                                                        <i class="ki-duotone ki-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }} fs-5 text-primary"></i>
                                                    @endif
                                                </a>
                                            </th>
                                            <th class="min-w-125px">
                                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => request('sort') == 'name' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-muted text-hover-primary">
                                                    Name
                                                    @if(request('sort') == 'name')
                                                        <i class="ki-duotone ki-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }} fs-5 text-primary"></i>
                                                    @endif
                                                </a>
                                            </th>
                                            <th class="min-w-125px">Category</th>
                                            <th class="min-w-125px">State</th>
                                            <th class="min-w-125px">Location</th>
                                            <th class="min-w-125px">
                                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('sort') == 'created_at' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="text-muted text-hover-primary">
                                                    Created
                                                    @if(request('sort') == 'created_at')
                                                        <i class="ki-duotone ki-arrow-{{ request('direction') == 'asc' ? 'up' : 'down' }} fs-5 text-primary"></i>
                                                    @endif
                                                </a>
                                            </th>
                                            <th class="text-end min-w-100px">Actions</th>
                                        </tr>
                                        <!--end::Table row-->
                                    </thead>
                                    <!--end::Table head-->
                                    <!--begin::Table body-->
                                    <tbody class="text-gray-600 fw-semibold">
                                        @forelse($assets as $asset)
                                            <tr>
                                                <!--begin::Asset Tag-->
                                                <td>
                                                    <a href="{{ route('admin.assets.show', $asset) }}" class="text-dark fw-bold text-hover-primary fs-6">
                                                        {{ $asset->asset_tag }}
                                                    </a>
                                                </td>
                                                <!--end::Asset Tag-->
                                                <!--begin::Name-->
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span class="text-gray-800 fw-bold mb-1">{{ $asset->name }}</span>
                                                        @if($asset->serial_number)
                                                            <span class="text-muted fs-7">SN: {{ $asset->serial_number }}</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <!--end::Name-->
                                                <!--begin::Category-->
                                                <td>
                                                    @if($asset->category)
                                                        <span class="badge badge-light-info">{{ $asset->category->name }}</span>
                                                    @else
                                                        <span class="text-muted">Uncategorized</span>
                                                    @endif
                                                </td>
                                                <!--end::Category-->
                                                <!--begin::State-->
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
                                                <!--end::State-->
                                                <!--begin::Location-->
                                                <td>
                                                    <span class="text-dark fw-bold">{{ $asset->location ?? 'N/A' }}</span>
                                                </td>
                                                <!--end::Location-->
                                                <!--begin::Created-->
                                                <td>
                                                    <span class="text-muted">{{ $asset->created_at->format('M j, Y') }}</span>
                                                </td>
                                                <!--end::Created-->
                                                <!--begin::Action-->
                                                <td class="text-end">
                                                    <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                                        Actions
                                                        <i class="ki-duotone ki-down fs-5 ms-1"></i>
                                                    </a>
                                                    <!--begin::Menu-->
                                                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                                                        <!--begin::Menu item-->
                                                        <div class="menu-item px-3">
                                                            <a href="{{ route('admin.assets.show', $asset) }}" class="menu-link px-3">View</a>
                                                        </div>
                                                        <!--end::Menu item-->
                                                        <!--begin::Menu item-->
                                                        <div class="menu-item px-3">
                                                            <a href="{{ route('admin.assets.edit', $asset) }}" class="menu-link px-3">Edit</a>
                                                        </div>
                                                        <!--end::Menu item-->
                                                        <!--begin::Menu item-->
                                                        <div class="menu-item px-3">
                                                            <form method="POST" action="{{ route('admin.assets.destroy', $asset) }}" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="menu-link px-3 text-danger border-0 bg-transparent" onclick="return confirm('Are you sure you want to delete this asset?')">Delete</button>
                                                            </form>
                                                        </div>
                                                        <!--end::Menu item-->
                                                    </div>
                                                    <!--end::Menu-->
                                                </td>
                                                <!--end::Action-->
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-10">
                                                    <div class="text-center">
                                                        <div class="d-flex flex-center">
                                                            <i class="ki-duotone ki-files fs-1 text-muted mb-5">
                                                                <span class="path1"></span>
                                                                <span class="path2"></span>
                                                            </i>
                                                        </div>
                                                        <div class="fs-6 text-muted">No assets found</div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <!--end::Table body-->
                                </table>
                            </div>
                            <!--end::Table-->
                            
                            @if($assets->hasPages())
                                <!--begin::Pagination-->
                                <div class="d-flex flex-stack flex-wrap pt-10">
                                    <div class="fs-6 fw-semibold text-gray-700">
                                        Showing {{ $assets->firstItem() }} to {{ $assets->lastItem() }} of {{ $assets->total() }} entries
                                    </div>
                                    <div class="d-flex align-items-center">
                                        {{ $assets->withQueryString()->links() }}
                                    </div>
                                </div>
                                <!--end::Pagination-->
                            @endif
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Card-->
                </div>
                <!--end::Content container-->
            </div>
            <!--end::Content-->
        </div>
        <!--end::Content wrapper-->
    </div>
    <!--end::Main-->
</x-dashboard.default>