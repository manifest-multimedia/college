<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class CreateSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:superadmin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a super admin user for testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Create or find the Super Admin role
        $role = Role::firstOrCreate(['name' => 'Super Admin']);

        // Check if user already exists
        $user = User::where('email', 'admin@admin.com')->first();
        
        if ($user) {
            $this->info('User already exists, updating...');
        } else {
            $user = new User();
        }

        $user->fill([
            'name' => 'Super Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        
        $user->save();
        
        // Assign role
        $user->assignRole('Super Admin');

        $this->info('Super Admin user created successfully!');
        $this->info('Email: admin@admin.com');
        $this->info('Password: password');
    }
}
