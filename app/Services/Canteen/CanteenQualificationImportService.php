<?php

namespace App\Services\Canteen;

use App\Models\CanteenQualification;
use App\Models\CanteenQualificationImport;
use App\Models\Cohort;
use App\Models\CollegeClass;
use App\Models\Student;
use App\Services\StudentIdGenerationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;
use Throwable;

/** Imports qualification evidence only. Never creates or edits financial records. */
class CanteenQualificationImportService
{
    public function importFromDirectory(string $directory, ?string $academicYear = null, ?int $semester = null, ?int $actor = null): array
    {
        $this->validateScope($academicYear, $semester);
        if (! is_dir($directory)) {
            throw new InvalidArgumentException('Qualification directory not found.');
        }
        $files = [];
        $errors = [];
        $paths = glob(rtrim($directory, '/\\').DIRECTORY_SEPARATOR.'{*.xlsx,*.xlsm}', GLOB_BRACE) ?: [];
        sort($paths);
        try {
            $files = DB::transaction(function () use ($paths, $academicYear, $semester, $actor) {
                $imported = [];
                $seen = [];
                $classes = [];
                foreach ($paths as $path) {
                    $file = $this->importFile($path, $academicYear, $semester, $actor);
                    if (isset($classes[$file['class']])) {
                        throw new RuntimeException('Multiple files describe the same class; import one reviewed replacement list at a time.');
                    }
                    $classes[$file['class']] = true;
                    $indexes = CanteenQualification::query()->where('import_id', $file['import_id'])->pluck('index_number');
                    foreach ($indexes as $index) {
                        if (isset($seen[$index])) {
                            throw new RuntimeException('A student appears in multiple files; review the directory before importing.');
                        }
                        $seen[$index] = true;
                    }
                    $imported[] = $file;
                }

                return $imported;
            });
        } catch (Throwable $error) {
            $errors[] = $error->getMessage();
        }

        return ['files' => $files, 'errors' => $errors];
    }

    public function importFile(string $path, ?string $academicYear = null, ?int $semester = null, ?int $actor = null): array
    {
        $prefix = $this->validateScope($academicYear, $semester);
        // The audit hash describes the exact byte snapshot parsed below.
        $bytes = file_get_contents($path);
        if ($bytes === false) {
            throw new RuntimeException('Cannot read qualification file.');
        }
        $hash = hash('sha256', $bytes);
        $snapshot = tempnam(sys_get_temp_dir(), 'canteen-list-');
        if ($snapshot === false) {
            throw new RuntimeException('Cannot prepare qualification file.');
        }
        try {
            file_put_contents($snapshot, $bytes);
            $rows = $this->parseSheet($snapshot);
        } finally {
            @unlink($snapshot);
        }
        if ($rows['records'] === [] || $rows['skipped'] !== []) {
            throw new RuntimeException('The file has missing/invalid student rows or statuses; no records were imported.');
        }
        $seen = [];
        foreach ($rows['records'] as $record) {
            if (! str_starts_with($record['index_number'], $prefix.'/')) {
                throw new RuntimeException('A student index does not match this institution prefix; no records were imported.');
            }
            if (isset($seen[$record['index_number']])) {
                throw new RuntimeException('Duplicate student index in the file; no records were imported.');
            }
            $seen[$record['index_number']] = true;
        }

        return DB::transaction(function () use ($rows, $prefix, $academicYear, $semester, $hash, $path, $actor) {
            $identity = ['institution_prefix' => $prefix, 'academic_year' => $academicYear, 'semester' => $semester, 'file_sha256' => $hash];
            $existing = CanteenQualificationImport::query()->where($identity)->first();
            $summary = ['file' => basename($path), 'class' => $rows['classLabel'], 'rows' => count($rows['records']),
                'qualified' => count(array_filter($rows['records'], fn ($row) => $row['qualified'])),
                'not_qualified' => count(array_filter($rows['records'], fn ($row) => ! $row['qualified'])),
                'students_created' => 0, 'students_existing' => 0, 'qualifications_created' => 0, 'replayed' => $existing !== null];
            if ($existing) {
                return $summary + ['import_id' => $existing->id];
            }
            $import = CanteenQualificationImport::create($identity + ['file_name' => basename($path), 'class_label' => $rows['classLabel'], 'imported_by' => $actor, 'imported_at' => now()]);
            // A class list is a snapshot. Omitted students must not retain an
            // earlier approval after a reviewed replacement is imported.
            $previous = CanteenQualificationImport::query()->where('institution_prefix', $prefix)
                ->where('academic_year', $academicYear)->where('semester', $semester)
                ->where('class_label', $rows['classLabel'])->where('id', '<>', $import->id)
                ->whereNull('revoked_at')->lockForUpdate()->get();
            foreach ($previous as $oldImport) {
                $oldImport->update(['revoked_at' => now(), 'revocation_reason' => 'Superseded by qualification import '.$import->id]);
            }
            $programmes = [];
            $cohort = Cohort::firstOrCreate(['name' => $rows['classLabel']], ['slug' => Str::slug($rows['classLabel']), 'is_active' => true, 'is_deleted' => false]);
            foreach ($rows['records'] as $record) {
                $student = Student::query()->where('student_id', $record['index_number'])->lockForUpdate()->first();
                if (! $student) {
                    $programme = $programmes[$record['programme_code']] ??= CollegeClass::firstOrCreate(
                        ['short_name' => $record['programme_code']], ['name' => $record['programme_code'], 'slug' => Str::slug($record['programme_code']), 'is_active' => true]);
                    $student = Student::create(['student_id' => $record['index_number'], 'first_name' => $record['first_name'],
                        'last_name' => $record['last_name'], 'other_name' => $record['other_name'],
                        'college_class_id' => $programme->id, 'cohort_id' => $cohort->id, 'status' => 'active']);
                    $summary['students_created']++;
                } else {
                    $summary['students_existing']++;
                }
                CanteenQualification::create(['import_id' => $import->id, 'student_id' => $student->id, 'index_number' => $record['index_number'],
                    'source_row' => $record['source_row'], 'qualified' => $record['qualified'], 'source_record' => $record]);
                $summary['qualifications_created']++;
            }

            return $summary + ['import_id' => $import->id];
        });
    }

