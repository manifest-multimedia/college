<x-dashboard.default title="Institution Branding">
    <div class="row">
        <div class="col-12">
            <!-- Flash Messages -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ki-duotone ki-check-circle fs-2 me-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="ki-duotone ki-cross-circle fs-2 me-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="ki-duotone ki-cross-circle fs-2 me-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    <strong>Please correct the following errors:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Theme Preview -->
            <div class="card mb-8">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ki-duotone ki-tablet-text-down fs-1 me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Authentication Theme Preview
                    </h3>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-6">Preview different authentication themes before applying them to your institution.</p>
                    
                    <div class="row g-6">
                        @foreach($availableThemes as $themeKey => $theme)
                        <div class="col-md-4">
                            <div class="card border {{ $currentTheme === $themeKey ? 'border-primary' : 'border-gray-300' }} theme-preview-card">
                                <div class="card-body text-center p-6">
                                    <div class="mb-4">
                                        @if(file_exists(public_path($theme['preview'])))
                                            <img src="{{ asset($theme['preview']) }}" alt="{{ $theme['name'] }}" class="img-fluid rounded" style="max-height: 200px;">
                                        @else
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 200px;">
                                                <i class="ki-duotone ki-picture fs-3x text-muted">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </div>
                                        @endif
                                    </div>
                                    <h4 class="fw-bold">{{ $theme['name'] }}</h4>
                                    <p class="text-muted mb-4">{{ $theme['description'] }}</p>
                                    
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('admin.branding.preview', ['theme' => $themeKey]) }}" 
                                           target="_blank" 
                                           class="btn btn-sm btn-light-primary flex-grow-1">
                                            <i class="ki-duotone ki-eye fs-4 me-1">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                            </i>
                                            Preview
                                        </a>
                                        @if($currentTheme === $themeKey)
                                            <button class="btn btn-sm btn-primary" disabled>
                                                <i class="ki-duotone ki-check fs-4 me-1">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                                Active
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Authentication Theme -->
            <div class="card mb-8">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ki-duotone ki-design-1 fs-1 me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Authentication Theme
                    </h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.branding.update-theme') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <label for="auth_theme" class="form-label required fw-semibold">Select Theme</label>
                                <select name="auth_theme" id="auth_theme" class="form-select form-select-solid" required>
                                    @foreach($availableThemes as $themeKey => $theme)
                                        <option value="{{ $themeKey }}" {{ $currentTheme === $themeKey ? 'selected' : '' }}>
                                            {{ $theme['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Choose the authentication page design for your institution.</div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="ki-duotone ki-check fs-2 me-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                Save Theme Configuration
                            </button>
                        </div>
                    </form>
                </div>
            </div>

                <!-- Institution Information -->
                <div class="card mb-8">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ki-duotone ki-home-2 fs-1 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Institution Information
                        </h3>
                    </div>
                    <form method="POST" action="{{ route('admin.branding.update-institution') }}">
                        @csrf
                        <div class="card-body">
                            <div class="row g-6">
                                <div class="col-md-6">
                                    <label for="institution_name" class="form-label required fw-semibold">Institution Name</label>
                                    <input type="text" name="institution_name" id="institution_name" 
                                           class="form-control form-control-solid" 
                                           value="{{ old('institution_name', $institution['name'] ?? config('app.name')) }}" 
                                           required maxlength="255">
                                </div>
                                <div class="col-md-6">
                                    <label for="institution_short_name" class="form-label fw-semibold">Short Name/Acronym</label>
                                    <input type="text" name="institution_short_name" id="institution_short_name" 
                                           class="form-control form-control-solid" 
                                           value="{{ old('institution_short_name', $institution['short_name'] ?? '') }}" 
                                           maxlength="50"
                                           placeholder="e.g., MIT, UCLA">
                                </div>
                                <div class="col-md-6">
                                    <label for="support_email" class="form-label required fw-semibold">Support Email</label>
                                    <input type="email" name="support_email" id="support_email" 
                                           class="form-control form-control-solid" 
                                           value="{{ old('support_email', $institution['support_email'] ?? '') }}" 
                                           required>
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label fw-semibold">Phone Number</label>
                                    <input type="text" name="phone" id="phone" 
                                           class="form-control form-control-solid" 
                                           value="{{ old('phone', $institution['phone'] ?? '') }}" 
                                           maxlength="50">
                                </div>
                                <div class="col-12">
                                    <label for="address" class="form-label fw-semibold">Address</label>
                                    <textarea name="address" id="address" 
                                              class="form-control form-control-solid" 
                                              rows="3" maxlength="500">{{ old('address', $institution['address'] ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-end">
                            <button type="submit" class="btn btn-success">
                                <i class="ki-duotone ki-check fs-2 me-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                Save Institution Information
                            </button>
                        </div>
                    </form>
                </div>

                <!-- URLs Configuration -->
                <div class="card mb-8">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ki-duotone ki-global fs-1 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            URL Configuration
                        </h3>
                    </div>
                    <form method="POST" action="{{ route('admin.branding.update-urls') }}">
                        @csrf
                        <div class="card-body">
                            <div class="row g-6">
                                <div class="col-md-6">
                                    <label for="staff_mail_url" class="form-label required fw-semibold">Staff Mail URL</label>
                                    <input type="url" name="staff_mail_url" id="staff_mail_url" 
                                           class="form-control form-control-solid" 
                                           value="{{ old('staff_mail_url', $institution['staff_mail_url'] ?? 'https://mail.google.com') }}" 
                                           required>
                                    <div class="form-text">URL where staff members will be redirected when clicking "Staff Mail".</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="student_portal_url" class="form-label fw-semibold">Student Portal URL</label>
                                    <input type="url" name="student_portal_url" id="student_portal_url" 
                                           class="form-control form-control-solid" 
                                           value="{{ old('student_portal_url', $institution['student_portal_url'] ?? '') }}"
                                           placeholder="https://portal.yourcollege.edu">
                                </div>
                                <div class="col-md-6">
                                    <label for="website_url" class="form-label fw-semibold">Institution Website</label>
                                    <input type="url" name="website_url" id="website_url" 
                                           class="form-control form-control-solid" 
                                           value="{{ old('website_url', $institution['website_url'] ?? '') }}"
                                           placeholder="https://yourcollege.edu">
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-end">
                            <button type="submit" class="btn btn-success">
                                <i class="ki-duotone ki-check fs-2 me-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                Save URL Configuration
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Logos -->
                <div class="card mb-8">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ki-duotone ki-picture fs-1 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Logos & Icons
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-6">Upload logos and icons for your institution.</p>
                        
                        <div class="row g-6">
                            <!-- Favicon Upload -->
                            <div class="col-md-6">
                                <div class="border border-gray-300 rounded p-5">
                                    <h5 class="fw-bold mb-4">Favicon</h5>
                                    
                                    @if($logos['favicon'] ?? null)
                                        <div class="mb-4 text-center">
                                            <img src="{{ asset($logos['favicon']) }}" alt="Current Favicon" style="max-width: 64px; max-height: 64px;">
                                            <p class="text-muted mt-2 mb-0 small">Current Favicon</p>
                                        </div>
                                    @endif
                                    
                                    <form method="POST" action="{{ route('admin.branding.upload-logo') }}" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="logo_type" value="favicon">
                                        
                                        <div class="mb-3">
                                            <label for="favicon_file" class="form-label fw-semibold">Upload Favicon</label>
                                            <input type="file" name="logo" id="favicon_file" 
                                                   class="form-control form-control-solid" 
                                                   accept=".ico,.png,.svg"
                                                   required>
                                            <div class="form-text">Recommended: 32x32px or 64x64px, .ico or .png format</div>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-sm btn-light-primary w-100">
                                            <i class="ki-duotone ki-cloud-upload fs-4 me-1">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                            Upload Favicon
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- App Logo Upload -->
                            <div class="col-md-6">
                                <div class="border border-gray-300 rounded p-5">
                                    <h5 class="fw-bold mb-4">App Logo</h5>
                                    
                                    @if($logos['app'] ?? null)
                                        <div class="mb-4 text-center">
                                            <img src="{{ asset($logos['app']) }}" alt="Current App Logo" style="max-width: 200px; max-height: 100px;">
                                            <p class="text-muted mt-2 mb-0 small">Current App Logo</p>
                                        </div>
                                    @endif
                                    
                                    <form method="POST" action="{{ route('admin.branding.upload-logo') }}" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="logo_type" value="app">
                                        
                                        <div class="mb-3">
                                            <label for="app_logo_file" class="form-label fw-semibold">Upload App Logo</label>
                                            <input type="file" name="logo" id="app_logo_file" 
                                                   class="form-control form-control-solid" 
                                                   accept=".jpg,.jpeg,.png,.svg"
                                                   required>
                                            <div class="form-text">Recommended: Transparent background, .png or .svg format</div>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-sm btn-light-primary w-100">
                                            <i class="ki-duotone ki-cloud-upload fs-4 me-1">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                            Upload App Logo
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Primary Logo Upload -->
                            <div class="col-md-6">
                                <div class="border border-gray-300 rounded p-5">
                                    <h5 class="fw-bold mb-4">Primary Logo</h5>
                                    
                                    @if($logos['primary'] ?? null)
                                        <div class="mb-4 text-center">
                                            <img src="{{ asset($logos['primary']) }}" alt="Current Primary Logo" style="max-width: 200px; max-height: 100px;">
                                            <p class="text-muted mt-2 mb-0 small">Current Primary Logo</p>
                                        </div>
                                    @endif
                                    
                                    <form method="POST" action="{{ route('admin.branding.upload-logo') }}" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="logo_type" value="primary">
                                        
                                        <div class="mb-3">
                                            <label for="primary_logo_file" class="form-label fw-semibold">Upload Primary Logo</label>
                                            <input type="file" name="logo" id="primary_logo_file" 
                                                   class="form-control form-control-solid" 
                                                   accept=".jpg,.jpeg,.png,.svg"
                                                   required>
                                            <div class="form-text">Used on light backgrounds</div>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-sm btn-light-primary w-100">
                                            <i class="ki-duotone ki-cloud-upload fs-4 me-1">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                            Upload Primary Logo
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- White Logo Upload -->
                            <div class="col-md-6">
                                <div class="border border-gray-300 rounded p-5 bg-dark">
                                    <h5 class="fw-bold mb-4 text-white">White Logo</h5>
                                    
                                    @if($logos['white'] ?? null)
                                        <div class="mb-4 text-center">
                                            <img src="{{ asset($logos['white']) }}" alt="Current White Logo" style="max-width: 200px; max-height: 100px;">
                                            <p class="text-light mt-2 mb-0 small">Current White Logo</p>
                                        </div>
                                    @endif
                                    
                                    <form method="POST" action="{{ route('admin.branding.upload-logo') }}" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="logo_type" value="white">
                                        
                                        <div class="mb-3">
                                            <label for="white_logo_file" class="form-label fw-semibold text-white">Upload White Logo</label>
                                            <input type="file" name="logo" id="white_logo_file" 
                                                   class="form-control form-control-solid" 
                                                   accept=".jpg,.jpeg,.png,.svg"
                                                   required>
                                            <div class="form-text text-light">Used on dark backgrounds</div>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-sm btn-light w-100">
                                            <i class="ki-duotone ki-cloud-upload fs-4 me-1">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                            Upload White Logo
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Auth Logo Upload -->
                            <div class="col-md-6">
                                <div class="border border-gray-300 rounded p-5">
                                    <h5 class="fw-bold mb-4">Auth Pages Logo</h5>
                                    
                                    @if($logos['auth'] ?? null)
                                        <div class="mb-4 text-center">
                                            <img src="{{ asset($logos['auth']) }}" alt="Current Auth Logo" style="max-width: 200px; max-height: 100px;">
                                            <p class="text-muted mt-2 mb-0 small">Current Auth Logo</p>
                                        </div>
                                    @endif
                                    
                                    <form method="POST" action="{{ route('admin.branding.upload-logo') }}" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="logo_type" value="auth">
                                        
                                        <div class="mb-3">
                                            <label for="auth_logo_file" class="form-label fw-semibold">Upload Auth Logo</label>
                                            <input type="file" name="logo" id="auth_logo_file" 
                                                   class="form-control form-control-solid" 
                                                   accept=".jpg,.jpeg,.png,.svg"
                                                   required>
                                            <div class="form-text">Used on login, register, and password reset pages</div>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-sm btn-light-primary w-100">
                                            <i class="ki-duotone ki-cloud-upload fs-4 me-1">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                            Upload Auth Logo
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Color Scheme -->
                <div class="card mb-8">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ki-duotone ki-colorfilter fs-1 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Color Scheme
                        </h3>
                    </div>
                    <form method="POST" action="{{ route('admin.branding.update-colors') }}">
                        @csrf
                        <div class="card-body">
                            <p class="text-muted mb-6">Customize the primary colors used throughout your application.</p>
                            
                            <div class="row g-6">
                                <div class="col-md-4">
                                    <label for="primary_color" class="form-label required fw-semibold">Primary Color</label>
                                    <div class="input-group">
                                        <input type="color" name="primary_color" id="primary_color" 
                                               class="form-control form-control-color" 
                                               value="{{ old('primary_color', $colors['primary'] ?? '#3B82F6') }}" 
                                               required style="width: 60px;">
                                        <input type="text" class="form-control form-control-solid" 
                                               value="{{ old('primary_color', $colors['primary'] ?? '#3B82F6') }}" 
                                               readonly id="primary_color_text">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="secondary_color" class="form-label required fw-semibold">Secondary Color</label>
                                    <div class="input-group">
                                        <input type="color" name="secondary_color" id="secondary_color" 
                                               class="form-control form-control-color" 
                                               value="{{ old('secondary_color', $colors['secondary'] ?? '#64748B') }}" 
                                               required style="width: 60px;">
                                        <input type="text" class="form-control form-control-solid" 
                                               value="{{ old('secondary_color', $colors['secondary'] ?? '#64748B') }}" 
                                               readonly id="secondary_color_text">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="accent_color" class="form-label required fw-semibold">Accent Color</label>
                                    <div class="input-group">
                                        <input type="color" name="accent_color" id="accent_color" 
                                               class="form-control form-control-color" 
                                               value="{{ old('accent_color', $colors['accent'] ?? '#10B981') }}" 
                                               required style="width: 60px;">
                                        <input type="text" class="form-control form-control-solid" 
                                               value="{{ old('accent_color', $colors['accent'] ?? '#10B981') }}" 
                                               readonly id="accent_color_text">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="success_color" class="form-label required fw-semibold">Success Color</label>
                                    <div class="input-group">
                                        <input type="color" name="success_color" id="success_color" 
                                               class="form-control form-control-color" 
                                               value="{{ old('success_color', $colors['success'] ?? '#10B981') }}" 
                                               required style="width: 60px;">
                                        <input type="text" class="form-control form-control-solid" 
                                               value="{{ old('success_color', $colors['success'] ?? '#10B981') }}" 
                                               readonly id="success_color_text">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="warning_color" class="form-label required fw-semibold">Warning Color</label>
                                    <div class="input-group">
                                        <input type="color" name="warning_color" id="warning_color" 
                                               class="form-control form-control-color" 
                                               value="{{ old('warning_color', $colors['warning'] ?? '#F59E0B') }}" 
                                               required style="width: 60px;">
                                        <input type="text" class="form-control form-control-solid" 
                                               value="{{ old('warning_color', $colors['warning'] ?? '#F59E0B') }}" 
                                               readonly id="warning_color_text">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="danger_color" class="form-label required fw-semibold">Danger Color</label>
                                    <div class="input-group">
                                        <input type="color" name="danger_color" id="danger_color" 
                                               class="form-control form-control-color" 
                                               value="{{ old('danger_color', $colors['danger'] ?? '#EF4444') }}" 
                                               required style="width: 60px;">
                                        <input type="text" class="form-control form-control-solid" 
                                               value="{{ old('danger_color', $colors['danger'] ?? '#EF4444') }}" 
                                               readonly id="danger_color_text">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-end">
                            <button type="submit" class="btn btn-success">
                                <i class="ki-duotone ki-check fs-2 me-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                Save Color Scheme
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Theme Settings -->
                <div class="card mb-8">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ki-duotone ki-setting-3 fs-1 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                                <span class="path4"></span>
                                <span class="path5"></span>
                            </i>
                            Theme Settings
                        </h3>
                    </div>
                    <form method="POST" action="{{ route('admin.branding.update-theme-settings') }}">
                        @csrf
                        <div class="card-body">
                            <div class="row g-6">
                                <div class="col-md-6">
                                    <label for="card_style" class="form-label required fw-semibold">Authentication Card Style</label>
                                    <select name="card_style" id="card_style" class="form-select form-select-solid" required>
                                        <option value="elevated" {{ old('card_style', $themeSettings['card_style'] ?? 'elevated') === 'elevated' ? 'selected' : '' }}>Elevated (Shadow)</option>
                                        <option value="flat" {{ old('card_style', $themeSettings['card_style'] ?? 'elevated') === 'flat' ? 'selected' : '' }}>Flat (No Shadow)</option>
                                        <option value="bordered" {{ old('card_style', $themeSettings['card_style'] ?? 'elevated') === 'bordered' ? 'selected' : '' }}>Bordered</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex flex-column gap-4">
                                        <div class="form-check form-switch form-check-custom form-check-solid">
                                            <input type="hidden" name="show_institution_name" value="0">
                                            <input class="form-check-input" type="checkbox" name="show_institution_name" value="1" 
                                                   id="show_institution_name" {{ old('show_institution_name', $themeSettings['show_institution_name'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="show_institution_name">
                                                Show Institution Name on Login
                                            </label>
                                        </div>
                                        
                                        <div class="form-check form-switch form-check-custom form-check-solid">
                                            <input type="hidden" name="enable_animations" value="0">
                                            <input class="form-check-input" type="checkbox" name="enable_animations" value="1" 
                                                   id="enable_animations" {{ old('enable_animations', $themeSettings['enable_animations'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="enable_animations">
                                                Enable Authentication Animations
                                            </label>
                                        </div>
                                        
                                        <div class="form-check form-switch form-check-custom form-check-solid">
                                            <input type="hidden" name="show_auth_central_button" value="0">
                                            <input class="form-check-input" type="checkbox" name="show_auth_central_button" value="1" 
                                                   id="show_auth_central_button" {{ old('show_auth_central_button', $themeSettings['show_auth_central_button'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="show_auth_central_button">
                                                Show Auth Central Login Button
                                            </label>
                                            <div class="form-text">Hide Auth Central button if your institution doesn't use SSO authentication</div>
                                        </div>
                                        
                                        <div class="form-check form-switch form-check-custom form-check-solid">
                                            <input type="hidden" name="show_regular_login" value="0">
                                            <input class="form-check-input" type="checkbox" name="show_regular_login" value="1" 
                                                   id="show_regular_login" {{ old('show_regular_login', $themeSettings['show_regular_login'] ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="show_regular_login">
                                                Show Regular Login Form
                                            </label>
                                            <div class="form-text">Hide regular email/password login form if your institution only uses SSO</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <div class="card-footer d-flex justify-content-end">
                        <button type="submit" class="btn btn-success">
                            <i class="ki-duotone ki-check fs-2 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Save Theme Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Lecturer Access Control -->
    <div class="row mb-8">
        <div class="col-12">
            <div class="card card-flush">
                <div class="card-header">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-900">Lecturer Access Control Mode</span>
                        <span class="text-muted mt-1 fw-semibold fs-7">Configure how lecturers access exam results</span>
                    </h3>
                </div>
                <form method="POST" action="{{ route('admin.branding.update-theme-settings') }}">
                    @csrf
                    <div class="card-body">
                        <div class="mb-10">
                            <label class="form-label required">Access Control Mode</label>
                            <select name="lecturer_access_mode" class="form-select form-select-solid" required>
                                <option value="exam_creator" {{ old('lecturer_access_mode', $themeSettings['lecturer_access_mode'] ?? 'exam_creator') == 'exam_creator' ? 'selected' : '' }}>
                                    Exam Creator Mode
                                </option>
                                <option value="course_assignment" {{ old('lecturer_access_mode', $themeSettings['lecturer_access_mode'] ?? 'exam_creator') == 'course_assignment' ? 'selected' : '' }}>
                                    Course Assignment Mode
                                </option>
                            </select>
                            <div class="form-text mt-3">
                                <div class="alert alert-info mb-0">
                                    <h6 class="mb-2"><i class="bi bi-info-circle"></i> Access Mode Comparison:</h6>
                                    <ul class="mb-2">
                                        <li><strong>Exam Creator Mode:</strong> Lecturers can only view and manage exam results for exams they personally created</li>
                                        <li><strong>Course Assignment Mode:</strong> Lecturers can view and manage exam results for any courses they're assigned to, regardless of who created the exam</li>
                                    </ul>
                                    <div class="mt-3 pt-2 border-top">
                                        <i class="bi bi-gear"></i> To assign courses to lecturers, go to <a href="{{ route('admin.course-assignments') }}" target="_blank" class="fw-bold">Settings → Course Assignments</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Hidden fields to preserve other theme settings -->
                        <input type="hidden" name="card_style" value="{{ $themeSettings['card_style'] ?? 'elevated' }}">
                        <input type="hidden" name="show_institution_name" value="{{ $themeSettings['show_institution_name'] ?? 1 }}">
                        <input type="hidden" name="enable_animations" value="{{ $themeSettings['enable_animations'] ?? 1 }}">
                        <input type="hidden" name="show_auth_central_button" value="{{ $themeSettings['show_auth_central_button'] ?? 1 }}">
                        <input type="hidden" name="show_regular_login" value="{{ $themeSettings['show_regular_login'] ?? 1 }}">
                    </div>
                    <div class="card-footer d-flex justify-content-end">
                        <button type="submit" class="btn btn-success">
                            <i class="ki-duotone ki-check fs-2 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Save Access Control Mode
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Student ID Configuration -->
        <div class="row mb-8">
            <div class="col-12">
                <div class="card card-flush">
                    <form method="POST" action="{{ route('admin.branding.update-student-id') }}">
                        @csrf
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="ki-duotone ki-profile-user fs-2 me-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                    <span class="path4"></span>
                                </i>
                                Student ID Generation Settings
                            </h3>
                        </div>
                        <div class="card-body">
                            <!-- Format Selection -->
                            <div class="row mb-8">
                                <div class="col-md-6">
                                    <div class="mb-5">
                                        <label class="form-label required">Student ID Format</label>
                                        <select name="student_id_format" class="form-select" id="student_id_format">
                                            <option value="structured" {{ old('student_id_format', config('branding.student_id.format', 'structured')) === 'structured' ? 'selected' : '' }}>
                                                Structured (COLLEGE/DEPT/PROG/22/23/001)
                                            </option>
                                            <option value="simple" {{ old('student_id_format', config('branding.student_id.format')) === 'simple' ? 'selected' : '' }}>
                                                Simple (COLLEGEPROG240135)
                                            </option>
                                            <option value="custom" {{ old('student_id_format', config('branding.student_id.format')) === 'custom' ? 'selected' : '' }}>
                                                Custom Pattern
                                            </option>
                                        </select>
                                        <div class="form-text">Choose the format for automatically generated student IDs</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-5">
                                        <label class="form-label">Custom Pattern</label>
                                        <input type="text" name="student_id_custom_pattern" class="form-control" 
                                               value="{{ old('student_id_custom_pattern', config('branding.student_id.custom_pattern')) }}"
                                               placeholder="{INSTITUTION_SIMPLE}-{PROGRAM}-{SEQUENCE_4}"
                                               id="custom_pattern_input" 
                                               {{ config('branding.student_id.format') !== 'custom' ? 'disabled' : '' }}>
                                        <div class="form-text">
                                            Create your own pattern using placeholders and separators (-, /, or no separator)
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Institution Configuration -->
                            <div class="separator separator-dashed my-6"></div>
                            <h4 class="mb-5">Institution Configuration</h4>
                            <div class="row mb-6">
                                <div class="col-md-6">
                                    <div class="mb-5">
                                        <label class="form-label required">Institution Prefix (Full)</label>
                                        <input type="text" name="institution_prefix" class="form-control" 
                                               value="{{ old('institution_prefix', config('branding.student_id.institution_prefix', 'COLLEGE/DEPT')) }}"
                                               placeholder="COLLEGE/DEPT">
                                        <div class="form-text">Full institution prefix used in structured format (e.g., COLLEGE/DEPT, UNIV/ENG)</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-5">
                                        <label class="form-label required">Institution Code (Simple)</label>
                                        <input type="text" name="institution_simple" class="form-control" 
                                               value="{{ old('institution_simple', config('branding.student_id.institution_simple', 'COLLEGE')) }}"
                                               placeholder="COLLEGE">
                                        <div class="form-text">Simplified institution code for simple and custom formats (4-6 characters)</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Sequence Configuration -->
                            <div class="separator separator-dashed my-6"></div>
                            <h4 class="mb-5">Sequence Configuration</h4>
                            <div class="row mb-6">
                                <div class="col-md-6">
                                    <div class="form-check form-switch form-check-custom form-check-solid mb-4">
                                        <input type="hidden" name="enable_alphabetical_ordering" value="0">
                                        <input class="form-check-input" type="checkbox" name="enable_alphabetical_ordering" value="1" 
                                               id="enable_alphabetical_ordering" {{ old('enable_alphabetical_ordering', config('branding.student_id.enable_alphabetical_ordering', true)) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="enable_alphabetical_ordering">
                                            Enable Alphabetical Ordering
                                        </label>
                                        <div class="form-text">Assign sequence numbers based on alphabetical order of student names</div>
                                    </div>
                                    
                                    <div class="form-check form-switch form-check-custom form-check-solid">
                                        <input type="hidden" name="sequence_reset_yearly" value="0">
                                        <input class="form-check-input" type="checkbox" name="sequence_reset_yearly" value="1" 
                                               id="sequence_reset_yearly" {{ old('sequence_reset_yearly', config('branding.student_id.sequence_reset_yearly', true)) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="sequence_reset_yearly">
                                            Reset Sequence Each Year
                                        </label>
                                        <div class="form-text">Start sequence from 001 each academic year</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-5">
                                        <label class="form-label">Starting Sequence Number</label>
                                        <input type="number" name="sequence_start" class="form-control" 
                                               value="{{ old('sequence_start', config('branding.student_id.sequence_start', 1)) }}"
                                               min="1" max="999">
                                        <div class="form-text">First sequence number to use (usually 1)</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Examples and Documentation -->
                            <div class="separator separator-dashed my-6"></div>
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="card bg-light-info">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">📋 Available Placeholders</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <ul class="list-unstyled mb-0">
                                                        <li class="mb-2"><code>{INSTITUTION}</code> - Full prefix</li>
                                                        <li class="mb-2"><code>{INSTITUTION_SIMPLE}</code> - Simple code</li>
                                                        <li class="mb-2"><code>{PROGRAM}</code> - Program code (RN, RM, etc.)</li>
                                                        <li class="mb-2"><code>{PROGRAM_SIMPLE}</code> - Short program code</li>
                                                    </ul>
                                                </div>
                                                <div class="col-md-6">
                                                    <ul class="list-unstyled mb-0">
                                                        <li class="mb-2"><code>{YEAR_FULL}</code> - 22/23</li>
                                                        <li class="mb-2"><code>{YEAR_SIMPLE}</code> - 23</li>
                                                        <li class="mb-2"><code>{SEQUENCE_3}</code> - 001</li>
                                                        <li class="mb-2"><code>{SEQUENCE_4}</code> - 0001</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-light-success">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">✅ Pattern Examples</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <strong>Structured:</strong><br>
                                                <code>COLLEGE/DEPT/RN/22/23/001</code>
                                            </div>
                                            <div class="mb-3">
                                                <strong>Simple:</strong><br>
                                                <code>COLLEGERN230135</code>
                                            </div>
                                            <div class="mb-3">
                                                <strong>Custom with dashes:</strong><br>
                                                <code>{INSTITUTION_SIMPLE}-{PROGRAM}-{SEQUENCE_4}</code><br>
                                                → <code>COLLEGE-RN-0135</code>
                                            </div>
                                            <div class="mb-0">
                                                <strong>Custom with slashes:</strong><br>
                                                <code>{INSTITUTION}/{PROGRAM}/{SEQUENCE_3}</code><br>
                                                → <code>COLLEGE/RN/135</code>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-end">
                            <button type="submit" class="btn btn-success">
                                <i class="ki-duotone ki-check fs-2 me-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                Save Student ID Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Update color text inputs when color picker changes
        document.addEventListener('DOMContentLoaded', function() {
            const colorInputs = ['primary_color', 'secondary_color', 'accent_color', 'success_color', 'warning_color', 'danger_color'];
            
            colorInputs.forEach(function(colorId) {
                const colorInput = document.getElementById(colorId);
                const textInput = document.getElementById(colorId + '_text');
                
                if (colorInput && textInput) {
                    colorInput.addEventListener('change', function() {
                        textInput.value = this.value.toUpperCase();
                    });
                }
            });

            // Handle student ID format changes
            const formatSelect = document.getElementById('student_id_format');
            const customPatternInput = document.getElementById('custom_pattern_input');

            if (formatSelect && customPatternInput) {
                function toggleCustomPattern() {
                    if (formatSelect.value === 'custom') {
                        customPatternInput.disabled = false;
                        customPatternInput.required = true;
                    } else {
                        customPatternInput.disabled = true;
                        customPatternInput.required = false;
                    }
                }

                formatSelect.addEventListener('change', toggleCustomPattern);
                toggleCustomPattern(); // Initialize on page load
            }
        });
    </script>
    @endpush
</x-dashboard.default>