<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\CanteenVerification;
use App\Models\CollegeClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentFeeBill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CanteenApiTest extends TestCase
{
    use RefreshDatabase;

    private AcademicYear $year;

    private Semester $semester;

    protected function setUp(): void
    {
        parent::setUp();

        config(['authentication.method' => 'regular']);

        config(['canteen.payment_threshold' => 50, 'branding.student_id.institution_prefix' => 'PNMTC/DA']);

        $this->year = AcademicYear::create([
            'name' => '2025/2026',
            'slug' => '2025-2026',
            'year' => 2025,
            'start_date' => '2025-09-01',
            'end_date' => '2026-08-31',
            'is_current' => true,
        ]);

        $this->semester = Semester::create([
            'name' => 'Semester 1',
            'slug' => 'semester-1-canteen-test',
            'academic_year_id' => $this->year->id,
            'sequence' => 1,
            'is_current' => true,
        ]);
    }

    public function test_login_issues_token_for_staff_account(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/canteen/login', [
            'username' => $user->email,
            'password' => 'password',
            'device_id' => 'device-abc',
            'device_name' => 'Counter tablet',
        ]);

        $response->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonStructure(['token', 'expires_at', 'user', 'server_time']);

        $this->assertDatabaseHas('canteen_devices', ['device_id' => 'device-abc', 'registered_by' => $user->id]);
        $this->assertNotEmpty($user->tokens()->where('name', 'canteen-app')->first());
    }

    public function test_login_rejects_wrong_password(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/v1/canteen/login', [
            'username' => $user->email,
            'password' => 'not-the-password',
        ])->assertUnauthorized();
    }

    public function test_login_rejects_student_linked_account(): void
    {
        $user = User::factory()->create();

        Student::create([
            'student_id' => 'PNMTC/DA/RGN/25/26/099',
            'first_name' => 'Portal',
            'last_name' => 'Student',
            'status' => 'active',
            'user_id' => $user->id,
        ]);

        $this->postJson('/api/v1/canteen/login', [
            'username' => $user->email,
            'password' => 'password',
        ])->assertForbidden();
    }

    public function test_verify_requires_authentication(): void
    {
        $this->postJson('/api/v1/canteen/verify', [
            'index_number' => 'PNMTC/DA/RGN/25/26/001',
            'verification_method' => 'manual',
            'device_id' => 'device-abc',
        ])->assertUnauthorized();
    }

    public function test_verify_approves_student_above_threshold(): void
    {
        $student = $this->studentWithBill(100.0);
        Sanctum::actingAs(User::factory()->create(), ['canteen:access']);
        $this->withToken('test-bearer');

        $response = $this->postJson('/api/v1/canteen/verify', [
            'index_number' => 'pnmtc/da/rgn/25/26/001',
            'verification_method' => 'manual',
            'device_id' => 'device-abc',
            'client_request_id' => (string) Str::uuid(),
        ]);

        $response->assertOk()
            ->assertJsonPath('decision', CanteenVerification::DECISION_APPROVED)
            ->assertJsonPath('student.index_number', $student->student_id)
            ->assertJsonPath('replay', false);

        $this->assertEquals(100.0, $response->json('payment_percentage'));

        $this->assertDatabaseHas('canteen_verifications', [
            'index_number' => 'PNMTC/DA/RGN/25/26/001',
            'decision' => CanteenVerification::DECISION_APPROVED,
            'device_id' => 'device-abc',
            'is_offline' => false,
        ]);
    }

    public function test_verify_denies_student_below_threshold(): void
    {
        $this->studentWithBill(40.0);
        Sanctum::actingAs(User::factory()->create(), ['canteen:access']);
        $this->withToken('test-bearer');

        $this->postJson('/api/v1/canteen/verify', [
            'index_number' => 'PNMTC/DA/RGN/25/26/001',
            'verification_method' => 'ocr',
            'device_id' => 'device-abc',
        ])->assertOk()->assertJsonPath('decision', CanteenVerification::DECISION_DENIED);
    }

    public function test_verify_returns_not_found_for_unknown_index(): void
    {
        Sanctum::actingAs(User::factory()->create(), ['canteen:access']);
        $this->withToken('test-bearer');

        $this->postJson('/api/v1/canteen/verify', [
            'index_number' => 'PNMTC/DA/RGN/99/99/999',
            'verification_method' => 'ocr',
            'device_id' => 'device-abc',
        ])->assertOk()->assertJsonPath('decision', CanteenVerification::DECISION_NOT_FOUND);

        $this->assertDatabaseHas('canteen_verifications', [
            'index_number' => 'PNMTC/DA/RGN/99/99/999',
            'decision' => CanteenVerification::DECISION_NOT_FOUND,
            'student_id' => null,
        ]);
    }

    public function test_verify_requires_review_without_bill_or_with_inactive_record(): void
    {
        Sanctum::actingAs(User::factory()->create(), ['canteen:access']);
        $this->withToken('test-bearer');

        // Active student, no current-semester bill: fail closed, never approve.
        Student::create([
            'student_id' => 'PNMTC/DA/RGN/25/26/050',
            'first_name' => 'No',
            'last_name' => 'Bill',
            'status' => 'active',
        ]);

        $this->postJson('/api/v1/canteen/verify', [
            'index_number' => 'PNMTC/DA/RGN/25/26/050',
            'verification_method' => 'manual',
            'device_id' => 'device-abc',
        ])->assertOk()->assertJsonPath('decision', CanteenVerification::DECISION_REVIEW_REQUIRED);

        // Inactive student with a fully paid bill: still not cleared.
        $this->studentWithBill(100.0, 'inactive', 'PNMTC/DA/RGN/25/26/051');

        $this->postJson('/api/v1/canteen/verify', [
            'index_number' => 'PNMTC/DA/RGN/25/26/051',
            'verification_method' => 'manual',
            'device_id' => 'device-abc',
        ])->assertOk()->assertJsonPath('decision', CanteenVerification::DECISION_REVIEW_REQUIRED);
    }

    public function test_verify_is_idempotent_on_client_request_id(): void
    {
        $this->studentWithBill(100.0);
        Sanctum::actingAs(User::factory()->create(), ['canteen:access']);
        $this->withToken('test-bearer');

        $payload = [
            'index_number' => 'PNMTC/DA/RGN/25/26/001',
            'verification_method' => 'ocr',
            'device_id' => 'device-abc',
            'client_request_id' => (string) Str::uuid(),
        ];

        $this->postJson('/api/v1/canteen/verify', $payload)->assertOk()->assertJsonPath('replay', false);
        $this->postJson('/api/v1/canteen/verify', $payload)->assertOk()->assertJsonPath('replay', true);

        $this->assertSame(1, CanteenVerification::count());
    }

    public function test_roster_pages_with_cursor_and_returns_clearance(): void
    {
        $this->studentWithBill(100.0, 'active', 'PNMTC/DA/RGN/25/26/001');
        $this->studentWithBill(40.0, 'active', 'PNMTC/DA/RGN/25/26/002');
        $this->studentWithBill(100.0, 'active', 'PNMTC/DA/RGN/25/26/003');
        Sanctum::actingAs(User::factory()->create(), ['canteen:access']);
        $this->withToken('test-bearer');

        $first = $this->getJson('/api/v1/canteen/roster?limit=2')
            ->assertOk()
            ->assertJsonPath('has_more', true)
            ->assertJsonCount(2, 'students')
            ->assertJsonPath('students.0.clearance.decision', CanteenVerification::DECISION_APPROVED);

        $cursor = $first->json('next_cursor');
        $this->assertNotNull($cursor);

        $this->getJson('/api/v1/canteen/roster?limit=2&cursor='.$cursor)
            ->assertOk()
            ->assertJsonPath('has_more', false)
            ->assertJsonCount(1, 'students');

        // A complete snapshot also includes clearance changes outside Student.updated_at.
        $future = now()->addHour()->toIso8601String();
        $this->getJson('/api/v1/canteen/roster?since='.urlencode($future))
            ->assertOk()
            ->assertJsonCount(3, 'students')
            ->assertJsonPath('has_more', false)
            ->assertJsonPath('sync_mode', 'full_snapshot');
    }

    public function test_roster_requires_authentication(): void
    {
        $this->getJson('/api/v1/canteen/roster')->assertUnauthorized();
    }

    public function test_sync_stores_offline_verifications_and_reports_mismatches(): void
    {
        $this->studentWithBill(100.0);
        Sanctum::actingAs(User::factory()->create(), ['canteen:access']);
        $this->withToken('test-bearer');

        $requestId = (string) Str::uuid();

        $payload = ['verifications' => [[
            'client_request_id' => $requestId,
            'index_number' => 'PNMTC/DA/RGN/25/26/001',
            'verification_method' => 'ocr',
            'device_id' => 'device-offline',
            'verified_at' => now()->subHour()->toIso8601String(),
            'decision' => CanteenVerification::DECISION_DENIED, // Shown offline from a stale cache.
        ]]];

        $this->postJson('/api/v1/canteen/verifications/sync', $payload)
            ->assertOk()
            ->assertJsonPath('accepted', 1)
            ->assertJsonPath('duplicates', 0)
            ->assertJsonPath('mismatches.0.offline_decision', CanteenVerification::DECISION_DENIED)
            ->assertJsonPath('mismatches.0.current_decision', CanteenVerification::DECISION_APPROVED);

        // Replaying the same batch must not duplicate records.
        $this->postJson('/api/v1/canteen/verifications/sync', $payload)
            ->assertOk()
            ->assertJsonPath('accepted', 0)
            ->assertJsonPath('duplicates', 1);

        $this->assertSame(1, CanteenVerification::count());

        $record = CanteenVerification::first();
        $this->assertTrue($record->is_offline);
        $this->assertSame(CanteenVerification::DECISION_DENIED, $record->decision);
        $this->assertSame(CanteenVerification::DECISION_APPROVED, $record->reconciled_decision);
    }

    public function test_sync_rejects_malformed_batches(): void
    {
        Sanctum::actingAs(User::factory()->create(), ['canteen:access']);
        $this->withToken('test-bearer');

        $this->postJson('/api/v1/canteen/verifications/sync', [
            'verifications' => [[
                'index_number' => 'PNMTC/DA/RGN/25/26/001',
                'verification_method' => 'ocr',
                'device_id' => 'device-offline',
                'verified_at' => now()->toIso8601String(),
                'decision' => 'approved',
                // client_request_id missing: idempotency would be impossible.
            ]],
        ])->assertStatus(422)->assertJsonValidationErrors('verifications.0.client_request_id');
    }

    private function studentWithBill(float $percentage, string $status = 'active', string $index = 'PNMTC/DA/RGN/25/26/001'): Student
    {
        $programme = CollegeClass::firstOrCreate(
            ['short_name' => 'RGN'],
            ['name' => 'Registered General Nursing', 'is_active' => true, 'slug' => 'registered-general-nursing']
        );

        $student = Student::create([
            'student_id' => $index,
            'first_name' => 'Abisa',
            'last_name' => 'Anita',
            'college_class_id' => $programme->id,
            'status' => $status,
        ]);

        $total = 1000.00;
        $paid = round($total * $percentage / 100, 2);

        StudentFeeBill::create([
            'student_id' => $student->id,
            'academic_year_id' => $this->year->id,
            'semester_id' => $this->semester->id,
            'total_amount' => $total,
            'amount_paid' => $paid,
            'balance' => round($total - $paid, 2),
            'payment_percentage' => $percentage,
            'status' => $percentage >= 100 ? 'paid' : 'partial',
            'billing_date' => now(),
            'bill_reference' => 'TEST-'.uniqid(),
        ]);

        return $student;
    }
}
