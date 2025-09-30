@php
    $authService = app(\App\Services\AuthenticationService::class);
@endphp

<x-backend.auth title="Login" description="Choose your preferred login method">
    @if (session('status'))
        <div class="alert alert-success mb-4" role="alert">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger mb-4" role="alert">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="px-4 mx-auto mw-sm">
        {{-- AuthCentral SSO Login Option --}}
        <div class="mb-4">
            <a href="{{ $authService->getAuthCentralLoginUrl() }}"
                class="btn btn-lg btn-primary fs-11 w-100 text-primary-light d-flex align-items-center justify-content-center"
                style="background-color: var(--brand-primary, #007bff); border-color: var(--brand-primary, #007bff);">
                <i class="fas fa-sign-in-alt me-2"></i>
                Sign In with AuthCentral
            </a>
            <p class="text-center text-muted fs-13 mt-2 mb-0">Single Sign-On (SSO) - Quick & Secure</p>
        </div>

        {{-- Divider --}}
        <div class="text-center my-4">
            <span class="text-muted fs-13 px-3" style="background: white; position: relative; z-index: 1;">
                OR
            </span>
            <hr style="margin-top: -12px; border-color: #dee2e6;">
        </div>

        {{-- Email/Password Login Form --}}
        <form method="POST" action="{{ route('regular.login') }}">
            @csrf
            
            <div class="mb-4">
                <label for="email" class="form-label fs-13 fw-medium text-light-dark">Email Address</label>
                <input type="email" 
                       class="form-control @error('email') is-invalid @enderror" 
                       id="email" 
                       name="email" 
                       value="{{ old('email') }}" 
                       required 
                       autofocus 
                       autocomplete="username">
                @error('email')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password" class="form-label fs-13 fw-medium text-light-dark">Password</label>
                <div style="position: relative;">
                    <input type="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           id="password" 
                           name="password" 
                           required 
                           autocomplete="current-password"
                           style="padding-right: 3rem;">
                    <button type="button" 
                            class="password-toggle" 
                            onclick="togglePassword()"
                            style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: #9ca3af; cursor: pointer; font-size: 1rem; transition: color 0.3s ease;"
                            onmouseover="this.style.color='var(--brand-primary, #007bff)'" 
                            onmouseout="this.style.color='#9ca3af'">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                @error('password')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="mb-4 d-flex justify-content-between align-items-center">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label fs-13 text-light-dark" for="remember">
                        Remember me
                    </label>
                </div>
                <a class="text-primary link-primary fs-13" href="{{ route('password.request') }}" 
                   style="color: var(--brand-primary, #007bff) !important;">
                    Forgot password?
                </a>
            </div>

            <div class="mb-6">
                <button type="submit" class="btn btn-lg btn-outline-primary fs-11 w-100"
                        style="border-color: var(--brand-primary, #007bff); color: var(--brand-primary, #007bff);">
                    <i class="fas fa-envelope me-2"></i>
                    Sign In with Email
                </button>
            </div>
        </form>

        {{-- Registration Links --}}
        <div class="text-center">
            <p class="mb-2 fs-13 fw-medium text-light-dark">
                <span>Don't have an account?</span>
            </p>
            <div class="d-flex justify-content-center gap-3">
                @if ($authService->getStaffSignupUrl())
                    <a class="text-primary link-primary fs-13" href="{{ $authService->getStaffSignupUrl() }}"
                       style="color: var(--brand-primary, #007bff) !important;">Staff Registration</a>
                @endif
                @if ($authService->getStudentSignupUrl())
                    <a class="text-primary link-primary fs-13" href="{{ $authService->getStudentSignupUrl() }}"
                       style="color: var(--brand-primary, #007bff) !important;">Student Registration</a>
                @endif
            </div>
        </div>

        {{-- Institution Footer --}}
        @if(config('branding.institution.website_url') || config('branding.institution.support_email'))
            <div class="mt-5 pt-4 border-top text-center">
                <div class="d-flex justify-content-center gap-3 text-muted fs-13">
                    @if(config('branding.institution.website_url'))
                        <a href="{{ config('branding.institution.website_url') }}" 
                           target="_blank" 
                           class="text-muted text-decoration-none">
                            <i class="bi bi-globe me-1"></i>Website
                        </a>
                    @endif
                    @if(config('branding.institution.support_email'))
                        <a href="mailto:{{ config('branding.institution.support_email') }}" 
                           class="text-muted text-decoration-none">
                            <i class="bi bi-envelope me-1"></i>Support
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-backend.auth>