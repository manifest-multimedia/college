<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class CreateAdminCommand extends Command
{
    protected $signature = 'admin:create 
                          {--name= : The name of the admin user}
                          {--email= : The email of the admin user}
                          {--password= : The password for the admin user}
                          {--role=Super Admin : The role to assign (Super Admin, IT Manager, System)}';

    protected $description = 'Create an admin user with specified role';

    public function handle()
    {
        $name = $this->option('name') ?: $this->ask('Enter admin name');
        $email = $this->option('email') ?: $this->ask('Enter admin email');
        $password = $this->option('password') ?: $this->secret('Enter admin password');
        $role = $this->option('role');

        // Validate input
        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            $this->error('Validation failed:');
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return 1;
        }

        // Check if role exists
        if (! Role::where('name', $role)->exists()) {
            $this->error("Role '{$role}' does not exist.");
            $this->info('Available roles: '.Role::pluck('name')->implode(', '));

            return 1;
        }

        // Create user
        try {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]);

            // Assign role
            $user->assignRole($role);

            $this->info('✅ Admin user created successfully!');
            $this->table(['Field', 'Value'], [
                ['Name', $user->name],
                ['Email', $user->email],
                ['Role', $role],
                ['ID', $user->id],
                ['Created', $user->created_at->format('Y-m-d H:i:s')],
            ]);

            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to create admin user: '.$e->getMessage());

            return 1;
        }
    }
}
