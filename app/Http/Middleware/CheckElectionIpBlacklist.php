<?php

namespace App\Http\Middleware;

use App\Models\Election;
use App\Models\ElectionAuditLog;
use App\Models\ElectionIpBlacklist;
use App\Models\ElectionIpWhitelist;
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
        $candidateIps = $this->resolveCandidateIps($request);
        $clientIp = $candidateIps[0] ?? (string) $request->ip();

        try {
            $isWhitelisted = ElectionIpWhitelist::query()
                ->whereIn('ip_address', $candidateIps)
                ->where('is_active', true)
                ->exists();

            if ($isWhitelisted) {
                return $next($request);
            }

            $isBlocked = ElectionIpBlacklist::query()
                ->whereIn('ip_address', $candidateIps)
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
                    'candidate_ips' => $candidateIps,
                ],
                $clientIp,
                $request->userAgent()
            );

            abort(403, 'Access to the election voting page is blocked from this IP address.');
        }

        return $next($request);
    }

    /**
     * @return array<int, string>
     */
    protected function resolveCandidateIps(Request $request): array
    {
        $candidates = [];

        foreach (['CF-Connecting-IP', 'X-Real-IP'] as $header) {
            $value = $request->header($header);

            if (is_string($value) && $value !== '') {
                $candidates[] = $value;
            }
        }

        $forwardedFor = $request->header('X-Forwarded-For');
        if (is_string($forwardedFor) && $forwardedFor !== '') {
            $candidates = array_merge($candidates, explode(',', $forwardedFor));
        }

        $candidates[] = (string) $request->ip();
        $candidates[] = (string) $request->server('REMOTE_ADDR');

        $normalized = collect($candidates)
            ->map(fn ($ip) => $this->normalizeIp((string) $ip))
            ->filter()
            ->values()
            ->all();

        return $normalized !== [] ? array_values(array_unique($normalized)) : [(string) $request->ip()];
    }

    protected function normalizeIp(string $ip): ?string
    {
        $ip = trim($ip);

        if ($ip === '') {
            return null;
        }

        if (str_starts_with($ip, '::ffff:')) {
            $ip = substr($ip, 7);
        }

        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : null;
    }
}
