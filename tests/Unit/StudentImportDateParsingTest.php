<?php

namespace Tests\Unit;

use App\Imports\StudentDateFormatInspector;
use App\Imports\StudentImporter;
use App\Models\Student;
use App\Services\StudentIdGenerationService;
use App\Services\StudentImportDateParser;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class StudentImportDateParsingTest extends TestCase
{
    private mixed $previousResolver;

    private mixed $previousFacadeApplication;

    protected function setUp(): void
    {
        parent::setUp();
        $this->previousResolver = Student::getConnectionResolver();
        $this->previousFacadeApplication = Facade::getFacadeApplication();
        // Standalone SQLite only: no Laravel bootstrap, .env or institution DB.
        $container = new Container;
        $database = new Capsule($container);
        $database->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
        $database->setAsGlobal();
        $database->bootEloquent();
        $container->instance('db', $database->getDatabaseManager());
        $container->instance('log', new NullLogger);
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($container);

        $database->schema()->create('students', function (Blueprint $table) {
            $table->id();
            $table->string('student_id')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('date_of_birth')->nullable();
            $table->integer('college_class_id');
            $table->integer('cohort_id');
            $table->integer('user_id')->nullable();
            $table->string('status');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        if ($this->previousResolver) {
            Student::setConnectionResolver($this->previousResolver);
        } else {
            Student::unsetConnectionResolver();
        }
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($this->previousFacadeApplication);
        parent::tearDown();
    }

    public static function submittedDates(): array
    {
        // Only dates from the reported file; no student identity data.
        return [
            ['16/8/2005', '2005-08-16'], ['16/3/2006', '2006-03-16'],
            ['15/9/2007', '2007-09-15'], ['5/4/2005', '2005-04-05'],
            ['9/1/2004', '2004-01-09'], ['17/2/2005', '2005-02-17'],
            ['17/11/2007', '2007-11-17'], ['23/4/2000', '2000-04-23'],
            ['12/6/2001', '2001-06-12'], ['1/12/2004', '2004-12-01'],
            ['23/7/2004', '2004-07-23'], ['17/5/2003', '2003-05-17'],
            ['16/8/1998', '1998-08-16'], ['22/9/1999', '1999-09-22'],
            ['15/12/2007', '2007-12-15'], ['11/11/2005', '2005-11-11'],
            ['22/7/2007', '2007-07-22'], ['8/3/2008', '2008-03-08'],
            ['10/5/2005', '2005-05-10'], ['9/9/2005', '2005-09-09'],
            ['11/10/2005', '2005-10-11'], ['17/4/2004', '2004-04-17'],
            ['7/12/2005', '2005-12-07'], ['22/11/2004', '2004-11-22'],
            ['3/8/1997', '1997-08-03'], ['24/4/2003', '2003-04-24'],
            ['27/8/2001', '2001-08-27'], ['27/5/2006', '2006-05-27'],
            ['24/7/2006', '2006-07-24'], ['23/7/2003', '2003-07-23'],
            ['7/7/2003', '2003-07-07'], ['18/8/2004', '2004-08-18'],
            ['14/6/2004', '2004-06-14'], ['31/5/2007', '2007-05-31'],
            ['21/8/2005', '2005-08-21'], ['6/12/2005', '2005-12-06'],
            ['10/6/2007', '2007-06-10'],
        ];
    }

    #[DataProvider('submittedDates')]
    public function test_submitted_dates_are_mapped_and_validated_day_first(string $input, string $expected): void
    {
        $importer = new InspectableDateStudentImporter('dmy');
        $row = $importer->map(['date_of_birth' => $input]);
        $this->assertSame($expected, $row['date_of_birth']);
        $this->assertTrue($importer->validateDate($row['date_of_birth']));
    }

    public function test_original_parser_reproduces_the_reported_fourteen_twenty_three_split(): void
    {
        $dates = array_column(self::submittedDates(), 0);
        $accepted = array_filter($dates, fn ($date) => strtotime($date) !== false);
        $this->assertCount(37, $dates);
        $this->assertCount(14, $accepted);
        $this->assertCount(23, array_diff($dates, $accepted));
        $misread = array_filter(self::submittedDates(), fn ($pair) => strtotime($pair[0]) !== false && date('Y-m-d', strtotime($pair[0])) !== $pair[1]);
        $this->assertCount(11, $misread);
    }

    public static function invalidDates(): array
    {
        return array_map(fn ($date) => [$date], [
            '31/4/2005', '29/2/2005', '2005-02-29', '12/31/2005',
            '0/8/2005', '16/13/2005', '05/04/05', 'not a date',
            '16/8/2005 extra', "16/8/\0002005", 0, -1,
        ]);
    }

    #[DataProvider('invalidDates')]
    public function test_invalid_dates_are_rejected_instead_of_silently_rolled_over(mixed $input): void
    {
        $importer = new InspectableDateStudentImporter('dmy');
        $row = $importer->map(['date_of_birth' => $input]);
        $this->assertFalse($importer->validateDate($row['date_of_birth']));
    }

    public function test_iso_excel_leap_and_optional_dates_remain_supported(): void
    {
        $importer = new InspectableDateStudentImporter;
        foreach (['2005-08-16', '2005/8/16', '16-8-2005', '16.8.2005', '16 August 2005', '16 Aug 2005', 38580] as $input) {
            $this->assertSame('2005-08-16', $importer->map(['date_of_birth' => $input])['date_of_birth']);
        }
        $this->assertSame('2004-02-29', $importer->map(['date_of_birth' => '29/2/2004'])['date_of_birth']);
        $this->assertNull($importer->map(['date_of_birth' => ''])['date_of_birth']);
        $this->assertTrue($importer->validateDate(null));
    }

    public function test_reimport_corrects_prior_dates_preserves_ids_and_creates_only_missing_students(): void
    {
        $rows = [];
        $oldIds = [];
        foreach (self::submittedDates() as $index => [$date, $expected]) {
            $row = ['first_name' => 'Example', 'last_name' => 'Student'.$index, 'email' => "student{$index}@example.com", 'date_of_birth' => $date];
            $rows[] = $row;
            if (strtotime($date) !== false) {
                $oldIds[$row['email']] = 'EXISTING/'.$index;
                Student::create(array_merge($row, [
                    'student_id' => $oldIds[$row['email']],
                    'date_of_birth' => $date,
                    'college_class_id' => 1, 'cohort_id' => 1,
                    'user_id' => $index + 100, 'status' => 'active',
                ]));
            }
        }
        $this->assertSame(14, Student::count());
        $importer = new InspectableDateStudentImporter;
        $importer->collection(new Collection($rows));
        $stats = $importer->getImportStats();
        $this->assertSame(23, $stats['created']);
        $this->assertSame(14, $stats['updated']);
        $this->assertSame(23, $stats['ids_generated']);
        $this->assertSame(0, $stats['skipped']);
        $this->assertSame(0, $stats['failed']);
        $this->assertSame(37, Student::count());
        foreach (self::submittedDates() as $index => [$date, $expected]) {
            $student = Student::where('email', "student{$index}@example.com")->firstOrFail();
            $this->assertSame($expected, $student->getRawOriginal('date_of_birth'));
            if (isset($oldIds[$student->email])) {
                $this->assertSame($oldIds[$student->email], $student->student_id);
                $this->assertSame($index + 100, $student->user_id);
            }
        }
        $ids = Student::orderBy('email')->pluck('student_id')->all();
        $retry = new InspectableDateStudentImporter;
        $retry->collection(new Collection($rows));
        $this->assertSame(0, $retry->getImportStats()['created']);
        $this->assertSame(0, $retry->getImportStats()['ids_generated']);
        $this->assertSame(37, $retry->getImportStats()['updated']);
        $this->assertSame($ids, Student::orderBy('email')->pluck('student_id')->all());
    }

    public function test_entire_submitted_column_detects_day_first_and_resolves_ambiguous_rows(): void
    {
        $parser = new StudentImportDateParser;
        $analysis = $parser->inspect(array_column(self::submittedDates(), 0));
        $this->assertSame('detected', $parser->status($analysis));
        $this->assertSame(23, $analysis['dmy']);
        $this->assertSame(0, $analysis['mdy']);
        $this->assertSame('dmy', $parser->resolve($analysis));
        $this->assertSame('2005-04-05', $parser->normalize('5/4/2005', $parser->resolve($analysis)));
    }

    public function test_month_first_evidence_selects_month_first_for_the_whole_column(): void
    {
        $parser = new StudentImportDateParser;
        $analysis = $parser->inspect(['4/5/2005', '8/16/2005', '2005-07-23']);
        $this->assertSame('mdy', $parser->resolve($analysis));
        $this->assertSame('2005-04-05', $parser->normalize('4/5/2005', $parser->resolve($analysis)));
        $this->assertSame('2005-07-23', $parser->normalize('2005-07-23', 'mdy'));
    }

    public function test_ambiguous_dates_require_selection_and_cannot_write_any_students(): void
    {
        $importer = new InspectableDateStudentImporter;
        try {
            $importer->collection(new Collection([
                ['first_name' => 'Example', 'last_name' => 'Student', 'email' => 'student@example.com', 'date_of_birth' => '5/4/2005'],
            ]));
            $this->fail('An ambiguous date must not silently select a format.');
        } catch (\InvalidArgumentException $exception) {
            $this->assertStringContainsString('ambiguous', $exception->getMessage());
        }
        $this->assertSame(0, Student::count());
        $this->assertSame(0, $importer->getImportStats()['ids_generated']);
    }

    public function test_explicit_month_first_choice_controls_mapping_and_persistence(): void
    {
        $importer = new InspectableDateStudentImporter('mdy');
        $importer->collection(new Collection([
            ['first_name' => 'Example', 'last_name' => 'Student', 'email' => 'student@example.com', 'date_of_birth' => '5/4/2005'],
            ['first_name' => 'Example', 'last_name' => 'Invalid', 'email' => 'invalid@example.com', 'date_of_birth' => '16/8/2005'],
        ]));
        $this->assertSame('2005-05-04', Student::firstOrFail()->getRawOriginal('date_of_birth'));
        $this->assertSame(1, $importer->getImportStats()['created']);
        $this->assertSame(1, $importer->getImportStats()['skipped']);
    }

    public function test_conflicting_formats_block_auto_instead_of_trying_each_row_both_ways(): void
    {
        $parser = new StudentImportDateParser;
        $analysis = $parser->inspect(['16/8/2005', '8/16/2005', '5/4/2005']);
        $this->assertSame('mixed', $parser->status($analysis));
        $this->assertSame('dmy', $parser->resolve($analysis, 'dmy'));
        $this->expectException(\InvalidArgumentException::class);
        $parser->resolve($analysis);
    }

    public function test_date_inspection_sees_evidence_beyond_the_first_chunk_and_selected_header(): void
    {
        $parser = new StudentImportDateParser;
        $inspector = new StudentDateFormatInspector('birth_date', 3);
        $inspector->collection(new Collection(array_fill(0, 500, ['birth_date' => '5/4/2005'])));
        $this->assertSame('ambiguous', $parser->status($inspector->analysis));
        $inspector->collection(new Collection([['birth_date' => '16/8/2005']]));
        $this->assertSame(501, $inspector->analysis['total']);
        $this->assertSame(3, $inspector->headingRow());
        $this->assertSame('dmy', $parser->resolve($inspector->analysis));
        $inspector->collection(new Collection([['birth_date' => '8/16/2005']]));
        $this->assertSame('mixed', $parser->status($inspector->analysis));
        $this->assertLessThanOrEqual(3, count($inspector->analysis['examples']));
    }

    public function test_year_first_named_excel_and_symmetric_dates_need_no_order_guess(): void
    {
        $parser = new StudentImportDateParser;
        $analysis = $parser->inspect(['2005-08-16', 'August 16, 2005', 38580, '11/11/2005', null, '']);
        $this->assertSame('independent', $parser->status($analysis));
        $this->assertSame('auto', $parser->resolve($analysis));
        $this->assertSame(0, $analysis['dmy']);
        $this->assertSame(0, $analysis['mdy']);
        $this->assertSame('2005-08-16', $parser->normalize('August 16, 2005'));
        $this->assertSame('2005-11-11', $parser->normalize('11/11/2005'));
        $this->assertNull($parser->normalize('5/4/2005'));
    }
}

class InspectableDateStudentImporter extends StudentImporter
{
    public function __construct(string $dateFormat = 'auto')
    {
        parent::__construct(1, 1, [], null, false, 1, $dateFormat);
        $this->studentIdService = new class extends StudentIdGenerationService
        {
            private int $sequence = 0;

            public function generateStudentId(string $firstName, string $lastName, ?int $collegeClassId = null, ?int $academicYearId = null): string
            {
                return 'GENERATED/'.++$this->sequence;
            }
        };
    }

    public function map(array $row): array
    {
        return $this->mapRowToStudentData($row);
    }

    public function validateDate(mixed $date): bool
    {
        return $this->validateStudentData([
            'first_name' => 'Example', 'last_name' => 'Student',
            'email' => 'example@example.com', 'date_of_birth' => $date,
        ], 2)['valid'];
    }
}
