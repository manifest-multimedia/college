<?php

namespace Database\Seeders;

use App\Services\Canteen\CanteenQualificationImportService;
use App\Services\StudentIdGenerationService;
use Illuminate\Database\Seeder;
use RuntimeException;

/** Compatibility seeder for PNMTC. New installations should use the generic import command. */
class PnmtrCanteenSeeder extends Seeder
{
    public function run(StudentIdGenerationService $studentIds): void
    {
        $prefix = $studentIds->configuredInstitutionPrefix();
        if (strtoupper(explode('/', $prefix ?? '')[0]) !== 'PNMTC') {
            return;
        }
        $year = config('canteen.import_academic_year');
        $semester = config('canteen.import_semester');
        $result = app(CanteenQualificationImportService::class)->importFromDirectory(
            config('canteen.finance_import_path'), $year, $semester === null ? null : (int) $semester);
        foreach ($result['errors'] as $error) {
            $this->command?->error($error);
        }
        if ($result['errors'] !== [] || $result['files'] === []) {
            throw new RuntimeException('Qualification import did not complete.');
        }
        $this->command?->info('Imported qualification evidence only; no financial records were created.');
    }
}
