<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class AuthenticationService
{
    /**
     * Get the current authentication method from configuration.
     */
    public function getAuthMethod(): string
    {
        return config('authentication.method', 'authcentral');
    }

    /**
     * Check if the current authentication method is AuthCentral.
     */
    public function isAuthCentral(): bool
    {
        return $this->getAuthMethod() === 'authcentral';
    }

    /**
     * Check if the current authentication method is regular.
     */
    public function isRegular(): bool
    {
        return $this->getAuthMethod() === 'regular';
    }

    /**
     * Get AuthCentral configuration.
     */
    public function getAuthCentralConfig(): array
    {
        return config('authentication.authcentral', []);
    }

    /**
     * Get regular authentication configuration.
     */
    public function getRegularConfig(): array
    {
        return config('authentication.regular', []);
    }

    /**
     * Get registration configuration.
     */
    public function getRegistrationConfig(): array
    {
        return config('authentication.registration', []);
    }

    /**
     * Get security configuration.
     */
    public function getSecurityConfig(): array
    {
        return config('authentication.security', []);
    }

    /**
     * Get role mapping configuration.
     */
    public function getRoleConfig(): array
    {
        return config('authentication.roles', []);
    }

    /**
     * Get password synchronization configuration.
     */
    public function getPasswordSyncConfig(): array
    {
        return config('authentication.password_sync', []);
    }

    /**
     * Get the AuthCentral login URL with callback redirect.
     * Available regardless of current AUTH_METHOD setting for user flexibility.
     */
    public function getAuthCentralLoginUrl(): string
    {
        // Use environment variables directly as fallback for cross-mode compatibility
        $loginUrl = config('authentication.authcentral.login_url') ?? env('AUTHCENTRAL_LOGIN_URL', 'https://auth.pnmtc.edu.gh/login');
        $callbackUrl = route('auth.callback');

        return $loginUrl.'?redirect_url='.urlencode($callbackUrl);
    }

    /**
     * Get the AuthCentral API URL.
     */
    public function getAuthCentralApiUrl(): string
    {
        return $this->getAuthCentralConfig()['api_url'] ?? 'https://auth.pnmtc.edu.gh/api/user';
    }

    /**
     * Get the signup URL based on authentication method.
     */
    public function getSignupUrl(): ?string
    {
        if ($this->isAuthCentral()) {
            return $this->getAuthCentralConfig()['signup_url'] ?? 'https://auth.pnmtc.edu.gh/sign-up';
        }

        if ($this->isRegular() && $this->getRegularConfig()['allow_registration']) {
            return route('staff.register');
        }

        return null;
    }

    /**
     * Get the staff signup URL.
     */
    public function getStaffSignupUrl(): ?string
    {
        if ($this->isAuthCentral()) {
            return $this->getAuthCentralConfig()['signup_url'] ?? 'https://auth.pnmtc.edu.gh/sign-up';
        }

        $regularConfig = $this->getRegularConfig();
        if ($this->isRegular() && ($regularConfig['allow_staff_registration'] ?? $regularConfig['allow_registration'] ?? false)) {
            return route('staff.register');
        }

        return null;
    }

    /**
     * Get the student signup URL.
     */
    public function getStudentSignupUrl(): ?string
    {
        if ($this->isAuthCentral()) {
            return $this->getAuthCentralConfig()['student_registration_url'] ?? 'https://auth.pnmtc.edu.gh/student/register';
        }

        // Check if student registration is allowed
        $regularConfig = $this->getRegularConfig();
        if ($regularConfig['allow_student_registration'] ?? true) {
            return route('students.register');
        }

        return null;
    }

    /**
     * Get the student registration URL.
     */
    public function getStudentRegistrationUrl(): ?string
    {
        if ($this->isAuthCentral()) {
            return $this->getAuthCentralConfig()['student_registration_url'] ?? 'https://auth.pnmtc.edu.gh/student/register';
        }

        // For regular auth, student registration is handled internally
        // This could be extended later if needed
        return null;
    }

    /**
     * Create or update user from AuthCentral data.
     *
     * @param  string|null  $password  Plain text password to sync (optional)
     */
    public function createOrUpdateAuthCentralUser(array $userData, array $roles = [], ?string $password = null): User
    {
        // Determine password handling strategy
        if ($password) {
            // Case 1: Password provided directly (e.g., from webhook or extended API)
            $hashedPassword = Hash::make($password);
            Log::info("Password synced from AuthCentral for user: {$userData['email']}");
        } else {
            // Case 2: No password provided (standard OAuth flow)
            $existingUser = User::where('email', $userData['email'])->first();

            if ($existingUser && $existingUser->password) {
                // Preserve existing password if user already exists
                $hashedPassword = $existingUser->password;
                Log::info("Preserving existing password for AuthCentral user: {$userData['email']}");
            } else {
                // New user or user without password - create a secure random password
                // Note: User should use AuthCentral SSO or password reset to set local password
                $hashedPassword = Hash::make(Str::random(32));
                Log::info("Generated secure temporary password for AuthCentral user: {$userData['email']} - user should use SSO or password reset for local access");
            }
        }

        // Find or create the user
        $user = User::updateOrCreate(
            ['email' => $userData['email']],
            [
                'name' => $userData['name'],
                'password' => $hashedPassword,
            ]
        );

        // Sync roles from AuthCentral
        $this->syncUserRoles($user, $roles, 'authcentral');

        Log::info("AuthCentral user created/updated: {$user->email}", [
            'roles' => $roles,
            'user_id' => $user->id,
        ]);

        return $user;
    }

    /**
     * Create or update user for regular authentication.
     */
    public function createRegularUser(array $userData, string $userType = 'staff'): User
    {
        $user = User::create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => Hash::make($userData['password']),
        ]);

        // Assign role based on user type
        $roleConfig = $this->getRoleConfig();
        $role = $userType === 'student'
            ? ($roleConfig['student_default'] ?? 'Student')
            : ($roleConfig['staff_default'] ?? $this->getRegularConfig()['default_role'] ?? 'Staff');
        $this->syncUserRoles($user, [$role], 'regular');

        Log::info("Regular user created: {$user->email}", [
            'user_type' => $userType,
            'assigned_role' => $role,
            'user_id' => $user->id,
        ]);

        return $user;
    }

    /**
     * Create a staff user via regular authentication.
     */
    public function createStaffUser(array $userData): User
    {
        return $this->createRegularUser($userData, 'staff');
    }

    /**
     * Create a student user via regular authentication.
     */
    public function createStudentUser(array $userData): User
    {
        $user = $this->createRegularUser($userData, 'student');

        // Create corresponding student record
        $this->createStudentRecord($user, $userData);

        return $user;
    }

    /**
     * Create a student record linked to a user.
     */
    private function createStudentRecord(User $user, array $userData): ?\App\Models\Student
    {
        try {
            // Check if Student model exists
            if (! class_exists(\App\Models\Student::class)) {
                Log::warning("Student model not found, skipping student record creation for user: {$user->email}");

                return null;
            }

            // Parse the name into first and last name
            $nameParts = explode(' ', trim($userData['name']), 2);
            $firstName = $nameParts[0] ?? '';
            $lastName = $nameParts[1] ?? '';

            $regularConfig = $this->getRegularConfig();
            $student = \App\Models\Student::create([
                'user_id' => $user->id,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $userData['email'],
                'status' => $regularConfig['student_default_status'] ?? 'active',
            ]);

            Log::info("Student record created for user: {$user->email}", [
                'student_id' => $student->id,
                'user_id' => $user->id,
            ]);

            return $student;
        } catch (\Exception $e) {
            Log::error("Failed to create student record for user: {$user->email}", [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return null;
        }
    }

    /**
     * Sync user roles based on authentication method.
     */
    public function syncUserRoles(User $user, array $roles, ?string $authMethod = null): void
    {
        $authMethod = $authMethod ?? $this->getAuthMethod();

        // Remove existing roles
        $user->syncRoles([]);

        // Assign new roles
        $assignedRoles = [];
        foreach ($roles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $user->assignRole($role);
                $assignedRoles[] = $roleName;
                Log::info("User {$user->email} assigned role: {$roleName} (method: {$authMethod})");
            } else {
                Log::warning("Role {$roleName} not found in system for user {$user->email} (method: {$authMethod})");
            }
        }

        // If no roles were assigned, assign a default role
        if (empty($assignedRoles)) {
            $roleConfig = $this->getRoleConfig();
            $defaultRole = $authMethod === 'regular'
                ? ($roleConfig['staff_default'] ?? $this->getRegularConfig()['default_role'] ?? 'Staff')
                : ($roleConfig['authcentral_fallback'] ?? 'Staff');

            $role = Role::where('name', $defaultRole)->first();
            if ($role) {
                $user->assignRole($role);
                Log::warning("No valid roles found for user {$user->email}. Assigned default role: {$defaultRole} (method: {$authMethod})");
            } else {
                Log::error("Default role {$defaultRole} not found in system for user {$user->email} (method: {$authMethod})");
            }
        }
    }

    /**
     * Authenticate user using regular Laravel authentication.
     */
    public function attemptRegularLogin(array $credentials): bool
    {
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            Log::info("Regular authentication successful: {$user->email}");

            return true;
        }

        Log::info('Regular authentication failed for email: '.($credentials['email'] ?? 'unknown'));

        return false;
    }

    /**
     * Get available authentication methods.
     */
    public function getAvailableMethods(): array
    {
        return config('authentication.methods', [
            'authcentral' => 'authcentral',
            'regular' => 'regular',
        ]);
    }

    /**
     * Validate authentication method.
     */
    public function isValidMethod(string $method): bool
    {
        return in_array($method, array_values($this->getAvailableMethods()));
    }

    /**
     * Sync password for AuthCentral user to enable local authentication.
     *
     * @param  string  $password  Plain text password
     */
    public function syncAuthCentralUserPassword(string $email, string $password): bool
    {
        $user = User::where('email', $email)->first();

        if (! $user) {
            Log::warning("Attempted to sync password for non-existent user: {$email}");

            return false;
        }

        $user->password = Hash::make($password);
        $success = $user->save();

        if ($success) {
            Log::info("Password synced successfully for AuthCentral user: {$email}");
        } else {
            Log::error("Failed to sync password for AuthCentral user: {$email}");
        }

        return $success;
    }

    /**
     * Check if a user likely came from AuthCentral (has random password pattern).
     */
    public function isLikelyAuthCentralUser(User $user): bool
    {
        // This is a heuristic - AuthCentral users typically have:
        // 1. Random password that user wouldn't know
        // 2. May lack certain profile fields set during regular registration

        // For now, we'll check if user has roles that suggest AuthCentral origin
        // This could be enhanced with additional metadata
        $authCentralRoles = $this->getRoleConfig()['authcentral_roles'] ?? ['Tutor', 'Super Admin', 'Admin'];

        return $user->hasAnyRole($authCentralRoles);
    }
}
