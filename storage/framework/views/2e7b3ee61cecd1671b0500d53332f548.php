<?php if (isset($component)) { $__componentOriginal78458ab11901cb5e12c9728346766652 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78458ab11901cb5e12c9728346766652 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.default','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.default'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
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
                                <a href="<?php echo e(route('dashboard')); ?>" class="text-muted text-hover-primary">Dashboard</a>
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
                        <a href="<?php echo e(route('admin.asset-categories.index')); ?>" class="btn btn-sm fw-bold btn-light-primary">
                            <i class="ki-duotone ki-category fs-2"></i>Categories
                        </a>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (\Illuminate\Support\Facades\Blade::check('hasanyrole', ['System', 'Super Admin'])): ?>
                        <a href="<?php echo e(route('admin.asset-settings.index')); ?>" class="btn btn-sm fw-bold btn-light-secondary">
                            <i class="ki-duotone ki-setting-3 fs-2"></i>Settings
                        </a>
                        <a href="<?php echo e(route('admin.assets.create')); ?>" class="btn btn-sm fw-bold btn-primary">
                            <i class="ki-duotone ki-plus fs-2"></i>Add Asset
                        </a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
                        <div class="alert alert-dismissible alert-success d-flex flex-column flex-sm-row p-5 mb-10">
                            <i class="ki-duotone ki-check fs-2hx text-success me-4 mb-5 mb-sm-0">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            <div class="d-flex flex-column text-light pe-0 pe-sm-10">
                                <h4 class="mb-2 text-dark">Success!</h4>
                                <span><?php echo e(session('success')); ?></span>
                            </div>
                            <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto" data-bs-dismiss="alert">
                                <i class="ki-duotone ki-cross fs-1 text-success"><span class="path1"></span><span class="path2"></span></i>
                            </button>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
                        <div class="alert alert-dismissible alert-danger d-flex flex-column flex-sm-row p-5 mb-10">
                            <i class="ki-duotone ki-information fs-2hx text-danger me-4 mb-5 mb-sm-0">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                            <div class="d-flex flex-column text-light pe-0 pe-sm-10">
                                <h4 class="mb-2 text-dark">Error!</h4>
                                <span><?php echo e(session('error')); ?></span>
                            </div>
                            <button type="button" class="position-absolute position-sm-relative m-2 m-sm-0 top-0 end-0 btn btn-icon ms-sm-auto" data-bs-dismiss="alert">
                                <i class="ki-duotone ki-cross fs-1 text-danger"><span class="path1"></span><span class="path2"></span></i>
                            </button>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <!--begin::Card-->
                    <div class="card">
                        <!--begin::Card header-->
                        <div class="card-header border-0 pt-6">
                            <!--begin::Card title-->
                            <div class="card-title">
                                <!--begin::Search-->
                                <div class="d-flex align-items-center position-relative my-1">
                                    <form method="GET" action="<?php echo e(route('admin.assets.index')); ?>" class="d-flex align-items-center">
                                        <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-6">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        <input type="text" name="search" value="<?php echo e(request('search')); ?>" 
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
                                        <form method="GET" action="<?php echo e(route('admin.assets.index')); ?>">
                                            <div class="px-7 py-5">
                                                <!--begin::Input group-->
                                                <div class="mb-10">
                                                    <label class="form-label fs-6 fw-semibold">Category:</label>
                                                    <select name="category_id" class="form-select form-select-solid fw-bold" data-kt-select2="true" data-placeholder="Select category" data-allow-clear="true">
                                                        <option></option>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <option value="<?php echo e($category->id); ?>" <?php echo e(request('category_id') == $category->id ? 'selected' : ''); ?>>
                                                                <?php echo e($category->full_path); ?>

                                                            </option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </select>
                                                </div>
                                                <!--end::Input group-->
                                                <!--begin::Input group-->
                                                <div class="mb-10">
                                                    <label class="form-label fs-6 fw-semibold">Department:</label>
                                                    <select name="department_id" class="form-select form-select-solid fw-bold" data-kt-select2="true" data-placeholder="Select department" data-allow-clear="true">
                                                        <option></option>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                            <option value="<?php echo e($department->id); ?>" <?php echo e(request('department_id') == $department->id ? 'selected' : ''); ?>>
                                                                <?php echo e($department->name); ?>

                                                            </option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </select>
                                                </div>
                                                <!--end::Input group-->
                                                <!--begin::Input group-->
                                                <div class="mb-10">
                                                    <label class="form-label fs-6 fw-semibold">State:</label>
                                                    <select name="state" class="form-select form-select-solid fw-bold" data-kt-select2="true" data-placeholder="Select state" data-allow-clear="true">
                                                        <option></option>
                                                        <option value="new" <?php echo e(request('state') == 'new' ? 'selected' : ''); ?>>New</option>
                                                        <option value="in_use" <?php echo e(request('state') == 'in_use' ? 'selected' : ''); ?>>In Use</option>
                                                        <option value="damaged" <?php echo e(request('state') == 'damaged' ? 'selected' : ''); ?>>Damaged</option>
                                                        <option value="repaired" <?php echo e(request('state') == 'repaired' ? 'selected' : ''); ?>>Repaired</option>
                                                        <option value="disposed" <?php echo e(request('state') == 'disposed' ? 'selected' : ''); ?>>Disposed</option>
                                                        <option value="lost" <?php echo e(request('state') == 'lost' ? 'selected' : ''); ?>>Lost</option>
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
                                            <th class="min-w-150px px-4 py-3">
                                                <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'asset_tag', 'direction' => request('sort') == 'asset_tag' && request('direction') == 'asc' ? 'desc' : 'asc'])); ?>" class="text-muted text-hover-primary d-flex align-items-center">
                                                    <i class="ki-duotone ki-tag fs-3 me-2">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                        <span class="path3"></span>
                                                    </i>
                                                    Asset Tag
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request('sort') == 'asset_tag'): ?>
                                                        <i class="ki-duotone ki-arrow-<?php echo e(request('direction') == 'asc' ? 'up' : 'down'); ?> fs-5 text-primary ms-1"></i>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </a>
                                            </th>
                                            <th class="min-w-150px px-4 py-3">
                                                <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'name', 'direction' => request('sort') == 'name' && request('direction') == 'asc' ? 'desc' : 'asc'])); ?>" class="text-muted text-hover-primary d-flex align-items-center">
                                                    <i class="ki-duotone ki-abstract-26 fs-3 me-2">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                    Asset Name
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request('sort') == 'name'): ?>
                                                        <i class="ki-duotone ki-arrow-<?php echo e(request('direction') == 'asc' ? 'up' : 'down'); ?> fs-5 text-primary ms-1"></i>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </a>
                                            </th>
                                            <th class="min-w-140px px-4 py-3">
                                                <div class="d-flex align-items-center">
                                                    <i class="ki-duotone ki-category fs-3 me-2 text-muted">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                        <span class="path3"></span>
                                                        <span class="path4"></span>
                                                    </i>
                                                    Category
                                                </div>
                                            </th>
                                            <th class="min-w-130px px-4 py-3">
                                                <div class="d-flex align-items-center">
                                                    <i class="ki-duotone ki-office-bag fs-3 me-2 text-muted">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                    Department
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
                                            <th class="min-w-140px px-4 py-3">
                                                <div class="d-flex align-items-center">
                                                    <i class="ki-duotone ki-geolocation fs-3 me-2 text-muted">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                    Location
                                                </div>
                                            </th>
                                            <th class="min-w-130px px-4 py-3">
                                                <a href="<?php echo e(request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('sort') == 'created_at' && request('direction') == 'asc' ? 'desc' : 'asc'])); ?>" class="text-muted text-hover-primary d-flex align-items-center">
                                                    <i class="ki-duotone ki-calendar fs-3 me-2">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                    Date Created
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request('sort') == 'created_at'): ?>
                                                        <i class="ki-duotone ki-arrow-<?php echo e(request('direction') == 'asc' ? 'up' : 'down'); ?> fs-5 text-primary ms-1"></i>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </a>
                                            </th>
                                            <th class="text-end min-w-120px px-4 py-3">
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
                                        <!--end::Table row-->
                                    </thead>
                                    <!--end::Table head-->
                                    <!--begin::Table body-->
                                    <tbody class="text-gray-600 fw-semibold">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $assets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $asset): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <tr class="border-bottom border-gray-200">
                                                <!--begin::Asset Tag-->
                                                <td class="px-4 py-4">
                                                    <a href="<?php echo e(route('admin.assets.show', $asset)); ?>" class="text-dark fw-bold text-hover-primary fs-6 d-flex align-items-center">
                                                        <div class="symbol symbol-45px me-3">
                                                            <div class="symbol-label bg-light-primary">
                                                                <i class="ki-duotone ki-tag fs-2 text-primary">
                                                                    <span class="path1"></span>
                                                                    <span class="path2"></span>
                                                                    <span class="path3"></span>
                                                                </i>
                                                            </div>
                                                        </div>
                                                        <div class="fw-bold"><?php echo e($asset->asset_tag); ?></div>
                                                    </a>
                                                </td>
                                                <!--end::Asset Tag-->
                                                <!--begin::Name-->
                                                <td class="px-4 py-4">
                                                    <div class="d-flex flex-column">
                                                        <span class="text-gray-800 fw-bold fs-6 mb-1"><?php echo e($asset->name); ?></span>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($asset->serial_number): ?>
                                                            <span class="text-muted fs-7">SN: <?php echo e($asset->serial_number); ?></span>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </div>
                                                </td>
                                                <!--end::Name-->
                                                <!--begin::Category-->
                                                <td class="px-4 py-4">
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($asset->category): ?>
                                                        <div class="d-flex align-items-center">
                                                            <i class="ki-duotone ki-category fs-3 me-2 text-info">
                                                                <span class="path1"></span>
                                                                <span class="path2"></span>
                                                                <span class="path3"></span>
                                                                <span class="path4"></span>
                                                            </i>
                                                            <span class="badge badge-light-info fs-7 fw-bold"><?php echo e($asset->category->name); ?></span>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="d-flex align-items-center">
                                                            <i class="ki-duotone ki-category fs-3 me-2 text-muted">
                                                                <span class="path1"></span>
                                                                <span class="path2"></span>
                                                                <span class="path3"></span>
                                                                <span class="path4"></span>
                                                            </i>
                                                            <span class="text-muted fs-7">Uncategorized</span>
                                                        </div>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </td>
                                                <!--end::Category-->
                                                <!--begin::Department-->
                                                <td class="px-4 py-4">
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($asset->department): ?>
                                                        <div class="d-flex align-items-center">
                                                            <i class="ki-duotone ki-office-bag fs-3 me-2 text-warning">
                                                                <span class="path1"></span>
                                                                <span class="path2"></span>
                                                            </i>
                                                            <span class="badge badge-light-warning fs-7 fw-bold"><?php echo e($asset->department->name); ?></span>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="d-flex align-items-center">
                                                            <i class="ki-duotone ki-office-bag fs-3 me-2 text-muted">
                                                                <span class="path1"></span>
                                                                <span class="path2"></span>
                                                            </i>
                                                            <span class="text-muted fs-7">Unassigned</span>
                                                        </div>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </td>
                                                <!--end::Department-->
                                                <!--begin::State-->
                                                <td class="px-4 py-4">
                                                    <?php
                                                        $stateColors = [
                                                            'new' => 'success',
                                                            'in_use' => 'primary',
                                                            'damaged' => 'danger',
                                                            'repaired' => 'warning',
                                                            'disposed' => 'dark',
                                                            'lost' => 'danger'
                                                        ];
                                                        $color = $stateColors[$asset->state] ?? 'secondary';
                                                        $stateIcons = [
                                                            'new' => 'ki-check-circle',
                                                            'in_use' => 'ki-play-circle',
                                                            'damaged' => 'ki-cross-circle',
                                                            'repaired' => 'ki-wrench',
                                                            'disposed' => 'ki-trash',
                                                            'lost' => 'ki-search'
                                                        ];
                                                        $icon = $stateIcons[$asset->state] ?? 'ki-question-circle';
                                                    ?>
                                                    <div class="d-flex align-items-center">
                                                        <i class="ki-duotone <?php echo e($icon); ?> fs-3 me-2 text-<?php echo e($color); ?>">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                        </i>
                                                        <span class="badge badge-light-<?php echo e($color); ?> fs-7 fw-bold"><?php echo e(ucfirst(str_replace('_', ' ', $asset->state))); ?></span>
                                                    </div>
                                                </td>
                                                <!--end::State-->
                                                <!--begin::Location-->
                                                <td class="px-4 py-4">
                                                    <div class="d-flex align-items-center">
                                                        <i class="ki-duotone ki-geolocation fs-3 me-2 text-muted">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                        </i>
                                                        <span class="text-dark fw-semibold fs-6"><?php echo e($asset->location ?? 'N/A'); ?></span>
                                                    </div>
                                                </td>
                                                <!--end::Location-->
                                                <!--begin::Created-->
                                                <td class="px-4 py-4">
                                                    <div class="d-flex align-items-center">
                                                        <i class="ki-duotone ki-calendar fs-3 me-2 text-muted">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                        </i>
                                                        <div class="d-flex flex-column">
                                                            <span class="text-muted fw-semibold fs-7"><?php echo e($asset->created_at->format('M j, Y')); ?></span>
                                                            <span class="text-muted fs-8"><?php echo e($asset->created_at->format('g:i A')); ?></span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <!--end::Created-->
                                                <!--begin::Action-->
                                                <td class="text-end px-4 py-4">
                                                    <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                                        <i class="ki-duotone ki-setting-3 fs-3 me-1">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                            <span class="path3"></span>
                                                            <span class="path4"></span>
                                                            <span class="path5"></span>
                                                        </i>
                                                        Actions
                                                        <i class="ki-duotone ki-down fs-5 ms-1"></i>
                                                    </a>
                                                    <!--begin::Menu-->
                                                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                                                        <!--begin::Menu item-->
                                                        <div class="menu-item px-3">
                                                            <a href="<?php echo e(route('admin.assets.show', $asset)); ?>" class="menu-link px-3">View</a>
                                                        </div>
                                                        <!--end::Menu item-->
                                                        <!--begin::Menu item-->
                                                        <div class="menu-item px-3">
                                                            <a href="<?php echo e(route('admin.assets.edit', $asset)); ?>" class="menu-link px-3">Edit</a>
                                                        </div>
                                                        <!--end::Menu item-->
                                                        <!--begin::Menu item-->
                                                        <div class="menu-item px-3">
                                                            <form method="POST" action="<?php echo e(route('admin.assets.destroy', $asset)); ?>" class="d-inline">
                                                                <?php echo csrf_field(); ?>
                                                                <?php echo method_field('DELETE'); ?>
                                                                <button type="submit" class="menu-link px-3 text-danger border-0 bg-transparent" onclick="return confirm('Are you sure you want to delete this asset?')">Delete</button>
                                                            </form>
                                                        </div>
                                                        <!--end::Menu item-->
                                                    </div>
                                                    <!--end::Menu-->
                                                </td>
                                                <!--end::Action-->
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <tr>
                                                <td colspan="7" class="text-center py-20">
                                                    <div class="text-center">
                                                        <div class="d-flex flex-center mb-5">
                                                            <i class="ki-duotone ki-chart-simple-3 fs-1x text-muted">
                                                                <span class="path1"></span>
                                                                <span class="path2"></span>
                                                                <span class="path3"></span>
                                                                <span class="path4"></span>
                                                            </i>
                                                        </div>
                                                        <h4 class="fs-5 fw-bold text-gray-800 mb-3">No Assets Found</h4>
                                                        <p class="fs-6 text-muted mb-5">Get started by adding your first asset to the inventory system.</p>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (\Illuminate\Support\Facades\Blade::check('hasanyrole', ['System', 'Super Admin'])): ?>
                                                        <a href="<?php echo e(route('admin.assets.create')); ?>" class="btn btn-sm btn-primary">
                                                            <i class="ki-duotone ki-plus fs-3 me-1">
                                                                <span class="path1"></span>
                                                                <span class="path2"></span>
                                                            </i>Add First Asset
                                                        </a>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </tbody>
                                    <!--end::Table body-->
                                </table>
                            </div>
                            <!--end::Table-->
                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($assets->hasPages()): ?>
                                <!--begin::Pagination-->
                                <div class="d-flex flex-stack flex-wrap pt-10">
                                    <div class="fs-6 fw-semibold text-gray-700">
                                        Showing <?php echo e($assets->firstItem()); ?> to <?php echo e($assets->lastItem()); ?> of <?php echo e($assets->total()); ?> entries
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <?php echo e($assets->withQueryString()->links()); ?>

                                    </div>
                                </div>
                                <!--end::Pagination-->
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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

    <!--begin::Custom styles-->
    <?php $__env->startPush('styles'); ?>
    <style>
        /* Enhanced table hover effects */
        #kt_assets_table tbody tr:hover {
            background-color: #f8f9fa !important;
            transform: translateY(-1px);
            transition: all 0.2s ease-in-out;
            box-shadow: 0 2px 6px 0 rgba(0,0,0,0.08);
        }

        /* Smooth transitions for table elements */
        #kt_assets_table tbody tr {
            transition: all 0.2s ease-in-out;
        }

        /* Better badge styling */
        #kt_assets_table .badge {
            padding: 0.5rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.025em;
        }

        /* Enhanced symbol styling */
        #kt_assets_table .symbol .symbol-label {
            transition: all 0.2s ease-in-out;
        }

        #kt_assets_table tbody tr:hover .symbol .symbol-label {
            transform: scale(1.1);
        }

        /* Icon animations */
        #kt_assets_table i.ki-duotone {
            transition: all 0.2s ease-in-out;
        }

        #kt_assets_table tbody tr:hover i.ki-duotone {
            transform: scale(1.1);
        }
    </style>
    <?php $__env->stopPush(); ?>
    <!--end::Custom styles-->
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78458ab11901cb5e12c9728346766652)): ?>
<?php $attributes = $__attributesOriginal78458ab11901cb5e12c9728346766652; ?>
<?php unset($__attributesOriginal78458ab11901cb5e12c9728346766652); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78458ab11901cb5e12c9728346766652)): ?>
<?php $component = $__componentOriginal78458ab11901cb5e12c9728346766652; ?>
<?php unset($__componentOriginal78458ab11901cb5e12c9728346766652); ?>
<?php endif; ?><?php /**PATH /home/johnsonsebire/www/college/resources/views/assets/index.blade.php ENDPATH**/ ?>