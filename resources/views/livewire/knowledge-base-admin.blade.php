<div>
    <x-dashboard.default>
        <div class="container-fluid">
        {{-- Page Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Knowledge Base Admin</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('support.knowledgebase') }}">Knowledge Base</a></li>
                        <li class="breadcrumb-item active">Admin</li>
                    </ol>
                </nav>
            </div>
        </div>

        {{-- Success Message --}}
        @if (session()->has('message'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Main Card --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">
                    <i class="ki-duotone ki-book fs-2 me-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    Manage Articles
                </h3>
                <button wire:click="openCreateModal" class="btn btn-primary">
                    <i class="ki-duotone ki-plus fs-3">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    Create Article
                </button>
            </div>

            <div class="card-body">
                {{-- Filters --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <input type="text" wire:model.live="searchTerm" class="form-control" placeholder="Search articles...">
                    </div>
                    <div class="col-md-3">
                        <select wire:model.live="categoryFilter" class="form-select">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select wire:model.live="statusFilter" class="form-select">
                            <option value="">All Status</option>
                            <option value="published">Published</option>
                            <option value="draft">Draft</option>
                            <option value="trashed">Trashed</option>
                        </select>
                    </div>
                </div>

                {{-- Articles Table --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Views</th>
                                <th>Helpful</th>
                                <th>Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($articles as $article)
                                <tr class="{{ $article->trashed() ? 'table-danger' : '' }}">
                                    <td>
                                        <div>
                                            <strong>{{ $article->title }}</strong>
                                            @if($article->is_featured)
                                                <span class="badge badge-sm bg-warning ms-1">
                                                    <i class="ki-duotone ki-star fs-6"></i>
                                                    Featured
                                                </span>
                                            @endif
                                            @if($article->trashed())
                                                <span class="badge badge-sm bg-danger ms-1">Trashed</span>
                                            @endif
                                        </div>
                                        @if($article->excerpt)
                                            <small class="text-muted">{{ Str::limit($article->excerpt, 60) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $article->category->color ?? 'secondary' }}">
                                            {{ $article->category->name }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($article->is_published)
                                            <span class="badge bg-success">Published</span>
                                        @else
                                            <span class="badge bg-secondary">Draft</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($article->views_count) }}</td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ $article->helpful_count }} / {{ $article->helpful_count + $article->not_helpful_count }}
                                        </span>
                                    </td>
                                    <td>{{ $article->updated_at->diffForHumans() }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            @if(!$article->trashed())
                                                <button wire:click="openEditModal({{ $article->id }})" 
                                                        class="btn btn-sm btn-light" 
                                                        title="Edit">
                                                    <i class="ki-duotone ki-pencil fs-5">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                </button>
                                                <button wire:click="togglePublish({{ $article->id }})" 
                                                        class="btn btn-sm btn-light" 
                                                        title="{{ $article->is_published ? 'Unpublish' : 'Publish' }}">
                                                    <i class="ki-duotone ki-{{ $article->is_published ? 'eye-slash' : 'eye' }} fs-5">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                </button>
                                                <button wire:click="toggleFeatured({{ $article->id }})" 
                                                        class="btn btn-sm btn-light" 
                                                        title="{{ $article->is_featured ? 'Unfeature' : 'Feature' }}">
                                                    <i class="ki-duotone ki-star fs-5 {{ $article->is_featured ? 'text-warning' : '' }}">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                </button>
                                                <button wire:click="deleteArticle({{ $article->id }})" 
                                                        wire:confirm="Are you sure you want to trash this article?"
                                                        class="btn btn-sm btn-light text-danger" 
                                                        title="Delete">
                                                    <i class="ki-duotone ki-trash fs-5">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                </button>
                                            @else
                                                <button wire:click="restoreArticle({{ $article->id }})" 
                                                        class="btn btn-sm btn-success" 
                                                        title="Restore">
                                                    <i class="ki-duotone ki-arrows-circle fs-5">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                    Restore
                                                </button>
                                                <button wire:click="forceDeleteArticle({{ $article->id }})" 
                                                        wire:confirm="Are you sure? This will permanently delete the article!"
                                                        class="btn btn-sm btn-danger" 
                                                        title="Permanently Delete">
                                                    <i class="ki-duotone ki-trash fs-5">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                    Delete Forever
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="ki-duotone ki-file-deleted fs-3x mb-3">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                            <p class="mb-0">No articles found</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-4">
                    {{ $articles->links() }}
                </div>
            </div>
        </div>

        {{-- Create/Edit Modal --}}
        @if($showModal)
            <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5);">
                <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ $editMode ? 'Edit Article' : 'Create New Article' }}</h5>
                            <button type="button" wire:click="closeModal" class="btn-close"></button>
                        </div>
                        <div class="modal-body">
                            <form wire:submit.prevent="save">
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                        <input type="text" wire:model="title" class="form-control @error('title') is-invalid @enderror" id="title">
                                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label for="knowledge_base_category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                        <select wire:model="knowledge_base_category_id" class="form-select @error('knowledge_base_category_id') is-invalid @enderror" id="knowledge_base_category_id">
                                            <option value="">Select Category</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('knowledge_base_category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-12">
                                        <label for="excerpt" class="form-label">Excerpt</label>
                                        <textarea wire:model="excerpt" class="form-control @error('excerpt') is-invalid @enderror" id="excerpt" rows="2" placeholder="Brief summary (optional, will auto-generate from content if empty)"></textarea>
                                        @error('excerpt') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-12" wire:ignore>
                                        <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                                        <textarea id="tinymce-editor" class="form-control @error('content') is-invalid @enderror">{{ $content }}</textarea>
                                        @error('content') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" wire:model="is_published" id="is_published">
                                            <label class="form-check-label" for="is_published">
                                                Publish Article
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" wire:model="is_featured" id="is_featured">
                                            <label class="form-check-label" for="is_featured">
                                                Feature Article
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" wire:click="closeModal" class="btn btn-secondary">Cancel</button>
                            <button type="button" wire:click="save" class="btn btn-primary">
                                {{ $editMode ? 'Update Article' : 'Create Article' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        </div>

        @push('scripts')
        <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
        <script>
            document.addEventListener('livewire:initialized', () => {
                let editor;

                // Initialize TinyMCE when modal opens
                Livewire.on('showModal', () => {
                    setTimeout(() => {
                        initTinyMCE();
                    }, 100);
                });

                // Initialize on page load if modal is already open
                @if($showModal)
                    setTimeout(() => {
                        initTinyMCE();
                    }, 100);
                @endif

                function initTinyMCE() {
                    if (editor) {
                        tinymce.remove(editor);
                    }

                    tinymce.init({
                        selector: '#tinymce-editor',
                        height: 400,
                        menubar: false,
                        plugins: [
                            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                            'insertdatetime', 'media', 'table', 'help', 'wordcount'
                        ],
                        toolbar: 'undo redo | formatselect | bold italic underline strikethrough | ' +
                                 'alignleft aligncenter alignright alignjustify | ' +
                                 'bullist numlist outdent indent | link image | ' +
                                 'forecolor backcolor | removeformat | code fullscreen',
                        content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; font-size: 14px; }',
                        setup: function (ed) {
                            editor = ed;
                            ed.on('change keyup', function () {
                                @this.set('content', ed.getContent());
                            });
                        }
                    });
                }

                // Clean up TinyMCE when modal closes
                window.addEventListener('closeModal', () => {
                    if (editor) {
                        tinymce.remove(editor);
                        editor = null;
                    }
                });
            });
        </script>
        @endpush
    </x-dashboard.default>
</div>