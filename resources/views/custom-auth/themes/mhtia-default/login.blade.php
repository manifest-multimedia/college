@php
    $authService = app(\App\Services\AuthenticationService::class);
@endphp

<x-backend.auth title="{{ config('branding.institution.name', config('app.name')) }} &mdash; Login" description="Sign in to your account">
    <!--begin::Form-->
    <form class="form w-100" novalidate="novalidate" action="{{ route('regular.login') }}" method="POST">
        @csrf
        <!--begin::Heading-->
        <div class="mb-11 text-center">
            <!--begin::Title-->
            <h1 class="mb-3 text-gray-900 fw-bolder">Sign In</h1>
            <!--end::Title-->
            <!--begin::Subtitle-->
            <div class="text-gray-500 fw-semibold fs-6">Sign in to your account</div>
            <!--end::Subtitle=-->
        </div>
        <!--begin::Heading-->

        {{-- AuthCentral SSO Login Option --}}
        @if($authService->isAuthCentral() || config('branding.features.show_sso', true))
            <!--begin::Login options-->
            <div class="mb-9 row g-3 d-flex justify-content-center">
                <!--begin::Col-->
                <div class="col-md-8">
                    <!--begin::AuthCentral link-->
                    <a href="{{ $authService->getAuthCentralLoginUrl() ?? '#' }}" 
                       class="btn btn-flex btn-outline btn-text-gray-700 btn-active-color-primary bg-state-light flex-center text-nowrap w-100">
                        <img alt="AuthCentral Logo" 
                             src="{{ asset('images/authcentral-icon.svg') }}" 
                             class="h-35px me-3" />
                        Sign in with AuthCentral
                    </a>
                    <!--end::AuthCentral link-->
                </div>
                <!--end::Col-->
            </div>
            <!--end::Login options-->
            
            <!--begin::Separator-->
            <div class="my-14 separator separator-content">
                <span class="text-gray-500 w-125px fw-semibold fs-7">Or with email</span>
            </div>
            <!--end::Separator-->
        @endif

        {{-- Error Messages --}}
        @if (session('status'))
            <div class="alert alert-success mb-8" role="alert">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger mb-8">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!--begin::Input group-->
        <div class="mb-8 fv-row">
            <!--begin::Email-->
            <input type="email" 
                   placeholder="Email" 
                   name="email" 
                   value="{{ old('email') }}"
                   autocomplete="username" 
                   class="bg-transparent form-control @error('email') is-invalid @enderror" 
                   required />
            <!--end::Email-->
            @error('email')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
        <!--end::Input group-->
        
        <div class="mb-3 fv-row">
            <!--begin::Password-->
            <input type="password" 
                   placeholder="Password" 
                   name="password" 
                   autocomplete="current-password" 
                   class="bg-transparent form-control @error('password') is-invalid @enderror" 
                   required />
            <!--end::Password-->
            @error('password')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
        <!--end::Input group-->
        
        <!--begin::Wrapper-->
        <div class="flex-wrap gap-3 mb-8 d-flex flex-stack fs-base fw-semibold">
            <!--begin::Remember me-->
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label text-gray-600" for="remember">
                    Remember me
                </label>
            </div>
            <!--end::Remember me-->
            
            <!--begin::Link-->
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="link-primary">Forgot Password?</a>
            @endif 
            <!--end::Link-->
        </div>
        <!--end::Wrapper-->
        
        <!--begin::Submit button-->
        <div class="mb-10 d-grid">
            <button type="submit" id="kt_sign_in_submit" class="btn btn-primary">
                <!--begin::Indicator label-->
                <span class="indicator-label">Sign In</span>
                <!--end::Indicator label-->
                <!--begin::Indicator progress-->
                <span class="indicator-progress">Please wait... 
                <span class="align-middle spinner-border spinner-border-sm ms-2"></span></span>
                <!--end::Indicator progress-->
            </button>
        </div>
        <!--end::Submit button-->
        
        <!--begin::Sign up-->
        <div class="text-center text-gray-500 fw-semibold fs-6">
            Don't have an account?
            <div class="mt-2">
                @if ($authService->getStaffSignupUrl())
                    <a href="{{ $authService->getStaffSignupUrl() }}" class="link-primary me-3">Staff Registration</a>
                @endif
                @if ($authService->getStudentSignupUrl())
                    <a href="{{ $authService->getStudentSignupUrl() }}" class="link-primary">Student Registration</a>
                @endif
            </div>
        </div>
        <!--end::Sign up-->
    </form>
    <!--end::Form-->
</x-backend.auth>