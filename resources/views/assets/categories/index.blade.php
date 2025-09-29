<x-dashboard.default title="Asset Categories">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title d-flex justify-content-between align-items-center w-100">
                        <h3 class="card-title fw-bold text-gray-800">
                            <i class="fas fa-tags me-2"></i>Asset Categories
                        </h3>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.assets.index') }}" class="btn btn-sm btn-light-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Back to Assets
                            </a>
                            @hasanyrole(['System', 'Super Admin'])
                            <a href="{{ route('admin.asset-categories.create') }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus me-1"></i>Add Category
                            </a>
                            @endhasanyrole
                        </div>
                    </div>
                </div>
                <div class="card-body">

                    <!-- Flash Messages -->
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Categories Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle gs-0 gy-3">
                            <thead class="table-dark">
                                <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                    <th class="min-w-150px">Name</th>
                                    <th class="min-w-200px">Description</th>
                                    <th class="min-w-150px">Parent Category</th>
                                    <th class="min-w-100px text-center">Assets Count</th>
                                    <th class="min-w-125px">Created</th>
                                    <th class="min-w-100px text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-semibold">
                                @forelse($categories as $category)
                                    <tr>
                                        <td class="text-dark fw-bold">
                                            <a href="{{ route('admin.asset-categories.show', $category) }}" class="text-primary text-hover-primary text-decoration-none fw-bold">
                                                {{ $category->name }}
                                            </a>
                                            @if($category->hasChildren())
                                                <br><span class="badge badge-light-info badge-sm mt-1">
                                                    {{ $category->children->count() }} subcategories
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="text-gray-600">{{ Str::limit($category->description, 50) ?: 'No description' }}</span>
                                        </td>
                                        <td>
                                            @if($category->parent)
                                                <span class="badge badge-light-secondary">{{ $category->parent->name }}</span>
                                            @else
                                                <span class="text-muted">Root Category</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-light-primary fs-7 fw-bold">{{ $category->assets->count() }}</span>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $category->created_at->format('M j, Y') }}</span>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.asset-categories.show', $category) }}" class="btn btn-sm btn-light-primary" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @hasanyrole(['System', 'Super Admin'])
                                                <a href="{{ route('admin.asset-categories.edit', $category) }}" class="btn btn-sm btn-light-warning" title="Edit Category">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @if(!$category->hasChildren() && $category->assets->count() === 0)
                                                    <form method="POST" action="{{ route('admin.asset-categories.destroy', $category) }}" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-light-danger" title="Delete Category" onclick="return confirm('Are you sure you want to delete this category?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-gray-400 text-xs">Cannot delete</span>
                                                @endif
                                                @endhasanyrole
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="fas fa-tags fa-2x mb-3 d-block"></i>
                                                <h5 class="fw-bold">No Categories Found</h5>
                                                <p class="mb-0">Start by creating your first asset category.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard.default>