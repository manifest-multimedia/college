<div>
    <x-dashboard.default title="Knowledge Base">
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
                @if (session()->has('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session()->has('info'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!--begin::Search Results-->
                @if($view === 'search' && $searchResults->count() > 0)
                    <div class="mb-15">
                        <div class="d-flex flex-stack mb-5">
                            <h4 class="text-gray-900 fw-bold">Search Results ({{ $searchResults->count() }})</h4>
                            <a href="#" wire:click.prevent="backToCategories" class="fs-6 fw-semibold">← Back to Categories</a>
                        </div>
                        
                        <div class="separator separator-dashed border-gray-300 mb-8"></div>
                        
                        <div class="row g-10">
                            @foreach($searchResults as $article)
                                <div class="col-md-6 mb-5">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="ki-duotone ki-book-open fs-2 text-primary me-3">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        <a href="#" wire:click.prevent="viewArticle('{{ $article->slug }}', '{{ $article->category->slug }}')" class="fs-5 fw-bold text-gray-900 text-hover-primary">{{ $article->title }}</a>
                                    </div>
                                    <div class="fs-7 text-muted ps-9">{{ $article->excerpt }}</div>
                                    <div class="fs-8 text-gray-500 ps-9 mt-2">
                                        <span class="badge badge-light-{{ $article->category->color }}">{{ $article->category->name }}</span>
                                        · {{ $article->views_count }} views
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @elseif($view === 'search')
                    <div class="py-10 text-center">
                        <i class="ki-duotone ki-information-5 fs-5x text-gray-400 mb-5">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        <h3 class="text-gray-600 mb-2">No articles found</h3>
                        <p class="text-gray-400">Try different search terms or <a href="#" wire:click.prevent="backToCategories">browse categories</a>.</p>
                    </div>
                @endif
                <!--end::Search Results-->

                <!--begin::Categories-->
                @if($view === 'categories')
                    <div class="mb-15">
                        <h4 class="text-gray-900 fw-bold mb-6">Categories</h4>
                        
                        <div class="row g-10">
                            @foreach($categories as $category)
                                <div class="col-md-4">
                                    <div class="card-xl-stretch bg-light-{{ $category->color }} bg-hover-{{ $category->color }} mb-10" style="min-height: 250px;">
                                        <div class="card-body d-flex flex-column px-9 py-9">
                                            <div class="mb-5">
                                                <div class="d-flex flex-center h-80px w-80px rounded-circle bg-light-{{ $category->color }} mb-6">
                                                    <i class="ki-duotone {{ $category->icon }} fs-3x text-{{ $category->color }}">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                </div>
                                            </div>
                                            <a href="#" wire:click.prevent="viewCategory('{{ $category->slug }}')" class="fs-4 text-gray-800 fw-bold hover-primary mb-3">{{ $category->name }}</a>
                                            <div class="fw-semibold text-gray-400 mb-6">{{ $category->description }}</div>
                                            <div class="d-flex flex-wrap mb-5">
                                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                                    <div class="fw-semibold fs-6 text-gray-800">{{ $category->published_articles_count }} Articles</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!--begin::Popular Articles-->
                    @if($popularArticles->count() > 0)
                        <div class="mb-15">
                            <h4 class="text-gray-900 fw-bold mb-6">Popular Articles</h4>
                            
                            <div class="row g-10">
                                @foreach($popularArticles as $article)
                                    <div class="col-md-4">
                                        <div class="card card-bordered hover-elevate-up">
                                            <div class="card-header">
                                                <h3 class="card-title">{{ $article->title }}</h3>
                                            </div>
                                            <div class="card-body">
                                                <div class="d-flex align-items-center mb-5">
                                                    <div class="d-flex align-items-center flex-grow-1">
                                                        <div class="symbol symbol-circle bg-light-{{ $article->category->color }} me-2">
                                                            <span class="symbol-label fw-bold">{{ strtoupper(substr($article->category->name, 0, 2)) }}</span>
                                                        </div>
                                                        <div class="me-3">
                                                            <a href="#" class="fs-7 text-gray-700 text-hover-primary">{{ $article->category->name }}</a>
                                                        </div>
                                                        <span class="badge badge-light fw-bold my-2">{{ $article->updated_at->diffForHumans() }}</span>
                                                    </div>
                                                </div>
                                                <p class="text-gray-700 fs-6 fw-normal mb-5">{{ $article->excerpt }}</p>
                                                <a href="#" wire:click.prevent="viewArticle('{{ $article->slug }}', '{{ $article->category->slug }}')" class="btn btn-sm btn-light-{{ $article->category->color }}">Read More</a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    <!--end::Popular Articles-->
                @endif
                <!--end::Categories-->

                <!--begin::Category Articles-->
                @if($view === 'category-articles' && $selectedCategorySlug)
                    @php
                        $category = $categories->firstWhere('slug', $selectedCategorySlug);
                    @endphp
                    @if($category)
                        <div class="mb-15">
                            <div class="d-flex flex-stack mb-5">
                                <h4 class="text-gray-900 fw-bold">{{ $category->name }} Articles</h4>
                                <a href="#" wire:click.prevent="backToCategories" class="fs-6 fw-semibold">← Back to Categories</a>
                            </div>
                            
                            <div class="separator separator-dashed border-gray-300 mb-8"></div>
                            
                            @if($categoryArticles->count() > 0)
                                <div class="row g-10">
                                    @foreach($categoryArticles as $article)
                                        <div class="col-md-6 mb-5">
                                            <div class="d-flex align-items-center mb-1">
                                                <i class="ki-duotone ki-book-open fs-2 text-{{ $category->color }} me-3">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                                <a href="#" wire:click.prevent="viewArticle('{{ $article->slug }}', '{{ $category->slug }}')" class="fs-5 fw-bold text-gray-900 text-hover-primary">{{ $article->title }}</a>
                                            </div>
                                            <div class="fs-7 text-muted ps-9">{{ $article->excerpt }}</div>
                                            <div class="fs-8 text-gray-500 ps-9 mt-2">{{ $article->views_count }} views</div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="py-10 text-center">
                                    <i class="ki-duotone ki-information-5 fs-5x text-gray-400 mb-5">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                    <h3 class="text-gray-600 mb-2">No articles in this category yet</h3>
                                    <p class="text-gray-400">Check back later for new content.</p>
                                </div>
                            @endif
                        </div>
                    @endif
                @endif
                <!--end::Category Articles-->

                <!--begin::Individual Article View-->
                @if($view === 'article' && $selectedArticle)
                    <div class="mb-15">
                        <div class="d-flex flex-stack mb-5">
                            <h3 class="text-gray-900 fw-bolder">{{ $selectedArticle->title }}</h3>
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
                                Last updated: {{ $selectedArticle->updated_at->format('M d, Y') }} | 
                                Category: <span class="badge badge-light-{{ $selectedArticle->category->color }}">{{ $selectedArticle->category->name }}</span> | 
                                {{ $selectedArticle->views_count }} views
                            </div>
                        </div>
                        
                        <div class="fs-5 fw-normal text-gray-700 mb-8">
                            {!! nl2br(e($selectedArticle->content)) !!}
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
                                <a href="#" wire:click.prevent="markHelpful({{ $selectedArticle->id }})" class="ms-2 fw-bold link-primary me-2">Yes ({{ $selectedArticle->helpful_count }})</a> 
                                <a href="#" wire:click.prevent="markNotHelpful({{ $selectedArticle->id }})" class="fw-bold link-danger">No ({{ $selectedArticle->not_helpful_count }})</a>
                            </div>
                        </div>
                        
                        <div class="d-flex flex-stack">
                            <a href="{{ route('support.tickets') }}" class="btn btn-sm btn-primary">
                                <i class="ki-duotone ki-plus-square fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                                Need More Help? Create a Support Ticket
                            </a>
                        </div>
                    </div>
                @endif
                <!--end::Individual Article View-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->
    </x-dashboard.default>
</div>
