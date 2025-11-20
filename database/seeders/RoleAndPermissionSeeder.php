<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        // Dashboard permissions
        Permission::create(['name' => 'view dashboard']);

        // User management permissions
        Permission::create(['name' => 'view users']);
        Permission::create(['name' => 'create users']);
        Permission::create(['name' => 'edit users']);
        Permission::create(['name' => 'delete users']);

        // Student management permissions
        Permission::create(['name' => 'view students']);
        Permission::create(['name' => 'create students']);
        Permission::create(['name' => 'edit students']);
        Permission::create(['name' => 'delete students']);

        // Finance permissions
        Permission::create(['name' => 'view finance']);
        Permission::create(['name' => 'create invoices']);
        Permission::create(['name' => 'process payments']);
        Permission::create(['name' => 'generate financial reports']);

        // Academic permissions
        Permission::create(['name' => 'view courses']);
        Permission::create(['name' => 'create courses']);
        Permission::create(['name' => 'edit courses']);
        Permission::create(['name' => 'delete courses']);
        Permission::create(['name' => 'manage curriculum']);

        // Exam permissions
        Permission::create(['name' => 'view exams']);
        Permission::create(['name' => 'create exams']);
        Permission::create(['name' => 'edit exams']);
        Permission::create(['name' => 'grade exams']);
        Permission::create(['name' => 'generate exam reports']);

        // Library permissions
        Permission::create(['name' => 'view library']);
        Permission::create(['name' => 'manage books']);
        Permission::create(['name' => 'process loans']);

        // IT permissions
        Permission::create(['name' => 'manage systems']);
        Permission::create(['name' => 'view system logs']);

        // Procurement permissions
        Permission::create(['name' => 'manage procurement']);
        Permission::create(['name' => 'approve purchases']);

        // Store permissions
        Permission::create(['name' => 'manage inventory']);
        Permission::create(['name' => 'view inventory']);

        // Security permissions
        Permission::create(['name' => 'view security logs']);
        Permission::create(['name' => 'manage security']);

        // Create roles and assign permissions

        // System role - has all permissions
        $systemRole = Role::create(['name' => 'System']);
        $systemRole->givePermissionTo(Permission::all());

        // Super Admin role - has all permissions except some system ones
        $superAdminRole = Role::create(['name' => 'Super Admin']);
        $superAdminRole->givePermissionTo(Permission::all());

        // Administrator role
        $administratorRole = Role::create(['name' => 'Administrator']);
        $administratorRole->givePermissionTo([
            'view dashboard',
            'view users', 'create users', 'edit users',
            'view students', 'create students', 'edit students',
            'view finance',
            'view courses', 'create courses', 'edit courses',
            'view exams', 'create exams', 'edit exams', 'generate exam reports',
            'view library',
            'view inventory',
        ]);

        // Finance Manager role
        $financeManagerRole = Role::create(['name' => 'Finance Manager']);
        $financeManagerRole->givePermissionTo([
            'view dashboard',
            'view finance',
            'create invoices',
            'process payments',
            'generate financial reports',
            'view students',
        ]);

        // Lecturer role
        $lecturerRole = Role::create(['name' => 'Lecturer']);
        $lecturerRole->givePermissionTo([
            'view dashboard',
            'view courses',
            'view students',
            'view exams', 'create exams', 'edit exams', 'grade exams',
            'generate exam reports',
        ]);

        // Student role
        $studentRole = Role::create(['name' => 'Student']);
        $studentRole->givePermissionTo([
            'view dashboard',
            'view courses',
            'view exams',
        ]);

        // Parent role
        $parentRole = Role::create(['name' => 'Parent']);
        $parentRole->givePermissionTo([
            'view dashboard',
        ]);

        // Academic Officer role
        $academicOfficerRole = Role::create(['name' => 'Academic Officer']);
        $academicOfficerRole->givePermissionTo([
            'view dashboard',
            'view students', 'edit students',
            'view courses', 'create courses', 'edit courses', 'manage curriculum',
            'view exams', 'generate exam reports',
        ]);

        // Procurement Officer role
        $procurementOfficerRole = Role::create(['name' => 'Procurement Officer']);
        $procurementOfficerRole->givePermissionTo([
            'view dashboard',
            'manage procurement',
            'approve purchases',
        ]);

        // Store Manager role
        $storeManagerRole = Role::create(['name' => 'Store Manager']);
        $storeManagerRole->givePermissionTo([
            'view dashboard',
            'manage inventory',
            'view inventory',
        ]);

        // Librarian role
        $librarianRole = Role::create(['name' => 'Librarian']);
        $librarianRole->givePermissionTo([
            'view dashboard',
            'view library',
            'manage books',
            'process loans',
        ]);

        // IT Manager role
        $itManagerRole = Role::create(['name' => 'IT Manager']);
        $itManagerRole->givePermissionTo([
            'view dashboard',
            'manage systems',
            'view system logs',
        ]);

        // Staff role
        $staffRole = Role::create(['name' => 'Staff']);
        $staffRole->givePermissionTo([
            'view dashboard',
        ]);

        // Security Officer role
        $securityOfficerRole = Role::create(['name' => 'Security Officer']);
        $securityOfficerRole->givePermissionTo([
            'view dashboard',
            'view security logs',
            'manage security',
        ]);
    }
}
