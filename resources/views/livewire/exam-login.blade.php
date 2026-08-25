@php
    $theme = config('branding.exam_portal_theme', 'split-screen');
    $instName = config('branding.institution.name', config('app.name', 'College360'));
    $instShortName = config('branding.institution.short_name', 'College360');
    $logoUrl = config('branding.logo.auth') ?: (config('branding.logo.primary') ?: asset('images/logos/default-logo.svg'));
    $supportEmail = config('branding.institution.support_email', 'support@college.edu');
    $supportPhone = config('branding.institution.phone', null);
@endphp

<div>
<style>
    .exam-portal-input::placeholder {
        color: #64748b !important;
        opacity: 1 !important;
    }
    .exam-portal-input {
        color: #0f172a !important;
        background-color: #f8fafc !important;
        border: 1px solid #cbd5e1 !important;
        padding-left: 0.875rem !important;
        padding-right: 0.875rem !important;
    }
    .exam-portal-input:focus {
        border-color: #2563eb !important;
        background-color: #ffffff !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15) !important;
    }
</style>

@if($theme === 'default')
    {{-- Theme 4: Legacy Default Floating Card --}}
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="p-4 shadow-lg card" style="max-width: 400px; width: 100%;">
            <h3 class="mb-4 text-center"> Exam Login</h3>
            
            @if($errors->any() || session()->has('error') || session()->has('message'))
                <div class="alert {{ $errors->any() || session()->has('error') ? 'alert-danger' : 'alert-success' }}">
                    @if($errors->any())
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @else
                        {{ session('error') ?? session('message') }}
                    @endif
                </div>
            @endif

            <div class="mb-3">
                <label for="studentID" class="form-label fw-bold">Student ID</label>
                <input type="text" class="form-control" id="studentID" placeholder="Enter your Student ID" required wire:model="studentId">
            </div>
            <div class="mb-3">
                <label for="examPassword" class="form-label fw-bold">Exam Password</label>
                <input type="{{ $showPassword ? 'text' : 'password' }}" class="form-control" id="examPassword" placeholder="Enter your Exam Password" required wire:model.live="examPassword">
            </div>
            <button wire:click="startExam" class="btn btn-primary w-100" type="submit" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="startExam">Start Exam</span>
                <span wire:loading wire:target="startExam"><i class="fas fa-spinner fa-spin me-1"></i> Starting...</span>
            </button>
        </div>
    </div>

