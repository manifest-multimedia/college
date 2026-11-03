<?php

namespace App\Console\Commands;

use App\Services\Canteen\CanteenQualificationImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class ImportPnmtrCanteen extends Command
{
    protected $signature = 'canteen:import-qualifications {path? : Directory of status lists (.xlsm/.xlsx)} {--academic-year= : Explicit clearance year, e.g. 2026/2027} {--semester= : Explicit semester sequence, e.g. 1} {--dry-run : Validate and preview without committing imported rows}';

    protected $aliases = ['canteen:import-pnmtr'];

    protected $description = 'Import institution qualification statuses without creating bills or payments';

    public function handle(CanteenQualificationImportService $importer): int
    {
        $semester = $this->option('semester');
        if (! ctype_digit((string) $semester)) {
            $this->error('An explicit --academic-year and numeric --semester are required.');

            return self::FAILURE;
        }
        $preview = (bool) $this->option('dry-run');
        if ($preview) {
            DB::beginTransaction();
        }
        try {
            $result = $importer->importFromDirectory($this->argument('path') ?: config('canteen.finance_import_path'), $this->option('academic-year'), (int) $semester);
        } catch (Throwable $error) {
            $this->error($error->getMessage());

            return self::FAILURE;
        } finally {
            if ($preview) {
                DB::rollBack();
            }
        }
        if ($preview) {
            $this->info('DRY RUN: preview only. No imported rows were committed.');
        }
        $this->table(['File', 'Import ID', 'Rows', 'Qualified', 'Not qualified', 'New statuses', 'Replayed'], array_map(fn ($file) => [
            $file['file'], $file['import_id'], $file['rows'], $file['qualified'], $file['not_qualified'], $file['qualifications_created'], $file['replayed'] ? 'yes' : 'no',
        ], $result['files']));
        foreach ($result['errors'] as $error) {
            $this->error($error);
        }

        return $result['files'] !== [] && $result['errors'] === [] ? self::SUCCESS : self::FAILURE;
    }
}
