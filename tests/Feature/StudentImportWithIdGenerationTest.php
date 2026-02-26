<?php

namespace Tests\Feature;

use App\Imports\StudentImporter;
use App\Models\AcademicYear;
use App\Models\Cohort;
use App\Models\CollegeClass;
use App\Models\Student;
use App\Services\StudentIdGenerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StudentImportWithIdGenerationTest extends TestCase
{
    use RefreshDatabase;

    protected $collegeClass;

    protected $cohort;

    protected $academicYear;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure we use structured format for these tests
        config(['branding.student_id.format' => 'structured']);

        // Create test data manually
        $this->collegeClass = CollegeClass::create([
            'name' => 'Registered Midwifery',
            'slug' => 'registered-midwifery',
        ]);

        $this->cohort = Cohort::create([
            'name' => '2024 Cohort',
            'slug' => '2024-cohort',
        ]);

        $this->academicYear = AcademicYear::create([
            'name' => '2024/2025',
            'slug' => '2024-2025',
            'start_date' => '2024-09-01',
            'end_date' => '2025-08-31',
            'is_current' => true,
        ]);

        // Set up institution prefix in settings table
        if (! DB::table('settings')->where('key', 'school_name_prefix')->exists()) {
            DB::table('settings')->insert([
                'key' => 'school_name_prefix',
                'value' => 'PNMTC/DA',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /** @test */
    public function it_generates_student_ids_for_students_without_ids_during_import()
    {
        // Prepare test data - students without student_id
        $testData = new Collection([
            [
                'first_name' => 'Alice',
                'last_name' => 'Johnson',
                'email' => 'alice.johnson@example.com',
                'gender' => 'Female',
                'date_of_birth' => '2000-01-15',
            ],
            [
                'first_name' => 'Bob',
                'last_name' => 'Smith',
                'email' => 'bob.smith@example.com',
                'gender' => 'Male',
                'date_of_birth' => '1999-05-20',
            ],
            [
                'student_id' => 'EXISTING/ID/001', // This one has an ID already
                'first_name' => 'Charlie',
                'last_name' => 'Brown',
                'email' => 'charlie.brown@example.com',
                'gender' => 'Male',
                'date_of_birth' => '2001-03-10',
            ],
        ]);

        // Create importer and process the collection
        $importer = new StudentImporter($this->collegeClass->id, $this->cohort->id, [], $this->academicYear->id);
        $importer->collection($testData);

        // Get import stats
        $stats = $importer->getImportStats();

        // Assertions
        $this->assertEquals(3, $stats['total'], 'Should process 3 total records');
        $this->assertEquals(3, $stats['created'], 'Should create 3 students');
        $this->assertEquals(0, $stats['failed'], 'Should have no failures');
        $this->assertEquals(2, $stats['ids_generated'], 'Should generate 2 student IDs');

        // Verify students were created
        $students = Student::all();
        $this->assertCount(3, $students);

        // Verify ID generation for students without IDs
        $alice = Student::where('email', 'alice.johnson@example.com')->first();
        $bob = Student::where('email', 'bob.smith@example.com')->first();
        $charlie = Student::where('email', 'charlie.brown@example.com')->first();

        $this->assertNotNull($alice);
        $this->assertNotNull($bob);
        $this->assertNotNull($charlie);

        // Alice and Bob should have generated IDs following the active institution prefix and a valid format
        $prefix = config('branding.student_id.institution_prefix', 'PNMTC/DA');
        $escapedPrefix = preg_quote($prefix, '/');
        $pattern = '/^'.$escapedPrefix.'\/[A-Z]+\/\d{2}\/\d{2}\/\d{3}$/';

        $this->assertMatchesRegularExpression($pattern, $alice->student_id);
        $this->assertMatchesRegularExpression($pattern, $bob->student_id);

        // Charlie should keep their existing ID
        $this->assertEquals('EXISTING/ID/001', $charlie->student_id);

        // Verify alphabetical ordering - Johnson comes before Smith
        $aliceSequence = (int) substr($alice->student_id, -3);
        $bobSequence = (int) substr($bob->student_id, -3);
        $this->assertLessThan($bobSequence, $aliceSequence, 'Johnson should come before Smith alphabetically');
    }

    /** @test */
    public function it_handles_students_with_existing_ids_correctly()
    {
        $testData = new Collection([
            [
                'student_id' => 'PNMTC/DA/RM/24/25/001',
                'first_name' => 'David',
                'last_name' => 'Wilson',
                'email' => 'david.wilson@example.com',
            ],
        ]);

        $importer = new StudentImporter($this->collegeClass->id, $this->cohort->id, [], $this->academicYear->id);
        $importer->collection($testData);

        $stats = $importer->getImportStats();

        $this->assertEquals(1, $stats['total']);
        $this->assertEquals(1, $stats['created']);
        $this->assertEquals(0, $stats['ids_generated'], 'Should not generate ID for student with existing ID');

        $student = Student::where('email', 'david.wilson@example.com')->first();
        $this->assertEquals('PNMTC/DA/RM/24/25/001', $student->student_id);
    }

    /** @test */
    public function it_skips_id_generation_when_required_fields_missing()
    {
        $testData = new Collection([
            [
                // Missing last_name
                'first_name' => 'Emma',
                'email' => 'emma@example.com',
            ],
            [
                // Missing first_name
                'last_name' => 'Davis',
                'email' => 'davis@example.com',
            ],
        ]);

        $importer = new StudentImporter($this->collegeClass->id, $this->cohort->id, [], $this->academicYear->id);
        $importer->collection($testData);

        $stats = $importer->getImportStats();

        $this->assertEquals(2, $stats['total']);
        // Current importer fully skips invalid rows (missing required names)
        $this->assertEquals(0, $stats['ids_generated'], 'Should not generate IDs when names are missing');
        $this->assertEquals(0, $stats['created'], 'Invalid rows should not create students');
        $this->assertEquals(2, $stats['skipped'], 'Both rows should be skipped due to validation');
    }

    /** @test */
    public function it_uses_student_id_generation_service_correctly()
    {
        // Verify that the service itself works
        $service = new StudentIdGenerationService;

        $studentId = $service->generateStudentId(
            'Test',
            'Student',
            $this->collegeClass->id,
            $this->academicYear->id
        );

        // With structured format and prefix configured, ID should be valid per service rules
        $this->assertTrue($service->isValidStudentIdFormat($studentId));
    }
}
