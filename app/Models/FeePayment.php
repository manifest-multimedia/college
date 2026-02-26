<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_fee_bill_id',
        'student_id',
        'amount',
        'payment_method',
        'reference_number',
        'receipt_number',
        'note',
        'recorded_by',
        'payment_date',
        'reversed_at',
        'reversed_by',
        'reversal_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
        'reversed_at' => 'datetime',
    ];

    /**
     * Get the student fee bill this payment belongs to
     */
    public function studentFeeBill()
    {
        return $this->belongsTo(StudentFeeBill::class);
    }

    /**
     * Get the student who made this payment
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the user who recorded this payment
     */
    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Get the user who reversed this payment (if any)
     */
    public function reversedBy()
    {
        return $this->belongsTo(User::class, 'reversed_by');
    }

    /**
     * Scope to only include active (non-reversed) payments
     */
    public function scopeActive($query)
    {
        return $query->whereNull('reversed_at');
    }

    /**
     * Whether this payment has been reversed
     */
    public function isReversed(): bool
    {
        return $this->reversed_at !== null;
    }
}
