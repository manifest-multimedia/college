<?php

namespace App\Services;

use App\Mail\AccountCredentialsMailable;
use App\Models\Cohort;
use App\Models\Student;
use App\Models\User;
use App\Services\Communication\SMS\SmsServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class PasswordResetManagementService
{
    protected ?SmsServiceInterface $smsService;

    public function __construct(?SmsServiceInterface $smsService = null)
    {
        $this->smsService = $smsService ?? (app()->bound(SmsServiceInterface::class) ? app(SmsServiceInterface::class) : null);
    }

    /**
     * Generate a temporary password.
     */
    public function generateTemporaryPassword(?string $customPassword = null): string
    {
        if (! empty($customPassword)) {
            return trim($customPassword);
        }

        // Generate readable, secure temporary password (e.g. Pass#9284)
        $letters = ucfirst(Str::lower(Str::random(4)));
        $numbers = rand(1000, 9999);
        return "{$letters}#{$numbers}";
    }

    /**
     * Reset password for an individual student.
     */
    public function resetStudent(Student $student, array $options = []): array
    {
        $plainPassword = $this->generateTemporaryPassword($options['custom_password'] ?? null);
        $forceChange = $options['require_change'] ?? true;
        $channel = $options['channel'] ?? 'both'; // 'both', 'email', 'sms', 'none'

        // 1. Ensure user account exists
        $user = null;
        if ($student->user_id) {
            $user = User::find($student->user_id);
        }

        if (! $user && ! empty($student->email)) {
            $user = User::where('email', $student->email)->first();
        }

        if (! $user) {
            // Provision user account for student using existing createUser business logic
            $createdUser = $student->createUser();
            $user = $createdUser ?? User::where('email', $student->email)->first();
        }

        if (! $user) {
            return [
                'success' => false,
                'message' => "Student {$student->name} does not have a valid email to provision an account.",
                'student_id' => $student->id,
            ];
        }

        // Link student user_id if missing
        if ($student->user_id !== $user->id) {
            $student->user_id = $user->id;
            $student->save();
        }

        // Ensure user has Student role
        if (! $user->hasRole('Student')) {
            $studentRole = Role::where('name', 'Student')->first();
            if ($studentRole) {
                $user->assignRole($studentRole);
            }
        }

        // 2. Update user password and force change flag
        $user->password = Hash::make($plainPassword);
        $user->force_password_change = $forceChange;
        $user->save();

        Log::info('System User reset password for student', [
            'student_id' => $student->student_id,
            'user_id' => $user->id,
            'email' => $user->email,
            'reset_by' => auth()->id(),
            'force_change' => $forceChange,
        ]);

        // 3. Dispatch credentials
        $delivery = $this->dispatchCredentials(
            recipientName: $student->full_name ?: $student->name,
            email: $user->email,
            phone: $student->mobile_number,
            plainPassword: $plainPassword,
            channel: $channel,
            forceChange: $forceChange,
            userId: $user->id,
            studentIdCode: $student->student_id
        );

        return [
            'success' => true,
            'student_id' => $student->id,
            'student_id_code' => $student->student_id,
            'user_id' => $user->id,
            'email' => $user->email,
            'phone' => $student->mobile_number,
            'temporary_password' => $plainPassword,
            'delivery' => $delivery,
        ];
    }

    /**
     * Reset password for bulk students by ID.
     */
    public function resetStudentsBulk(array $studentIds, array $options = []): array
    {
        $students = Student::whereIn('id', $studentIds)->get();
        return $this->processStudentBatch($students, $options);
    }

    /**
     * Reset passwords for an entire cohort.
     */
    public function resetCohort(int $cohortId, array $options = []): array
    {
        $students = Student::where('cohort_id', $cohortId)->active()->get();
        return $this->processStudentBatch($students, $options);
    }

    /**
     * Reset passwords for all active students.
     */
    public function resetAllStudents(array $options = []): array
    {
        $students = Student::active()->get();
        return $this->processStudentBatch($students, $options);
    }

    /**
     * Process a collection of students for password reset.
     */
    public function processStudentBatch(Collection $students, array $options = []): array
    {
        $total = $students->count();
        $processed = 0;
        $emailsSent = 0;
        $smsSent = 0;
        $failed = 0;
        $results = [];

        foreach ($students as $student) {
            try {
                $res = $this->resetStudent($student, $options);
                if ($res['success']) {
                    $processed++;
                    if (! empty($res['delivery']['email_sent'])) {
                        $emailsSent++;
                    }
                    if (! empty($res['delivery']['sms_sent'])) {
                        $smsSent++;
                    }
                    $results[] = [
                        'student_id' => $student->student_id,
                        'name' => $student->name,
                        'email' => $res['email'],
                        'password' => $res['temporary_password'],
                        'delivery' => $res['delivery'],
                    ];
                } else {
                    $failed++;
                }
            } catch (\Throwable $e) {
                $failed++;
                Log::error('Error resetting student password in batch', [
                    'student_id' => $student->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [
            'total' => $total,
            'processed' => $processed,
            'emails_sent' => $emailsSent,
            'sms_sent' => $smsSent,
            'failed' => $failed,
            'details' => $results,
        ];
    }

    /**
     * Reset password for an individual staff member (User).
     */
    public function resetStaff(User $user, array $options = []): array
    {
        $plainPassword = $this->generateTemporaryPassword($options['custom_password'] ?? null);
        $forceChange = $options['require_change'] ?? true;
        $channel = $options['channel'] ?? 'both';

        $user->password = Hash::make($plainPassword);
        $user->force_password_change = $forceChange;
        $user->save();

        Log::info('System User reset password for staff member', [
            'user_id' => $user->id,
            'email' => $user->email,
            'reset_by' => auth()->id(),
            'force_change' => $forceChange,
        ]);

        $delivery = $this->dispatchCredentials(
            recipientName: $user->name,
            email: $user->email,
            phone: $user->phone,
            plainPassword: $plainPassword,
            channel: $channel,
            forceChange: $forceChange,
            userId: $user->id,
            studentIdCode: null
        );

        return [
            'success' => true,
            'user_id' => $user->id,
            'email' => $user->email,
            'phone' => $user->phone,
            'temporary_password' => $plainPassword,
            'delivery' => $delivery,
        ];
    }

    /**
     * Reset passwords for bulk staff by user ID.
     */
    public function resetStaffBulk(array $userIds, array $options = []): array
    {
        $users = User::whereIn('id', $userIds)
            ->whereDoesntHave('roles', fn ($q) => $q->whereIn('name', ['Student', 'Parent']))
            ->get();

        return $this->processStaffBatch($users, $options);
    }

    /**
     * Reset passwords for all staff members.
     */
    public function resetAllStaff(array $options = []): array
    {
        $users = User::whereDoesntHave('roles', fn ($q) => $q->whereIn('name', ['Student', 'Parent']))->get();
        return $this->processStaffBatch($users, $options);
    }

    /**
     * Process a collection of staff users for password reset.
     */
    public function processStaffBatch(Collection $users, array $options = []): array
    {
        $total = $users->count();
        $processed = 0;
        $emailsSent = 0;
        $smsSent = 0;
        $failed = 0;
        $results = [];

        foreach ($users as $user) {
            try {
                $res = $this->resetStaff($user, $options);
                if ($res['success']) {
                    $processed++;
                    if (! empty($res['delivery']['email_sent'])) {
                        $emailsSent++;
                    }
                    if (! empty($res['delivery']['sms_sent'])) {
                        $smsSent++;
                    }
                    $results[] = [
                        'user_id' => $user->id,
                        'name' => $user->name,
                        'email' => $res['email'],
                        'password' => $res['temporary_password'],
                        'delivery' => $res['delivery'],
                    ];
                } else {
                    $failed++;
                }
            } catch (\Throwable $e) {
                $failed++;
                Log::error('Error resetting staff password in batch', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [
            'total' => $total,
            'processed' => $processed,
            'emails_sent' => $emailsSent,
            'sms_sent' => $smsSent,
            'failed' => $failed,
            'details' => $results,
        ];
    }

    /**
     * Dispatch credentials via Email and/or SMS according to selected channel.
     */
    protected function dispatchCredentials(
        string $recipientName,
        string $email,
        ?string $phone,
        string $plainPassword,
        string $channel,
        bool $forceChange,
        int $userId,
        ?string $studentIdCode = null
    ): array {
        $emailSent = false;
        $smsSent = false;
        $errors = [];

        $loginUrl = route('login');
        $institutionName = config('branding.institution.name', config('app.name', 'College Portal'));

        // 1. Send Email
        if (in_array($channel, ['email', 'both']) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::to($email)->queue(new AccountCredentialsMailable([
                    'name' => $recipientName,
                    'email' => $email,
                    'student_id' => $studentIdCode,
                    'password' => $plainPassword,
                    'force_change' => $forceChange,
                    'login_url' => $loginUrl,
                ]));
                $emailSent = true;
            } catch (\Throwable $e) {
                $errors[] = "Email delivery error: {$e->getMessage()}";
                Log::error('Failed to queue credentials email', [
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // 2. Send SMS
        if (in_array($channel, ['sms', 'both']) && ! empty($phone) && $this->smsService) {
            try {
                $normalizedPhone = $this->smsService->normalizePhoneNumber($phone);
                if ($normalizedPhone && $this->smsService->validatePhoneNumber($normalizedPhone)) {
                    $message = "Dear {$recipientName}, your {$institutionName} portal password has been reset. Email: {$email}, Temp Password: {$plainPassword}. Login: {$loginUrl}";
                    if ($forceChange) {
                        $message .= ". You must change this password upon login.";
                    }

                    $res = $this->smsService->sendSingle($normalizedPhone, $message, [
                        'user_id' => $userId,
                        'audience_type' => 'password_reset',
                    ]);

                    $smsSent = $res['success'] ?? false;
                    if (! $smsSent && ! empty($res['message'])) {
                        $errors[] = "SMS error: {$res['message']}";
                    }
                } else {
                    $errors[] = "Invalid phone number format: {$phone}";
                }
            } catch (\Throwable $e) {
                $errors[] = "SMS delivery error: {$e->getMessage()}";
                Log::error('Failed to send credentials SMS', [
                    'phone' => $phone,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [
            'email_sent' => $emailSent,
            'sms_sent' => $smsSent,
            'errors' => $errors,
        ];
    }
}
