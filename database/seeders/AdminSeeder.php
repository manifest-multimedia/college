<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating default admin accounts...');

        // Create default Super Admin if it doesn't exist
        if (! User::where('email', 'admin@college.local')->exists()) {
            $superAdmin = User::create([
                'name' => 'System Administrator',
                'email' => 'admin@college.local',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]);

            $superAdmin->assignRole('Super Admin');
            $this->command->info('✅ Super Admin created: admin@college.local / password123');
        } else {
            $this->command->warn('⚠️  Super Admin already exists: admin@college.local');
        }

        // Create default IT Manager if it doesn't exist
        if (! User::where('email', 'itmanager@college.local')->exists()) {
            $itManager = User::create([
                'name' => 'IT Manager',
                'email' => 'itmanager@college.local',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]);

            $itManager->assignRole('IT Manager');
            $this->command->info('✅ IT Manager created: itmanager@college.local / password123');
        } else {
            $this->command->warn('⚠️  IT Manager already exists: itmanager@college.local');
        }

        // Create default System user if it doesn't exist
        if (! User::where('email', 'system@college.local')->exists()) {
            $systemUser = User::create([
                'name' => 'System User',
                'email' => 'system@college.local',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]);

            $systemUser->assignRole('System');
            $this->command->info('✅ System User created: system@college.local / password123');
        } else {
            $this->command->warn('⚠️  System User already exists: system@college.local');
        }

        $this->command->info('Admin account seeding completed!');
    }
}
