<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    public function handleCallback(Request $request)
    {
        // Get the token from AuthCentral
        $token = $request->get('token');

        // Initialize Guzzle client
        $client = new Client();

        try {
            // Send the request to AuthCentral with the Bearer token
            $response = $client->request('GET', 'https://auth.pnmtc.edu.gh/api/user', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ]
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
                        $roles = array_map(function($role) {
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
                    // Set a random password if the user doesn't exist in College
                    $password = Hash::make(Str::random(10));

                    // Find or create the user in College's local database using email
                    $user = User::updateOrCreate(
                        ['email' => $userData['email']],
                        [
                            'name' => $userData['name'],
                            'password' => $password,
                        ]
                    );

                    // Sync Spatie roles
                    $this->syncUserRoles($user, is_array($roles) ? $roles : [$roles]);

                    // Authenticate user access
                    Auth::login($user);

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
                'body' => $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body'
            ]);

            return redirect()->route('login')->withErrors(['login' => 'Authentication failed. Please try again.']);
        }
    }

    /**
     * Sync user roles based on the roles provided by AuthCentral.
     *
     * @param User $user
     * @param array $authCentralRoles
     * @return void
     */
    protected function syncUserRoles(User $user, array $authCentralRoles): void
    {
        // Remove any existing roles
        $user->syncRoles([]);

        // Assign new roles
        foreach ($authCentralRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $user->assignRole($role);
                Log::info("User {$user->email} assigned role: {$roleName}");
            } else {
                Log::warning("Role {$roleName} not found in system for user {$user->email}.");
            }
        }

        // If no roles were assigned, assign a default role
        if (empty($user->getRoleNames())) {
            $defaultRole = 'Staff';
            $user->assignRole($defaultRole);
            Log::warning("No valid roles found for user {$user->email}. Assigned default role: {$defaultRole}");
        }
    }
}