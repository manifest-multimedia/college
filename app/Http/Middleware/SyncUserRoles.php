<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use App\Services\AuthenticationService;

class SyncUserRoles
{
    protected AuthenticationService $authService;

    public function __construct(AuthenticationService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        if ($user && empty($user->roles->toArray())) {
            Log::info("Middleware: Syncing roles for user {$user->email} with no assigned roles");
            
            // Determine authentication method for this user
            $authMethod = $user->isAuthCentralUser() ? 'authcentral' : 'regular';
            
            if ($authMethod === 'authcentral') {
                // Handle AuthCentral users with legacy role field
                $this->handleAuthCentralUserRoles($user);
            } else {
                // Handle regular authentication users
                $this->handleRegularUserRoles($user);
            }
        }
        
        return $next($request);
    }

    /**
     * Handle role assignment for AuthCentral users.
     *
     * @param \App\Models\User $user
     * @return void
     */
    protected function handleAuthCentralUserRoles($user): void
    {
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
                    
                    // If we got valid roles, assign them using the service
                    if (!empty($authCentralRoles)) {
                        $this->authService->syncUserRoles($user, $authCentralRoles, 'authcentral');
                        return;
                    }
                }
            } catch (\Exception $e) {
                Log::error("Middleware: Error parsing AuthCentral role data for user {$user->email}: " . $e->getMessage());
                // Continue with normal role assignment below
            }
        }
        
        // Simple string case - use the service for consistent role assignment
        $this->authService->syncUserRoles($user, [$authCentralRole], 'authcentral');
    }

    /**
     * Handle role assignment for regular authentication users.
     *
     * @param \App\Models\User $user
     * @return void
     */
    protected function handleRegularUserRoles($user): void
    {
        // For regular users, assign the default role from configuration
        $defaultRole = $this->authService->getRegularConfig()['default_role'] ?? 'Staff';
        $this->authService->syncUserRoles($user, [$defaultRole], 'regular');
        
        Log::info("Middleware: Assigned default role '{$defaultRole}' to regular user {$user->email}");
    }
}
