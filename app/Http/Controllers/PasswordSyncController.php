<?php

namespace App\Http\Controllers;

use App\Services\AuthenticationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PasswordSyncController extends Controller
{
    protected AuthenticationService $authService;

    public function __construct(AuthenticationService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Get API key from database with fallback to config/env.
     * Priority: Database → Config → Environment
     */
    private function getApiKey(): ?string
    {
        // First, try to get from database
        $setting = DB::table('settings')
            ->where('key', 'password_sync_api_key')
            ->first();

        if ($setting && ! empty($setting->value)) {
            return $setting->value;
        }

        // Fallback to config, then environment
        return config('authentication.password_sync.api_key') ?: env('PASSWORD_SYNC_API_KEY');
    }

    /**
     * Update the last used timestamp for API key.
     */
    private function updateApiKeyLastUsed(): void
    {
        try {
            DB::table('settings')->updateOrInsert(
                ['key' => 'password_sync_api_key_last_used_at'],
                [
                    'value' => now()->toDateTimeString(),
                    'description' => 'Last time the password sync API key was used',
                    'updated_at' => now(),
                ]
            );
        } catch (\Exception $e) {
            // Don't fail the request if we can't update last used
            Log::debug('Failed to update API key last used timestamp', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle password synchronization webhook from AuthCentral.
     * This allows AuthCentral users to use the same password locally.
     */
    public function syncPassword(Request $request): JsonResponse
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string|min:6',
                'event' => 'in:password_changed,user_registered,password_reset',
                'api_key' => 'required|string', // For security
            ]);

            // Verify API key (priority: Database → Config → Environment)
            $expectedApiKey = $this->getApiKey();
            if (! $expectedApiKey || ! hash_equals($expectedApiKey, $validated['api_key'])) {
                Log::warning('Invalid API key for password sync', [
                    'email' => $validated['email'],
                    'ip' => $request->ip(),
                    'provided_key' => 'hidden_for_security',
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }

            // Update last used timestamp
            $this->updateApiKeyLastUsed();

            // Sync the password using AuthenticationService
            $success = $this->authService->syncAuthCentralUserPassword(
                $validated['email'],
                $validated['password']
            );

            if ($success) {
                Log::info('Password synchronized successfully via webhook', [
                    'email' => $validated['email'],
                    'event' => $validated['event'] ?? 'unknown',
                    'ip' => $request->ip(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Password synchronized successfully',
                ]);
            } else {
                Log::warning('Password sync failed - user not found', [
                    'email' => $validated['email'],
                    'ip' => $request->ip(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'User not found or sync failed',
                ], 404);
            }

        } catch (ValidationException $e) {
            Log::warning('Password sync validation failed', [
                'errors' => $e->errors(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Password sync API error', [
                'email' => $request->get('email'),
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
            ], 500);
        }
    }

    /**
     * Health check endpoint for password sync API.
     */
    public function healthCheck(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'service' => 'Password Sync API',
            'timestamp' => now()->toISOString(),
        ]);
    }
}
