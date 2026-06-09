<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentFeeBillItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_fee_bill_id',
        'fee_type_id',
        'fee_structure_id',
        'amount',
        'amount_paid',
        'balance',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    /**
     * Get the student fee bill this item belongs to
     */
    public function studentFeeBill()
    {
        return $this->belongsTo(StudentFeeBill::class);
    }

    /**
     * Get the fee type associated with this bill item
     */
    public function feeType()
    {
        return $this->belongsTo(FeeType::class);
    }

    /**
     * Get the fee structure associated with this bill item
     */
    public function feeStructure()
    {
        return $this->belongsTo(FeeStructure::class);
    }

    /**
     * Get the payments made for this bill item
     */
    public function payments()
    {
        return $this->hasMany(FeePayment::class, 'student_fee_bill_item_id');
    }
}
