<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * @property mixed $description
 */
class CreateDemoAccounts extends Command
{
    protected $signature = 'demo:create-accounts';

    protected $description = 'Create demo accounts with predefined roles';

    public function handle()
    {
        $this->info('Creating demo accounts...');

        // Create Super Admin account
        $user = User::firstOrCreate(
            ['email' => 'superadmin@demo.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('demo@233'),
                'email_verified_at' => now(),
            ]
        );

        // Ensure the roles exist
        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        $systemRole = Role::firstOrCreate(['name' => 'System']);

        // Assign roles to the user
        $user->syncRoles([$superAdminRole, $systemRole]);

        $this->info('Demo accounts created successfully!');
        $this->info('Super Admin Credentials:');
        $this->info('Email: superadmin@demo.com');
        $this->info('Password: demo@233');
    }
}
