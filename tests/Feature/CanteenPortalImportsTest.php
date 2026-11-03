<?php

namespace Tests\Feature;

use App\Models\CanteenQualificationImport;
use App\Models\User;
use App\Services\Canteen\CanteenClearanceService;
use App\Services\Canteen\CanteenPolicy;
use App\Services\Canteen\PortalQualificationImports;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CanteenPortalImportsTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
        Storage::fake('local');
        config(['branding.student_id.institution_prefix' => 'PNMTC/DA']);
        $this->manager = User::factory()->create();
        $this->manager->assignRole(Role::findOrCreate('Finance Manager', 'web'));
        app(CanteenPolicy::class)->setPeriod('2026/2027', 1);
        $this->actingAs($this->manager);
    }

    private function workbook(array $rows = [['001', 'QUALIFIED']], string $name = 'class.xlsm', string $class = 'RGN Test'): UploadedFile
    {
        $book = new Spreadsheet;
        $sheet = $book->getActiveSheet();
        $sheet->setTitle('Sheet1');
        $sheet->setCellValue('C1', $class);
        foreach ($rows as $i => [$serial, $status]) {
            $sheet->setCellValue('C'.($i + 2), 'PNMTC/DA/RGN/24/25/'.$serial.' Fictional Student');
            $sheet->setCellValue('D'.($i + 2), $status);
        }
        $path = tempnam(sys_get_temp_dir(), 'portal-list-');
        (new Xlsx($book))->save($path);
        $bytes = file_get_contents($path);
        unlink($path);
        $book->disconnectWorksheets();

        return UploadedFile::fake()->createWithContent($name, $bytes);
    }

    private function preview(array $rows = [['001', 'QUALIFIED']], array $extra = []): string
    {
        $response = $this->post(route('canteen.lists.upload'), array_merge(['files' => [$this->workbook($rows)], 'academic_year' => '2026/2027', 'semester' => 1], $extra));
        $response->assertSessionHasNoErrors()->assertRedirect();

        return basename($response->headers->get('Location'));
    }

    private function confirm(string $token)
    {
        return $this->post(route('canteen.lists.confirm', $token), ['acknowledge' => '1']);
    }

    public function test_finance_manager_can_open_a_separate_module_without_finance_data(): void
    {
        $this->get(route('canteen.lists.index'))->assertOk()->assertSee('Canteen qualification lists');
        $this->assertDatabaseCount('student_fee_bills', 0);
    }

    public function test_ordinary_staff_cannot_read_upload_confirm_or_revoke(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('Staff', 'web'));
        $this->actingAs($user);
        $this->get(route('canteen.lists.index'))->assertForbidden();
        $this->post(route('canteen.lists.upload'), [])->assertForbidden();
        $this->confirm('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa')->assertForbidden();
        $this->post(route('canteen.lists.revoke', 1), ['reason' => 'Test reason', 'acknowledge' => 1])->assertForbidden();
        $this->get(route('canteen.lists.source', 1))->assertForbidden();
    }

    public function test_staff_with_explicit_college_permission_can_manage_lists(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('Staff', 'web'));
        $permission = Permission::findOrCreate('canteen.manage qualifications', 'web');
        $user->givePermissionTo($permission);
        $this->actingAs($user->fresh())->get(route('canteen.lists.index'))->assertOk();
        $user->revokePermissionTo($permission);
        $this->actingAs($user->fresh())->get(route('canteen.lists.index'))->assertForbidden();
    }

    public function test_preview_commits_no_qualification_or_student_rows(): void
    {
        $token = $this->preview([['001', 'QUALIFIED'], ['002', 'NOT QUALIFIED']]);
        $this->get(route('canteen.lists.preview', $token))->assertOk()->assertSee('Review qualification import');
        $this->assertDatabaseCount('canteen_qualifications', 0);
        $this->assertDatabaseCount('canteen_qualification_imports', 0);
        $this->assertDatabaseCount('students', 0);
    }

    public function test_confirmation_requires_acknowledgment_and_preserves_actor_and_private_source(): void
    {
        $token = $this->preview();
        $this->post(route('canteen.lists.confirm', $token), [])->assertSessionHasErrors('acknowledge');
        $this->assertDatabaseCount('canteen_qualifications', 0);
        $this->confirm($token)->assertSessionHasNoErrors()->assertRedirect(route('canteen.lists.index'));
        $batch = CanteenQualificationImport::first();
        $this->assertSame($this->manager->id, $batch->imported_by);
        $path = app(PortalQualificationImports::class)->archivePath('PNMTC/DA', $batch->file_sha256);
        Storage::disk('local')->assertExists($path);
        $this->get(route('canteen.lists.source', $batch->id))->assertDownload('class.xlsm');
        $result = app(CanteenClearanceService::class)->verify('PNMTC/DA/RGN/24/25/001');
        $this->assertSame('approved', $result['decision']);
        $this->assertNull($result['payment_percentage']);
        $this->assertDatabaseCount('student_fee_bills', 0);
    }

    public function test_preview_is_bound_to_the_uploading_user(): void
    {
        $token = $this->preview();
        $other = User::factory()->create();
        $other->assignRole('Finance Manager');
        $this->actingAs($other);
        $this->get(route('canteen.lists.preview', $token))->assertForbidden();
        $this->confirm($token)->assertForbidden();
        $this->assertDatabaseCount('canteen_qualifications', 0);
    }

    public function test_file_changes_after_preview_are_rejected(): void
    {
        $token = $this->preview();
        Storage::disk('local')->append('canteen/previews/'.$token.'/class.xlsm', 'changed');
        $this->confirm($token)->assertSessionHasErrors('files');
        $this->assertDatabaseCount('canteen_qualifications', 0);
    }

    public function test_expired_or_reused_previews_cannot_commit(): void
    {
        $token = $this->preview();
        $this->travel(31)->minutes();
        $this->confirm($token)->assertSessionHasErrors('files');
        $this->assertDatabaseCount('canteen_qualifications', 0);
        $this->travelBack();
        $token = $this->preview();
        $this->confirm($token)->assertSessionHasNoErrors();
        $this->confirm($token)->assertSessionHasErrors('files');
        $this->assertDatabaseCount('canteen_qualifications', 1);
    }

    public function test_institution_or_policy_changes_require_a_new_preview(): void
    {
        $token = $this->preview();
        config(['branding.student_id.institution_prefix' => 'OTHER/COLLEGE']);
        $this->confirm($token)->assertSessionHasErrors('files');
        $this->assertDatabaseCount('canteen_qualifications', 0);
        config(['branding.student_id.institution_prefix' => 'PNMTC/DA']);
        $token = $this->preview();
        app(CanteenPolicy::class)->setPeriod('2027/2028', 2);
        $this->confirm($token)->assertSessionHasErrors('files');
        $this->assertDatabaseCount('canteen_qualifications', 0);
    }

    public function test_invalid_and_non_excel_uploads_do_not_partially_import(): void
    {
        $this->post(route('canteen.lists.upload'), ['files' => [$this->workbook(), $this->workbook([['002', 'UNKNOWN']], 'bad.xlsx', 'Other class')], 'academic_year' => '2026/2027', 'semester' => 1])->assertSessionHasErrors('files');
        $this->assertDatabaseCount('canteen_qualifications', 0);
        $this->assertDatabaseCount('students', 0);
        $this->post(route('canteen.lists.upload'), ['files' => [UploadedFile::fake()->createWithContent('fake.xlsx', 'not a workbook')], 'academic_year' => '2026/2027', 'semester' => 1])->assertSessionHasErrors('files');
    }

    public function test_replacement_warns_about_omissions_and_records_revoking_actor(): void
    {
        $this->confirm($this->preview([['001', 'QUALIFIED'], ['002', 'QUALIFIED']]))->assertSessionHasNoErrors();
        $old = CanteenQualificationImport::first();
        $token = $this->preview([['001', 'NOT QUALIFIED']]);
        $preview = app(PortalQualificationImports::class)->preview($token, $this->manager->id);
        $this->assertSame([$old->id], $preview['summary'][0]['replaces']);
        $this->assertSame(1, $preview['summary'][0]['omitted']);
        $this->confirm($token)->assertSessionHasNoErrors();
        $this->assertNotNull($old->fresh()->revoked_at);
        $this->assertSame($this->manager->id, $old->fresh()->revoked_by);
        $this->assertSame('review_required', app(CanteenClearanceService::class)->verify('PNMTC/DA/RGN/24/25/002')['decision']);
    }

    public function test_unchanged_file_replay_does_not_duplicate_rows(): void
    {
        $this->confirm($this->preview())->assertSessionHasNoErrors();
        $token = $this->preview();
        $preview = app(PortalQualificationImports::class)->preview($token, $this->manager->id);
        $this->assertTrue($preview['summary'][0]['replayed']);
        $this->confirm($token)->assertSessionHasNoErrors();
        $this->assertDatabaseCount('canteen_qualifications', 1);
    }

    public function test_active_period_changes_only_after_explicit_confirmation(): void
    {
        $token = $this->preview(extra: ['academic_year' => '2027/2028', 'semester' => 3, 'activate' => 1]);
        $this->assertSame('2026/2027', app(CanteenPolicy::class)->period()['academic_year']);
        $this->post(route('canteen.lists.confirm', $token), ['acknowledge' => 1, 'academic_year' => 'tampered', 'semester' => 99])->assertSessionHasNoErrors();
        $this->assertSame(['academic_year' => '2027/2028', 'semester' => 3], app(CanteenPolicy::class)->period());
    }

    public function test_revocation_requires_reason_and_keeps_audit_history(): void
    {
        $this->confirm($this->preview());
        $batch = CanteenQualificationImport::first();
        $this->post(route('canteen.lists.revoke', $batch->id), ['acknowledge' => 1])->assertSessionHasErrors('reason');
        $this->post(route('canteen.lists.revoke', $batch->id), ['acknowledge' => 1, 'reason' => 'Withdrawn pending correction'])->assertSessionHasNoErrors();
        $this->assertSame($this->manager->id, $batch->fresh()->revoked_by);
        $this->assertDatabaseCount('canteen_qualifications', 1);
        $this->assertSame('review_required', app(CanteenClearanceService::class)->verify('PNMTC/DA/RGN/24/25/001')['decision']);
    }

    public function test_history_source_and_revocation_are_scoped_to_institution(): void
    {
        $this->confirm($this->preview());
        $id = CanteenQualificationImport::first()->id;
        config(['branding.student_id.institution_prefix' => 'OTHER/COLLEGE']);
        $this->get(route('canteen.lists.index'))->assertDontSee('class.xlsm');
        $this->get(route('canteen.lists.source', $id))->assertNotFound();
        $this->get(route('canteen.lists.withdrawal', $id))->assertNotFound();
        $this->post(route('canteen.lists.revoke', $id), ['acknowledge' => 1, 'reason' => 'Wrong institution'])->assertNotFound();
    }
}
