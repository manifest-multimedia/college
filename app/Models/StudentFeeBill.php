<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentFeeBill extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'academic_year_id',
        'semester_id',
        'fee_type_id',
        'total_amount',
        'amount_paid',
        'balance',
        'payment_percentage',
        'status',
        'billing_date',
        'bill_reference',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'payment_percentage' => 'decimal:2',
        'billing_date' => 'datetime',
    ];

    /**
     * Get the student associated with this fee bill
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the academic year associated with this fee bill
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the semester associated with this fee bill
     */
    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Get the fee type associated with this fee bill
     */
    public function feeType()
    {
        return $this->belongsTo(FeeType::class);
    }

    /**
     * Get the bill items associated with this fee bill
     */
    public function billItems()
    {
        return $this->hasMany(StudentFeeBillItem::class);
    }

    /**
     * Get the payments made for this fee bill
     */
    public function payments()
    {
        return $this->hasMany(FeePayment::class);
    }

    /**
     * Check if student has paid at least the given percentage
     */
    public function hasMinimumPayment(float $percentage): bool
    {
        return $this->payment_percentage >= $percentage;
    }

    /**
     * Accessor for total_paid (alias for amount_paid)
     * This ensures consistency across views
     */
    public function getTotalPaidAttribute(): float
    {
        return $this->amount_paid;
    }

    /**
     * Display payment percentage: cap at 100% so we never show "155%" etc.
     * When overpaid, show 100% and use balance display for the credit (green, in brackets).
     */
    public function getDisplayPaymentPercentageAttribute(): float
    {
        $pct = (float) $this->payment_percentage;

        return round(min(100.0, $pct), 1);
    }

    /**
     * Overpayment amount (credit in favor of student). Zero if not overpaid.
     */
    public function getOverpaymentAmountAttribute(): float
    {
        return max(0, (float) $this->amount_paid - (float) $this->total_amount);
    }

    /**
     * Balance display type for UI: 'credit' (overpaid), 'debit' (amount owed), or 'zero'.
     */
    public function getBalanceDisplayTypeAttribute(): string
    {
        $over = $this->overpayment_amount;
        if ($over > 0.005) {
            return 'credit';
        }
        $bal = (float) $this->balance;
        if ($bal > 0.005) {
            return 'debit';
        }

        return 'zero';
    }

    /**
     * Amount to show for balance display: credit amount (positive), balance (positive), or 0.
     */
    public function getBalanceDisplayAmountAttribute(): float
    {
        if ($this->balance_display_type === 'credit') {
            return $this->overpayment_amount;
        }
        if ($this->balance_display_type === 'debit') {
            return (float) $this->balance;
        }

        return 0.0;
    }

    /**
     * Get simple payment status based on balance
     * Returns: 'paid', 'partial', or 'unpaid'
     */
    public function getPaymentStatus(): string
    {
        // Simple logic: if balance is 0, it's paid
        if ($this->balance <= 0.01) {
            return 'paid';
        }
        // If some amount has been paid, it's partial
        if ($this->amount_paid > 0) {
            return 'partial';
        }

        // Otherwise it's unpaid
        return 'unpaid';
    }

    /**
     * Get the computed payment status based on current payment percentage
     * This is useful for displaying correct status even if DB is not updated
     */
    public function getComputedStatus(): string
    {
        if ($this->payment_percentage >= 100) {
            return 'paid';
        } elseif ($this->payment_percentage > 0) {
            return 'partially_paid';
        } else {
            return 'pending';
        }
    }

    /**
     * Get HTML badge for payment status.
     * Paid is always shown as 100%; partial/unpaid show actual percentage (capped at 100 for display).
     */
    public function getStatusBadgeHtml(): string
    {
        $status = $this->getPaymentStatus();
        $displayPct = $this->display_payment_percentage;

        return match ($status) {
            'paid' => '<span class="badge bg-success">PAID (100%)</span>',
            'partial' => '<span class="badge bg-primary">PARTIAL ('.number_format($displayPct, 1).'%)</span>',
            'unpaid' => '<span class="badge bg-danger">UNPAID ('.number_format($displayPct, 1).'%)</span>',
            default => '<span class="badge bg-secondary">UNKNOWN</span>',
        };
    }

    /**
     * Recalculate payment status after a payment is recorded
     * Updates amount_paid, balance, payment_percentage and status fields
     *
     * Simple algorithm:
     * 1. Sum all payments
     * 2. Calculate balance = total - paid
     * 3. If balance is 0 (or negative), status is 'paid'
     * 4. If paid > 0 but balance > 0, status is 'partially_paid'
     * 5. Otherwise, status is 'pending'
     */
    public function recalculatePaymentStatus()
    {
        // Calculate total amount paid from all confirmed payments
        $totalPaid = $this->payments()->sum('amount');

        // Update bill details
        $this->amount_paid = $totalPaid;
        $this->balance = max(0, $this->total_amount - $totalPaid);

        // Calculate payment percentage (avoid division by zero)
        if ($this->total_amount > 0) {
            $this->payment_percentage = ($totalPaid / $this->total_amount) * 100;
        } else {
            $this->payment_percentage = 0;
        }

        // Simple status logic based on balance
        if ($this->balance <= 0.01) {
            // Balance is effectively zero - PAID
            $this->status = 'paid';
        } elseif ($this->amount_paid > 0) {
            // Some payment made but balance remains - PARTIALLY PAID
            $this->status = 'partially_paid';
        } else {
            // No payment made - PENDING
            $this->status = 'pending';
        }

        return $this->save();
    }
}
