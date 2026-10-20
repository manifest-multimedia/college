<?php

namespace Tests\Feature;

use App\Exports\StudentImportTemplateExport;
use App\Imports\StudentImporter;
use App\Livewire\StudentImport;
use App\Models\AcademicYear;
use App\Models\Cohort;
use App\Models\CollegeClass;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentImportFlexibleMappingTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_an_arbitrary_spreadsheet_mapping_without_the_template_headings(): void
    {
        $program = CollegeClass::create(['name' => 'Registered Nursing', 'slug' => 'registered-nursing']);
        $cohort = Cohort::create(['name' => '2026 Cohort', 'slug' => '2026-cohort']);
        $academicYear = AcademicYear::create([
            'name' => '2026/2027',
            'slug' => '2026-2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-08-31',
            'is_current' => true,
        ]);
        Role::create(['name' => 'Student', 'guard_name' => 'web']);

        $importer = new StudentImporter($program->id, $cohort->id, [
            'index_number' => 'student_id',
            'given_name' => 'first_name',
            'surname' => 'last_name',
            'email_address' => 'email',
            'phone' => 'mobile_number',
        ], $academicYear->id);

        $importer->collection(new Collection([[
            'index_number' => 'RN/26/001',
            'given_name' => 'Ama',
            'surname' => 'Mensah',
            'email_address' => ' ama.mensah@example.edu.gh ',
            'phone' => '0244000000',
        ]]));

        $student = Student::where('email', 'ama.mensah@example.edu.gh')->firstOrFail();

        $this->assertSame('RN/26/001', $student->student_id);
        $this->assertSame('Ama', $student->first_name);
        $this->assertSame('Mensah', $student->last_name);
        $this->assertSame('0244000000', $student->mobile_number);
        $this->assertSame($program->id, $student->college_class_id);
        $this->assertSame($cohort->id, $student->cohort_id);
    }

    public function test_the_recommended_template_keeps_identifier_columns_as_text(): void
    {
        $template = new StudentImportTemplateExport;

        $this->assertSame('Student ID', $template->headings()[0]);
        $this->assertSame('Mobile Number', $template->headings()[11]);
        $this->assertSame('@', $template->columnFormats()['A']);
        $this->assertSame('@', $template->columnFormats()['L']);
    }

    public function test_it_detects_uploaded_headings_and_suggests_flexible_mappings(): void
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(['PNMTC Student Register'], null, 'A1');
        $sheet->fromArray(['Index Number', 'Given Name', 'Surname', 'Email Address', 'Phone'], null, 'A2');
        $sheet->fromArray(['RN/26/002', 'Esi', 'Owusu', 'esi.owusu@example.edu.gh', '0244111111'], null, 'A3');

        $filePath = tempnam(sys_get_temp_dir(), 'student-import-');
        (new Xlsx($spreadsheet))->save($filePath);
        $upload = UploadedFile::fake()->createWithContent('students.xlsx', file_get_contents($filePath));
        @unlink($filePath);

        Livewire::test(StudentImport::class)
            ->set('file', $upload)
            ->set('headingRow', 2)
            ->assertHasNoErrors('file')
            ->assertSet('columnMapping.index_number', 'student_id')
            ->assertSet('columnMapping.given_name', 'first_name')
            ->assertSet('columnMapping.surname', 'last_name')
            ->assertSet('columnMapping.email_address', 'email')
            ->assertSet('columnMapping.phone', 'mobile_number');
    }
}
