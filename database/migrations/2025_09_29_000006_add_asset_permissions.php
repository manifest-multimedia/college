<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create asset management permissions
        $permissions = [
            'assets.view',
            'assets.create',
            'assets.edit',
            'assets.delete',
            'asset-categories.view',
            'asset-categories.create',
            'asset-categories.edit',
            'asset-categories.delete',
            'asset-settings.view',
            'asset-settings.edit',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to roles
        $auditorRole = Role::where('name', 'Auditor')->first();
        if ($auditorRole) {
            $auditorRole->givePermissionTo([
                'assets.view',
                'assets.create',
                'assets.edit',
                'assets.delete',
                'asset-categories.view',
                'asset-categories.create',
                'asset-categories.edit',
                'asset-categories.delete',
            ]);
        }

        $systemRole = Role::where('name', 'System')->first();
        if ($systemRole) {
            $systemRole->givePermissionTo($permissions);
        }

        $superAdminRole = Role::where('name', 'Super Admin')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo($permissions);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove permissions
        $permissions = [
            'assets.view',
            'assets.create',
            'assets.edit',
            'assets.delete',
            'asset-categories.view',
            'asset-categories.create',
            'asset-categories.edit',
            'asset-categories.delete',
            'asset-settings.view',
            'asset-settings.edit',
        ];

        Permission::whereIn('name', $permissions)->delete();
    }
};
