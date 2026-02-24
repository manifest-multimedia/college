<?php

namespace Tests\Feature;

use App\Livewire\Finance\FeePaymentManager;
use App\Models\AcademicYear;
use App\Models\FeePayment;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentFeeBill;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FeePaymentManagerTest extends TestCase
{
    use RefreshDatabase;

    protected User $financeUser;

    protected AcademicYear $academicYear;

    protected Semester $semester;

    protected Student $student;

    protected StudentFeeBill $bill;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Finance Manager']);
        $this->financeUser = User::factory()->create(['role' => 'Finance Manager']);
        $this->financeUser->assignRole('Finance Manager');

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
            'student_id' => 'STU001',
            'first_name' => 'Test',
            'last_name' => 'Student',
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
            'bill_reference' => 'BILL-'.strtoupper(\Illuminate\Support\Str::random(8)),
        ]);
    }

    public function test_payments_page_loads_for_authorized_user(): void
    {
        $this->actingAs($this->financeUser);

        $response = $this->get(route('finance.payments'));

        $response->assertOk();
        $response->assertSee('Fee Payment Manager', false);
        $response->assertSee('Record', false);
    }

    public function test_component_renders_and_shows_search(): void
    {
        $this->actingAs($this->financeUser);

        Livewire::test(FeePaymentManager::class)
            ->assertOk()
            ->assertSee('Fee Payment Manager', false)
            ->assertSee('Search', false);
    }

    public function test_open_payment_form_without_bill_does_not_open_form(): void
    {
        $this->actingAs($this->financeUser);

        // With no student/bill loaded, openPaymentForm should not open the form
        Livewire::test(FeePaymentManager::class)
            ->call('openPaymentForm')
            ->assertSet('showPaymentForm', false);
    }

    public function test_load_student_and_bill_then_open_payment_form(): void
    {
        $this->actingAs($this->financeUser);

        Livewire::test(FeePaymentManager::class)
            ->call('loadStudent', $this->student->id)
            ->assertSet('loadedStudent.id', $this->student->id)
            ->assertSet('studentId', $this->student->id)
            ->assertSet('loadedBill.id', $this->bill->id)
            ->call('openPaymentForm')
            ->assertSet('showPaymentForm', true)
            ->assertSet('paymentAmount', 1000.00);
    }

    public function test_close_payment_form(): void
    {
        $this->actingAs($this->financeUser);

        Livewire::test(FeePaymentManager::class)
            ->call('loadStudent', $this->student->id)
            ->call('openPaymentForm')
            ->assertSet('showPaymentForm', true)
            ->call('closePaymentForm')
            ->assertSet('showPaymentForm', false);
    }

    public function test_record_payment_updates_bill_and_shows_receipt(): void
    {
        $this->actingAs($this->financeUser);

        $ref = 'REF-'.uniqid();
        $component = Livewire::test(FeePaymentManager::class)
            ->call('loadStudent', $this->student->id)
            ->call('openPaymentForm')
            ->set('paymentAmount', 500.00)
            ->set('paymentMethod', 'Cash')
            ->set('paymentDate', now()->format('Y-m-d'))
            ->set('referenceNumber', $ref);

        $component->call('recordPayment');

        $component->assertSet('showPaymentForm', false);
        $component->assertSet('showPaymentDetails', true);

        $this->bill->refresh();
        $this->assertEquals(500.00, (float) $this->bill->amount_paid);
        $this->assertEquals(500.00, (float) $this->bill->balance);
        $this->assertEquals(1, FeePayment::where('student_fee_bill_id', $this->bill->id)->count());
        $this->assertDatabaseHas('fee_payments', [
            'student_fee_bill_id' => $this->bill->id,
            'amount' => 500.00,
            'reference_number' => $ref,
        ]);
    }

    public function test_overpayment_allowed_and_reflected_on_bill(): void
    {
        $this->actingAs($this->financeUser);

        Livewire::test(FeePaymentManager::class)
            ->call('loadStudent', $this->student->id)
            ->call('openPaymentForm')
            ->set('paymentAmount', 1500.00)
            ->set('paymentMethod', 'Mobile Money')
            ->set('paymentDate', now()->format('Y-m-d'))
            ->set('referenceNumber', 'REF-OVER-'.uniqid())
            ->call('recordPayment');

        $this->bill->refresh();
        $this->assertEquals(1500.00, (float) $this->bill->amount_paid);
        $this->assertEquals(0.00, (float) $this->bill->balance);
        $this->assertEquals('paid', $this->bill->status);
    }

    public function test_close_payment_details_clears_state(): void
    {
        $this->actingAs($this->financeUser);

        $payment = FeePayment::create([
            'student_fee_bill_id' => $this->bill->id,
            'student_id' => $this->student->id,
            'amount' => 100.00,
            'payment_method' => 'Cash',
            'reference_number' => 'REF-'.uniqid(),
            'receipt_number' => 'FP'.date('Ymd').strtoupper(\Illuminate\Support\Str::random(5)),
            'recorded_by' => $this->financeUser->id,
            'payment_date' => Carbon::now(),
        ]);

        Livewire::test(FeePaymentManager::class)
            ->call('loadStudent', $this->student->id)
            ->call('viewPayment', $payment->id)
            ->assertSet('showPaymentDetails', true)
            ->assertSet('selectedPaymentId', $payment->id)
            ->call('closePaymentDetails')
            ->assertSet('showPaymentDetails', false)
            ->assertSet('selectedPaymentId', null);
    }

    public function test_view_payment_opens_receipt(): void
    {
        $this->actingAs($this->financeUser);

        $payment = FeePayment::create([
            'student_fee_bill_id' => $this->bill->id,
            'student_id' => $this->student->id,
            'amount' => 200.00,
            'payment_method' => 'Bank Transfer',
            'reference_number' => 'REF-VIEW-'.uniqid(),
            'receipt_number' => 'FP'.date('Ymd').strtoupper(\Illuminate\Support\Str::random(5)),
            'recorded_by' => $this->financeUser->id,
            'payment_date' => Carbon::now(),
        ]);

        Livewire::test(FeePaymentManager::class)
            ->call('viewPayment', $payment->id)
            ->assertSet('showPaymentDetails', true)
            ->assertSet('selectedPaymentId', $payment->id)
            ->assertSee('PAYMENT RECEIPT', false)
            ->assertSee($payment->receipt_number, false);
    }
}
