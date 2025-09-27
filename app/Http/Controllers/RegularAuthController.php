<?php

namespace App\Http\Controllers;

use App\Services\AuthenticationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class RegularAuthController extends Controller
{
    protected AuthenticationService $authService;

    public function __construct(AuthenticationService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Handle a regular login request.
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function login(Request $request): RedirectResponse
    {
        // Ensure regular authentication is enabled
        if (!$this->authService->isRegular()) {
            Log::warning('Regular authentication attempt when method is not regular', [
                'current_method' => $this->authService->getAuthMethod(),
                'ip' => $request->ip(),
            ]);
            
            return redirect()->route('login')
                ->withErrors(['login' => 'Regular authentication is not enabled.']);
        }

        // Validate the login request
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Prepare credentials
        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        // Add remember me if requested
        $remember = $request->boolean('remember');

        // Attempt authentication
        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            Log::info('Regular authentication successful', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
            ]);

            // Ensure user has proper roles
            if ($user->roles->isEmpty()) {
                $defaultRole = $this->authService->getRegularConfig()['default_role'] ?? 'Staff';
                $this->authService->syncUserRoles($user, [$defaultRole], 'regular');
            }

            // Redirect to intended destination or dashboard
            return redirect()->intended(route('dashboard'));
        }

        // Authentication failed
        Log::info('Regular authentication failed', [
            'email' => $request->email,
            'ip' => $request->ip(),
        ]);

        throw ValidationException::withMessages([
            'email' => ['The provided credentials do not match our records.'],
        ]);
    }

    /**
     * Handle a logout request.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        if ($user) {
            Log::info('User logged out', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Show the registration form (only if registration is enabled).
     *
     * @return \Illuminate\View\View|RedirectResponse
     */
    public function showRegistrationForm()
    {
        if (!$this->authService->isRegular()) {
            return redirect()->route('login')
                ->withErrors(['registration' => 'Registration is not available with the current authentication method.']);
        }

        $config = $this->authService->getRegularConfig();
        if (!($config['allow_registration'] ?? false)) {
            return redirect()->route('login')
                ->withErrors(['registration' => 'Registration is currently disabled.']);
        }

        return view('auth.register');
    }

    /**
     * Handle a registration request.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function register(Request $request): RedirectResponse
    {
        if (!$this->authService->isRegular()) {
            return redirect()->route('login')
                ->withErrors(['registration' => 'Registration is not available with the current authentication method.']);
        }

        $config = $this->authService->getRegularConfig();
        if (!($config['allow_registration'] ?? false)) {
            return redirect()->route('login')
                ->withErrors(['registration' => 'Registration is currently disabled.']);
        }

        // Validate the registration request
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Create the user
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ];

        $user = $this->authService->createRegularUser($userData);

        // Log the user in
        Auth::login($user);

        Log::info('User registered successfully', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip(),
        ]);

        return redirect()->route('dashboard');
    }
}