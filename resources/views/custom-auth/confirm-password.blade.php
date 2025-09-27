<x-backend.auth title="Confirm Password" description="This is a secure area. Please confirm your password.">
    @if ($errors->any())
        <div class="alert alert-danger mb-4" role="alert">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="mb-4 text-center text-light-dark fs-13">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="px-4 mx-auto mw-sm">
        @csrf

        <div class="mb-4">
            <label for="password" class="form-label fs-13 fw-medium text-light-dark">Password</label>
            <input type="password" 
                   class="form-control @error('password') is-invalid @enderror" 
                   id="password" 
                   name="password" 
                   required 
                   autofocus 
                   autocomplete="current-password">
            @error('password')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="mb-6 row">
            <button type="submit" class="btn btn-lg btn-primary fs-11 w-100 text-primary-light">
                Confirm Password
            </button>
        </div>
    </form>
</x-backend.auth>