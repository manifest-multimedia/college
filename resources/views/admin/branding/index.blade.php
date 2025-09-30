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

            <!-- Configuration Form -->
            <form method="POST" action="{{ route('admin.branding.update') }}" enctype="multipart/form-data">
                @csrf
                
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
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="card">
                    <div class="card-body text-center">
                        <button type="submit" class="btn btn-primary">
                            <i class="ki-duotone ki-check fs-2 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Save Branding Configuration
                        </button>
                    </div>
                </div>
            </form>
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
        });
    </script>
    @endpush
</x-dashboard.default>