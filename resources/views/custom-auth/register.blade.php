@php
    $authService = app(\App\Services\AuthenticationService::class);
    $userType = $userType ?? 'staff';
    $isStudent = $userType === 'student';
    $title = $isStudent ? 'Student Registration' : 'Staff Registration';
    $description = $isStudent ? 'Create your student account' : 'Create your staff account';
    $actionUrl = $isStudent ? route('students.register') : route('staff.register');
    
    $theme = config('branding.auth_theme', 'default');
    $themeView = "custom-auth.themes.{$theme}.register";
    
    // Check if theme-specific register view exists, fallback to default component
    if (!view()->exists($themeView)) {
        // Fallback to component-based registration
        $useComponent = true;
    } else {
        $useComponent = false;
    }
@endphp

@if($useComponent)
<x-backend.auth :title="$title" :description="$description">
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

    <form method="POST" action="{{ $actionUrl }}" class="px-4 mx-auto mw-sm">
        @csrf

        <div class="mb-4">
            <label for="name" class="form-label fs-13 fw-medium text-light-dark">Full Name</label>
            <input type="text" 
                   class="form-control @error('name') is-invalid @enderror" 
                   id="name" 
                   name="name" 
                   value="{{ old('name') }}" 
                   required 
                   autofocus 
                   autocomplete="name">
            @error('name')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>



        <div class="mb-4">
            <label for="email" class="form-label fs-13 fw-medium text-light-dark">Email Address</label>
            <input type="email" 
                   class="form-control @error('email') is-invalid @enderror" 
                   id="email" 
                   name="email" 
                   value="{{ old('email') }}" 
                   required 
                   autocomplete="username">
            @error('email')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password" class="form-label fs-13 fw-medium text-light-dark">Password</label>
            <input type="password" 
                   class="form-control @error('password') is-invalid @enderror" 
                   id="password" 
                   name="password" 
                   required 
                   autocomplete="new-password">
            @error('password')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="form-label fs-13 fw-medium text-light-dark">Confirm Password</label>
            <input type="password" 
                   class="form-control @error('password_confirmation') is-invalid @enderror" 
                   id="password_confirmation" 
                   name="password_confirmation" 
                   required 
                   autocomplete="new-password">
            @error('password_confirmation')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
            <div class="mb-4">
                <div class="form-check">
                    <input class="form-check-input @error('terms') is-invalid @enderror" 
                           type="checkbox" 
                           name="terms" 
                           id="terms" 
                           required>
                    <label class="form-check-label fs-13 text-light-dark" for="terms">
                        {!! __('I agree to the :terms_of_service and :privacy_policy', [
                                'terms_of_service' => '<a target="_blank" href="'.route('terms.show').'" class="text-primary link-primary">'.__('Terms of Service').'</a>',
                                'privacy_policy' => '<a target="_blank" href="'.route('policy.show').'" class="text-primary link-primary">'.__('Privacy Policy').'</a>',
                        ]) !!}
                    </label>
                    @error('terms')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>
        @endif

        <div class="mb-6 row">
            <button type="submit" class="btn btn-lg btn-primary fs-11 w-100 text-primary-light">
                Register {{ $isStudent ? 'Student' : 'Staff' }} Account
            </button>
        </div>
        
        <div class="text-center">
            <p class="mb-2 fs-13 fw-medium text-light-dark">
                <span>Already have an account?</span>
                <a class="text-primary link-primary" href="{{ route('login') }}">Sign in</a>
            </p>
            
            @if ($isStudent)
                <p class="mb-0 fs-13 text-light-dark">
                    <span>Are you staff?</span>
                    <a class="text-primary link-primary" href="{{ route('staff.register') }}">Register as Staff</a>
                </p>
            @else
                <p class="mb-0 fs-13 text-light-dark">
                    <span>Are you a student?</span>
                    <a class="text-primary link-primary" href="{{ route('students.register') }}">Register as Student</a>
                </p>
            @endif
        </div>
    </form>
</x-backend.auth>
@else
    @include($themeView, compact('userType', 'isStudent', 'title', 'description', 'actionUrl'))
@endif