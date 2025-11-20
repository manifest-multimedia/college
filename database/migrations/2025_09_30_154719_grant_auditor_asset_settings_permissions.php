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
        // Grant asset settings permissions to Auditor role
        $auditorRole = Role::where('name', 'Auditor')->first();

        if ($auditorRole) {
            // Grant asset settings permissions that were missing
            $permissions = [
                'asset-settings.view',
                'asset-settings.edit',
            ];

            foreach ($permissions as $permissionName) {
                $permission = Permission::where('name', $permissionName)->first();
                if ($permission && ! $auditorRole->hasPermissionTo($permission)) {
                    $auditorRole->givePermissionTo($permission);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove asset settings permissions from Auditor role
        $auditorRole = Role::where('name', 'Auditor')->first();

        if ($auditorRole) {
            $permissions = [
                'asset-settings.view',
                'asset-settings.edit',
            ];

            foreach ($permissions as $permissionName) {
                $permission = Permission::where('name', $permissionName)->first();
                if ($permission && $auditorRole->hasPermissionTo($permission)) {
                    $auditorRole->revokePermissionTo($permission);
                }
            }
        }
    }
};
