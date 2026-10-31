<?php

namespace App\Console\Commands;

use App\Services\Canteen\CanteenQualificationImportService;
use Illuminate\Console\Command;
use Throwable;

class ImportPnmtrCanteen extends Command
{
    protected $signature = 'canteen:import-qualifications {path? : Directory of status lists (.xlsm/.xlsx)} {--academic-year= : Explicit clearance year, e.g. 2026/2027} {--semester= : Explicit semester sequence, e.g. 1}';

    protected $aliases = ['canteen:import-pnmtr'];

    protected $description = 'Import institution qualification statuses without creating bills or payments';

    public function handle(CanteenQualificationImportService $importer): int
    {
        $semester = $this->option('semester');
        if (! ctype_digit((string) $semester)) {
            $this->error('An explicit --academic-year and numeric --semester are required.');

            return self::FAILURE;
        }
        try {
            $result = $importer->importFromDirectory($this->argument('path') ?: config('canteen.finance_import_path'), $this->option('academic-year'), (int) $semester);
        } catch (Throwable $error) {
            $this->error($error->getMessage());

            return self::FAILURE;
        }
        $this->table(['File', 'Rows', 'Qualified', 'Not qualified', 'New statuses', 'Replayed'], array_map(fn ($file) => [
            $file['file'], $file['rows'], $file['qualified'], $file['not_qualified'], $file['qualifications_created'], $file['replayed'] ? 'yes' : 'no',
        ], $result['files']));
        foreach ($result['errors'] as $error) {
            $this->error($error);
        }

        return $result['files'] !== [] && $result['errors'] === [] ? self::SUCCESS : self::FAILURE;
    }
}
