<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Services\StudentIdGenerationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StudentCustomIdGenerationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'custom_ids', 'database.connections.custom_ids' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Schema::create('students', function (Blueprint $table) {
            $table->increments('id');
            $table->string('student_id')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->unsignedInteger('college_class_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });
        Schema::create('college_classes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('short_name');
        });
        (require database_path('migrations/2024_10_25_122821_create_academic_years_table.php'))->up();
        DB::table('college_classes')->insert([
            ['id' => 1, 'name' => 'Dental Nursing', 'short_name' => 'DN'],
            ['id' => 2, 'name' => 'Midwifery', 'short_name' => 'RM'],
        ]);
        foreach ([1 => 2026, 2 => 2027] as $id => $year) {
            DB::table('academic_years')->insert([
                'id' => $id, 'name' => $year.'-'.($year + 1), 'slug' => (string) $year,
                'start_date' => $year.'-09-01', 'end_date' => ($year + 1).'-08-31', 'is_current' => $id === 1,
            ]);
        }
        config([
            'branding.student_id.format' => 'custom',
            'branding.student_id.custom_pattern' => '{PROGRAM}/{YEAR_SIMPLE}/{SEQUENCE_4}',
            'branding.student_id.institution_prefix' => 'MHTIDNS',
            'branding.student_id.institution_simple' => 'MHTIDNS',
            'branding.student_id.enable_alphabetical_ordering' => true,
            'branding.student_id.sequence_start' => 1,
        ]);
    }

    private function generate(string $first = 'Example', string $last = 'Student', int $program = 1, int $year = 1): string
    {
        return app(StudentIdGenerationService::class)->generateStudentId($first, $last, $program, $year);
    }

    public function test_dnsa_batch_keeps_the_configured_pattern_without_fallbacks(): void
    {
        foreach (range(1, 20) as $number) {
            $id = $this->generate('Example', 'Student'.str_pad($number, 2, '0', STR_PAD_LEFT));
            $this->assertSame('DN/26/'.str_pad($number, 4, '0', STR_PAD_LEFT), $id);
            Student::create(['student_id' => $id, 'first_name' => 'Example', 'last_name' => 'Student'.str_pad($number, 2, '0', STR_PAD_LEFT)]);
        }
        $this->assertSame(20, Student::count());
    }

    public function test_existing_first_id_is_preserved_when_new_surname_sorts_before_it(): void
    {
        Student::create(['student_id' => 'DN/26/0001', 'first_name' => 'Example', 'last_name' => 'Quainoo']);
        $this->assertSame('DN/26/0002', $this->generate('Example', 'Amonu'));
        $this->assertSame('DN/26/0001', Student::firstOrFail()->student_id);
    }

    public function test_program_and_year_sequences_are_scoped_to_the_rendered_pattern(): void
    {
        Student::create(['student_id' => 'DN/26/0001', 'first_name' => 'Example', 'last_name' => 'Student']);
        $this->assertSame('RM/26/0001', $this->generate(program: 2));
        $this->assertSame('DN/27/0001', $this->generate(year: 2));
    }

    public function test_non_alphabetical_custom_ids_continue_after_the_highest_sequence_and_ignore_other_formats(): void
    {
        config(['branding.student_id.enable_alphabetical_ordering' => false]);
        Student::create(['student_id' => 'DN/26/0010', 'first_name' => 'Example', 'last_name' => 'Student']);
        Student::create(['student_id' => 'MHTIDNS20260855', 'first_name' => 'Example', 'last_name' => 'Fallback']);
        $this->assertSame('DN/26/0011', $this->generate());
    }

    public function test_three_digit_sequences_and_literal_pattern_characters_are_supported(): void
    {
        config(['branding.student_id.custom_pattern' => '{INSTITUTION}/({PROGRAM})/{YEAR_FULL}/{SEQUENCE_3}']);
        Student::create(['student_id' => 'MHTIDNS/(DN)/26/27/001', 'first_name' => 'Example', 'last_name' => 'Student']);
        $this->assertSame('MHTIDNS/(DN)/26/27/002', $this->generate());
    }

    public function test_configured_start_and_sequence_overflow_are_supported(): void
    {
        config(['branding.student_id.sequence_start' => 50, 'branding.student_id.enable_alphabetical_ordering' => false]);
        $this->assertSame('DN/26/0050', $this->generate());
        Student::create(['student_id' => 'DN/26/9999', 'first_name' => 'Example', 'last_name' => 'Student']);
        $this->assertSame('DN/26/10000', $this->generate());
    }

    public function test_repair_previews_then_applies_only_selected_fallback_ids_and_keeps_links(): void
    {
        Schema::create('student_id_changes', function (Blueprint $table) {
            $table->id();
            $table->integer('student_id');
            $table->string('old_student_id');
            $table->string('new_student_id');
            $table->integer('changed_by')->nullable();
            $table->string('status');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
        $first = Student::create(['student_id' => 'DN/26/0001', 'first_name' => 'Example', 'last_name' => 'Quainoo']);
        $target = Student::create(['student_id' => 'MHTIDNS20260855', 'first_name' => 'Example', 'last_name' => 'Amonu', 'college_class_id' => 1, 'user_id' => 25]);
        $other = Student::create(['student_id' => 'MHTIDNS20260926', 'first_name' => 'Example', 'last_name' => 'Other']);
        $options = ['--student' => [$target->id], '--academic-year' => 1];
        $this->artisan('students:repair-fallback-ids', $options)->assertSuccessful();
        $this->assertSame('MHTIDNS20260855', $target->fresh()->student_id);
        $this->assertSame(0, DB::table('student_id_changes')->count());
        $this->artisan('students:repair-fallback-ids', $options + ['--apply' => true])->assertSuccessful();
        $this->assertSame('DN/26/0002', $target->fresh()->student_id);
        $this->assertSame(25, $target->fresh()->user_id);
        $this->assertSame('DN/26/0001', $first->fresh()->student_id);
        $this->assertSame('MHTIDNS20260926', $other->fresh()->student_id);
        $this->assertSame('MHTIDNS20260855', DB::table('student_id_changes')->value('old_student_id'));
        $this->artisan('students:repair-fallback-ids', $options + ['--apply' => true])->assertFailed();
        $this->assertSame(1, DB::table('student_id_changes')->count());
    }

    public function test_structured_format_still_uses_its_existing_sequence_rules(): void
    {
        config(['branding.student_id.format' => 'structured']);
        $this->assertSame('MHTIDNS/DN/26/27/001', $this->generate());
        Student::create(['student_id' => 'MHTIDNS/DN/26/27/001', 'first_name' => 'Example', 'last_name' => 'Student']);
        $this->assertSame('MHTIDNS/DN/26/27/002', $this->generate());
    }
}
