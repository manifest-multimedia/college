<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function handleCallback(Request $request)
    {
        // Get the token from AuthCentral
        $token = $request->get('token');

        // Verify the token with AuthCentral and retrieve user info
        $response = Http::withToken($token)->get('http://auth.pnmtc.edu.gh/api/user');

        if ($response->successful()) {
            // Check if the response is JSON
            if ($response->header('Content-Type') === 'application/json') {
                $authUser = $response->json();

                // Verify authUser is not null and contains necessary fields
                if ($authUser !== null && isset($authUser['email']) && isset($authUser['name'])) {
                    // Set a random password if the user doesn't exist in App1
                    $password = Hash::make(Str::random(10));

                    // Find or create the user in App1's local database using email
                    $user = User::updateOrCreate(
                        ['email' => $authUser['email']],
                        [
                            'name' => $authUser['name'],
                            'password' => $password,
                            'role' => $authUser['role'] ?? 'user',
                        ]
                    );

                    // Log the user into App1
                    Auth::login($user);

                    // Redirect to the intended page or dashboard if no redirect URI provided
                    return redirect($request->input('redirect_uri') ?? '/dashboard');
                } else {
                    // Redirect back to login if the response format is unexpected
                    return redirect()->route('login')->withErrors(['login' => 'Unexpected response format.']);
                }
            } else {
                // Handle non-JSON response
                return redirect()->route('login')->withErrors(['login' => 'Invalid response format from authentication server.']);
            }
        } else {
            // Log failure with status code and body
            Log::error('Failed to authenticate with AuthCentral:', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            // Redirect back to login with an error
            return redirect()->route('login')->withErrors(['login' => 'Authentication failed. Please try again.']);
        }
    }
}
