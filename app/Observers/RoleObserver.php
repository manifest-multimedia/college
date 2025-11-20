<?php

namespace App\Observers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class RoleObserver
{
    /**
     * Handle the Role "created" event.
     */
    public function created(Role $role): void
    {
        $this->sendWebhook($role, 'role.created');
    }

    /**
     * Handle the Role "updated" event.
     */
    public function updated(Role $role): void
    {
        $this->sendWebhook($role, 'role.updated');
    }

    /**
     * Handle the Role "deleted" event.
     */
    public function deleted(Role $role): void
    {
        $this->sendWebhook($role, 'role.deleted');
    }

    /**
     * Send webhook to AuthCentral about role changes
     */
    protected function sendWebhook(Role $role, string $eventType): void
    {
        try {
            $webhookUrl = config('auth.authcentral.webhook_url') ?: env('AUTHCENTRAL_WEBHOOK_URL');
            $apiKey = config('auth.authcentral.webhook_api_key') ?: env('AUTHCENTRAL_WEBHOOK_API_KEY');

            if (! $webhookUrl || ! $apiKey) {
                Log::warning('AuthCentral webhook not configured');

                return;
            }

            $response = Http::timeout(30)
                ->withHeaders([
                    'X-API-Key' => $apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($webhookUrl, [
                    'event_type' => $eventType,
                    'app_identifier' => 'cis',
                    'timestamp' => now()->toIso8601String(),
                    'data' => [
                        'id' => $role->id,
                        'name' => $role->name,
                        'guard_name' => $role->guard_name,
                        'metadata' => [
                            'permissions_count' => $role->permissions()->count(),
                            'users_count' => $role->users()->count(),
                            'created_at' => $role->created_at?->toIso8601String(),
                            'updated_at' => $role->updated_at?->toIso8601String(),
                        ],
                    ],
                ]);

            if (! $response->successful()) {
                Log::error('Failed to send role webhook to AuthCentral', [
                    'role_id' => $role->id,
                    'event_type' => $eventType,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Exception sending role webhook to AuthCentral', [
                'role_id' => $role->id,
                'event_type' => $eventType,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle role-permission assignments
     */
    public function pivotAttached($role, $relationName, $pivotIds, $pivotIdsAttributes)
    {
        if ($relationName !== 'permissions') {
            return;
        }

        // Get permission details
        $permissions = $role->permissions()->whereIn('id', $pivotIds)->get();

        foreach ($permissions as $permission) {
            try {
                $webhookUrl = config('auth.authcentral.webhook_url') ?: env('AUTHCENTRAL_WEBHOOK_URL');
                $apiKey = config('auth.authcentral.webhook_api_key') ?: env('AUTHCENTRAL_WEBHOOK_API_KEY');

                if (! $webhookUrl || ! $apiKey) {
                    Log::warning('AuthCentral webhook not configured');

                    return;
                }

                Http::timeout(30)
                    ->withHeaders([
                        'X-API-Key' => $apiKey,
                        'Content-Type' => 'application/json',
                    ])
                    ->post($webhookUrl, [
                        'event_type' => 'role.permission.attached',
                        'app_identifier' => 'cis',
                        'timestamp' => now()->toIso8601String(),
                        'data' => [
                            'role_id' => $role->id,
                            'role_name' => $role->name,
                            'permission_id' => $permission->id,
                            'permission_name' => $permission->name,
                        ],
                    ]);

            } catch (\Exception $e) {
                Log::error('Failed to send permission assignment webhook', [
                    'role_id' => $role->id,
                    'permission_id' => $permission->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Handle role-permission detachments
     */
    public function pivotDetached($role, $relationName, $pivotIds)
    {
        if ($relationName !== 'permissions') {
            return;
        }

        // Get permission details (from detached records)
        $permissions = \Spatie\Permission\Models\Permission::whereIn('id', $pivotIds)->get();

        foreach ($permissions as $permission) {
            try {
                $webhookUrl = config('auth.authcentral.webhook_url') ?: env('AUTHCENTRAL_WEBHOOK_URL');
                $apiKey = config('auth.authcentral.webhook_api_key') ?: env('AUTHCENTRAL_WEBHOOK_API_KEY');

                if (! $webhookUrl || ! $apiKey) {
                    Log::warning('AuthCentral webhook not configured');

                    return;
                }

                Http::timeout(30)
                    ->withHeaders([
                        'X-API-Key' => $apiKey,
                        'Content-Type' => 'application/json',
                    ])
                    ->post($webhookUrl, [
                        'event_type' => 'role.permission.detached',
                        'app_identifier' => 'cis',
                        'timestamp' => now()->toIso8601String(),
                        'data' => [
                            'role_id' => $role->id,
                            'role_name' => $role->name,
                            'permission_id' => $permission->id,
                            'permission_name' => $permission->name,
                        ],
                    ]);

            } catch (\Exception $e) {
                Log::error('Failed to send permission detachment webhook', [
                    'role_id' => $role->id,
                    'permission_id' => $permission->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
