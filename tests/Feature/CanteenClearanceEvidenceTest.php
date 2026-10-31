<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\CanteenQualification;
use App\Models\CanteenQualificationImport;
use App\Models\CanteenVerification;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentFeeBill;
use App\Models\User;
use App\Services\Canteen\CanteenClearanceService;
use App\Services\Canteen\CanteenPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CanteenClearanceEvidenceTest extends TestCase
{
    use RefreshDatabase;

    private Student $student;

    private AcademicYear $year;

    private Semester $semester;

    protected function setUp(): void
    {
        parent::setUp();
        config(['branding.student_id.institution_prefix' => 'PNMTC/DA', 'canteen.payment_threshold' => null]);
        $this->year = AcademicYear::create(['name' => '2026/2027', 'slug' => '2026-2027', 'year' => 2026,
            'start_date' => '2026-09-01', 'end_date' => '2027-08-31', 'is_current' => true]);
        $this->semester = Semester::create(['name' => 'First Semester', 'slug' => 'first', 'academic_year_id' => $this->year->id, 'sequence' => 1, 'is_current' => true]);
        $this->student = Student::create(['student_id' => 'PNMTC/DA/RGN/24/25/001', 'first_name' => 'Example', 'last_name' => 'Student', 'status' => 'active']);
        app(CanteenPolicy::class)->setPaymentThreshold(50);
    }

    public function test_qualification_is_not_a_payment_percentage_and_both_resolvers_agree(): void
    {
        $this->qualification(true);
        $result = $this->verify();
        $this->assertSame('approved', $result['decision']);
        $this->assertNull($result['payment_percentage']);
        $this->assertSame('qualification_list', $result['decision_context']['source']);
        $batch = app(CanteenClearanceService::class)->verifyMany(collect([$this->student]))[$this->student->id];
        $this->assertSame($result['decision'], $batch['decision']);
        $this->assertSame($result['decision_context']['qualification_id'], $batch['decision_context']['qualification_id']);
    }

    public function test_genuine_financial_denial_overrides_an_imported_approval(): void
    {
        $this->qualification(true);
        $this->bill(200, 80);
        $result = $this->verify();
        $this->assertSame('denied', $result['decision']);
        $this->assertSame(40.0, $result['payment_percentage']);
        $this->assertSame('financial_records', $result['decision_context']['source']);
    }

    public function test_genuine_financial_approval_overrides_an_imported_denial_at_exact_boundary(): void
    {
        $this->qualification(false);
        $this->bill(246, 123);
        $this->assertSame('approved', $this->verify()['decision']);
        $this->assertSame(50.0, $this->verify()['payment_percentage']);
    }

    public function test_a_rounded_percentage_must_not_turn_a_below_threshold_payment_into_approval(): void
    {
        $this->bill(1000, 499.99);
        $this->assertSame('denied', $this->verify()['decision']);
    }

    public function test_missing_institution_payment_rule_requires_review_even_with_an_approved_list(): void
    {
        DB::table('system_settings')->where('group', 'canteen')->where('key', 'like', '%.payment_threshold')->delete();
        $this->qualification(true);
        $this->bill(200, 200);
        $this->assertSame('review_required', $this->verify()['decision']);
        $this->assertNull($this->verify()['payment_percentage']);
    }

    public function test_unsafe_financial_data_does_not_fall_back_to_a_list_approval(): void
    {
        $this->qualification(true);
        $this->bill(0, 0);
        $this->assertSame('review_required', $this->verify()['decision']);
    }

    public function test_ambiguous_billing_period_cannot_silently_fall_back_to_list_approval(): void
    {
        $this->qualification(true);
        $this->bill(200, 0);
        AcademicYear::create(['name' => '2026/2027', 'slug' => 'duplicate-year', 'year' => 2026,
            'start_date' => '2026-09-01', 'end_date' => '2027-08-31', 'is_current' => false]);
        $this->assertSame('review_required', $this->verify()['decision']);
    }

    public function test_bill_reference_prefix_does_not_determine_whether_a_real_bill_is_valid(): void
    {
        $this->qualification(false);
        $this->bill(200, 200, 'CNT-GENUINE-INSTITUTION-REFERENCE');
        $result = $this->verify();
        $this->assertSame('approved', $result['decision']);
        $this->assertSame(100.0, $result['payment_percentage']);
    }

    public function test_changing_institution_does_not_reuse_another_institutions_payment_rule(): void
    {
        config(['branding.student_id.institution_prefix' => 'OTHER/DA']);
        $this->assertNull(app(CanteenPolicy::class)->paymentThreshold());
        app(CanteenPolicy::class)->setPaymentThreshold(75);
        $this->assertSame(75.0, app(CanteenPolicy::class)->paymentThreshold());
        config(['branding.student_id.institution_prefix' => 'PNMTC/DA']);
        $this->assertSame(50.0, app(CanteenPolicy::class)->paymentThreshold());
    }

    public function test_invalid_configuration_rolls_back_period_changes(): void
    {
        $this->artisan('canteen:configure', ['--academic-year' => '2030/2031', '--semester' => '2', '--payment-threshold' => '150'])->assertFailed();
        $this->assertSame(['academic_year' => '2026/2027', 'semester' => 1], app(CanteenPolicy::class)->period());
        $this->assertSame(50.0, app(CanteenPolicy::class)->paymentThreshold());
    }

    public function test_other_institution_list_does_not_create_financial_approval(): void
    {
        $this->qualification(true, '2026/2027', 'OTHER/DA');
        $this->assertSame('review_required', $this->verify()['decision']);
    }

    public function test_stale_period_and_other_institution_lists_are_not_used(): void
    {
        $this->qualification(true, '2025/2026');
        $this->qualification(true, '2026/2027', 'OTHER/DA');
        $this->assertSame('review_required', $this->verify()['decision']);
    }

    public function test_revoked_latest_status_does_not_reactivate_an_older_approval(): void
    {
        $this->qualification(true);
        $new = $this->qualification(false);
        $new->import->update(['revoked_at' => now(), 'revocation_reason' => 'Under review']);
        $this->assertSame('review_required', $this->verify()['decision']);
    }

    public function test_inactive_student_is_not_approved_by_either_source(): void
    {
        $this->qualification(true);
        $this->bill(200, 200);
        $this->student->update(['status' => 'inactive']);
        $this->assertSame('review_required', $this->verify()['decision']);
    }

    public function test_current_period_is_not_guessed_when_no_semester_is_current(): void
    {
        $this->qualification(true);
        $this->semester->update(['is_current' => false]);
        $this->assertSame('review_required', $this->verify()['decision']);
    }

    public function test_explicit_list_period_does_not_require_invented_billing_period_dates(): void
    {
        app(CanteenPolicy::class)->setPeriod('2027/2028', 1);
        $this->qualification(true, '2027/2028');
        $this->assertSame('approved', $this->verify()['decision']);
        $this->assertSame(1, AcademicYear::count());
    }

    public function test_latest_real_bill_and_actual_amounts_are_used_instead_of_stored_percentage(): void
    {
        $this->bill(200, 200);
        $latest = $this->bill(200, 0);
        $latest->update(['payment_percentage' => 100]);
        $this->assertSame('denied', $this->verify()['decision']);
        $this->assertSame(0.0, $this->verify()['payment_percentage']);
    }

    public function test_api_and_roster_preserve_source_and_null_payment_and_log_evidence(): void
    {
        $entry = $this->qualification(true);
        Sanctum::actingAs(User::factory()->create());
        $this->postJson('/api/v1/canteen/verify', ['index_number' => $this->student->student_id, 'verification_method' => 'manual', 'device_id' => 'test'])
            ->assertOk()->assertJsonPath('decision', 'approved')->assertJsonPath('payment_percentage', null)
            ->assertJsonPath('decision_context.source', 'qualification_list')->assertJsonPath('decision_context.qualification_id', $entry->id);
        $this->assertSame($entry->id, CanteenVerification::first()->decision_context['qualification_id']);
        $this->getJson('/api/v1/canteen/roster?since='.urlencode(now()->addHour()->toIso8601String()))
            ->assertOk()->assertJsonPath('students.0.clearance.decision_context.source', 'qualification_list');
    }

    public function test_offline_sync_reconciles_against_current_evidence(): void
    {
        $this->qualification(true);
        $this->bill(200, 0);
        Sanctum::actingAs(User::factory()->create());
        $this->postJson('/api/v1/canteen/verifications/sync', ['verifications' => [[
            'client_request_id' => (string) Str::uuid(), 'index_number' => $this->student->student_id,
            'verification_method' => 'manual', 'device_id' => 'test', 'verified_at' => now()->toIso8601String(), 'decision' => 'approved',
        ]]])->assertOk()->assertJsonPath('mismatches.0.current_decision', 'denied');
    }

    private function verify(): array
    {
        return app(CanteenClearanceService::class)->verify($this->student->student_id);
    }

    private function qualification(bool $qualified, string $year = '2026/2027', string $prefix = 'PNMTC/DA'): CanteenQualification
    {
        $batch = CanteenQualificationImport::create(['institution_prefix' => $prefix, 'academic_year' => $year, 'semester' => 1,
            'file_name' => 'test.xlsx', 'file_sha256' => hash('sha256', (string) Str::uuid()), 'class_label' => 'RGN', 'imported_at' => now()]);

        return CanteenQualification::create(['import_id' => $batch->id, 'student_id' => $this->student->id,
            'index_number' => $this->student->student_id, 'source_row' => 2, 'qualified' => $qualified, 'source_record' => ['source_status' => $qualified ? 'QUALIFIED' : 'NOT QUALIFIED']]);
    }

    private function bill(float $total, float $paid, ?string $reference = null): StudentFeeBill
    {
        return StudentFeeBill::create(['student_id' => $this->student->id, 'academic_year_id' => $this->year->id,
            'semester_id' => $this->semester->id, 'total_amount' => $total, 'amount_paid' => $paid, 'balance' => $total - $paid,
            'payment_percentage' => 0, 'status' => 'partial', 'billing_date' => now(), 'bill_reference' => $reference ?? 'REAL-'.Str::uuid()]);
    }
}
