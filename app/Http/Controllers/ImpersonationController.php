<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    public function __construct()
    {
        // Controller is protected by route middleware; no global middleware here
    }

    /**
     * Start an impersonation session. Only users with the 'System' role can impersonate.
     */
    public function start(Request $request, User $user): RedirectResponse
    {
        // Store the original user id only if not already impersonating
        if (! $request->session()->has('impersonator_id')) {
            $request->session()->put('impersonator_id', (int) auth()->id());
        }

        // Login as the target user
        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('success', 'You are now impersonating: '.$user->name);
    }

    /**
     * Stop an active impersonation session and return to the impersonator account.
     */
    public function stop(Request $request): RedirectResponse
    {
        $impersonatorId = $request->session()->pull('impersonator_id');

        if ($impersonatorId) {
            $impersonator = User::find($impersonatorId);
            if ($impersonator) {
                Auth::guard('web')->login($impersonator);
                $request->session()->regenerate();

                return redirect()->route('dashboard')->with('success', 'Impersonation ended.');
            }
        }

        // If no impersonator found, just logout as a safety measure
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
