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
}