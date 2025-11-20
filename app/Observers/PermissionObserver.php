<?php

namespace App\Observers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;

class PermissionObserver
{
    /**
     * Handle the Permission "created" event.
     */
    public function created(Permission $permission): void
    {
        $this->sendWebhook($permission, 'permission.created');
    }

    /**
     * Handle the Permission "updated" event.
     */
    public function updated(Permission $permission): void
    {
        $this->sendWebhook($permission, 'permission.updated');
    }

    /**
     * Handle the Permission "deleted" event.
     */
    public function deleted(Permission $permission): void
    {
        $this->sendWebhook($permission, 'permission.deleted');
    }

    /**
     * Send webhook to AuthCentral about permission changes
     */
    protected function sendWebhook(Permission $permission, string $eventType): void
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
                        'id' => $permission->id,
                        'name' => $permission->name,
                        'guard_name' => $permission->guard_name,
                        'metadata' => [
                            'roles_count' => $permission->roles()->count(),
                            'created_at' => $permission->created_at?->toIso8601String(),
                            'updated_at' => $permission->updated_at?->toIso8601String(),
                        ],
                    ],
                ]);

            if (! $response->successful()) {
                Log::error('Failed to send permission webhook to AuthCentral', [
                    'permission_id' => $permission->id,
                    'event_type' => $eventType,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Exception sending permission webhook to AuthCentral', [
                'permission_id' => $permission->id,
                'event_type' => $eventType,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
