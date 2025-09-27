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
     *
     * @return string
     */
    public function getAuthMethod(): string
    {
        return config('authentication.method', 'authcentral');
    }

    /**
     * Check if the current authentication method is AuthCentral.
     *
     * @return bool
     */
    public function isAuthCentral(): bool
    {
        return $this->getAuthMethod() === 'authcentral';
    }

    /**
     * Check if the current authentication method is regular.
     *
     * @return bool
     */
    public function isRegular(): bool
    {
        return $this->getAuthMethod() === 'regular';
    }

    /**
     * Get AuthCentral configuration.
     *
     * @return array
     */
    public function getAuthCentralConfig(): array
    {
        return config('authentication.authcentral', []);
    }

    /**
     * Get regular authentication configuration.
     *
     * @return array
     */
    public function getRegularConfig(): array
    {
        return config('authentication.regular', []);
    }

    /**
     * Get the AuthCentral login URL with callback redirect.
     *
     * @return string
     */
    public function getAuthCentralLoginUrl(): string
    {
        $loginUrl = $this->getAuthCentralConfig()['login_url'] ?? 'https://auth.pnmtc.edu.gh/login';
        $callbackUrl = route('auth.callback');
        
        return $loginUrl . '?redirect_url=' . urlencode($callbackUrl);
    }

    /**
     * Get the AuthCentral API URL.
     *
     * @return string
     */
    public function getAuthCentralApiUrl(): string
    {
        return $this->getAuthCentralConfig()['api_url'] ?? 'https://auth.pnmtc.edu.gh/api/user';
    }

    /**
     * Get the signup URL based on authentication method.
     *
     * @return string|null
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
     *
     * @return string|null
     */
    public function getStaffSignupUrl(): ?string
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
     * Get the student signup URL.
     *
     * @return string|null
     */
    public function getStudentSignupUrl(): ?string
    {
        if ($this->isAuthCentral()) {
            return $this->getAuthCentralConfig()['student_registration_url'] ?? 'https://auth.pnmtc.edu.gh/student/register';
        }

        // Students can always register via regular auth
        return route('students.register');
    }

    /**
     * Get the student registration URL.
     *
     * @return string|null
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
     * @param array $userData
     * @param array $roles
     * @return User
     */
    public function createOrUpdateAuthCentralUser(array $userData, array $roles = []): User
    {
        // Set a random password for AuthCentral users
        $password = Hash::make(Str::random(10));

        // Find or create the user
        $user = User::updateOrCreate(
            ['email' => $userData['email']],
            [
                'name' => $userData['name'],
                'password' => $password,
            ]
        );

        // Sync roles from AuthCentral
        $this->syncUserRoles($user, $roles, 'authcentral');

        Log::info("AuthCentral user created/updated: {$user->email}", [
            'roles' => $roles,
            'user_id' => $user->id
        ]);

        return $user;
    }

    /**
     * Create or update user for regular authentication.
     *
     * @param array $userData
     * @param string $userType
     * @return User
     */
    public function createRegularUser(array $userData, string $userType = 'staff'): User
    {
        $user = User::create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => Hash::make($userData['password']),
        ]);

        // Assign role based on user type
        $role = $userType === 'student' ? 'Student' : ($this->getRegularConfig()['default_role'] ?? 'Staff');
        $this->syncUserRoles($user, [$role], 'regular');

        Log::info("Regular user created: {$user->email}", [
            'user_type' => $userType,
            'assigned_role' => $role,
            'user_id' => $user->id
        ]);

        return $user;
    }
    
    /**
     * Create a staff user via regular authentication.
     *
     * @param array $userData
     * @return User
     */
    public function createStaffUser(array $userData): User
    {
        return $this->createRegularUser($userData, 'staff');
    }
    
    /**
     * Create a student user via regular authentication.
     *
     * @param array $userData
     * @return User
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
     *
     * @param User $user
     * @param array $userData
     * @return \App\Models\Student|null
     */
    private function createStudentRecord(User $user, array $userData): ?\App\Models\Student
    {
        try {
            // Check if Student model exists
            if (!class_exists(\App\Models\Student::class)) {
                Log::warning("Student model not found, skipping student record creation for user: {$user->email}");
                return null;
            }

            // Parse the name into first and last name
            $nameParts = explode(' ', trim($userData['name']), 2);
            $firstName = $nameParts[0] ?? '';
            $lastName = $nameParts[1] ?? '';

            $student = \App\Models\Student::create([
                'user_id' => $user->id,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $userData['email'],
                'status' => 'active',
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
     *
     * @param User $user
     * @param array $roles
     * @param string $authMethod
     * @return void
     */
    public function syncUserRoles(User $user, array $roles, string $authMethod = null): void
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
            $defaultRole = $authMethod === 'regular' 
                ? $this->getRegularConfig()['default_role'] ?? 'Staff'
                : 'Staff';
                
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
     *
     * @param array $credentials
     * @return bool
     */
    public function attemptRegularLogin(array $credentials): bool
    {
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            Log::info("Regular authentication successful: {$user->email}");
            return true;
        }

        Log::info("Regular authentication failed for email: " . ($credentials['email'] ?? 'unknown'));
        return false;
    }

    /**
     * Get available authentication methods.
     *
     * @return array
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
     *
     * @param string $method
     * @return bool
     */
    public function isValidMethod(string $method): bool
    {
        return in_array($method, array_values($this->getAvailableMethods()));
    }
}