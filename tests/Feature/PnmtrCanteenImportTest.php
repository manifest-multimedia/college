<?php

namespace Tests\Feature;

use App\Models\CanteenQualification;
use App\Models\CanteenQualificationImport;
use App\Models\Student;
use App\Services\Canteen\CanteenClearanceService;
use App\Services\Canteen\CanteenPolicy;
use App\Services\Canteen\PnmtrFinanceImport;
use Database\Seeders\PnmtrCanteenSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class PnmtrCanteenImportTest extends TestCase
{
    use RefreshDatabase;

    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'qualification-test-'.uniqid();
        mkdir($this->directory);
        config(['branding.student_id.institution_prefix' => 'PNMTC/DA', 'canteen.finance_import_path' => $this->directory,
            'canteen.import_academic_year' => '2026/2027', 'canteen.import_semester' => 1]);
        app(CanteenPolicy::class)->setPeriod('2026/2027', 1);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->directory.DIRECTORY_SEPARATOR.'*') ?: [] as $file) {
            unlink($file);
        }
        rmdir($this->directory);
        parent::tearDown();
    }

    public function test_status_import_preserves_evidence_without_creating_financial_or_academic_records(): void
    {
        $path = $this->writeSheet('list.xlsm', [
            ['PNMTC/DA/RGN/25/26/001 Example First', 'QUALIFIED'],
            ['PNMTC/DA/RGN/25/26/002 Example Second', 'NOT QUALIFIED'],
        ]);
        $summary = $this->import();
        $this->assertSame([], $summary['errors']);
        $this->assertSame(2, $summary['files'][0]['qualifications_created']);
        $this->assertSame(2, Student::count());
        $this->assertSame(2, CanteenQualification::count());
        $this->assertDatabaseHas('canteen_qualification_imports', ['academic_year' => '2026/2027', 'semester' => 1, 'file_sha256' => hash_file('sha256', $path)]);
        foreach (['student_fee_bills', 'student_fee_bill_items', 'fee_payments', 'fee_types', 'fee_structures', 'academic_years'] as $table) {
            $this->assertSame(0, DB::table($table)->count(), $table.' must not be fabricated by status imports');
        }
        $service = app(CanteenClearanceService::class);
        $approved = $service->verify('PNMTC/DA/RGN/25/26/001');
        $this->assertSame('approved', $approved['decision']);
        $this->assertNull($approved['payment_percentage']);
        $this->assertSame('qualification_list', $approved['decision_context']['source']);
        $this->assertSame('denied', $service->verify('PNMTC/DA/RGN/25/26/002')['decision']);
    }

    public function test_identical_file_replay_does_not_duplicate_or_change_evidence(): void
    {
        $this->writeSheet('a.xlsx', [['PNMTC/DA/RGN/25/26/001 Example First', 'QUALIFIED']]);
        $this->import();
        $again = $this->import();
        $this->assertTrue($again['files'][0]['replayed']);
        $this->assertSame(0, $again['files'][0]['qualifications_created']);
        $this->assertSame(1, CanteenQualificationImport::count());
        $this->assertSame(1, CanteenQualification::count());
    }

    public function test_old_file_replay_does_not_undo_a_later_correction_and_revocation_does_not_restore_old_approval(): void
    {
        $old = $this->writeSheet('a.xlsx', [['PNMTC/DA/RGN/25/26/001 Example First', 'QUALIFIED']]);
        $service = new PnmtrFinanceImport;
        $service->importFile($old, '2026/2027', 1);
        $new = $this->writeSheet('b.xlsx', [['PNMTC/DA/RGN/25/26/001 Example First', 'NOT QUALIFIED']]);
        $newImport = $service->importFile($new, '2026/2027', 1);
        $service->importFile($old, '2026/2027', 1);
        $this->assertSame('denied', app(CanteenClearanceService::class)->verify('PNMTC/DA/RGN/25/26/001')['decision']);
        $this->artisan('canteen:revoke-list', ['import' => $newImport['import_id'], 'reason' => 'Correction under review'])->assertSuccessful();
        $this->assertSame('review_required', app(CanteenClearanceService::class)->verify('PNMTC/DA/RGN/25/26/001')['decision']);
        $this->assertSame(2, CanteenQualification::count());
    }

    public function test_invalid_status_rejects_the_whole_file_without_partial_students(): void
    {
        $this->writeSheet('a.xlsx', [['PNMTC/DA/RGN/25/26/001 Example First', 'QUALIFIED'], ['PNMTC/DA/RGN/25/26/002 Example Second', 'UNKNOWN']]);
        $this->assertNotEmpty($this->import()['errors']);
        $this->assertSame(0, Student::count());
        $this->assertSame(0, CanteenQualificationImport::count());
    }

    public function test_replacement_class_list_does_not_keep_approval_for_omitted_students(): void
    {
        $old = $this->writeSheet('a.xlsx', [['PNMTC/DA/RGN/25/26/001 Example First', 'QUALIFIED']]);
        (new PnmtrFinanceImport)->importFile($old, '2026/2027', 1);
        $replacement = $this->writeSheet('b.xlsx', [['PNMTC/DA/RGN/25/26/002 Example Second', 'QUALIFIED']]);
        (new PnmtrFinanceImport)->importFile($replacement, '2026/2027', 1);
        $this->assertSame('review_required', app(CanteenClearanceService::class)->verify('PNMTC/DA/RGN/25/26/001')['decision']);
        $this->assertSame('approved', app(CanteenClearanceService::class)->verify('PNMTC/DA/RGN/25/26/002')['decision']);
        $this->assertSame(2, CanteenQualification::count());
    }

    public function test_duplicate_or_conflicting_student_rows_are_rejected(): void
    {
        $this->writeSheet('a.xlsx', [['PNMTC/DA/RGN/25/26/001 Example First', 'QUALIFIED'], ['PNMTC/DA/RGN/25/26/001 Example First', 'NOT QUALIFIED']]);
        $this->assertNotEmpty($this->import()['errors']);
        $this->assertSame(0, CanteenQualification::count());
    }

    public function test_directory_conflicts_roll_back_every_file(): void
    {
        $this->writeSheet('a.xlsx', [['PNMTC/DA/RGN/25/26/001 Example First', 'QUALIFIED']]);
        $this->writeSheet('b.xlsx', [['PNMTC/DA/RGN/25/26/001 Example First', 'NOT QUALIFIED']]);
        $result = $this->import();
        $this->assertNotEmpty($result['errors']);
        $this->assertSame([], $result['files']);
        $this->assertSame(0, Student::count());
        $this->assertSame(0, CanteenQualificationImport::count());
    }

    public function test_invalid_later_file_rolls_back_earlier_valid_files(): void
    {
        $this->writeSheet('a.xlsx', [['PNMTC/DA/RGN/25/26/001 Example First', 'QUALIFIED']]);
        $this->writeSheet('b.xlsx', [['PNMTC/DA/RGN/25/26/002 Example Second', 'UNKNOWN']]);
        $this->assertNotEmpty($this->import()['errors']);
        $this->assertSame(0, Student::count());
        $this->assertSame(0, CanteenQualificationImport::count());
    }

    public function test_import_requires_explicit_scope_and_checks_institution_prefix(): void
    {
        $this->writeSheet('a.xlsx', [['OTHER/DA/RGN/25/26/001 Example First', 'QUALIFIED']]);
        $this->assertNotEmpty($this->import()['errors']);
        $this->assertSame(0, Student::count());
        $this->expectException(\InvalidArgumentException::class);
        (new PnmtrFinanceImport)->importFromDirectory($this->directory);
    }

    public function test_generic_import_supports_other_institutions_without_pnmtc_defaults(): void
    {
        config(['branding.student_id.institution_prefix' => 'OTHER/CAMPUS']);
        $this->writeSheet('a.xlsx', [['OTHER/CAMPUS/RGN/25/26/001 Example First', 'QUALIFIED']]);
        $this->assertSame([], $this->import()['errors']);
        $this->assertDatabaseHas('canteen_qualification_imports', ['institution_prefix' => 'OTHER/CAMPUS']);
    }

    public function test_command_and_compatibility_seeder_require_period_and_use_saved_prefix(): void
    {
        $this->writeSheet('a.xlsx', [['PNMTC/DA/RGN/25/26/001 Example First', 'QUALIFIED']]);
        $this->artisan('canteen:import-qualifications', ['path' => $this->directory])->assertFailed();
        $this->artisan('canteen:import-pnmtr', ['path' => $this->directory, '--academic-year' => '2026/2027', '--semester' => '1'])->assertSuccessful();
        $this->seed(PnmtrCanteenSeeder::class);
        $this->assertSame(1, CanteenQualification::count());
        $this->assertSame(0, DB::table('student_fee_bills')->count());
    }

    public function test_index_parsing_does_not_treat_intake_year_as_clearance_period(): void
    {
        $parsed = (new PnmtrFinanceImport)->parseIndexAndName('PNMTC/DA/RM/24/25/001 Example First');
        $this->assertSame('2024/2025', $parsed['academic_year']);
        $this->assertNull((new PnmtrFinanceImport)->parseIndexAndName('PNMTC/DA/RM/2024/2025/001 Example First'));
    }

    private function import(): array
    {
        return (new PnmtrFinanceImport)->importFromDirectory($this->directory, '2026/2027', 1);
    }

    private function writeSheet(string $file, array $rows): string
    {
        $book = new Spreadsheet;
        $sheet = $book->getActiveSheet();
        $sheet->setCellValue('C1', 'RGN10');
        $sheet->setCellValue('D1', 'STATUS');
        foreach ($rows as $i => [$name, $status]) {
            $sheet->setCellValue('C'.($i + 2), $name);
            $sheet->setCellValue('D'.($i + 2), $status);
        }
        $path = $this->directory.DIRECTORY_SEPARATOR.$file;
        (new Xlsx($book))->save($path);
        $book->disconnectWorksheets();

        return $path;
    }
}
