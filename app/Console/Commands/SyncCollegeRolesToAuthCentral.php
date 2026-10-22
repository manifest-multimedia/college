<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AuthCentralSyncBridgeService;
use Illuminate\Console\Command;

class SyncCollegeRolesToAuthCentral extends Command
{
    protected $signature = 'authcentral:sync-college-roles {--dry-run : Report the College360 role sets without changing AuthCentral}';

    protected $description = 'Mirror College360 user roles to AuthCentral without importing any AuthCentral roles';

    public function handle(AuthCentralSyncBridgeService $bridge): int
    {
        if (! $bridge->isAvailable()) {
            $this->error('AuthCentral is not configured or reachable. No roles were changed.');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $processed = 0;
        $synced = 0;
        $skipped = 0;

        User::query()->with('roles')->orderBy('id')->chunkById(100, function ($users) use ($bridge, $dryRun, &$processed, &$synced, &$skipped): void {
            foreach ($users as $user) {
                $processed++;

                if ($dryRun) {
                    $this->line("{$user->id}: ".($user->getRoleNames()->implode(', ') ?: '[no College360 roles]'));
                    continue;
                }

                $result = $bridge->syncRoleAssignments($user);
                if ($result['success'] ?? false) {
                    $synced++;
                } else {
                    $skipped++;
                    $this->warn("{$user->id}: ".($result['message'] ?? 'not synchronized'));
                }
            }
        });

        if ($dryRun) {
            $this->info("Dry run complete. {$processed} College360 user role sets inspected; AuthCentral was not changed.");
        } else {
            $this->info("Role synchronization complete. Processed: {$processed}; mirrored: {$synced}; skipped: {$skipped}.");
        }

        return self::SUCCESS;
    }
}