@elseif($theme === 'centered-gateway')
    {{-- Theme 2: Premium Centered Exam Gateway --}}
    <div class="min-vh-100 d-flex flex-column justify-content-between p-3 p-md-5" style="background: linear-gradient(135deg, #0F172A 0%, #1E293B 50%, #0F172A 100%);">
        <div class="text-center pt-3">
            @if($logoUrl && file_exists(public_path($logoUrl)))
                <img src="{{ asset($logoUrl) }}" alt="{{ $instName }}" class="mb-3" style="max-height: 70px; width: auto; object-fit: contain;">
            @endif
            <h2 class="heading-font text-white fw-bold mb-2 fs-3">{{ $instName }}</h2>
            <span class="d-inline-flex align-items-center px-3 py-2 rounded-pill fw-semibold" style="background: rgba(14, 165, 233, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); font-size: 0.875rem;">
                <i class="fas fa-shield-halved me-2"></i> College360 Secure Examination Portal
            </span>
        </div>

        <div class="my-auto py-4">
            <div class="mx-auto card border-0 shadow-lg rounded-4 overflow-hidden" style="max-width: 460px; background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(12px);">
                <div class="card-body p-4 p-sm-5">
                    <div class="text-center mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 56px; height: 56px; background: rgba(37, 99, 235, 0.1); color: #2563eb;">
                            <i class="fas fa-lock fs-4"></i>
                        </div>
                        <h3 class="heading-font fw-bold mb-1" style="color: #0f172a;">Access Your Exam</h3>
                        <p class="mb-0" style="color: #475569; font-size: 0.9rem;">Enter your credentials to securely enter your scheduled examination.</p>
                    </div>

                    @if($errors->any() || session()->has('error') || session()->has('message'))
                        <div class="alert {{ $errors->any() || session()->has('error') ? 'alert-danger' : 'alert-success' }} border-0 shadow-sm rounded-3 mb-4">
                            <div class="d-flex align-items-start">
                                <i class="fas {{ $errors->any() || session()->has('error') ? 'fa-triangle-exclamation text-danger' : 'fa-circle-check text-success' }} fs-5 me-2 mt-1"></i>
                                <div class="small" style="font-size: 0.9rem;">
                                    @if($errors->any())
                                        <ul class="mb-0 ps-3">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        {{ session('error') ?? session('message') }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <form wire:submit.prevent="startExam" autocomplete="off">
                        <div class="mb-3">
                            <label for="studentId" class="form-label fw-bold mb-2" style="color: #1e293b; font-size: 0.9rem;">Student ID</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0 px-3" style="background-color: #f1f5f9; border-color: #cbd5e1; color: #475569;"><i class="fas fa-id-card"></i></span>
                                <input type="text" class="form-control exam-portal-input border-start-0 ps-3" id="studentId" wire:model="studentId" placeholder="Enter your Student ID" required autocomplete="off">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="examPassword" class="form-label fw-bold mb-2" style="color: #1e293b; font-size: 0.9rem;">Exam Password</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0 px-3" style="background-color: #f1f5f9; border-color: #cbd5e1; color: #475569;"><i class="fas fa-key"></i></span>
                                <input type="{{ $showPassword ? 'text' : 'password' }}" class="form-control exam-portal-input border-start-0 border-end-0 ps-3" id="examPassword" wire:model="examPassword" placeholder="Enter exam password" required>
                                <button type="button" class="input-group-text border-start-0 px-3" style="background-color: #f1f5f9; border-color: #cbd5e1; color: #475569; cursor: pointer;" wire:click="toggleShowPassword">
                                    <i class="fas {{ $showPassword ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn w-100 py-3 rounded-3 fw-bold shadow-sm" style="background-color: #2563eb; color: #ffffff; font-size: 1rem; border: none;" wire:loading.attr="disabled" wire:target="startExam">
                            <span wire:loading.remove wire:target="startExam">
                                Continue to Exam <i class="fas fa-arrow-right ms-2"></i>
                            </span>
                            <span wire:loading wire:target="startExam">
                                <i class="fas fa-spinner fa-spin me-2"></i> Verifying Credentials...
                            </span>
                        </button>
                    </form>

                    <div class="mt-4 pt-3 border-top text-center">
                        <div class="d-flex justify-content-center gap-3 small mb-2" style="color: #475569;">
                            <span><i class="fas fa-check-circle text-success me-1"></i> Secure Access</span>
                            <span><i class="fas fa-clock text-warning me-1"></i> Timed Session</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center pb-2" style="color: #94a3b8; font-size: 0.9rem;">
            Having trouble accessing your exam? Contact <a href="mailto:{{ $supportEmail }}" class="fw-semibold text-decoration-none" style="color: #38bdf8;">{{ $supportEmail }}</a>
        </div>
    </div>

@elseif($theme === 'academic-portal')
    {{-- Theme 3: Academic Portal Layout --}}
    <div class="min-vh-100 d-flex flex-column bg-light">
        <header class="text-white py-3 shadow-sm" style="background-color: #0F172A !important;">
            <div class="container d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    @if($logoUrl && file_exists(public_path($logoUrl)))
                        <img src="{{ asset($logoUrl) }}" alt="{{ $instName }}" class="me-3" style="max-height: 44px; width: auto; object-fit: contain;">
                    @endif
                    <div>
                        <h1 class="heading-font h5 text-white mb-0 fw-bold">{{ $instName }}</h1>
                        <span style="color: #38bdf8; font-size: 0.85rem;"><i class="fas fa-graduation-cap me-1"></i> Online Examination Center</span>
                    </div>
                </div>
                <div class="d-none d-md-block text-end">
                    <span class="badge px-3 py-2 rounded-pill" style="background: rgba(37, 99, 235, 0.2); color: #60a5fa; border: 1px solid rgba(96, 165, 250, 0.3);"><i class="fas fa-shield-alt me-1"></i> Secure Gateway</span>
                </div>
            </div>
        </header>

        <main class="container my-auto py-5">
            <div class="row g-4 align-items-center justify-content-center">
                <div class="col-lg-6">
                    <div class="pe-lg-4">
                        <h2 class="heading-font fw-bold fs-2 mb-3" style="color: #0f172a;">Student Examination Login</h2>
                        <p class="fs-6 mb-4" style="color: #334155; line-height: 1.6;">Welcome to the institution examination system. Please verify your identity using your assigned Student ID and the Examination Password provided by your supervisor.</p>

                        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                            <h6 class="fw-bold mb-3" style="color: #2563eb;"><i class="fas fa-info-circle me-2"></i> Examination Guidelines</h6>
                            <ul class="list-unstyled mb-0 space-y-2" style="color: #334155; font-size: 0.925rem;">
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Ensure you are connected to a stable internet connection.</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Your timer will begin automatically once authentication succeeds.</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Do not close or refresh your browser tab during the exam.</li>
                                <li><i class="fas fa-check text-success me-2"></i> Progress is automatically saved as you answer questions.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card border-0 shadow-lg rounded-4 overflow-hidden bg-white">
                        <div class="card-header text-white p-4 text-center" style="background-color: #2563eb;">
                            <h4 class="heading-font fw-bold mb-0 text-white"><i class="fas fa-user-lock me-2"></i> Examination Access</h4>
                        </div>
                        <div class="card-body p-4">
                            @if($errors->any() || session()->has('error') || session()->has('message'))
                                <div class="alert {{ $errors->any() || session()->has('error') ? 'alert-danger' : 'alert-success' }} border-0 rounded-3 mb-4">
                                    @if($errors->any())
                                        <ul class="mb-0 ps-3">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        {{ session('error') ?? session('message') }}
                                    @endif
                                </div>
                            @endif

                            <form wire:submit.prevent="startExam" autocomplete="off">
                                <div class="mb-3">
                                    <label for="studentId" class="form-label fw-bold mb-2" style="color: #1e293b; font-size: 0.9rem;">Student ID</label>
                                    <input type="text" class="form-control form-control-lg fs-6 exam-portal-input ps-3" id="studentId" wire:model="studentId" placeholder="Enter your Student ID" required>
                                </div>

                                <div class="mb-4">
                                    <label for="examPassword" class="form-label fw-bold mb-2" style="color: #1e293b; font-size: 0.9rem;">Exam Password</label>
                                    <div class="input-group">
                                        <input type="{{ $showPassword ? 'text' : 'password' }}" class="form-control form-control-lg fs-6 exam-portal-input border-end-0 ps-3" id="examPassword" wire:model="examPassword" placeholder="Enter Exam Password" required>
                                        <button type="button" class="btn btn-outline-secondary px-3" style="border-color: #cbd5e1;" wire:click="toggleShowPassword">
                                            <i class="fas {{ $showPassword ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                        </button>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-lg w-100 fw-bold fs-6 shadow-sm" style="background-color: #2563eb; color: #ffffff; border: none; padding: 0.875rem;" wire:loading.attr="disabled" wire:target="startExam">
                                    <span wire:loading.remove wire:target="startExam">
                                        Access Examination <i class="fas fa-arrow-right ms-2"></i>
                                    </span>
                                    <span wire:loading wire:target="startExam">
                                        <i class="fas fa-spinner fa-spin me-2"></i> Authenticating...
                                    </span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <footer class="bg-white border-top py-3 mt-auto">
            <div class="container text-center small" style="color: #475569;">
                Powered by <strong style="color: #0f172a;">College360 Examination Platform</strong>
            </div>
        </footer>
    </div>

@else
    {{-- Theme 1: Split-Screen Exam Gateway (Alternative A - Recommended Default) --}}
    <div class="min-vh-100 d-flex flex-column flex-lg-row overflow-hidden" style="background-color: #F8FAFC;">
        <!-- Left Brand / Context Panel (Desktop Split 55%) -->
        <div class="col-lg-6 col-xl-7 d-flex flex-column justify-content-between p-4 p-md-5 text-white position-relative overflow-hidden" 
             style="background: linear-gradient(135deg, #0A192F 0%, #112240 55%, #1E3A8A 100%); min-height: 100vh;">
            
            <!-- Atmospheric Pattern Overlay -->
            <div class="position-absolute top-0 start-0 w-100 h-100 opacity-10 pointer-events-none" 
                 style="background-image: radial-gradient(circle at 2px 2px, rgba(255,255,255,0.3) 1px, transparent 0); background-size: 32px 32px;"></div>
            <div class="position-absolute top-0 end-0 w-50 h-50 rounded-circle opacity-20 pointer-events-none" 
                 style="background: radial-gradient(circle, #3B82F6 0%, transparent 70%); transform: translate(30%, -30%); filter: blur(60px);"></div>

            <!-- Header Branding -->
            <div class="position-relative z-1 mb-4">
                <div class="d-flex align-items-center gap-3">
                    @if($logoUrl && file_exists(public_path($logoUrl)))
                        <div class="bg-white p-2 rounded-3 shadow-sm d-inline-flex align-items-center justify-content-center" style="max-height: 56px;">
                            <img src="{{ asset($logoUrl) }}" alt="{{ $instName }}" style="max-height: 40px; width: auto; object-fit: contain;">
                        </div>
                    @endif
                    <div>
                        <h2 class="heading-font text-white fw-bold mb-0 fs-4" style="color: #ffffff !important; text-shadow: 0 1px 2px rgba(0,0,0,0.4);">{{ $instName }}</h2>
                        <span class="small fw-semibold" style="color: #38bdf8;"><i class="fas fa-shield-halved me-1"></i> College360 Exam Portal</span>
                    </div>
                </div>
            </div>

            <!-- Center Context Panel -->
            <div class="position-relative z-1 my-auto py-4">
                <div class="d-inline-block mb-3">
                    <span class="d-inline-flex align-items-center px-3 py-2 rounded-pill fw-semibold" style="background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); font-size: 0.85rem;">
                        <i class="fas fa-lock me-2"></i> Official Examination Environment
                    </span>
                </div>

                <h1 class="heading-font display-6 fw-extrabold mb-3" style="color: #ffffff !important; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">Ready for your exam?</h1>
                <p class="fs-5 mb-4 style-leading" style="color: #cbd5e1; max-width: 560px; line-height: 1.6;">
                    Welcome to your institution's secure assessment gateway. Enter your credentials to begin your timed examination.
                </p>

                <!-- Trust Indicators -->
                <div class="d-flex flex-column gap-3 mb-4" style="max-width: 520px;">
                    <div class="d-flex align-items-center p-3 rounded-3 shadow-sm" style="background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.15); backdrop-filter: blur(8px);">
                        <div class="me-3 d-flex align-items-center justify-content-center flex-shrink-0" style="background: rgba(56, 189, 248, 0.2); color: #38bdf8; width: 42px; height: 42px; border-radius: 50%;">
                            <i class="fas fa-shield-alt fs-5"></i>
                        </div>
                        <div>
                            <span class="fw-bold d-block" style="color: #ffffff; font-size: 0.95rem;">Secure & Monitored Gateway</span>
                            <span style="color: #cbd5e1; font-size: 0.85rem;">Encrypted transmission and device verification</span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center p-3 rounded-3 shadow-sm" style="background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.15); backdrop-filter: blur(8px);">
                        <div class="me-3 d-flex align-items-center justify-content-center flex-shrink-0" style="background: rgba(251, 191, 36, 0.2); color: #fbbf24; width: 42px; height: 42px; border-radius: 50%;">
                            <i class="fas fa-clock fs-5"></i>
                        </div>
                        <div>
                            <span class="fw-bold d-block" style="color: #ffffff; font-size: 0.95rem;">Timed Assessment Environment</span>
                            <span style="color: #cbd5e1; font-size: 0.85rem;">Real-time session management and auto-submission</span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center p-3 rounded-3 shadow-sm" style="background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.15); backdrop-filter: blur(8px);">
                        <div class="me-3 d-flex align-items-center justify-content-center flex-shrink-0" style="background: rgba(74, 222, 128, 0.2); color: #4ade80; width: 42px; height: 42px; border-radius: 50%;">
                            <i class="fas fa-cloud-arrow-up fs-5"></i>
                        </div>
                        <div>
                            <span class="fw-bold d-block" style="color: #ffffff; font-size: 0.95rem;">Automatic Progress Protection</span>
                            <span style="color: #cbd5e1; font-size: 0.85rem;">Your exam progress is automatically saved throughout</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Branding -->
            <div class="position-relative z-1 pt-3 border-top border-white border-opacity-10 d-flex justify-content-between align-items-center">
                <span style="color: #94a3b8; font-size: 0.875rem;">Powered by College360 Examination Platform</span>
                <span style="color: #38bdf8; font-size: 0.875rem;"><i class="fas fa-check-circle text-success me-1"></i> System Online</span>
            </div>
        </div>

        <!-- Right Authentication Panel (Desktop Split 45%) -->
        <div class="col-lg-6 col-xl-5 d-flex flex-column justify-content-between p-4 p-md-5 bg-white" style="min-height: 100vh;">
            <div class="my-auto w-100 mx-auto" style="max-width: 440px;">
                <!-- Mobile Header Brand -->
                <div class="d-lg-none text-center mb-4 pb-2 border-bottom">
                    @if($logoUrl && file_exists(public_path($logoUrl)))
                        <img src="{{ asset($logoUrl) }}" alt="{{ $instName }}" class="mb-2" style="max-height: 48px; width: auto; object-fit: contain;">
                    @endif
                    <h3 class="heading-font fw-bold text-dark fs-5 mb-0">{{ $instName }}</h3>
                    <span class="text-muted small">Examination Gateway</span>
                </div>

                <div class="text-center text-lg-start mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 52px; height: 52px; background: rgba(37, 99, 235, 0.1); color: #2563eb;">
                        <i class="fas fa-key fs-4"></i>
                    </div>
                    <h2 class="heading-font fw-bold mb-1 fs-3" style="color: #0f172a;">Access Your Exam</h2>
                    <p class="mb-0" style="color: #475569; font-size: 0.95rem;">Enter your credentials to securely enter your examination.</p>
                </div>

                <!-- Error Alert Box -->
                @if($errors->any() || session()->has('error') || session()->has('message'))
                    <div class="alert {{ $errors->any() || session()->has('error') ? 'alert-danger' : 'alert-success' }} border-0 shadow-sm rounded-3 mb-4">
                        <div class="d-flex align-items-start">
                            <i class="fas {{ $errors->any() || session()->has('error') ? 'fa-triangle-exclamation text-danger' : 'fa-circle-check text-success' }} fs-5 me-2 mt-1"></i>
                            <div style="font-size: 0.9rem;">
                                @if($errors->any())
                                    <ul class="mb-0 ps-3">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    {{ session('error') ?? session('message') }}
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Auth Form -->
                <form wire:submit.prevent="startExam" autocomplete="off">
                    <div class="mb-3">
                        <label for="studentIdInput" class="form-label fw-bold mb-2" style="color: #1e293b; font-size: 0.9rem;">Student ID</label>
                        <div class="input-group">
                            <span class="input-group-text border-end-0 px-3" style="background-color: #f1f5f9; border-color: #cbd5e1; color: #475569;"><i class="fas fa-user-graduate"></i></span>
                            <input type="text" class="form-control form-control-lg fs-6 exam-portal-input border-start-0 ps-3 @error('studentId') is-invalid @enderror" 
                                   id="studentIdInput" wire:model="studentId" placeholder="Enter your Student ID" required autocomplete="off">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="examPasswordInput" class="form-label fw-bold mb-2" style="color: #1e293b; font-size: 0.9rem;">Exam Password</label>
                        <div class="input-group">
                            <span class="input-group-text border-end-0 px-3" style="background-color: #f1f5f9; border-color: #cbd5e1; color: #475569;"><i class="fas fa-lock"></i></span>
                            <input type="{{ $showPassword ? 'text' : 'password' }}" 
                                   class="form-control form-control-lg fs-6 exam-portal-input border-start-0 border-end-0 ps-3 @error('examPassword') is-invalid @enderror" 
                                   id="examPasswordInput" wire:model="examPassword" placeholder="Enter your Exam Password" required>
                            <button type="button" class="input-group-text border-start-0 px-3" style="background-color: #f1f5f9; border-color: #cbd5e1; color: #475569; cursor: pointer;" wire:click="toggleShowPassword" title="Toggle password visibility">
                                <i class="fas {{ $showPassword ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-lg w-100 rounded-3 fw-bold fs-6 shadow-sm mb-3" 
                            style="background-color: #2563eb; color: #ffffff; border: none; padding: 0.875rem 1.5rem;"
                            wire:loading.attr="disabled" wire:target="startExam">
                        <span wire:loading.remove wire:target="startExam">
                            Continue to Exam <i class="fas fa-arrow-right ms-2"></i>
                        </span>
                        <span wire:loading wire:target="startExam">
                            <i class="fas fa-spinner fa-spin me-2"></i> Verifying Credentials...
                        </span>
                    </button>
                </form>

                <!-- Help / Support Footer -->
                <div class="text-center text-lg-start mt-4 pt-3 border-top">
                    <p class="mb-0" style="color: #475569; font-size: 0.9rem;">
                        Having trouble accessing your exam? 
                        <a href="mailto:{{ $supportEmail }}" class="fw-semibold text-decoration-none ms-1" style="color: #2563eb;">
                            Contact Support
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
@endif
</div>