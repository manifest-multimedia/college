<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ExamPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define exam-related permissions
        $permissions = [
            // Offline Exam Permissions
            ['name' => 'view offline exams', 'description' => 'Can view offline exams'],
            ['name' => 'create offline exams', 'description' => 'Can create new offline exams'],
            ['name' => 'update offline exams', 'description' => 'Can edit offline exams'],
            ['name' => 'delete offline exams', 'description' => 'Can delete offline exams'],

            // Exam Clearance Permissions
            ['name' => 'view clearances', 'description' => 'Can view exam clearances'],
            ['name' => 'manage clearances', 'description' => 'Can manage exam clearances'],
            ['name' => 'override clearances', 'description' => 'Can override clearance requirements manually'],

            // Exam Entry Tickets Permissions
            ['name' => 'view entry tickets', 'description' => 'Can view exam entry tickets'],
            ['name' => 'issue entry tickets', 'description' => 'Can issue exam entry tickets manually'],
            ['name' => 'verify entry tickets', 'description' => 'Can verify exam entry tickets'],
        ];

        try {
            // Start a transaction
            DB::beginTransaction();

            // Create permissions if they don't exist
            foreach ($permissions as $permissionData) {
                Permission::firstOrCreate([
                    'name' => $permissionData['name'],
                ], [
                    'description' => $permissionData['description'],
                    'guard_name' => 'web',
                ]);
            }

            // Assign permissions to roles
            $this->assignPermissionsToRoles();

            // Commit the transaction
            DB::commit();

            $this->command->info('Exam permissions created successfully!');
        } catch (\Exception $e) {
            // Rollback the transaction on error
            DB::rollBack();

            Log::error('Error creating exam permissions: '.$e->getMessage());
            $this->command->error('Error creating exam permissions: '.$e->getMessage());
        }
    }

    /**
     * Assign permissions to roles.
     */
    protected function assignPermissionsToRoles(): void
    {
        // Define role to permission mappings
        $rolePermissions = [
            'admin' => [
                'view offline exams', 'create offline exams', 'update offline exams', 'delete offline exams',
                'view clearances', 'manage clearances', 'override clearances',
                'view entry tickets', 'issue entry tickets', 'verify entry tickets',
            ],
            'lecturer' => [
                'view offline exams', 'create offline exams', 'update offline exams',
                'view clearances', 'view entry tickets', 'verify entry tickets',
            ],
            'finance' => [
                'view offline exams', 'view clearances', 'manage clearances', 'override clearances',
                'view entry tickets', 'issue entry tickets',
            ],
            'student' => [
                'view entry tickets',
            ],
        ];

        // Assign permissions to roles
        foreach ($rolePermissions as $roleName => $permissionNames) {
            $role = Role::firstWhere('name', $roleName);

            if ($role) {
                foreach ($permissionNames as $permissionName) {
                    $permission = Permission::firstWhere('name', $permissionName);

                    if ($permission && ! $role->hasPermissionTo($permission)) {
                        $role->givePermissionTo($permission);
                    }
                }
            }
        }
    }
}
