<x-dashboard.default title="Asset Settings">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title d-flex justify-content-between align-items-center w-100">
                        <h3 class="card-title fw-bold text-gray-800">
                            <i class="fas fa-cogs me-2"></i>Asset Settings
                        </h3>
                        <div>
                            <a href="{{ route('admin.assets.index') }}" class="btn btn-sm btn-light-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Back to Assets
                            </a>
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

                    <!-- Asset Tag Prefix Setting -->
                    <div class="row mb-8">
                        <div class="col-12">
                            <div class="card border border-gray-300">
                                <div class="card-header bg-light-primary">
                                    <div class="card-title m-0">
                                        <h4 class="fw-bold text-primary mb-0">
                                            <i class="fas fa-tag me-2"></i>Asset Tag Configuration
                                        </h4>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted mb-4">Configure the prefix used for auto-generated asset tags.</p>
                                    
                                    <form method="POST" action="{{ route('admin.asset-settings.update-prefix') }}">
                                        @csrf
                                        @method('PATCH')
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="asset_tag_prefix" class="form-label required fw-semibold">Asset Tag Prefix</label>
                                                <input type="text" name="asset_tag_prefix" id="asset_tag_prefix" 
                                                       value="{{ old('asset_tag_prefix', \App\Models\AssetSetting::getValue('asset_tag_prefix', 'COL-')) }}"
                                                       maxlength="10" required
                                                       class="form-control form-control-solid @error('asset_tag_prefix') is-invalid @enderror">
                                                <div class="form-text">Example: COL- will generate tags like COL-0001, COL-0002, etc.</div>
                                                @error('asset_tag_prefix')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6 d-flex align-items-end mb-3">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-save me-1"></i>Update Prefix
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- All Settings -->
                    <div class="row mb-8">
                        <div class="col-12">
                            <div class="card border border-gray-300">
                                <div class="card-header bg-light-info">
                                    <div class="card-title m-0">
                                        <h4 class="fw-bold text-info mb-0">
                                            <i class="fas fa-list me-2"></i>All Asset Settings
                                        </h4>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted mb-4">Manage all asset-related configuration settings.</p>
                                    
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover align-middle gs-0 gy-3">
                                            <thead class="table-dark">
                                                <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                                    <th class="min-w-150px">Setting Key</th>
                                                    <th class="min-w-150px">Value</th>
                                                    <th class="min-w-200px">Description</th>
                                                    <th class="min-w-100px text-end">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody class="text-gray-600 fw-semibold">
                                                @forelse($settings as $setting)
                                                    <tr>
                                                        <td class="text-dark fw-bold">{{ $setting->key }}</td>
                                                        <td><code class="bg-light p-1 rounded">{{ $setting->value }}</code></td>
                                                        <td class="text-gray-600">{{ $setting->description ?? 'No description' }}</td>
                                                        <td class="text-end">
                                                            @if($setting->key !== 'asset_tag_prefix')
                                                                <form method="POST" action="{{ route('admin.asset-settings.destroy', $setting) }}" class="d-inline">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-sm btn-light-danger" title="Delete Setting" onclick="return confirm('Are you sure you want to delete this setting?')">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                            </form>
                                        @else
                                            <span class="text-gray-400 text-xs">System setting</span>
                                        @endif
                                    </td>
                                </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center py-5">
                                                            <div class="text-muted">
                                                                <i class="fas fa-cogs fa-2x mb-3 d-block"></i>
                                                                <h5 class="fw-bold">No Custom Settings Found</h5>
                                                                <p class="mb-0">Add your first custom asset setting below.</p>
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

                    <!-- Add New Setting -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card border border-gray-300">
                                <div class="card-header bg-light-success">
                                    <div class="card-title m-0">
                                        <h4 class="fw-bold text-success mb-0">
                                            <i class="fas fa-plus-circle me-2"></i>Add New Setting
                                        </h4>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted mb-4">Create a new configuration setting for the asset management module.</p>
                                    
                                    <form method="POST" action="{{ route('admin.asset-settings.store') }}">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="key" class="form-label required fw-semibold">Setting Key</label>
                                                <input type="text" name="key" id="key" value="{{ old('key') }}" required
                                                       placeholder="e.g., depreciation_rate"
                                                       class="form-control form-control-solid @error('key') is-invalid @enderror">
                                                @error('key')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="value" class="form-label required fw-semibold">Setting Value</label>
                                                <input type="text" name="value" id="value" value="{{ old('value') }}" required
                                                       placeholder="e.g., 0.10"
                                                       class="form-control form-control-solid @error('value') is-invalid @enderror">
                                                @error('value')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="description" class="form-label fw-semibold">Description</label>
                                                <input type="text" name="description" id="description" value="{{ old('description') }}"
                                                       placeholder="e.g., Annual depreciation rate"
                                                       class="form-control form-control-solid @error('description') is-invalid @enderror">
                                                @error('description')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="text-center">
                                            <button type="submit" class="btn btn-success">
                                                <i class="fas fa-plus-circle me-1"></i>Add Setting
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard.default>