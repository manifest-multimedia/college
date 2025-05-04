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
                $authUser = json_decode($response->getBody(), true);

                if ($authUser !== null && isset($authUser['email']) && isset($authUser['name'])) {
                    // Set a random password if the user doesn't exist in App1
                    $password = Hash::make(Str::random(10));

                    // Get role from AuthCentral response or default to 'Staff'
                    $authCentralRole = $authUser['role'] ?? 'Staff';

                    // Find or create the user in App1's local database using email
                    $user = User::updateOrCreate(
                        ['email' => $authUser['email']],
                        [
                            'name' => $authUser['name'],
                            'password' => $password,
                            'role' => $authCentralRole, // Keep this for backward compatibility
                        ]
                    );

                    // Sync Spatie roles based on AuthCentral role
                    $this->syncUserRole($user, $authCentralRole);

                    //Authenticate User Access
                    Auth::login($user);

                    // Redirect to the intended page or dashboard if no redirect URI is provided
                    return redirect($request->input('redirect_uri') ?? route('dashboard'));
                } else {
                    // Redirect back to login if the response format is unexpected
                    return redirect()->route('login')->withErrors(['login' => 'Unexpected response format.']);
                }
            } else {
                // Handle non-200 responses
                return redirect()->route('login')->withErrors(['login' => 'Authentication server error.']);
            }
        } catch (RequestException $e) {
            // Log failure with status code and error details
            Log::error('Failed to authenticate with AuthCentral:', [
                'status' => $e->getResponse() ? $e->getResponse()->getStatusCode() : 'N/A',
                'error' => $e->getMessage(),
                'body' => $e->getResponse() ? $e->getResponse()->getBody()->getContents() : 'No response body'
            ]);

            // Redirect back to login with an error message
            return redirect()->route('login')->withErrors(['login' => 'Authentication failed. Please try again.']);
        }
    }

    /**
     * Sync user roles based on the role provided by AuthCentral.
     *
     * @param User $user
     * @param string $authCentralRole
     * @return void
     */
    protected function syncUserRole(User $user, string $authCentralRole): void
    {
        // Remove any existing roles first
        $user->syncRoles([]);
        
        // Check if the role exists in our Spatie roles
        $role = Role::where('name', $authCentralRole)->first();
        
        if ($role) {
            // Assign the matching role
            $user->assignRole($role);
            Log::info("User {$user->email} assigned role: {$authCentralRole}");
        } else {
            // If no matching role found, assign a default role (e.g., Staff)
            $defaultRole = 'Staff';
            $user->assignRole($defaultRole);
            Log::warning("Role {$authCentralRole} not found in system. User {$user->email} assigned default role: {$defaultRole}");
        }
    }
}
