<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'recipient',
        'message',
        'provider',
        'type',
        'group_id',
        'status',
        'response_data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'response_data' => 'json',
    ];

    /**
     * Get the user that created the SMS log.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
