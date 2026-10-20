<?php

namespace App\Livewire;

use App\Imports\StudentDateFormatInspector;
use App\Imports\StudentImporter;
use App\Models\AcademicYear;
use App\Models\Cohort;
use App\Models\CollegeClass;
use App\Services\StudentImportDateParser;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

class StudentImport extends Component
{
    use WithFileUploads;

    public $file;

    public $programId;

    public $cohortId;

    public $academicYearId;

    /**
     * Every imported student must have a portal account in order to access
     * examinations and other student services.
     */
    public $syncUsers = true;

    public $columnMapping = [];

    /** @var array<int, array{key: string, label: string, sample: string|null}> */
    public $uploadedHeaders = [];

    /**
     * Some institution spreadsheets include a title above the headings. The
     * administrator may select that heading row after upload.
     */
    public $headingRow = 1;

    public $dateFormat = 'auto';

    public $dateFormatAnalysis = [];

    public $importResults = null;

    public $isProcessing = false;

    // Available fields in the student model
    public $availableFields = [
        'student_id' => 'Student ID',
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'other_name' => 'Other Name',
        'gender' => 'Gender',
        'date_of_birth' => 'Date of Birth',
        'nationality' => 'Nationality',
        'country_of_residence' => 'Country of Residence',
        'home_region' => 'Home Region',
        'home_town' => 'Home Town',
        'religion' => 'Religion',
        'mobile_number' => 'Mobile Number',
        'email' => 'Email',
        'gps_address' => 'GPS Address',
        'postal_address' => 'Postal Address',
        'residential_address' => 'Residential Address',
        'marital_status' => 'Marital Status',
        'status' => 'Status',
    ];

    // Default column mapping
    public $defaultColumnMapping = [
        'student_id' => 'student_id',
        'first_name' => 'first_name',
        'last_name' => 'last_name',
        'other_names' => 'other_name',
        'gender' => 'gender',
        'date_of_birth' => 'date_of_birth',
        'nationality' => 'nationality',
        'country' => 'country_of_residence',
        'home_region' => 'home_region',
        'home_town' => 'home_town',
        'religion' => 'religion',
        'mobile_number' => 'mobile_number',
        'email' => 'email',
        'gps_address' => 'gps_address',
        'postal_address' => 'postal_address',
        'residential_address' => 'residential_address',
        'marital_status' => 'marital_status',
    ];

    protected $rules = [
        'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
        'programId' => 'required|exists:college_classes,id',
        'cohortId' => 'required|exists:cohorts,id',
        'academicYearId' => 'nullable|exists:academic_years,id',
        'dateFormat' => 'required|in:auto,dmy,mdy',
    ];

    protected $messages = [
        'file.required' => 'Please select a file to import',
        'file.file' => 'The uploaded file is invalid',
        'file.mimes' => 'The file must be an Excel or CSV file',
        'file.max' => 'The file size must not exceed 10MB',
        'programId.required' => 'Please select a program',
        'cohortId.required' => 'Please select a cohort',
        'academicYearId.exists' => 'Please select a valid academic year',
    ];

