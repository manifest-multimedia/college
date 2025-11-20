<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuthenticationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    protected AuthenticationService $authService;

    public function __construct(AuthenticationService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Show the password reset request form.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showLinkRequestForm()
    {
        if (! $this->authService->isRegular()) {
            return redirect()->route('login')
                ->withErrors(['password_reset' => 'Password reset is only available for regular authentication.']);
        }

        return view('auth.forgot-password');
    }

    /**
     * Send a password reset link to the user.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        if (! $this->authService->isRegular()) {
            return redirect()->route('login')
                ->withErrors(['password_reset' => 'Password reset is only available for regular authentication.']);
        }

        $request->validate(['email' => 'required|email']);

        // Check if user exists and is an AuthCentral user
        $user = User::where('email', $request->email)->first();
        if ($user && $user->isAuthCentralUser()) {
            Log::info('AuthCentral user attempted password reset', [
                'email' => $request->email,
                'ip' => $request->ip(),
            ]);

            throw ValidationException::withMessages([
                'email' => ['This account uses AuthCentral authentication. Password reset is not available for AuthCentral accounts.'],
            ]);
        }

        // Attempt to send the password reset link
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            Log::info('Password reset link sent', [
                'email' => $request->email,
                'ip' => $request->ip(),
            ]);

            return back()->with(['status' => __($status)]);
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }

    /**
     * Show the password reset form.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showResetForm(Request $request)
    {
        if (! $this->authService->isRegular()) {
            return redirect()->route('login')
                ->withErrors(['password_reset' => 'Password reset is only available for regular authentication.']);
        }

        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Reset the user's password.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reset(Request $request)
    {
        if (! $this->authService->isRegular()) {
            return redirect()->route('login')
                ->withErrors(['password_reset' => 'Password reset is only available for regular authentication.']);
        }

        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        // Check if user exists and is an AuthCentral user
        $user = User::where('email', $request->email)->first();
        if ($user && $user->isAuthCentralUser()) {
            Log::warning('AuthCentral user attempted password reset completion', [
                'email' => $request->email,
                'ip' => $request->ip(),
            ]);

            throw ValidationException::withMessages([
                'email' => ['This account uses AuthCentral authentication. Password reset is not available for AuthCentral accounts.'],
            ]);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->password = bcrypt($password);
                $user->save();

                Log::info('Password reset completed', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', __($status));
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }
}
