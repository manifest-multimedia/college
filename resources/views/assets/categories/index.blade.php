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
                                    <th class="min-w-150px px-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <i class="ki-duotone ki-tag fs-3 me-2 text-gray-400">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                            </i>
                                            Name
                                        </div>
                                    </th>
                                    <th class="min-w-200px px-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <i class="ki-duotone ki-information fs-3 me-2 text-gray-400">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                            </i>
                                            Description
                                        </div>
                                    </th>
                                    <th class="min-w-150px px-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <i class="ki-duotone ki-abstract-26 fs-3 me-2 text-gray-400">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                            Parent Category
                                        </div>
                                    </th>
                                    <th class="min-w-100px text-center px-4 py-3">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <i class="ki-duotone ki-chart fs-3 me-2 text-gray-400">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                            Assets Count
                                        </div>
                                    </th>
                                    <th class="min-w-125px px-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <i class="ki-duotone ki-calendar fs-3 me-2 text-gray-400">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                            Created
                                        </div>
                                    </th>
                                    <th class="min-w-100px text-end px-4 py-3">
                                        <div class="d-flex align-items-center justify-content-end">
                                            <i class="ki-duotone ki-setting-3 fs-3 me-2 text-gray-400">
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
                            <tbody class="text-gray-600 fw-semibold">
                                @forelse($categories as $category)
                                    <tr class="border-bottom border-gray-200">
                                        <td class="px-4 py-4">
                                            <div class="d-flex align-items-center">
                                                <div class="symbol symbol-35px me-3">
                                                    <div class="symbol-label bg-light-primary">
                                                        <i class="ki-duotone ki-tag fs-3 text-primary">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                            <span class="path3"></span>
                                                        </i>
                                                    </div>
                                                </div>
                                                <div>
                                                    <a href="{{ route('admin.asset-categories.show', $category) }}" class="text-dark fw-bold text-hover-primary d-block fs-6 text-decoration-none">
                                                        {{ $category->name }}
                                                    </a>
                                                    @if($category->hasChildren())
                                                        <div class="mt-1">
                                                            <span class="badge badge-light-info badge-sm">
                                                                <i class="ki-duotone ki-abstract-26 fs-7 me-1">
                                                                    <span class="path1"></span>
                                                                    <span class="path2"></span>
                                                                </i>
                                                                {{ $category->children->count() }} subcategories
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="text-gray-700 fs-6">{{ Str::limit($category->description, 50) ?: 'No description' }}</span>
                                        </td>
                                        <td class="px-4 py-4">
                                            @if($category->parent)
                                                <div class="d-flex align-items-center">
                                                    <i class="ki-duotone ki-abstract-26 fs-4 me-2 text-secondary">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                    <span class="badge badge-light-secondary fs-7 fw-bold">{{ $category->parent->name }}</span>
                                                </div>
                                            @else
                                                <div class="d-flex align-items-center">
                                                    <i class="ki-duotone ki-home-2 fs-4 me-2 text-success">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                    <span class="badge badge-light-success fs-7 fw-bold">Root Category</span>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-center px-4 py-4">
                                            <div class="d-flex align-items-center justify-content-center">
                                                <i class="ki-duotone ki-chart fs-4 me-2 text-primary">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                                <span class="badge badge-light-primary fs-6 fw-bold px-3 py-2">{{ $category->assets->count() }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="d-flex align-items-center">
                                                <i class="ki-duotone ki-calendar fs-4 me-2 text-muted">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                                <span class="text-gray-600 fs-6">{{ $category->created_at->format('M j, Y') }}</span>
                                            </div>
                                        </td>
                                        <td class="text-end px-4 py-4">
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.asset-categories.show', $category) }}" class="btn btn-sm btn-light-primary" title="View Details">
                                                    <i class="ki-duotone ki-eye fs-4">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                        <span class="path3"></span>
                                                    </i>
                                                </a>
                                                @hasanyrole(['System', 'Super Admin'])
                                                <a href="{{ route('admin.asset-categories.edit', $category) }}" class="btn btn-sm btn-light-warning" title="Edit Category">
                                                    <i class="ki-duotone ki-notepad-edit fs-4">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                </a>
                                                @if(!$category->hasChildren() && $category->assets->count() === 0)
                                                    <form method="POST" action="{{ route('admin.asset-categories.destroy', $category) }}" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-light-danger" title="Delete Category" onclick="return confirm('Are you sure you want to delete this category?')">
                                                            <i class="ki-duotone ki-trash fs-4">
                                                                <span class="path1"></span>
                                                                <span class="path2"></span>
                                                                <span class="path3"></span>
                                                                <span class="path4"></span>
                                                                <span class="path5"></span>
                                                            </i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <div class="d-flex align-items-center">
                                                        <i class="ki-duotone ki-shield-cross fs-4 me-1 text-danger">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                            <span class="path3"></span>
                                                        </i>
                                                        <span class="badge badge-light-danger fs-8 fw-bold">Protected</span>
                                                    </div>
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