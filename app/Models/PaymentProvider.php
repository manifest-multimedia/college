<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class PaymentProvider extends Model
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'name',
        'code',
        'status',
        'created_by',
    ];

    /**
     * Get the user who created this provider.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
