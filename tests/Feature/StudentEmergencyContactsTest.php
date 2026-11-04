<?php

namespace Tests\Feature;

use App\Exports\StudentImportTemplateExport;
use App\Imports\StudentImporter;
use App\Livewire\StudentCreateForm;
use App\Livewire\StudentEmergencyContacts;
use App\Livewire\StudentImport;
use App\Models\Student;
use App\Models\StudentEmergencyContact;
use App\Models\User;
use App\Services\StudentEmergencyContactService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StudentEmergencyContactsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
        // Isolated schema: never migrate or write an institution database.
        config(['database.default' => 'emergency_tests', 'database.connections.emergency_tests' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
        ]]);
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        foreach (['college_classes', 'cohorts'] as $table) {
            Schema::create($table, function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->nullable();
            });
        }
        foreach ([
            '0001_01_01_000000_create_users_table.php',
            '2025_05_04_154455_create_permission_tables.php',
            '2024_10_24_175533_create_students_table.php',
            '2024_10_25_122821_create_academic_years_table.php',
            '2025_05_04_124006_add_user_id_to_students_table.php',
            '2025_11_08_151945_add_academic_year_id_to_students_table.php',
            '2026_11_04_000001_create_student_emergency_contacts_table.php',
        ] as $file) {
            (require database_path('migrations/'.$file))->up();
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function student(array $data = []): Student
    {
        return Student::create($data + ['student_id' => 'TEST/'.(Student::count() + 1), 'first_name' => 'Example', 'last_name' => 'Student', 'email' => 'student'.Student::count().'@example.test']);
    }

    private function user(string $role, ?Student $student = null): User
    {
        $user = User::create(['name' => 'Example User', 'email' => 'user'.User::count().'@example.test', 'password' => 'password']);
        $user->assignRole(Role::findOrCreate($role, 'web'));
        $student?->update(['user_id' => $user->id]);

        return $user;
    }

    private function contact(array $data = []): array
    {
        return $data + ['full_name' => 'Example Parent', 'relationship' => 'Parent', 'mobile_number' => '0244000000'];
    }

    public function test_existing_students_without_contacts_still_render_and_need_no_backfill(): void
    {
        $student = $this->student();
        $this->assertCount(0, $student->emergencyContacts);
        Livewire::actingAs($this->user('Administrator'))->test(StudentEmergencyContacts::class, ['studentId' => $student->id])
            ->assertSee('No emergency contacts added')->assertSee('Add Contact');
        $this->assertSame(1, Student::count());
        $this->assertSame(0, StudentEmergencyContact::count());
    }

    public function test_migration_preserves_preexisting_student_records(): void
    {
        $migration = require database_path('migrations/2026_11_04_000001_create_student_emergency_contacts_table.php');
        $migration->down();
        $student = $this->student();
        $before = $student->fresh()->getAttributes();
        $migration->up();
        $this->assertSame($before, $student->fresh()->getAttributes());
        $this->assertSame(0, $student->emergencyContacts()->count());
    }

    public function test_staff_can_add_multiple_edit_and_remove_contacts(): void
    {
        $student = $this->student();
        $component = Livewire::actingAs($this->user('Academic Officer'))->test(StudentEmergencyContacts::class, ['studentId' => $student->id]);
        $component->call('addContact')->set('contact', $this->contact(['email' => ' parent @example.test ', 'gps_address' => 'AK-001-0001', 'residential_address' => 'Ankaase']))
            ->call('saveContact')->assertHasNoErrors()->assertSee('Example Parent');
        $component->call('addContact')->set('contact', $this->contact(['full_name' => 'Example Spouse', 'relationship' => 'Spouse', 'mobile_number' => '+233244000001']))
            ->call('saveContact')->assertHasNoErrors();
        $this->assertSame(2, $student->emergencyContacts()->count());
        $contact = $student->emergencyContacts()->first();
        $this->assertSame('parent@example.test', $contact->email);
        $component->call('editContact', $contact->id)->set('contact.alternate_phone', '0204000000')->call('saveContact')->assertHasNoErrors();
        $this->assertSame('0204000000', $contact->fresh()->alternate_phone);
        $component->call('deleteContact', $contact->id)->assertHasNoErrors();
        $this->assertSame(1, $student->emergencyContacts()->count());
    }

    public function test_student_can_manage_only_their_own_contacts(): void
    {
        $own = $this->student();
        $other = $this->student();
        $user = $this->user('Student', $own);
        Livewire::actingAs($user)->test(StudentEmergencyContacts::class, ['studentId' => $own->id])
            ->call('addContact')->set('contact', $this->contact())->call('saveContact')->assertHasNoErrors();
        Livewire::test(StudentEmergencyContacts::class, ['studentId' => $other->id])->assertForbidden();
        $this->assertSame(1, $own->emergencyContacts()->count());
        $this->assertSame(0, $other->emergencyContacts()->count());
    }

    public function test_contact_ids_are_scoped_to_the_selected_student_for_edit_and_delete(): void
    {
        $first = $this->student();
        $second = $this->student();
        $contact = app(StudentEmergencyContactService::class)->save($second, $this->contact());
        $user = $this->user('Administrator');
        foreach (['editContact', 'deleteContact'] as $action) {
            try {
                Livewire::actingAs($user)->test(StudentEmergencyContacts::class, ['studentId' => $first->id])
                    ->call($action, $contact->id);
                $this->fail('A contact belonging to another student must be inaccessible.');
            } catch (ModelNotFoundException $exception) {
                $this->assertSame(StudentEmergencyContact::class, $exception->getModel());
            }
        }
        $this->assertNotNull($contact->fresh());
    }

    public function test_guest_and_unrelated_staff_cannot_read_contact_details(): void
    {
        $student = $this->student();
        Livewire::test(StudentEmergencyContacts::class, ['studentId' => $student->id])->assertForbidden();
        Livewire::actingAs($this->user('Lecturer'))->test(StudentEmergencyContacts::class, ['studentId' => $student->id])->assertForbidden();
    }

    public function test_student_id_is_locked_against_browser_state_tampering(): void
    {
        $own = $this->student();
        $other = $this->student();
        $component = Livewire::actingAs($this->user('Student', $own))->test(StudentEmergencyContacts::class, ['studentId' => $own->id]);
        $this->expectException(CannotUpdateLockedPropertyException::class);
        $component->set('studentId', $other->id);
    }

    public function test_required_contact_fields_and_optional_email_are_validated(): void
    {
        $student = $this->student();
        $component = Livewire::actingAs($this->user('Administrator'))->test(StudentEmergencyContacts::class, ['studentId' => $student->id]);
        $component->call('addContact')->call('saveContact')->assertHasErrors(['contact.full_name', 'contact.relationship', 'contact.mobile_number']);
        $component->set('contact', $this->contact(['email' => 'invalid', 'alternate_phone' => 'bad']))->call('saveContact')
            ->assertHasErrors(['contact.email', 'contact.alternate_phone']);
        $this->assertSame(0, StudentEmergencyContact::count());
        $component->set('contact', $this->contact())->call('saveContact')->assertHasNoErrors();
    }

    private function importer(): StudentImporter
    {
        // Existing student ID and null academics keep this focused on contact import.
        return new StudentImporter(null, null, [], null, false);
    }

    private function importRow(array $values = []): array
    {
        return $values + ['student_id' => 'TEST/IMPORT', 'first_name' => 'Example', 'last_name' => 'Imported', 'email' => 'imported@example.test'];
    }

    public function test_dnsa_contact_columns_import_and_retries_update_without_duplicates_or_erasing_manual_contacts(): void
    {
        $row = $this->importRow([
            'emergency_name' => 'Imported Parent', 'emergency_relationship' => 'Parent', 'emergency_mobile' => '0244000000',
            'emergency_alt_phone' => '0204000000', 'emergency_email' => ' parent @example.test ',
            'emergency_gps' => 'AK-001-0001', 'emergency_residential' => "Ankaase\nAshanti",
            'emergency_2_full_name' => 'Imported Sibling', 'emergency_2_relationship' => 'Sibling', 'emergency_2_mobile_number' => '+233244000001',
        ]);
        $importer = $this->importer();
        $importer->collection(new Collection([$row]));
        $this->assertSame(0, $importer->getImportStats()['failed']);
        $this->assertSame(2, $importer->getImportStats()['emergency_contacts_imported']);
        $student = Student::firstOrFail();
        $this->assertSame('parent@example.test', $student->emergencyContacts()->where('import_slot', 1)->firstOrFail()->email);
        app(StudentEmergencyContactService::class)->save($student, $this->contact(['full_name' => 'Manual Contact']));
        $row['emergency_mobile'] = '0244000002';
        $retry = $this->importer();
        $retry->collection(new Collection([$row]));
        $this->assertSame(3, $student->emergencyContacts()->count());
        $this->assertSame(1, Student::count());
        $this->assertSame('0244000002', $student->emergencyContacts()->where('import_slot', 1)->firstOrFail()->mobile_number);
        $blank = $this->importRow();
        foreach (array_keys($row) as $key) {
            if (str_starts_with($key, 'emergency_')) {
                $blank[$key] = '';
            }
        }
        $this->importer()->collection(new Collection([$blank]));
        $this->assertSame(3, $student->emergencyContacts()->count());
        $this->assertTrue($student->emergencyContacts()->where('full_name', 'Manual Contact')->exists());
    }

    public function test_partial_contact_import_reports_row_errors_and_does_not_create_student(): void
    {
        $importer = $this->importer();
        $importer->collection(new Collection([$this->importRow(['emergency_name' => 'Incomplete Contact'])]));
        $stats = $importer->getImportStats();
        $this->assertSame(1, $stats['skipped']);
        $this->assertStringContainsString('Emergency contact 1', implode(' ', $stats['validation_errors'][0]['errors']));
        $this->assertSame(0, Student::count());
    }

    public function test_template_contact_headings_work_for_programmatic_imports_and_blank_optional_fields_preserve_saved_details(): void
    {
        $row = $this->importRow([
            'emergency_contact_full_name' => 'Example Contact',
            'emergency_contact_relationship' => 'Parent',
            'emergency_contact_mobile_number' => '0244000000',
            'emergency_contact_email' => 'parent@example.test',
            'emergency_contact_2_full_name' => 'Second Contact',
            'emergency_contact_2_relationship' => 'Sibling',
            'emergency_contact_2_mobile_number' => '0204000000',
        ]);
        $this->importer()->collection(new Collection([$row]));
        $this->assertSame(2, StudentEmergencyContact::count());
        $row['emergency_contact_email'] = '';
        $row['emergency_contact_mobile_number'] = '0244000001';
        $this->importer()->collection(new Collection([$row]));
        $contact = StudentEmergencyContact::where('import_slot', 1)->firstOrFail();
        $this->assertSame('parent@example.test', $contact->email);
        $this->assertSame('0244000001', $contact->mobile_number);
        $this->assertSame(2, StudentEmergencyContact::count());
    }

    public function test_contact_and_student_import_roll_back_together_if_account_provisioning_fails(): void
    {
        $importer = new class(null, null) extends StudentImporter
        {
            protected function provisionStudentAccount(Student $student): void
            {
                throw new \RuntimeException('Simulated account failure');
            }
        };
        $importer->collection(new Collection([$this->importRow([
            'emergency_name' => 'Example Contact', 'emergency_relationship' => 'Sibling', 'emergency_mobile' => '0244000000',
        ])]));
        $this->assertSame(1, $importer->getImportStats()['failed']);
        $this->assertSame(0, $importer->getImportStats()['created']);
        $this->assertSame(0, Student::count());
        $this->assertSame(0, StudentEmergencyContact::count());
    }

    public function test_manual_admission_can_include_multiple_contacts_or_none(): void
    {
        DB::table('college_classes')->insert(['id' => 1, 'name' => 'Nursing']);
        DB::table('cohorts')->insert(['id' => 1, 'name' => '2026']);
        DB::table('academic_years')->insert([
            'id' => 1, 'name' => '2026-2027', 'slug' => '2026-2027', 'start_date' => '2026-09-01', 'end_date' => '2027-08-31',
        ]);
        $user = $this->user('Administrator');
        foreach ([true, false] as $withContacts) {
            $component = Livewire::actingAs($user)->test(StudentCreateForm::class)
                ->set('first_name', 'Example')->set('last_name', 'Admitted')
                ->set('student_id_number', $withContacts ? 'MANUAL/1' : 'MANUAL/2')
                ->set('email', $withContacts ? 'first@example.test' : 'second@example.test')
                ->set('college_class_id', 1)->set('cohort_id', 1)->set('academic_year_id', 1);
            if ($withContacts) {
                $component->call('addEmergencyContact');
                $key = array_key_first($component->get('emergencyContacts'));
                $component->set('emergencyContacts.'.$key, $this->contact())
                    ->call('addEmergencyContact');
                $keys = array_keys($component->get('emergencyContacts'));
                $component->set('emergencyContacts.'.$keys[1], $this->contact(['full_name' => 'Second Contact']));
            }
            $component->call('createStudent')->assertHasNoErrors();
            $student = Student::where('email', $withContacts ? 'first@example.test' : 'second@example.test')->firstOrFail();
            $component->assertRedirect(route('students.show', $student->id));
            $this->assertSame($withContacts ? 2 : 0, $student->emergencyContacts()->count());
        }
    }

    public function test_deleting_student_cascades_contacts_and_does_not_touch_other_students(): void
    {
        $first = $this->student();
        $second = $this->student();
        $service = app(StudentEmergencyContactService::class);
        $service->save($first, $this->contact());
        $service->save($second, $this->contact());
        $first->delete();
        $this->assertSame(1, StudentEmergencyContact::count());
        $this->assertSame(1, $second->emergencyContacts()->count());
    }

    public function test_template_headings_and_dnsa_aliases_suggest_the_correct_contact_fields(): void
    {
        $component = new class extends StudentImport
        {
            public function suggestion(string $heading): string
            {
                return $this->suggestFieldForHeading($heading);
            }
        };
        $this->assertSame('emergency_full_name', $component->suggestion('emergency_full_name'));
        $this->assertSame('emergency_full_name', $component->suggestion('emergency_name'));
        $this->assertSame('emergency_mobile_number', $component->suggestion('emergency_mobile'));
        $this->assertSame('emergency_alternate_phone', $component->suggestion('emergency_alt_phone'));
        $this->assertSame('emergency_residential_address', $component->suggestion('emergency_residential'));
        $template = new StudentImportTemplateExport;
        foreach ($template->headings() as $heading) {
            if (str_starts_with($heading, 'Emergency Contact')) {
                $this->assertNotSame('', $component->suggestion(strtolower(str_replace(' ', '_', $heading))));
            }
        }
        $this->assertSame('@', $template->columnFormats()['U']);
        $this->assertSame('@', $template->columnFormats()['AB']);
    }
}
