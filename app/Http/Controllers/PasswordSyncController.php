<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\AuthenticationService;
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
     * Handle password synchronization webhook from AuthCentral.
     * This allows AuthCentral users to use the same password locally.
     *
     * @param Request $request
     * @return JsonResponse
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

            // Verify API key (should match config or environment)
            $expectedApiKey = config('authentication.password_sync.api_key') ?: env('PASSWORD_SYNC_API_KEY');
            if (!$expectedApiKey || !hash_equals($expectedApiKey, $validated['api_key'])) {
                Log::warning('Invalid API key for password sync', [
                    'email' => $validated['email'],
                    'ip' => $request->ip(),
                    'provided_key' => 'hidden_for_security',
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

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
                    'message' => 'Password synchronized successfully'
                ]);
            } else {
                Log::warning('Password sync failed - user not found', [
                    'email' => $validated['email'],
                    'ip' => $request->ip(),
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'User not found or sync failed'
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
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Password sync API error', [
                'email' => $request->get('email'),
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error'
            ], 500);
        }
    }

    /**
     * Health check endpoint for password sync API.
     *
     * @return JsonResponse
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