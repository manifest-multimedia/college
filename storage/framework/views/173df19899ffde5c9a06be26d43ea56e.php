<div>
    <?php if (isset($component)) { $__componentOriginal78458ab11901cb5e12c9728346766652 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78458ab11901cb5e12c9728346766652 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.default','data' => ['title' => 'Knowledge Base']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.default'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Knowledge Base']); ?>
        <div class="card">
            <!--begin::Card header-->
            <div class="card-header">
                <div class="card-title">
                    <h3 class="card-title">
                        <i class="ki-duotone ki-book-open fs-1 me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Knowledge Base
                    </h3>
                </div>
                <div class="card-toolbar">
                    <div class="d-flex align-items-center position-relative">
                        <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        <input type="text" class="form-control form-control-solid w-250px ps-10" wire:model.live="searchTerm" placeholder="Search Knowledge Base" />
                    </div>
                </div>
            </div>
            <!--end::Card header-->
            
            <!--begin::Card body-->
            <div class="card-body pt-6">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo e(session('success')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('info')): ?>
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <?php echo e(session('info')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <!--begin::Search Results-->
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($view === 'search' && $searchResults->count() > 0): ?>
                    <div class="mb-15">
                        <div class="d-flex flex-stack mb-5">
                            <h4 class="text-gray-900 fw-bold">Search Results (<?php echo e($searchResults->count()); ?>)</h4>
                            <a href="#" wire:click.prevent="backToCategories" class="fs-6 fw-semibold">← Back to Categories</a>
                        </div>
                        
                        <div class="separator separator-dashed border-gray-300 mb-8"></div>
                        
                        <div class="row g-10">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $searchResults; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $article): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="col-md-6 mb-5">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="ki-duotone ki-book-open fs-2 text-primary me-3">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        <a href="#" wire:click.prevent="viewArticle('<?php echo e($article->slug); ?>', '<?php echo e($article->category->slug); ?>')" class="fs-5 fw-bold text-gray-900 text-hover-primary"><?php echo e($article->title); ?></a>
                                    </div>
                                    <div class="fs-7 text-muted ps-9"><?php echo e($article->excerpt); ?></div>
                                    <div class="fs-8 text-gray-500 ps-9 mt-2">
                                        <span class="badge badge-light-<?php echo e($article->category->color); ?>"><?php echo e($article->category->name); ?></span>
                                        · <?php echo e($article->views_count); ?> views
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                <?php elseif($view === 'search'): ?>
                    <div class="py-10 text-center">
                        <i class="ki-duotone ki-information-5 fs-5x text-gray-400 mb-5">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        <h3 class="text-gray-600 mb-2">No articles found</h3>
                        <p class="text-gray-400">Try different search terms or <a href="#" wire:click.prevent="backToCategories">browse categories</a>.</p>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <!--end::Search Results-->

                <!--begin::Categories-->
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($view === 'categories'): ?>
                    <div class="mb-15">
                        <h4 class="text-gray-900 fw-bold mb-6">Categories</h4>
                        
                        <div class="row g-10">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="col-md-4">
                                    <div class="card-xl-stretch bg-light-<?php echo e($category->color); ?> bg-hover-<?php echo e($category->color); ?> mb-10" style="min-height: 250px;">
                                        <div class="card-body d-flex flex-column px-9 py-9">
                                            <div class="mb-5">
                                                <div class="d-flex flex-center h-80px w-80px rounded-circle bg-light-<?php echo e($category->color); ?> mb-6">
                                                    <i class="ki-duotone <?php echo e($category->icon); ?> fs-3x text-<?php echo e($category->color); ?>">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                </div>
                                            </div>
                                            <a href="#" wire:click.prevent="viewCategory('<?php echo e($category->slug); ?>')" class="fs-4 text-gray-800 fw-bold hover-primary mb-3"><?php echo e($category->name); ?></a>
                                            <div class="fw-semibold text-gray-400 mb-6"><?php echo e($category->description); ?></div>
                                            <div class="d-flex flex-wrap mb-5">
                                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                                    <div class="fw-semibold fs-6 text-gray-800"><?php echo e($category->published_articles_count); ?> Articles</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>

                    <!--begin::Popular Articles-->
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($popularArticles->count() > 0): ?>
                        <div class="mb-15">
                            <h4 class="text-gray-900 fw-bold mb-6">Popular Articles</h4>
                            
                            <div class="row g-10">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $popularArticles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $article): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="col-md-4">
                                        <div class="card card-bordered hover-elevate-up">
                                            <div class="card-header">
                                                <h3 class="card-title"><?php echo e($article->title); ?></h3>
                                            </div>
                                            <div class="card-body">
                                                <div class="d-flex align-items-center mb-5">
                                                    <div class="d-flex align-items-center flex-grow-1">
                                                        <div class="symbol symbol-circle bg-light-<?php echo e($article->category->color); ?> me-2">
                                                            <span class="symbol-label fw-bold"><?php echo e(strtoupper(substr($article->category->name, 0, 2))); ?></span>
                                                        </div>
                                                        <div class="me-3">
                                                            <a href="#" class="fs-7 text-gray-700 text-hover-primary"><?php echo e($article->category->name); ?></a>
                                                        </div>
                                                        <span class="badge badge-light fw-bold my-2"><?php echo e($article->updated_at->diffForHumans()); ?></span>
                                                    </div>
                                                </div>
                                                <p class="text-gray-700 fs-6 fw-normal mb-5"><?php echo e($article->excerpt); ?></p>
                                                <a href="#" wire:click.prevent="viewArticle('<?php echo e($article->slug); ?>', '<?php echo e($article->category->slug); ?>')" class="btn btn-sm btn-light-<?php echo e($article->category->color); ?>">Read More</a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <!--end::Popular Articles-->
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <!--end::Categories-->

                <!--begin::Category Articles-->
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($view === 'category-articles' && $selectedCategorySlug): ?>
                    <?php
                        $category = $categories->firstWhere('slug', $selectedCategorySlug);
                    ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($category): ?>
                        <div class="mb-15">
                            <div class="d-flex flex-stack mb-5">
                                <h4 class="text-gray-900 fw-bold"><?php echo e($category->name); ?> Articles</h4>
                                <a href="#" wire:click.prevent="backToCategories" class="fs-6 fw-semibold">← Back to Categories</a>
                            </div>
                            
                            <div class="separator separator-dashed border-gray-300 mb-8"></div>
                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($categoryArticles->count() > 0): ?>
                                <div class="row g-10">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $categoryArticles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $article): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="col-md-6 mb-5">
                                            <div class="d-flex align-items-center mb-1">
                                                <i class="ki-duotone ki-book-open fs-2 text-<?php echo e($category->color); ?> me-3">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                                <a href="#" wire:click.prevent="viewArticle('<?php echo e($article->slug); ?>', '<?php echo e($category->slug); ?>')" class="fs-5 fw-bold text-gray-900 text-hover-primary"><?php echo e($article->title); ?></a>
                                            </div>
                                            <div class="fs-7 text-muted ps-9"><?php echo e($article->excerpt); ?></div>
                                            <div class="fs-8 text-gray-500 ps-9 mt-2"><?php echo e($article->views_count); ?> views</div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="py-10 text-center">
                                    <i class="ki-duotone ki-information-5 fs-5x text-gray-400 mb-5">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                    <h3 class="text-gray-600 mb-2">No articles in this category yet</h3>
                                    <p class="text-gray-400">Check back later for new content.</p>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <!--end::Category Articles-->

                <!--begin::Individual Article View-->
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($view === 'article' && $selectedArticle): ?>
                    <div class="mb-15">
                        <div class="d-flex flex-stack mb-5">
                            <h3 class="text-gray-900 fw-bolder"><?php echo e($selectedArticle->title); ?></h3>
                            <a href="#" wire:click.prevent="backToCategoryArticles" class="fs-6 fw-semibold">← Back</a>
                        </div>
                        
                        <div class="separator separator-dashed border-gray-300 mb-8"></div>
                        
                        <div class="d-flex align-items-center bg-light-info rounded p-5 mb-7">
                            <div class="d-flex flex-center w-40px h-40px rounded-circle bg-light-info me-3">
                                <i class="ki-duotone ki-information-5 fs-2 text-info">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                            </div>
                            <div class="text-gray-700 fw-semibold fs-6">
                                Last updated: <?php echo e($selectedArticle->updated_at->format('M d, Y')); ?> | 
                                Category: <span class="badge badge-light-<?php echo e($selectedArticle->category->color); ?>"><?php echo e($selectedArticle->category->name); ?></span> | 
                                <?php echo e($selectedArticle->views_count); ?> views
                            </div>
                        </div>
                        
                        <div class="fs-5 fw-normal text-gray-700 mb-8">
                            <?php echo nl2br(e($selectedArticle->content)); ?>

                        </div>
                        
                        <div class="d-flex align-items-center rounded border border-dashed border-gray-300 p-5 mb-7">
                            <div class="d-flex flex-center w-40px h-40px rounded-circle bg-light-success me-3">
                                <i class="ki-duotone ki-questionnaire-tablet fs-2 text-success">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </div>
                            <div class="text-gray-700 fw-semibold fs-6">
                                Was this article helpful? 
                                <a href="#" wire:click.prevent="markHelpful(<?php echo e($selectedArticle->id); ?>)" class="ms-2 fw-bold link-primary me-2">Yes (<?php echo e($selectedArticle->helpful_count); ?>)</a> 
                                <a href="#" wire:click.prevent="markNotHelpful(<?php echo e($selectedArticle->id); ?>)" class="fw-bold link-danger">No (<?php echo e($selectedArticle->not_helpful_count); ?>)</a>
                            </div>
                        </div>
                        
                        <div class="d-flex flex-stack">
                            <a href="<?php echo e(route('support.tickets')); ?>" class="btn btn-sm btn-primary">
                                <i class="ki-duotone ki-plus-square fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                                Need More Help? Create a Support Ticket
                            </a>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <!--end::Individual Article View-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78458ab11901cb5e12c9728346766652)): ?>
<?php $attributes = $__attributesOriginal78458ab11901cb5e12c9728346766652; ?>
<?php unset($__attributesOriginal78458ab11901cb5e12c9728346766652); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78458ab11901cb5e12c9728346766652)): ?>
<?php $component = $__componentOriginal78458ab11901cb5e12c9728346766652; ?>
<?php unset($__componentOriginal78458ab11901cb5e12c9728346766652); ?>
<?php endif; ?>
</div>
<?php /**PATH /home/johnsonsebire/www/college/resources/views/livewire/knowledge-base.blade.php ENDPATH**/ ?>