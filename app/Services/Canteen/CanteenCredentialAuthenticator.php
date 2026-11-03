<?php

namespace App\Services\Canteen;

use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class CanteenCredentialAuthenticator
{
    public function authenticate(string $username, string $password): ?User
    {
        $method = config('authentication.method');
        if ($method === 'regular') {
            $matches = User::where('email', trim($username))->orWhere('name', trim($username))->limit(2)->get();
            $user = $matches->count() === 1 ? $matches->first() : null;

            return $user && Hash::check($password, $user->password) ? $user : null;
        }
        if ($method !== 'authcentral') {
            $this->unavailable();
        }
        $url = config('authentication.authcentral.credential_verify_url');
        $key = config('authentication.authcentral.service_key');
        $parts = is_string($url) ? parse_url($url) : false;
        if (! $parts || ($parts['scheme'] ?? '') !== 'https' || empty($parts['host']) || isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment']) || ! is_string($key) || strlen($key) < 32) {
            $this->unavailable();
        }
        try {
            $options = ['allow_redirects' => false];
            if ($ca = config('authentication.authcentral.ca_file')) {
                $options['verify'] = $ca;
            }
            $response = Http::acceptJson()->connectTimeout(5)->timeout(10)->withOptions($options)
                ->withHeaders(['X-College-Service-Key' => $key])->post($url, ['username' => trim($username), 'password' => $password]);
        } catch (\Throwable) {
            $this->unavailable();
        }
        if ($response->status() === 401) {
            return null;
        }
        if ($response->status() === 403) {
            throw new HttpResponseException(response()->json(['message' => 'AuthCentral could not authorize this account. Check service configuration or account verification requirements.'], 403));
        }
        if (! $response->successful()) {
            $this->unavailable();
        }
        $identity = $response->json('user');
        if ($response->json('authenticated') !== true || ! is_array($identity) || ! is_string($identity['email'] ?? null) || ! filter_var($identity['email'], FILTER_VALIDATE_EMAIL)) {
            $this->unavailable();
        }

        // No automatic staff provisioning, remote role import, or password synchronization.
        return User::where('email', $identity['email'])->first();
    }

    private function unavailable(): never
    {
        throw new HttpResponseException(response()->json(['message' => 'The configured authentication provider is unavailable. Please try again later.'], 503));
    }
}