    private function validateScope(?string $year, ?int $semester): string
    {
        $prefix = strtoupper(trim(app(StudentIdGenerationService::class)->configuredInstitutionPrefix() ?? ''));
        if ($prefix === '' || $year === null || trim($year) !== $year || $year === '' || strlen($year) > 32 || $semester === null || $semester < 1 || $semester > 99) {
            throw new InvalidArgumentException('A configured institution prefix, explicit academic year and semester are required.');
        }

        return $prefix;
    }

    private function parseSheet(string $path): array
    {
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $book = $reader->load($path);
        try {
            $sheet = $book->getSheetByName('Sheet1') ?? $book->getSheet(0);
            $classLabel = trim((string) $sheet->getCell('C1')->getValue());
            if ($classLabel === '') {
                throw new RuntimeException('Missing class label in C1.');
            }
            $records = [];
            $skipped = [];
            for ($row = 2; $row <= $sheet->getHighestDataRow(); $row++) {
                $cell = trim((string) $sheet->getCell('C'.$row)->getValue());
                $status = strtoupper(trim((string) $sheet->getCell('D'.$row)->getValue()));
                if ($cell === '' && $status === '') {
                    continue;
                }
                $parsed = $this->parseIndexAndName($cell);
                if ($parsed === null || ! in_array($status, ['QUALIFIED', 'NOT QUALIFIED'], true)) {
                    $skipped[] = $row;

                    continue;
                }
                $records[] = $parsed + ['source_row' => $row, 'qualified' => $status === 'QUALIFIED', 'source_status' => $status, 'source_text' => $cell];
            }

            return compact('classLabel', 'records', 'skipped');
        } finally {
            $book->disconnectWorksheets();
        }
    }

    public function parseIndexAndName(string $cell): ?array
    {
        if (! preg_match('~^([A-Z0-9-]+(?:/[A-Z0-9-]+)*)/([A-Z]{2,8})/(\d{2})/(\d{2})/(\d{1,6})\s+(.+)$~i', trim($cell), $match)) {
            return null;
        }
        $tokens = preg_split('/\s+/', trim($match[6]), -1, PREG_SPLIT_NO_EMPTY);
        if (! $tokens) {
            return null;
        }
        $surname = array_shift($tokens);
        $firstName = array_shift($tokens);

        return ['index_number' => strtoupper($match[1].'/'.$match[2].'/'.$match[3].'/'.$match[4].'/'.$match[5]),
            'programme_code' => strtoupper($match[2]), 'academic_year' => '20'.$match[3].'/20'.$match[4], 'intake_year' => 2000 + (int) $match[3],
            'first_name' => $firstName, 'last_name' => $surname, 'other_name' => $tokens ? implode(' ', $tokens) : null];
    }
}
