<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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

                // Verify authUser is not null
                if ($authUser !== null && isset($authUser['email']) && isset($authUser['name'])) {
                    // Proceed with creating or updating the user
                    // ...
                } else {
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

            return redirect()->route('login')->withErrors(['login' => 'Authentication failed. Please try again.']);
        }
    }
}
