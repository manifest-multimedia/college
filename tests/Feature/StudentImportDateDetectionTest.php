<?php

namespace Tests\Feature;

use App\Livewire\StudentImport;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class StudentImportDateDetectionTest extends TestCase
{
    private array $files = [];

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'student_dates', 'database.connections.student_dates' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ], 'excel.transactions.handler' => 'db']);
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
    }

    protected function tearDown(): void
    {
        foreach ($this->files as $file) {
            @unlink($file);
        }
        parent::tearDown();
    }

    private function upload(array $dates, bool $csv = false): InspectableDateImportComponent
    {
        $path = tempnam(sys_get_temp_dir(), 'date-detection-');
        $this->files[] = $path;
        if ($csv) {
            $stream = fopen($path, 'w');
            fputcsv($stream, ['Register title'], escape: '');
            fputcsv($stream, ['First Name', 'Surname', 'Email Address', 'Birth Date'], escape: '');
            foreach ($dates as $index => $date) {
                fputcsv($stream, ['Example', 'Student', "student{$index}@example.com", $date], escape: '');
            }
            fclose($stream);
        } else {
            $spreadsheet = new Spreadsheet;
            $spreadsheet->getActiveSheet()->fromArray(['Register title'], null, 'A1');
            $spreadsheet->getActiveSheet()->fromArray(['First Name', 'Surname', 'Email Address', 'Birth Date'], null, 'A2');
            foreach ($dates as $index => $date) {
                $spreadsheet->getActiveSheet()->fromArray(['Example', 'Student', "student{$index}@example.com", $date], null, 'A'.($index + 3));
            }
            (new Xlsx($spreadsheet))->save($path);
            $spreadsheet->disconnectWorksheets();
        }
        $component = new InspectableDateImportComponent;
        $component->file = new UploadedFile($path, 'students.'.($csv ? 'csv' : 'xlsx'), test: true);
        $component->headingRow = 2;
        $component->inspectHeaders();

        return $component;
    }

    public function test_xlsx_full_column_detection_uses_evidence_after_the_first_chunk(): void
    {
        $component = $this->upload([...array_fill(0, 500, '5/4/2005'), '16/8/2005']);
        $this->assertSame('date_of_birth', $component->columnMapping['birth_date']);
        $this->assertSame(501, $component->dateFormatAnalysis['total']);
        $this->assertSame(1, $component->dateFormatAnalysis['dmy']);
        $this->assertSame('dmy', $component->resolveDates());
        $html = view('livewire.student-import', array_merge(get_object_vars($component), [
            'programs' => collect(), 'cohorts' => collect(), 'academicYears' => collect(), 'errors' => new ViewErrorBag,
        ]))->render();
        $this->assertStringContainsString('Detected <strong>day/month/year</strong>', $html);
        $this->assertStringContainsString('5 Apr 2005', $html);
        $this->assertStringContainsString('4 May 2005', $html);
    }

    public function test_csv_month_first_detection_is_not_forced_to_day_first(): void
    {
        $component = $this->upload(['4/5/2005', '8/16/2005'], true);
        $this->assertSame('mdy', $component->resolveDates());
    }

    public function test_ambiguous_upload_requires_choice_even_if_preview_state_is_tampered_with(): void
    {
        $component = $this->upload(['5/4/2005', '8/6/2006']);
        $this->assertSame(2, $component->dateFormatAnalysis['ambiguous']);
        $component->dateFormatAnalysis = ['dmy' => 100];
        try {
            $component->resolveDates();
            $this->fail('Import must re-read the file before trusting a detected format.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('dateFormat', $exception->errors());
        }
        $component->dateFormat = 'mdy';
        $this->assertSame('mdy', $component->resolveDates());
    }

    public function test_changing_the_date_mapping_rechecks_the_actual_selected_column(): void
    {
        $component = $this->upload(['16/8/2005']);
        $component->columnMapping['birth_date'] = '';
        $component->columnMapping['first_name'] = 'date_of_birth';
        $component->updatedColumnMapping();
        $this->assertSame(1, $component->dateFormatAnalysis['invalid']);
        $this->assertSame(0, $component->dateFormatAnalysis['dmy']);
    }

    public function test_mixed_upload_blocks_automatic_format_selection(): void
    {
        $component = $this->upload(['16/8/2005', '8/16/2005']);
        $this->expectException(ValidationException::class);
        $component->resolveDates();
    }

    public function test_excel_serials_and_iso_dates_keep_their_meaning_without_an_order_choice(): void
    {
        $component = $this->upload([38580, '2005-08-16', '16 August 2005']);
        $this->assertSame(3, $component->dateFormatAnalysis['independent']);
        $this->assertSame('auto', $component->resolveDates());
    }
}

class InspectableDateImportComponent extends StudentImport
{
    public function inspectHeaders(): void
    {
        $this->detectUploadedHeaders();
    }

    public function resolveDates(): string
    {
        return $this->resolveDateFormatForImport();
    }
}
