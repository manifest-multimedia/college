<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentFeeBill;
use App\Models\StudentFeeBillItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicReferenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_reference_generated_for_bill_and_item_on_create(): void
    {
        $user = User::factory()->create();

        $student = Student::create([
            'student_id' => 'REF-TEST-001',
            'first_name' => 'Ref',
            'last_name' => 'Tester',
            'email' => $user->email,
            'user_id' => $user->id,
        ]);

        $academicYear = AcademicYear::create([
            'name' => '2026-2027',
            'year' => 2026,
            'slug' => '2026-2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-08-31',
            'is_current' => true,
            'is_deleted' => false,
        ]);

        $semester = Semester::create([
            'name' => 'First Semester',
            'slug' => 'first-semester',
            'academic_year_id' => $academicYear->id,
            'is_current' => true,
        ]);

        $bill = StudentFeeBill::create([
            'student_id' => $student->id,
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'total_amount' => 500.00,
            'amount_paid' => 0.00,
            'balance' => 500.00,
            'payment_percentage' => 0,
            'status' => 'pending',
            'billing_date' => Carbon::now(),
            'bill_reference' => 'BILL-REF-TEST',
        ]);

        $feeType = \App\Models\FeeType::create([
            'name' => 'Test Fee',
            'code' => 'TST',
            'description' => 'Test fee type',
        ]);

        $item = StudentFeeBillItem::create([
            'student_fee_bill_id' => $bill->id,
            'fee_type_id' => $feeType->id,
            'amount' => 500.00,
            'amount_paid' => 0.00,
            'balance' => 500.00,
            'status' => 'pending',
        ]);

        $this->assertNotEmpty($bill->public_reference);
        $this->assertStringStartsWith('BILL-', $bill->public_reference);

        $this->assertNotEmpty($item->public_reference);
        $this->assertStringStartsWith('FEE-', $item->public_reference);
    }

    public function test_student_lookup_returns_item_references(): void
    {
        $user = User::factory()->create();

        $student = Student::create([
            'student_id' => 'REF-TEST-002',
            'first_name' => 'Ref',
            'last_name' => 'Lookup',
            'email' => $user->email,
            'user_id' => $user->id,
        ]);

        $academicYear = AcademicYear::create([
            'name' => '2026-2027',
            'year' => 2026,
            'slug' => '2026-2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-08-31',
            'is_current' => true,
            'is_deleted' => false,
        ]);

        $semester = Semester::create([
            'name' => 'First Semester',
            'slug' => 'first-semester',
            'academic_year_id' => $academicYear->id,
            'is_current' => true,
        ]);

        $bill = StudentFeeBill::create([
            'student_id' => $student->id,
            'academic_year_id' => $academicYear->id,
            'semester_id' => $semester->id,
            'total_amount' => 200.00,
            'amount_paid' => 0.00,
            'balance' => 200.00,
            'payment_percentage' => 0,
            'status' => 'pending',
            'billing_date' => Carbon::now(),
            'bill_reference' => 'BILL-REF-LOOKUP',
        ]);

        $feeType = \App\Models\FeeType::create([
            'name' => 'Lookup Fee',
            'code' => 'LUF',
            'description' => 'Lookup fee type',
        ]);

        $item = StudentFeeBillItem::create([
            'student_fee_bill_id' => $bill->id,
            'fee_type_id' => $feeType->id,
            'amount' => 200.00,
            'amount_paid' => 0.00,
            'balance' => 200.00,
            'status' => 'pending',
        ]);

        // Acting as student should return lookup with item_reference
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Student']);
        $user->assignRole('Student');
        $this->actingAs($user);
        $response = $this->getJson('/api/v1/payments/student');

        $response->assertStatus(200)
            ->assertJsonPath('bills.0.items.0.item_reference', $item->public_reference);
    }
}
