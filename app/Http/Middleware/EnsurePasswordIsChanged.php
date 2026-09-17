<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsChanged
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            if (! empty($user->force_password_change)) {
                // Allow force password change routes and logout
                if (! $request->routeIs('password.force-change', 'password.force-change.update', 'logout')) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'message' => 'Your password has been reset by an administrator. You must set a new password to continue.',
                            'redirect' => route('password.force-change'),
                        ], 403);
                    }

                    return redirect()->route('password.force-change');
                }
            }
        }

        return $next($request);
    }
}
