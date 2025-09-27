<x-backend.auth title="Verify Email" description="Please verify your email address">
    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success mb-4" role="alert">
            {{ __('A new verification link has been sent to the email address you provided in your profile settings.') }}
        </div>
    @endif

    <div class="mb-4 text-center text-light-dark fs-13">
        {{ __('Before continuing, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </div>

    <div class="px-4 mx-auto mw-sm">
        <form method="POST" action="{{ route('verification.send') }}" class="mb-4">
            @csrf
            <button type="submit" class="btn btn-lg btn-primary fs-11 w-100 text-primary-light">
                Resend Verification Email
            </button>
        </form>

        <div class="text-center">
            <a href="{{ route('profile.show') }}" class="text-primary link-primary fs-13 me-3">
                Edit Profile
            </a>
            
            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-link text-primary link-primary fs-13 p-0">
                    Log Out
                </button>
            </form>
        </div>
    </div>
</x-backend.auth>