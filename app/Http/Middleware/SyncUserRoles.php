<?php

namespace App\Http\Middleware;

use App\Services\AuthenticationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

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

            // Roles are always owned by College360. The legacy users.role field
            // may contain stale AuthCentral data and must not be used here.
            $this->handleCollegeDefaultRole($user);
        }

        return $next($request);
    }

    /**
     * Give a roleless account a College360-owned default role.
     *
     * @param  \App\Models\User  $user
     */
    protected function handleCollegeDefaultRole($user): void
    {
        $roleConfig = $this->authService->getRoleConfig();
        $defaultRole = $user->student()->exists()
            ? ($roleConfig['student_default'] ?? 'Student')
            : ($roleConfig['staff_default'] ?? 'Staff');

        $this->authService->syncUserRoles($user, [$defaultRole], 'college-default');
        Log::warning("Middleware: Assigned College360 default role '{$defaultRole}' to roleless user {$user->email}");
    }
}
