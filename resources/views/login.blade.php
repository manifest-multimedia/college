@php
    $authService = app(\App\Services\AuthenticationService::class);
@endphp

<x-backend.auth title="Login" description="Login to your account">
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

    @if ($authService->isAuthCentral())
        {{-- AuthCentral Login --}}
        <form class="px-4 mx-auto mw-sm">
            <div class="mb-6 row">
                <a href="{{ $authService->getAuthCentralLoginUrl() }}"
                    class="btn btn-lg btn-primary fs-11 w-100 text-primary-light">
                    Login with AuthCentral
                </a>
            </div>
            
            @if ($authService->getSignupUrl())
                <p class="mb-0 text-center fs-13 fw-medium text-light-dark">
                    <span>Don't have an account?</span>
                    <a class="text-primary link-primary" href="{{ $authService->getSignupUrl() }}">Sign up</a>
                </p>
            @endif
        </form>
    @else
        {{-- Regular Login --}}
        <form method="POST" action="{{ route('regular.login') }}" class="px-4 mx-auto mw-sm">
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
                <input type="password" 
                       class="form-control @error('password') is-invalid @enderror" 
                       id="password" 
                       name="password" 
                       required 
                       autocomplete="current-password">
                @error('password')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label fs-13 text-light-dark" for="remember">
                        Remember me
                    </label>
                </div>
            </div>

            <div class="mb-6 row">
                <button type="submit" class="btn btn-lg btn-primary fs-11 w-100 text-primary-light">
                    Login
                </button>
            </div>
            
            @if ($authService->getSignupUrl())
                <p class="mb-0 text-center fs-13 fw-medium text-light-dark">
                    <span>Don't have an account?</span>
                    <a class="text-primary link-primary" href="{{ $authService->getSignupUrl() }}">Sign up</a>
                </p>
            @endif
        </form>
    @endif
</x-backend.auth>