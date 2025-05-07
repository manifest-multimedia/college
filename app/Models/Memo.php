<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Memo extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'user_id',
        'department_id',
        'recipient_id',
        'recipient_department_id',
        'priority',
        'requested_action',
        'status',
        'reference_number',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($memo) {
            // Generate a unique reference number if not provided
            if (!$memo->reference_number) {
                $memo->reference_number = 'MEMO-' . date('Ymd') . '-' . strtoupper(Str::random(5));
            }
        });
    }

    /**
     * Get the user who created the memo.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the department that the memo originated from.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the recipient user of the memo.
     */
    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /**
     * Get the recipient department of the memo.
     */
    public function recipientDepartment()
    {
        return $this->belongsTo(Department::class, 'recipient_department_id');
    }

    /**
     * Get the actions related to this memo.
     */
    public function actions()
    {
        return $this->hasMany(MemoAction::class);
    }

    /**
     * Get the attachments for this memo.
     */
    public function attachments()
    {
        return $this->hasMany(MemoAttachment::class);
    }

    /**
     * Scope a query to only include memos with a specific status.
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include memos with a specific priority.
     */
    public function scopeWithPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope a query to only include memos created by a specific user.
     */
    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include memos sent to a specific user.
     */
    public function scopeSentTo($query, $userId)
    {
        return $query->where('recipient_id', $userId);
    }

    /**
     * Scope a query to only include memos sent to a specific department.
     */
    public function scopeSentToDepartment($query, $departmentId)
    {
        return $query->where('recipient_department_id', $departmentId);
    }

    /**
     * Get the latest action of a specific type for this memo.
     */
    public function getLatestAction($actionType)
    {
        return $this->actions()
            ->where('action_type', $actionType)
            ->latest()
            ->first();
    }
}
