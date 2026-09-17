<x-backend.auth title="Change Password" description="Set your personal password to continue">
    <div class="px-3 px-sm-4 mx-auto mw-sm">
        
        <div class="alert alert-warning d-flex align-items-center mb-4 p-3 rounded-3 shadow-sm border border-warning border-opacity-50" role="alert">
            <i class="bi bi-shield-lock-fill fs-3 text-warning me-3 flex-shrink-0"></i>
            <div class="fs-13">
                <span class="fw-bold d-block text-gray-900 mb-1">Temporary Credentials Detected</span>
                Your account password was recently reset by the System Administrator. For your security, you must set a new personal password before accessing the system.
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger mb-4 p-3 rounded-3" role="alert">
                <div class="fw-bold mb-1 fs-13"><i class="bi bi-exclamation-triangle-fill me-1"></i> Please correct the following:</div>
                <ul class="mb-0 ps-3 fs-13">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.force-change.update') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label fs-13 fw-semibold text-gray-700">Account</label>
                <input type="text" class="form-control bg-light" value="{{ auth()->user()->email }}" disabled readonly>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label fs-13 fw-semibold text-gray-700">New Password <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           id="password" 
                           name="password" 
                           required 
                           autocomplete="new-password"
                           placeholder="At least 8 characters">
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('password', this)">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                @error('password')
                    <div class="text-danger fs-12 mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="form-label fs-13 fw-semibold text-gray-700">Confirm New Password <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="password" 
                           class="form-control" 
                           id="password_confirmation" 
                           name="password_confirmation" 
                           required 
                           autocomplete="new-password"
                           placeholder="Re-enter your new password">
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('password_confirmation', this)">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-primary py-2 fw-semibold shadow-sm">
                    <i class="bi bi-check2-circle me-1"></i> Save New Password & Proceed
                </button>
            </div>
        </form>

        <div class="text-center pt-2">
            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-link text-muted fs-13 text-decoration-none p-0">
                    <i class="bi bi-box-arrow-right me-1"></i> Sign Out Instead
                </button>
            </form>
        </div>
    </div>

    <script>
        function togglePasswordVisibility(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }
    </script>
</x-backend.auth>
