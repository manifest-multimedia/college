<?php if (isset($component)) { $__componentOriginal272129a90a649ce9d162b78b063f6c31 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal272129a90a649ce9d162b78b063f6c31 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.partials.dash-head','data' => ['title' => isset($title) ? $title : 'Dashboard','description' => 'Dashboard']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('partials.dash-head'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(isset($title) ? $title : 'Dashboard'),'description' => 'Dashboard']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal272129a90a649ce9d162b78b063f6c31)): ?>
<?php $attributes = $__attributesOriginal272129a90a649ce9d162b78b063f6c31; ?>
<?php unset($__attributesOriginal272129a90a649ce9d162b78b063f6c31); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal272129a90a649ce9d162b78b063f6c31)): ?>
<?php $component = $__componentOriginal272129a90a649ce9d162b78b063f6c31; ?>
<?php unset($__componentOriginal272129a90a649ce9d162b78b063f6c31); ?>
<?php endif; ?>
<!--end::Head-->
<!--begin::Body-->
<?php if (isset($component)) { $__componentOriginal49ce17670ec36ad8796bc70a01534662 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal49ce17670ec36ad8796bc70a01534662 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.partials.dash-header','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('partials.dash-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal49ce17670ec36ad8796bc70a01534662)): ?>
<?php $attributes = $__attributesOriginal49ce17670ec36ad8796bc70a01534662; ?>
<?php unset($__attributesOriginal49ce17670ec36ad8796bc70a01534662); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal49ce17670ec36ad8796bc70a01534662)): ?>
<?php $component = $__componentOriginal49ce17670ec36ad8796bc70a01534662; ?>
<?php unset($__componentOriginal49ce17670ec36ad8796bc70a01534662); ?>
<?php endif; ?>
<!--begin::Content-->
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('impersonator_id')): ?>
    <!-- Impersonation Floating Bar -->
    <div id="impersonationBar" class="position-fixed bottom-4 right-4 shadow-lg rounded bg-warning text-dark px-4 py-3 d-flex align-items-center gap-3" style="bottom: 1rem; right: 1rem; z-index: 9999 !important;">
        <i class="fas fa-user-secret"></i>
        <span>Impersonation active</span>
        <a href="<?php echo e(route('impersonate.stop')); ?>" class="btn btn-sm btn-dark">Exit</a>
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
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <!--begin::Toolbar-->
    <div class="mb-3 toolbar d-flex flex-stack mb-lg-5" id="kt_toolbar">
        <!--begin::Container-->
        <div id="kt_toolbar_container" class="flex-wrap container-fluid d-flex flex-stack">
            <!--begin::Page title-->
            <div class="py-2 page-title d-flex flex-column me-5">
                <!--begin::Title-->
                <h1 class="mb-0 text-gray-900 d-flex flex-column fw-bold fs-3">Welcome back, <?php echo e(Auth::user()->name); ?></h1>
                <!--end::Title-->
                <!--begin::Breadcrumb-->
                <ul class="pt-1 breadcrumb breadcrumb-separatorless fw-semibold fs-7">
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-muted">
                        <a href="<?php echo e(route('dashboard')); ?>" class="text-muted text-hover-primary">Dashboard</a>
                    </li>
                    <!--end::Item-->
                   
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bg-gray-200 bullet w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($title) && $title): ?>
                        <li class="text-gray-900 breadcrumb-item"><?php echo e($title); ?></li>
                    <?php else: ?> 
                        <li class="text-gray-900 breadcrumb-item">Dashboard</li>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <!--end::Item-->
                </ul>
                <!--end::Breadcrumb-->
            </div>
            <!--end::Page title-->
            <!--begin::Actions-->
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($pageActions)): ?>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php switch($pageActions):
            case ('examcenter'): ?>
            <div class="float-end">
                <a href="<?php echo e(route('exams.create')); ?>" class="btn btn-sm btn-success me-2"
                    >Create Exam</a>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Route::currentRouteName() != 'questionbank'): ?>
                <a href="<?php echo e(route('questionbank')); ?>" class="btn btn-sm btn-primary"
                 >Access Question Bank</a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            

                <?php break; ?>

            <?php case ('students'): ?>
            <div class="d-flex align-items-center gap-2 float-end">
                <a href="<?php echo e(route('students.create')); ?>" class="btn btn-sm btn-primary px-3.5 py-2 fw-semibold">
                    <i class="fas fa-plus me-1.5 fs-7"></i>Add Student
                </a>
                <a href="<?php echo e(route('students.import')); ?>" class="btn btn-sm btn-light-primary px-3.5 py-2 fw-semibold">
                    <i class="fas fa-file-import me-1.5 fs-7"></i>Import
                </a>
            </div>
                <?php break; ?>


            <?php default: ?>
                
          <?php endswitch; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <!--end::Actions-->
                        </div>
                        <!--end::Container-->
                    </div>
                    <!--end::Toolbar-->
                  
                  
                        <!--begin::Container-->
                        <div id="kt_content_container" class="container-fluid">
                         
                          <?php echo e($slot); ?>

                     
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
                            <span class="text-muted fw-semibold me-1"><?php echo e(date('Y')); ?> &copy;</span>
                            <a href="https://manifestghana.com" target="_blank"
                                class="text-gray-800 text-hover-primary">Manifest Digital</a>
                        </div>
                        <!--end::Copyright-->
                        <!--begin::Menu-->
                        
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
  

 
    <!--end::Drawers-->




<?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('feature-request-modal', []);

$__key = null;

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-2432138596-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key);

echo $__html;

unset($__html);
unset($__key);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
<?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('support-request-modal', []);

$__key = null;

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-2432138596-1', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key);

echo $__html;

unset($__html);
unset($__key);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>

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
    
   
    <!--end::Modals-->
    <?php if (isset($component)) { $__componentOriginalf98b4451267e63a478823dee4c3a9269 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf98b4451267e63a478823dee4c3a9269 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.partials.dash-footer','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('partials.dash-footer'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf98b4451267e63a478823dee4c3a9269)): ?>
<?php $attributes = $__attributesOriginalf98b4451267e63a478823dee4c3a9269; ?>
<?php unset($__attributesOriginalf98b4451267e63a478823dee4c3a9269); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf98b4451267e63a478823dee4c3a9269)): ?>
<?php $component = $__componentOriginalf98b4451267e63a478823dee4c3a9269; ?>
<?php unset($__componentOriginalf98b4451267e63a478823dee4c3a9269); ?>
<?php endif; ?>
    <!--begin::Javascript-->
<?php /**PATH /home/johnsonsebire/www/college/resources/views/components/dashboard/default.blade.php ENDPATH**/ ?>