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
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
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
}