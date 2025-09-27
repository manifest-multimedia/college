<x-backend.auth title="Two-Factor Authentication" description="Enter your authentication code">
    @if ($errors->any())
        <div class="alert alert-danger mb-4" role="alert">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div x-data="{ recovery: false }" class="px-4 mx-auto mw-sm">
        <div class="mb-4 text-center text-light-dark fs-13" x-show="! recovery">
            {{ __('Please confirm access to your account by entering the authentication code provided by your authenticator application.') }}
        </div>

        <div class="mb-4 text-center text-light-dark fs-13" x-cloak x-show="recovery">
            {{ __('Please confirm access to your account by entering one of your emergency recovery codes.') }}
        </div>

        <form method="POST" action="{{ route('two-factor.login') }}">
            @csrf

            <div class="mb-4" x-show="! recovery">
                <label for="code" class="form-label fs-13 fw-medium text-light-dark">Authentication Code</label>
                <input type="text" 
                       class="form-control @error('code') is-invalid @enderror" 
                       id="code" 
                       name="code" 
                       inputmode="numeric" 
                       autofocus 
                       x-ref="code"
                       autocomplete="one-time-code">
                @error('code')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="mb-4" x-cloak x-show="recovery">
                <label for="recovery_code" class="form-label fs-13 fw-medium text-light-dark">Recovery Code</label>
                <input type="text" 
                       class="form-control @error('recovery_code') is-invalid @enderror" 
                       id="recovery_code" 
                       name="recovery_code" 
                       x-ref="recovery_code"
                       autocomplete="one-time-code">
                @error('recovery_code')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="mb-4 text-center">
                <button type="button" 
                        class="btn btn-link text-primary link-primary fs-13 p-0"
                        x-show="! recovery"
                        x-on:click="recovery = true; $nextTick(() => { $refs.recovery_code.focus() })">
                    {{ __('Use a recovery code') }}
                </button>

                <button type="button" 
                        class="btn btn-link text-primary link-primary fs-13 p-0"
                        x-cloak
                        x-show="recovery"
                        x-on:click="recovery = false; $nextTick(() => { $refs.code.focus() })">
                    {{ __('Use an authentication code') }}
                </button>
            </div>

            <div class="mb-6 row">
                <button type="submit" class="btn btn-lg btn-primary fs-11 w-100 text-primary-light">
                    Log in
                </button>
            </div>
        </form>
    </div>
</x-backend.auth>