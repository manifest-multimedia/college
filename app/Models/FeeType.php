<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the fee structures for this fee type
     */
    public function feeStructures()
    {
        return $this->hasMany(FeeStructure::class);
    }

    /**
     * Get fee bill items for this fee type
     */
    public function feeBillItems()
    {
        return $this->hasMany(StudentFeeBillItem::class);
    }
}
