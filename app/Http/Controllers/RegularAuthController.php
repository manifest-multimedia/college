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
        // Email/password login is now available regardless of AUTH_METHOD setting
        // This provides flexibility for users to choose their preferred login method

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

        // Log login attempt for all users (security auditing)
        Log::info('Regular authentication attempt', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'method' => 'email_password',
        ]);

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

        // Authentication failed - provide helpful messaging
        Log::info('Regular authentication failed', [
            'email' => $request->email,
            'ip' => $request->ip(),
        ]);

        // Check if user exists to provide better error messaging
        $user = \App\Models\User::where('email', $request->email)->first();
        
        // Generic authentication failure message for security
        // Don't reveal whether user exists or password is wrong
        throw ValidationException::withMessages([
            'email' => [
                'Authentication failed. Please check your credentials and try again. ' .
                'If your account was created with AuthCentral, you may also use the "Sign In with AuthCentral" option above.'
            ],
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
     * Show the staff registration form.
     *
     * @return \Illuminate\View\View|RedirectResponse
     */
    public function showStaffRegistrationForm()
    {
        if (!$this->authService->isRegular()) {
            return redirect()->route('login')
                ->withErrors(['registration' => 'Staff registration is not available with the current authentication method.']);
        }

        $config = $this->authService->getRegularConfig();
        if (!($config['allow_registration'] ?? false)) {
            return redirect()->route('login')
                ->withErrors(['registration' => 'Staff registration is currently disabled.']);
        }

        return view('custom-auth.register', ['userType' => 'staff']);
    }

    /**
     * Show the student registration form.
     *
     * @return \Illuminate\View\View|RedirectResponse
     */
    public function showStudentRegistrationForm()
    {
        // Student registration is available for both auth methods
        return view('custom-auth.register', ['userType' => 'student']);
    }

    /**
     * Handle a staff registration request.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function registerStaff(Request $request): RedirectResponse
    {
        if (!$this->authService->isRegular()) {
            return redirect()->route('login')
                ->withErrors(['registration' => 'Staff registration is not available with the current authentication method.']);
        }

        $config = $this->authService->getRegularConfig();
        if (!($config['allow_registration'] ?? false)) {
            return redirect()->route('login')
                ->withErrors(['registration' => 'Staff registration is currently disabled.']);
        }

        // Validate the registration request
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Create the staff user
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ];

        $user = $this->authService->createStaffUser($userData);

        // Log the user in
        Auth::login($user);

        Log::info('Staff user registered successfully', [
            'user_id' => $user->id,
            'email' => $user->email,
            'user_type' => 'staff',
            'ip' => $request->ip(),
        ]);

        return redirect()->route('dashboard');
    }

    /**
     * Handle a student registration request.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function registerStudent(Request $request): RedirectResponse
    {
        // Validation for students
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Create the student user
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ];

        $user = $this->authService->createStudentUser($userData);

        // Log the user in
        Auth::login($user);

        Log::info('Student user registered successfully', [
            'user_id' => $user->id,
            'email' => $user->email,
            'user_type' => 'student',
            'ip' => $request->ip(),
        ]);

        // Redirect students to their dashboard
        return redirect()->route('student.dashboard');
    }

    /**
     * Show the registration form (legacy - redirects to staff registration).
     *
     * @return RedirectResponse
     */
    public function showRegistrationForm()
    {
        return redirect()->route('staff.register');
    }

    /**
     * Handle a registration request (legacy - redirects to staff registration).
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function register(Request $request): RedirectResponse
    {
        return $this->registerStaff($request);
    }
}