    public function mount()
    {
        $this->columnMapping = $this->defaultColumnMapping;

        // Set current academic year as default
        $currentAcademicYear = AcademicYear::where('is_current', true)->first();
        if ($currentAcademicYear) {
            $this->academicYearId = $currentAcademicYear->id;
        }
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    /**
     * Inspect only the first few rows of the uploaded workbook so that the
     * mapping UI reflects the institution's actual column labels.
     */
    public function updatedFile(): void
    {
        $this->uploadedHeaders = [];
        $this->columnMapping = [];
        $this->dateFormat = 'auto';
        $this->dateFormatAnalysis = [];
        $this->resetValidation('file');
        $this->resetValidation('dateFormat');

        if (! $this->file) {
            return;
        }

        $this->validateOnly('file');
        $this->detectUploadedHeaders();
    }

    public function updatedHeadingRow(): void
    {
        if ($this->file) {
            $this->detectUploadedHeaders();
        }
    }

    public function updatedColumnMapping(): void
    {
        $this->detectDateFormat();
    }

    public function updatedDateFormat(): void
    {
        $this->resetValidation('dateFormat');
    }

    protected function detectDateFormat(): void
    {
        $this->dateFormatAnalysis = [];
        $this->resetValidation('dateFormat');
        $parser = new StudentImportDateParser;
        $column = array_search('date_of_birth', $this->columnMapping, true);
        if (! $this->file || $column === false) {
            $this->dateFormatAnalysis = $parser->inspect([]);

            return;
        }
        try {
            $inspector = new StudentDateFormatInspector($column, (int) $this->headingRow);
            Excel::import($inspector, $this->file);
            $this->dateFormatAnalysis = $inspector->analysis;
        } catch (\Throwable $exception) {
            Log::warning('Student import date inspection failed', ['error' => $exception->getMessage()]);
            $this->addError('dateFormat', 'The date column could not be checked. Re-upload the file and confirm its mapping.');
        }
    }

    protected function detectUploadedHeaders(): void
    {
        try {
            $headingRow = min(10, max(1, (int) $this->headingRow));
            $this->headingRow = $headingRow;

            $reader = IOFactory::createReaderForFile($this->file->getRealPath());
            $reader->setReadDataOnly(true);
            $reader->setReadFilter(new class($headingRow) implements IReadFilter
            {
                public function __construct(private readonly int $lastRow) {}

                public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
                {
                    return $row <= $this->lastRow + 3;
                }
            });

            $spreadsheet = $reader->load($this->file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $lastColumn = $worksheet->getHighestDataColumn($headingRow);
            $headers = $worksheet->rangeToArray(
                "A{$headingRow}:{$lastColumn}{$headingRow}",
                null,
                false,
                false,
                false
            )[0] ?? [];

            $samples = $worksheet->rangeToArray(
                'A'.($headingRow + 1).":{$lastColumn}".($headingRow + 3),
                null,
                false,
                false,
                false
            );

            $seen = [];
            $detectedHeaders = [];
            $mapping = [];

            foreach ($headers as $index => $header) {
                $label = trim((string) $header);
                if ($label === '') {
                    continue;
                }

                $key = HeadingRowFormatter::format([$label])[0];
                if ($key === '' || isset($seen[$key])) {
                    throw new \RuntimeException("Duplicate or unsupported column heading: {$label}");
                }

                $seen[$key] = true;
                $sample = null;
                foreach ($samples as $sampleRow) {
                    $value = $sampleRow[$index] ?? null;
                    if ($value !== null && $value !== '') {
                        $sample = trim((string) $value);
                        break;
                    }
                }

                $detectedHeaders[] = [
                    'key' => $key,
                    'label' => $label,
                    'sample' => $sample,
                ];
                $mapping[$key] = $this->suggestFieldForHeading($key);
            }

            if (empty($detectedHeaders)) {
                throw new \RuntimeException("No headings were found on row {$headingRow}.");
            }

            $this->uploadedHeaders = $detectedHeaders;
            $this->columnMapping = $mapping;
            $spreadsheet->disconnectWorksheets();
            $this->detectDateFormat();
        } catch (\Throwable $exception) {
            Log::warning('Student import header detection failed', ['error' => $exception->getMessage()]);
            $this->uploadedHeaders = [];
            $this->columnMapping = [];
            $this->addError('file', 'The file headings could not be read: '.$exception->getMessage());
        }
    }

    protected function suggestFieldForHeading(string $heading): string
    {
        $aliases = [
            'index_number' => 'student_id',
            'index_no' => 'student_id',
            'student_number' => 'student_id',
            'student_no' => 'student_id',
            'id_number' => 'student_id',
            'firstname' => 'first_name',
            'given_name' => 'first_name',
            'surname' => 'last_name',
            'family_name' => 'last_name',
            'other_names' => 'other_name',
            'middle_name' => 'other_name',
            'sex' => 'gender',
            'dob' => 'date_of_birth',
            'birth_date' => 'date_of_birth',
            'phone' => 'mobile_number',
            'telephone' => 'mobile_number',
            'contact' => 'mobile_number',
            'contact_number' => 'mobile_number',
            'phone_number' => 'mobile_number',
            'mobile' => 'mobile_number',
            'email_address' => 'email',
            'e_mail' => 'email',
            'country_of_residence' => 'country_of_residence',
        ];

        return $this->defaultColumnMapping[$heading]
            ?? $aliases[$heading]
            ?? (array_key_exists($heading, $this->availableFields) ? $heading : '');
    }

    protected function validateColumnMapping(): void
    {
        if (empty($this->uploadedHeaders)) {
            throw ValidationException::withMessages([
                'file' => 'Upload a readable spreadsheet before importing students.',
            ]);
        }

        $mappedFields = array_values(array_filter($this->columnMapping));
        $errors = [];

        foreach (['first_name', 'last_name', 'email'] as $requiredField) {
            if (! in_array($requiredField, $mappedFields, true)) {
                $errors[] = $this->availableFields[$requiredField].' must be mapped.';
            }
        }

        if (count($mappedFields) !== count(array_unique($mappedFields))) {
            $errors[] = 'Each student field can be mapped to only one spreadsheet column.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(['columnMapping' => $errors]);
        }
    }

    protected function resolveDateFormatForImport(): string
    {
        // Never trust a preview retained in Livewire state: re-read the complete
        // mapped column before any ID generation, student or account writes.
        $this->detectDateFormat();
        if ($this->dateFormatAnalysis === []) {
            throw ValidationException::withMessages(['dateFormat' => 'The date column could not be checked. Re-upload the file and confirm its mapping.']);
        }
        try {
            return (new StudentImportDateParser)->resolve($this->dateFormatAnalysis, $this->dateFormat);
        } catch (\InvalidArgumentException $exception) {
            throw ValidationException::withMessages(['dateFormat' => $exception->getMessage()]);
        }
    }

    public function import()
    {
        $this->validate();
        $this->validateColumnMapping();
        $resolvedDateFormat = $this->resolveDateFormatForImport();

        try {
            $this->isProcessing = true;

            // Increase execution time for large imports
            set_time_limit(3000); // 50 minutes
            ini_set('max_execution_time', 3000);

            // Import students from the Excel file
            $importer = new StudentImporter(
                $this->programId,
                $this->cohortId,
                $this->columnMapping,
                $this->academicYearId,
                true,
                $this->headingRow,
                $resolvedDateFormat
            );

            Excel::import($importer, $this->file);

            // Get import results
            $this->importResults = $importer->getImportStats();

            $hasIssues = $this->importResults['skipped'] > 0 || $this->importResults['failed'] > 0;
            $message = ($hasIssues ? 'Student import completed with issues: ' : 'Students imported successfully: ').
                $this->importResults['created'].' created, '.
                $this->importResults['updated'].' updated';

            if ($this->importResults['skipped'] > 0) {
                $message .= ', '.$this->importResults['skipped'].' skipped due to validation errors';
            }

            if ($this->importResults['failed'] > 0) {
                $message .= ', '.$this->importResults['failed'].' failed';
            }

            if (isset($this->importResults['ids_generated']) && $this->importResults['ids_generated'] > 0) {
                $message .= ', '.$this->importResults['ids_generated'].' student IDs generated automatically';
            }

            if (isset($this->importResults['accounts_created'])) {
                $message .= ', '.$this->importResults['accounts_created'].' student accounts created';
            }

            session()->flash($hasIssues ? 'warning' : 'success', $message.'.');

            Log::channel('daily')->info('Student import completed', $this->importResults);

        } catch (\Exception $e) {
            Log::error('Student import error: '.$e->getMessage());
            session()->flash('error', 'Error importing students: '.$e->getMessage());
        } finally {
            $this->isProcessing = false;
        }
    }

    public function resetForm()
    {
        $this->reset(['file', 'importResults', 'academicYearId', 'uploadedHeaders', 'headingRow', 'dateFormat', 'dateFormatAnalysis']);
        $this->columnMapping = $this->defaultColumnMapping;

        // Reset to current academic year
        $currentAcademicYear = AcademicYear::where('is_current', true)->first();
        if ($currentAcademicYear) {
            $this->academicYearId = $currentAcademicYear->id;
        }
    }

    public function render()
    {
        return view('livewire.student-import', [
            'programs' => CollegeClass::orderBy('name')->get(),
            'cohorts' => Cohort::orderBy('name')->get(),
            'academicYears' => AcademicYear::orderBy('start_date', 'desc')->get(),
        ]);
    }
}
