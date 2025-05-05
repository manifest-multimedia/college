<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;

class SyncUserRoles
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        if ($user && empty($user->roles->toArray())) {
            // If user has no Spatie roles yet, assign them based on the legacy 'role' field
            $authCentralRole = $user->role ?? 'Staff';
            
            // Handle if role is stored as an array or JSON string
            if (is_string($authCentralRole) && (str_starts_with($authCentralRole, '[') || str_starts_with($authCentralRole, '{'))) {
                try {
                    $roleData = json_decode($authCentralRole, true);
                    if (is_array($roleData)) {
                        // Extract role names from complex objects if needed
                        $authCentralRoles = [];
                        foreach ($roleData as $role) {
                            if (is_array($role) && isset($role['name'])) {
                                $authCentralRoles[] = $role['name'];
                            } elseif (is_string($role)) {
                                $authCentralRoles[] = $role;
                            }
                        }
                        
                        // If we got valid roles, assign them
                        if (!empty($authCentralRoles)) {
                            foreach ($authCentralRoles as $roleName) {
                                $role = Role::where('name', $roleName)->first();
                                if ($role) {
                                    $user->assignRole($role);
                                    Log::info("Middleware: User {$user->email} assigned role: {$roleName}");
                                }
                            }
                            return $next($request);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("Error parsing role data: " . $e->getMessage());
                    // Continue with normal role assignment below
                }
            }
            
            // Check if the role exists in our Spatie roles - simple string case
            $role = Role::where('name', $authCentralRole)->first();
            
            if ($role) {
                // Assign the matching role
                $user->assignRole($role);
                Log::info("Middleware: User {$user->email} assigned role: {$authCentralRole}");
            } else {
                // If no matching role found, assign a default role
                $defaultRole = 'Staff';
                $user->assignRole($defaultRole);
                Log::warning("Middleware: Role {$authCentralRole} not found in system. User {$user->email} assigned default role: {$defaultRole}");
            }
        }
        
        return $next($request);
    }
}
