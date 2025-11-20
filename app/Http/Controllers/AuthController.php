<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuthenticationService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    protected AuthenticationService $authService;

    public function __construct(AuthenticationService $authService)
    {
        $this->authService = $authService;
    }

    public function handleCallback(Request $request)
    {
        // Log AuthCentral callback attempt for monitoring
        Log::info('AuthCentral callback received', [
            'current_method' => $this->authService->getAuthMethod(),
            'ip' => $request->ip(),
        ]);

        // Note: AuthCentral SSO is always available regardless of AUTH_METHOD setting
        // This allows users flexibility to choose between SSO and direct email/password login

        // Get the token from AuthCentral
        $token = $request->get('token');

        if (! $token) {
            Log::error('AuthCentral callback received without token');

            return redirect()->route('login')
                ->withErrors(['login' => 'Authentication failed: No token received.']);
        }

        // Initialize Guzzle client
        $client = new Client;

        try {
            // Send the request to AuthCentral with the Bearer token
            $response = $client->request('GET', $this->authService->getAuthCentralApiUrl(), [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.$token,
                ],
            ]);

            // Check if the response is successful and in JSON format
            if ($response->getStatusCode() === 200) {
                $responseData = json_decode($response->getBody(), true);

                // Log the complete response for debugging
                Log::info('Response from AuthCentral:', $responseData);

                // Extract user data from the new response structure
                $userData = null;
                $roles = [];

                // Handle the new response format where user is inside response.user
                if (isset($responseData['response']) && isset($responseData['response']['user'])) {
                    $userData = $responseData['response']['user'];

                    // Get roles either from response.roles or response.user.role_names
                    if (isset($responseData['response']['roles'])) {
                        $roles = $responseData['response']['roles'];
                    } elseif (isset($userData['role_names'])) {
                        $roles = $userData['role_names'];
                    } elseif (isset($userData['roles'])) {
                        // For complex role objects, extract just the names
                        $roles = array_map(function ($role) {
                            return is_array($role) ? $role['name'] : $role;
                        }, $userData['roles']);
                    }
                } else {
                    // Fall back to old format or direct structure
                    $userData = $responseData['user'] ?? $responseData;
                    $roles = $responseData['roles'] ?? [];
                }

                // Log extracted data
                Log::info('Extracted user data:', ['userData' => $userData, 'roles' => $roles]);

                // Validate user data has required fields
                if ($userData && isset($userData['email']) && isset($userData['name'])) {
                    // Extract password if provided by AuthCentral (for synchronization)
                    $password = $userData['password'] ?? null;

                    // Use AuthenticationService to create/update user
                    $user = $this->authService->createOrUpdateAuthCentralUser(
                        $userData,
                        is_array($roles) ? $roles : [$roles],
                        $password
                    );

                    // Authenticate user access
                    Auth::login($user);

                    Log::info('AuthCentral authentication successful', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'ip' => $request->ip(),
                    ]);

                    // Redirect to the intended page or dashboard if no redirect URI is provided
                    return redirect($request->input('redirect_uri') ?? route('dashboard'));
                } else {
                    Log::error('Required user fields missing in response:', ['userData' => $userData]);

                    return redirect()->route('login')->withErrors(['login' => 'Authentication failed: Missing required user information.']);
                }
            } else {
                return redirect()->route('login')->withErrors(['login' => 'Authentication server error.']);
            }
        } catch (RequestException $e) {
            // Log failure with status code and error details
            Log::error('Failed to authenticate with AuthCentral:', [
                'status' => $e->getResponse() ? $e->getResponse()->getStatusCode() : 'N/A',
                'error' => $e->getMessage(),
                'body' => $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body',
            ]);

            return redirect()->route('login')->withErrors(['login' => 'Authentication failed. Please try again.']);
        }
    }
}
