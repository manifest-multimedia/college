<?php

namespace App\Services;

use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthCentralSyncBridgeService
{
    protected ?bool $isAvailable = null;

    /**
     * Check if AuthCentral database connection is configured and reachable.
     */
    public function isAvailable(): bool
    {
        if ($this->isAvailable !== null) {
            return $this->isAvailable;
        }

        $authMethod = config('authentication.method', 'authcentral');
        if ($authMethod !== 'authcentral') {
            return $this->isAvailable = false;
        }

        $databaseName = config('database.connections.authcentral.database');
        if (blank($databaseName)) {
            return $this->isAvailable = false;
        }

        try {
            DB::connection('authcentral')->getPdo();
            return $this->isAvailable = true;
        } catch (\Throwable $e) {
            Log::warning('AuthCentral database connection is not available: ' . $e->getMessage());
            return $this->isAvailable = false;
        }
    }

    /**
     * Synchronize a user's password and profile to AuthCentral.
     *
     * @param string $email
     * @param string $plainPassword
     * @param array $attributes Optional metadata: name, student_id, phone, gender, date_of_birth
     * @param string|null $roleName Role to assign in AuthCentral (e.g. 'Student', 'Staff')
     * @return array Result array with success, action (created|updated), and details
     */
    public function syncUserCredentials(
        string $email,
        string $plainPassword,
        array $attributes = [],
        ?string $roleName = null
    ): array {
        if (! $this->isAvailable()) {
            return [
                'success' => false,
                'action' => 'skipped',
                'message' => 'AuthCentral database connection is not configured or unavailable.',
            ];
        }

        $email = strtolower(trim($email));
        if (blank($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'action' => 'failed',
                'message' => 'Invalid email address provided for AuthCentral sync.',
            ];
        }

        try {
            $authCentralUser = DB::connection('authcentral')
                ->table('users')
                ->where('email', $email)
                ->first();

            $hashedPassword = Hash::make($plainPassword);

            if ($authCentralUser) {
                // 1. Update existing AuthCentral user
                $updateData = [
                    'password' => $hashedPassword,
                    'status' => 'active',
                    'updated_at' => now(),
                ];

                if (! empty($attributes['name']) && empty($authCentralUser->name)) {
                    $updateData['name'] = $attributes['name'];
                }
                if (! empty($attributes['student_id']) && empty($authCentralUser->student_id)) {
                    $updateData['student_id'] = $attributes['student_id'];
                }
                if (! empty($attributes['phone']) && empty($authCentralUser->phone)) {
                    $updateData['phone'] = $attributes['phone'];
                }

                DB::connection('authcentral')
                    ->table('users')
                    ->where('id', $authCentralUser->id)
                    ->update($updateData);

                // Ensure role assignment exists
                if ($roleName) {
                    $this->ensureRoleAssigned($authCentralUser->id, $roleName);
                }

                Log::info("Synchronized existing user password to AuthCentral: {$email}", [
                    'authcentral_user_id' => $authCentralUser->id,
                    'role' => $roleName,
                ]);

                return [
                    'success' => true,
                    'action' => 'updated',
                    'authcentral_user_id' => $authCentralUser->id,
                    'email' => $email,
                ];
            }

            // 2. Create new user in AuthCentral
            $name = $attributes['name'] ?? explode('@', $email)[0];
            $studentId = $attributes['student_id'] ?? null;
            $phone = $attributes['phone'] ?? null;
            $gender = ! empty($attributes['gender']) && in_array(strtolower($attributes['gender']), ['male', 'female', 'other'])
                ? strtolower($attributes['gender'])
                : null;
            $dob = $attributes['date_of_birth'] ?? null;

            $newUserId = DB::connection('authcentral')->table('users')->insertGetId([
                'name' => $name,
                'email' => $email,
                'student_id' => $studentId,
                'phone' => $phone,
                'gender' => $gender,
                'date_of_birth' => $dob,
                'status' => 'active',
                'password' => $hashedPassword,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Assign role
            $assignedRole = $roleName ?? 'Student';
            $this->ensureRoleAssigned($newUserId, $assignedRole);

            Log::info("Provisioned new user in AuthCentral: {$email}", [
                'authcentral_user_id' => $newUserId,
                'role' => $assignedRole,
                'student_id' => $studentId,
            ]);

            return [
                'success' => true,
                'action' => 'created',
                'authcentral_user_id' => $newUserId,
                'email' => $email,
            ];
        } catch (\Throwable $e) {
            Log::error("Failed to sync user to AuthCentral: {$email}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'action' => 'error',
                'message' => $e->getMessage(),
                'email' => $email,
            ];
        }
    }

    /**
     * Synchronize a student model and password to AuthCentral.
     */
    public function syncStudent(Student $student, string $plainPassword): array
    {
        $user = $student->user_id ? User::find($student->user_id) : null;
        $email = $student->email ?? $user?->email;

        if (blank($email)) {
            return [
                'success' => false,
                'action' => 'skipped',
                'message' => 'Student has no email address.',
            ];
        }

        $attributes = [
            'name' => $student->full_name ?? $user?->name ?? "{$student->first_name} {$student->last_name}",
            'student_id' => $student->student_id,
            'phone' => $student->phone ?? $user?->phone,
            'gender' => $student->gender,
            'date_of_birth' => $student->date_of_birth,
        ];

        return $this->syncUserCredentials($email, $plainPassword, $attributes, 'Student');
    }

    /**
     * Synchronize a College User model and password to AuthCentral.
     */
    public function syncCollegeUser(User $user, string $plainPassword, ?string $roleName = null): array
    {
        $attributes = [
            'name' => $user->name,
            'phone' => $user->phone,
        ];

        // If user is a student, attach student ID if found
        if ($user->student) {
            $attributes['student_id'] = $user->student->student_id;
            $attributes['phone'] = $attributes['phone'] ?? $user->student->phone;
            $attributes['gender'] = $user->student->gender;
            $roleName = $roleName ?? 'Student';
        }

        $effectiveRole = $roleName ?? ($user->roles->first()?->name ?? 'Staff');

        return $this->syncUserCredentials($user->email, $plainPassword, $attributes, $effectiveRole);
    }

    /**
     * Ensure a role is assigned in AuthCentral's model_has_roles table.
     */
    protected function ensureRoleAssigned(int $authCentralUserId, string $roleName): void
    {
        try {
            $role = DB::connection('authcentral')
                ->table('roles')
                ->where('name', $roleName)
                ->first();

            if (! $role) {
                // Fallback to Student or Staff if specific role is missing
                $role = DB::connection('authcentral')
                    ->table('roles')
                    ->whereIn('name', ['Student', 'Staff'])
                    ->first();
            }

            if ($role) {
                DB::connection('authcentral')
                    ->table('model_has_roles')
                    ->updateOrInsert(
                        [
                            'role_id' => $role->id,
                            'model_type' => 'App\Models\User',
                            'model_id' => $authCentralUserId,
                        ],
                        []
                    );
            }
        } catch (\Throwable $e) {
            Log::warning("Failed to assign role {$roleName} to AuthCentral user {$authCentralUserId}: " . $e->getMessage());
        }
    }

    /**
     * Bulk sync students who exist in College but are missing in AuthCentral.
     * Generates a temporary secure password or uses their student ID as initial credentials.
     */
    public function syncMissingStudents(
        ?int $limit = null,
        bool $dryRun = false,
        ?callable $progressCallback = null
    ): array {
        if (! $this->isAvailable()) {
            return [
                'success' => false,
                'message' => 'AuthCentral database connection is not available.',
                'total_checked' => 0,
                'missing_count' => 0,
                'synced_count' => 0,
            ];
        }

        $authCentralEmails = DB::connection('authcentral')
            ->table('users')
            ->pluck('email')
            ->map(fn ($e) => strtolower(trim($e)))
            ->flip()
            ->all();

        $query = Student::whereNotNull('email')
            ->where('email', '!=', '')
            ->orderBy('id');

        if ($limit) {
            $query->limit($limit);
        }

        $students = $query->get();
        $totalChecked = $students->count();
        $missingCount = 0;
        $syncedCount = 0;
        $failedCount = 0;

        foreach ($students as $student) {
            $email = strtolower(trim($student->email));
            if (! isset($authCentralEmails[$email])) {
                $missingCount++;

                if (! $dryRun) {
                    // Generate initial temporary password: Pass#<student_last_4_id_or_random>
                    $cleanId = preg_replace('/[^0-9]/', '', (string) $student->student_id);
                    $pin = strlen($cleanId) >= 4 ? substr($cleanId, -4) : (string) rand(1000, 9999);
                    $initialPassword = "Pass#{$pin}";

                    $res = $this->syncStudent($student, $initialPassword);
                    if ($res['success']) {
                        $syncedCount++;
                        // Also make sure College user has force_password_change enabled
                        if ($student->user_id) {
                            User::where('id', $student->user_id)->update(['force_password_change' => true]);
                        }
                    } else {
                        $failedCount++;
                    }
                }
            }

            if ($progressCallback) {
                $progressCallback($student, $missingCount, $syncedCount);
            }
        }

        return [
            'success' => true,
            'dry_run' => $dryRun,
            'total_checked' => $totalChecked,
            'missing_count' => $missingCount,
            'synced_count' => $syncedCount,
            'failed_count' => $failedCount,
        ];
    }
}
