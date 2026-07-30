<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentIsActive
{
    /**
     * Only students with an explicitly active student profile may use student-facing routes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // This middleware is also used on shared System|Student routes. Staff
        // users continue through; the Student role is still enforced by routes.
        if (! $user || ! $user->hasRole('Student')) {
            return $next($request);
        }

        $student = $user->student;

        if (! $student || ! $student->isActive()) {
            abort(403, 'Your student portal access is unavailable. Please contact the academic office.');
        }

        return $next($request);
    }
}
