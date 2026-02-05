<?php

namespace Tests\Unit;

use App\Models\StudentFeeBill;
use Tests\TestCase;

class StudentFeeBillPaymentStatusTest extends TestCase
{
    /**
     * Test payment status when bill is fully paid
     */
    public function test_payment_status_when_fully_paid(): void
    {
        $bill = new StudentFeeBill([
            'total_amount' => 100.00,
            'amount_paid' => 100.00,
            'balance' => 0.00,
            'payment_percentage' => 100.00,
        ]);

        $this->assertEquals('paid', $bill->getPaymentStatus());
    }

    /**
     * Test payment status when bill is partially paid
     */
    public function test_payment_status_when_partially_paid(): void
    {
        $bill = new StudentFeeBill([
            'total_amount' => 100.00,
            'amount_paid' => 60.00,
            'balance' => 40.00,
            'payment_percentage' => 60.00,
        ]);

        $this->assertEquals('partial', $bill->getPaymentStatus());
    }

    /**
     * Test payment status when bill is unpaid
     */
    public function test_payment_status_when_unpaid(): void
    {
        $bill = new StudentFeeBill([
            'total_amount' => 100.00,
            'amount_paid' => 0.00,
            'balance' => 100.00,
            'payment_percentage' => 0.00,
        ]);

        $this->assertEquals('unpaid', $bill->getPaymentStatus());
    }

    /**
     * Test payment status with very small balance (rounding)
     */
    public function test_payment_status_with_negligible_balance(): void
    {
        $bill = new StudentFeeBill([
            'total_amount' => 100.00,
            'amount_paid' => 99.99,
            'balance' => 0.01,
            'payment_percentage' => 99.99,
        ]);

        // Balance of 0.01 should still be considered as paid
        $this->assertEquals('paid', $bill->getPaymentStatus());
    }

    /**
     * Test that total_paid accessor returns amount_paid
     */
    public function test_total_paid_accessor(): void
    {
        $bill = new StudentFeeBill([
            'amount_paid' => 50.00,
        ]);

        $this->assertEquals(50.00, $bill->total_paid);
        $this->assertEquals($bill->amount_paid, $bill->total_paid);
    }

    /**
     * Test getComputedStatus method for backwards compatibility
     */
    public function test_computed_status_method(): void
    {
        $paidBill = new StudentFeeBill([
            'payment_percentage' => 100.00,
        ]);
        $this->assertEquals('paid', $paidBill->getComputedStatus());

        $partialBill = new StudentFeeBill([
            'payment_percentage' => 50.00,
        ]);
        $this->assertEquals('partially_paid', $partialBill->getComputedStatus());

        $unpaidBill = new StudentFeeBill([
            'payment_percentage' => 0.00,
        ]);
        $this->assertEquals('pending', $unpaidBill->getComputedStatus());
    }

    /**
     * Test status badge HTML generation
     */
    public function test_status_badge_html(): void
    {
        $paidBill = new StudentFeeBill([
            'total_amount' => 100.00,
            'amount_paid' => 100.00,
            'balance' => 0.00,
            'payment_percentage' => 100.00,
        ]);
        $this->assertStringContainsString('bg-success', $paidBill->getStatusBadgeHtml());
        $this->assertStringContainsString('PAID', $paidBill->getStatusBadgeHtml());

        $partialBill = new StudentFeeBill([
            'total_amount' => 100.00,
            'amount_paid' => 60.00,
            'balance' => 40.00,
            'payment_percentage' => 60.00,
        ]);
        $this->assertStringContainsString('bg-primary', $partialBill->getStatusBadgeHtml());
        $this->assertStringContainsString('PARTIAL', $partialBill->getStatusBadgeHtml());

        $unpaidBill = new StudentFeeBill([
            'total_amount' => 100.00,
            'amount_paid' => 0.00,
            'balance' => 100.00,
            'payment_percentage' => 0.00,
        ]);
        $this->assertStringContainsString('bg-danger', $unpaidBill->getStatusBadgeHtml());
        $this->assertStringContainsString('UNPAID', $unpaidBill->getStatusBadgeHtml());
    }
}
