<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemoAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'memo_id',
        'user_id',
        'action_type',
        'comment',
        'forwarded_to_user_id',
        'forwarded_to_department_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the memo this action belongs to.
     */
    public function memo()
    {
        return $this->belongsTo(Memo::class);
    }

    /**
     * Get the user who performed this action.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user this memo was forwarded to (if applicable).
     */
    public function forwardedToUser()
    {
        return $this->belongsTo(User::class, 'forwarded_to_user_id');
    }

    /**
     * Get the department this memo was forwarded to (if applicable).
     */
    public function forwardedToDepartment()
    {
        return $this->belongsTo(Department::class, 'forwarded_to_department_id');
    }

    /**
     * Scope a query to only include actions of a specific type.
     */
    public function scopeOfType($query, $actionType)
    {
        return $query->where('action_type', $actionType);
    }

    /**
     * Scope a query to only include actions performed by a specific user.
     */
    public function scopePerformedBy($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
