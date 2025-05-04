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
     * Recalculate payment status after a payment is recorded
     * Updates amount_paid, balance, payment_percentage and status fields
     */
    public function recalculatePaymentStatus()
    {
        // Calculate total amount paid from all payments
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
        
        // Update status based on payment percentage
        if ($this->payment_percentage >= 100) {
            $this->status = 'Paid';
        } elseif ($this->payment_percentage > 0) {
            $this->status = 'Partial';
        } else {
            $this->status = 'Unpaid';
        }
        
        return $this->save();
    }
}