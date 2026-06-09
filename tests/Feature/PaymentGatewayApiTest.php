<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\FeePayment;
use App\Models\FeeType;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentFeeBill;
use App\Models\StudentFeeBillItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaymentGatewayApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $financeUser;
    protected User $studentUser;
    protected Student $student;
    protected AcademicYear $academicYear;
    protected Semester $semester;
    protected StudentFeeBill $bill;
    protected StudentFeeBillItem $itemA;
    protected StudentFeeBillItem $itemB;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Roles & Permissions
        Permission::firstOrCreate(['name' => 'view finance']);
        Permission::firstOrCreate(['name' => 'process payments']);
        Permission::firstOrCreate(['name' => 'view students']);

        $adminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        $adminRole->givePermissionTo(Permission::all());

        $financeRole = Role::firstOrCreate(['name' => 'Finance Manager']);
        $financeRole->givePermissionTo(['view finance', 'process payments', 'view students']);

        $studentRole = Role::firstOrCreate(['name' => 'Student']);

        // 2. Setup Users
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('Super Admin');

        $this->financeUser = User::factory()->create();
        $this->financeUser->assignRole('Finance Manager');

        $this->studentUser = User::factory()->create();
        $this->studentUser->assignRole('Student');

        // 3. Setup Academic & Billing Data
        $this->academicYear = AcademicYear::create([
            'name' => '2025-2026',
            'year' => 2025,
            'slug' => '2025-2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-08-31',
            'is_current' => true,
            'is_deleted' => false,
        ]);

        $this->semester = Semester::create([
            'name' => 'First Semester',
            'slug' => 'first-semester',
            'academic_year_id' => $this->academicYear->id,
            'is_current' => true,
        ]);

        $this->student = Student::create([
            'student_id' => 'STU-TEST-99',
            'first_name' => 'Alice',
            'last_name' => 'Smith',
            'email' => $this->studentUser->email,
            'user_id' => $this->studentUser->id,
        ]);

        $feeTypeA = FeeType::create([
            'name' => 'Tuition Fees',
            'code' => 'TUI',
            'description' => 'Academic tuition fee item',
        ]);

        $feeTypeB = FeeType::create([
            'name' => 'Registration Fees',
            'code' => 'REG',
            'description' => 'Semester registration fee item',
        ]);

        $this->bill = StudentFeeBill::create([
            'student_id' => $this->student->id,
            'academic_year_id' => $this->academicYear->id,
            'semester_id' => $this->semester->id,
            'total_amount' => 1000.00,
            'amount_paid' => 0.00,
            'balance' => 1000.00,
            'payment_percentage' => 0,
            'status' => 'pending',
            'billing_date' => Carbon::now(),
            'bill_reference' => 'BILL-TEST-REF',
        ]);

        $this->itemA = StudentFeeBillItem::create([
            'student_fee_bill_id' => $this->bill->id,
            'fee_type_id' => $feeTypeA->id,
            'amount' => 600.00,
            'amount_paid' => 0.00,
            'balance' => 600.00,
            'status' => 'pending',
        ]);

        $this->itemB = StudentFeeBillItem::create([
            'student_fee_bill_id' => $this->bill->id,
            'fee_type_id' => $feeTypeB->id,
            'amount' => 400.00,
            'amount_paid' => 0.00,
            'balance' => 400.00,
            'status' => 'pending',
        ]);
    }

    /**
     * Test getting student details (unauthenticated)
     */
    public function test_unauthenticated_request_is_blocked(): void
    {
        $response = $this->getJson('/api/v1/payments/student');
        $response->assertStatus(401);
    }

    /**
     * Test getting student details (authenticated as Student)
     */
    public function test_student_can_fetch_own_details_and_bills(): void
    {
        $this->actingAs($this->studentUser);

        $response = $this->getJson('/api/v1/payments/student');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('student.student_id', 'STU-TEST-99')
            ->assertJsonPath('student.name', 'Alice Smith')
            ->assertJsonCount(1, 'bills')
            ->assertJsonPath('bills.0.bill_reference', 'BILL-TEST-REF')
            ->assertJsonCount(2, 'bills.0.items');
    }

    /**
     * Test admin/finance manager can retrieve student details via ID
     */
    public function test_finance_manager_can_fetch_any_student_details(): void
    {
        $this->actingAs($this->financeUser);

        // Fetch using the student's registration ID
        $response = $this->getJson('/api/v1/payments/student?student_id=STU-TEST-99');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('student.student_id', 'STU-TEST-99')
            ->assertJsonPath('student.name', 'Alice Smith');
    }

    /**
     * Test recording partial payment for a specific item
     */
    public function test_can_record_partial_payment_for_fee_item(): void
    {
        $this->actingAs($this->financeUser);

        $response = $this->postJson('/api/v1/payments/pay-item', [
            'student_fee_bill_item_id' => $this->itemA->id,
            'amount' => 250.00,
            'payment_method' => 'Mobile Money',
            'reference_number' => 'REF-API-101',
            'external_receipt' => 'https://paymentgateway.com/receipt/101',
            'note' => 'Paid part of Tuition via MoMo',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('fee_item.status', 'partially_paid')
            ->assertJsonPath('fee_item.amount_paid', 250)
            ->assertJsonPath('fee_item.balance', 350)
            ->assertJsonPath('bill.amount_paid', 250)
            ->assertJsonPath('bill.balance', 750)
            ->assertJsonPath('bill.status', 'partially_paid');

        $this->assertDatabaseHas('fee_payments', [
            'reference_number' => 'REF-API-101',
            'student_fee_bill_item_id' => $this->itemA->id,
            'amount' => 250.00,
            'external_receipt' => 'https://paymentgateway.com/receipt/101',
        ]);
    }

    /**
     * Test recording full payment for a specific item
     */
    public function test_can_record_full_payment_for_fee_item(): void
    {
        $this->actingAs($this->financeUser);

        $response = $this->postJson('/api/v1/payments/pay-item', [
            'student_fee_bill_item_id' => $this->itemB->id,
            'amount' => 400.00,
            'payment_method' => 'Credit Card',
            'reference_number' => 'REF-API-102',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('fee_item.status', 'paid')
            ->assertJsonPath('fee_item.amount_paid', 400)
            ->assertJsonPath('fee_item.balance', 0)
            ->assertJsonPath('bill.amount_paid', 400)
            ->assertJsonPath('bill.balance', 600)
            ->assertJsonPath('bill.status', 'partially_paid');
    }

    /**
     * Test recording duplicate reference numbers is rejected
     */
    public function test_duplicate_reference_numbers_are_rejected(): void
    {
        $this->actingAs($this->financeUser);

        // Record first payment
        $this->postJson('/api/v1/payments/pay-item', [
            'student_fee_bill_item_id' => $this->itemB->id,
            'amount' => 100.00,
            'payment_method' => 'Cash',
            'reference_number' => 'REF-UNIQUE-999',
        ])->assertStatus(201);

        // Try duplicate
        $response = $this->postJson('/api/v1/payments/pay-item', [
            'student_fee_bill_item_id' => $this->itemA->id,
            'amount' => 100.00,
            'payment_method' => 'Cash',
            'reference_number' => 'REF-UNIQUE-999',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['reference_number']);
    }

    /**
     * Test general payment sequential allocation logic
     */
    public function test_general_payment_allocates_sequentially(): void
    {
        // Simulate recording a general payment at the bill level (no item ID specified)
        // This simulates cash/cheque payments made through the old system dashboard
        $payment = FeePayment::create([
            'student_fee_bill_id' => $this->bill->id,
            'student_id' => $this->student->id,
            'amount' => 750.00, // This should fully pay Item A ($600) and partially pay Item B ($150 of $400)
            'payment_method' => 'Bank Transfer',
            'reference_number' => 'REF-GENERAL-888',
            'recorded_by' => $this->adminUser->id,
            'payment_date' => Carbon::now(),
        ]);

        // Recalculate
        $this->bill->recalculatePaymentStatus();

        // Check Item A (ordered first in DB)
        $this->itemA->refresh();
        $this->assertEquals(600.00, (float)$this->itemA->amount_paid);
        $this->assertEquals(0.00, (float)$this->itemA->balance);
        $this->assertEquals('paid', $this->itemA->status);

        // Check Item B
        $this->itemB->refresh();
        $this->assertEquals(150.00, (float)$this->itemB->amount_paid);
        $this->assertEquals(250.00, (float)$this->itemB->balance);
        $this->assertEquals('partially_paid', $this->itemB->status);

        // Check parent bill
        $this->bill->refresh();
        $this->assertEquals(750.00, (float)$this->bill->amount_paid);
        $this->assertEquals(250.00, (float)$this->bill->balance);
        $this->assertEquals('partially_paid', $this->bill->status);
    }
}
