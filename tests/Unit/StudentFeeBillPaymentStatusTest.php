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

    /**
     * Display percentage is capped at 100% when overpaid
     */
    public function test_display_payment_percentage_capped_at_100(): void
    {
        $bill = new StudentFeeBill([
            'total_amount' => 1000.00,
            'amount_paid' => 1554.00,
            'balance' => 0.00,
            'payment_percentage' => 155.4,
        ]);

        $this->assertEquals(100.0, $bill->display_payment_percentage);
    }

    /**
     * Overpayment amount is correct when paid more than total
     */
    public function test_overpayment_amount(): void
    {
        $bill = new StudentFeeBill([
            'total_amount' => 1000.00,
            'amount_paid' => 1200.00,
            'balance' => 0.00,
        ]);

        $this->assertEquals(200.0, $bill->overpayment_amount);
    }

    /**
     * Balance display type: credit when overpaid, debit when balance due, zero when exact
     */
    public function test_balance_display_type_and_amount(): void
    {
        $overpaid = new StudentFeeBill([
            'total_amount' => 100.00,
            'amount_paid' => 150.00,
            'balance' => 0.00,
        ]);
        $this->assertEquals('credit', $overpaid->balance_display_type);
        $this->assertEquals(50.0, $overpaid->balance_display_amount);

        $underpaid = new StudentFeeBill([
            'total_amount' => 100.00,
            'amount_paid' => 40.00,
            'balance' => 60.00,
        ]);
        $this->assertEquals('debit', $underpaid->balance_display_type);
        $this->assertEquals(60.0, $underpaid->balance_display_amount);

        $exact = new StudentFeeBill([
            'total_amount' => 100.00,
            'amount_paid' => 100.00,
            'balance' => 0.00,
        ]);
        $this->assertEquals('zero', $exact->balance_display_type);
        $this->assertEquals(0.0, $exact->balance_display_amount);
    }
}
