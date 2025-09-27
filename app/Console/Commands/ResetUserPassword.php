<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AuthenticationService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ResetUserPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:reset-password {email} {--password= : Set specific password, otherwise generates random one}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset a user\'s password - useful for AuthCentral users who need local password access';

    protected AuthenticationService $authService;

    public function __construct(AuthenticationService $authService)
    {
        parent::__construct();
        $this->authService = $authService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->option('password');

        // Find the user
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email '{$email}' not found.");
            return Command::FAILURE;
        }

        // Generate password if not provided
        if (!$password) {
            $password = Str::random(12) . '!';
            $this->info("Generated random password: {$password}");
        }

        // Check if this is likely an AuthCentral user
        $isAuthCentralUser = $this->authService->isLikelyAuthCentralUser($user);
        if ($isAuthCentralUser) {
            $this->warn("This appears to be an AuthCentral user. Syncing password for dual authentication support.");
        }

        // Use AuthenticationService for consistency and logging
        $success = $this->authService->syncAuthCentralUserPassword($email, $password);

        if (!$success) {
            $this->error("Failed to reset password for user: {$email}");
            return Command::FAILURE;
        }

        $this->info("Password reset successfully for user: {$user->name} ({$email})");
        $this->warn("Please share this password securely with the user.");
        $this->warn("User should change this password after first login.");

        // Show login instructions
        $this->line('');
        $this->line('The user can now:');
        $this->line('1. Login with email/password using the credentials above');
        $this->line('2. Continue using AuthCentral SSO (if available)');
        $this->line('3. Use "Forgot Password" to set their own password');
        
        if ($isAuthCentralUser) {
            $this->line('');
            $this->warn('Note: This user can now authenticate using BOTH methods:');
            $this->warn('- AuthCentral SSO (original method)');
            $this->warn('- Email/Password (newly enabled)');
        }

        return Command::SUCCESS;
    }
}