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
            $authUser = $response->json();

            // Set a random password if the user doesn't exist in App1
            $password = Hash::make(Str::random(10));

            // Find or create the user in App1's local database using email
            $user = User::updateOrCreate(
                ['email' => $authUser['email']],
                ['name' => $authUser['name'], 'password' => $password]
            );

            // Log the user into App1
            Auth::login($user);

            // Redirect to the intended page or dashboard if no redirect URI provided
            return redirect($request->input('redirect_uri') ?? '/dashboard');
        }

        // Redirect back to login with error if authentication fails
        return redirect()->route('login')->withErrors(['login' => 'Authentication failed. Please try again.']);
    }
}
