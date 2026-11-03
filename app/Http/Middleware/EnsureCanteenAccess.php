<?php

namespace App\Http\Middleware;

use App\Models\Student;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class EnsureCanteenAccess
{
    public static function eligible(User $user): bool
    {
        // College360 owns account roles; do not trust the legacy users.role field.
        $studentRole = config('branding.roles.student_default', 'Student');
        return ! $user->hasRole($studentRole)
            && ! Student::query()->where('user_id', $user->id)
                ->when(filled($user->email), fn ($q) => $q->orWhere('email', $user->email))->exists()
            && ! $user->force_password_change
            && ! $user->two_factor_confirmed_at;
    }

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        abort_unless($request->bearerToken() && $user && self::eligible($user)
            && $user->currentAccessToken() instanceof \Laravel\Sanctum\PersonalAccessToken
            && $user->tokenCan('canteen:access'), 403, 'College staff access is required.');
        return $next($request);
    }
}
