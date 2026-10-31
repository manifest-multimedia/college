<?php

namespace App\Console\Commands;

use App\Models\CanteenQualificationImport;
use App\Services\StudentIdGenerationService;
use Illuminate\Console\Command;

class RevokeCanteenList extends Command
{
    protected $signature = 'canteen:revoke-list {import} {reason}';

    protected $description = 'Revoke an imported qualification list while preserving its history';

    public function handle(StudentIdGenerationService $studentIds): int
    {
        $reason = trim((string) $this->argument('reason'));
        $batch = CanteenQualificationImport::query()->where('institution_prefix', strtoupper($studentIds->configuredInstitutionPrefix() ?? ''))->find($this->argument('import'));
        if (! $batch || $reason === '') {
            $this->error('A matching institution import and a revocation reason are required.');

            return self::FAILURE;
        }
        if ($batch->revoked_at === null) {
            $batch->update(['revoked_at' => now(), 'revocation_reason' => $reason]);
        }
        $this->info('List revoked. It will not reactivate older qualification statuses.');

        return self::SUCCESS;
    }
}
