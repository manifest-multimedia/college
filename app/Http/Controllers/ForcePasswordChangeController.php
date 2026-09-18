<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ForcePasswordChangeController extends Controller
{
    /**
     * Display the forced password change prompt.
     */
    public function show(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! $user || ! $user->force_password_change) {
            return redirect()->route('dashboard');
        }

        return view('custom-auth.force-password-change', [
            'user' => $user,
        ]);
    }

    /**
     * Update the user's password and remove the forced change flag.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user || ! $user->force_password_change) {
            return redirect()->route('dashboard');
        }

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->password = Hash::make($request->password);
        $user->force_password_change = false;
        $user->save();

        // Also synchronize the updated password to AuthCentral so SSO credentials match
        try {
            app(\App\Services\AuthCentralSyncBridgeService::class)->syncCollegeUser($user, $request->password);
        } catch (\Throwable $e) {
            Log::warning('Failed to sync updated personal password to AuthCentral: ' . $e->getMessage());
        }

        Log::info('User successfully completed initial forced password change', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip(),
        ]);

        $destination = $user->hasRole('Student') ? 'student.dashboard' : 'dashboard';

        return redirect()->route($destination)->with('success', 'Your password has been successfully updated. Welcome to your account!');
    }
}
