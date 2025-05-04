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
            
            // Check if the role exists in our Spatie roles
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
