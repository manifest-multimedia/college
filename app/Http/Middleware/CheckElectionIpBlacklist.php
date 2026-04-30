<?php

namespace App\Http\Middleware;

use App\Models\Election;
use App\Models\ElectionAuditLog;
use App\Models\ElectionIpBlacklist;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckElectionIpBlacklist
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $clientIp = $this->resolveClientIp($request);

        try {
            $isBlocked = ElectionIpBlacklist::query()
                ->where('ip_address', $clientIp)
                ->where('is_active', true)
                ->exists();
        } catch (QueryException $exception) {
            // Allow requests to continue before migrations are run.
            return $next($request);
        }

        if ($isBlocked) {
            $routeElection = $request->route('election');
            $election = $routeElection instanceof Election ? $routeElection : null;

            ElectionAuditLog::log(
                $election,
                'ip',
                $clientIp,
                'ip_blacklist_block',
                'Request blocked by election IP blacklist',
                [
                    'path' => $request->path(),
                    'method' => $request->method(),
                ],
                $clientIp,
                $request->userAgent()
            );

            abort(403, 'Access to the election voting page is blocked from this IP address.');
        }

        return $next($request);
    }

    protected function resolveClientIp(Request $request): string
    {
        $cfIp = $request->header('CF-Connecting-IP');
        if (filter_var($cfIp, FILTER_VALIDATE_IP)) {
            return $cfIp;
        }

        $forwardedFor = $request->header('X-Forwarded-For');
        if (is_string($forwardedFor) && $forwardedFor !== '') {
            $firstForwardedIp = trim(explode(',', $forwardedFor)[0]);
            if (filter_var($firstForwardedIp, FILTER_VALIDATE_IP)) {
                return $firstForwardedIp;
            }
        }

        return (string) $request->ip();
    }
}
