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
                            Edit Asset - {{ $asset->asset_tag }}
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
                            <li class="breadcrumb-item text-muted">Edit</li>
                        </ul>
                        <!--end::Breadcrumb-->
                    </div>
                    <!--end::Page title-->
                    <!--begin::Actions-->
                    <div class="d-flex align-items-center gap-2 gap-lg-3">
                        <a href="{{ route('admin.assets.show', $asset) }}" class="btn btn-sm fw-bold btn-secondary">
                            <i class="ki-duotone ki-arrow-left fs-2"></i>Cancel
                        </a>
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
                    <!--begin::Card-->
                    <div class="card">
                        <!--begin::Card header-->
                        <div class="card-header">
                            <!--begin::Card title-->
                            <div class="card-title fs-3 fw-bold">Asset Information</div>
                            <!--end::Card title-->
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-0">
                            <!-- Flash Messages -->
                            @if (session('success'))
                                <div class="alert alert-dismissible alert-success d-flex flex-column flex-sm-row p-5 mb-10">
                                    <i class="ki-duotone ki-check fs-2hx text-success me-4 mb-5 mb-sm-0">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    <div class="d-flex flex-column text-light pe-0 pe-sm-10">
                                        <h4 class="mb-2 text-dark">Success!</h4>
                                        <span class="text-dark">{{ session('success') }}</span>
                                    </div>
                                    <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto" data-bs-dismiss="alert">
                                        <i class="ki-duotone ki-cross fs-1 text-success"><span class="path1"></span><span class="path2"></span></i>
                                    </button>
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="alert alert-dismissible alert-danger d-flex flex-column flex-sm-row p-5 mb-10">
                                    <i class="ki-duotone ki-cross-circle fs-2hx text-danger me-4 mb-5 mb-sm-0">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    <div class="d-flex flex-column text-light pe-0 pe-sm-10">
                                        <h4 class="mb-2 text-dark">Error!</h4>
                                        <span class="text-dark">{{ session('error') }}</span>
                                    </div>
                                    <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto" data-bs-dismiss="alert">
                                        <i class="ki-duotone ki-cross fs-1 text-danger"><span class="path1"></span><span class="path2"></span></i>
                                    </button>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('admin.assets.update', $asset) }}" class="form">
                                @csrf
                                @method('PATCH')

                                <div class="row mb-7">
                                    <div class="col-md-6 fv-row">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-semibold mb-2">Asset Tag</label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input type="text" class="form-control form-control-solid bg-light" value="{{ $asset->asset_tag }}" readonly>
                                        <!--end::Input-->
                                        <div class="text-muted fs-7 mt-1">Asset tags cannot be changed after creation.</div>
                                    </div>
                                    <div class="col-md-6 fv-row">
                                        <!--begin::Label-->
                                        <label class="required fs-6 fw-semibold mb-2">Asset Name</label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input type="text" class="form-control form-control-solid @error('name') is-invalid @enderror" name="name" value="{{ old('name', $asset->name) }}" required placeholder="Enter asset name">
                                        <!--end::Input-->
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-7">
                                    <div class="col-md-6 fv-row">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-semibold mb-2">Category</label>
                                        <!--end::Label-->
                                        <!--begin::Select-->
                                        <select name="category_id" class="form-select form-select-solid @error('category_id') is-invalid @enderror" data-control="select2" data-placeholder="Select a category">
                                            <option value="">Select a category</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}" {{ old('category_id', $asset->category_id) == $category->id ? 'selected' : '' }}>
                                                    {{ $category->full_path }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <!--end::Select-->
                                        @error('category_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 fv-row">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-semibold mb-2">Location</label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input type="text" class="form-control form-control-solid @error('location') is-invalid @enderror" name="location" value="{{ old('location', $asset->location) }}" placeholder="e.g., Room 101, Building A">
                                        <!--end::Input-->
                                        @error('location')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-7">
                                    <div class="col-md-6 fv-row">
                                        <!--begin::Label-->
                                        <label class="required fs-6 fw-semibold mb-2">State</label>
                                        <!--end::Label-->
                                        <!--begin::Select-->
                                        <select name="state" class="form-select form-select-solid @error('state') is-invalid @enderror" required data-control="select2" data-placeholder="Select asset state">
                                            <option value="new" {{ old('state', $asset->state) == 'new' ? 'selected' : '' }}>New</option>
                                            <option value="in_use" {{ old('state', $asset->state) == 'in_use' ? 'selected' : '' }}>In Use</option>
                                            <option value="damaged" {{ old('state', $asset->state) == 'damaged' ? 'selected' : '' }}>Damaged</option>
                                            <option value="repaired" {{ old('state', $asset->state) == 'repaired' ? 'selected' : '' }}>Repaired</option>
                                            <option value="disposed" {{ old('state', $asset->state) == 'disposed' ? 'selected' : '' }}>Disposed</option>
                                            <option value="lost" {{ old('state', $asset->state) == 'lost' ? 'selected' : '' }}>Lost</option>
                                        </select>
                                        <!--end::Select-->
                                        @error('state')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 fv-row">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-semibold mb-2">Purchase Date</label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input type="date" class="form-control form-control-solid @error('purchase_date') is-invalid @enderror" name="purchase_date" value="{{ old('purchase_date', $asset->purchase_date ? $asset->purchase_date->format('Y-m-d') : '') }}">
                                        <!--end::Input-->
                                        @error('purchase_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-7">
                                    <div class="col-md-6 fv-row">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-semibold mb-2">Purchase Price</label>
                                        <!--end::Label-->
                                        <!--begin::Input group-->
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="ki-duotone ki-dollar fs-2">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                </i>
                                            </span>
                                            <input type="number" step="0.01" min="0" class="form-control form-control-solid @error('purchase_price') is-invalid @enderror" name="purchase_price" value="{{ old('purchase_price', $asset->purchase_price) }}" placeholder="0.00">
                                        </div>
                                        <!--end::Input group-->
                                        @error('purchase_price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 fv-row">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-semibold mb-2">Current Value</label>
                                        <!--end::Label-->
                                        <!--begin::Input group-->
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="ki-duotone ki-dollar fs-2">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                </i>
                                            </span>
                                            <input type="number" step="0.01" min="0" class="form-control form-control-solid @error('current_value') is-invalid @enderror" name="current_value" value="{{ old('current_value', $asset->current_value) }}" placeholder="0.00">
                                        </div>
                                        <!--end::Input group-->
                                        @error('current_value')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-7">
                                    <div class="col-12 fv-row">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-semibold mb-2">Description</label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <textarea class="form-control form-control-solid @error('description') is-invalid @enderror" name="description" rows="3" placeholder="Enter asset description">{{ old('description', $asset->description) }}</textarea>
                                        <!--end::Input-->
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-7">
                                    <div class="col-12 fv-row">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-semibold mb-2">Notes</label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <textarea class="form-control form-control-solid @error('notes') is-invalid @enderror" name="notes" rows="3" placeholder="Enter any additional notes">{{ old('notes', $asset->notes) }}</textarea>
                                        <!--end::Input-->
                                        @error('notes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!--begin::Actions-->
                                <div class="text-center pt-15">
                                    <a href="{{ route('admin.assets.show', $asset) }}" class="btn btn-light me-3">
                                        <i class="ki-duotone ki-arrow-left fs-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ki-duotone ki-check fs-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>Update Asset
                                    </button>
                                </div>
                                <!--end::Actions-->
                            </form>
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