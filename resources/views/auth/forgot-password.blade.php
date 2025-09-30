@php
    $theme = config('branding.auth_theme', 'default');
    $themeView = "custom-auth.themes.{$theme}.forgot-password";
    
    // Check if theme-specific view exists, fallback to component
    if (!view()->exists($themeView)) {
        $useComponent = true;
    } else {
        $useComponent = false;
    }
@endphp

@if($useComponent)
<x-backend.auth title="Reset Password" description="Enter your email to receive a password reset link">
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

    <div class="mb-4 text-center text-light-dark fs-13">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </div>

    <form method="POST" action="{{ route('password.email') }}" class="px-4 mx-auto mw-sm">
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

        <div class="mb-6 row">
            <button type="submit" class="btn btn-lg btn-primary fs-11 w-100 text-primary-light">
                Email Password Reset Link
            </button>
        </div>
        
        <p class="mb-0 text-center fs-13 fw-medium text-light-dark">
            <span>Remember your password?</span>
            <a class="text-primary link-primary" href="{{ route('login') }}">Back to login</a>
        </p>
    </form>
</x-backend.auth>
@else
    @include($themeView)
@endif
