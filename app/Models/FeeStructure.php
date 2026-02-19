<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'fee_type_id',
        'college_class_id',
        'academic_year_id',
        'semester_id',
        'amount',
        'is_mandatory',
        'is_active',
        'applicable_gender',
    ];

    /** Applicable gender: 'all', 'male', 'female' */
    public const APPLICABLE_GENDER_ALL = 'all';
    public const APPLICABLE_GENDER_MALE = 'male';
    public const APPLICABLE_GENDER_FEMALE = 'female';

    protected $casts = [
        'is_mandatory' => 'boolean',
        'is_active' => 'boolean',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the fee type associated with this fee structure
     */
    public function feeType()
    {
        return $this->belongsTo(FeeType::class);
    }

    /**
     * Get the college class associated with this fee structure
     */
    public function collegeClass()
    {
        return $this->belongsTo(CollegeClass::class);
    }

    /**
     * Get the academic year associated with this fee structure
     */
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the semester associated with this fee structure
     */
    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Get bill items associated with this fee structure
     */
    public function billItems()
    {
        return $this->hasMany(StudentFeeBillItem::class);
    }
}
