<body id="kt_body" class="header-tablet-and-mobile-fixed aside-enabled">
    <?php echo $__env->yieldPushContent('styles'); ?>
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
            <?php if (isset($component)) { $__componentOriginal790df3a3003b05a46d3e5fdd59aeab47 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal790df3a3003b05a46d3e5fdd59aeab47 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app.sidebar','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app.sidebar'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal790df3a3003b05a46d3e5fdd59aeab47)): ?>
<?php $attributes = $__attributesOriginal790df3a3003b05a46d3e5fdd59aeab47; ?>
<?php unset($__attributesOriginal790df3a3003b05a46d3e5fdd59aeab47); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal790df3a3003b05a46d3e5fdd59aeab47)): ?>
<?php $component = $__componentOriginal790df3a3003b05a46d3e5fdd59aeab47; ?>
<?php unset($__componentOriginal790df3a3003b05a46d3e5fdd59aeab47); ?>
<?php endif; ?>
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
                            <?php
                                $appLogo = config('branding.logo.app') ?: config('branding.logo.primary');
                                $appLogoFile = is_string($appLogo) && str_starts_with($appLogo, '/')
                                    ? public_path(ltrim($appLogo, '/'))
                                    : null;
                                $hasAppLogo = $appLogoFile && is_file($appLogoFile) && is_readable($appLogoFile);
                                $institutionAcronym = config('branding.institution.short_name') ?: config('app.name');
                            ?>
                            <a href="<?php echo e(url('/')); ?>" class="d-flex align-items-center text-white text-decoration-none">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasAppLogo): ?>
                                    <img alt="<?php echo e(config('branding.institution.name', config('app.name'))); ?>"
                                        src="<?php echo e(asset($appLogo)); ?>" class="h-25px h-lg-30px"
                                        onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none');" />
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <span class="fw-bold fs-5 <?php echo e($hasAppLogo ? 'd-none' : ''); ?>"><?php echo e($institutionAcronym); ?></span>
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
                                    
                                    <!--end::Form-->
                                    <!--begin::Menu-->
                                    
                                    <!--end::Menu-->
                                </div>
                                <!--end::Search-->
                            </div>
                            <!--end::Item-->
                            <!--begin::Item-->
                            <?php if (isset($component)) { $__componentOriginaldacdf80d28bc5f079f7c13a353d2df5d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldacdf80d28bc5f079f7c13a353d2df5d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.academic-context','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('academic-context'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldacdf80d28bc5f079f7c13a353d2df5d)): ?>
<?php $attributes = $__attributesOriginaldacdf80d28bc5f079f7c13a353d2df5d; ?>
<?php unset($__attributesOriginaldacdf80d28bc5f079f7c13a353d2df5d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldacdf80d28bc5f079f7c13a353d2df5d)): ?>
<?php $component = $__componentOriginaldacdf80d28bc5f079f7c13a353d2df5d; ?>
<?php unset($__componentOriginaldacdf80d28bc5f079f7c13a353d2df5d); ?>
<?php endif; ?>
                            <!--end::Item-->
                            <!--begin::Item-->
                            
                            <?php if (isset($component)) { $__componentOriginale5bc9b34dd139a393f71cdc403b71855 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale5bc9b34dd139a393f71cdc403b71855 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.notifications','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('notifications'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale5bc9b34dd139a393f71cdc403b71855)): ?>
<?php $attributes = $__attributesOriginale5bc9b34dd139a393f71cdc403b71855; ?>
<?php unset($__attributesOriginale5bc9b34dd139a393f71cdc403b71855); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale5bc9b34dd139a393f71cdc403b71855)): ?>
<?php $component = $__componentOriginale5bc9b34dd139a393f71cdc403b71855; ?>
<?php unset($__componentOriginale5bc9b34dd139a393f71cdc403b71855); ?>
<?php endif; ?>
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
                                                <img alt="Avatar" src="<?php echo e(Auth::user()->profile_photo_url); ?>" />
                                            </div>
                                            <!--end::Avatar-->
                                            <!--begin::Username-->
                                            <div class="d-flex flex-column">
                                                <div class="fw-bold d-flex align-items-center fs-5"><?php echo e(Auth::user()->name); ?>

                                                    <span
                                                        class="px-2 py-1 badge badge-light-success fw-bold fs-8 ms-2"><?php echo e(Auth::user()->role); ?></span>
                                                </div>
                                                <a href="#"
                                                    class="fw-semibold text-muted text-hover-primary fs-7"><?php echo e(Auth::user()->email); ?></a>
                                            </div>
                                            <!--end::Username-->
                                        </div>
                                    </div>
                                    <!--end::Menu item-->
                                    <!--begin::Menu separator-->
                                    <div class="my-2 separator"></div>
                                    <!--end::Menu separator-->
                                    <!--begin::Menu item-->
                                    
                                    <!--end::Menu item-->
                                    
                                    <!--end::Menu item-->
                                    
                                    <!--end::Menu item-->
                                    <!--begin::Menu item-->
                                    
                                    <!--end::Menu item-->
                                    <!--begin::Menu separator-->
                                    
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
                                                        src="<?php echo e(asset('dashboard/assets/media/flags/united-states.svg')); ?>"
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
                                                            src="<?php echo e(asset('dashboard/assets/media/flags/united-states.svg')); ?>"
                                                            alt="" />
                                                    </span>English</a>
                                            </div>
                                            <!--end::Menu item-->
                                            <!--begin::Menu item-->
                                            
                                            <!--end::Menu item-->
                                            <!--begin::Menu item-->
                                            
                                            <!--end::Menu item-->
                                            <!--begin::Menu item-->
                                            
                                            <!--end::Menu item-->
                                            <!--begin::Menu item-->
                                            
                                            <!--end::Menu item-->
                                        </div>
                                        <!--end::Menu sub-->
                                    </div>
                                    <!--end::Menu item-->
                                    <!--begin::Menu item-->
                                    
                                    <!--end::Menu item-->
                                    <!--begin::Menu item-->
                                    <div class="px-5 menu-item">
                                        
                                        <form method="POST" action="<?php echo e(route('logout')); ?>">
                                           
                                            <?php echo csrf_field(); ?>

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
                            
                            <!--end::Item-->
                            <!--begin::Item-->
                            
                            <!--end::Item-->
                        </div>
                        <!--end::Topbar-->
                    </div>
                    <!--end::Container-->
                </div>
                <!--end::Header-->
<?php /**PATH /home/johnsonsebire/www/college/resources/views/components/partials/dash-header.blade.php ENDPATH**/ ?>